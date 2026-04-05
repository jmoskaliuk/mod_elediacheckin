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
- **🔎 Nach Deploy verifizieren** sammelt Punkte, die Claude gefixt und
  gepusht hat, wo aber noch jemand im Browser nachschauen muss, ob der
  Fix auch wirklich wirkt. Sobald Johannes ein Häkchen drunter setzt,
  wandert der Punkt in „Erledigt".
- **✅ Erledigt** ist das Archiv, jeweils mit Commit-Hash. Kann alle paar
  Wochen nach unten weggekürzt werden.

Workflow: Du schreibst unter „Neu" weiter, während Claude an etwas
anderem arbeitet. Zu Beginn jeder Session triagiert Claude die Inbox
(Bug → sofort, Designfrage → „Klärung notwendig", Phase 2 → verschieben),
bündelt verwandte Punkte und setzt sie um.

---

## 🆕 Neu

_(leer)_

## ❓ Klärung notwendig

_(leer)_

## 🔧 In Arbeit

_(leer)_

## 🔎 Nach Deploy verifizieren

Mit `~/moodle-update.sh checkin` deployen, dann der Reihe nach durchgehen.
Häkchen oder Fehlermeldung unter den jeweiligen Punkt schreiben — Claude
räumt dann ggf. nach.

- **v2026040537 — Bundled Fixes aus dem ersten PHPUnit-Run.** Sechs
  Themen in einem Commit:
  (1) `@covers`-Docblocks in allen 4 Testklassen → `#[CoversClass]`-
  Attribute (PHPUnit 11 deprecated Docblock-Metadata). Nach Deploy
  einmal `docker compose -f ~/demo/compose.yml exec -T -w
  /var/www/site/moodle webserver vendor/bin/phpunit -c phpunit.xml
  --testsuite mod_elediacheckin_testsuite` laufen lassen — 38 Tests
  müssen grün sein OHNE „PHPUnit Deprecations"-Zeile am Ende.
  (2) XMLDB: 4 Spalten in `elediacheckin_question` (categories,
  zielgruppe, kontext, license) von NOTNULL=true/DEFAULT="" auf
  NOTNULL=false umgestellt. Nach Deploy `php admin/tool/phpunit/cli/
  init.php` neu laufen lassen → keine debugging-Warnings mehr über
  „Invalid default value for …".
  (3) `db/install.php`: Tour-Import guard mit `table_exists
  ('tool_usertours_tours')`. Phpunit init-Output darf keine
  „relation phpu_tool_usertours_tours does not exist" mehr zeigen.
  (4) Save-Button der Settings-Seite: kein DOM-Reorder mehr. Oben im
  Dashboard-Panel erscheint eine `alert alert-light`-Zeile mit
  „Save changes"-Button. Nach Deploy → Site admin → Plugins → Check-in
  öffnen: Du musst oben im grauen Kasten einen Save-Changes-Button
  sehen, der genau wie der untere Button speichert. Kein Layout-
  Glitch, keine leere Fläche mehr. ⚠ der alte Reorder ist komplett
  raus — falls der Button oben NICHT erscheint, purge_caches + Hard
  Reload, dann testen.
  (5) Dritte User-Tour: `activity_settings_tour.json`. Zum Testen
  eine NEUE Check-in-Aktivität im Kurs anlegen (oder eine bestehende
  editieren). Tour muss auf `/course/modedit.php?add=elediacheckin…`
  bzw. `?update=…` automatisch starten und 7 Schritte zeigen
  (Welcome → Check-in-settings-Header → Ziele → Kategorien →
  Zielgruppe → Eigene Fragen → Save-Button). Mit `?lang=en` muss sie
  englisch sein. In Site admin → Appearance → Tours taucht sie als
  „Check-In Aktivitäts-Einstellungen" mit 7 Schritten auf.
  (6) Lang-String-Audit: `close` aus eigenem Sprachfile entfernt,
  `view.php`/`present.php` nutzen jetzt `get_string('closebuttontitle')`
  aus Core. Nach Deploy einmal die View-Seite öffnen → „Schließen"-
  Button muss weiterhin lesbar sein.
  Konzept §10.29.
- **v2026040536 — Prechecks + PHPUnit + Behat Scaffold.** Kein Runtime-
  Change, aber ein ganzer Testblock kommt dazu: `.github/workflows/
  moodle-ci.yml` mit dem Standard-`moodle-plugin-ci`-Pipeline (phplint,
  phpcs, phpdoc, validate, savepoints, mustache, grunt, PHPUnit, Behat)
  gegen Moodle 4.5 (PHP 8.2) + 5.0 (PHP 8.3). Vier Unit-Test-Klassen
  unter `tests/`: `schema_validator_test`, `bundle_signature_verifier_test`,
  `feature_flags_test`, `activity_pool_test`. Drei Behat-Features unter
  `tests/behat/`: `golden_path.feature`, `settings_dashboard.feature`,
  `block_and_tour.feature`. Verifikation passiert auf GitHub Actions —
  nach dem nächsten Push solltest du im Tab „Actions" den Run sehen.
  Lokal optional via Docker:
    - `docker exec -u www-data -it moodle php admin/tool/phpunit/cli/init.php`
    - `docker exec -u www-data -it moodle vendor/bin/phpunit --testsuite mod_elediacheckin_testsuite`
  Wenn PHPUnit-Output grün und der CI-Run grün ist → Haken. Konzept §10.28.
- **v2026040535 — Companion-Block-Health-Check.** Oben im Sync-Status-
  Panel der Settings-Seite erscheint jetzt eine kleine Alert-Zeile:
  grün „Begleit-Plugin aktiv v2026040503" wenn alles ok, gelb
  „installiert aber verborgen → jetzt sichtbar schalten" wenn
  `mdl_block.visible=0`, rot „nicht installiert → admin/index.php" bei
  fehlendem Record. Nach Deploy: grüne Zeile muss oben erscheinen.
  Optional als Test: in Site admin → Plugins → Blöcke → Blöcke
  verwalten den Check-In-Block auf „verborgen" toggeln, Settings-Seite
  neu laden → gelber Alert mit funktionierendem CTA-Link. Danach wieder
  sichtbar schalten. Konzept §10.27.
- **v2026040534 — Admin-Settings-User-Tour.** Zweite Tour, parallel zur
  Lehrkräfte-Tour, führt Admins durch die Plugin-Einstellungsseite.
  Fünf Schritte: Welcome → Inhaltsquelle → Save → Sync-State-Card →
  Log-Tabelle. Pathmatch
  `/admin/settings.php?section=modsettingelediacheckin%`, Rollen
  `-1` + `manager`, alle Texte als Lang-String-Refs, 13 neue Strings
  in `lang/de` + `lang/en`. Upgrade-Step 2026040534 löscht beide
  bundled Tours und importiert neu. Nach Deploy in Site admin →
  Appearance → Tours muss „Check-In Einstellungen" mit 5 Schritten
  erscheinen. Auf der Settings-Seite selbst (als Site-Admin) unten
  rechts „Reset user tour on this page" → Tour durchlaufen. Mit
  `?lang=en` muss sie englisch sein. Konzept §10.26.
- **v2026040533 — Save-Button-Reorder-Regression gefixt.** Mein
  v2026040532-Walk-Up lief zu weit (beide Walks landeten beim
  `<fieldset>`, `insertBefore` war No-op, Save rutschte unter die
  Log-Tabelle). Jetzt läuft Submit hoch, bis sein Parent das Panel
  enthält; dieser gemeinsame Container ist die Basis für den Reorder.
  Nach Deploy: Save-Button oben, darunter `<h3>Sync status</h3>` mit
  sichtbarem Abstand, dann Panel-Card, dann Log-Tabelle.
- **v2026040532 — Sync-Status-Heading wandert mit + Abstand über Panel.**
  Nach Deploy von v2026040531 war der Save-Button korrekt oben, aber
  die `Sync status`-h3 blieb verwaist darüber liegen und „Current state"
  klebte direkt am Button. Jetzt verschiebt das JS den gesamten
  Form-Item-Container (h3 + Panel gemeinsam) und setzt `margin-top:
  2.5rem` auf den verschobenen Block. Nach Deploy: Heading direkt über
  „Current state", sichtbare Luft zwischen Save-Button und Heading.
  Konzept §10.25.
- **v2026040531 — Save-Button oberhalb Sync-Status-Panel (Fix 2).**
  v2026040528-Fix griff nicht, weil `admin_setting_heading` keinen
  Wrapper mit `id="admin-<name>"` rendert. Jetzt umschließt
  `dashboard_renderer` den kompletten Panel-Output in
  `<div id="elediacheckin-dashboardpanel">`, das JS sucht die neue ID
  und hat zusätzlich einen MutationObserver-Fallback + 5 s-Timeout.
  Nach Deploy: Save-Button muss *über* dem Sync-Log stehen. Falls
  nicht: Purge caches + hart refreshen.
- **v2026040531 — Lehrkräfte-Tour wird Site-Admins angezeigt.**
  Role-Filter enthielt nur `editingteacher/teacher/manager`. Site-Admins
  matchen das nicht — `tool_usertours` prüft den Sentinel `-1`
  (`ROLE_SITEADMIN`). Role-Array jetzt auf
  `["-1","coursecreator","manager","teacher","editingteacher"]`.
  Nach Deploy: als Site-Admin auf `/mod/elediacheckin/view.php?id=…`
  sollte das „Reset user tour on this page" unten rechts erscheinen
  und die Tour beim Auslösen 5 Schritte durchlaufen.
- **v2026040531 — Tour-Texte auf Englisch im EN-Paket.**
  Alle Tour-Textfelder waren hartkodiertes Deutsch. Jetzt als
  `stringid,mod_elediacheckin`-Refs, die `tool_usertours` über
  `helper::get_string_from_input()` auflöst. 15 neue Strings in
  `lang/de` + `lang/en`. Upgrade-Step 2026040531 reimportiert die Tour.
  Nach Deploy mit `?lang=en`: Tour-Schritte müssen englisch sein.
- **v2026040530 — Premium wirklich ausgeblendet.** Peinlicher Fix: der
  `PREMIUM_ENABLED`-Flip war nur im lokalen Workspace, nicht im Repo.
  Jetzt auf `false` committet. Im Admin → Einstellungen → eLeDia Check-In
  sollte der Dropdown „Inhaltsquelle" nur noch *Bundled default* und
  *Custom git repository* zeigen, und der Abschnitt „eLeDia Premium
  (license server)" darf komplett weg sein.
- **v2026040529 — Lehrkräfte-Tour nicht mehr leer.** Tour-JSON-Format
  gegen Moodle-Core-Referenz angeglichen + Upgrade-Step löscht die alte
  kaputte Tour und importiert neu. In Site admin → Appearance → Tours
  sollte „Check-In für Lehrkräfte" jetzt 5 Schritte zeigen. Filter:
  editingteacher/teacher/manager auf `/mod/elediacheckin/view.php%`. Wenn
  du die Tour auf einer Check-in-Aktivität auslöst, sollte sie Welcome →
  Karte → Ziel-Picker → Nächste Frage → Fullscreen/Popup durchlaufen.
- **v2026040529 — Karten-Stage volle Breite.** `.elediacheckin-stage`
  nutzt jetzt die komplette Content-Spalte, keine 760 px-Einrückung mehr.
  Im direkten Vergleich zu LeitnerFlow sollten Header, Intro und
  Fragekarte auf derselben linken Kante sitzen.
- **v2026040528 — Save-Changes-Button über Sync-Status-Panel.** Inline-
  `<script>` im `dashboard_renderer` verschiebt das Panel per DOM-Reorder
  hinter den Submit-Container. In den Plugin-Einstellungen muss der „Save
  changes"-Button jetzt sichtbar oberhalb des Sync-Status-Panels stehen.
  Falls nicht: Site admin → Development → Purge all caches + hart
  refreshen, Browser-Cache killt das gerne mal.
- **v2026040528 — Sync-Diagnose.** Hattest du schon mit „SYNC HAT
  GEKLAPPT" bestätigt. Würde erst beim nächsten Fehlversuch sichtbar:
  Top-Level-Keys der empfangenen JSON + URL-Hinweise (blob/, api.github,
  .git-Endung) in der Fehlermeldung. Wenn Sync jemals wieder rot wird,
  bitte den vollen Fehlertext kopieren.
- **v2026040527 — Barrierefreiheits-Pass.** Ziel-Picker als `<nav>` mit
  `aria-current`, Fullscreen-Overlay als echtes Modal mit Focus-Trap in
  `view.js`, `:focus-visible`-Outlines. Kurz-Test: Tab durch die
  View-Seite, jeder interaktive Control muss eine sichtbare 3 px
  Orange-Outline bekommen. Fullscreen öffnen → Tab bleibt im Overlay →
  Close schließt und Fokus landet wieder auf dem Launcher-Button.

## ✅ Erledigt

- **Premium wirklich ausgeblendet (Konstanten-Flip committet).** Einziger
  Change: `PREMIUM_ENABLED = true` → `false` in `classes/feature_flags.php`.
  Der Flag war seit §10.18 als gefixt dokumentiert, die Konstante selbst
  aber lag nur im Workspace und wurde nie gepusht. — Commit `e1f8952`
- **Lehrkräfte-Tour repariert + Karten-Vollbreite.** Tour-JSON hatte
  `configdata` als verschachteltes Objekt, was in PHP 8 beim
  `json_decode($record->configdata)` einen TypeError warf und die
  Step-Inserts stumm abbrach. Format an Moodle-Core-Referenz-Tour
  angeglichen (configdata als JSON-String, filtervalues nested,
  contentformat="1" pro Step). Upgrade-Step 2026040529 löscht die
  kaputte Tour per pathmatch-LIKE und importiert neu. Karten-Stage
  parallel auf volle Breite umgestellt (`max-width: none`).
  Siehe Konzept §10.23. — Commit `94b7a36`
- **Sync-Error-Diagnostics für Git-Content-Source.** `fetch_bundle()`
  wirft jetzt aussagekräftige Fehlermeldungen mit Top-Level-Key-Liste,
  Body-Preview und URL-Heuristiken (erkennt `/blob/`-URLs,
  `api.github.com/contents/`, `.git`-Endungen, fehlendes `.json`).
  Zusätzlich `global $CFG;`-Scope-Fix in `fetch_raw()`, der den
  Verbindungstest vorher zerlegt hatte. Siehe Konzept §10.22. —
  Commit `ee8beef`
- **Frontpage-Block: welche Aktivität wird ausgewählt?** Design-Entscheidung
  dokumentiert (Konzept-Doc §10.21): Dropdown zeigt auf der Startseite
  exakt die Check-in-Aktivitäten, die **auf der Startseite selbst**
  angelegt sind — dieselbe Logik wie im Kurs, nur mit SITEID als „Kurs".
  Cross-Course-Linking bewusst verworfen (Enrolment/Capability-Mismatch,
  Sichtbarkeits-Leak, Backup-Brüche). UX-Verbesserung: wenn der Dropdown
  leer ist und der Nutzer `moodle/course:manageactivities` hat, rendert
  das Block-Edit-Form eine alert-warning mit Direktlink auf
  `course/modedit.php?add=elediacheckin&course=<SITEID>` — also „Jetzt
  eine Check-in-Aktivität anlegen"-Button statt ratlosem leeren Dropdown.
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
- **Save-Changes-Button über Sync-Status-Panel.** Der `dashboard_renderer`-
  Output enthält am Ende einen kleinen `<script>`-Block, der
  `#admin-dashboardpanel` per DOM-Reorder hinter den Form-Level-Container
  des Submit-Buttons verschiebt. Ohne JS steht das Panel oberhalb —
  graceful degradation.
- **`$CFG`-Scope-Bug im Verbindungstest.** `git_content_source::fetch_raw()`
  hatte `require_once($GLOBALS['CFG']->libdir . '/filelib.php')` ohne
  `global $CFG;` davor. filelib.php's Top-Level ruft selbst
  `require_once($CFG->libdir . '/…')` — und `$CFG` war im Methoden-Scope
  nicht deklariert, was die Fehlerkaskade „Undefined variable $CFG →
  `/filestorage/file_exceptions.php` not found" auslöste.
- **Learning-Content als Reflexionsfragen reformuliert.** Ziel `learning`
  ist jetzt „Lernreflexion": offene Reflexionsimpulse ohne Musterantwort
  (`hat_antwort: false`). Kategorien komplett neu (`tagesreflexion`,
  `transfer`, `aha`, `hindernis`, `meta`).
- **Tri-state „Eigene Fragen"-Modus.** 3-Wege-Auswahl `ownquestionsmode`:
  0 = gemischt mit Bundle (Default), 1 = nur eigene Fragen, 2 = Bundle-
  only. Spalte via `rename_field()` umbenannt.
- **Block auch auf der Startseite.** `applicable_formats()` liefert jetzt
  `site-index => true` + `site => true`.
- **Zitate mit Autor-Attribution.** Template rendert bei `ziel === 'zitat'`
  einen zusätzlichen Autor-Absatz unter dem Zitat, Fragekarte bekommt
  `--quote`-Klasse (italic serif, zentriert).
- **User-Tour für Lehrkräfte** (ursprünglicher Scaffold-Import). —
  Commit `(pre-94b7a36)` — Format-Bug siehe §10.23.
- **Firefox: Popup öffnete neues Fenster.** `popup=yes` in
  `POPUP_FEATURES` vorangestellt.
- **Aktivitätsbeschreibung wurde doppelt angezeigt.** Explizites
  `$OUTPUT->box(format_module_intro(...))` aus view.php entfernt.
- **Premium/License-Server-Option per Build-Flag ausblendbar.** Neue
  `classes/feature_flags.php` mit `PREMIUM_ENABLED`-Konstante (Mechanik —
  der Release-Flip selbst ist erst im Commit `e1f8952` tatsächlich
  passiert, s.o.).
- **Sync-Now-Button wieder auf der Settings-Seite sichtbar.** Quick-
  Actions-Panel oben auf der Settings-Seite zeigt aktive Inhaltsquelle
  + „Sync jetzt ausführen" + „Sync-Log & Verlauf ansehen".
- **Phase 2 License-Server-MVP komplett gebaut** — plugin-seitig
  (verifier, eledia_premium_content_source, Registry, Admin-Settings)
  + server-seitig (`/license_server` mit TokenMinter, Controllers,
  CLI-Tools). Demo-Keypair seeded. Konzept §10.17.
- **Icon auf `message-circle-question` umgestellt.** — Commit `ee57a16`
- **Block-Launch pinnt gezeigte Frage.** `?q=<externalid>&activeziel=<ziel>`.
  — Commit `7b880b5` + `30d7d29`
- **Popup-Formatierung.** `body.pagelayout-popup` Padding genullt,
  ActivityHeader in present.php deaktiviert, 100 vh Karte.
  — Commit `878ae16`
- **„Zur vorherigen Frage"-Button pro Aktivität** mit 2er-Stack in
  `$SESSION`. — Commit `9d483f1`
- **„Nur eigene Fragen verwenden"-Toggle.** — Commit `a4c203a`
- **Plugin heißt „eLeDia Check-In".** — Commit `7fcb105`
- **Mini-Anleitung (Quickstart) als Intro-Block.** — Commit `7fcb105`
- **Git-Repository-Section `hide_if` auf Default-Source.** — Commit
  `7fcb105`
