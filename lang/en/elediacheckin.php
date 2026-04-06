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

$string['activitytour_description']   = 'Guide for teachers through the Check-in-specific settings of a course activity.';
$string['activitytour_endlabel']      = 'Got it';
$string['activitytour_name']          = 'Check-In activity settings';
$string['activitytour_step1_content'] = 'Welcome! I\'ll walk you through the Check-in–specific fields you are about to configure. Everything else on this page (name, intro text, course module availability) works like any other Moodle activity.';
$string['activitytour_step1_title']   = 'Configure a Check-in activity';
$string['activitytour_step2_content'] = 'All fields that decide which cards your learners will see live in this section. Everything above is standard Moodle activity metadata.';
$string['activitytour_step2_title']   = 'Check-in settings';
$string['activitytour_step3_content'] = 'Pick one or more goals the activity should draw from — e.g. check-in at the start of a session, retrospective at the end, or a learning-reflection prompt. You can combine several goals; your learners will then see a mix.';
$string['activitytour_step3_title']   = 'Goals (ziele)';
$string['activitytour_step4_content'] = 'Narrow down the pool further by picking categories (e.g. mood, energy, focus). The list updates automatically based on the goals you selected above. Leave empty to allow all categories of the chosen goals.';
$string['activitytour_step4_title']   = 'Categories';
$string['activitytour_step5_content'] = 'Optionally restrict the activity to a specific audience — e.g. leaders, primary school. Cards without an audience tag are general-purpose and always drawn. Leave as "All audiences" if the activity should work for everybody.';
$string['activitytour_step5_title']   = 'Audience (optional)';
$string['activitytour_step6_content'] = 'Pick how your personal prompts interact with the site bundle: <strong>Mixed</strong> merges your prompts into the pool (default), <strong>Only own</strong> ignores the bundle completely, <strong>No own</strong> temporarily sets your textarea aside. The textarea appears just below once you have chosen a mode that uses it.';
$string['activitytour_step6_title']   = 'Own questions';
$string['activitytour_step7_content'] = 'Save to create the activity. You can always come back and tweak the settings — learners will pick up the new configuration on their next visit.';
$string['activitytour_step7_title']   = 'Save and go';
$string['adminintro_desc']    = '<div class="alert alert-info mb-3"><strong>Quick guide for administrators</strong><ol class="mb-0"><li>Choose a <em>content source</em> below. The bundled default works out of the box — no configuration needed.</li><li>To use your own questions, pick <em>Custom git repository</em>. A ready-to-fork example lives at <a href="https://github.com/jmoskaliuk/content_elediacheckin" target="_blank">github.com/jmoskaliuk/content_elediacheckin</a> — fork the repo, adjust <code>bundle.json</code> in the root, and paste the <em>raw URL</em> of your copy into the field below (format: <code>https://raw.githubusercontent.com/&lt;user&gt;/content_elediacheckin/main/bundle.json</code>).</li><li>Set the <em>language fallbacks</em> that apply when an activity does not pin a specific language.</li><li>Save changes. Then open the <em>Sync status</em> nav entry and click <em>Run sync now</em> to load the bundle.</li></ol></div>';
$string['adminintro_heading'] = 'Getting started';
$string['avoidrepeat']        = 'Avoid repeating the previous question';
$string['avoidrepeat_help']   = 'If enabled, the same question will not be shown twice in a row within a single view.';
$string['blockhealth_hidden']       = 'is installed but hidden in "Manage blocks" — teachers cannot add it to their pages.';
$string['blockhealth_hidden_cta']   = 'Make it visible →';
$string['blockhealth_missing']      = 'is not installed. Without it, course and front pages are missing the Check-in activity launcher.';
$string['blockhealth_missing_cta']  = 'Go to admin notifications →';
$string['blockhealth_ok']           = 'installed and available.';
$string['blockhealth_title']        = 'Companion plugin "Check-In block":';
$string['cat_aha']                    = 'Aha moment';
$string['cat_aktion']                 = 'Action';
$string['cat_alltag']                 = 'Everyday life';
$string['cat_arbeitsmodus']           = 'Work mode';
$string['cat_ausblick']               = 'Outlook';
$string['cat_beziehung']              = 'Relationship';
$string['cat_eisbrecher']             = 'Icebreaker';
$string['cat_energie']                = 'Energy';
$string['cat_entscheidung']           = 'Decision';
$string['cat_feedback']               = 'Feedback';
$string['cat_fokus']                  = 'Focus';
$string['cat_fuehrung']               = 'Leadership';
$string['cat_geschichte']             = 'History';
$string['cat_hindernis']              = 'Obstacle & misunderstanding';
$string['cat_humor']                  = 'Humour';
$string['cat_kennenlernen']           = 'Getting to know each other';
$string['cat_kreativitaet']           = 'Creativity';
$string['cat_lebensweisheit']         = 'Wisdom';
$string['cat_lernen']                 = 'Learning';
$string['cat_meta']                   = 'Learning about learning';
$string['cat_motivation']             = 'Motivation';
$string['cat_natur']                  = 'Nature';
$string['cat_persoenliche-entwicklung'] = 'Personal development';
$string['cat_perspektivwechsel']      = 'Change of perspective';
$string['cat_prozess']                = 'Process';
$string['cat_reflexion']              = 'Reflection';
$string['cat_sprache']                = 'Language';
$string['cat_stimmung']               = 'Mood';
$string['cat_tagesreflexion']         = 'Daily reflection';
$string['cat_technik']                = 'Technology';
$string['cat_transfer']               = 'Transfer to practice';
$string['cat_verbesserung']           = 'Improvement';
$string['cat_wandel']                 = 'Change';
$string['cat_was-lief-gut']           = 'What went well';
$string['cat_was-lief-schlecht']      = 'What went badly';
$string['cat_werte']                  = 'Values';
$string['cat_wertschaetzung']         = 'Appreciation';
$string['cat_wissenschaft']           = 'Science';
$string['cat_zusammenarbeit']         = 'Collaboration';
$string['categories']         = 'Categories';
$string['categories_all']     = 'All categories';
$string['categories_help']    = 'Restrict the activity to specific categories. Only categories that belong to the goals selected above are shown. Leave empty to allow all categories of the selected goals.';
$string['checkinsettings']    = 'Check-in settings';
$string['checkintour_description']   = 'A short tour through the Check-in activity: the card, the aim picker, the next-question button, and presentation mode.';
$string['checkintour_endlabel']      = 'Got it';
$string['checkintour_name']          = 'Check-in for teachers';
$string['checkintour_step1_content'] = 'This activity shows your learners short prompts, reflection questions, or quotes — perfect for the beginning, the end, or a retrospective of a session.';
$string['checkintour_step1_title']   = 'Welcome to Check-in';
$string['checkintour_step2_content'] = 'Every page load shows a random card from the configured pool. Content is pulled centrally from the eLeDia content repository, and you can add your own questions per activity.';
$string['checkintour_step2_title']   = 'The card';
$string['checkintour_step3_content'] = 'If your activity combines several aims (e.g. check-in and check-out), you can switch between them here.';
$string['checkintour_step3_title']   = 'Switch aim';
$string['checkintour_step4_content'] = 'Click here to pull a new random card from the pool. No card is drawn twice in a row.';
$string['checkintour_step4_title']   = 'Next question';
$string['checkintour_step5_content'] = 'For presentations via screen share: fullscreen (large on the whole screen) or popup (in its own lean window). Both work perfectly well in video calls.';
$string['checkintour_step5_title']   = 'Fullscreen & popup';
$string['contenterror_bundleinvalid'] = 'The bundled default content does not conform to the expected schema.';
$string['contenterror_bundlemissing'] = 'The bundled default content was not found. Please check the plugin installation.';
$string['contenterror_bundleparse']   = 'The bundled default content contains invalid JSON.';
$string['contenterror_bundleread']    = 'The bundled default content could not be read.';
$string['contenterror_eledia_http']      = 'The license server is unreachable or returned an error.';
$string['contenterror_eledia_nokey']     = 'No license key is configured for eLeDia Premium.';
$string['contenterror_eledia_nourl']     = 'No license server URL is configured for eLeDia Premium.';
$string['contenterror_eledia_parse']     = 'The license server response is not valid JSON.';
$string['contenterror_eledia_rejected']  = 'The license server rejected the provided key (invalid, expired or max_installs reached).';
$string['contenterror_eledia_schema']       = 'The premium bundle does not conform to the expected schema.';
$string['contenterror_eledia_sigfailed']    = 'ED25519 signature verification for the premium bundle failed against the hardcoded public key. Import aborted.';
$string['contenterror_eledia_sigmalformed'] = 'The premium bundle signature is malformed.';
$string['contenterror_gitempty']   = 'The repository returned an empty response.';
$string['contenterror_githttp']    = 'The configured repository URL could not be fetched over HTTPS.';
$string['contenterror_gitinvalid'] = 'The repository bundle does not conform to the expected schema.';
$string['contenterror_gitnourl']   = 'No repository URL is configured for the Git content source.';
$string['contenterror_gitparse']   = 'The repository response contains invalid JSON.';
$string['contentlang']        = 'Content language';
$string['contentlang_help']   = 'Language of the questions shown in this activity. "User language" uses the language the learner is currently viewing Moodle in; "Course language" uses the course default. Pick a specific language to pin the activity to that bundle.';
$string['contentsource']      = 'Active content source';
$string['contentsource_bundled']   = 'Bundled default questions';
$string['contentsource_desc'] = 'Which source the scheduled synchronisation task uses. Change this and run the sync task to load a new bundle.';
$string['contentsource_eledia']    = 'eLeDia premium questions';
$string['contentsource_git']       = 'Custom git repository';
$string['dashboard_activesource'] = 'Active content source: <strong>{$a}</strong>';
$string['dashboard_current']     = 'Current state';
$string['dashboard_heading']     = 'Sync status';
$string['dashboard_heading_desc'] = 'Current state of the active content source, manual actions and most recent sync runs at a glance.';
$string['dashboard_recent']      = 'Recent sync runs';
$string['dashboard_runfailed']   = 'Sync failed: {$a}';
$string['dashboard_runnow']      = 'Run sync now';
$string['dashboard_runsuccess']  = 'Sync completed: imported {$a->count} questions from bundle "{$a->bundle}".';
$string['dashboard_saveFirstHint'] = 'After changing any configuration, click "Save changes" first, then trigger the sync.';
$string['dashboard_savehint']    = 'Click to save all settings on this page.';
$string['dashboard_testconnection'] = 'Test connection';
$string['dashboard_testconnection_error'] = 'Connection test aborted with error: {$a}';
$string['dashboard_testconnection_fail']  = 'Connection test failed ({$a}).';
$string['dashboard_testconnection_ok']    = 'Connection test succeeded ({$a}).';
$string['dashboard_viewlog']     = 'View sync log & history';
$string['defaultlang']        = 'Default content language';
$string['defaultlang_desc']   = 'Language used when none is configured on the activity and no match is found for the user.';
$string['displaysettings']    = 'Display options';
$string['elediacheckin:addinstance']   = 'Add a new Check-in activity';
$string['elediacheckin:manage']        = 'Manage Check-in activity settings';
$string['elediacheckin:synccontent']   = 'Trigger content synchronisation';
$string['elediacheckin:view']          = 'View a Check-in activity';
$string['exhaustedbehavior']       = 'When every question has been seen';
$string['exhaustedbehavior_empty']   = 'Show an empty "all done" card';
$string['exhaustedbehavior_help']  = 'What should happen when the learner has worked through every question in the pool in this session? "Start over" silently resets the seen-tracker and keeps showing cards (recommended for small pools and short visits). "Show empty card" stops drawing and displays a placeholder message instead.';
$string['exhaustedbehavior_restart'] = 'Start over from the beginning';
$string['exhaustedmessage']   = 'You have seen every question in this pool for now. Come back later — or ask your teacher to add more.';
$string['fallbacklang']       = 'Fallback content language';
$string['fallbacklang_desc']  = 'Last-resort language used when neither the configured nor the default language has matching questions.';
$string['kontext']            = 'Context';
$string['kontext_all']        = 'All contexts';
$string['kontext_arbeit']     = 'Work';
$string['kontext_help']       = 'Optional filter on a usage context. Questions without a context tag are considered general-purpose and always displayed. Empty = no restriction.';
$string['kontext_hochschule'] = 'Higher education';
$string['kontext_privat']     = 'Personal';
$string['kontext_schule']     = 'School';
$string['lang_auto']          = 'User language (recommended)';
$string['lang_course']        = 'Course language';
$string['langheading']        = 'Language fallbacks';
$string['langheading_desc']   = 'Used when an activity does not pin a specific content language.';
$string['licensekey']            = 'License key';
$string['licensekey_desc']       = 'The UUID issued on purchase. Sent to the license server on every sync; the server checks validity, expiry and the <code>max_installs</code> limit.';
$string['licenseserverurl']      = 'License server URL';
$string['licenseserverurl_desc'] = 'Base URL of the eLeDia license server, no trailing slash. Production: <code>https://licenses.eledia.de</code>. For local tests the MVP server at <code>/license_server/</code> typically runs on e.g. <code>http://host.docker.internal:8787</code>.';
$string['modulename']         = 'Check-In';
$string['modulename_help']    = 'The "Check-in" activity shows short didactic impulses, questions and cards — for example check-in rounds at the beginning of a session, check-out reflections at the end, retrospective prompts, quotes or fun facts.
$string['modulenameplural']   = 'Check-Ins';
$string['newquestion']        = 'Another question';
$string['nextquestion']       = 'Next';
$string['noinstances']        = 'There are no Check-in activities in this course.';
$string['noquestions']        = 'No questions are currently available for this filter.';
$string['openfullscreen']     = 'Fullscreen';
$string['openpopup']          = 'Open as popup';
$string['ownquestions']       = 'Own questions';
$string['ownquestions_help']  = 'Extra questions that appear only in this activity. One question per line. Empty lines are ignored. These questions are mixed additively into the bundle pool (equal draw probability), not as a replacement. They apply to all goals of the activity and have no back side. Leave empty to use bundle content only.';
$string['ownquestionsmode']   = 'Question pool for this activity';
$string['ownquestionsmode_help'] = 'Decides which questions are drawn in this activity. "Mixed" adds the own questions entered below to the bundle questions from the site content source with equal weight (default). "Only own questions" ignores the site bundle entirely — if the textarea is empty, no cards are shown. "No own questions" ignores the textarea even if it is filled — useful to temporarily take an activity out of the own-questions pool without deleting the entries.';
$string['ownquestionsmode_mixed']   = 'Mixed: own questions in addition to bundle questions';
$string['ownquestionsmode_none']    = 'No own questions (bundle only)';
$string['ownquestionsmode_onlyown'] = 'Only own questions (bundle is ignored)';
$string['pluginadministration'] = 'Check-in administration';
$string['pluginname']         = 'eLeDia Check-In';
$string['premiumheading']      = 'eLeDia Premium (license server)';
$string['premiumheading_desc'] = 'Curated, signed premium questions delivered through the eLeDia license server. Every bundle is verified against an ED25519 public key baked into the plugin code before import — a compromised server cannot inject forged content.';
$string['prevquestion']       = 'Previous';
$string['privacy:metadata']   = 'The Check-in activity does not store any personal data. Questions are displayed read-only and answers are not captured.';
$string['repoheading']        = 'Content repository';
$string['repoheading_desc']   = 'Configure the external Git-based content source. The bundle JSON is pulled on a schedule and cached locally. A ready-to-use example lives at <a href="https://github.com/jmoskaliuk/content_elediacheckin" target="_blank">github.com/jmoskaliuk/content_elediacheckin</a> — fork the repo, adjust <code>bundle.json</code> in the root, and paste the raw URL of your copy below.';
$string['reporef']            = 'Branch, tag or commit';
$string['reporef_desc']       = 'Git ref to pin the content to. Defaults to "main".';
$string['repotoken']          = 'Access token';
$string['repotoken_desc']     = 'Optional access token for private repositories. Stored encrypted where supported.';
$string['repourl']            = 'Repository URL';
$string['repourl_desc']       = 'HTTPS URL of the raw JSON bundle (e.g. <code>https://raw.githubusercontent.com/&lt;user&gt;/content_elediacheckin/main/bundle.json</code>). <strong>Not</strong> a <code>.git</code> clone URL — the file is fetched directly over HTTPS.';
$string['repourl_example']    = 'Example: <code>https://raw.githubusercontent.com/jmoskaliuk/content_elediacheckin/main/bundle.json</code>';
$string['settingstour_description']   = 'A quick tour of the plugin settings: content source, saving, sync status and log.';
$string['settingstour_endlabel']      = 'Got it';
$string['settingstour_name']          = 'Check-In settings';
$string['settingstour_step1_content'] = 'This page is the central entry point for admins. In five steps I will show you where to pick the content source, how to save, and where to inspect the current sync status.';
$string['settingstour_step1_title']   = 'Plugin settings at a glance';
$string['settingstour_step2_content'] = 'This is where you decide where questions come from: <strong>Bundled default</strong> uses the starter questions shipped with the plugin — ideal for getting started immediately. <strong>Custom git repository</strong> lets you plug in your own question catalogues from a Git repository.';
$string['settingstour_step2_title']   = 'Pick a content source';
$string['settingstour_step3_content'] = 'Save after every configuration change. Note: the Sync-status card below only refreshes once you have saved and a new sync has run.';
$string['settingstour_step3_title']   = 'Save your changes';
$string['settingstour_step4_content'] = 'This card shows the active content source and offers two quick actions: <strong>Run sync now</strong> fetches the latest questions into the system, <strong>Test connection</strong> (only when using Git) verifies the repository is reachable without importing.';
$string['settingstour_step4_title']   = 'Current sync state';
$string['settingstour_step5_content'] = 'The table lists the last 15 sync attempts with timestamp, source, result (green = OK, red = error) and the number of imported questions. On red entries, read the error message in the last column. The hourly cron task runs automatically in the background, so you do not have to sync manually.';
$string['settingstour_step5_title']   = 'Recent sync runs';
$string['showanswer']         = 'Show answer';
$string['sourceheading']      = 'Content source';
$string['sourceheading_desc'] = 'Choose where the check-in questions come from. The bundled default is always available as a fallback.';
$string['syncerror_norepourl'] = 'No repository URL configured - aborting synchronisation.';
$string['synclog_bundle']     = 'Bundle';
$string['synclog_count']      = 'Questions';
$string['synclog_empty']      = 'No sync runs have been recorded yet.';
$string['synclog_message']    = 'Message';
$string['synclog_result']     = 'Result';
$string['synclog_source']     = 'Trigger';
$string['synclog_sourceid']   = 'Source';
$string['task_sync_content']  = 'Synchronise check-in questions from repository';
$string['ziel_checkin']       = 'Check-in';
$string['ziel_checkout']      = 'Check-out';
$string['ziel_funfact']       = 'Fun fact';
$string['ziel_impuls']        = 'Impulse';
$string['ziel_learning']      = 'Learning reflection';
$string['ziel_learning_help'] = 'Short, open prompts that invite learners to reflect on their own learning — e.g. "What is the most important thing I learned today?" or "What surprised me this week?". These are deliberately NOT knowledge questions with right/wrong answers, but personal reflection impulses without a back side.';
$string['ziel_retro']         = 'Retrospective';
$string['ziel_zitat']         = 'Quote';
$string['ziele']              = 'Goal';
$string['ziele_all']          = 'All goals';
$string['ziele_help']         = 'Which goal this activity serves (Impulse, Check-in, Retro …). Multiple selection allowed. Your choice restricts the categories offered below.';
$string['zielgruppe']         = 'Audience';
$string['zielgruppe_all']     = 'All audiences';
$string['zielgruppe_fuehrungskraefte'] = 'Leaders / managers';
$string['zielgruppe_grundschule'] = 'Primary school';
$string['zielgruppe_help']    = 'Optional filter on an audience. Questions without an audience tag are considered general-purpose and always displayed. Empty = no restriction.';
$string['zielgruppe_team']    = 'Team';
