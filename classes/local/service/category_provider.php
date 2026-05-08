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
 * Category provider service - reads locally-synchronised categories.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\local\service;

/**
 * Returns language-aware category labels for the filter UI.
 */
class category_provider {
    /**
     * Returns all categories available for a given language, keyed by external id.
     *
     * @param string $lang The language code.
     * @return \stdClass[] Array of category records.
     */
    public function get_all(string $lang): array {
        global $DB;
        $cache = (new cache_service())->categories();
        $cachekey = 'lang_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $lang);
        $cached = $cache->get($cachekey);
        if ($cached !== false) {
            $records = [];
            foreach ($cached as $key => $row) {
                $records[$key] = (object) $row;
            }
            return $records;
        }

        $records = $DB->get_records('elediacheckin_category', ['lang' => $lang], 'label ASC');
        $cachedrecords = [];
        foreach ($records as $key => $record) {
            $cachedrecords[$key] = (array) $record;
        }
        $cache->set($cachekey, $cachedrecords);
        return $records;
    }
}
