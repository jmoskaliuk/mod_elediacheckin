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
 * German language strings for mod_elediacheckin.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']         = 'Check-in';
$string['modulename']         = 'Check-in';
$string['modulenameplural']   = 'Check-ins';
$string['modulename_help']    = 'Die Aktivität "Check-in" zeigt didaktische Check-in- und Check-out-Fragen aus einem extern gepflegten Git-Repository an.';
$string['pluginadministration'] = 'Check-in-Verwaltung';

$string['checkinsettings']    = 'Check-in-Einstellungen';
$string['displaysettings']    = 'Anzeigeoptionen';

$string['mode']               = 'Modus';
$string['mode_help']          = 'Welche Fragetypen diese Aktivität bereitstellt: Check-in, Check-out oder beides.';
$string['mode_both']          = 'Check-in und Check-out';
$string['mode_checkin']       = 'Nur Check-in';
$string['mode_checkout']      = 'Nur Check-out';
$string['categories']         = 'Erlaubte Kategorien';
$string['categories_help']    = 'Kommagetrennte Liste von Kategorie-IDs. Leer lassen, um alle zuzulassen.';
$string['contentlang']        = 'Inhaltssprache';
$string['contentlang_help']   = 'Zweibuchstabiger Sprachcode für die Fragen. Leer lassen, um Kurs- oder Nutzersprache zu verwenden.';
$string['randomstart']        = 'Zufällige Frage beim Öffnen';
$string['randomstart_help']   = 'Wenn aktiviert, wird direkt beim Öffnen der Aktivität eine zufällige Frage angezeigt.';
$string['shownav']            = 'Navigationspfeile anzeigen';
$string['showother']          = 'Schaltfläche "Andere Frage" anzeigen';
$string['showfilter']         = 'Kategorienfilter anzeigen';
$string['avoidrepeat']        = 'Wiederholung der letzten Frage vermeiden';
$string['avoidrepeat_help']   = 'Wenn aktiviert, wird innerhalb einer Sitzung nicht zweimal dieselbe Frage gezeigt.';

$string['newquestion']        = 'Andere Frage';
$string['noquestions']        = 'Für diesen Filter sind aktuell keine Fragen verfügbar.';
$string['noinstances']        = 'In diesem Kurs gibt es keine Check-in-Aktivitäten.';

$string['elediacheckin:addinstance']   = 'Neue Check-in-Aktivität hinzufügen';
$string['elediacheckin:view']          = 'Check-in-Aktivität ansehen';
$string['elediacheckin:manage']        = 'Check-in-Aktivität verwalten';
$string['elediacheckin:synccontent']   = 'Inhalt synchronisieren';

$string['repoheading']        = 'Inhalts-Repository';
$string['repoheading_desc']   = 'Konfiguration der externen Git-basierten Inhaltsquelle. Fragen werden regelmäßig abgerufen und lokal zwischengespeichert.';
$string['repourl']            = 'Repository-URL';
$string['repourl_desc']       = 'HTTPS-URL des Raw-JSON-Artefakts oder Release-Assets.';
$string['reporef']            = 'Branch, Tag oder Commit';
$string['reporef_desc']       = 'Git-Ref, auf die der Inhalt festgepinnt wird. Standard: "main".';
$string['repotoken']          = 'Zugriffstoken';
$string['repotoken_desc']     = 'Optionales Zugriffstoken für private Repositories. Wird, wo möglich, verschlüsselt gespeichert.';
$string['defaultlang']        = 'Standard-Inhaltssprache';
$string['defaultlang_desc']   = 'Sprache, die verwendet wird, wenn in der Aktivität keine konfiguriert ist und keine Nutzerübereinstimmung besteht.';
$string['fallbacklang']       = 'Fallback-Sprache';
$string['fallbacklang_desc']  = 'Letzte Rückfallsprache, wenn weder die konfigurierte noch die Standardsprache passende Fragen hat.';

$string['task_sync_content']  = 'Check-in-Fragen aus Repository synchronisieren';

$string['syncerror_norepourl'] = 'Keine Repository-URL konfiguriert – Synchronisierung abgebrochen.';

$string['privacy:metadata']   = 'Die Aktivität "Check-in" speichert keine personenbezogenen Daten. Fragen werden nur angezeigt, Antworten werden nicht erfasst.';
