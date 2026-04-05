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
 * Question provider service - reads locally-synchronised questions.
 *
 * This is the shared fachliche core used by both mod_elediacheckin and
 * block_elediacheckin. The block depends on this class and MUST NOT query
 * tables directly.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\local\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Resolves questions by filter criteria from the local Moodle database.
 *
 * Only live rows (stage = 0) are ever returned. Staging rows written mid-sync
 * are invisible to this service by construction.
 */
class question_provider {

    /** Live-stage marker, kept in sync with sync_service. */
    private const STAGE_LIVE = 0;

    /** Table name constant to avoid scattering string literals. */
    private const TBL_QUESTION = 'elediacheckin_question';

    /**
     * Fetches a single live question by its internal id.
     *
     * @param int $id
     * @return \stdClass|null
     */
    public function get_question_by_id(int $id): ?\stdClass {
        global $DB;
        $record = $DB->get_record(self::TBL_QUESTION, [
            'id'    => $id,
            'stage' => self::STAGE_LIVE,
        ]);
        return $record ?: null;
    }

    /**
     * Fetches a random live question matching the given filter.
     *
     * @param array<string, mixed> $filter See get_questions_by_filter().
     * @return \stdClass|null
     */
    public function get_random_question(array $filter): ?\stdClass {
        $questions = $this->get_questions_by_filter($filter);
        if (empty($questions)) {
            return null;
        }
        return $questions[array_rand($questions)];
    }

    /**
     * Fetches all live questions matching the given filter.
     *
     * Supported filter keys:
     *  - ziele      (string[]|string|null) One or more ziel values, or a CSV
     *               string. Empty/null = all.
     *  - categories (string[]|string|null) Category ids — a question matches
     *               if any of its categories intersect with the filter.
     *  - lang       (string|null)          ISO-639-1, null = any language.
     *  - qstatus    (string|null)          Defaults to 'published'.
     *
     * @param array<string, mixed> $filter
     * @return \stdClass[]
     */
    public function get_questions_by_filter(array $filter): array {
        global $DB;

        $where  = ['stage = :stage'];
        $params = ['stage' => self::STAGE_LIVE];

        // qstatus: default to 'published' so draft/deprecated rows are hidden.
        $where[]           = 'qstatus = :qstatus';
        $params['qstatus'] = (string) ($filter['qstatus'] ?? 'published');

        // ziele: accept CSV string or array.
        $ziele = $this->normalise_csv($filter['ziele'] ?? null);
        if (!empty($ziele)) {
            [$insql, $inparams] = $DB->get_in_or_equal($ziele, SQL_PARAMS_NAMED, 'ziel');
            $where[] = "ziel {$insql}";
            $params += $inparams;
        }

        // Language filter.
        if (!empty($filter['lang'])) {
            $where[]        = 'lang = :lang';
            $params['lang'] = (string) $filter['lang'];
        }

        $sql = 'SELECT * FROM {' . self::TBL_QUESTION . '} WHERE ' . implode(' AND ', $where);
        $records = $DB->get_records_sql($sql, $params);

        // Category filter is post-SQL: the column is a CSV, so we can't
        // portably intersect it in all DB backends. Since a typical activity
        // has at most a few hundred questions per language, filtering in PHP
        // is both simple and fast enough.
        $categoryfilter = $this->normalise_csv($filter['categories'] ?? null);
        if (!empty($categoryfilter)) {
            $records = array_filter($records, function (\stdClass $r) use ($categoryfilter): bool {
                $qcats = $r->categories === '' ? [] : explode(',', $r->categories);
                return (bool) array_intersect($categoryfilter, $qcats);
            });
        }

        return array_values($records);
    }

    /**
     * Normalise a CSV string or array of strings into a clean string array.
     *
     * @param mixed $value
     * @return string[]
     */
    private function normalise_csv($value): array {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (!is_array($value)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', $value), static fn($v) => $v !== ''));
    }
}
