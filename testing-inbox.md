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

- der Block ist wiede weg.
- schaue das JSON nochmal durch. Ist da jetzt alles drin, was wir brauchen inkl. Kontexten und Zielgruppe?
- stimmt das bei der Anleitung? „fork it, edit content/default.json"
- müsst der Save Changes button nicht höher in der Admin ansicht, also über syncstatus?
- wie genau ist die URL https://github.com/jmoskaliuk/content_elediacheckin.git oder  github.com/jmoskaliuk/content_elediacheckin
- kannst du den beispiel URL um GIT direkt schon im Feld speichern?
- FEhler beim Sync mit dem GIT This page did not call $PAGE->set_url(...). Using http://127.0.0.1:9501/mod/elediacheckin/admin/actions.php?action=runsync&sesskey=TB52gls7LW

    line 684 of /public/lib/pagelib.php: call to debugging()
    line 965 of /public/lib/pagelib.php: call to moodle_page->magic_get_url()
    line 1691 of /public/lib/pagelib.php: call to moodle_page->__get()
    line 403 of /public/lib/classes/output/requirements/page_requirements_manager.php: call to moodle_page->get_edited_page_hash()
    line 1675 of /public/lib/classes/output/requirements/page_requirements_manager.php: call to core\output\requirements\page_requirements_manager->init_requirements_data()
    line 256 of /public/lib/classes/output/core_renderer.php: call to core\output\requirements\page_requirements_manager->get_head_code()
    line 219 of /public/lib/mustache/src/Mustache/Context.php: call to core\output\core_renderer->standard_head_html()
    line 138 of /public/lib/mustache/src/Mustache/Context.php: call to Mustache_Context->findVariableInStack()
    line 34 of /var/www/dataroot/localcache/mustache/1775401209/boost/__Mustache_1368aaee13500a959bf1f3c79cfda932.php: call to Mustache_Context->findDot()
    line 66 of /public/lib/mustache/src/Mustache/Template.php: call to __Mustache_1368aaee13500a959bf1f3c79cfda932->renderInternal()
    line 192 of /public/lib/classes/output/renderer_base.php: call to Mustache_Template->render()
    line 38 of /public/theme/boost/layout/embedded.php: call to core\output\renderer_base->render_from_template()
    line 959 of /public/lib/classes/output/core_renderer.php: call to include()
    line 875 of /public/lib/classes/output/core_renderer.php: call to core\output\core_renderer->render_page_layout()
    line 805 of /public/lib/classes/output/core_renderer.php: call to core\output\core_renderer->header()
    line 0 of unknownfile: call to core\output\core_renderer->redirect_message()
    line 109 of /public/lib/classes/output/bootstrap_renderer.php: call to call_user_func_array()
    line 2252 of /public/lib/weblib.php: call to core\output\bootstrap_renderer->__call()
    line 90 of /public/mod/elediacheckin/admin/actions.php: call to redirect()
    
    


## ❓ Klärung notwendig

_(leer — alle offenen Fragen sind aktuell beantwortet)_

## 🔧 In Arbeit

_(leer — nächste Aufgabe: „Nur eigene Fragen"-Toggle)_

## ✅ Erledigt

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
