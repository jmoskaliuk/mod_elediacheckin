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
 * Site-wide admin settings for mod_elediacheckin.
 *
 * Single entry point for all plugin administration. Layout follows the
 * "configure first, inspect later" principle:
 *
 *   1. Intro mini-guide  (what to do, in order)
 *   2. Content source    (the first decision the admin has to make)
 *   3. Git repo config   (conditionally shown only if source = git)
 *   4. Language fallbacks
 *   5. Sync status panel (dashboard — checked AFTER the config is saved)
 *
 * There is intentionally no separate admin_externalpage anymore.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // ---------------------------------------------------------------------
    // 1. Intro: short how-to for first-time admins. Purely informational.
    // ---------------------------------------------------------------------
    $settings->add(new admin_setting_heading(
        'mod_elediacheckin/adminintro_heading',
        get_string('adminintro_heading', 'elediacheckin'),
        get_string('adminintro_desc', 'elediacheckin')
    ));

    // ---------------------------------------------------------------------
    // 2. Content source selection.
    // ---------------------------------------------------------------------
    $settings->add(new admin_setting_heading(
        'mod_elediacheckin/sourceheading',
        get_string('sourceheading', 'elediacheckin'),
        get_string('sourceheading_desc', 'elediacheckin')
    ));

    $sourceoptions = [
        'bundled'        => get_string('contentsource_bundled', 'elediacheckin'),
        'git'            => get_string('contentsource_git', 'elediacheckin'),
        // Phase-2 placeholder — listed but disabled until the premium backend is live.
        // The sync service falls back to 'bundled' if this value is ever picked.
        'eledia_premium' => get_string('contentsource_eledia', 'elediacheckin') . ' (Phase 2)',
    ];
    $settings->add(new admin_setting_configselect(
        'mod_elediacheckin/contentsource',
        get_string('contentsource', 'elediacheckin'),
        get_string('contentsource_desc', 'elediacheckin'),
        'bundled',
        $sourceoptions
    ));

    // ---------------------------------------------------------------------
    // 3. Git source configuration — only relevant when source = git.
    // The heading + all three fields are hidden unless the admin actually
    // picked the git source, so the "Default" case is visually calm.
    // ---------------------------------------------------------------------
    $repoheading = new admin_setting_heading(
        'mod_elediacheckin/repoheading',
        get_string('repoheading', 'elediacheckin'),
        get_string('repoheading_desc', 'elediacheckin')
    );
    $settings->add($repoheading);

    // Default auf das oeffentliche Beispiel-Repo von eLeDia. Admins, die
    // ihren eigenen Content betreuen wollen, koennen den Wert einfach
    // ueberschreiben — aber „leer lassen und direkt ausprobieren" ist ein
    // valider Pfad. Siehe Konzept §10.16.
    $settings->add(new admin_setting_configtext(
        'mod_elediacheckin/repourl',
        get_string('repourl', 'elediacheckin'),
        get_string('repourl_desc', 'elediacheckin'),
        'https://github.com/jmoskaliuk/content_elediacheckin.git',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'mod_elediacheckin/reporef',
        get_string('reporef', 'elediacheckin'),
        get_string('reporef_desc', 'elediacheckin'),
        'main',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'mod_elediacheckin/repotoken',
        get_string('repotoken', 'elediacheckin'),
        get_string('repotoken_desc', 'elediacheckin'),
        ''
    ));

    // Hide repo fields unless the git source is chosen. The heading itself
    // cannot be hidden by the core hide_if machinery (it targets form
    // elements by setting name) — we therefore also hide it via a small
    // JS-less CSS sibling rule rendered right after it. Admins that pick
    // "Default" or the Phase-2 premium source never see any repo UI at all.
    $settings->hide_if('mod_elediacheckin/repourl',   'mod_elediacheckin/contentsource', 'neq', 'git');
    $settings->hide_if('mod_elediacheckin/reporef',   'mod_elediacheckin/contentsource', 'neq', 'git');
    $settings->hide_if('mod_elediacheckin/repotoken', 'mod_elediacheckin/contentsource', 'neq', 'git');

    // ---------------------------------------------------------------------
    // 4. Language fallbacks (apply to all sources).
    // ---------------------------------------------------------------------
    $settings->add(new admin_setting_heading(
        'mod_elediacheckin/langheading',
        get_string('langheading', 'elediacheckin'),
        get_string('langheading_desc', 'elediacheckin')
    ));

    $settings->add(new admin_setting_configtext(
        'mod_elediacheckin/defaultlang',
        get_string('defaultlang', 'elediacheckin'),
        get_string('defaultlang_desc', 'elediacheckin'),
        'en',
        PARAM_LANG
    ));

    $settings->add(new admin_setting_configtext(
        'mod_elediacheckin/fallbacklang',
        get_string('fallbacklang', 'elediacheckin'),
        get_string('fallbacklang_desc', 'elediacheckin'),
        'en',
        PARAM_LANG
    ));

    // ---------------------------------------------------------------------
    // 5. Embedded dashboard: active source, manual sync, connection test,
    // recent sync log. Intentionally at the BOTTOM: the logical flow is
    // "set the configuration first, then verify the sync status".
    // ---------------------------------------------------------------------
    $settings->add(new admin_setting_heading(
        'mod_elediacheckin/dashboard_heading',
        get_string('dashboard_heading', 'elediacheckin'),
        get_string('dashboard_heading_desc', 'elediacheckin')
    ));

    $settings->add(new admin_setting_description(
        'mod_elediacheckin/dashboard_panel',
        '',
        \mod_elediacheckin\local\admin\dashboard_renderer::render()
    ));
}
