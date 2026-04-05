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
 * Restore structure definition for mod_elediacheckin.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Defines how to restore a mod_elediacheckin activity from a backup package.
 */
class restore_elediacheckin_activity_structure_step extends restore_activity_structure_step {

    /**
     * Declares the paths in the backup XML that this step will process.
     *
     * @return array
     */
    protected function define_structure(): array {
        $paths = [];
        $paths[] = new restore_path_element('elediacheckin', '/activity/elediacheckin');
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Handles a restored activity instance row.
     *
     * @param array|object $data
     */
    protected function process_elediacheckin($data): void {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $data->timecreated  = time();
        $data->timemodified = time();

        $newid = $DB->insert_record('elediacheckin', $data);
        $this->apply_activity_instance($newid);
    }

    /**
     * Post-execute hook - re-attach intro files.
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_elediacheckin', 'intro', null);
    }
}
