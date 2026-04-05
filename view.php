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
 * Main (embedded) view page for an elediacheckin activity.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id         = required_param('id', PARAM_INT);
$activeziel = optional_param('activeziel', '', PARAM_ALPHA);

$cm       = get_coursemodule_from_id('elediacheckin', $id, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
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

// Resolve active ziel: if instance has multiple ziele, honour query param,
// otherwise default to the first configured ziel. Invalid values fall back.
$ziele = array_values(array_filter(array_map('trim', explode(',', (string)$instance->ziele))));
if (empty($ziele)) {
    $ziele = ['checkin'];
}
$multiziel = count($ziele) > 1;
if (!$multiziel || !in_array($activeziel, $ziele, true)) {
    $activeziel = $ziele[0];
}

// Resolve a question through the service layer — only for the active ziel.
// Language resolution tries: (1) the activity's configured lang, resolving
// the sentinels '_auto_' → current user language and '_course_' → course
// language; (2) the user's current language; (3) any language. This keeps
// the UX friendly on dev sites where the bundle language may not match
// the site language yet.
$provider = new \mod_elediacheckin\local\service\question_provider();
$langcandidates = [];
// Sentinels: '_auto_' = user language, '_course_' = course language.
// Kept as string literals so this hot-path resolves without loading
// mod_form.php; the canonical definitions live in mod_form::LANG_AUTO /
// LANG_COURSE and must stay in sync.
$configured = (string) ($instance->contentlang ?? '');
if ($configured === '_auto_') {
    $langcandidates[] = current_language();
} else if ($configured === '_course_') {
    $langcandidates[] = !empty($course->lang) ? $course->lang : current_language();
} else if ($configured !== '') {
    $langcandidates[] = $configured;
}
$langcandidates[] = current_language();
$langcandidates[] = null; // Final fallback: accept any language.
$question = null;
foreach (array_unique(array_filter($langcandidates, static fn($v) => $v !== ''), SORT_REGULAR) as $lang) {
    $question = $provider->get_random_question([
        'ziele'      => [$activeziel],
        'categories' => $instance->categories,
        'zielgruppe' => $instance->zielgruppe ?? null,
        'kontext'    => $instance->kontext ?? null,
        'lang'       => $lang,
    ]);
    if ($question) {
        break;
    }
}

// Build ziel-picker buttons (only used if $multiziel).
$zielbuttons = [];
foreach ($ziele as $z) {
    $zielbuttons[] = [
        'key'    => $z,
        'label'  => get_string_manager()->string_exists('ziel_' . $z, 'elediacheckin')
            ? get_string('ziel_' . $z, 'elediacheckin')
            : ucfirst($z),
        'active' => ($z === $activeziel),
        'url'    => (new moodle_url('/mod/elediacheckin/view.php', [
            'id'         => $cm->id,
            'activeziel' => $z,
        ]))->out(false),
    ];
}

// URLs used by the template.
$nexturl = new moodle_url('/mod/elediacheckin/view.php', [
    'id'         => $cm->id,
    'activeziel' => $activeziel,
    'r'          => time(), // cache-buster so the "Nächste Frage" link is always a new request.
]);
$popupurl = new moodle_url('/mod/elediacheckin/present.php', [
    'id'         => $cm->id,
    'activeziel' => $activeziel,
    'layout'     => 'popup',
]);

$templatecontext = [
    'cmid'            => $cm->id,
    'hasquestion'     => !empty($question),
    'question'        => $question ? [
        'frage'     => format_text($question->frage, FORMAT_HTML),
        'antwort'   => $question->antwort ? format_text($question->antwort, FORMAT_HTML) : '',
        'hasanswer' => (bool)$question->hasanswer,
        'lang'      => $question->lang,
    ] : null,
    'multiziel'       => $multiziel,
    'zielbuttons'     => $zielbuttons,
    'nextquestionurl' => $nexturl->out(false),
    'popupurl'        => $popupurl->out(false),
    'presenturl'      => $popupurl->out(false),
    'strnext'         => get_string('nextquestion', 'elediacheckin'),
    'strshowanswer'   => get_string('showanswer', 'elediacheckin'),
    'strnone'         => get_string('noquestions', 'elediacheckin'),
    'strpopup'        => get_string('openpopup', 'elediacheckin'),
    'strfullscreen'   => get_string('openfullscreen', 'elediacheckin'),
    'strclose'        => get_string('close', 'elediacheckin'),
];

echo $OUTPUT->header();

if (!empty($instance->intro)) {
    echo $OUTPUT->box(format_module_intro('elediacheckin', $instance, $cm->id),
        'generalbox mod_introbox', 'elediacheckinintro');
}

echo $OUTPUT->render_from_template('mod_elediacheckin/view', $templatecontext);

echo $OUTPUT->footer();
