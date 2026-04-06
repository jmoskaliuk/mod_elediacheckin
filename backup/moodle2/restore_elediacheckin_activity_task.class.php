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
 * Restore task wiring for mod_elediacheckin.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/elediacheckin/backup/moodle2/restore_elediacheckin_stepslib.php');

/**
 * Activity task for restoring a mod_elediacheckin instance.
 */
class restore_elediacheckin_activity_task extends restore_activity_task {
    /**
     * Defines task-level settings. None needed.
     */
    protected function define_my_settings(): void {
    }

    /**
     * Registers the restore structure step.
     */
    protected function define_my_steps(): void {
        $this->add_step(new restore_elediacheckin_activity_structure_step(
            'elediacheckin_structure',
            'elediacheckin.xml'
        ));
    }

    /**
     * Lists content areas whose file references must be restored.
     *
     * @return array
     */
    public static function define_decode_contents(): array {
        $contents = [];
        $contents[] = new restore_decode_content('elediacheckin', ['intro'], 'elediacheckin');
        return $contents;
    }

    /**
     * Defines decoding rules for backed-up links.
     *
     * @return array
     */
    public static function define_decode_rules(): array {
        $rules = [];
        $rules[] = new restore_decode_rule(
            'ELEDIACHECKINVIEWBYID',
            '/mod/elediacheckin/view.php?id=$1',
            'course_module'
        );
        $rules[] = new restore_decode_rule(
            'ELEDIACHECKININDEX',
            '/mod/elediacheckin/index.php?id=$1',
            'course'
        );
        return $rules;
    }

    /**
     * Returns the event restore rules. None (no logs in MVP).
     *
     * @return array
     */
    public static function define_restore_log_rules(): array {
        return [];
    }
}
