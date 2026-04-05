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
 * This is a scaffold. The concrete HTTP fetch, JSON Schema validation and
 * staging-to-live swap are deliberately left as TODOs until the content
 * repository format is finalised. See concept sections 8.3 and 8.4.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\local\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Orchestrates a content synchronisation run.
 */
class sync_service {

    /** @var config_service */
    private $config;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->config = new config_service();
    }

    /**
     * Runs a full sync cycle. Records a row in elediacheckin_sync_log and returns the result.
     *
     * Contract: a failing sync MUST leave the existing dataset untouched (concept 8.4).
     *
     * @param string $source Either 'manual' or 'scheduled'.
     * @return \stdClass Log row (also persisted).
     */
    public function run(string $source): \stdClass {
        global $DB;

        $log               = new \stdClass();
        $log->timestarted  = time();
        $log->source       = $source;
        $log->result       = 'other';
        $log->questionsimported = 0;

        try {
            $repourl = $this->config->get('repourl');
            if (empty($repourl)) {
                throw new \moodle_exception('syncerror_norepourl', 'elediacheckin');
            }

            // TODO: 1. Fetch raw JSON/ZIP from repourl via curl helper.
            // TODO: 2. Validate JSON structure against schema/questions.schema.json.
            // TODO: 3. Run plausibility checks (required fields, known categories).
            // TODO: 4. Write into staging tables.
            // TODO: 5. On full success, swap staging into live tables in a transaction.
            // TODO: 6. Invalidate the questions+categories caches.

            $log->result = 'success';
            $log->message = 'scaffold: no-op sync';
        } catch (\Throwable $e) {
            $log->result  = 'other';
            $log->message = $e->getMessage();
        }

        $log->timefinished = time();
        $log->id = $DB->insert_record('elediacheckin_sync_log', $log);

        return $log;
    }
}
