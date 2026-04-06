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

    // 2026040516 — Toggle „Nur eigene Fragen verwenden".
    //
    // Neue tinyint-Spalte `onlyownquestions` auf der Aktivitäts-Instanz.
    // Default 0. Wenn aktiv, überspringt `activity_pool::build_pool()`
    // die Bundle-Query komplett und nutzt ausschließlich
    // `parse_own_questions()`. Siehe Konzept §10.15.
    if ($oldversion < 2026040516) {
        $tableinstance = new xmldb_table('elediacheckin');
        $fieldonly = new xmldb_field('onlyownquestions', XMLDB_TYPE_INTEGER, '1', null,
            XMLDB_NOTNULL, null, '0', 'showprevbutton');
        if (!$dbman->field_exists($tableinstance, $fieldonly)) {
            $dbman->add_field($tableinstance, $fieldonly);
        }
        upgrade_mod_savepoint(true, 2026040516, 'elediacheckin');
    }

    // 2026040524 — Tri-state „Eigene Fragen": rename onlyownquestions → ownquestionsmode.
    //
    // Das Yes/No-Feld war missverständlich: „Nein" heißt NICHT „keine
    // eigenen Fragen", sondern „gemischt mit Bundle". Die neue Spalte
    // `ownquestionsmode` hält drei klare Zustände: 0 = mixed (default,
    // bisheriges „Nein"), 1 = only_own (bisheriges „Ja"), 2 = none
    // (neu — eigene Fragen werden komplett ignoriert, auch wenn das
    // Textfeld gefüllt ist). Spalte wird per rename_field umbenannt,
    // damit bestehende 0/1-Werte erhalten bleiben.
    if ($oldversion < 2026040524) {
        $tableinstance = new xmldb_table('elediacheckin');
        $fieldold = new xmldb_field('onlyownquestions', XMLDB_TYPE_INTEGER, '1', null,
            XMLDB_NOTNULL, null, '0', 'showprevbutton');
        if ($dbman->field_exists($tableinstance, $fieldold)) {
            $dbman->rename_field($tableinstance, $fieldold, 'ownquestionsmode');
        }
        upgrade_mod_savepoint(true, 2026040524, 'elediacheckin');
    }

    // 2026040525 — Teacher user tour + misc UX polish.
    //
    // Kein Schema-Change, aber bestehende Installationen sollen die neue
    // Lehrkräfte-Tour nachträglich importieren. install.php ruft den
    // Import nur bei fresh installs; für Upgrades wiederholen wir den
    // Aufruf hier idempotent (manager::import_tour_from_json erlaubt
    // identisches Mehrfach-Importieren, legt bei Kollision einen neuen
    // Datensatz an — die Tour ist nur aktiv, wenn enabled=true, und
    // der Admin kann Duplikate bei Bedarf manuell löschen).
    if ($oldversion < 2026040525) {
        \mod_elediacheckin\local\tour_installer::install_bundled_tours();
        upgrade_mod_savepoint(true, 2026040525, 'elediacheckin');
    }

    // 2026040529 — Tour-JSON repariert.
    //
    // Die in 2026040525 importierte Lehrkräfte-Tour war leer (0 Schritte im
    // Admin-UI), weil das JSON `configdata` als verschachteltes Objekt statt
    // als JSON-String enthielt. tool_usertours ruft aber intern
    // `json_decode($record->configdata)` auf — ein stdClass-Input wirft in
    // PHP 8 einen TypeError, was den Step-Insert stumm scheitern lässt.
    // Siehe docs/content-distribution-konzept.md §10.23.
    //
    // Fix: kaputte Tour(s) per pathmatch löschen und neu importieren. Der
    // pathmatch ist eindeutig genug (/mod/elediacheckin/view.php%), dass wir
    // nicht versehentlich fremde Tours treffen.
    if ($oldversion < 2026040529) {
        if (class_exists('\\tool_usertours\\tour')) {
            $brokentours = $DB->get_records_select(
                'tool_usertours_tours',
                $DB->sql_like('pathmatch', ':path'),
                ['path' => '/mod/elediacheckin/%']
            );
            foreach ($brokentours as $record) {
                try {
                    $tour = \tool_usertours\tour::load_from_record($record);
                    $tour->remove();
                } catch (\Throwable $e) {
                    debugging(
                        'mod_elediacheckin upgrade: could not remove broken tour '
                            . $record->id . ': ' . $e->getMessage(),
                        DEBUG_DEVELOPER
                    );
                }
            }
        }
        \mod_elediacheckin\local\tour_installer::install_bundled_tours();
        upgrade_mod_savepoint(true, 2026040529, 'elediacheckin');
    }

    // 2026040531 — Tour nochmal neu laden.
    //
    // Zwei Gründe: (a) der Rollen-Filter enthielt kein `-1` (Site-Admin-
    // Sentinel), wodurch Site-Admins die Tour nicht angezeigt bekamen; und
    // (b) die Texte lagen hartcodiert auf Deutsch im JSON, statt als
    // lang-string-Referenzen (`stringid,component`), sodass die englische
    // Oberfläche dieselben deutschen Schritte sah. Beide Änderungen wirken
    // nur, wenn die Tour vollständig neu importiert wird — `persist()` auf
    // einer bestehenden Tour würde die Schritte nicht neu anlegen.
    if ($oldversion < 2026040531) {
        if (class_exists('\\tool_usertours\\tour')) {
            $oldtours = $DB->get_records_select(
                'tool_usertours_tours',
                $DB->sql_like('pathmatch', ':path'),
                ['path' => '/mod/elediacheckin/%']
            );
            foreach ($oldtours as $record) {
                try {
                    $tour = \tool_usertours\tour::load_from_record($record);
                    $tour->remove();
                } catch (\Throwable $e) {
                    debugging(
                        'mod_elediacheckin upgrade: could not remove old tour '
                            . $record->id . ': ' . $e->getMessage(),
                        DEBUG_DEVELOPER
                    );
                }
            }
        }
        \mod_elediacheckin\local\tour_installer::install_bundled_tours();
        upgrade_mod_savepoint(true, 2026040531, 'elediacheckin');
    }

    // 2026040534 — Neue Admin-Settings-User-Tour importieren.
    //
    // Mit dieser Version wird eine zweite User-Tour ausgeliefert, die den
    // Admin durch die Plugin-Einstellungsseite führt (Inhaltsquelle wählen,
    // Speichern, Sync-Status, Log). Sie liegt in
    // db/tours/settings_checkin_tour.json und matcht
    // /admin/settings.php?section=modsettingelediacheckin%.
    //
    // Vorgehen: ALLE von diesem Plugin ausgelieferten Tours löschen (Teacher
    // *und* Settings), danach reimportieren. Grund: `mod_elediacheckin_
    // install_bundled_tours()` iteriert über alle JSONs im db/tours/-Ordner
    // und ruft `tool_usertours\manager::import_tour_from_json()` für jede
    // — das legt stets einen neuen Record an, niemals update. Würden wir
    // nur die Settings-Tour löschen, entstünde eine doppelte Teacher-Tour.
    if ($oldversion < 2026040534) {
        if (class_exists('\\tool_usertours\\tour')) {
            $patterns = [
                '/mod/elediacheckin/%',
                '/admin/settings.php?section=modsettingelediacheckin%',
            ];
            foreach ($patterns as $pattern) {
                $oldtours = $DB->get_records_select(
                    'tool_usertours_tours',
                    $DB->sql_like('pathmatch', ':path'),
                    ['path' => $pattern]
                );
                foreach ($oldtours as $record) {
                    try {
                        $tour = \tool_usertours\tour::load_from_record($record);
                        $tour->remove();
                    } catch (\Throwable $e) {
                        debugging(
                            'mod_elediacheckin upgrade: could not remove old tour '
                                . $record->id . ': ' . $e->getMessage(),
                            DEBUG_DEVELOPER
                        );
                    }
                }
            }
        }
        \mod_elediacheckin\local\tour_installer::install_bundled_tours();
        upgrade_mod_savepoint(true, 2026040534, 'elediacheckin');
    }

    // 2026040538 — Neues Feld exhaustedbehavior + dritte User-Tour
    // (activity_settings_tour) importieren.
    //
    // Johannes' Feedback v2026040537→v2026040538: Bei Wiederholt-Nutzung der
    // Check-in-Aktivität sollen Lehrkräfte pro Aktivität festlegen können,
    // was passiert, wenn alle Fragen einmal gezogen wurden — entweder still
    // von vorne beginnen (Default) oder eine „Alle Fragen durch"-Karte
    // zeigen. Das neue CHAR-Feld `exhaustedbehavior` hält genau diesen
    // Selector. Außerdem: die in v2026040537 neu dazugekommene
    // `activity_settings_tour.json` wurde bei bestehenden Installationen
    // nie nachinstalliert, weil der install.php-Helper nur bei fresh
    // installs läuft. Deshalb hier zusätzlich alle Plugin-Tours löschen und
    // neu importieren — idempotent und betrifft nur vom Plugin bundled Tours.
    if ($oldversion < 2026040538) {
        $tableinstance = new xmldb_table('elediacheckin');
        $fieldex = new xmldb_field('exhaustedbehavior', XMLDB_TYPE_CHAR, '16', null,
            XMLDB_NOTNULL, null, 'restart', 'showprevbutton');
        if (!$dbman->field_exists($tableinstance, $fieldex)) {
            $dbman->add_field($tableinstance, $fieldex);
        }

        if (class_exists('\\tool_usertours\\tour')) {
            $patterns = [
                '/mod/elediacheckin/%',
                '/admin/settings.php?section=modsettingelediacheckin%',
                '/course/modedit.php%',
            ];
            foreach ($patterns as $pattern) {
                $oldtours = $DB->get_records_select(
                    'tool_usertours_tours',
                    $DB->sql_like('pathmatch', ':path'),
                    ['path' => $pattern]
                );
                foreach ($oldtours as $record) {
                    // Nur Tours mit eindeutigem eLeDia-Prefix im Namen
                    // entfernen — `/course/modedit.php%` ist zu generisch,
                    // könnte fremde Tours treffen.
                    if (strpos((string) $record->name, 'Check-In') === false
                        && strpos((string) $record->name, 'Check-in') === false) {
                        continue;
                    }
                    try {
                        $tour = \tool_usertours\tour::load_from_record($record);
                        $tour->remove();
                    } catch (\Throwable $e) {
                        debugging(
                            'mod_elediacheckin upgrade: could not remove old tour '
                                . $record->id . ': ' . $e->getMessage(),
                            DEBUG_DEVELOPER
                        );
                    }
                }
            }
        }
        \mod_elediacheckin\local\tour_installer::install_bundled_tours();
        upgrade_mod_savepoint(true, 2026040538, 'elediacheckin');
    }

    // 2026040543 — Align DB schema with install.xml.
    //
    // Fixes reported by admin/cli/check_database_schema.php:
    //  - contentlang: widen to CHAR(16), set DEFAULT '_auto_'.
    //  - showprevbutton: change DEFAULT from 0 to 1.
    //  - elediacheckin_question: categories/zielgruppe/kontext/license → allow NULL.
    if ($oldversion < 2026040543) {
        $table = new xmldb_table('elediacheckin');

        // Widen contentlang CHAR(10) → CHAR(16) and set default to '_auto_'.
        $field = new xmldb_field(
            'contentlang',
            XMLDB_TYPE_CHAR,
            '16',
            null,
            null,
            null,
            '_auto_',
            'kontext'
        );
        $dbman->change_field_precision($table, $field);
        $dbman->change_field_default($table, $field);

        // showprevbutton: change column default from 0 to 1.
        $field = new xmldb_field(
            'showprevbutton',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '1',
            'ownquestionsmode'
        );
        $dbman->change_field_default($table, $field);

        // elediacheckin_question: make four columns nullable.
        $qtable = new xmldb_table('elediacheckin_question');
        $nullcols = [
            'categories' => ['type' => XMLDB_TYPE_CHAR, 'len' => '255', 'after' => 'ziel'],
            'zielgruppe' => ['type' => XMLDB_TYPE_CHAR, 'len' => '255', 'after' => 'categories'],
            'kontext'    => ['type' => XMLDB_TYPE_CHAR, 'len' => '255', 'after' => 'zielgruppe'],
            'license'    => ['type' => XMLDB_TYPE_CHAR, 'len' => '64', 'after' => 'quelle'],
        ];
        foreach ($nullcols as $colname => $spec) {
            $field = new xmldb_field(
                $colname,
                $spec['type'],
                $spec['len'],
                null,
                null,
                null,
                null,
                $spec['after']
            );
            $dbman->change_field_notnull($qtable, $field);
        }

        upgrade_mod_savepoint(true, 2026040543, 'elediacheckin');
    }

    return true;
}
