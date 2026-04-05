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
 * English language strings for mod_elediacheckin.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']         = 'Check-in';
$string['modulename']         = 'Check-in';
$string['modulenameplural']   = 'Check-ins';
$string['modulename_help']    = 'The Check-in activity displays didactic check-in and check-out questions sourced from an external, Git-managed content repository.';
$string['pluginadministration'] = 'Check-in administration';

// Form sections.
$string['checkinsettings']    = 'Check-in settings';
$string['displaysettings']    = 'Display options';

// Fields.
$string['mode']               = 'Mode';
$string['mode_help']          = 'Which question types this activity offers: check-in, check-out or both.';
$string['mode_both']          = 'Check-in and check-out';
$string['mode_checkin']       = 'Check-in only';
$string['mode_checkout']      = 'Check-out only';
$string['categories']         = 'Allowed categories';
$string['categories_help']    = 'Comma-separated list of category external ids. Leave empty to allow all.';
$string['contentlang']        = 'Content language';
$string['contentlang_help']   = 'Two-letter language code for the questions. Leave empty to use the course or user language.';
$string['randomstart']        = 'Show a random question on open';
$string['randomstart_help']   = 'If enabled, a random question from the pool is shown as soon as the activity is opened.';
$string['shownav']            = 'Show navigation arrows';
$string['showother']          = 'Show "Another question" button';
$string['showfilter']         = 'Show category filter';
$string['avoidrepeat']        = 'Avoid repeating the previous question';
$string['avoidrepeat_help']   = 'If enabled, the same question will not be shown twice in a row within a single view.';

// View page.
$string['newquestion']        = 'Another question';
$string['noquestions']        = 'No questions are currently available for this filter.';
$string['noinstances']        = 'There are no Check-in activities in this course.';

// Capabilities.
$string['elediacheckin:addinstance']   = 'Add a new Check-in activity';
$string['elediacheckin:view']          = 'View a Check-in activity';
$string['elediacheckin:manage']        = 'Manage Check-in activity settings';
$string['elediacheckin:synccontent']   = 'Trigger content synchronisation';

// Admin settings.
$string['repoheading']        = 'Content repository';
$string['repoheading_desc']   = 'Configure the external Git-based content source. Questions are pulled on a schedule and cached locally.';
$string['repourl']            = 'Repository URL';
$string['repourl_desc']       = 'HTTPS URL of the raw JSON artefact or release asset to pull.';
$string['reporef']            = 'Branch, tag or commit';
$string['reporef_desc']       = 'Git ref to pin the content to. Defaults to "main".';
$string['repotoken']          = 'Access token';
$string['repotoken_desc']     = 'Optional access token for private repositories. Stored encrypted where supported.';
$string['defaultlang']        = 'Default content language';
$string['defaultlang_desc']   = 'Language used when none is configured on the activity and no match is found for the user.';
$string['fallbacklang']       = 'Fallback content language';
$string['fallbacklang_desc']  = 'Last-resort language used when neither the configured nor the default language has matching questions.';

// Tasks.
$string['task_sync_content']  = 'Synchronise check-in questions from repository';

// Errors.
$string['syncerror_norepourl'] = 'No repository URL configured - aborting synchronisation.';

// Privacy.
$string['privacy:metadata']   = 'The Check-in activity does not store any personal data. Questions are displayed read-only and answers are not captured.';
