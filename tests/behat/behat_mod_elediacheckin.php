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
 * Custom Behat step definitions for mod_elediacheckin.
 *
 * @package    mod_elediacheckin
 * @category   test
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Step definitions for mod_elediacheckin Behat tests.
 *
 * @package    mod_elediacheckin
 * @category   test
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_elediacheckin extends behat_base {
    /**
     * Ensures the bundled eLeDia Check-in user tours are present in the DB.
     *
     * This is needed because mod_* plugins install before tool_usertours in
     * Moodle's plugin-type order, so the tool_usertours_tours table does not
     * exist when mod_elediacheckin's db/install.php runs. The install hook
     * bails out silently; this step explicitly imports the tours so tour-
     * related Behat scenarios can proceed without depending on install order.
     *
     * Unlike tour_installer::install_bundled_tours() (which catches all
     * Throwables silently), this step propagates exceptions so Behat can
     * report the real cause of failure.
     *
     * @Given the elediacheckin bundled tours are installed
     */
    public function the_elediacheckin_bundled_tours_are_installed(): void {
        global $CFG, $DB;

        if (!class_exists('\\tool_usertours\\manager')) {
            throw new \Exception('tool_usertours\\manager class not found — is tool_usertours enabled?');
        }

        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('tool_usertours_tours')) {
            throw new \Exception('tool_usertours_tours table does not exist in Behat DB');
        }

        $toursdir = $CFG->dirroot . '/mod/elediacheckin/db/tours';
        if (!is_dir($toursdir)) {
            throw new \Exception('Tours directory not found: ' . $toursdir);
        }

        $files = glob($toursdir . '/*.json');
        if (empty($files)) {
            throw new \Exception('No JSON tour files found in: ' . $toursdir);
        }

        foreach ($files as $file) {
            $json = file_get_contents($file);
            if ($json === false) {
                throw new \Exception('Could not read tour file: ' . $file);
            }
            \tool_usertours\manager::import_tour_from_json($json);
        }

        // Purge all caches so the browser request sees the freshly imported
        // tours and does not serve a stale empty list from a persistent cache.
        purge_all_caches();

        // Verify at least the teacher tour was actually stored in the DB.
        $exists = $DB->record_exists('tool_usertours_tours', ['name' => 'Check-in for teachers']);
        if (!$exists) {
            throw new \Exception(
                'Tour "Check-in for teachers" was not found in tool_usertours_tours after import. ' .
                'Check that db/tours/teacher_checkin_tour.json has "name": "Check-in for teachers".'
            );
        }
    }
}
