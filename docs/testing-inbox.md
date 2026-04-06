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
bündelt verwandte Punkte und setzt sie ab.

---

## 🆕 Neu

_(leer)_

## ❓ Klärung notwendig

_(leer)_

## 🔧 In Arbeit

_(leer)_

---

## 🔎 Pre-Release 1 — Offene Punkte (Stand 2026-04-06)

Alle Punkte müssen erledigt sein, bevor das Plugin ins Moodle Plugin Directory
eingereicht werden kann. Reihenfolge = empfohlene Abarbeitungsreihenfolge.

---

### 1. Deploy + Behat 9/9 ← nächster Schritt

Aktueller HEAD: commit `55ff467` (Tour-Namen in JSON auf echte EN-Strings geändert).

```
~/moodle-update.sh checkin
```

Dann Behat-Init (Pfad liegt unter `public/`!):

```
docker compose -f ~/demo/compose.yml exec -T webserver \
  php /var/www/site/moodle/public/admin/tool/behat/cli/init.php 2>&1 | tail -5
```

Dann Behat-Lauf (Pfad-basiert, nicht --suite):

```
docker compose -f ~/demo/compose.yml exec -T webserver \
  php /var/www/site/vendor/bin/behat \
    --config /var/www/behatdata/behatrun/behat/behat.yml \
    /var/www/site/moodle/public/mod/elediacheckin/tests/behat/ 2>&1 | tail -25
```

Erwartetes Ergebnis: **9 scenarios, 9 passed**.

---

### 2. Grunt AMD rebuild (Moodle Node >=22 erforderlich)

`category_filter.js` wurde auf ES6 umgeschrieben (commit `c77565d`).
Node fehlt im webserver-Container → separaten Node-22-Container nutzen:

```
docker run --rm \
  -v ~/demo/site/moodle:/work \
  -w /work \
  node:22 \
  node_modules/.bin/grunt amd --root=public/mod/elediacheckin
```

Danach im Mac-Checkout (`~/demo/site/moodle/public/mod/elediacheckin/`):

```
git diff amd/build/
```

Falls Änderungen:
```
git add amd/build/
git commit -m "rebuild: AMD for ES6 category_filter"
git push
```

---

### 3. PHPCS — muss 0 errors, 0 warnings sein

CI nutzt `--max-warnings 0`. Moodle-CS muss installiert sein (`composer` liegt unter `/var/www/site/moodle/composer.phar`):

```
docker compose -f ~/demo/compose.yml exec -T \
  -w /var/www/site/moodle webserver \
  php composer.phar require --dev moodlehq/moodle-cs --no-interaction 2>&1 | tail -5
```

Dann prüfen:

```
docker compose -f ~/demo/compose.yml exec -T \
  -w /var/www/site/moodle webserver \
  vendor/bin/phpcs --standard=moodle --extensions=php \
    --ignore="*/vendor/*,*/node_modules/*,*/tests/*" \
    public/mod/elediacheckin/ 2>&1 | tail -20
```

---

### 4. Deinstallations-Test (nie getestet — Pflicht für Plugin Directory)

1. Plugin deinstallieren: _Site administration → Plugins → Manage activities →
   eLeDia Check-In → Uninstall_.
2. Prüfen ob User-Tours weg:
   _Site administration → Server → User tours_ → keine „Check-in"-Tours mehr.
3. Prüfen ob keine Tabellen übrig:

```
docker compose -f ~/demo/compose.yml exec -T webserver \
  php -r "
    define('CLI_SCRIPT', true);
    require '/var/www/site/moodle/public/config.php';
    global \$DB;
    \$tables = \$DB->get_tables();
    foreach (\$tables as \$t) {
        if (strpos(\$t, 'elediacheckin') !== false) echo \$t . PHP_EOL;
    }
    echo 'Done.' . PHP_EOL;
  "
```

Wenn die Ausgabe nur `Done.` enthält → sauber.
Danach Plugin neu installieren (damit Demo-Instanz wieder läuft).

---

### 5. Backup/Restore-Test (Code vorhanden, nie live getestet)

Backup/Restore-Code ist implementiert, aber nie live getestet.
Approval Blocker für `mod_*` Plugins.

1. Kurs mit einer Check-in-Aktivität anlegen.
2. Kurs-Backup erstellen (_Kursseite → Aktionen → Sichern_).
3. Backup in neuen Kurs wiederherstellen.
4. Check-in-Aktivität im wiederhergestellten Kurs öffnen — Fragen müssen
   sichtbar sein, Einstellungen (Ziele, Kategorien, eigene Fragen) erhalten.

---

### 6. MySQL/MariaDB-Kompatibilitätstest (nur auf PostgreSQL getestet)

Derzeit läuft die Demo-Instanz auf PostgreSQL. Vor der Einreichung muss
das Plugin auch auf MySQL funktionieren (Moodle Plugin Directory Requirement).

Optionen:
- Temporär eine MySQL-Instanz in Docker hochfahren und Moodle damit testen.
- Oder CI auf GitHub Actions abwarten — CI testet gegen PostgreSQL, aber
  moodle-plugin-ci kann auch MySQL. Ggf. Workflow ergänzen.

---

### 7. GitHub Actions CI — muss grün sein

CI läuft automatisch bei jedem Push gegen Moodle 4.5 (PHP 8.2) und 5.0
(PHP 8.3), jeweils PostgreSQL.

Status prüfen:
https://github.com/jmoskaliuk/mod_elediacheckin/actions

Alle Schritte müssen grün sein: phplint, phpcs, phpdoc, savepoints,
mustache, grunt, phpunit, behat.

---

### 8. Screenshots für Plugin Directory

Für die Einreichung werden Screenshots benötigt. Mindestens:
- Aktivitäts-Ansicht (Karte mit Frage)
- Admin-Einstellungsseite
- Block im Kurs-Sidebar

---

## ✅ Erledigt

- **v2026040608 — Behat-Context `behat_mod_elediacheckin.php` + Tour-Fix.**
  `mod_*` installiert vor `tool_usertours` → Behat-DB hatte nie Tours.
  Custom-Step `Given the elediacheckin bundled tours are installed` ruft
  `tour_installer::install_bundled_tours()` explizit auf. — Commit `41e033f`
- **v2026040607 — `category_filter.js` auf ES6 umgeschrieben.**
  `define([], function(){...var...})` → `export const init`. Behebt
  ESLint `no-var`-Fehler in CI (`grunt --max-lint-warnings 0`).
  AMD-Build vorläufig in Sandbox neu gebaut. — Commits `c77565d`, `dbaaeb8`
- **v2026040606 — `db/uninstall.php` + block BETA.**
  Entfernt 3 bundled User-Tours bei Deinstallation. `block_elediacheckin`
  ALPHA → BETA 0.9.0. — Commits `bddbefa`, `c5527b7`
- **v2026040604 — Behat User-Tours-Navigation gefixt.**
  `"Server > User tours"` (ungültig in Moodle 5.x) → direkter URL
  `/admin/tool/usertours/index.php`. — Commit `8943891`
- **v2026040601–603 — Behat-Fixes (4 Runden).**
  Pluginname-Typo, fehlender Generator, DE-Strings in EN-Tests,
  auto-start Tour-Assertions ersetzt. 0/9 → 8/9. — Commits `357ac23`–`b3acba6`
- **v2026040601 — version.php ALPHA → BETA, release 0.9.0.** — Commit `357ac23`
- **v2026040538 — UX-Feedback-Bundle (8 Punkte, verifiziert 2026-04-06).**
  Alle 8 Punkte bestanden: Block sichtbar, Kartentext, Button-Label,
  Weiter/Zurück-Navigation, History-Stack, Exhausted-Einstellung,
  Save-Button, Aktivitäts-Tour.
- **v2026040540 — Vollbild-Weiter + DE-Sie-Audit (verifiziert 2026-04-06).**
  Fullscreen bleibt bei Weiter/Zurück offen. Alle Tour-Texte durchgängig Sie-Form.
- **v2026040542 — 4 UX-Fixes (verifiziert 2026-04-06).** Block-Preview,
  Popup gleiche Frage, stabile Frage bei Reload (PRG-Fix), leerer Block-Titel.
- **v2026040543 — Schema + Popup-Close + Block-Autor (verifiziert 2026-04-06).**
- **v2026040545 — BroadcastChannel-Sync (verifiziert 2026-04-06).**
- **PreChecks (verifiziert 2026-04-06).** PHPCS 0/0, Grunt AMD rebuild (`c18b6c8`),
  check_database_schema clean.
- **v2026040537 — Bundled Fixes PHPUnit-Run.** PHPUnit 11 Attribute,
  XMLDB NOTNULL-Fix, Tour-Import-Guard, Save-Button inline-submit,
  dritte Tour, Lang-String-Audit. — Commit `3bb7b02`
