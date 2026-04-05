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
 * Admin report: synchronisation log for mod_elediacheckin.
 *
 * Shows the most recent content sync runs with their source, bundle,
 * result, imported-questions count and a trimmed message column.
 * Supports a manual "run now" button that invokes sync_service::run('manual').
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use mod_elediacheckin\local\service\sync_service;

admin_externalpage_setup('mod_elediacheckin_synclog', '', null,
    new moodle_url('/mod/elediacheckin/admin/sync_log.php'));

require_capability('moodle/site:config', context_system::instance());

$action  = optional_param('action', '', PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$PAGE->set_title(get_string('synclog_title', 'elediacheckin'));
$PAGE->set_heading(get_string('synclog_title', 'elediacheckin'));

// Manual sync trigger.
if ($action === 'runsync' && confirm_sesskey()) {
    $service = new sync_service();
    $log = $service->run('manual');

    if ($log->result === 'success') {
        \core\notification::success(get_string('synclog_runsuccess', 'elediacheckin',
            (object)['count' => $log->questionsimported, 'bundle' => $log->bundleid]));
    } else {
        \core\notification::error(get_string('synclog_runfailed', 'elediacheckin',
            s($log->message)));
    }

    redirect(new moodle_url('/mod/elediacheckin/admin/sync_log.php'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('synclog_title', 'elediacheckin'));

// ---------------------------------------------------------------------------
// Summary + run-now button.
// ---------------------------------------------------------------------------
$activesource = get_config('mod_elediacheckin', 'contentsource') ?: 'bundled';
$activelabel  = get_string('contentsource_' . $activesource, 'elediacheckin');

echo html_writer::start_div('card mb-4');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('synclog_current', 'elediacheckin'),
    ['class' => 'card-title']);
echo html_writer::tag('p', get_string('synclog_activesource', 'elediacheckin', $activelabel));

$runurl = new moodle_url('/mod/elediacheckin/admin/sync_log.php', [
    'action'  => 'runsync',
    'sesskey' => sesskey(),
]);
echo html_writer::link($runurl, get_string('synclog_runnow', 'elediacheckin'),
    ['class' => 'btn btn-primary']);

echo html_writer::end_div();
echo html_writer::end_div();

// ---------------------------------------------------------------------------
// Log table.
// ---------------------------------------------------------------------------
$records = $DB->get_records('elediacheckin_sync_log', null, 'timestarted DESC', '*', 0, 100);

if (empty($records)) {
    echo html_writer::div(
        get_string('synclog_empty', 'elediacheckin'),
        'alert alert-info'
    );
} else {
    $table = new html_table();
    $table->attributes['class'] = 'table table-sm table-hover generaltable';
    $table->head = [
        get_string('date'),
        get_string('synclog_source', 'elediacheckin'),
        get_string('synclog_sourceid', 'elediacheckin'),
        get_string('synclog_bundle', 'elediacheckin'),
        get_string('synclog_result', 'elediacheckin'),
        get_string('synclog_count', 'elediacheckin'),
        get_string('synclog_message', 'elediacheckin'),
    ];

    foreach ($records as $row) {
        $resultbadge = $row->result === 'success'
            ? '<span class="badge bg-success">' . s($row->result) . '</span>'
            : '<span class="badge bg-danger">' . s($row->result) . '</span>';

        $bundlecell = $row->bundleid
            ? format_string($row->bundleid) . ' <small class="text-muted">'
                . s($row->bundleversion) . '</small>'
            : '<span class="text-muted">–</span>';

        $msg = (string)$row->message;
        if (core_text::strlen($msg) > 120) {
            $msg = core_text::substr($msg, 0, 117) . '…';
        }

        $table->data[] = [
            userdate($row->timestarted, get_string('strftimedatetimeshort', 'langconfig')),
            s($row->source),
            s((string)$row->sourceid),
            $bundlecell,
            $resultbadge,
            (int)$row->questionsimported,
            s($msg),
        ];
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();
