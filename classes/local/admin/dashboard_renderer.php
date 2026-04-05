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

namespace mod_elediacheckin\local\admin;

/**
 * Renders the sync-status panel embedded in the main plugin admin page.
 *
 * Produces a self-contained HTML string (no templates, no $OUTPUT calls)
 * so it can be fed into an admin_setting_description — which is how we
 * merge the former stand-alone "Sync-Log"-externalpage into the regular
 * Site Admin → Plugins → Activity modules → Check-in page.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_renderer {

    /** Max log rows shown inside the embedded panel. */
    private const LIMIT = 15;

    /**
     * @return string HTML, safe for echoing inside an admin settings page.
     */
    public static function render(): string {
        global $DB;

        $out = '';

        // ----- Summary card with action buttons. -----
        $activesource = get_config('mod_elediacheckin', 'contentsource') ?: 'bundled';
        $sourcekey    = 'contentsource_' . $activesource;
        $activelabel  = get_string_manager()->string_exists($sourcekey, 'elediacheckin')
            ? get_string($sourcekey, 'elediacheckin')
            : $activesource;

        $runurl = new \moodle_url('/mod/elediacheckin/admin/actions.php', [
            'action'  => 'runsync',
            'sesskey' => sesskey(),
        ]);
        $testurl = new \moodle_url('/mod/elediacheckin/admin/actions.php', [
            'action'  => 'testconnection',
            'sesskey' => sesskey(),
        ]);

        $out .= \html_writer::start_div('card mb-3');
        $out .= \html_writer::start_div('card-body');
        $out .= \html_writer::tag('h5',
            get_string('dashboard_current', 'elediacheckin'),
            ['class' => 'card-title']);
        $out .= \html_writer::tag('p',
            get_string('dashboard_activesource', 'elediacheckin', $activelabel));

        $out .= \html_writer::start_div('d-flex gap-2 flex-wrap');
        $out .= \html_writer::link($runurl,
            get_string('dashboard_runnow', 'elediacheckin'),
            ['class' => 'btn btn-primary btn-sm']);
        if ($activesource === 'git') {
            $out .= \html_writer::link($testurl,
                get_string('dashboard_testconnection', 'elediacheckin'),
                ['class' => 'btn btn-outline-secondary btn-sm']);
        }
        $out .= \html_writer::end_div();

        $out .= \html_writer::end_div();
        $out .= \html_writer::end_div();

        // ----- Recent log entries. -----
        $records = $DB->get_records('elediacheckin_sync_log', null,
            'timestarted DESC', '*', 0, self::LIMIT);

        $out .= \html_writer::tag('h5',
            get_string('dashboard_recent', 'elediacheckin'),
            ['class' => 'mt-4']);

        if (empty($records)) {
            $out .= \html_writer::div(
                get_string('synclog_empty', 'elediacheckin'),
                'alert alert-info'
            );
            return $out;
        }

        $table = new \html_table();
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
            $badgeclass = $row->result === 'success' ? 'bg-success' : 'bg-danger';
            $resultbadge = \html_writer::tag('span', s($row->result),
                ['class' => 'badge ' . $badgeclass]);

            if ($row->bundleid) {
                $bundlecell = format_string($row->bundleid)
                    . ' ' . \html_writer::tag('small',
                        s((string)$row->bundleversion),
                        ['class' => 'text-muted']);
            } else {
                $bundlecell = \html_writer::tag('span', '–',
                    ['class' => 'text-muted']);
            }

            $msg = (string)$row->message;
            if (\core_text::strlen($msg) > 120) {
                $msg = \core_text::substr($msg, 0, 117) . '…';
            }

            $table->data[] = [
                userdate($row->timestarted,
                    get_string('strftimedatetimeshort', 'langconfig')),
                s($row->source),
                s((string)$row->sourceid),
                $bundlecell,
                $resultbadge,
                (int)$row->questionsimported,
                s($msg),
            ];
        }

        $out .= \html_writer::table($table);

        return $out;
    }
}
