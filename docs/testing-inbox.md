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

## 🔬 PreCheck-Verifizierung (v2026040541, auf Docker)

Die folgenden Kommandos brauchen PHP und laufen deshalb im Docker-Container.
Bitte nach `~/moodle-update.sh checkin` (v2026040541 Deploy) ausführen und
Ergebnis hier einfügen oder in der Chat-Session melden.

### 1. PHPCS (Moodle Coding Standards)

```
docker compose -f ~/demo/compose.yml exec -T webserver \
  bash -c 'cd /var/www/site/moodle && vendor/bin/phpcs \
    --standard=moodle \
    --extensions=php \
    --ignore=*/vendor/*,*/node_modules/*,*/tests/* \
    public/mod/elediacheckin/'
```

Falls `phpcs` oder der moodle-Standard fehlt:

```
docker compose -f ~/demo/compose.yml exec -T -w /var/www/site/moodle webserver \
  composer require --dev moodlehq/moodle-cs
```

### 2. Grunt AMD-Rebuild (offizielle Moodle-Toolchain)

```
docker compose -f ~/demo/compose.yml exec -T \
  -w /var/www/site/moodle webserver \
  npx grunt amd --root=public/mod/elediacheckin
```

Danach `git diff amd/build/` prüfen — wenn sich nur Whitespace oder
Kommentar-Hashes ändern, ist alles OK. Wenn Funktionslogik abweicht,
muss der Grunt-Build committet werden.

### 3. Savepoints-Check (upgrade.php Konsistenz)

```
docker compose -f ~/demo/compose.yml exec -T webserver \
  php /var/www/site/moodle/admin/cli/check_database_schema.php
```

Suche in der Ausgabe nach `elediacheckin` — keine Fehler = OK.

### 4. install.xml vs. Upgrade-Endstand

```
docker compose -f ~/demo/compose.yml exec -T webserver \
  php /var/www/site/moodle/admin/cli/check_database_schema.php 2>&1 \
  | grep -i elediacheckin
```

Falls Schema-Differenzen auftauchen, install.xml muss nachgezogen werden.

## ❓ Klärung notwendig

_(leer)_

## 🔧 In Arbeit

_(leer)_

## 🔎 Nach Deploy verifizieren

Mit `~/moodle-update.sh checkin` deployen, dann der Reihe nach durchgehen.
Häkchen oder Fehlermeldung unter den jeweiligen Punkt schreiben — Claude
räumt dann ggf. nach.

- **v2026040538 — UX-Feedback-Bundle nach v2026040537-Deploy.** Acht Punkte
  in einem Commit, braucht ein paar Minuten Browser-Test. Konzept §10.30.
  Zum Deployen auch Block aktualisieren: `~/moodle-update.sh checkin` UND
  `~/moodle-update.sh checkinblock`, danach einmal `purge_caches` auf die
  Admin-Seite.
  (1) **Block auf Aktivitätsseite sichtbar.** Auf einer Check-in-Aktivität
  (z.B. `/mod/elediacheckin/view.php?id=…`) muss in der rechten Spalte über
  „Block hinzufügen" der Check-in-Block auswählbar sein. Dasselbe auf der
  Aktivitäts-Einstellungsseite (`course/modedit.php?update=…`) — der
  bereits platzierte Block soll dort sichtbar bleiben und nicht
  ausgeblendet werden.
  (2) **Block-Kartentext.** Kurseite öffnen, Check-in-Block mit
  aktivierter Preview anzeigen lassen → der Fragetext muss wieder in der
  Karte zu sehen sein (war nach v2026040537 leer).
  (3) **Button-Label „Öffnen".** Im Block der primäre Launcher-Button
  heißt jetzt nur noch „Öffnen" (DE) bzw. „Open" (EN), nicht mehr
  „Check-in öffnen".
  (4) **Navigation „Weiter" / „Zurück".** View-Seite einer Aktivität
  öffnen → primäre Navigation sagt „Weiter", sekundäre (falls sichtbar)
  „Zurück". Kein „Nächste Frage" mehr.
  (5) **History-Stack.** Weiter, Weiter, Weiter, Weiter, Weiter → jetzt
  mehrfach Zurück drücken. Der Zurück-Button muss bis zur ersten Karte
  verfügbar bleiben; erst auf der allerersten Karte verschwindet er.
  Wenn man dann wieder Weiter drückt, soll er beim Verlassen der ersten
  Karte sofort wieder erscheinen.
  (6) **Exhausted-Einstellung.** In den Aktivitäts-Einstellungen neue
  Option „Wenn alle Fragen durch sind" mit zwei Werten: „Von vorne
  beginnen" (Default) und „Leere Abschluss-Karte anzeigen". Testen: eine
  Aktivität mit 2 eigenen Fragen und Modus „Nur eigene Fragen" anlegen.
  Einmal auf „Leere Abschluss-Karte" stellen → nach 2× Weiter muss eine
  Karte „Für diese Sitzung sind alle Fragen aus dem Pool durch" kommen.
  Danach umstellen auf „Von vorne beginnen" → nach 2× Weiter muss einfach
  wieder eine der 2 Karten gezogen werden (und zwar zufällig, nicht immer
  dieselbe).
  (7) **Save-Button-Größe.** Admin → Plugins → Check-in: der obere
  Save-Button im grauen Kasten ist jetzt genauso groß wie der untere
  Moodle-Save-Button. Kein `btn-sm`-Unterschied mehr.
  (7b) **Save-Bar-Position (v2026040539).** Die Save-Changes-Zeile muss
  jetzt ÜBER dem Heading „Sync status" / „Sync-Status" erscheinen, nicht
  darunter. Reihenfolge auf der Settings-Seite von oben nach unten:
  Save-Changes-Streifen → „Sync status"-Heading → Companion-Plugin-Zeile
  → „Current state" → Recent log. Ohne JS.
  (8) **Aktivitäts-Settings-Tour.** Neue Check-in-Aktivität im Kurs
  anlegen oder eine bestehende bearbeiten. Auf `/course/modedit.php`
  muss die Tour automatisch starten (7 Schritte: Welcome → Check-in
  settings → Ziele → Kategorien → Zielgruppe → Eigene Fragen →
  Speichern). In Site admin → Appearance → Tours muss „Check-In
  Aktivitäts-Einstellungen" mit 7 Schritten gelistet sein. Wenn sie
  fehlt: Upgrade-Step hat nicht gegriffen — dann bitte den vollen Output
  von `php admin/cli/upgrade.php` schicken.

- **v2026040544 — Popup-Fernsteuerung (bidirektional).** Deploy:
  `~/moodle-update.sh checkin`. Kein Block-Update nötig.
  (7) **View steuert Popup.** View-Seite öffnen → Popup öffnen. Auf der
  View-Seite „Weiter" klicken → das Popup muss automatisch zur selben
  nächsten Frage wechseln, ohne dass man im Popup klickt.
  (7b) **Popup steuert View.** Umgekehrt: im Popup „Weiter" klicken →
  die View-Seite (im Hintergrund) wechselt ebenfalls zur neuen Frage.
  Prüfen: nach Popup-Steuerung die View-Seite anschauen — zeigt sie die
  aktuelle Frage?
  (7c) **Ziel-Picker.** Auf der View-Seite ein anderes Ziel wählen →
  das Popup wechselt ebenfalls das Ziel.

- **v2026040543 — Schema-Fix + Popup-Close-Refresh + Block-Autor.**
  Deploy: `~/moodle-update.sh checkin` UND `~/moodle-update.sh checkinblock`.
  (5) **Block: Autor bei Zitaten.** Auf der Kursseite einen Block mit
  Vorschau öffnen (showpreview=Ja). Wenn ein Zitat angezeigt wird, muss
  der Autorname klein-kursiv unter dem Text stehen.
  (6) **Popup-Close: View aktualisiert sich.** View-Seite öffnen → Popup
  öffnen → im Popup „Weiter" klicken → Popup schließen (× oder Esc).
  Die View-Seite muss jetzt die gleiche Frage zeigen wie das Popup
  zuletzt, nicht mehr den alten Text.
  (Schema) **check_database_schema** nochmal laufen lassen — es sollten
  keine `elediacheckin`-Einträge mehr auftauchen.

- **v2026040542 — 4 UX-Fixes aus dem 2026-04-06-Test.** Vier Punkte,
  alle im Bundle. Deploy: `~/moodle-update.sh checkin` UND
  `~/moodle-update.sh checkinblock`.
  (1) **Block-Fragenvorschau.** `showpreview`-Default von 0 auf 1
  geändert. Neue Blöcke zeigen die Vorschau sofort. Bestehende
  Block-Instanzen einmal in der Block-Konfiguration „Vorschau anzeigen"
  auf Ja umstellen.
  (2) **Popup zeigt gleiche Frage.** Popup-URL enthält jetzt `?q=<id>`
  mit der aktuellen Frage aus der View. „Open as popup" darf keine
  andere Frage zeigen.
  (3) **Frage bleibt stabil beim Zurückkehren.** `resolve_navigation()`
  verwendet die Session-Frage weiter statt bei jedem frischen
  Seitenaufruf eine neue zu ziehen. Erst „Weiter" holt eine neue Frage.
  (4) **Leerer Block-Titel = kein Titel.** `specialization()` +
  `hide_header()` im Block: wenn der Titel in der Block-Konfiguration
  leer gelassen wird, verschwindet die Block-Kopfzeile komplett.

- **v2026040540 — Vollbild-Weiter-Fix + DE-Sie-Audit.** Zwei kleine
  Folgefixes aus dem 2026-04-05-Feedback.
  (1) **Vollbild bleibt beim Weiter-Klick offen.** Auf einer Check-in-
  Aktivität Vollbild öffnen → „Weiter" klicken → das Overlay muss
  offen bleiben, es darf KEIN sichtbarer Flash zurück auf die View-
  Seite passieren. Gleiches bei „Zurück" und beim Ziel-Picker im
  Vollbild-Header. Technisch: der URL-Parameter `fs=1` wird beim Klick
  an den Link angehängt, `view.js` liest ihn beim Laden und öffnet das
  Overlay sofort. Esc und der Schließen-Button (×) schließen weiterhin
  wie gehabt.
  (2) **DE-Lang durchgängig Sie.** Admin-Settings-Seite, Tour-Texte
  (Check-in-Tour, Einstellungs-Tour, Aktivitäts-Einstellungs-Tour),
  Exhausted-Message, Block-Hilfe — keine „du/dich/dein"-Formen mehr,
  alles auf „Sie/Ihnen/Ihre". Quick-Check: Aktivitäts-Tour starten,
  alle Steps lesen; Settings-Tour starten, alle 5 Steps lesen;
  Aktivitäts-Einstellungs-Tour auf modedit.php starten, alle 7 Steps
  lesen. Wenn noch irgendwo ein „du" steht, bitte Stichwort in die
  Neu-Section.

## ✅ Erledigt

- **v2026040537 — Bundled Fixes aus dem ersten PHPUnit-Run (verifiziert
  per Test-Run am 2026-04-05).** Sechs Themen in einem Commit: (1)
  `@covers`-Docblocks in allen 4 Testklassen → `#[CoversClass]`-Attribute
  (PHPUnit 11 deprecation gone — `38 tests, 61 assertions`, keine
  Deprecations mehr). (2) XMLDB: `categories/zielgruppe/kontext/license`
  in `elediacheckin_question` von NOTNULL=true/DEFAULT="" auf
  NOTNULL=false (keine Debugging-Warnings mehr im init-Output). (3)
  `db/install.php`: Tour-Import guard mit `table_exists('tool_usertours_tours')`,
  Fresh-Install-Crash weg. (4) Save-Button der Settings-Seite: inline
  secondary submit über `alert alert-light`-Zeile (kein DOM-Reorder
  mehr). (5) Dritte User-Tour `activity_settings_tour.json`. (6)
  Lang-String-Audit: `close` → Core-String `closebuttontitle`.
  Konzept §10.29. — Commit `3bb7b02`
- **v2026040536 — Prechecks + PHPUnit + Behat Scaffold.** Kein
  Runtime-Change. `.github/workflows/moodle-ci.yml` mit moodle-plugin-ci
  gegen Moodle 4.5/5.0. Vier Unit-Test-Klassen
  (`schema_validator_test`, `bundle_signature_verifier_test`,
  `feature_flags_test`, `activity_pool_test`). Drei Behat-Features
  (`golden_path`, `settings_dashboard`, `block_and_tour`). Erster lokaler
  Lauf erfolgreich: 38 Tests, 61 Assertions. Konzept §10.28. —
  Commit `7aeec32`
- **v2026040535 — Companion-Block-Health-Check.** Sync-Status-Panel
  zeigt jetzt oben eine Alert-Zeile: grün bei aktivem Begleit-Plugin,
  gelb bei „installiert aber verborgen" mit CTA-Link, rot bei fehlendem
  Record mit Link auf `admin/index.php`. Konzept §10.27. — Commit
  `1f2c3df`
- **v2026040534 — Admin-Settings-User-Tour.** Zweite Tour (5 Schritte:
  Welcome → Inhaltsquelle → Save → Sync-State-Card → Log-Tabelle),
  pathmatch `/admin/settings.php?section=modsettingelediacheckin%`,
  Rollen `-1 + manager`, alle Texte als Lang-String-Refs. Upgrade-Step
  2026040534 löscht und reimportiert beide bundled Tours. Konzept
  §10.26. — Commit `06fc195`
- **v2026040533 — Save-Button-Reorder-Regression gefixt.** (in §10.29 dann
  durch die inline-Submit-Strategie komplett ersetzt.) — Commit `4b6c94a`
- **v2026040532 — Sync-Status-Heading wandert mit + Abstand über Panel.**
  Konzept §10.25. — (obsolet seit §10.29 Save-Button-Fix.)
- **v2026040531 — Tour-Texte auf Englisch im EN-Paket + Site-Admin-Fix.**
  Role-Filter um `-1` ergänzt, alle Tour-Textfelder als
  `stringid,mod_elediacheckin`-Refs. Upgrade-Step reimportiert die Tour.
  Konzept §10.24.
- **v2026040530 — Premium wirklich ausgeblendet (Konstanten-Flip
  committet).** — Commit `e1f8952`
- **v2026040529 — Lehrkräfte-Tour repariert + Karten-Vollbreite.** Tour-
  JSON-Format an Moodle-Core-Referenz angeglichen (configdata als
  JSON-String). Karten-Stage auf volle Breite. Konzept §10.23. — Commit
  `94b7a36`
- **v2026040528 — Sync-Diagnose.** Aussagekräftige Fehlermeldungen mit
  Top-Level-Key-Liste, Body-Preview und URL-Heuristiken. Konzept §10.22.
  — Commit `ee8beef`
- **v2026040527 — Barrierefreiheits-Pass.** Ziel-Picker als `<nav>` mit
  `aria-current`, Fullscreen als echtes Modal mit Focus-Trap,
  `:focus-visible`-Outlines. Konzept-Doc §10.20.
- **Frontpage-Block: welche Aktivität wird ausgewählt?** Design-Entscheidung
  dokumentiert (Konzept-Doc §10.21): Dropdown zeigt auf der Startseite
  exakt die Check-in-Aktivitäten, die auf der Startseite selbst angelegt
  sind. Cross-Course-Linking bewusst verworfen.
- **`$CFG`-Scope-Bug im Verbindungstest.** `git_content_source::fetch_raw()`
  hatte `require_once($GLOBALS['CFG']->libdir . '/filelib.php')` ohne
  `global $CFG;` davor.
- **Learning-Content als Reflexionsfragen reformuliert.** Ziel `learning`
  jetzt „Lernreflexion" mit neuen Kategorien.
- **Tri-state „Eigene Fragen"-Modus.** 3-Wege-Auswahl `ownquestionsmode`.
- **Block auch auf der Startseite.** `applicable_formats()` mit
  `site-index => true` + `site => true`.
- **Zitate mit Autor-Attribution.** Template rendert Autor-Absatz nur bei
  `ziel === 'zitat'`.
- **Firefox: Popup öffnete neues Fenster.** `popup=yes` in
  `POPUP_FEATURES` vorangestellt.
- **Aktivitätsbeschreibung wurde doppelt angezeigt.** Explizites
  `$OUTPUT->box(format_module_intro(...))` aus view.php entfernt.
- **Premium/License-Server-Option per Build-Flag ausblendbar.**
- **Sync-Now-Button wieder auf der Settings-Seite sichtbar.**
- **Phase 2 License-Server-MVP komplett gebaut** — Konzept §10.17.
- **Icon auf `message-circle-question` umgestellt.** — Commit `ee57a16`
- **Block-Launch pinnt gezeigte Frage.** `?q=<externalid>&activeziel=<ziel>`.
  — Commit `7b880b5` + `30d7d29`
- **Popup-Formatierung.** `body.pagelayout-popup` Padding genullt,
  ActivityHeader in present.php deaktiviert, 100 vh Karte. — Commit
  `878ae16`
- **„Zur vorherigen Frage"-Button pro Aktivität** mit 2er-Stack in
  `$SESSION`. — Commit `9d483f1` (in v2026040538 durch echten
  Cursor-History-Stack ersetzt, siehe §10.30)
- **„Nur eigene Fragen verwenden"-Toggle.** — Commit `a4c203a`
- **Plugin heißt „eLeDia Check-In".** — Commit `7fcb105`
- **Mini-Anleitung (Quickstart) als Intro-Block.** — Commit `7fcb105`
- **Git-Repository-Section `hide_if` auf Default-Source.** — Commit
  `7fcb105`
