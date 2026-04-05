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

$string['ziele']              = 'Fragetypen';
$string['ziele_help']         = 'Welche Kartenarten diese Aktivität bereitstellt. Mehrfach-Auswahl möglich.';
$string['ziel_impuls']        = 'Impuls';
$string['ziel_checkin']       = 'Check-in';
$string['ziel_checkout']      = 'Check-out';
$string['ziel_retro']         = 'Retro';
$string['ziel_learning']      = 'Lerninhalt';
$string['ziel_funfact']       = 'Funfact';
$string['ziel_zitat']         = 'Zitat';
$string['showanswer']         = 'Antwort anzeigen';
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
$string['nextquestion']       = 'Nächste Frage';
$string['openpopup']          = 'Als Popup öffnen';
$string['openfullscreen']     = 'Vollbild';
$string['close']              = 'Schließen';
$string['noquestions']        = 'Für diesen Filter sind aktuell keine Fragen verfügbar.';
$string['noinstances']        = 'In diesem Kurs gibt es keine Check-in-Aktivitäten.';

$string['elediacheckin:addinstance']   = 'Neue Check-in-Aktivität hinzufügen';
$string['elediacheckin:view']          = 'Check-in-Aktivität ansehen';
$string['elediacheckin:manage']        = 'Check-in-Aktivität verwalten';
$string['elediacheckin:synccontent']   = 'Inhalt synchronisieren';

$string['sourceheading']      = 'Inhaltsquelle';
$string['sourceheading_desc'] = 'Legt fest, woher die Check-in-Fragen kommen. Die mitgelieferten Standardfragen stehen immer als Fallback zur Verfügung.';
$string['contentsource']      = 'Aktive Inhaltsquelle';
$string['contentsource_desc'] = 'Welche Quelle der geplante Synchronisationstask verwendet. Nach einer Änderung muss der Sync erneut laufen, um das neue Bundle zu laden.';
$string['langheading']        = 'Sprach-Fallbacks';
$string['langheading_desc']   = 'Greift, wenn eine Aktivität keine eigene Inhaltssprache vorgibt.';
$string['synclog_link']       = 'Synchronisations-Report';
$string['synclog_open']       = 'Sync-Log öffnen';
$string['synclog_title']      = 'Check-in Sync-Log';
$string['synclog_current']    = 'Aktueller Zustand';
$string['synclog_activesource'] = 'Aktive Inhaltsquelle: <strong>{$a}</strong>';
$string['synclog_runnow']     = 'Sync jetzt ausführen';
$string['synclog_runsuccess'] = 'Sync erfolgreich: {$a->count} Fragen aus Bundle „{$a->bundle}" importiert.';
$string['synclog_runfailed']  = 'Sync fehlgeschlagen: {$a}';
$string['synclog_empty']      = 'Bisher wurden keine Sync-Läufe protokolliert.';
$string['synclog_source']     = 'Auslöser';
$string['synclog_sourceid']   = 'Quelle';
$string['synclog_bundle']     = 'Bundle';
$string['synclog_result']     = 'Ergebnis';
$string['synclog_count']      = 'Fragen';
$string['synclog_message']    = 'Meldung';
$string['contenterror_gitnourl']   = 'Für die Git-Quelle ist keine Repository-URL konfiguriert.';
$string['contenterror_githttp']    = 'Die konfigurierte Repository-URL konnte nicht per HTTPS geladen werden.';
$string['contenterror_gitempty']   = 'Das Repository lieferte eine leere Antwort zurück.';
$string['contenterror_gitparse']   = 'Die Antwort des Repositories enthält ungültiges JSON.';
$string['contenterror_gitinvalid'] = 'Das Bundle im Repository entspricht nicht dem erwarteten Schema.';
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

// Content sources.
$string['contentsource_bundled']   = 'Mitgelieferte Standardfragen';
$string['contentsource_git']       = 'Eigenes Git-Repository';
$string['contentsource_eledia']    = 'eLeDia Premium-Fragen';

// Content-source error messages.
$string['contenterror_bundlemissing'] = 'Das mitgelieferte Standard-Bundle wurde nicht gefunden. Bitte Plugin-Installation prüfen.';
$string['contenterror_bundleread']    = 'Das mitgelieferte Standard-Bundle konnte nicht gelesen werden.';
$string['contenterror_bundleparse']   = 'Das mitgelieferte Standard-Bundle enthält ungültiges JSON.';
$string['contenterror_bundleinvalid'] = 'Das mitgelieferte Standard-Bundle entspricht nicht dem erwarteten Schema.';

$string['privacy:metadata']   = 'Die Aktivität "Check-in" speichert keine personenbezogenen Daten. Fragen werden nur angezeigt, Antworten werden nicht erfasst.';
