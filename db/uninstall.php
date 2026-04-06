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
 * Uninstall callback for mod_elediacheckin.
 *
 * Removes the three bundled user tours (teacher activity tour,
 * admin settings tour, activity-modedit tour) from tool_usertours.
 * Moodle drops the plugin's own DB tables automatically from install.xml,
 * so only cross-table cleanup is needed here.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Uninstall callback.
 *
 * @return bool
 */
function xmldb_elediacheckin_uninstall(): bool {
    global $DB;

    // Nothing to do if tool_usertours is not present.
    if (!class_exists('\\tool_usertours\\manager')) {
        return true;
    }
    try {
        $dbman = $DB->get_manager();
        if (
            !$dbman->table_exists('tool_usertours_tours')
            || !$dbman->table_exists('tool_usertours_steps')
        ) {
            return true;
        }
    } catch (\Throwable $e) {
        return true;
    }

    // The three pathmatch patterns registered by this plugin's tours.
    // We double-check the tour name to avoid accidentally deleting a
    // site-admin tour that happens to share the same URL pattern.
    $patterns = [
        '/mod/elediacheckin/view.php%',
        '/admin/settings.php?section=modsettingelediacheckin%',
        '/course/modedit.php%',
    ];

    foreach ($patterns as $pattern) {
        try {
            $tours = $DB->get_records_select(
                'tool_usertours_tours',
                $DB->sql_like('pathmatch', ':path'),
                ['path' => $pattern]
            );
            foreach ($tours as $record) {
                // Only remove tours whose names are clearly ours.
                $name = (string) ($record->name ?? '');
                if (
                    stripos($name, 'check-in') === false
                    && stripos($name, 'elediacheckin') === false
                ) {
                    continue;
                }
                $tour = \tool_usertours\tour::load_from_record($record);
                $tour->remove();
            }
        } catch (\Throwable $e) {
            debugging(
                'mod_elediacheckin: could not remove tour (pathmatch=' . $pattern
                    . '): ' . $e->getMessage(),
                DEBUG_DEVELOPER
            );
        }
    }

    return true;
}
