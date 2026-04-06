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
 * Backup structure definition for mod_elediacheckin.
 *
 * Only the activity instance row is backed up. Synchronised questions/categories
 * are shared across all instances and are re-populated by the sync task on restore.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Defines the backup structure for one activity instance.
 */
class backup_elediacheckin_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the structure of the activity backup XML.
     *
     * @return backup_nested_element The root backup element.
     */
    protected function define_structure(): backup_nested_element {
        $elediacheckin = new backup_nested_element('elediacheckin', ['id'], [
            'name', 'intro', 'introformat',
            'ziele', 'categories', 'contentlang',
            'randomstart', 'shownav', 'showother', 'showfilter', 'avoidrepeat',
            'timecreated', 'timemodified',
        ]);

        $elediacheckin->set_source_table('elediacheckin', ['id' => backup::VAR_ACTIVITYID]);

        $elediacheckin->annotate_files('mod_elediacheckin', 'intro', null);

        return $this->prepare_activity_structure($elediacheckin);
    }
}
