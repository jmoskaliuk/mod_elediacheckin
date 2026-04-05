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
 * Backup task wiring for mod_elediacheckin.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/elediacheckin/backup/moodle2/backup_elediacheckin_stepslib.php');

/**
 * Activity task for backing up a single mod_elediacheckin instance.
 */
class backup_elediacheckin_activity_task extends backup_activity_task {

    /**
     * Declares task-level settings. None are needed.
     */
    protected function define_my_settings(): void {
    }

    /**
     * Registers the structure step defined in stepslib.
     */
    protected function define_my_steps(): void {
        $this->add_step(new backup_elediacheckin_activity_structure_step(
            'elediacheckin_structure',
            'elediacheckin.xml'
        ));
    }

    /**
     * Encodes content links for inter-course restore portability.
     *
     * @param string $content
     * @return string
     */
    public static function encode_content_links($content): string {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '#');

        $search  = '#(' . $base . '/mod/elediacheckin/view\.php\?id\=)([0-9]+)#';
        $content = preg_replace($search, '$@ELEDIACHECKINVIEWBYID*$2@$', $content);

        $search  = '#(' . $base . '/mod/elediacheckin/index\.php\?id\=)([0-9]+)#';
        $content = preg_replace($search, '$@ELEDIACHECKININDEX*$2@$', $content);

        return $content;
    }
}
