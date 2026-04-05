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
 * Post-install hook for mod_elediacheckin.
 *
 * Runs an initial content synchronisation so the bundled default questions
 * are available immediately after installation — without waiting for the
 * scheduled task to run. Failures are logged but do NOT abort the install;
 * an admin can always re-run the sync from the admin report.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post-install callback.
 */
function xmldb_elediacheckin_install() {
    try {
        $service = new \mod_elediacheckin\local\service\sync_service();
        $service->run('install');
    } catch (\Throwable $e) {
        debugging(
            'mod_elediacheckin: initial content sync failed during install: ' . $e->getMessage(),
            DEBUG_DEVELOPER
        );
    }

    // Import bundled user tours (Lehrkräfte-Onboarding). tool_usertours auto-
    // imports tours from any plugin's db/tours/*.json directory on core
    // upgrade, but fresh installs skip that window because the plugin is
    // installed mid-upgrade. So we invoke the importer directly for each
    // JSON file shipped in db/tours. Failures are non-fatal — an admin can
    // always import the tour manually via Site admin → Appearance → Tours.
    mod_elediacheckin_install_bundled_tours();
}

/**
 * Imports every JSON tour from db/tours/ via tool_usertours.
 *
 * Skipped silently if tool_usertours is disabled or unavailable (e.g. in
 * minimal test environments).
 */
function mod_elediacheckin_install_bundled_tours(): void {
    global $CFG;

    if (!class_exists('\\tool_usertours\\manager')) {
        return;
    }

    $toursdir = $CFG->dirroot . '/mod/elediacheckin/db/tours';
    if (!is_dir($toursdir)) {
        return;
    }

    foreach (glob($toursdir . '/*.json') as $file) {
        try {
            $json = file_get_contents($file);
            if ($json === false) {
                continue;
            }
            \tool_usertours\manager::import_tour_from_json($json);
        } catch (\Throwable $e) {
            debugging(
                'mod_elediacheckin: could not import tour ' . basename($file) . ': ' . $e->getMessage(),
                DEBUG_DEVELOPER
            );
        }
    }
}
