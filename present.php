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
 * Presentation view for an elediacheckin activity (popup layout).
 *
 * Loaded via window.open() from view.php. Uses Moodle's 'popup' page layout
 * which strips navigation, blocks and footer, leaving a clean chrome-less
 * window suitable for screen-sharing in video calls.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id         = required_param('id', PARAM_INT);
$activeziel = optional_param('activeziel', '', PARAM_ALPHA);
// Same "lock onto a specific card" mechanism as view.php: the block
// launcher passes ?q=<externalid> so the popup shows exactly the card the
// user was looking at in the block preview. See view.php for rationale.
$qext       = optional_param('q', '', PARAM_ALPHANUMEXT);
$goback     = (bool) optional_param('prev', 0, PARAM_BOOL);
$isnext     = (bool) optional_param('next', 0, PARAM_BOOL);

$cm       = get_coursemodule_from_id('elediacheckin', $id, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$instance = $DB->get_record('elediacheckin', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = \core\context\module::instance($cm->id);
require_capability('mod/elediacheckin:view', $context);

// Resolve active ziel (same logic as view.php).
$ziele = array_values(array_filter(array_map('trim', explode(',', (string)$instance->ziele))));
if (empty($ziele)) {
    $ziele = ['checkin'];
}
$multiziel = count($ziele) > 1;
if (!$multiziel || !in_array($activeziel, $ziele, true)) {
    $activeziel = $ziele[0];
}

// Language resolution with graceful fallback (see view.php for rationale).
// Sentinels: '_auto_' → current user language, '_course_' → course language.
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

// Bundle-Pool + eigene Fragen via activity_pool (Konzept §10.13).
// resolve_navigation() kapselt das Pin-per-?q + One-Step-History für den
// „Zur vorherigen Frage"-Button. Wichtig: Popup und view.php teilen sich
// denselben $SESSION->elediacheckin_history[$cmid]-State, d. h. ein Back-
// Schritt im Popup wirkt auch zurück auf die embedded View und umgekehrt.
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

// PRG redirect: same pattern as view.php — prevent F5 from re-triggering
// next/prev navigation. Redirects to a clean URL with ?q=<externalid>.
if (($isnext || $goback) && $question && !empty($question->externalid)) {
    $cleanparams = [
        'id'     => $cm->id,
        'q'      => (string) $question->externalid,
        'layout' => 'popup',
    ];
    if ($multiziel) {
        $cleanparams['activeziel'] = $activeziel;
    }
    redirect(new moodle_url('/mod/elediacheckin/present.php', $cleanparams));
}

$zielbuttons = [];
foreach ($ziele as $z) {
    $zielbuttons[] = [
        'key'    => $z,
        'label'  => get_string_manager()->string_exists('ziel_' . $z, 'elediacheckin')
            ? get_string('ziel_' . $z, 'elediacheckin')
            : ucfirst($z),
        'active' => ($z === $activeziel),
        'url'    => (new moodle_url('/mod/elediacheckin/present.php', [
            'id'         => $cm->id,
            'activeziel' => $z,
            'layout'     => 'popup',
        ]))->out(false),
    ];
}

$nexturl = new moodle_url('/mod/elediacheckin/present.php', [
    'id'         => $cm->id,
    'activeziel' => $activeziel,
    'layout'     => 'popup',
    'next'       => 1,
    'r'          => time(),
]);
$prevurl = new moodle_url('/mod/elediacheckin/present.php', [
    'id'         => $cm->id,
    'activeziel' => $activeziel,
    'layout'     => 'popup',
    'prev'       => 1,
    'r'          => time(),
]);

$PAGE->set_url('/mod/elediacheckin/present.php', [
    'id'         => $cm->id,
    'activeziel' => $activeziel,
    'layout'     => 'popup',
]);
$PAGE->set_pagelayout('popup'); // Chrome-less Moodle layout.
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading('');
$PAGE->set_context($context);

// Suppress the activity header (title + description) that Moodle auto-
// renders at the top of every module page. Without this, the popup still
// shows the activity name with ~80 px of padding above the pill bar.
if (isset($PAGE->activityheader)) {
    $PAGE->activityheader->disable();
}
$PAGE->requires->js_call_amd('mod_elediacheckin/present', 'init', ['#mod-elediacheckin-present']);

$templatecontext = [
    'cmid'            => $cm->id,
    'externalid'      => $question && !empty($question->externalid) ? (string) $question->externalid : '',
    'hasquestion'     => !empty($question),
    'question'        => $question ? [
        // Own questions use FORMAT_PLAIN (teacher textarea input),
        // bundle questions FORMAT_HTML (trusted JSON content).
        'frage' => format_text(
            $question->frage,
            !empty($question->isown) ? FORMAT_PLAIN : FORMAT_HTML
        ),
        'antwort'   => $question->antwort ? format_text($question->antwort, FORMAT_HTML) : '',
        'hasanswer' => (bool)$question->hasanswer,
        // Autor-Attribution nur für Zitate (siehe view.php für Begründung).
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
    'strnext'         => get_string('nextquestion', 'elediacheckin'),
    'strprev'         => get_string('prevquestion', 'elediacheckin'),
    'strshowanswer'   => get_string('showanswer', 'elediacheckin'),
    'strnone'         => get_string('noquestions', 'elediacheckin'),
    'strclose'        => get_string('closebuttontitle'),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_elediacheckin/present', $templatecontext);
echo $OUTPUT->footer();
