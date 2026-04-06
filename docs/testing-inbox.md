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

_(leer)_

## ✅ Erledigt

- **v2026040538 — UX-Feedback-Bundle (8 Punkte, verifiziert 2026-04-06).**
  Alle 8 Punkte bestanden: Block sichtbar, Kartentext, Button-Label,
  Weiter/Zurück-Navigation, History-Stack, Exhausted-Einstellung,
  Save-Button, Aktivitäts-Tour.
- **v2026040540 — Vollbild-Weiter + DE-Sie-Audit (verifiziert 2026-04-06).**
  Fullscreen bleibt bei Weiter/Zurück offen. Alle Tour-Texte durchgängig
  Sie-Form.
- **v2026040542 — 4 UX-Fixes (verifiziert 2026-04-06).** Block-Preview,
  Popup gleiche Frage, stabile Frage bei Reload (PRG-Fix `2538adf`),
  leerer Block-Titel.
- **v2026040543 — Schema + Popup-Close + Block-Autor (verifiziert
  2026-04-06).** Autor bei Zitaten, Popup-Close aktualisiert View,
  check_database_schema sauber.
- **v2026040545 — BroadcastChannel-Sync (verifiziert 2026-04-06).**
  View↔Popup bidirektional synchron, Ziel-Picker, mehrfach Weiter.
- **PreChecks (verifiziert 2026-04-06).** PHPCS 0/0, Grunt AMD rebuild
  committet (`c18b6c8`), check_database_schema clean.

- **v2026040544 — Popup-Fernsteuerung (bidirektional, bestätigt
  2026-04-06).** Grundmechanismus funktioniert (View↔Popup Navigation
  via postMessage). Externalid-Sync in v2026040545 nachgezogen
  (BroadcastChannel statt postMessage, gleiche Frage statt unabhängiger
  Zufallsziehung). — Commits `755ab42`, `c6be736`, `30b3dac`
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
