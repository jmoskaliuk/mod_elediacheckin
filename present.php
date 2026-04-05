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
$provider = new \mod_elediacheckin\local\service\question_provider();
$langcandidates = [];
if (!empty($instance->contentlang)) {
    $langcandidates[] = $instance->contentlang;
}
$langcandidates[] = current_language();
$langcandidates[] = null;
$question = null;
foreach (array_unique($langcandidates, SORT_REGULAR) as $lang) {
    $question = $provider->get_random_question([
        'ziele'      => [$activeziel],
        'categories' => $instance->categories,
        'lang'       => $lang,
    ]);
    if ($question) {
        break;
    }
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
$PAGE->requires->js_call_amd('mod_elediacheckin/present', 'init', ['#mod-elediacheckin-present']);

$templatecontext = [
    'cmid'            => $cm->id,
    'hasquestion'     => !empty($question),
    'question'        => $question ? [
        'frage'     => format_text($question->frage, FORMAT_HTML),
        'antwort'   => $question->antwort ? format_text($question->antwort, FORMAT_HTML) : '',
        'hasanswer' => (bool)$question->hasanswer,
    ] : null,
    'multiziel'       => $multiziel,
    'zielbuttons'     => $zielbuttons,
    'nextquestionurl' => $nexturl->out(false),
    'strnext'         => get_string('nextquestion', 'elediacheckin'),
    'strshowanswer'   => get_string('showanswer', 'elediacheckin'),
    'strnone'         => get_string('noquestions', 'elediacheckin'),
    'strclose'        => get_string('close', 'elediacheckin'),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_elediacheckin/present', $templatecontext);
echo $OUTPUT->footer();
