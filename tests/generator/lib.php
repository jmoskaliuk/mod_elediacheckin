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
 * Data generator for mod_elediacheckin.
 *
 * Enables the standard Behat/PHPUnit fixture syntax:
 *   the following "activity" exists:
 *     | activity | elediacheckin |
 *     | course   | C1            |
 *     | name     | My Check-in   |
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_elediacheckin_generator extends testing_module_generator {
    /**
     * Creates a new activity instance.
     *
     * All columns that have a DB default can be omitted — parent::create_instance()
     * merges them from get_instance_with_defaults() automatically.
     *
     * @param array|stdClass|null $record Field values (activity, course, name, …)
     * @param array|null $options         Generator options (section, visible, …)
     * @return stdClass                   The newly created cm record.
     */
    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;
        return parent::create_instance($record, $options);
    }
}
