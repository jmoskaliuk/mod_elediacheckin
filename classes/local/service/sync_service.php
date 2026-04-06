<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Synchronisation service - orchestrates fetch, validate, stage and activate.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\local\service;

use mod_elediacheckin\content\content_bundle;
use mod_elediacheckin\content\content_source_exception;
use mod_elediacheckin\content\content_source_registry;

/**
 * Orchestrates a content synchronisation run.
 *
 * Flow:
 *   1. Resolve the configured content source (fallback: bundled).
 *   2. Ask it for a validated content_bundle.
 *   3. Write all questions into stage=1 rows, then swap stage=1 → stage=0
 *      inside a transaction. A failing run leaves the live dataset intact
 *      (contract from concept doc section 8.4).
 *   4. Invalidate the questions cache and record a log row.
 */
class sync_service {
    /** Question is live and visible for fetching. */
    private const STAGE_LIVE    = 0;

    /** Question is staged for activation and not yet live. */
    private const STAGE_STAGING = 1;

    /** Questions table, central enough to avoid repeating the literal. */
    private const TBL_QUESTION = 'elediacheckin_question';

    /** @var config_service */
    private config_service $config;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        $this->config = new config_service();
    }

    /**
     * Runs a full sync cycle and records result.
     *
     * Contract: a failing sync MUST leave the existing dataset untouched.
     *
     * @param string $source Either 'manual' or 'scheduled'.
     * @return \stdClass Log row (also persisted to database).
     */
    public function run(string $source): \stdClass {
        global $DB;

        $log                    = new \stdClass();
        $log->timestarted       = time();
        $log->source            = $source;
        $log->result            = 'other';
        $log->questionsimported = 0;
        $log->message           = '';
        $log->sourceid          = null;
        $log->bundleid          = null;
        $log->bundleversion     = null;

        try {
            // Resolve content source (fallback: bundled).
            $sourceid = (string) $this->config->get('contentsource', 'bundled');
            $contentsource = content_source_registry::get($sourceid)
                ?? content_source_registry::get_fallback();
            $log->sourceid = $contentsource->get_id();

            // Fetch + validate.
            $bundle = $contentsource->fetch_bundle();
            $log->bundleid      = $bundle->get_bundle_id();
            $log->bundleversion = $bundle->get_bundle_version();

            // Stage + swap.
            $imported = $this->stage_and_swap($bundle);

            // Invalidate caches so the next read sees the new bundle.
            (new cache_service())->purge_all();

            $log->result            = 'success';
            $log->questionsimported = $imported;
            $log->message           = sprintf(
                'source=%s bundle=%s version=%s imported=%d',
                $contentsource->get_id(),
                $bundle->get_bundle_id(),
                $bundle->get_bundle_version(),
                $imported
            );
        } catch (content_source_exception $e) {
            $log->result  = 'other';
            $log->message = $e->getMessage() . ' :: ' . $e->debuginfo;
        } catch (\Throwable $e) {
            $log->result  = 'other';
            $log->message = $e->getMessage();
        }

        $log->timefinished = time();
        $log->id = $DB->insert_record('elediacheckin_sync_log', $log);

        return $log;
    }

    /**
     * Write the bundle into staging rows and atomically swap into live.
     *
     * Strategy:
     *   1. Purge any leftover staging rows (from a previous crashed run).
     *   2. Insert all bundle questions with stage = STAGING.
     *   3. In one transaction: delete live rows, flip staging → live.
     *
     * Step 3 is the only part that touches live data. If step 2 throws, the
     * live dataset is never modified.
     *
     * @param content_bundle $bundle
     * @return int Number of questions now live.
     * @throws \dml_exception
     */
    private function stage_and_swap(content_bundle $bundle): int {
        global $DB;

        // 1) Clean leftover staging data from a previous failed run.
        $DB->delete_records(self::TBL_QUESTION, ['stage' => self::STAGE_STAGING]);

        // 2) Insert all questions as staging rows.
        $now = time();
        foreach ($bundle->get_questions() as $q) {
            $record = $this->question_to_record($q, $bundle, $now);
            $DB->insert_record(self::TBL_QUESTION, $record);
        }

        // 3) Atomic swap.
        $transaction = $DB->start_delegated_transaction();
        try {
            $DB->delete_records(self::TBL_QUESTION, ['stage' => self::STAGE_LIVE]);
            $DB->set_field(self::TBL_QUESTION, 'stage', self::STAGE_LIVE,
                ['stage' => self::STAGE_STAGING]);
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }

        return $DB->count_records(self::TBL_QUESTION, ['stage' => self::STAGE_LIVE]);
    }

    /**
     * Map a validated question array from the bundle to a database row.
     *
     * @param array<string, mixed> $q The question array.
     * @param content_bundle $bundle The content bundle.
     * @param int $now Current Unix timestamp.
     * @return \stdClass Database record object.
     */
    private function question_to_record(array $q, content_bundle $bundle, int $now): \stdClass {
        $categories = array_values($q['kategorie'] ?? []);
        $zielgruppe = is_array($q['zielgruppe'] ?? null)
            ? array_values($q['zielgruppe'])
            : [];
        $kontext = is_array($q['kontext'] ?? null)
            ? array_values($q['kontext'])
            : [];

        $record = new \stdClass();
        $record->stage         = self::STAGE_STAGING;
        $record->bundleid      = $bundle->get_bundle_id();
        $record->bundleversion = $bundle->get_bundle_version();
        $record->externalid    = (string) ($q['id'] ?? '');
        $record->ziel          = (string) ($q['ziel'] ?? '');
        $record->categories    = implode(',', $categories);
        $record->zielgruppe    = implode(',', $zielgruppe);
        $record->kontext       = implode(',', $kontext);
        $record->frage         = (string) ($q['frage'] ?? '');
        $record->hasanswer     = !empty($q['hat_antwort']) ? 1 : 0;
        $record->antwort       = isset($q['antwort']) ? (string) $q['antwort'] : null;
        $record->lang          = (string) ($q['sprache'] ?? $bundle->get_language());
        $record->author        = isset($q['autor']) ? (string) $q['autor'] : null;
        $record->quelle        = isset($q['quelle']) ? (string) $q['quelle'] : null;
        $record->license       = (string) ($q['lizenz'] ?? '');
        $record->qversion      = (string) ($q['version'] ?? '1');
        $record->qstatus       = (string) ($q['status'] ?? 'published');
        $record->link          = isset($q['link']) ? (string) $q['link'] : null;
        $record->media         = isset($q['media']) ? (string) $q['media'] : null;
        $record->extcreated    = $this->parse_iso($q['created_at'] ?? null);
        $record->extmodified   = $this->parse_iso($q['updated_at'] ?? null);
        $record->timecreated   = $now;
        $record->timemodified  = $now;
        return $record;
    }

    /**
     * Best-effort ISO-8601 parser. Returns null on any failure.
     *
     * @param mixed $value
     * @return int|null
     */
    private function parse_iso($value): ?int {
        if (!is_string($value) || $value === '') {
            return null;
        }
        $ts = strtotime($value);
        return $ts === false ? null : $ts;
    }
}
