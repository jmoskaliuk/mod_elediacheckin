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
 * Database upgrade steps for mod_elediacheckin.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Runs the upgrade steps.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Always true.
 */
function xmldb_elediacheckin_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();

    // 2026040501 — Content-source schema v1.0.
    //
    // Replaces the initial scaffold schema with the final Phase-1 layout:
    //  - elediacheckin_question gains all fields from the content JSON
    //    schema (ziel, categories CSV, hasanswer/antwort, license, author,
    //    quelle, qversion, qstatus, link, media, extcreated/extmodified)
    //    plus a 'stage' flag for the staging-swap sync pattern.
    //  - elediacheckin (activity instance) renames 'mode' → 'ziele' so a
    //    single activity can draw from multiple ziele.
    //  - The separate category + question-category tables are dropped:
    //    categories are a fixed enum validated by the schema validator, so
    //    a CSV column on the question row is sufficient and avoids joins.
    //  - elediacheckin_sync_log gains bundleid/bundleversion/sourceid.
    //
    // Because the plugin is still MATURITY_ALPHA and has never shipped to a
    // production site, the upgrade simply drops & recreates the affected
    // tables. Any dev-only sync data will be re-populated on the next sync.
    if ($oldversion < 2026040501) {

        // Drop the old per-question category link table.
        $tablelink = new xmldb_table('elediacheckin_question_cat');
        if ($dbman->table_exists($tablelink)) {
            $dbman->drop_table($tablelink);
        }

        // Drop the old category master table.
        $tablecat = new xmldb_table('elediacheckin_category');
        if ($dbman->table_exists($tablecat)) {
            $dbman->drop_table($tablecat);
        }

        // Drop and recreate elediacheckin_question with the new layout.
        $tablequestion = new xmldb_table('elediacheckin_question');
        if ($dbman->table_exists($tablequestion)) {
            $dbman->drop_table($tablequestion);
        }

        $tablequestion->add_field('id',            XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $tablequestion->add_field('stage',         XMLDB_TYPE_INTEGER, '1',  null, XMLDB_NOTNULL, null, '0');
        $tablequestion->add_field('bundleid',      XMLDB_TYPE_CHAR,    '64', null, XMLDB_NOTNULL, null, '');
        $tablequestion->add_field('bundleversion', XMLDB_TYPE_CHAR,    '64', null, XMLDB_NOTNULL, null, '');
        $tablequestion->add_field('externalid',    XMLDB_TYPE_CHAR,    '128', null, XMLDB_NOTNULL, null, '');
        $tablequestion->add_field('ziel',          XMLDB_TYPE_CHAR,    '16', null, XMLDB_NOTNULL, null, '');
        $tablequestion->add_field('categories',    XMLDB_TYPE_CHAR,    '255', null, XMLDB_NOTNULL, null, '');
        $tablequestion->add_field('frage',         XMLDB_TYPE_TEXT,    null, null, XMLDB_NOTNULL, null, null);
        $tablequestion->add_field('hasanswer',     XMLDB_TYPE_INTEGER, '1',  null, XMLDB_NOTNULL, null, '0');
        $tablequestion->add_field('antwort',       XMLDB_TYPE_TEXT,    null, null, null,          null, null);
        $tablequestion->add_field('lang',          XMLDB_TYPE_CHAR,    '10', null, XMLDB_NOTNULL, null, '');
        $tablequestion->add_field('author',        XMLDB_TYPE_CHAR,    '255', null, null,         null, null);
        $tablequestion->add_field('quelle',        XMLDB_TYPE_CHAR,    '255', null, null,         null, null);
        $tablequestion->add_field('license',       XMLDB_TYPE_CHAR,    '64', null, XMLDB_NOTNULL, null, '');
        $tablequestion->add_field('qversion',      XMLDB_TYPE_CHAR,    '32', null, XMLDB_NOTNULL, null, '1');
        $tablequestion->add_field('qstatus',       XMLDB_TYPE_CHAR,    '16', null, XMLDB_NOTNULL, null, 'published');
        $tablequestion->add_field('link',          XMLDB_TYPE_CHAR,    '1333', null, null,        null, null);
        $tablequestion->add_field('media',         XMLDB_TYPE_CHAR,    '1333', null, null,        null, null);
        $tablequestion->add_field('extcreated',    XMLDB_TYPE_INTEGER, '10', null, null,          null, null);
        $tablequestion->add_field('extmodified',   XMLDB_TYPE_INTEGER, '10', null, null,          null, null);
        $tablequestion->add_field('timecreated',   XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablequestion->add_field('timemodified',  XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $tablequestion->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $tablequestion->add_index('stage_ext_lang', XMLDB_INDEX_UNIQUE, ['stage', 'externalid', 'lang']);
        $tablequestion->add_index('stage_ziel_lang_status', XMLDB_INDEX_NOTUNIQUE, ['stage', 'ziel', 'lang', 'qstatus']);
        $dbman->create_table($tablequestion);

        // Activity instance: rename 'mode' → 'ziele' and widen the default.
        $tableinstance = new xmldb_table('elediacheckin');
        $fieldmode = new xmldb_field('mode', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, 'both');
        if ($dbman->field_exists($tableinstance, $fieldmode)) {
            $dbman->rename_field($tableinstance, $fieldmode, 'ziele');
            // After rename, widen it to 255 chars to hold a CSV list.
            $fieldziele = new xmldb_field('ziele', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'checkin,checkout');
            $dbman->change_field_precision($tableinstance, $fieldziele);
            $dbman->change_field_default($tableinstance, $fieldziele);
            // Migrate any old 'both' values to the new CSV default.
            $DB->execute("UPDATE {elediacheckin} SET ziele = 'checkin,checkout' WHERE ziele = 'both'");
        }

        // Sync log: add bundle metadata columns.
        $tablelog = new xmldb_table('elediacheckin_sync_log');

        $fieldsourceid = new xmldb_field('sourceid', XMLDB_TYPE_CHAR, '32', null, null, null, null, 'source');
        if (!$dbman->field_exists($tablelog, $fieldsourceid)) {
            $dbman->add_field($tablelog, $fieldsourceid);
        }

        $fieldbundleid = new xmldb_field('bundleid', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'sourceid');
        if (!$dbman->field_exists($tablelog, $fieldbundleid)) {
            $dbman->add_field($tablelog, $fieldbundleid);
        }

        $fieldbundleversion = new xmldb_field('bundleversion', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'bundleid');
        if (!$dbman->field_exists($tablelog, $fieldbundleversion)) {
            $dbman->add_field($tablelog, $fieldbundleversion);
        }

        // Drop legacy sourceversion/sourcecommit fields if they still exist.
        foreach (['sourceversion', 'sourcecommit'] as $legacy) {
            $field = new xmldb_field($legacy);
            if ($dbman->field_exists($tablelog, $field)) {
                $dbman->drop_field($tablelog, $field);
            }
        }

        // Widen 'source' to 32 chars to match the installer.
        $fieldsource = new xmldb_field('source', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, 'manual');
        if ($dbman->field_exists($tablelog, $fieldsource)) {
            $dbman->change_field_precision($tablelog, $fieldsource);
        }

        upgrade_mod_savepoint(true, 2026040501, 'elediacheckin');
    }

    // 2026040502 — Trigger an initial content sync.
    //
    // The post-install hook (db/install.php) only runs on a fresh install.
    // Existing dev sites that installed earlier versions have an empty
    // question table until cron runs the scheduled task. Force an immediate
    // sync here so the bundled default questions become visible right after
    // the upgrade. Failures are logged but never abort the upgrade.
    if ($oldversion < 2026040502) {
        try {
            (new \mod_elediacheckin\local\service\sync_service())->run('upgrade');
        } catch (\Throwable $e) {
            debugging(
                'mod_elediacheckin: initial content sync failed during upgrade: ' . $e->getMessage(),
                DEBUG_DEVELOPER
            );
        }
        upgrade_mod_savepoint(true, 2026040502, 'elediacheckin');
    }

    // 2026040503 — Drop dead instance columns, default contentlang to '_auto_'.
    //
    // The following columns were scaffolded for the old UI mock-up but are
    // never read by view.php/present.php or any service: randomstart,
    // shownav, showother, showfilter. They are dropped to keep the schema
    // honest and avoid confusing future developers.
    //
    // The contentlang column gets the new sentinel default '_auto_' so
    // freshly created activities resolve to the user language without any
    // admin intervention. Existing rows with an empty contentlang are left
    // alone; the view resolves empty → current_language() anyway.
    if ($oldversion < 2026040503) {
        $tableinstance = new xmldb_table('elediacheckin');

        foreach (['randomstart', 'shownav', 'showother', 'showfilter'] as $deadfield) {
            $field = new xmldb_field($deadfield);
            if ($dbman->field_exists($tableinstance, $field)) {
                $dbman->drop_field($tableinstance, $field);
            }
        }

        // Widen contentlang and change its default to the auto sentinel.
        $fieldcontentlang = new xmldb_field('contentlang', XMLDB_TYPE_CHAR, '16', null,
            null, null, '_auto_', 'categories');
        if ($dbman->field_exists($tableinstance, $fieldcontentlang)) {
            $dbman->change_field_precision($tableinstance, $fieldcontentlang);
            $dbman->change_field_default($tableinstance, $fieldcontentlang);
        }

        upgrade_mod_savepoint(true, 2026040503, 'elediacheckin');
    }

    // 2026040508 — Zielgruppe + Kontext als optionale Tag-Dimensionen.
    //
    // Adds two orthogonal, optional filter dimensions to both the activity
    // instance row and the question row:
    //  - zielgruppe (fuehrungskraefte, team, grundschule)
    //  - kontext    (arbeit, schule, hochschule, privat)
    //
    // Semantics: an empty filter means "no restriction". A non-empty filter
    // matches a question if the question is either untagged for that
    // dimension OR shares at least one value with the filter. This lets
    // content authors leave generally applicable questions untagged without
    // losing them in filtered activities.
    if ($oldversion < 2026040508) {
        // Activity instance: optional CSV filter columns.
        $tableinstance = new xmldb_table('elediacheckin');

        $fieldzg = new xmldb_field('zielgruppe', XMLDB_TYPE_CHAR, '255', null,
            null, null, null, 'categories');
        if (!$dbman->field_exists($tableinstance, $fieldzg)) {
            $dbman->add_field($tableinstance, $fieldzg);
        }

        $fieldkx = new xmldb_field('kontext', XMLDB_TYPE_CHAR, '255', null,
            null, null, null, 'zielgruppe');
        if (!$dbman->field_exists($tableinstance, $fieldkx)) {
            $dbman->add_field($tableinstance, $fieldkx);
        }

        // Question table: CSV columns, NOT NULL with empty default so the
        // provider can treat empty-string as "untagged".
        $tablequestion = new xmldb_table('elediacheckin_question');

        $qzg = new xmldb_field('zielgruppe', XMLDB_TYPE_CHAR, '255', null,
            XMLDB_NOTNULL, null, '', 'categories');
        if (!$dbman->field_exists($tablequestion, $qzg)) {
            $dbman->add_field($tablequestion, $qzg);
        }

        $qkx = new xmldb_field('kontext', XMLDB_TYPE_CHAR, '255', null,
            XMLDB_NOTNULL, null, '', 'zielgruppe');
        if (!$dbman->field_exists($tablequestion, $qkx)) {
            $dbman->add_field($tablequestion, $qkx);
        }

        // Re-sync so the new columns get populated for any existing rows.
        try {
            (new \mod_elediacheckin\local\service\sync_service())->run('upgrade');
        } catch (\Throwable $e) {
            debugging(
                'mod_elediacheckin: sync after zielgruppe/kontext upgrade failed: ' . $e->getMessage(),
                DEBUG_DEVELOPER
            );
        }

        upgrade_mod_savepoint(true, 2026040508, 'elediacheckin');
    }

    // 2026040509 — Eigene Fragen pro Aktivität (§10.13 im Konzept).
    //
    // Adds an optional TEXT column 'ownquestions' to the activity instance
    // row. Teachers can fill it via a textarea in mod_form (one question
    // per line). At draw time, view.php/present.php merge these lines into
    // the bundle-sourced pool via activity_pool helper. The virtual
    // category "eigene" lives purely in code; the JSON schema and the
    // question table are unaffected.
    if ($oldversion < 2026040509) {
        $tableinstance = new xmldb_table('elediacheckin');

        $fieldown = new xmldb_field('ownquestions', XMLDB_TYPE_TEXT, null, null,
            null, null, null, 'avoidrepeat');
        if (!$dbman->field_exists($tableinstance, $fieldown)) {
            $dbman->add_field($tableinstance, $fieldown);
        }

        upgrade_mod_savepoint(true, 2026040509, 'elediacheckin');
    }

    // 2026040515 — Display-Option: „Zur vorherigen Frage"-Button.
    //
    // Neue tinyint-Spalte `showprevbutton` auf der Aktivitäts-Instanz.
    // Default 0 (aus). Wenn aktiv, rendert view.php/present.php einen
    // zusätzlichen Button „Zur vorherigen Frage", der die im Session-State
    // gespeicherte vorherige Karte erneut anzeigt (single-step back,
    // keine Forward-/Backward-Pfeile — siehe Konzept §10.14).
    if ($oldversion < 2026040515) {
        $tableinstance = new xmldb_table('elediacheckin');
        $fieldprev = new xmldb_field('showprevbutton', XMLDB_TYPE_INTEGER, '1', null,
            XMLDB_NOTNULL, null, '0', 'ownquestions');
        if (!$dbman->field_exists($tableinstance, $fieldprev)) {
            $dbman->add_field($tableinstance, $fieldprev);
        }
        upgrade_mod_savepoint(true, 2026040515, 'elediacheckin');
    }

    return true;
}
