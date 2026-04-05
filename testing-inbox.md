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

- Kategorie Learning Content ist anders gemeint. Es geht um Fragen, die Lernen anregen also z.B. "Was ist das wichtigeste, was ich heute gelernt habe". Bitte Wording überlegen und anpassen. 
- Use own questions only ist missverständlich. Was passeirt werden Fragen drin stehen, aber nein ausgewählt ist. Es gibt denke ich drei Optionen: Eigene Fragen gemeinsam mit den fertigen Nutzen. Nur eigene Fragen. Keine eigene Fragen. Kannst Du das nochmal überdenken und dann anpassen. 
- könnten wir den Block so anpassen, dass es auch auf der Startseite funktioniert?
- Bei Quote wollten wir noch den Autor haben. Bitte LAyout überlegen und Json anpassen. 
- im Aktivity chooser kannst Du a einen Info link einbauen, wie auch die anderen Akvititäten haben. Sollte sein eledia.de/mod_elediacheckin
- Können wir eine Usertour für die Teacher erstellen, wenn die Akbitäten zum ersten Mal genutzt wird?
- Ich nutze Firefox. Wenn ich im Vollbildmodus des Browers bin öffnet sich das Pop up nicht in einem Popup, sondern in einem neuen Fenster. Ist das gewollt oder ein FEhler?
- Die Beschreibung der Akvitität wird zweil mal angezeigt im Grauen Kasten und separat. Der Graue kasten kann weg.
  - Bitte keine separate sycn Seite sondern in Admin-Seite einbauen.
- in den einstellungen eigene Fragen nach Display options.
- der block ist wieder weg

    line 111 of /public/lib/classes/event/url_blocked.php: call to debugging()
    line 785 of /public/lib/classes/event/base.php: call to core\event\url_blocked->validate_before_trigger()
    line 3984 of /public/lib/filelib.php: call to core\event\base->trigger()
    line 3779 of /public/lib/filelib.php: call to curl->trigger_url_blocked_event()
    line 4056 of /public/lib/filelib.php: call to curl->request()
    line 213 of /public/mod/elediacheckin/classes/content/eledia_premium_content_source.php: call to curl->post()
    line 111 of /public/mod/elediacheckin/classes/content/eledia_premium_content_source.php: call to mod_elediacheckin\content\eledia_premium_content_source->verify_license()
    line 92 of /public/mod/elediacheckin/classes/local/service/sync_service.php: call to mod_elediacheckin\content\eledia_premium_content_source->fetch_bundle()
    line 57 of /public/mod/elediacheckin/admin/actions.php: call to mod_elediacheckin\local\service\sync_service->run()

Skip to main content
Sync failed: The license server is unreachable or returned an error. (HTTP 0 from /verify: The URL is blocked.) :: HTTP 0 from /verify: The URL is blocked.
This page should automatically redirect. If nothing is happening please use the continue link below.
(Continue)

Error output, so disabling automatic redirect.
Script /mod/elediacheckin/admin/actions.php?action=runsync&sesskey=TB52gls7LW mutated the session after it was closed: $SESSION->cachestore_session: default_session-core/navigation_cache,default_session-core/coursecat,default_session-core/calendar_categories,default_session-core/navigation_expandcourse,default_session-core/userselections,default_session-core/courseeditorstate $SESSION->editedpages $SESSION->notifications

    line 807 of /public/lib/classes/session/manager.php: call to debugging()
    line 185 of /public/lib/classes/shutdown_manager.php: call to core\session\manager::check_mutated_closed_session()
## ❓ Klärung notwendig
  


## 🔧 In Arbeit

_(leer)_

## ✅ Erledigt

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
