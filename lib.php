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
 * Core callbacks for mod_elediacheckin.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Declares which optional features the activity module supports.
 *
 * @param string $feature One of the FEATURE_* constants.
 * @return mixed True if supported, false if not, null if unknown.
 */
function elediacheckin_supports(string $feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_MOD_PURPOSE:
            return defined('MOD_PURPOSE_COMMUNICATION') ? MOD_PURPOSE_COMMUNICATION : null;
        default:
            return null;
    }
}

/**
 * Creates a new instance of the activity.
 *
 * @param stdClass $data Form data from mod_form.
 * @param mod_elediacheckin_mod_form|null $mform The form instance.
 * @return int The new instance id.
 */
function elediacheckin_add_instance(stdClass $data, $mform = null): int {
    global $DB;

    $data->timecreated  = time();
    $data->timemodified = $data->timecreated;

    $data->id = $DB->insert_record('elediacheckin', $data);

    return $data->id;
}

/**
 * Updates an existing activity instance.
 *
 * @param stdClass $data Form data from mod_form.
 * @param mod_elediacheckin_mod_form|null $mform The form instance.
 * @return bool Always true.
 */
function elediacheckin_update_instance(stdClass $data, $mform = null): bool {
    global $DB;

    $data->id           = $data->instance;
    $data->timemodified = time();

    return $DB->update_record('elediacheckin', $data);
}

/**
 * Deletes an activity instance and all associated data.
 *
 * @param int $id Instance id.
 * @return bool True on success.
 */
function elediacheckin_delete_instance(int $id): bool {
    global $DB;

    if (!$DB->record_exists('elediacheckin', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('elediacheckin', ['id' => $id]);

    return true;
}
