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
 * Sync-Status-Dashboard as a dedicated admin_externalpage.
 *
 * The plugin has two admin nodes:
 *   1. „Einstellungen"  — modsettingelediacheckin (auto-settingpage),
 *       where all configurable fields live. The form's Save-Changes button
 *       now sits directly under the last config field, not buried below
 *       a status panel.
 *   2. „Sync-Status"    — this page, rendered through dashboard_renderer.
 *       Status + „Jetzt synchronisieren"/„Verbindung testen"-Buttons.
 *
 * This layout restores the Save-button to its natural position (Johannes'
 * Feedback in testing-inbox, April 2026) while keeping the dashboard one
 * click away under the same plugin category.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('mod_elediacheckin_dashboard');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dashboard_heading', 'elediacheckin'));
echo \mod_elediacheckin\local\admin\dashboard_renderer::render();
echo $OUTPUT->footer();
