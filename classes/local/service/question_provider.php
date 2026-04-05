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
 * block_elediacheckin (see concept section 10). The block depends on this
 * class and MUST NOT query tables directly.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\local\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Resolves questions by filter criteria from the local Moodle database.
 */
class question_provider {

    /**
     * Fetches a single question by its internal id.
     *
     * @param int $id
     * @return \stdClass|null
     */
    public function get_question_by_id(int $id): ?\stdClass {
        global $DB;
        $record = $DB->get_record('elediacheckin_question', ['id' => $id]);
        return $record ?: null;
    }

    /**
     * Fetches a random question matching the given filter.
     *
     * @param array $filter Keys: mode (both|checkin|checkout), categories (csv of externalids), lang.
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
     * Fetches all questions matching the given filter.
     *
     * @param array $filter Keys: mode, categories, lang, status.
     * @return \stdClass[]
     */
    public function get_questions_by_filter(array $filter): array {
        global $DB;

        $where  = ['status = :status'];
        $params = ['status' => $filter['status'] ?? 'active'];

        $mode = $filter['mode'] ?? 'both';
        if ($mode === 'checkin' || $mode === 'checkout') {
            $where[]        = 'type = :type';
            $params['type'] = $mode;
        }

        if (!empty($filter['lang'])) {
            $where[]        = 'lang = :lang';
            $params['lang'] = $filter['lang'];
        }

        $sql = 'SELECT * FROM {elediacheckin_question} WHERE ' . implode(' AND ', $where);

        $records = $DB->get_records_sql($sql, $params);

        // TODO: Apply category filter via elediacheckin_question_cat once sync populates it.
        return array_values($records);
    }
}
