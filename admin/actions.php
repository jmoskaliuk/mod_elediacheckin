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
 * Admin action handler for mod_elediacheckin.
 *
 * Handles "Sync jetzt ausführen" and "Verbindung testen" button clicks
 * from the plugin's merged admin page, posts a notification, and redirects
 * back to Site Admin → Plugins → Activity modules → Check-in.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use mod_elediacheckin\content\content_source_registry;
use mod_elediacheckin\local\service\sync_service;

require_login();
require_capability('moodle/site:config', context_system::instance());
require_sesskey();

$action = required_param('action', PARAM_ALPHA);

$returnurl = new moodle_url('/admin/settings.php',
    ['section' => 'modsettingelediacheckin']);

switch ($action) {
    case 'runsync':
        $service = new sync_service();
        $log = $service->run('manual');
        if ($log->result === 'success') {
            \core\notification::success(get_string('dashboard_runsuccess',
                'elediacheckin',
                (object)[
                    'count'  => $log->questionsimported,
                    'bundle' => $log->bundleid,
                ]));
        } else {
            \core\notification::error(get_string('dashboard_runfailed',
                'elediacheckin', s($log->message)));
        }
        break;

    case 'testconnection':
        $sourceid = get_config('mod_elediacheckin', 'contentsource') ?: 'bundled';
        $source = content_source_registry::get($sourceid)
            ?? content_source_registry::get_fallback();
        try {
            $ok = $source->test_connection();
            if ($ok) {
                \core\notification::success(get_string(
                    'dashboard_testconnection_ok',
                    'elediacheckin',
                    $source->get_display_name()));
            } else {
                \core\notification::error(get_string(
                    'dashboard_testconnection_fail',
                    'elediacheckin',
                    $source->get_display_name()));
            }
        } catch (\Throwable $e) {
            \core\notification::error(get_string(
                'dashboard_testconnection_error',
                'elediacheckin',
                s($e->getMessage())));
        }
        break;

    default:
        \core\notification::error('Unknown action: ' . s($action));
}

redirect($returnurl);
