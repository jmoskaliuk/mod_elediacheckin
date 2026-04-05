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

_(leer)_

## ❓ Klärung notwendig

- **Block ist wieder weg**: Vermutung — der letzte `moodle-update.sh`-Lauf
  hat nur `checkinmod` aktualisiert und den Block-Ordner nicht mehr
  deployt. Bitte einmal `~/moodle-update.sh checkin` laufen lassen
  (Meta-Key, deployt mod **und** block atomar). Falls der Block danach
  noch immer fehlt: `ls ~/demo/site/moodle/public/blocks/ | grep
  eledia` — Ergebnis hier reinschreiben, dann weiß ich, ob die Dateien
  nicht ankommen oder Moodle sie nicht registriert.

## 🔧 In Arbeit

_(leer — nächstes großes Thema: Phase 2 License-Server-MVP)_

## ✅ Erledigt

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
