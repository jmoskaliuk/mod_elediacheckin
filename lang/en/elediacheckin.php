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
$string['ziele']              = 'Card types';
$string['ziele_help']         = 'Which card types this activity offers. Multiple selection allowed.';
$string['ziel_impuls']        = 'Impulse';
$string['ziel_checkin']       = 'Check-in';
$string['ziel_checkout']      = 'Check-out';
$string['ziel_retro']         = 'Retrospective';
$string['ziel_learning']      = 'Learning content';
$string['ziel_funfact']       = 'Fun fact';
$string['ziel_zitat']         = 'Quote';
$string['showanswer']         = 'Show answer';
$string['categories']         = 'Allowed categories';
$string['categories_help']    = 'Restrict this activity to the selected categories. Categories are listed under the ziel they belong to — pick one or many. Leave empty to allow all categories for the chosen ziele.';
$string['categories_all']     = 'All categories';
$string['ziele_all']          = 'All question types';
$string['contentlang']        = 'Content language';
$string['contentlang_help']   = 'Language of the questions shown in this activity. "User language" uses the language the learner is currently viewing Moodle in; "Course language" uses the course default. Pick a specific language to pin the activity to that bundle.';
$string['lang_auto']          = 'User language (recommended)';
$string['lang_course']        = 'Course language';
$string['avoidrepeat']        = 'Avoid repeating the previous question';
$string['avoidrepeat_help']   = 'If enabled, the same question will not be shown twice in a row within a single view.';

// Category labels — match CATEGORIES_BY_ZIEL in schema_validator.php.
$string['cat_kennenlernen']           = 'Getting to know each other';
$string['cat_eisbrecher']             = 'Icebreaker';
$string['cat_arbeitsmodus']           = 'Work mode';
$string['cat_fokus']                  = 'Focus';
$string['cat_persoenliche-entwicklung'] = 'Personal development';
$string['cat_stimmung']               = 'Mood';
$string['cat_energie']                = 'Energy';
$string['cat_beziehung']              = 'Relationship';
$string['cat_feedback']               = 'Feedback';
$string['cat_verbesserung']           = 'Improvement';
$string['cat_aktion']                 = 'Action';
$string['cat_ausblick']               = 'Outlook';
$string['cat_wertschaetzung']         = 'Appreciation';
$string['cat_was-lief-gut']           = 'What went well';
$string['cat_was-lief-schlecht']      = 'What went badly';
$string['cat_lernen']                 = 'Learning';
$string['cat_zusammenarbeit']         = 'Collaboration';
$string['cat_prozess']                = 'Process';
$string['cat_kreativitaet']           = 'Creativity';
$string['cat_perspektivwechsel']      = 'Change of perspective';
$string['cat_reflexion']              = 'Reflection';
$string['cat_entscheidung']           = 'Decision';
$string['cat_werte']                  = 'Values';
$string['cat_methode']                = 'Method';
$string['cat_theorie']                = 'Theory';
$string['cat_praxis']                 = 'Practice';
$string['cat_transfer']               = 'Transfer';
$string['cat_tool']                   = 'Tool';
$string['cat_modell']                 = 'Model';
$string['cat_wissenschaft']           = 'Science';
$string['cat_geschichte']             = 'History';
$string['cat_sprache']                = 'Language';
$string['cat_natur']                  = 'Nature';
$string['cat_technik']                = 'Technology';
$string['cat_alltag']                 = 'Everyday life';
$string['cat_fuehrung']               = 'Leadership';
$string['cat_motivation']             = 'Motivation';
$string['cat_wandel']                 = 'Change';
$string['cat_lebensweisheit']         = 'Wisdom';
$string['cat_humor']                  = 'Humour';

// View page.
$string['newquestion']        = 'Another question';
$string['nextquestion']       = 'Next question';
$string['openpopup']          = 'Open as popup';
$string['openfullscreen']     = 'Fullscreen';
$string['close']              = 'Close';
$string['noquestions']        = 'No questions are currently available for this filter.';
$string['noinstances']        = 'There are no Check-in activities in this course.';

// Capabilities.
$string['elediacheckin:addinstance']   = 'Add a new Check-in activity';
$string['elediacheckin:view']          = 'View a Check-in activity';
$string['elediacheckin:manage']        = 'Manage Check-in activity settings';
$string['elediacheckin:synccontent']   = 'Trigger content synchronisation';

// Admin settings.
$string['sourceheading']      = 'Content source';
$string['sourceheading_desc'] = 'Choose where the check-in questions come from. The bundled default is always available as a fallback.';
$string['contentsource']      = 'Active content source';
$string['contentsource_desc'] = 'Which source the scheduled synchronisation task uses. Change this and run the sync task to load a new bundle.';
$string['langheading']        = 'Language fallbacks';
$string['langheading_desc']   = 'Used when an activity does not pin a specific content language.';
$string['synclog_link']       = 'Synchronisation report';
$string['synclog_open']       = 'Open sync log';
$string['synclog_title']      = 'Check-in sync log';
$string['synclog_current']    = 'Current state';
$string['synclog_activesource'] = 'Active content source: <strong>{$a}</strong>';
$string['synclog_runnow']     = 'Run sync now';
$string['synclog_runsuccess'] = 'Sync completed: imported {$a->count} questions from bundle "{$a->bundle}".';
$string['synclog_runfailed']  = 'Sync failed: {$a}';
$string['synclog_empty']      = 'No sync runs have been recorded yet.';
$string['synclog_source']     = 'Trigger';
$string['synclog_sourceid']   = 'Source';
$string['synclog_bundle']     = 'Bundle';
$string['synclog_result']     = 'Result';
$string['synclog_count']      = 'Questions';
$string['synclog_message']    = 'Message';
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

// Content sources.
$string['contentsource_bundled']   = 'Bundled default questions';
$string['contentsource_git']       = 'Custom git repository';
$string['contentsource_eledia']    = 'eLeDia premium questions';

// Content-source error messages.
$string['contenterror_bundlemissing'] = 'The bundled default content was not found. Please check the plugin installation.';
$string['contenterror_bundleread']    = 'The bundled default content could not be read.';
$string['contenterror_bundleparse']   = 'The bundled default content contains invalid JSON.';
$string['contenterror_bundleinvalid'] = 'The bundled default content does not conform to the expected schema.';
$string['contenterror_gitnourl']   = 'No repository URL is configured for the Git content source.';
$string['contenterror_githttp']    = 'The configured repository URL could not be fetched over HTTPS.';
$string['contenterror_gitempty']   = 'The repository returned an empty response.';
$string['contenterror_gitparse']   = 'The repository response contains invalid JSON.';
$string['contenterror_gitinvalid'] = 'The repository bundle does not conform to the expected schema.';

// Privacy.
$string['privacy:metadata']   = 'The Check-in activity does not store any personal data. Questions are displayed read-only and answers are not captured.';
