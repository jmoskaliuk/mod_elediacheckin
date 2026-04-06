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
// Optional externalid of a specific question to lock onto on first load (used by block_elediacheckin).
// When the block preview shows question X and the user clicks "Open Check-in", we want view.php to.
// Show the same X instead of rolling a fresh random one. Empty means "random".
$qext       = optional_param('q', '', PARAM_ALPHANUMEXT);
// „Zur vorherigen Frage"-Button click: show the previously drawn card from the session history
// stack instead of a fresh random.
$goback     = (bool) optional_param('prev', 0, PARAM_BOOL);
// Explicit „Nächste Frage"-click: pushes on the history stack. Fresh page loads (without this
// flag) reset the stack so the back-button only appears once the user has actively moved forward.
$isnext     = (bool) optional_param('next', 0, PARAM_BOOL);

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

// Resolve active ziel: if instance has multiple ziele, honour query param, otherwise
// default to the first configured ziel. Invalid values fall back.
$ziele = array_values(array_filter(array_map('trim', explode(',', (string)$instance->ziele))));
if (empty($ziele)) {
    $ziele = ['checkin'];
}
$multiziel = count($ziele) > 1;
if (!$multiziel || !in_array($activeziel, $ziele, true)) {
    $activeziel = $ziele[0];
}

// Resolve a question through the service layer for the active ziel only.
// Language resolution tries: (1) the activity's configured lang, resolving the sentinels
// '_auto_' → current user language and '_course_' → course language; (2) the user's
// current language; (3) any language. This keeps the UX friendly on dev sites where the
// bundle language may not match the site language yet.
// Build language candidate chain. The sentinels '_auto_' → user language and '_course_' →
// course language are kept as string literals so this hot-path resolves without loading
// mod_form.php; the canonical definitions live in mod_form::LANG_AUTO / LANG_COURSE and
// must stay in sync.
$langcandidates = [];
$configured = (string) ($instance->contentlang ?? '');
if ($configured === '_auto_') {
    $langcandidates[] = current_language();
} else if ($configured === '_course_') {
    $langcandidates[] = !empty($course->lang) ? $course->lang : current_language();
} else if ($configured !== '') {
    $langcandidates[] = $configured;
}
$langcandidates[] = current_language();

// The activity_pool helper merges bundle questions (filtered via question_provider) with
// the teacher's per-activity own questions additively — see concept doc §10.13.
// `resolve_navigation()` wraps the pool draw with the single-step history stack that powers
// the „Zur vorherigen Frage"-Button.
$nav = \mod_elediacheckin\local\service\activity_pool::resolve_navigation(
    $instance,
    (int) $cm->id,
    $activeziel,
    $langcandidates,
    $qext,
    $goback,
    $isnext
);
$question  = $nav['question'];
$hasprev   = !empty($instance->showprevbutton) && !empty($nav['hasprev']);
$exhausted = !empty($nav['exhausted']);

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
    'next'       => 1, // Marks this as an explicit "next" click → pushes on history.
    'r'          => time(), // Cache-buster so the "Nächste Frage" link is always a new request.
]);
$prevurl = new moodle_url('/mod/elediacheckin/view.php', [
    'id'         => $cm->id,
    'activeziel' => $activeziel,
    'prev'       => 1,
    'r'          => time(),
]);
// Pin the popup to the same question the user is looking at so "Open as popup" does not
// Roll a fresh random card (issue #2/#3).
$popupparams = [
    'id'         => $cm->id,
    'activeziel' => $activeziel,
    'layout'     => 'popup',
];
if ($question && !empty($question->externalid)) {
    $popupparams['q'] = (string) $question->externalid;
}
$popupurl = new moodle_url('/mod/elediacheckin/present.php', $popupparams);

$templatecontext = [
    'cmid'            => $cm->id,
    'externalid'      => $question && !empty($question->externalid) ? (string) $question->externalid : '',
    'hasquestion'     => !empty($question),
    'question'        => $question ? [
        // Own questions come from a teacher-filled textarea and are rendered as plain text.
        // Bundle questions come from a trusted JSON bundle and may contain simple HTML.
        'frage' => format_text(
            $question->frage,
            !empty($question->isown) ? FORMAT_PLAIN : FORMAT_HTML
        ),
        'antwort'   => $question->antwort ? format_text($question->antwort, FORMAT_HTML) : '',
        'hasanswer' => (bool)$question->hasanswer,
        'lang'      => $question->lang,
        // Ziel-abhängige Metadaten für das Template: nur Zitate bekommen die Autor-Attribution
        // unter dem Text. Für andere Ziele ist das Feld zwar möglicherweise gesetzt
        // (z. B. "eLeDia Redaktion" bei learning-Fragen), gehört aber didaktisch NICHT auf die Karte.
        'isquote'   => !empty($question->ziel) && $question->ziel === 'zitat',
        'hasauthor' => !empty($question->ziel) && $question->ziel === 'zitat'
                        && !empty($question->author),
        'author'    => !empty($question->author) ? s($question->author) : '',
    ] : null,
    'multiziel'       => $multiziel,
    'zielbuttons'     => $zielbuttons,
    'nextquestionurl' => $nexturl->out(false),
    'prevquestionurl' => $prevurl->out(false),
    'hasprev'         => $hasprev,
    'exhausted'       => $exhausted,
    'strexhausted'    => get_string('exhaustedmessage', 'elediacheckin'),
    'popupurl'        => $popupurl->out(false),
    'presenturl'      => $popupurl->out(false),
    'strnext'         => get_string('nextquestion', 'elediacheckin'),
    'strprev'         => get_string('prevquestion', 'elediacheckin'),
    'strshowanswer'   => get_string('showanswer', 'elediacheckin'),
    'strnone'         => get_string('noquestions', 'elediacheckin'),
    'strpopup'        => get_string('openpopup', 'elediacheckin'),
    'strfullscreen'   => get_string('openfullscreen', 'elediacheckin'),
    'strclose'        => get_string('closebuttontitle'),
];

echo $OUTPUT->header();

// NOTE: In Moodle 4.x+ the activity header ($PAGE->activityheader) auto-renders the module
// intro, so an explicit generalbox here would duplicate the description. We intentionally do NOT
// echo a second intro box.

echo $OUTPUT->render_from_template('mod_elediacheckin/view', $templatecontext);

echo $OUTPUT->footer();
