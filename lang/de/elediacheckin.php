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

$string['pluginname']         = 'eLeDia Check-In';
$string['modulename']         = 'Check-In';
$string['modulenameplural']   = 'Check-Ins';
$string['modulename_help']    = 'Die Aktivität „Check-in" zeigt kurze didaktische Impulse, Fragen und Karten — zum Beispiel für Check-in-Runden zu Beginn einer Sitzung, Check-out-Reflexionen am Ende, Retro-Fragen, Zitate oder Fun-Facts.

Sie können pro Aktivität auswählen, welche Ziele (Check-in, Check-out, Retro, Impuls, Lernreflexion, Fun-Fact, Zitat) und welche Kategorien gezogen werden dürfen. Optional lässt sich zusätzlich auf Zielgruppe (z. B. Team, Führungskräfte) und Kontext (Arbeit, Schule, Hochschule, Privat) einschränken. Teilnehmende sehen dann bei jedem Seitenaufruf eine zufällig passende Karte aus dem konfigurierten Pool.

Die Inhalte werden zentral von der Site-Administration aus einem Content-Repository synchronisiert. Lehrkräfte entscheiden nur, wie die Karten in ihrer Aktivität eingesetzt werden; sie können zusätzlich eigene Fragen pro Aktivität hinzufügen.

<a href="https://www.eledia.de/mod_elediacheckin" target="_blank" rel="noopener">Mehr erfahren auf eledia.de</a>';
$string['pluginadministration'] = 'Check-in-Verwaltung';

$string['checkinsettings']    = 'Check-in-Einstellungen';
$string['displaysettings']    = 'Anzeigeoptionen';

$string['ziele']              = 'Ziel';
$string['ziele_help']         = 'Welches Ziel diese Aktivität verfolgt (Impuls, Check-in, Retro …). Mehrfach-Auswahl möglich. Die Auswahl schränkt die unten zur Verfügung stehenden Kategorien ein.';
$string['ziel_impuls']        = 'Impuls';
$string['ziel_checkin']       = 'Check-in';
$string['ziel_checkout']      = 'Check-out';
$string['ziel_retro']         = 'Retro';
$string['ziel_learning']      = 'Lernreflexion';
$string['ziel_learning_help'] = 'Kurze, offene Fragen, die zum Nachdenken über das eigene Lernen anregen — z. B. „Was ist das Wichtigste, was ich heute gelernt habe?" oder „Welches Aha-Erlebnis hatte ich diese Woche?". Es sind bewusst KEINE Wissensfragen mit richtig/falsch, sondern persönliche Reflexionsimpulse ohne Rückseite.';
$string['ziel_funfact']       = 'Funfact';
$string['ziel_zitat']         = 'Zitat';
$string['showanswer']         = 'Antwort anzeigen';
$string['categories']         = 'Kategorien';
$string['categories_help']    = 'Schränkt die Aktivität auf bestimmte Kategorien ein. Es werden nur Kategorien angezeigt, die zu den oben ausgewählten Zielen passen. Leer lassen heißt: alle Kategorien der gewählten Ziele.';
$string['categories_all']     = 'Alle Kategorien';
$string['ziele_all']          = 'Alle Ziele';
$string['contentlang']        = 'Inhaltssprache';
$string['contentlang_help']   = 'Sprache der Fragen in dieser Aktivität. „Nutzersprache" übernimmt die aktuell eingestellte Moodle-Sprache; „Kurssprache" verwendet die im Kurs hinterlegte Sprache. Wählen Sie eine konkrete Sprache, um die Aktivität an dieses Sprachbundle zu binden.';
$string['lang_auto']          = 'Nutzersprache (empfohlen)';
$string['lang_course']        = 'Kurssprache';
$string['avoidrepeat']        = 'Wiederholung der letzten Frage vermeiden';
$string['avoidrepeat_help']   = 'Wenn aktiviert, wird innerhalb einer Sitzung nicht zweimal dieselbe Frage gezeigt.';
$string['showprevbutton']     = 'Button „Zurück" anzeigen';
$string['showprevbutton_help'] = 'Wenn aktiviert, erscheint neben „Weiter" ein „Zurück"-Button, mit dem Lernende durch die bereits gesehenen Karten dieser Sitzung zurückblättern können. Auf der allerersten Karte bleibt der Button ausgeblendet.';
$string['prevquestion']       = 'Zurück';
$string['exhaustedbehavior']       = 'Wenn alle Fragen durch sind';
$string['exhaustedbehavior_help']  = 'Was soll passieren, wenn in dieser Sitzung alle Fragen des Pools einmal gezeigt wurden? „Von vorne beginnen" setzt den Gesehen-Zähler still zurück und zieht weiter neue Karten (Standard, empfohlen für kleine Pools). „Leere Abschluss-Karte anzeigen" stoppt das Ziehen und zeigt stattdessen eine Hinweismeldung.';
$string['exhaustedbehavior_restart'] = 'Von vorne beginnen';
$string['exhaustedbehavior_empty']   = 'Leere Abschluss-Karte anzeigen';
$string['ownquestionsmode']   = 'Quelle für diese Aktivität';
$string['ownquestionsmode_help'] = 'Legt fest, welche Fragen in dieser Aktivität gezogen werden. „Gemischt" mischt die unten eingetragenen eigenen Fragen additiv zu den Bundle-Fragen der Site-Inhaltsquelle (Default). „Nur eigene Fragen" ignoriert das Site-Bundle komplett — ist das Textfeld leer, werden keine Karten angezeigt. „Keine eigenen Fragen" ignoriert das Textfeld, auch wenn es gefüllt ist — nützlich, um eine Aktivität temporär aus dem eigenen Pool zu nehmen, ohne die Einträge zu löschen.';
$string['ownquestionsmode_mixed']   = 'Gemischt: eigene Fragen zusätzlich zu Bundle-Fragen';
$string['ownquestionsmode_onlyown'] = 'Nur eigene Fragen (Bundle wird ignoriert)';
$string['ownquestionsmode_none']    = 'Keine eigenen Fragen (nur Bundle)';

// Zielgruppe + Kontext (optionale Tag-Dimensionen).
$string['zielgruppe']         = 'Zielgruppe';
$string['zielgruppe_help']    = 'Optionaler Filter auf eine Zielgruppe. Fragen ohne Zielgruppen-Tag sind allgemeingültig und werden immer angezeigt. Leer = keine Einschränkung.';
$string['zielgruppe_all']     = 'Alle Zielgruppen';
$string['zielgruppe_fuehrungskraefte'] = 'Führungskräfte';
$string['zielgruppe_team']    = 'Team';
$string['zielgruppe_grundschule'] = 'Grundschule';
$string['kontext']            = 'Kontext';
$string['kontext_help']       = 'Optionaler Filter auf einen Einsatzkontext. Fragen ohne Kontext-Tag sind allgemeingültig und werden immer angezeigt. Leer = keine Einschränkung.';
$string['kontext_all']        = 'Alle Kontexte';
$string['kontext_arbeit']     = 'Arbeit';
$string['kontext_schule']     = 'Schule';
$string['kontext_hochschule'] = 'Hochschule';
$string['kontext_privat']     = 'Privat';

// Eigene Fragen (§10.13).
$string['ownquestions']       = 'Eigene Fragen';
$string['ownquestions_help']  = 'Zusätzliche Fragen, die nur in dieser Aktivität angezeigt werden. Eine Frage pro Zeile. Leere Zeilen werden ignoriert. Diese Fragen werden zu den Bundle-Fragen hinzugemischt (gleiche Ziehwahrscheinlichkeit), nicht ersetzt. Sie gelten für alle Ziele der Aktivität und haben keine Rückseite. Leer lassen = nur Bundle-Inhalte verwenden.';

// Kategorielabels — passen zu CATEGORIES_BY_ZIEL in schema_validator.php.
$string['cat_kennenlernen']           = 'Kennenlernen';
$string['cat_eisbrecher']             = 'Eisbrecher';
$string['cat_arbeitsmodus']           = 'Arbeitsmodus';
$string['cat_fokus']                  = 'Fokus';
$string['cat_persoenliche-entwicklung'] = 'Persönliche Entwicklung';
$string['cat_stimmung']               = 'Stimmung';
$string['cat_energie']                = 'Energie';
$string['cat_beziehung']              = 'Beziehung';
$string['cat_feedback']               = 'Feedback';
$string['cat_verbesserung']           = 'Verbesserung';
$string['cat_aktion']                 = 'Aktion';
$string['cat_ausblick']               = 'Ausblick';
$string['cat_wertschaetzung']         = 'Wertschätzung';
$string['cat_was-lief-gut']           = 'Was lief gut';
$string['cat_was-lief-schlecht']      = 'Was lief schlecht';
$string['cat_lernen']                 = 'Lernen';
$string['cat_zusammenarbeit']         = 'Zusammenarbeit';
$string['cat_prozess']                = 'Prozess';
$string['cat_kreativitaet']           = 'Kreativität';
$string['cat_perspektivwechsel']      = 'Perspektivwechsel';
$string['cat_reflexion']              = 'Reflexion';
$string['cat_entscheidung']           = 'Entscheidung';
$string['cat_werte']                  = 'Werte';
// Kategorien für Lernreflexion (ziel: learning) — offene Reflexionsimpulse,
// keine Wissenskategorien. Die alten fachdidaktischen Labels (methode,
// theorie, modell, tool) sind bewusst entfernt, damit das Ziel
// „Lernreflexion" nicht mit einer Vorlesung verwechselt wird.
$string['cat_tagesreflexion']         = 'Tagesreflexion';
$string['cat_transfer']               = 'Transfer in die Praxis';
$string['cat_aha']                    = 'Aha-Erlebnis';
$string['cat_hindernis']              = 'Hürde & Missverständnis';
$string['cat_meta']                   = 'Lernen über Lernen';
$string['cat_wissenschaft']           = 'Wissenschaft';
$string['cat_geschichte']             = 'Geschichte';
$string['cat_sprache']                = 'Sprache';
$string['cat_natur']                  = 'Natur';
$string['cat_technik']                = 'Technik';
$string['cat_alltag']                 = 'Alltag';
$string['cat_fuehrung']               = 'Führung';
$string['cat_motivation']             = 'Motivation';
$string['cat_wandel']                 = 'Wandel';
$string['cat_lebensweisheit']         = 'Lebensweisheit';
$string['cat_humor']                  = 'Humor';

$string['newquestion']        = 'Andere Frage';
$string['nextquestion']       = 'Weiter';
$string['exhaustedmessage']   = 'Für diese Sitzung sind alle Fragen aus dem Pool durch. Schauen Sie später wieder vorbei — oder bitten Sie Ihre Lehrkraft, weitere Fragen zu ergänzen.';
$string['openpopup']          = 'Als Popup öffnen';
$string['openfullscreen']     = 'Vollbild';
// HINWEIS: "Schließen" nutzt den Core-String `closebuttontitle` (moodle).
$string['noquestions']        = 'Für diesen Filter sind aktuell keine Fragen verfügbar.';
$string['noinstances']        = 'In diesem Kurs gibt es keine Check-in-Aktivitäten.';

$string['elediacheckin:addinstance']   = 'Neue Check-in-Aktivität hinzufügen';
$string['elediacheckin:view']          = 'Check-in-Aktivität ansehen';
$string['elediacheckin:manage']        = 'Check-in-Aktivität verwalten';
$string['elediacheckin:synccontent']   = 'Inhalt synchronisieren';

$string['adminintro_heading'] = 'Erste Schritte';
$string['adminintro_desc']    = '<div class="alert alert-info mb-3"><strong>Kurzanleitung für Administrator:innen</strong><ol class="mb-0"><li>Wählen Sie unten eine <em>Inhaltsquelle</em>. Die mitgelieferten Standardfragen funktionieren ohne weitere Konfiguration.</li><li>Für eigene Fragen wählen Sie <em>Eigenes Git-Repository</em>. Als Vorlage dient <a href="https://github.com/jmoskaliuk/content_elediacheckin" target="_blank">github.com/jmoskaliuk/content_elediacheckin</a> — Repository forken, <code>bundle.json</code> an die eigenen Bedürfnisse anpassen und unten die <em>Raw-URL</em> der eigenen Kopie eintragen (Format: <code>https://raw.githubusercontent.com/&lt;user&gt;/content_elediacheckin/main/bundle.json</code>).</li><li>Hinterlegen Sie die <em>Sprach-Fallbacks</em> für Aktivitäten, die keine eigene Sprache vorgeben.</li><li>Änderungen speichern. Danach im Nav-Eintrag <em>Sync-Status</em> auf <em>Sync jetzt ausführen</em> klicken, um das Bundle zu laden.</li></ol></div>';
$string['sourceheading']      = 'Inhaltsquelle';
$string['sourceheading_desc'] = 'Legt fest, woher die Check-in-Fragen kommen. Die mitgelieferten Standardfragen stehen immer als Fallback zur Verfügung.';
$string['contentsource']      = 'Aktive Inhaltsquelle';
$string['contentsource_desc'] = 'Welche Quelle der geplante Synchronisationstask verwendet. Nach einer Änderung muss der Sync erneut laufen, um das neue Bundle zu laden.';
$string['langheading']        = 'Sprach-Fallbacks';
$string['langheading_desc']   = 'Greift, wenn eine Aktivität keine eigene Inhaltssprache vorgibt.';
$string['dashboard_savehint']    = 'Klicken, um alle Einstellungen auf dieser Seite zu speichern.';
$string['dashboard_heading']     = 'Sync-Status';
$string['dashboard_heading_desc'] = 'Aktueller Zustand der aktiven Inhaltsquelle, manuelle Aktionen und letzte Sync-Läufe auf einen Blick.';
$string['dashboard_current']     = 'Aktueller Zustand';
$string['dashboard_activesource'] = 'Aktive Inhaltsquelle: <strong>{$a}</strong>';
$string['dashboard_runnow']      = 'Sync jetzt ausführen';
$string['dashboard_testconnection'] = 'Verbindung testen';
$string['dashboard_testconnection_ok']    = 'Verbindungstest erfolgreich ({$a}).';
$string['dashboard_testconnection_fail']  = 'Verbindungstest fehlgeschlagen ({$a}).';
$string['dashboard_testconnection_error'] = 'Verbindungstest mit Fehler abgebrochen: {$a}';
$string['dashboard_viewlog']     = 'Sync-Log & Verlauf ansehen';
$string['dashboard_saveFirstHint'] = 'Nach Änderungen an der Konfiguration zuerst „Änderungen speichern" klicken, dann den Sync auslösen.';
$string['dashboard_runsuccess']  = 'Sync erfolgreich: {$a->count} Fragen aus Bundle „{$a->bundle}" importiert.';
$string['dashboard_runfailed']   = 'Sync fehlgeschlagen: {$a}';
$string['dashboard_recent']      = 'Letzte Sync-Läufe';
$string['synclog_empty']         = 'Bisher wurden keine Sync-Läufe protokolliert.';
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
$string['repoheading_desc']   = 'Konfiguration der externen Git-basierten Inhaltsquelle. Das Bundle-JSON wird regelmäßig abgerufen und lokal zwischengespeichert. Als Vorlage steht <a href="https://github.com/jmoskaliuk/content_elediacheckin" target="_blank">github.com/jmoskaliuk/content_elediacheckin</a> bereit — Repository forken, <code>bundle.json</code> im Wurzelverzeichnis anpassen und unten die Raw-URL Ihrer Kopie eintragen.';
$string['repourl_example']    = 'Beispiel: <code>https://raw.githubusercontent.com/jmoskaliuk/content_elediacheckin/main/bundle.json</code>';
$string['repourl']            = 'Repository-URL';
$string['repourl_desc']       = 'HTTPS-URL des Raw-JSON-Bundles (z. B. <code>https://raw.githubusercontent.com/&lt;user&gt;/content_elediacheckin/main/bundle.json</code>). <strong>Keine</strong> <code>.git</code>-Clone-URL — die Datei wird direkt über HTTPS geladen.';
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

// eLeDia-Premium-Settings.
$string['premiumheading']      = 'eLeDia Premium (Lizenzserver)';
$string['premiumheading_desc'] = 'Kuratierte, signierte Premium-Fragen, die regelmäßig über den eLeDia-Lizenzserver ausgeliefert werden. Jedes Bundle wird vor dem Import gegen einen fest im Plugin hinterlegten ED25519-Public-Key verifiziert — auch ein kompromittierter Server kann keine gefälschten Fragen einspielen.';
$string['licenseserverurl']      = 'Lizenzserver-URL';
$string['licenseserverurl_desc'] = 'Basis-URL des eLeDia-Lizenzservers, ohne abschließenden Slash. Produktiv: <code>https://licenses.eledia.de</code>. Für lokale Tests zeigt der MVP-Server im Workspace unter <code>/license_server/</code> auf z. B. <code>http://host.docker.internal:8787</code>.';
$string['licensekey']            = 'Lizenzschlüssel';
$string['licensekey_desc']       = 'UUID, die Sie beim Kauf erhalten haben. Wird bei jeder Synchronisation an den Lizenzserver gesendet; der Server prüft Gültigkeit, Ablauf und <code>max_installs</code>-Limit.';

// Content-source error messages.
$string['contenterror_bundlemissing'] = 'Das mitgelieferte Standard-Bundle wurde nicht gefunden. Bitte Plugin-Installation prüfen.';
$string['contenterror_bundleread']    = 'Das mitgelieferte Standard-Bundle konnte nicht gelesen werden.';
$string['contenterror_bundleparse']   = 'Das mitgelieferte Standard-Bundle enthält ungültiges JSON.';
$string['contenterror_bundleinvalid'] = 'Das mitgelieferte Standard-Bundle entspricht nicht dem erwarteten Schema.';
$string['contenterror_eledia_nourl']     = 'Für eLeDia Premium ist keine Lizenzserver-URL konfiguriert.';
$string['contenterror_eledia_nokey']     = 'Für eLeDia Premium ist kein Lizenzschlüssel konfiguriert.';
$string['contenterror_eledia_http']      = 'Der Lizenzserver ist nicht erreichbar oder liefert einen Fehler zurück.';
$string['contenterror_eledia_rejected']  = 'Der Lizenzserver hat den angegebenen Schlüssel abgelehnt (ungültig, abgelaufen oder max_installs erreicht).';
$string['contenterror_eledia_parse']     = 'Die Antwort des Lizenzservers enthält kein gültiges JSON.';
$string['contenterror_eledia_sigmalformed'] = 'Die Signatur des Premium-Bundles ist formal ungültig.';
$string['contenterror_eledia_sigfailed']    = 'Die ED25519-Signatur des Premium-Bundles stimmt nicht mit dem hinterlegten Public-Key überein. Import abgebrochen.';
$string['contenterror_eledia_schema']       = 'Das Premium-Bundle entspricht nicht dem erwarteten Schema.';

$string['privacy:metadata']   = 'Die Aktivität "Check-in" speichert keine personenbezogenen Daten. Fragen werden nur angezeigt, Antworten werden nicht erfasst.';

// User-Tour für Lehrkräfte (tool_usertours, ausgeliefert über db/tours/teacher_checkin_tour.json).
$string['checkintour_name']          = 'Check-In für Lehrkräfte';
$string['checkintour_description']   = 'Kurze Tour durch die Check-in-Aktivität: Karte, Ziel-Picker, Nächste Frage und Präsentationsmodus.';
$string['checkintour_endlabel']      = 'Verstanden';
$string['checkintour_step1_title']   = 'Willkommen beim Check-In';
$string['checkintour_step1_content'] = 'Diese Aktivität zeigt Ihren Teilnehmer:innen kurze Impulse, Reflexionsfragen oder Zitate — ideal zum Start, Ende oder für Retrospektiven einer Sitzung.';
$string['checkintour_step2_title']   = 'Die Karte';
$string['checkintour_step2_content'] = 'Jeder Seitenaufruf zeigt eine zufällig passende Frage oder einen Impuls aus dem konfigurierten Pool. Die Inhalte kommen zentral aus dem eLeDia-Content-Repository; Sie können pro Aktivität eigene Fragen ergänzen.';
$string['checkintour_step3_title']   = 'Ziel wechseln';
$string['checkintour_step3_content'] = 'Wenn Ihre Aktivität mehrere Ziele kombiniert (z. B. Check-in und Check-out), können Sie hier zwischen ihnen wechseln.';
$string['checkintour_step4_title']   = 'Nächste Frage';
$string['checkintour_step4_content'] = 'Klicken Sie hier, um eine neue zufällige Karte aus dem Pool zu ziehen. Keine Karte wird zweimal hintereinander gezogen.';
$string['checkintour_step5_title']   = 'Vollbild & Popup';
$string['checkintour_step5_content'] = 'Für Präsentationen per Screen-Share: Vollbild (groß auf dem ganzen Bildschirm) oder Popup (in einem eigenen schlanken Fenster). Beide eignen sich perfekt für Videocalls.';

// Block-Health-Check im Dashboard-Panel (klein oben in der Settings-Seite).
$string['blockhealth_title']        = 'Begleit-Plugin „Check-In Block":';
$string['blockhealth_ok']           = 'installiert und verfügbar.';
$string['blockhealth_hidden']       = 'ist installiert, aber in „Blöcke verwalten" verborgen — Lehrkräfte können ihn deshalb nicht hinzufügen.';
$string['blockhealth_hidden_cta']   = 'Jetzt sichtbar schalten →';
$string['blockhealth_missing']      = 'ist nicht installiert. Ohne diesen Block fehlt auf Kurs- und Startseite der Launcher für die Check-in-Aktivität.';
$string['blockhealth_missing_cta']  = 'Zu den Admin-Benachrichtigungen →';

// User-Tour für Admin-Settings-Seite (tool_usertours, ausgeliefert über db/tours/settings_checkin_tour.json).
$string['settingstour_name']          = 'Check-In Einstellungen';
$string['settingstour_description']   = 'Kurze Tour durch die Plugin-Einstellungen: Inhaltsquelle, Speichern, Sync-Status und Log.';
$string['settingstour_endlabel']      = 'Alles klar';
$string['settingstour_step1_title']   = 'Plugin-Einstellungen im Überblick';
$string['settingstour_step1_content'] = 'Diese Seite ist der zentrale Einstiegspunkt für Admins. In fünf Schritten zeigen wir Ihnen, wo Sie die Inhaltsquelle wählen, wie Sie speichern und wo Sie den aktuellen Sync-Status einsehen können.';
$string['settingstour_step2_title']   = 'Inhaltsquelle wählen';
$string['settingstour_step2_content'] = 'Hier entscheiden Sie, woher die Fragen kommen: <strong>Bundled default</strong> nutzt die mit dem Plugin ausgelieferten Startfragen — ideal, um sofort loszulegen. <strong>Custom git repository</strong> erlaubt Ihnen, eigene Fragenkataloge aus einem Git-Repository anzubinden.';
$string['settingstour_step3_title']   = 'Änderungen speichern';
$string['settingstour_step3_content'] = 'Nach jeder Konfigurationsänderung hier speichern. Wichtig: die Sync-Status-Karte unterhalb aktualisiert sich erst, nachdem Sie gespeichert haben und ein neuer Sync gelaufen ist.';
$string['settingstour_step4_title']   = 'Aktueller Sync-Zustand';
$string['settingstour_step4_content'] = 'Die Karte zeigt die aktive Inhaltsquelle und bietet zwei Quick-Actions: <strong>Sync jetzt ausführen</strong> holt die aktuellen Fragen ins System, <strong>Verbindung testen</strong> (nur bei Git-Quelle) prüft ohne Import, ob das Repository erreichbar ist.';
$string['settingstour_step5_title']   = 'Letzte Sync-Läufe';
$string['settingstour_step5_content'] = 'Die Tabelle listet die letzten 15 Sync-Versuche mit Zeitstempel, Quelle, Ergebnis (grün = OK, rot = Fehler) und Anzahl importierter Fragen. Bei Rot bitte die Fehlermeldung in der letzten Spalte lesen. Der stündliche Cron-Task läuft automatisch im Hintergrund, Sie müssen nicht manuell nachsyncen.';

// User-Tour für das Aktivitäts-Formular von Lehrkräften (tool_usertours, ausgeliefert über db/tours/activity_settings_tour.json).
// Wird auf /course/modedit.php gezeigt, wenn eine eLeDia-Check-in-Aktivität bearbeitet wird — führt durch die
// Check-in-spezifischen Felder (Ziele, Kategorien, Zielgruppe, eigene Fragen) und endet am Speichern-Button.
$string['activitytour_name']          = 'Check-In Aktivitäts-Einstellungen';
$string['activitytour_description']   = 'Tour durch die Check-in-spezifischen Einstellungen einer Kursaktivität für Lehrkräfte.';
$string['activitytour_endlabel']      = 'Alles klar';
$string['activitytour_step1_title']   = 'Check-in-Aktivität konfigurieren';
$string['activitytour_step1_content'] = 'Willkommen! Wir zeigen Ihnen die Check-in-spezifischen Felder, die Sie gleich konfigurieren werden. Alles andere auf dieser Seite (Name, Beschreibung, Verfügbarkeit) funktioniert wie bei jeder anderen Moodle-Aktivität.';
$string['activitytour_step2_title']   = 'Check-in-Einstellungen';
$string['activitytour_step2_content'] = 'Alle Felder, die steuern, welche Karten Ihre Teilnehmer:innen sehen, stehen in diesem Abschnitt. Alles oberhalb ist Standard-Moodle-Metadatei der Aktivität.';
$string['activitytour_step3_title']   = 'Ziele';
$string['activitytour_step3_content'] = 'Wählen Sie ein oder mehrere Ziele, aus denen die Aktivität ziehen soll — z. B. Check-in am Sitzungsstart, Retrospektive am Ende oder Lern-Reflexion. Sie können mehrere Ziele kombinieren; Ihre Teilnehmer:innen sehen dann einen Mix.';
$string['activitytour_step4_title']   = 'Kategorien';
$string['activitytour_step4_content'] = 'Schränken Sie den Pool weiter ein, indem Sie Kategorien wählen (z. B. Stimmung, Energie, Fokus). Die Liste aktualisiert sich automatisch anhand der oben gewählten Ziele. Leer lassen, um alle Kategorien der gewählten Ziele zuzulassen.';
$string['activitytour_step5_title']   = 'Zielgruppe (optional)';
$string['activitytour_step5_content'] = 'Optional können Sie die Aktivität auf eine bestimmte Zielgruppe einschränken — z. B. Führungskräfte, Grundschule. Karten ohne Zielgruppen-Tag gelten als allgemein und werden immer gezogen. Auf „Alle Zielgruppen" lassen, wenn die Aktivität für alle passen soll.';
$string['activitytour_step6_title']   = 'Eigene Fragen';
$string['activitytour_step6_content'] = 'Entscheiden Sie, wie Ihre persönlichen Impulse mit dem Site-Bundle interagieren: <strong>Mixed</strong> mischt Ihre Impulse in den Pool (Standard), <strong>Nur eigene</strong> ignoriert das Bundle komplett, <strong>Keine eigenen</strong> legt Ihren Textbereich vorübergehend beiseite. Das Textfeld erscheint direkt darunter, sobald ein passender Modus gewählt ist.';
$string['activitytour_step7_title']   = 'Speichern';
$string['activitytour_step7_content'] = 'Speichern, um die Aktivität anzulegen. Sie können jederzeit zurückkommen und die Einstellungen anpassen — Teilnehmer:innen übernehmen die neue Konfiguration beim nächsten Besuch.';
