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

        // Wrap the entire panel in a div with a stable id. Previous versions
        // Used this as an anchor for a DOM-reorder script that tried to move
        // The Moodle save button above the panel, but that was fragile and
        // Produced visible layout glitches (v2026040532—v2026040536).
        //
        // New approach (v2026040537): keep the panel in its natural position
        // At the bottom of the settings form, but inject a secondary
        // <button type="submit"> at the TOP of the panel. Because this
        // Button lives inside the same admin settings <form>, clicking it
        // Submits the form — no JS, no reorder, no race conditions. The
        // Original bottom save button stays where Moodle puts it, so users
        // Who scroll to the bottom still see the familiar Moodle flow.
        $out = '<div id="elediacheckin-dashboardpanel">';

        // ----- Companion-plugin health check. -----
        //
        // Block_elediacheckin is a separate plugin but tightly coupled to
        // This mod — without it, the frontpage/course-page launcher is
        // Missing. Johannes has twice now reported the block silently
        // Disappearing from the "Add block" dropdown (cache race after
        // Upgrades, or manual "hide" in Site admin → Plugins → Blocks).
        // The symptom is invisible until someone tries to add the block,
        // By which point diagnosis is painful. This small health strip
        // Surfaces the companion state on every visit to the settings
        // Page so the admin sees broken state immediately.
        $out .= self::render_block_health();

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
            $out .= '</div>';
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
        $out .= '</div>';

        return $out;
    }

    /**
     * Early-save strip rendered ABOVE the "Sync status" heading on the
     * admin settings page. Contains a native <button type="submit"> that
     * submits the parent admin settings form — no JS, no DOM reorder.
     *
     * Lives in its own admin_setting_heading so it appears above the
     * dashboard panel heading, matching Johannes' April 2026 UX feedback
     * ("Der Button Save Changes muss über Sync Status").
     *
     * @return string Safe HTML.
     */
    public static function render_save_bar(): string {
        $out  = \html_writer::start_div('alert alert-light border d-flex align-items-center justify-content-between mb-3');
        $out .= \html_writer::tag('span',
            get_string('dashboard_savehint', 'elediacheckin'),
            ['class' => 'text-muted me-3']);
        $out .= '<button type="submit" class="btn btn-primary" name="elediacheckin_earlysave">'
            . s(get_string('savechanges')) . '</button>';
        $out .= \html_writer::end_div();
        return $out;
    }

    /**
     * Renders a status strip reporting on block_elediacheckin health.
     *
     * @return string Safe HTML.
     */
    private static function render_block_health(): string {
        global $DB;

        $blockrec = $DB->get_record('block', ['name' => 'elediacheckin'],
            'id, name, visible');

        // Block missing from mdl_block entirely.
        if (!$blockrec) {
            $msg = get_string('blockhealth_missing', 'elediacheckin');
            $link = \html_writer::link(
                new \moodle_url('/admin/index.php'),
                get_string('blockhealth_missing_cta', 'elediacheckin'),
                ['class' => 'alert-link']
            );
            return \html_writer::div(
                '<strong>⚠ ' . s(get_string('blockhealth_title', 'elediacheckin'))
                    . '</strong> ' . s($msg) . ' ' . $link,
                'alert alert-danger py-2 mb-3'
            );
        }

        // Block installed but hidden from the add-block list.
        if ((int)$blockrec->visible !== 1) {
            $link = \html_writer::link(
                new \moodle_url('/admin/blocks.php'),
                get_string('blockhealth_hidden_cta', 'elediacheckin'),
                ['class' => 'alert-link']
            );
            return \html_writer::div(
                '<strong>⚠ ' . s(get_string('blockhealth_title', 'elediacheckin'))
                    . '</strong> ' . s(get_string('blockhealth_hidden', 'elediacheckin'))
                    . ' ' . $link,
                'alert alert-warning py-2 mb-3'
            );
        }

        // All good — short green confirmation line with version badge.
        $version = '';
        try {
            $pluginman = \core_plugin_manager::instance();
            $plugininfo = $pluginman->get_plugin_info('block_elediacheckin');
            if ($plugininfo && $plugininfo->versiondb) {
                $version = ' <span class="badge bg-success-subtle text-success-emphasis">v'
                    . s((string)$plugininfo->versiondb) . '</span>';
            }
        } catch (\Throwable $e) {
            // Core_plugin_manager should never fail here; if it does, we
            // Just skip the version badge and still show the green strip.
            $version = '';
        }
        return \html_writer::div(
            '<strong>✓ ' . s(get_string('blockhealth_title', 'elediacheckin'))
                . '</strong> ' . s(get_string('blockhealth_ok', 'elediacheckin')) . $version,
            'alert alert-success py-2 mb-3'
        );
    }

}
