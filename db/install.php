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

/**
 * Post-install callback.
 */
function xmldb_elediacheckin_install() {
    try {
        $service = new \mod_elediacheckin\local\service\sync_service();
        $service->run('install');
    } catch (\Throwable $e) {
        debugging(
            'mod_elediacheckin: initial content sync failed during install: '
                . $e->getMessage(),
            DEBUG_DEVELOPER
        );
    }

    // Import bundled user tours (Lehrkräfte-Onboarding). The autoloaded
    // tour_installer class handles all tool_usertours guards (table
    // existence, class availability) internally.
    \mod_elediacheckin\local\tour_installer::install_bundled_tours();
}
