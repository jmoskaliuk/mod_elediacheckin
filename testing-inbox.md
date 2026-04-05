# Testing-Inbox – mod_elediacheckin

Niedrigschwellige Sammelstelle für Ideen, Beobachtungen und Bugs, die
Johannes beim Testen der Plugins findet. Claude liest sie zu Beginn jeder
Session und arbeitet sie ab.

## So funktioniert diese Datei

- **🆕 Neu** ist Dein Bereich. Einfach reinschreiben, ohne Anspruch auf
  Formulierung. Stichwort + optional kurzer Kontext reicht. Datum davor
  wenn Du magst, muss aber nicht.
- **❓ Klärung notwendig** ist Claudes Bereich für Rückfragen. Wenn Claude
  beim Umsetzen auf eine Designentscheidung stößt, die er nicht allein
  treffen will, landet der Punkt hier mit einer konkreten Frage. Du
  antwortest direkt unter dem Punkt, Claude arbeitet ihn dann ab.
- **🔧 In Arbeit** zeigt, was Claude gerade umsetzt. Wird von Claude
  gepflegt.
- **✅ Erledigt** ist das Archiv, jeweils mit Commit-Hash. Kann alle paar
  Wochen nach unten weggekürzt werden.

Workflow: Du schreibst unter „Neu" weiter, während Claude an etwas
anderem arbeitet. Zu Beginn jeder Session triagiert Claude die Inbox
(Bug → sofort, Designfrage → „Klärung notwendig", Phase 2 → verschieben),
bündelt verwandte Punkte und setzt sie um.

---

## 🆕 Neu

- Wenn der Block auf der Startseite ist, welche Checkin Aktivität wird an ausgeählt? Wa brauchte s noch ein Konzept.

_(offene Punkte — siehe oben)_

## ❓ Klärung notwendig
  


## 🔧 In Arbeit

_(leer)_

## ✅ Erledigt

- **Barrierefreiheits-Pass.** View-Seite gegen WAI-ARIA-Checkliste gezogen:
  Ziel-Picker ist jetzt `<nav aria-label>` mit `aria-current="page"` statt
  rollenlosem `<div>`; die Fullscreen-Overlay ist ein echtes Modal
  (`role="dialog"`, `aria-modal`, `aria-labelledby` auf visuell
  versteckten h2) mit vollem Focus-Management in `view.js` (previousFocus
  speichern → Close-Button fokussieren → Tab/Shift-Tab trappen → Fokus
  restaurieren). `:focus-visible`-Regeln in `styles.css` für alle
  interaktiven Plugin-Controls (3 px Orange-Outline + 5 px Ring). `lang`-
  Attribut auf Fragekarte (wichtig für englische Zitate auf DE-Instanz).
  Konzept-Doc §10.20.
- **Premium für Release ausgeblendet.** `feature_flags::PREMIUM_ENABLED`
  auf `false` geflippt. Dropdown-Eintrag + Settings-Block + Registry-
  Registrierung verschwinden; Klassen bleiben ladbar für PHPUnit.
- **Save-Changes-Button über Sync-Status-Panel.** Der `dashboard_renderer`-
  Output enthält am Ende einen kleinen `<script>`-Block, der
  `#admin-dashboardpanel` per DOM-Reorder hinter den Form-Level-Container
  des Submit-Buttons verschiebt. Ohne JS steht das Panel oberhalb —
  graceful degradation. Umbau auf `admin_externalpage` bewusst vermieden.
- **`$CFG`-Scope-Bug im Verbindungstest.** `git_content_source::fetch_raw()`
  hatte `require_once($GLOBALS['CFG']->libdir . '/filelib.php')` ohne
  `global $CFG;` davor. filelib.php's Top-Level ruft selbst
  `require_once($CFG->libdir . '/…')` — und `$CFG` war im Methoden-Scope
  nicht deklariert, was die Fehlerkaskade „Undefined variable $CFG →
  `/filestorage/file_exceptions.php` not found" auslöste. Fix: `global
  $CFG;` + Kommentar, warum das nicht wieder rausrefactored werden darf.
- **Sync-Status-Panel wieder auf der Einstellungsseite.** Der zweite Nav-
  Eintrag „Sync-Status" ist entfernt; der `dashboard_renderer`-Output ist
  jetzt direkt als letztes Heading der Plugin-Settings-Seite eingebettet,
  unterhalb der Konfig-Felder. actions.php redirected jetzt zurück auf
  die Settings-Seite statt ins Dashboard, damit Success/Error-Toast und
  aktualisierte Log-Tabelle im selben Screen sichtbar werden. Technisch
  steht das Panel weiterhin *über* dem Save-Changes-Button — core-Moodle
  rendert den Save-Button immer als letztes Element einer
  admin_settingpage. Johannes' Wunsch „nach Save-Button" ließe sich nur
  mit einem kompletten Umbau auf admin_externalpage umsetzen; der
  Tradeoff wurde im Konzept-Doc §10.19 dokumentiert. admin/dashboard.php
  bleibt als Redirect-Ziel für Legacy-Bookmarks bestehen.
- **Learning-Content als Reflexionsfragen reformuliert.** Ziel `learning`
  ist jetzt „Lernreflexion": offene Reflexionsimpulse ohne Musterantwort
  (`hat_antwort: false`). Kategorien komplett neu (`tagesreflexion`,
  `transfer`, `aha`, `hindernis`, `meta`) — die alten Lexikon-Kategorien
  (methode, theorie, tool, modell) wurden entfernt. Betroffene Dateien:
  `schema.json`, `schema_validator.php`, `default.json`, `bundle.json`,
  lang/de + lang/en. Neue `ziel_learning_help`-Erklärung.
- **Tri-state „Eigene Fragen"-Modus.** Yes/No-Toggle war missverständlich;
  neue 3-Wege-Auswahl `ownquestionsmode`: 0 = gemischt mit Bundle
  (Default), 1 = nur eigene Fragen, 2 = Bundle-only (eigene Fragen
  ignorieren auch wenn Feld gefüllt). Spalte via `rename_field()`
  umbenannt — existierende 0/1-Werte bleiben erhalten. `hideIf` blendet
  das Textarea aus wenn `none` gewählt ist. Section „Eigene Fragen"
  steht jetzt nach „Anzeigeoptionen" in mod_form.
- **Block auch auf der Startseite.** `applicable_formats()` liefert jetzt
  `site-index => true` + `site => true` zusätzlich zu `course-view`.
  Dropdown im Edit-Form zieht Check-in-Activities aus der Frontpage
  (SITEID) über `get_fast_modinfo($COURSE)`, das auf der Startseite
  korrekt auflöst.
- **Zitate mit Autor-Attribution.** Template (view + fullscreen + present)
  rendert bei `ziel === 'zitat'` einen zusätzlichen Autor-Absatz
  (`— Name`) unter dem Zitat, und die Fragekarte bekommt eine
  `--quote`-Klasse (italic serif, zentriert) als visuelle Abgrenzung.
  Im Bundle ist `autor` für Zitate jetzt der tatsächliche Urheber
  (Henry Ford, Steve Jobs, …) statt des Platzhalters „eLeDia Redaktion";
  `quelle` bleibt als Bibliographie-Feld daneben. view.php + present.php
  füllen `isquote`, `hasauthor` und `author` in den Template-Context.
- **Info-Link im Activity-Chooser.** `modulename_help` in lang/de + lang/en
  um einen Absatz mit `<a href="https://www.eledia.de/mod_elediacheckin"
  target="_blank">` erweitert. Moodle rendert den HTML-Link im Chooser-
  Info-Panel direkt unter der Beschreibung.
- **User-Tour für Lehrkräfte beim ersten Nutzen.** Neues
  `db/tours/teacher_checkin_tour.json` mit 5 Schritten (Welcome →
  Karte → Ziel-Picker → Nächste-Frage-Button → Popup/Fullscreen-
  Launchers). Gefiltert auf Rollen `editingteacher`/`teacher`/`manager`,
  pathmatch `/mod/elediacheckin/view.php%`. Import über
  `\tool_usertours\manager::import_tour_from_json()` im `install.php`-
  Hook + idempotenter Upgrade-Pfad (2026040525), so dass sowohl fresh
  installs als auch bestehende Instanzen die Tour bekommen.
- **Firefox: Popup öffnete neues Fenster statt chrome-less Popup.**
  Ursache: `window.open(url, name, features)` ohne explizites
  `popup=yes` wird von Firefox ≥ 109 (und modernen WebKit-Versionen)
  silently zu einem neuen Tab „upgraded", auch wenn width/height gesetzt
  sind. Fix in `amd/src/view.js` + `amd/build/view.min.js`: `popup=yes`
  als erste Feature-Flag in `POPUP_FEATURES` vorangestellt. Chromium
  toleriert beide Varianten.
- **Aktivitätsbeschreibung wurde doppelt angezeigt.** `view.php` rief
  explizit `$OUTPUT->box(format_module_intro(...))` auf, gleichzeitig
  rendert Moodles `$PAGE->activityheader` die Intro seit Moodle 4.x
  schon automatisch. Der zweite (graue) Kasten ist jetzt gelöscht; ein
  Kommentar in view.php hält fest, warum hier bewusst nichts mehr
  ausgegeben wird.
- **Premium/License-Server-Option per Build-Flag ausblendbar.** Neue
  `classes/feature_flags.php` mit einer einzigen Konstante
  `PREMIUM_ENABLED`. Wenn `false`, wird weder der Dropdown-Eintrag
  „eLeDia Premium" noch der Konfigurationsblock (Server-URL, License-Key)
  in den Admin-Settings gerendert, und `content_source_registry` hängt
  `eledia_premium_content_source` gar nicht erst ein. Die Klassen selbst
  bleiben ladbar, damit PHPUnit sie weiterhin ausführen kann. Release-
  Build-Workflow: ein `sed`-Einzeiler vor dem `git archive`. Konzept-
  Doc §10.18 erklärt Motivation + Mechanik. Johannes' Wunsch aus der
  Inbox („für die erste Version rausnehmen, aber intern weiterbauen").
- **Sync-Status-Curl-Block auf 127.0.0.1:8787 entfernt.** Moodles
  `curl_security_helper` blockt per Default `127.0.0.1` als SSRF-Schutz.
  Für den lokalen License-Server-Test via Docker-Compose wurde
  `curlsecurityblockedhosts` geleert und `8787` zu
  `curlsecurityallowedport` hinzugefügt. Produktiv ist das egal, weil
  `licenses.eledia.de` sowieso auf 443 läuft.
- **Sync-Now-Button wieder auf der Settings-Seite sichtbar.** Nach dem
  Dashboard-Split war der Button nur noch auf der Sync-Status-Externalpage
  erreichbar, die man im Site-Admin-Nav aber leicht übersieht. Fix: ein
  Quick-Actions-Panel oben auf der Settings-Seite zeigt die aktive
  Inhaltsquelle + „Sync jetzt ausführen" + „Sync-Log & Verlauf ansehen"
  (Deep-Link ins Dashboard) + bei Git-Quelle zusätzlich „Verbindung testen".
  Save-Changes-Button bleibt weiterhin direkt unter den Config-Feldern.
- **Phase 2 License-Server-MVP komplett gebaut** — plugin-seitig:
  `bundle_signature_verifier` (ED25519 via libsodium), `eledia_premium_content_source`
  (verify → bundle + sig download → verify → schema-check), Registry-Eintrag,
  Admin-Settings `licenseserverurl` + `licensekey` (hide_if an `contentsource`),
  Lang-Strings DE/EN. Server-seitig (`/license_server`): `public/index.php`,
  `src/{Database,TokenMinter,VerifyController,BundleController,helpers}.php`,
  `bin/{generate-keypair,sign-bundle,create-license,seed-demo}.php`.
  Demo-Keypair seeded, Demo-Bundle signiert, Public Key in den Verifier
  eingepflegt. Kompletter Konzept-Abschnitt §10.17 geschrieben.
- **Icon auf lucide mirror-round umgestellt** (`pix/icon.svg`) — circle + Linien
  entsprechend der Lucide-Struktur. Johannes' Wunsch aus der Inbox.
- Sync-Status-Dashboard auf eigene admin_externalpage ausgelagert. Die
  Einstellungs-Seite enthält jetzt nur noch Konfiguration; der Save-
  Changes-Button sitzt direkt unter dem letzten Feld. „Sync-Status" ist
  ein zweiter Nav-Eintrag im Site Admin neben „Einstellungen". —
  Commit `bd46b48`
- Default-Repo-URL im Admin-Setting ist jetzt die Raw-JSON-URL
  (`https://raw.githubusercontent.com/jmoskaliuk/content_elediacheckin/main/bundle.json`)
  statt der .git-Clone-URL, die curl HTML hätte liefern lassen.
  Quickstart-Anleitung + Feld-Beschreibungen klargestellt: bundle.json
  im Repository-Root, Raw-URL, nicht `.git`. — Commit `bd46b48`
- `$PAGE->set_url()`-Debug-Warning beim Sync-Flash-Redirect behoben:
  admin/actions.php setzt URL + Context vor dem `redirect()`-Aufruf. —
  Commit `bd46b48`
- Bundled default.json + content_elediacheckin bundle.json tragen jetzt
  auf allen 51 Fragen `zielgruppe[]` (team/fuehrungskraefte, teils auch
  grundschule) und `kontext[]` (arbeit/schule/hochschule/privat). Damit
  funktionieren die Activity-Level-Filter gegen echte Daten. — Commits
  `bd46b48` (mod) + `1c7cf6c` (content)
- Prev-Button-Layout angeglichen (Ghost-Variante, weißer Hintergrund
  + oranger Rand) und erscheint erst nach explizitem „Nächste Frage"-
  Klick statt bei jedem Page-Load. — Commit `31b2b0c`
- Block-Launch pinnt jetzt die im Block-Preview gezeigte Frage: Block
  hängt `?q=<externalid>&activeziel=<ziel>` an die Launch-URLs,
  `view.php`/`present.php` nehmen die Karte über die neue Methode
  `activity_pool::pick_by_externalid()` auf. „Nächste Frage" zieht
  wieder random. — Commits `7b880b5` (mod) + `30d7d29` (block)
- Popup-Formatierung: Moodle-Wrapper-Padding auf `body.pagelayout-popup`
  genullt, ActivityHeader in `present.php` deaktiviert, Karte nutzt
  volle `100vh`. — Commit `878ae16`
- Single-Select für Zielgruppe + Kontext: „Alle Zielgruppen" / „Alle
  Kontexte" als erster Dropdown-Eintrag, Ziele + Kategorien bleiben
  Multi-Select. — Commit `4f450bc`
- „Zur vorherigen Frage"-Button pro Aktivität: neues tinyint-Feld
  `showprevbutton` + `activity_pool::resolve_navigation()` mit 2er-
  Stack in `$SESSION`. One-step back, kein vor/zurück-Paar, view.php
  und present.php teilen sich den State. — Commit `9d483f1`
- „Nur eigene Fragen verwenden"-Toggle im Abschnitt „Eigene Fragen":
  neues tinyint-Feld `onlyownquestions`, `activity_pool::build_pool()`
  kürzt Bundle-Query komplett weg wenn aktiv. — Commit `a4c203a`
- Custom-Repo-URL als Default im Admin-Setting: Bei Inhaltsquelle „Git"
  ist `https://github.com/jmoskaliuk/content_elediacheckin.git` jetzt
  vorbelegt (Happy Path: Quelle auswählen, Sync drücken, fertig).
- Block-Deploy auf Demo-Instanz geklärt: Verzeichnispfad in Moodle 5.x
  public layout ist `public/blocks/elediacheckin/`. `moodle-update.sh`
  Meta-Key `checkin` updated jetzt mod + block atomisch.
- Längere Beschreibung der Aktivität im Aktivitäten-Chooser
  (`modulename_help` in lang/de + lang/en erweitert um Zweck, Konfig-
  Optionen und Verantwortungs-Split zwischen Admin und Teacher). —
  Commit `63c12a2`
- Eigene Fragen pro Aktivität (§10.13): neue `ownquestions`-Spalte,
  `activity_pool` mergt Bundle + eigene additiv, View rendert eigene
  Fragen als FORMAT_PLAIN. — Commit `63c12a2`
- Plugin heißt in der Admin-Übersicht jetzt „eLeDia Check-In"
  (lang/de + lang/en: `pluginname`, `modulename`, `modulenameplural`). —
  Commit `7fcb105`
- Sync-Status-Panel in der Admin-Seite nach unten verschoben. Reihenfolge
  ist jetzt: Intro → Inhaltsquelle → Git-Config → Sprach-Fallbacks →
  Sync-Status. — Commit `7fcb105`
- Mini-Anleitung (Quickstart) als Intro-Block am Anfang der Admin-
  Einstellungen. Nummerierte Schritte, Hinweis auf Beispiel-Repo. —
  Commit `7fcb105`
- Git-Repository-Section wird per `hide_if` komplett ausgeblendet, wenn
  die Inhaltsquelle auf „Default" steht — saubere UI wenn man Bundled
  benutzt. — Commit `7fcb105`
- Beispiel-Repo `github.com/jmoskaliuk/content_elediacheckin` als Fork-
  Vorlage in `repoheading_desc` und im Intro-Quickstart verlinkt. —
  Commit `7fcb105`
- Neues Plugin-Icon: `message-circle-question` aus lucide.dev (Sprechblase
  mit Fragezeichen) statt dem alten Kalender-Check — didaktisch passender
  für „Fragekarte anzeigen". Johannes' Vorschlag „mirror-round" existiert
  in lucide nicht; Alternativen-Liste im Commit-Body. — Commit `ee57a16`
