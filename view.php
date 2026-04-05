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
 * Main view page for an elediacheckin activity.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT); // Course module id.

$cm      = get_coursemodule_from_id('elediacheckin', $id, 0, false, MUST_EXIST);
$course  = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('elediacheckin', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = \core\context\module::instance($cm->id);
require_capability('mod/elediacheckin:view', $context);

// Completion & log.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$event = \mod_elediacheckin\event\course_module_viewed::create([
    'objectid' => $instance->id,
    'context'  => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('elediacheckin', $instance);
$event->trigger();

$PAGE->set_url('/mod/elediacheckin/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->js_call_amd('mod_elediacheckin/view', 'init', ['#mod-elediacheckin-root']);

echo $OUTPUT->header();

if (!empty($instance->intro)) {
    echo $OUTPUT->box(format_module_intro('elediacheckin', $instance, $cm->id), 'generalbox mod_introbox', 'elediacheckinintro');
}

// Resolve a question through the service layer.
$provider = new \mod_elediacheckin\local\service\question_provider();
$question = $provider->get_random_question([
    'mode'       => $instance->mode,
    'categories' => $instance->categories,
    'lang'       => $instance->contentlang ?: current_language(),
]);

$templatecontext = [
    'cmid'       => $cm->id,
    'mode'       => $instance->mode,
    'shownav'    => (bool)$instance->shownav,
    'showother'  => (bool)$instance->showother,
    'showfilter' => (bool)$instance->showfilter,
    'hasquestion' => !empty($question),
    'question'   => $question,
    'strnew'     => get_string('newquestion', 'elediacheckin'),
    'strnone'    => get_string('noquestions', 'elediacheckin'),
];

echo $OUTPUT->render_from_template('mod_elediacheckin/view', $templatecontext);

echo $OUTPUT->footer();
