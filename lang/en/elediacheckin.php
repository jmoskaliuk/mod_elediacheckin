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

$string['pluginname']         = 'eLeDia Check-In';
$string['modulename']         = 'Check-In';
$string['modulenameplural']   = 'Check-Ins';
$string['modulename_help']    = 'The "Check-in" activity shows short didactic impulses, questions and cards — for example check-in rounds at the beginning of a session, check-out reflections at the end, retrospective prompts, quotes or fun facts.

For each activity you can select which goals (check-in, check-out, retro, impulse, learning reflection, fun-fact, quote) and which categories are drawn from. Optionally, you can narrow down by audience (e.g. team, leaders) and context (work, school, higher education, personal). Participants then see a randomly matching card from the configured pool on every page load.

Content is synchronised centrally by the site administration from a content repository. Teachers only decide how the cards are used in their activity; they can additionally add their own questions per activity.

<a href="https://www.eledia.de/mod_elediacheckin" target="_blank" rel="noopener">Learn more at eledia.de</a>';
$string['pluginadministration'] = 'Check-in administration';

// Form sections.
$string['checkinsettings']    = 'Check-in settings';
$string['displaysettings']    = 'Display options';

// Fields.
$string['ziele']              = 'Goal';
$string['ziele_help']         = 'Which goal this activity serves (Impulse, Check-in, Retro …). Multiple selection allowed. Your choice restricts the categories offered below.';
$string['ziel_impuls']        = 'Impulse';
$string['ziel_checkin']       = 'Check-in';
$string['ziel_checkout']      = 'Check-out';
$string['ziel_retro']         = 'Retrospective';
$string['ziel_learning']      = 'Learning reflection';
$string['ziel_learning_help'] = 'Short, open prompts that invite learners to reflect on their own learning — e.g. "What is the most important thing I learned today?" or "What surprised me this week?". These are deliberately NOT knowledge questions with right/wrong answers, but personal reflection impulses without a back side.';
$string['ziel_funfact']       = 'Fun fact';
$string['ziel_zitat']         = 'Quote';
$string['showanswer']         = 'Show answer';
$string['categories']         = 'Categories';
$string['categories_help']    = 'Restrict the activity to specific categories. Only categories that belong to the goals selected above are shown. Leave empty to allow all categories of the selected goals.';
$string['categories_all']     = 'All categories';
$string['ziele_all']          = 'All goals';
$string['contentlang']        = 'Content language';
$string['contentlang_help']   = 'Language of the questions shown in this activity. "User language" uses the language the learner is currently viewing Moodle in; "Course language" uses the course default. Pick a specific language to pin the activity to that bundle.';
$string['lang_auto']          = 'User language (recommended)';
$string['lang_course']        = 'Course language';
$string['avoidrepeat']        = 'Avoid repeating the previous question';
$string['avoidrepeat_help']   = 'If enabled, the same question will not be shown twice in a row within a single view.';
$string['showprevbutton']     = 'Show "Previous question" button';
$string['showprevbutton_help'] = 'If enabled, a second button appears next to "Next question" that lets learners jump back one step — to the card that was shown just before. Only a single step back, not a full history.';
$string['prevquestion']       = 'Previous question';
$string['ownquestionsmode']   = 'Question pool for this activity';
$string['ownquestionsmode_help'] = 'Decides which questions are drawn in this activity. "Mixed" adds the own questions entered below to the bundle questions from the site content source with equal weight (default). "Only own questions" ignores the site bundle entirely — if the textarea is empty, no cards are shown. "No own questions" ignores the textarea even if it is filled — useful to temporarily take an activity out of the own-questions pool without deleting the entries.';
$string['ownquestionsmode_mixed']   = 'Mixed: own questions in addition to bundle questions';
$string['ownquestionsmode_onlyown'] = 'Only own questions (bundle is ignored)';
$string['ownquestionsmode_none']    = 'No own questions (bundle only)';

// Audience + context (optional tag dimensions).
$string['zielgruppe']         = 'Audience';
$string['zielgruppe_help']    = 'Optional filter on an audience. Questions without an audience tag are considered general-purpose and always displayed. Empty = no restriction.';
$string['zielgruppe_all']     = 'All audiences';
$string['zielgruppe_fuehrungskraefte'] = 'Leaders / managers';
$string['zielgruppe_team']    = 'Team';
$string['zielgruppe_grundschule'] = 'Primary school';
$string['kontext']            = 'Context';
$string['kontext_help']       = 'Optional filter on a usage context. Questions without a context tag are considered general-purpose and always displayed. Empty = no restriction.';
$string['kontext_all']        = 'All contexts';
$string['kontext_arbeit']     = 'Work';
$string['kontext_schule']     = 'School';
$string['kontext_hochschule'] = 'Higher education';
$string['kontext_privat']     = 'Personal';

// Own questions (§10.13).
$string['ownquestions']       = 'Own questions';
$string['ownquestions_help']  = 'Extra questions that appear only in this activity. One question per line. Empty lines are ignored. These questions are mixed additively into the bundle pool (equal draw probability), not as a replacement. They apply to all goals of the activity and have no back side. Leave empty to use bundle content only.';

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
// Categories for learning reflection (ziel: learning) — open reflection
// prompts, not knowledge categories. The old didactic labels (method,
// theory, model, tool) are intentionally removed so the goal "learning
// reflection" can no longer be confused with a lecture.
$string['cat_tagesreflexion']         = 'Daily reflection';
$string['cat_transfer']               = 'Transfer to practice';
$string['cat_aha']                    = 'Aha moment';
$string['cat_hindernis']              = 'Obstacle & misunderstanding';
$string['cat_meta']                   = 'Learning about learning';
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
$string['adminintro_heading'] = 'Getting started';
$string['adminintro_desc']    = '<div class="alert alert-info mb-3"><strong>Quick guide for administrators</strong><ol class="mb-0"><li>Choose a <em>content source</em> below. The bundled default works out of the box — no configuration needed.</li><li>To use your own questions, pick <em>Custom git repository</em>. A ready-to-fork example lives at <a href="https://github.com/jmoskaliuk/content_elediacheckin" target="_blank">github.com/jmoskaliuk/content_elediacheckin</a> — fork the repo, adjust <code>bundle.json</code> in the root, and paste the <em>raw URL</em> of your copy into the field below (format: <code>https://raw.githubusercontent.com/&lt;user&gt;/content_elediacheckin/main/bundle.json</code>).</li><li>Set the <em>language fallbacks</em> that apply when an activity does not pin a specific language.</li><li>Save changes. Then open the <em>Sync status</em> nav entry and click <em>Run sync now</em> to load the bundle.</li></ol></div>';
$string['sourceheading']      = 'Content source';
$string['sourceheading_desc'] = 'Choose where the check-in questions come from. The bundled default is always available as a fallback.';
$string['contentsource']      = 'Active content source';
$string['contentsource_desc'] = 'Which source the scheduled synchronisation task uses. Change this and run the sync task to load a new bundle.';
$string['langheading']        = 'Language fallbacks';
$string['langheading_desc']   = 'Used when an activity does not pin a specific content language.';
$string['dashboard_heading']     = 'Sync status';
$string['dashboard_heading_desc'] = 'Current state of the active content source, manual actions and most recent sync runs at a glance.';
$string['dashboard_current']     = 'Current state';
$string['dashboard_activesource'] = 'Active content source: <strong>{$a}</strong>';
$string['dashboard_runnow']      = 'Run sync now';
$string['dashboard_testconnection'] = 'Test connection';
$string['dashboard_testconnection_ok']    = 'Connection test succeeded ({$a}).';
$string['dashboard_testconnection_fail']  = 'Connection test failed ({$a}).';
$string['dashboard_testconnection_error'] = 'Connection test aborted with error: {$a}';
$string['dashboard_viewlog']     = 'View sync log & history';
$string['dashboard_saveFirstHint'] = 'After changing any configuration, click "Save changes" first, then trigger the sync.';
$string['dashboard_runsuccess']  = 'Sync completed: imported {$a->count} questions from bundle "{$a->bundle}".';
$string['dashboard_runfailed']   = 'Sync failed: {$a}';
$string['dashboard_recent']      = 'Recent sync runs';
$string['synclog_empty']      = 'No sync runs have been recorded yet.';
$string['synclog_source']     = 'Trigger';
$string['synclog_sourceid']   = 'Source';
$string['synclog_bundle']     = 'Bundle';
$string['synclog_result']     = 'Result';
$string['synclog_count']      = 'Questions';
$string['synclog_message']    = 'Message';
$string['repoheading']        = 'Content repository';
$string['repoheading_desc']   = 'Configure the external Git-based content source. The bundle JSON is pulled on a schedule and cached locally. A ready-to-use example lives at <a href="https://github.com/jmoskaliuk/content_elediacheckin" target="_blank">github.com/jmoskaliuk/content_elediacheckin</a> — fork the repo, adjust <code>bundle.json</code> in the root, and paste the raw URL of your copy below.';
$string['repourl']            = 'Repository URL';
$string['repourl_desc']       = 'HTTPS URL of the raw JSON bundle (e.g. <code>https://raw.githubusercontent.com/&lt;user&gt;/content_elediacheckin/main/bundle.json</code>). <strong>Not</strong> a <code>.git</code> clone URL — the file is fetched directly over HTTPS.';
$string['repourl_example']    = 'Example: <code>https://raw.githubusercontent.com/jmoskaliuk/content_elediacheckin/main/bundle.json</code>';
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

// eLeDia premium settings.
$string['premiumheading']      = 'eLeDia Premium (license server)';
$string['premiumheading_desc'] = 'Curated, signed premium questions delivered through the eLeDia license server. Every bundle is verified against an ED25519 public key baked into the plugin code before import — a compromised server cannot inject forged content.';
$string['licenseserverurl']      = 'License server URL';
$string['licenseserverurl_desc'] = 'Base URL of the eLeDia license server, no trailing slash. Production: <code>https://licenses.eledia.de</code>. For local tests the MVP server at <code>/license_server/</code> typically runs on e.g. <code>http://host.docker.internal:8787</code>.';
$string['licensekey']            = 'License key';
$string['licensekey_desc']       = 'The UUID issued on purchase. Sent to the license server on every sync; the server checks validity, expiry and the <code>max_installs</code> limit.';

// Content-source error messages.
$string['contenterror_bundlemissing'] = 'The bundled default content was not found. Please check the plugin installation.';
$string['contenterror_bundleread']    = 'The bundled default content could not be read.';
$string['contenterror_bundleparse']   = 'The bundled default content contains invalid JSON.';
$string['contenterror_bundleinvalid'] = 'The bundled default content does not conform to the expected schema.';
$string['contenterror_eledia_nourl']     = 'No license server URL is configured for eLeDia Premium.';
$string['contenterror_eledia_nokey']     = 'No license key is configured for eLeDia Premium.';
$string['contenterror_eledia_http']      = 'The license server is unreachable or returned an error.';
$string['contenterror_eledia_rejected']  = 'The license server rejected the provided key (invalid, expired or max_installs reached).';
$string['contenterror_eledia_parse']     = 'The license server response is not valid JSON.';
$string['contenterror_eledia_sigmalformed'] = 'The premium bundle signature is malformed.';
$string['contenterror_eledia_sigfailed']    = 'ED25519 signature verification for the premium bundle failed against the hardcoded public key. Import aborted.';
$string['contenterror_eledia_schema']       = 'The premium bundle does not conform to the expected schema.';
$string['contenterror_gitnourl']   = 'No repository URL is configured for the Git content source.';
$string['contenterror_githttp']    = 'The configured repository URL could not be fetched over HTTPS.';
$string['contenterror_gitempty']   = 'The repository returned an empty response.';
$string['contenterror_gitparse']   = 'The repository response contains invalid JSON.';
$string['contenterror_gitinvalid'] = 'The repository bundle does not conform to the expected schema.';

// Privacy.
$string['privacy:metadata']   = 'The Check-in activity does not store any personal data. Questions are displayed read-only and answers are not captured.';
