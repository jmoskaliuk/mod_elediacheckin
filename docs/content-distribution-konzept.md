# mod_elediacheckin — Content-Distribution-Konzept

**Stand:** 2026-04-05 (Phase 1 Implementierungs-Update)
**Autoren:** Johannes Moskaliuk, Claude (Anthropic)
**Status:** Entwurf, Grundlage für Implementierung

---

## 1. Ausgangslage

Das Plugin `mod_elediacheckin` zieht Fragen/Impulse aus einer externen Content-Quelle und staged sie lokal in der Moodle-DB (siehe Fach- und Technikkonzept §8). Die ursprüngliche Annahme war "eine Git-basierte Content-Quelle pro Installation". Diese Annahme greift für das Geschäftsmodell zu kurz, weil sie drei sehr unterschiedliche Kundenbedürfnisse in einen Topf wirft:

- Der Kunde, der **sofort nach der Installation loslegen** will, ohne sich mit Repos oder Credentials zu beschäftigen.
- Der Kunde, der **eigene, firmenspezifische Fragen** pflegen möchte (HR-Onboarding, interne Prozesse, Produkt-Quizze) und dafür einen eigenen Redaktions-Workflow hat.
- Der Kunde, der **von eLeDia kuratierte Premium-Inhalte** beziehen möchte, die regelmäßig aktualisiert werden (Lernkarten, Reflexionsfragen, Assessment-Fragen) — als kostenpflichtiges Abo.

Dieses Konzept beschreibt eine Architektur, die alle drei Bedürfnisse aus einem einheitlichen Plugin-Core bedient.

## 2. Drei-Modi-Architektur

Der `sync_service` im Plugin kennt keine einzelne Content-Quelle mehr, sondern ein **Content-Source-Registry** mit drei austauschbaren Implementierungen. Die Quellen sind nicht exklusiv — ein Kunde kann beliebig viele gleichzeitig aktivieren. Jede Frage in der lokalen Moodle-DB bekommt eine Herkunfts-Kennzeichnung (`source_id`), der `question_provider` kann auf Wunsch aus allen oder nur einer bestimmten Quelle ziehen.

### Modus 1 — Bundled (integriert)

Mit dem Plugin wird eine JSON-Datei mitausgeliefert (z. B. `pix/content/default.json`), die einen kuratierten Starter-Set an Fragen enthält. Beim ersten Install-Durchlauf werden diese Fragen direkt in die `elediacheckin_question`-Tabelle importiert. Keine Netzverbindung nötig, keine Credentials, kein Setup.

Zweck: Sofort nach der Installation funktionsfähig. Demo-Content für Screenshots im Moodle-Plugin-Directory-Review. Kostenlose Basis für jede Installation. Dient gleichzeitig als lebendes Schema-Beispiel.

### Modus 2 — Eigenes Git-Repo

Der Kunde trägt in den Plugin-Settings eine HTTPS-Repo-URL ein, optional einen Personal Access Token (PAT) für private Repos. Der `sync_service` pullt per HTTPS, validiert den Inhalt gegen das eLeDia-JSON-Schema, staged die Daten und führt einen atomischen Swap durch.

Zweck: Kunden mit eigenem Redaktionsteam können firmenspezifische Fragen pflegen, ohne dass eLeDia oder irgendein Dritter Einblick hat. Git-nativer Workflow mit Pull Requests, CI-Validation, Rollbacks. Im Plugin-Admin-UI wird auf eine öffentlich einsehbare Vorlage-Repo-Struktur verlinkt (eLeDia stellt auf GitHub ein Template-Repo bereit, das man forken kann).

**UI-Hinweis:** Im Admin-Bereich wird erklärt, wie man ein eigenes Repo anlegt, welche Datei-Struktur erwartet wird, und wie der PAT erzeugt wird. Ein "Verbindung testen"-Button validiert Repo-URL, Token und Schema-Konformität, bevor der erste Sync läuft.

### Modus 3 — eLeDia Premium (License Server)

Der Kunde trägt einen License-Key in die Plugin-Settings ein. Der `sync_service` spricht mit dem eLeDia-License-Server, bekommt einen kurzlebigen Token, lädt das aktuelle signierte Content-Bundle von einem CDN, verifiziert die Signatur gegen einen fest im Plugin-Code hinterlegten eLeDia-Public-Key, und übergibt die Daten an die Staging-Pipeline.

Zweck: Paid-Feature. eLeDia monetarisiert kuratierte, gepflegte Content-Pakete (z. B. "Führungskräfte-Reflexion", "Onboarding Generation Z", "Resilienz-Training"). Regelmäßige Updates ohne Kundenaufwand. Zentrale License-Verwaltung bei eLeDia.

### Kombination mehrerer Modi

Ein typischer Kunde der zahlenden Kategorie hätte aktiv:

- Modus 1 (Bundled) mit 50 kostenlosen Starter-Fragen
- Modus 3 (eLeDia Premium) mit 500 regelmäßig aktualisierten Premium-Fragen
- Modus 2 (eigenes Repo) mit 120 firmenspezifischen Fragen

Im Plugin-Admin sieht man eine Quellenliste mit jeweils Herkunft, Anzahl Fragen, letzter Sync-Zeitpunkt, Status, und An-/Ausschalter. Der Kursleiter kann beim Anlegen einer Check-in-Aktivität optional filtern, aus welchen Quellen gezogen werden soll.

## 3. Content-Source-Abstraktion im Plugin

### Interface

Alle drei Implementierungen implementieren dasselbe Interface:

```php
namespace mod_elediacheckin\local\content_source;

interface content_source_interface {
    public function get_id(): string;
    public function get_display_name(): string;
    public function test_connection(): connection_result;
    public function fetch_manifest(): manifest;
    public function fetch_bundle(string $version): bundle;
    public function verify(bundle $bundle): bool;
}
```

- `get_id()` liefert eine eindeutige Kennung pro Quelle (z. B. `bundled`, `git:<hash-der-url>`, `eledia:<license-key-hash>`).
- `fetch_manifest()` liefert Metadaten: aktuelle Version, Anzahl Fragen, verfügbare Sprachen, Kategorien — ohne den vollen Bundle-Download.
- `fetch_bundle()` liefert das eigentliche Content-Paket.
- `verify()` prüft Signatur, Hashes, Schema-Konformität.

### Gemeinsame Pipeline

Der `sync_service` ruft für jede aktive Quelle `fetch_manifest()` auf, vergleicht die Version mit dem zuletzt gesyncten Stand aus `elediacheckin_sync_log`, lädt bei Bedarf per `fetch_bundle()` nach, validiert, staged in temporäre Tabellen, und tauscht atomisch via Transaktion. Diese Pipeline ist für alle drei Modi identisch — der einzige Unterschied liegt in der Quelle-Implementierung.

Vorteil: Staging, Swap, Cache-Invalidierung, Task-Scheduling, Logging, Admin-UI für Sync-Status — alles existiert einmal und gilt für alle Modi.

### Registry und Erweiterbarkeit

Die Content-Source-Registry ist eine einfache Liste in der Plugin-Konfiguration mit Source-Typ, Konfigurations-Werten und Aktiv-Flag. Neue Source-Typen können in Zukunft hinzugefügt werden (z. B. ein hypothetischer `moodle_dataset_source`, der aus einem zentralen Moodle-Dataset-Plugin zieht), ohne den sync_service oder die Staging-Logik anfassen zu müssen.

## 4. License-Server-Architektur (Modus 3)

### Komponenten

Der License-Server besteht aus einer schlanken Web-API, einer kleinen Datenbank, einem CDN-Bucket für die Content-Bundles, und einer Bezahl-Integration.

**API** — zwei Kern-Endpunkte:

- `POST /verify` — nimmt `{license_key, site_hash, plugin_version}` entgegen, prüft Gültigkeit in der DB, registriert/aktualisiert den Install-Eintrag, gibt `{token, bundle_version, bundle_url}` zurück. Token ist ein HMAC-signierter, kurzlebiger (24-48h) Signed Token.
- `GET /bundle/{version}` — nimmt den Token als Bearer-Auth, leitet auf eine signierte CDN-URL um oder liefert das Bundle direkt bei kleinen Größen.

Optionale Endpunkte: `/usage` für Telemetrie (anonymisierte Zähler), `/revoke` für manuelles Widerrufen durch eLeDia-Admins, `/health`.

**Datenbank** — drei Tabellen reichen für den MVP:

- `licenses`: key (UUID), customer_id, tier, created_at, expires_at, max_installs, revoked_at, stripe_subscription_id
- `installs`: license_id, site_hash, site_url, plugin_version, first_seen, last_seen
- `usage_log`: optional, für Analytics — nur aggregierte Zähler, keine personenbezogenen Daten

SQLite reicht für die ersten hundert Kunden, ab dann Postgres.

**CDN-Bucket** — Empfehlung: **Cloudflare R2**. S3-API-kompatibel, aber **keine Egress-Kosten**. Storage kostet $0,015 pro GB und Monat. Bei JSON-Fragen-Bundles von 1-10 MB pro Version reden wir über ein paar Cent pro Monat, auch bei Tausenden Kunden.

**Signing** — zwei Ebenen:
- Transport-Signing: HMAC-signed URL mit kurzer TTL für den Download selbst (CDN-native).
- Content-Signing: Die Bundles werden vor dem Upload mit einem eLeDia-Private-Key (ED25519) signiert. Der Public-Key ist im Plugin-Code hardcoded. Selbst wenn jemand den License-Server oder den CDN-Bucket kompromittiert, kann er den Kunden keine manipulierten Fragen unterschieben, weil die Signatur nicht passt.

**Bezahl-Integration** — Empfehlung: **Lemon Squeezy** statt Stripe direkt. Lemon Squeezy agiert als Merchant of Record und erledigt die EU-VAT-Komplexität komplett für euch. Weniger Buchhaltungsaufwand, etwas höhere Gebühren. Webhook-Handler erzeugt bei erfolgreicher Zahlung automatisch einen License-Key, trägt ihn in die DB ein, schickt ihn per E-Mail an den Kunden.

### Tech-Stack-Empfehlung

Zwei realistische Optionen:

**Option A — klassischer PHP/Laravel-Stack**, gehostet auf einem Hetzner-VPS (€5-10/Monat). Vorteil: passt zum Moodle-Umfeld, ihr kennt PHP, Deployment per Git + `git pull` + Composer. Nachteil: Server-Pflege, Updates, Monitoring.

**Option B — Cloudflare Workers + D1 + R2**, vollständig serverless. Vorteil: null Server-Pflege, weltweit edge-verteilt, praktisch unbegrenzt skalierbar, Gesamtkosten unter €10/Monat bis in die Tausende von Kunden. Nachteil: JavaScript/TypeScript statt PHP, weniger vertraut.

Für einen schlanken Start bei eLeDia würde ich Option A empfehlen, weil der Team-Skill passt. Option B ist die langfristig skalierbarere Wahl, macht aber erst ab mittlerer dreistelliger Kundenzahl echten Unterschied.

### Aufwandsschätzung

**MVP (funktionsfähig, noch kein Dashboard, kein Auto-Billing):**
- API mit 2 Endpunkten: 1 Tag
- DB-Schema + Migrationen: halber Tag
- Bundle-Upload-Skript (manuell ausführbar): halber Tag
- CDN-Setup (R2, Bucket, Credentials, Domain): halber Tag
- Content-Signing-Pipeline: halber Tag
- Plugin-seitige `eledia_content_source`-Klasse: 1 Tag
- Integration-Test gegen echten License-Server: 1 Tag

**Summe MVP: 4-5 Arbeitstage**

Keys werden in dieser Phase manuell per SQL-Insert angelegt — reicht für die ersten 10 zahlenden Kunden problemlos.

**Production-ready (inkl. Automatisierung):**
- Lemon-Squeezy-Webhook + Key-Generierung: 1 Tag
- Admin-Dashboard für eLeDia (Kunden anlegen, widerrufen, verlängern, sehen): 2-3 Tage
- GitHub Action für automatisches Build + Upload bei Push auf `main`: 1 Tag
- Monitoring (Sentry + Uptime-Robot): halber Tag
- Rate-Limiting gegen Brute-Force-Key-Versuche: halber Tag
- Bundle-Update-Notification an License-Server: halber Tag
- Security-Hardening (HTTPS, CSP, DB-Backups): 1 Tag
- Dokumentation (intern + customer-facing): 1 Tag

**Summe Production-ready: zusätzlich 7-9 Arbeitstage.**

**Gesamt: etwa zwei bis zweieinhalb Wochen fokussierter Arbeit für eine wirklich produktionstaugliche Lösung.**

### Betriebskosten (monatlich)

- Hetzner VPS CX11 oder CX22: €5-10
- Cloudflare R2 Storage (selbst bei 10 GB Bundles): €0,15
- Cloudflare R2 Egress: €0 (kostenlos)
- Domain (`licenses.eledia.de` oder `content.eledia.de`): ~€1 (anteilig)
- Lemon Squeezy: 5% + $0,50 pro Transaktion (keine monatliche Fixgebühr)
- Monitoring (Sentry Free-Tier, UptimeRobot Free): €0

**Summe fix: <€15/Monat**, unabhängig von der Kundenzahl, bis in den vierstelligen Bereich.

## 5. GitHub-basierter Autoren-Workflow (Hybrid)

Der License-Server verwaltet die Distribution, aber die eigentlichen Fragen werden in einem privaten GitHub-Repo bei eLeDia gepflegt. Damit hat die Redaktion einen git-nativen Workflow mit Pull Requests, Code-Review, CI-Validation und vollständiger Versionshistorie.

### Pipeline

1. **Autoren arbeiten in einem privaten Repo** (z. B. `eledia/checkin-content-premium`). Jede neue Frage, jede Änderung ist ein Pull Request.
2. **Eine GitHub Action auf jedem PR** validiert:
   - JSON-Schema-Konformität (gegen `schema/question.schema.json`)
   - Qualitäts-Lints: keine Duplikate, alle Fragen haben Kategorien, Mindestlänge, Mindest-/Maximal-Zeichen, verbotene Begriffe
   - Link-Check falls Fragen auf externe Ressourcen verweisen
3. **Merge auf `main`** triggert eine zweite Action, die:
   - alle Fragen einsammelt und zu einem versionierten Bundle packt (`bundles/premium-v1.2.3.json`)
   - die Version aus `git describe` oder einer `VERSION`-Datei zieht
   - das Bundle mit dem eLeDia-Private-Key (ED25519) signiert (`bundles/premium-v1.2.3.json.sig`)
   - Bundle + Signatur in den R2-CDN-Bucket hochlädt
   - den License-Server per Webhook informiert ("v1.2.3 ist live")
4. **Der License-Server** aktualisiert seinen `current_bundle_version`-Eintrag. Beim nächsten `verify`-Call liefert er automatisch die neue Version aus.
5. **Kunden-Moodles** pullen via geplantem Task alle 6h, entdecken die neue Version im Manifest, laden das Bundle, verifizieren die Signatur, stagen und swappen.

### Warum privat und nicht öffentlich?

Das Content-Repo ist der einzige Ort, an dem die Fragen im Klartext vorliegen. Öffentlich wäre das Paid-Feature wertlos, weil jeder Dritte die Fragen einfach forken und kostenlos weiterverteilen könnte. Zahlende Kunden bekommen die Fragen ausschließlich über den License-Server als signiertes, tokengeschütztes Bundle.

### Branch-Strategie

- `main` → aktive Premium-Produktion. Jeder Merge = neuer Release.
- `feature/*` → einzelne Autoren-PRs, bevor sie in `main` gemerged werden.
- Optional `staging` → wenn ihr größere Release-Zyklen mit Sammel-Releases haben wollt.

## 6. Sicherheits-Modell

### Angriffs-Szenarien und Gegenmaßnahmen

**Szenario: License-Key wird geleakt** (z. B. ein Kunde stellt seine config.php versehentlich auf GitHub). Gegenmaßnahme: `max_installs`-Limit auf jedem Key (Standard 3-5, je nach Tier). Bei Überschreitung lehnt der Server neue Site-Hashes ab. eLeDia-Admin kann den Key manuell widerrufen.

**Szenario: License-Server wird kompromittiert** und ein Angreifer versucht, den Kunden manipulierte Fragen unterzuschieben. Gegenmaßnahme: Content-Signing mit Private-Key, der *nicht* auf dem License-Server liegt (sondern im GitHub-Actions-Secret). Der Server verteilt nur die Signatur, die Plugins verifizieren sie. Selbst ein vollständig übernommener Server kann keine gültigen Bundles ausstellen.

**Szenario: Man-in-the-Middle auf dem Kunden-Netzwerk**. Gegenmaßnahme: HTTPS-Only, Certificate Pinning im Plugin (optional, macht Maintenance schwieriger), plus Content-Signatur als zweite Schicht.

**Szenario: Kunde kündigt, will aber weiter Updates bekommen**. Gegenmaßnahme: Key wird in `licenses.revoked_at` gesetzt, nächster `verify`-Call schlägt fehl, keine neuen Syncs mehr. Die zuletzt gesyncten Fragen bleiben in der lokalen Moodle-DB (das ist bewusst so, damit laufende Kurse nicht abbrechen), aber neue Inhalte kommen nicht mehr.

**Szenario: Brute-Force auf License-Keys**. Gegenmaßnahme: Rate-Limiting pro IP und pro (potentieller) License-Key, Keys sind UUID v4 (122 Bit Entropie, praktisch unratbar).

### DSGVO-Betrachtung

Der License-Server speichert:
- License-Key ↔ Kundendaten (Name, E-Mail, Rechnungsadresse) — notwendig für Vertragsabwicklung, Rechtsgrundlage Art. 6 Abs. 1 lit. b DSGVO.
- Site-Hash + optional Site-URL — technisch notwendig für `max_installs`-Enforcement und Missbrauchserkennung.
- Optional: anonymisierte Nutzungszähler (wie viele Syncs, welche Bundle-Version). Keine Nutzerdaten aus dem Kunden-Moodle.

**Was der License-Server bewusst nicht speichert:** welche Fragen einzelne Moodle-Nutzer sehen oder beantworten. Diese Daten bleiben komplett in der Kunden-Moodle-DB. Das Plugin ist bereits als `null_provider` für die Moodle-Privacy-API deklariert, das ändert sich nicht.

Ein Auftragsverarbeitungsvertrag (AVV) zwischen eLeDia und den Kunden regelt die Verarbeitung der Install-Metadaten.

## 7. UI-Konzept für die Plugin-Settings

Im Moodle-Admin (Website-Administration → Plugins → Aktivitäten → Check-in):

**Abschnitt "Inhaltsquellen"**

Eine Liste konfigurierter Quellen mit folgenden Spalten:

- **Status**: Ampel (grün = letzte Sync erfolgreich, gelb = veraltet, rot = Fehler)
- **Typ**: Bundled / Git / eLeDia Premium
- **Name**: vom Nutzer vergeben oder automatisch
- **Fragen**: Anzahl
- **Letzter Sync**: Zeitstempel
- **Nächster Sync**: Zeitstempel (aus Scheduled Task)
- **Aktionen**: Aktivieren/Deaktivieren, Jetzt synchronisieren, Bearbeiten, Entfernen

Unter der Liste ein Button "Quelle hinzufügen" mit Drop-Down:
- Bundled (nur einmal möglich, ist standardmäßig aktiv)
- Eigenes Git-Repo
- eLeDia Premium (License-Key eingeben)

**Beim Hinzufügen einer Git-Quelle**:
- Feld "Repository-URL" (HTTPS)
- Feld "Personal Access Token" (optional, für private Repos, als configpasswordunmask)
- Feld "Branch" (Default: `main`)
- Feld "Sync-Intervall" (Default: alle 6h)
- Button "Verbindung testen" — prüft URL, Token, Schema-Konformität, ohne zu syncen
- Link "Wie lege ich ein Repo an?" → öffnet Doku-Seite mit Template-Link

**Beim Hinzufügen einer eLeDia-Premium-Quelle**:
- Feld "License-Key"
- Button "License prüfen" — verifiziert gegen License-Server, zeigt Tier, Ablaufdatum, verfügbare Bundles
- Link "License erwerben" → eLeDia-Shop

**Abschnitt "Quellen-Filter beim Kursanlegen"**

Eine Kursleitung kann beim Anlegen einer Check-in-Aktivität optional festlegen, aus welchen der aktiven Quellen gezogen werden soll. Standard: alle. Nützlich z. B., wenn eine HR-Abteilung in einem Onboarding-Kurs nur firmeneigene Fragen nutzen will und die allgemeinen Bundled-Fragen unterdrücken.

## 8. Roadmap — Build-Reihenfolge

### Phase 1 — Plugin-Grundgerüst mit Modus 1 + 2

**Ziel:** Testbares, vollständiges Plugin ohne jegliche eLeDia-Infrastruktur. Moodle-Plugin-Directory-fähig.

- `content_source_interface` und Registry im Plugin
- `bundled_content_source` mit einer mitgelieferten `pix/content/default.json`
- `git_content_source` mit HTTPS-Pull, PAT-Support, Schema-Validation
- Settings-UI für Quellenliste (noch ohne eLeDia-Modus)
- Öffentliches Demo-Content-Repo auf GitHub (`jmoskaliuk/elediacheckin-content-demo`) mit JSON-Schema, Beispielfragen, CI-Validation, README mit Schema-Doku
- End-to-end-Test: Plugin installiert sich, Bundled-Content ist sofort da, Demo-Repo-URL eintragen, Sync läuft durch, Fragen erscheinen im Check-in

**Aufwand:** ca. 3-4 Cowork-Sessions. Danach ist das Plugin vollständig funktionsfähig und kann ins Moodle-Plugin-Directory eingereicht werden.

### Phase 2 — License-Server-MVP

**Ziel:** Erster zahlender Kunde kann Premium-Content beziehen.

- API, DB, CDN, Signing, manuelles Key-Management per SQL
- `eledia_content_source` im Plugin
- Privates Premium-Content-Repo mit initialem Content-Set
- GitHub-Action für Build + Upload + Sign
- Ein erster zahlender Pilotkunde als End-to-end-Test

**Aufwand:** 4-5 fokussierte Arbeitstage. Startet erst, wenn Phase 1 stabil läuft und erster Kunde in Aussicht ist.

### Phase 3 — Production-ready License-Server

**Ziel:** Selbstbedienungs-fähig, skalierbar, überwacht.

- Lemon-Squeezy-Integration mit Webhook-Key-Generierung
- Admin-Dashboard für eLeDia
- Monitoring, Rate-Limiting, Backups, Dokumentation
- Security-Review
- Öffentlicher Launch

**Aufwand:** weitere 7-9 Arbeitstage. Erst sinnvoll, wenn Phase 2 validiert ist.

### Phase 4 — Erweiterungen

- Optionale Telemetrie (welche Fragen wie oft gezogen, mit Opt-In)
- Content-Personalisierung nach Kategorien oder Lernpfaden
- Alternative Bezahl-Modelle (pay-per-bundle statt Abo)
- Weitere Content-Source-Implementierungen (z. B. AI-generierte Fragen via eLeDia-API)

## 9. Offene Entscheidungen

Diese Punkte sollten vor Beginn der Phase-1-Implementierung geklärt werden:

**JSON-Schema der Fragen.** Wie viele Felder? Nur "Frage + optionaler Hinweis" oder vollständige Struktur mit Kategorien, Schwierigkeit, Tags, Mehrsprachigkeit, Multimedia-Anhängen, Lösungsweg, optionalen Mehrfachantworten? → Johannes arbeitet parallel am Schema.

**Repo-Naming-Konvention** für das öffentliche Demo-Content-Repo und das private Premium-Repo. Vorschlag: `jmoskaliuk/elediacheckin-content-demo` (public, Schema + Demo), `eledia/checkin-content-premium` (private, Redaktion).

**License-Server-Stack** — Option A (Laravel/Hetzner) oder Option B (Cloudflare Workers)? Kann später entschieden werden, blockt Phase 1 nicht.

**Bundle-Format** — ein einziges JSON-File pro Version, oder aufgeteilt nach Kategorien/Sprachen für Delta-Updates? Vorschlag: ein Bundle pro Version für MVP, Delta-Updates später falls nötig.

**Public-Key-Rotation** — wie verfahren wir, wenn der eLeDia-Private-Key einmal rotiert werden muss? Vorschlag: Plugin unterstützt von Anfang an eine Liste erlaubter Public Keys (Array statt String), ein neuer Key wird per Plugin-Update ausgeliefert, alte Bundles bleiben verifizierbar.

**Signing-Verfahren** — ED25519 (empfohlen: schnell, klein) oder RSA-4096 (etablierter)? Vorschlag: ED25519 mittels `sodium_crypto_sign_verify_detached()`, das ist in allen Moodle-5.x-Hostings verfügbar (libsodium ist PHP-Core seit 7.2).

**Sprachen-Handling** — wie werden mehrsprachige Fragen im Bundle repräsentiert? Vorschlag: jede Frage hat ein `translations`-Array mit ISO-Code als Key. Schema-Entscheidung, wird im Zuge von Punkt 1 oben geklärt.

**Versionierungs-Strategie** — SemVer für Content-Bundles, oder Monotone Zähler, oder Datum-basiert? Vorschlag: SemVer für Premium (z. B. `2026.4.0`), weil es Kompatibilitäts-Aussagen erlaubt ("v2026.x ist mit Plugin v0.5+ kompatibel").

---

## 10. Phase-1-Implementierungs-Entscheidungen (2026-04-05)

Während der Cowork-Sessions zu Phase 1 wurden folgende Detail-Entscheidungen getroffen. Sie ergänzen die vorherigen Abschnitte, ersetzen sie aber nicht.

### 10.1 JSON-Schema der Fragen (finalisiert)

Das Bundle-Format ist in `docs/content-schema.json` (JSON Schema Draft 2020-12) definiert. Wichtige Entscheidungen:

- **Bundle-Wrapper** enthält `schema_version`, `bundle_id`, `bundle_version`, `generated_at`, `language`, `questions[]`.
- **Pflichtfelder je Frage:** `id` (stable, `[A-Za-z0-9_.-]+`), `ziel`, `kategorie[]`, `frage`, `hat_antwort`, `sprache` (ISO-639-1), `lizenz`, `version`, `status`, `created_at`, `updated_at`.
- **Optionale Felder je Frage:** `antwort` (Pflicht gdw. `hat_antwort=true`), `autor`, `quelle`, `link`, `media`.
- **`ziel` ist single-select**, Enum: `impuls | checkin | checkout | retro | learning | funfact | zitat`. Bewusste Entscheidung gegen Multi-Select — jede Frage hat genau eine didaktische Intention. **Achtung zwei Ebenen:** Auf Frage-Ebene (`question.ziel` im Bundle) ist das Feld single-select; auf Aktivitäts-Ebene (`elediacheckin.ziele` in `mod_form`) ist es multi-select, weil eine Aktivität mehrere Zielarten in einem Kartenset anbieten darf. Gleiches Wort, unterschiedliche Kardinalität — beim Lesen nicht verwechseln.
- **`status` Enum:** `draft | published | deprecated`. Der `question_provider` liefert per Default nur `published`.
- **Kategorie-Enums sind pro `ziel` unterschiedlich**, erzwungen über `allOf`/`if-then`-Blöcke im Schema. Jede Kategorie ist ein externes ID-Token (keine lokalisierten Strings im Content-Repo).
- **Verworfen:** `tags`, `dauer`, `gruppengroesse`, `replaced_by`. Können bei Bedarf als Schema-v2 nachgezogen werden.

### 10.2 XMLDB-Schema (Phase 1, Version 2026040501)

- **Eine einzige Fragetabelle** `elediacheckin_question` statt getrennter `_category`/`_question_cat`-Tabellen. Kategorien werden als CSV im Feld `categories` mitgeführt. Der `question_provider` filtert die CSV in PHP nach dem SQL-Abruf — akzeptabel bei erwarteten <10k Fragen pro Site.
- **Staging-Swap-Pattern:** Feld `stage` (0 = live, 1 = staging). Sync-Flow:
  1. Alle alten Staging-Zeilen löschen.
  2. Bundle komplett als `stage=1` einschreiben.
  3. In einer Transaktion: alle `stage=0` löschen, dann `stage=1 → stage=0` umfärben.
  Garantie: ein fehlschlagender Sync lässt den Live-Datenbestand unverändert.
- **`elediacheckin`-Tabelle:** Feld `mode` (`checkin|checkout|both`) wurde durch `ziele` (CHAR(255), CSV der erlaubten Kartenarten) ersetzt. `mod_form` bietet Multi-Select. Upgrade migriert `mode=both` → `ziele='checkin,checkout'`.
- **`elediacheckin_sync_log`** wurde um `sourceid`, `bundleid`, `bundleversion` erweitert, `sourceversion`/`sourcecommit` entfernt.
- **Indizes:** `stage_ext_lang` (unique) für idempotente Inserts, `stage_ziel_lang_status` (nonunique) für den häufigsten Abfragepfad.

### 10.3 Content-Source-Architektur (Phase 1, umgesetzt)

- **Interface** `mod_elediacheckin\content\content_source_interface` mit `get_id()`, `get_display_name()`, `test_connection()`, `fetch_bundle(): content_bundle`. Wirft `content_source_exception` bei Fehlern.
- **Registry** `content_source_registry::all()/get($id)/get_fallback()` liefert eine statische Liste.
- **`bundled_content_source`** liest `db/content/default.json` aus dem Plugin-Verzeichnis. Ist garantierter Fallback.
- **`git_content_source`** lädt per Moodle-`curl`-Wrapper eine einzelne Bundle-JSON von einer HTTPS-URL (nicht per `git clone` — Moodle-Hoster haben meist kein `git`, und wir brauchen ohnehin nur eine Datei). Optional: Bearer-Token (PAT für private GitHub/GitLab-Repos).
- **`eledia_premium_content_source`** kommt in Phase 2.
- **Hand-gerollter `schema_validator`** (keine Composer-Dependency erlaubt in Moodle-Plugins). Validiert Bundle-Header + alle Fragen gegen die Schema-Konstanten (ZIEL_ENUM, STATUS_ENUM, CATEGORIES_BY_ZIEL).
- **Fallback-Verhalten:** Wenn die konfigurierte Quelle fehlschlägt, loggt `sync_service` den Fehler, lässt aber den Live-Datenbestand intakt (siehe 10.2). Der Admin sieht den Fehler im Sync-Log-Report.

### 10.4 Darstellungs-Modi (Mockup v2 in `docs/display-modes-mockup-v2.html`)

Drei Modi, alle als Teil von `mod_elediacheckin` implementiert (nicht im Block):

- **`normal`** — eingebettet in die normale Moodle-Aktivitätsseite. Standardfall.
- **`popup`** — Frage in separatem Browserfenster (`window.open`), optimiert für Bildschirm-Teilen in Videokonferenzen. Großer Font (clamp 1.8rem–2.6rem). Close-Button oben rechts. Die Host-Moodle-Seite bleibt offen und zeigt weiterhin Präsentator-Controls.
- **`vollbild`** — Fullscreen-Overlay im gleichen Tab (`position: fixed; inset: 0`). Radial-Gradient-Hintergrund, sehr großer Font (clamp 2.5rem–5.5rem). Close-Button (✕) rotiert beim Hover. Esc-Taste schließt.

**UI-Regeln (konsolidiert nach v2-Feedback):**

- **Keine Kategorie-Anzeige** in irgendeinem Modus. Kategorien sind Filterkriterium, nicht Inhalt.
- **Kein statischer Ziel-Chip.** Das Ziel der Aktivität ist in der Instanz-Einstellung (`elediacheckin.ziele`) festgelegt.
- **Ziel-Umschalter nur bei mehreren Zielen:** Sind in `ziele` z. B. `checkin,checkout` aktiviert, erscheint am Kopf der Karte (und analog in Popup/Vollbild-Header) ein Umschalter mit je einem Button pro aktivem Ziel. Bei nur einem aktiven Ziel keinerlei Ziel-UI.
- **Navigation:** Ein einziger Primär-Button **„Nächste Frage"**. Keine Vor/Zurück-Pfeile, kein „Andere Frage".
- **Antwort-Toggle** erscheint nur, wenn die Frage `hat_antwort=true` hat. Bei `false` wird der Button komplett ausgeblendet (nicht disabled).
- **Vollbild- und Popup-Launcher** sind kleine runde Icon-Buttons (SVG, 36×36) oben rechts auf der Karte in Normal-Modus. Keine Text-Buttons mehr.

**Mod vs. Block:** Alle drei Modi leben im `mod`. Der Block ruft intern `mod_elediacheckin/present.php?cmid=...&layout=popup|fullscreen` auf und ist damit nur ein dünner Frontend-Launcher. Das stellt sicher, dass Berechtigungen, Tracking und Caching einheitlich bleiben.

### 10.5 Admin-Settings-UI (Phase 1, umgesetzt)

`settings.php` zeigt drei Abschnitte:

1. **Inhaltsquelle** — Dropdown (`bundled | git | [eledia — Phase 2]`). Steuert welche Quelle der geplante Sync-Task nutzt.
2. **Git-Repository** — `repourl`, `reporef`, `repotoken` (passwordunmask). Per `hide_if` nur sichtbar, wenn `contentsource=git`.
3. **Sprach-Fallbacks** — `defaultlang`, `fallbacklang`.

Zusätzlich: ein Link-Button zum neuen Sync-Log-Report (siehe 10.6).

### 10.6 Admin-Report: Sync-Log

`admin/sync_log.php` registriert sich als `admin_externalpage` unter `modsettings`. Zeigt:

- **Aktuellen Zustand:** aktive Quelle + "Sync jetzt ausführen"-Button (ruft `sync_service::run('manual')`).
- **Log-Tabelle:** letzte 100 Läufe mit Datum, Auslöser (`manual`/`scheduled`), Source-ID, Bundle+Version, Ergebnis als Badge (`bg-success`/`bg-danger`), Fragen-Anzahl, getrimmte Fehlermeldung.

UI folgt eledia-moodle-ux: Bootstrap-Karten, `table table-sm table-hover`, keine custom CSS.

### 10.7 Demo-Content-Repo: `content_elediacheckin`

Eigenes Repo in `content_elediacheckin/` im Workspace, Name auf expliziten Wunsch von Johannes:

```
content_elediacheckin/
├── bundle.json                # 8 Beispielfragen, bundle_id "eledia-default"
├── schema.json                # Kopie des docs/content-schema.json
├── README.md                  # Editor-Anleitung, Feld-Tabelle, lokale Validation
├── .github/workflows/
│   └── validate.yml           # CI: Schema-Validation + Duplicate-ID-Check
└── .gitignore
```

CI läuft `jsonschema>=4.21` (Draft 2020-12) und prüft zusätzlich auf doppelte `id`s. Läuft auf Push, PR, Workflow-Dispatch.

Dieses Repo dient gleichzeitig als (a) Referenz-Implementierung für Kunden, die ihr eigenes Repo forken wollen, (b) Demo-Content für den Plugin-Directory-Review, (c) End-to-end-Testfall für `git_content_source`.

### 10.8 Deploy-Pipeline

Der Deploy-Workflow ist: Workspace → Mac-Git-Repo → GitHub → `~/moodle-update.sh checkin`. Das Plugin-Git-Repo auf dem Mac ist gleichzeitig der Moodle-Plugin-Pfad im Demo-Stack, siehe User-Memory. Änderungen aus Cowork-Sessions müssen explizit rsynct, committet und gepusht werden — sie landen nicht automatisch im Moodle.

### 10.9 Aktivitäts-Form-UX und Sprach-Auflösung (Phase-1-Finalisierung)

Nach dem ersten Demo-Durchlauf wurde das Aktivitätsformular (`mod_form.php`) deutlich verschlankt und die Sprach-Auflösung robuster gemacht:

- **Fragetypen (`ziele`)** werden als `autocomplete`-Multi-Select angeboten, Default `checkin,checkout`. Ein einzelnes Kursmodul kann damit mehrere Kartenarten gleichzeitig abbilden (Check-in + Check-out im selben Meeting-Opener).
- **Kategorien (`categories`)** verwenden denselben `autocomplete`-Typ. Die Optionen werden zur Laufzeit aus `schema_validator::CATEGORIES_BY_ZIEL` gebaut und als zusammengesetzte Keys (`ziel__cat`, z. B. `checkin__energie`) mit ziel-präfigierten Labels („Check-in: Energie") angezeigt. Das vermeidet Kollisionen zwischen Kategorien gleichen Namens in verschiedenen Zielen und bleibt auf DB-Ebene ein schlichtes CSV (die Composite-Keys werden in `get_data()` wieder auf eindeutige Kategorie-Slugs reduziert, in `data_preprocessing()` wieder auf alle passenden `ziel__cat`-Keys expandiert).
- **Inhaltssprache (`contentlang`)** ist ein einfaches `select` mit zwei Sentinels plus allen installierten Sprachpacks: `_auto_` („Nutzersprache — empfohlen", Default für neue Aktivitäten), `_course_` („Kurssprache") und danach die konkreten ISO-Codes. Die Sentinels werden als String-Literale in `view.php`/`present.php` aufgelöst; es gibt bewusst keine Klassenkonstanten, um das Laden von `mod_form.php` auf dem Hot-Path zu vermeiden.
- **Sprach-Fallback-Kette**: Der Provider wird zunächst mit der konfigurierten Sprache aufgerufen, danach mit `current_language()`, danach mit `null` (irgendein Bundle). Dadurch zeigt ein frisch erstelltes Modul auf einer Moodle-Site mit nur DE-Bundle auch bei englischem User sofort Fragen an, statt den Filter „keine Fragen verfügbar" zu liefern.
- **Tote Felder entfernt**: `randomstart`, `shownav`, `showother` und `showfilter` waren noch aus dem frühen UI-Mock übrig und wurden weder gelesen noch angezeigt. Sie sind in `install.xml` und über `db/upgrade.php` Schritt 2026040503 aus der Datenbank entfernt.
- **AMD-Bundles sind Pflicht**: Moodles RequireJS lädt nur `amd/build/*.min.js`, nicht `amd/src/*.js`. Die Build-Artefakte werden im Repo mitversioniert (kein Grunt im Deploy), damit die Präsenzmodus-Buttons (Popup, Vollbild) direkt nach einem Upgrade funktionieren. Dies war der Grund für den „Buttons ohne Funktion"-Bug beim ersten Test und ist ab jetzt Teil der Deploy-Checkliste.
- **Initial-Sync**: `db/install.php` triggert den Sync direkt nach einer Neuinstallation, `db/upgrade.php` 2026040502 holt den Sync für bereits installierte Dev-Sites nach. Vorher war das Fragen-Repository leer, bis der Cron-Scheduler das erste Mal lief.
- **eledia_premium** erscheint ab sofort als sichtbarer, aber als „(Phase 2)" markierter Eintrag im Content-Source-Dropdown. Es ist keine funktionierende Strategie dahinter registriert; die Sync-Service-Implementierung fällt sauber auf `bundled` zurück, wenn jemand den Wert wählt. Das macht die geplante Premium-Quelle sichtbar, ohne einen Feature-Flag-Branch zu riskieren.

### 10.11 Form-Refinement: „Ziel" + dynamischer Kategorienfilter (April 2026)

Nach dem ersten Sicht-Test wurde das Aktivitätsformular noch einmal nachgeschärft:

- **Labels umbenannt**: „Fragetypen" → „**Ziel**" (Singular, auch wenn Mehrfachauswahl möglich ist — das Feld beantwortet die Frage „was soll diese Aktivität erreichen?"). „Erlaubte Kategorien" → „**Kategorien**". Die alte „Ziel: Kategorie"-Doppelbeschriftung im Kategorien-Picker entfällt, weil sie durch den neuen Filter redundant wird.
- **Komposit-Keys durch bare Keys ersetzt**: Der Kategorien-Picker benutzt ab Version `2026040505` nur noch die nackten Kategorie-IDs (`stimmung`, `fokus`, …) als Option-Keys. Kategorien, die zu mehreren Zielen gehören (z. B. `stimmung` in `checkin`, `checkout`, `retro`), erscheinen nur einmal in der Liste. Das vereinfacht `data_preprocessing()` und `get_data()` zu trivialen CSV↔Array-Konvertierungen.
- **Dynamischer Filter per AMD-Modul** `mod_elediacheckin/category_filter`: Beim Laden der Form wird über `$PAGE->requires->js_call_amd` eine Map `{ category_id: [ziel1, ziel2, ...] }` serverseitig erzeugt und an das Modul übergeben. Das Modul hört auf `change`-Events des `id_ziele`-Selects, durchläuft alle `<option>`-Elemente des `id_categories`-Selects und setzt `disabled`/`hidden` je nachdem, ob mindestens eines der selektierten Ziele zu der Kategorie passt. Leere Ziele-Auswahl = alle Kategorien sichtbar. Moodles `form-autocomplete` respektiert `option.disabled` beim Suggestion-Aufbau; ein `change`-Dispatch auf das Source-Select sorgt außerdem dafür, dass bereits selektierte, jetzt nicht mehr passende Kategorien als Pills entfernt werden.
- **Kein Server-Round-Trip**: Die Filterung läuft vollständig clientseitig, weil die Map klein ist (O(Kategorien)) und sich bei normalen Form-Interaktionen nicht ändert. Ein zukünftiges Feature „dynamische Kategorien aus externem Bundle" würde das Modul auf `ajax`-Typ mit Backend-Handler umstellen.

### 10.12 Zielgruppe + Kontext als orthogonale Tag-Dimensionen (April 2026, Version 2026040508)

Um Kartenbundles für unterschiedliche Nutzer:innen-Szenarien verwendbar zu machen, ohne die `ziel/kategorie`-Achse zu überladen, wurden zwei orthogonale, optionale Tag-Dimensionen eingeführt:

- **`zielgruppe`** — Enum `fuehrungskraefte | team | grundschule`. Beschreibt, für wen die Karte primär gedacht ist.
- **`kontext`** — Enum `arbeit | schule | hochschule | privat`. Beschreibt, in welchem Setting die Karte gespielt wird.

**Schema-Ebene:** Beide Felder sind im Bundle optional, akzeptieren Arrays von Enum-Werten (mehrfach möglich), sind in `db/content/schema.json` als `$defs` definiert und werden vom `schema_validator` geprüft. Karten ohne diese Felder bleiben 100 % abwärtskompatibel.

**DB-Ebene:** Neue CSV-Spalten `zielgruppe` und `kontext` in `elediacheckin_question` (NOT NULL, Default `''`) und in `elediacheckin` (NULL erlaubt — leer = kein Filter). Upgrade-Step `2026040508` fügt die Spalten hinzu und triggert einen Re-Sync, damit bestehende Bundles die neuen Felder einlesen.

**UI-Ebene:** Zwei Single-Select-Dropdowns im `mod_form` mit „Alle Zielgruppen" bzw. „Alle Kontexte" als erstem, explizit wählbarem Eintrag (leerer Wert = keine Einschränkung). Bewusst **kein dynamischer Filter** wie bei Kategorien: `zielgruppe` und `kontext` sind orthogonal zu `ziel`, d. h. jede Karte kann theoretisch jede Kombination haben, und eine Einschränkung wäre nur Scheingenauigkeit.

> **UX-Umbau 2026-04-05:** Ursprünglich waren beide Felder Autocomplete-**Multi**-Selects mit „Alle X" als Noselection-String. In der Praxis war der häufige Fall „Alle" dadurch versteckt und die Multi-Auswahl („Team **und** Führungskräfte gleichzeitig") wurde nie genutzt. Umgebaut zu Single-Select, damit „Alle X" als erster Dropdown-Eintrag sichtbar ist. `ziele` und `categories` bleiben Multi-Select, weil dort echte Mehrfachauswahl regelmäßig vorkommt. DB-Layout unverändert (CSV-String, der jetzt 0 oder 1 Wert enthält); `question_provider::normalise_csv()` nimmt Skalar wie Array entgegen.

**Matching-Semantik (wichtig):** „**Or-untagged**" — ein Filter matcht eine Karte, wenn die Karte entweder **keinen** Tag in dieser Dimension hat (= allgemeingültig) **oder** mindestens einen Wert mit dem Filter teilt. Konsequenz für Content-Autor:innen: Karten, die überall funktionieren, werden **nicht** getaggt. Nur spezifische Karten (z. B. „Was hast du heute in der Pause gespielt?" → `zielgruppe: [grundschule], kontext: [schule]`) bekommen Tags. Damit bleiben allgemeine Bundles auch bei gesetztem Filter brauchbar.

Implementiert in `question_provider::get_questions_by_filter()` nach dem SQL-Abruf, analog zum bestehenden Kategorien-Filter. Beide Filter werden von `view.php` und `present.php` an den Provider durchgereicht.

### 10.13 Content-Quelle nur admin-seitig, „eigene Fragen" pro Aktivität (April 2026)

Nach einer kurzen Gap-Diskussion wurde die Multi-Source-UI aus der Gap-Analyse bewusst verworfen und durch ein einfacheres, klareres Modell ersetzt:

**Entscheidung 1: Admin owns the source.** Es gibt genau **eine** aktive Content-Quelle pro Moodle-Site. Nur die Site-Administration (Capability `moodle/site:config`) wählt, ob `bundled`, `git` oder später `eledia_premium` aktiv ist — unter *Site-Administration → Plugins → Aktivitäts-Module → Check-in*. Lehrkräfte können die Quelle nicht pro Aktivität overriden. Begründung: (a) Verantwortung für Inhaltsqualität und Lizenzkompatibilität liegt bei der Einrichtung, nicht bei jeder einzelnen Lehrkraft, (b) Sync-Log, Cache-Invalidierung und Quota-Betrachtungen werden durch ein zweites Achsenkreuz (pro Aktivität) unnötig verkompliziert, (c) der Premium-Lizenzcheck wird in Phase 2 genau einmal pro Site bewertet, nicht einmal pro Aktivität.

**Entscheidung 2: „Eigene Fragen" als per-Aktivität-Feld.** Lehrkräfte bekommen im Aktivitätsformular einen neuen Abschnitt „Eigene Fragen", der ein **Freitextfeld** enthält — eine Frage pro Zeile (alternativ ein `Repeat`-Element mit je einem Textfeld; Entscheidung bei der Umsetzung nach UX-Test). Damit kann eine Lehrkraft ihre eigenen Check-in-Karten hinzufügen, ohne das Content-Repo zu berühren und ohne Admin-Rechte zu brauchen.

**Eigenschaften der eigenen Fragen:**

- **Scope: nur diese eine Aktivität.** Nicht global, nicht kursweit. In einem Kurs können mehrere Check-in-Aktivitäten nebeneinander existieren, jede mit einer eigenen Liste — z. B. eine „Morgenrunde" mit anderen Fragen als ein „Wochenabschluss".
- **Virtuelle Kategorie „eigene".** Diese Kategorie liegt bewusst **außerhalb** des `schema.json`-Kategorien-Enums (schema bleibt unberührt, Bundles sind nicht betroffen). Sie wird rein plugin-seitig als UI-Label und als interner Marker verwendet.
- **Additive Vermischung in den Zufallspool.** Die eigenen Fragen werden nicht anstelle der Bundle-Fragen gezogen, sondern zusätzlich. Wenn eine Aktivität z. B. Ziel=`checkin+checkout` gewählt hat und der Lehrer drei eigene Fragen eingibt, erscheint jede einzelne Frage (Bundle-Fragen + eigene Fragen) in etwa mit gleicher Wahrscheinlichkeit. Keine Gewichtung in Phase 1.
- **Vererbung der Scope-Metadaten.** Eigene Fragen haben kein eigenes `ziel`, keine `zielgruppe`, keinen `kontext` und keine `sprache`. Sie gelten implizit für alle Ziele der Aktivität und werden in jeder Sprache angezeigt (keine Übersetzung im MVP).
- **Reine Impulskarten.** Keine Rückseite, kein `hat_antwort=true`, keine Quelle, kein Autor. Maximale Einfachheit im UI.
- **Kein Publishing-Status.** Was im Feld steht, ist veröffentlicht. Wer sie ausblenden will, löscht die Zeile.

**Datenhaltung.** Neue Spalte `ownquestions` (`TEXT NOT NULL DEFAULT ''`) auf der Tabelle `elediacheckin`. Persistiert als `\n`-getrennter String (ein Eintrag pro Zeile, leere Zeilen werden beim Speichern verworfen). Kein eigener Table — der Umfang (erwartet: 0–20 Zeilen pro Aktivität) rechtfertigt keine Join-Struktur und kein DB-Schema-Kopfzerbrechen.

**Integration in den Question-Provider.** Der `question_provider::get_questions_by_filter()` bleibt unverändert — er liefert weiterhin nur Bundle-Fragen aus der DB. Stattdessen wird an der Aufrufstelle (`view.php`/`present.php`) die eigene-Fragen-Liste aus der Aktivitäts-Instanz geparst und in den Pool gemerged, bevor `array_rand` aufgerufen wird. Das hält den Provider fachlich sauber (er kennt nur bundle-sourced Content) und bündelt die Vermischungs-Logik an einer Stelle.

**UI.** Im `mod_form` kommt ein neuer Abschnitt „Eigene Fragen" direkt unter „Kategorien" (vor „Zielgruppe/Kontext"). Zur Umsetzung wahrscheinlich ein `textarea` mit 5 sichtbaren Zeilen und Platzhaltertext „Eine Frage pro Zeile. Bleiben leer = nur Bundle-Content." — alternativ Moodles `repeat_elements` mit einzelnen Textfeldern für mehr Struktur. Wird beim Bauen entschieden.

**Was das für die Gap-Analyse bedeutet:** Punkte **C** (Multi-Source-Admin-UI — Auswahl zwischen mehreren parallelen Quellen) und **D** (per-Aktivität-Source-Override) sind damit endgültig **gestrichen**. Stattdessen gibt es genau diese eine Admin-Auswahl plus die per-Aktivität-Eigenfragen. Deutlich einfacher, deutlich klarer in der Verantwortungsaufteilung.

## 11. Aktualisierter Stand der offenen Entscheidungen

Viele Punkte aus Abschnitt 9 sind inzwischen beantwortet:

- **JSON-Schema der Fragen** — ✅ erledigt, siehe 10.1 und `docs/content-schema.json`.
- **Repo-Naming** — ✅ erledigt. Demo: `content_elediacheckin` (öffentlich). Premium: `eledia/checkin-content-premium` (privat, kommt in Phase 2).
- **Bundle-Format** — ✅ erledigt: ein JSON pro Version, keine Delta-Updates im MVP.
- **Sprachen-Handling** — ✅ erledigt: ein Bundle pro Sprache, `language` im Bundle-Header. Kein `translations`-Array pro Frage.
- **Versionierungs-Strategie** — ✅ `bundle_version` als freier String, Empfehlung Kalender-Semver (`2026.04.1`).

Weiterhin offen (blocken Phase 1 nicht):

- **License-Server-Stack** (Phase 2).
- **Public-Key-Rotation-Strategie** (Phase 2).
- **Signing-Verfahren** — Vorschlag ED25519 bleibt stehen (Phase 2).
- **Modus-Auswahl Default vs. Session-Switch** (siehe 10.4) — wird in der Phase-1-View-Implementierung geklärt.

### 10.16 Default-Wert für Custom-Git-URL (April 2026, Version 2026040517)

Das Admin-Setting `mod_elediacheckin/repourl` bekommt einen vorbelegten Wert: `https://github.com/jmoskaliuk/content_elediacheckin.git`. Dabei handelt es sich um das öffentliche Beispiel-Repo von eLeDia, das als Referenz-Bundle und für Demo-Installationen dient. Admins, die eigene Inhalte pflegen, können den Wert jederzeit überschreiben — aber „Content-Quelle = Git wählen, direkt Sync drücken, läuft" ist jetzt ein Default-Happy-Path ohne zusätzliche Suche nach einer URL. Der Hinweis auf die Existenz des Beispiel-Repos steht bereits in `repoheading_desc` und in der Quickstart-Intro des Admin-Screens.

### 10.15 Toggle „Nur eigene Fragen verwenden" (April 2026, Version 2026040516)

Ergänzend zu den additiv gemischten „Eigenen Fragen" (§10.13) bekommen Lehrkräfte einen Toggle, mit dem sie den Bundle-Pool für eine Aktivität ausdrücklich ausschließen können. Anwendungsfall: eine Aktivität, die ausschließlich aus eigenen, firmenspezifischen Fragen zieht, ohne die Site-Inhaltsquelle verändern zu müssen.

**DB-Ebene.** Neues tinyint-Feld `onlyownquestions` auf der Instanz-Tabelle, Default 0. Upgrade-Step 2026040516.

**Semantik.** Wenn aktiv, kürzt `activity_pool::build_pool()` die Bundle-Query komplett weg und gibt nur das Ergebnis von `parse_own_questions()` zurück. Konsequenz: ist der Toggle aktiv, aber das Feld „Eigene Fragen" leer, zeigt die Aktivität den `noquestions`-Empty-State — das ist beabsichtigt und wird im Hilfetext des Toggles explizit erwähnt, damit niemand überrascht ist.

**UI.** Single-Yes/No-Element direkt unter dem Textarea im Abschnitt „Eigene Fragen". Bewusst nicht als „Radiobutton bundle vs. own", weil die additive Semantik der Default-Pfad ist und der exklusive Modus die Ausnahme darstellt.

### 10.14 „Zur vorherigen Frage"-Button als per-Aktivität-Option (April 2026, Version 2026040515)

Lehrkräfte können pro Aktivität einen Button „Zur vorherigen Frage" einblenden, mit dem Lernende zur zuletzt gezogenen Karte zurückspringen. Bewusste Entscheidung gegen ein vor/zurück-Pfeilpaar: bei zufälligem Ziehen ergibt „zurück" nur für genau **einen** Schritt semantischen Sinn, und ein Forward-Button würde bei random-Draw entweder überraschend redraw oder gar nichts tun.

**Umsetzung.** Neues tinyint-Feld `showprevbutton` auf `elediacheckin` (Default 0, Toggle im `mod_form` unter „Anzeigeoptionen"). Der Navigations-State wird in `$SESSION->elediacheckin_history[$cmid]` als bounded Liste mit maximal zwei externalids gehalten (Top = aktuelle Karte). Die Logik kapselt `activity_pool::resolve_navigation()`, damit `view.php` und `present.php` dieselbe Semantik teilen und Back-Schritte über beide Ansichten hinweg konsistent wirken.

**State-Maschine:**

- **Fresh page load** (keine Params) → neuer Random-Draw, Stack wird auf genau diesen einen Eintrag zurückgesetzt. Back-Button bleibt aus. Wichtig: der `$SESSION`-State aus einem früheren Besuch derselben Aktivität wird dabei bewusst verworfen, weil „ich bin gerade erst auf der Aktivität gelandet" semantisch kein Zurück erlaubt. (Entscheidung 2026-04-05 nach Johannes' Rückmeldung: „Button soll erst bei der zweiten Frage erscheinen".)
- **Expliziter „Nächste Frage"-Klick** (`?next=1`) → neuer Random-Draw, wird auf den Stack gepusht, ältester Eintrag bei Bedarf abgeschnitten. Erst jetzt wird der Back-Button sichtbar.
- **„Zur vorherigen Frage"** (`?prev=1`, nur wenn der Stack ≥ 2 Einträge hat) → Top poppen, zuvor sichtbare Karte wird neuer Top. Der Back-Button verschwindet danach, bis der Lernende einen neuen „Nächste Frage"-Klick macht — verhindert Ping-Pong zwischen zwei Karten.
- **Pin per `?q=<externalid>`** (Block-Launcher) → Stack wird auf genau diesen Eintrag zurückgesetzt, Back-Button ist initial aus.

**Robustheit.** Wenn ein im Stack gespeicherter Eintrag nicht mehr im aktuellen Pool liegt (z. B. weil das Bundle zwischen zwei Requests neu synchronisiert wurde), fällt `resolve_navigation` transparent auf `pick_random()` zurück und setzt den Stack neu auf.

### 10.17 Phase 2 MVP — eLeDia-Premium via License-Server + signierte Bundles (April 2026, Version 2026040521)

Nach Abschluss der Phase-1-Stabilisierung wurde die gesamte Phase-2-Achse „Premium" in einem Rutsch aufgebaut. Es gibt jetzt **beide Seiten** des signierten-Content-Pfads: die plugin-seitige Content-Source und einen schlanken, lokal lauffähigen License-Server, gegen den sich der Premium-Modus end-to-end testen lässt.

**Plugin-Seite.** Drei neue Klassen plus Admin-Felder:

- `classes/content/bundle_signature_verifier.php` — kapselt die ED25519-Verifikation mittels PHPs Kern-libsodium (`sodium_crypto_sign_verify_detached`). Der Public Key ist als 64-stellige Hex-Konstante `ELEDIA_PREMIUM_PUBLIC_KEY_HEX` im Source hart verdrahtet — bewusst, weil der Public Key der Trust-Root ist und seine Rotation ein Plugin-Release erzwingen soll. Die Klasse akzeptiert sowohl base64- als auch hex-kodierte Signaturen (via `decode_signature()`) und kennt einen `has_production_key()`-Check, damit der Dashboard-Renderer später vor Dev-Builds mit Demo-Key warnen kann. Aktuell ist der Demo-Key (`35154cbd…e2c0`) gemountet, passend zum Demo-Keypair in `license_server/data/keys/`.
- `classes/content/eledia_premium_content_source.php` — implementiert das bestehende `content_source_interface`. Ablauf: `fetch_bundle()` → `verify_license()` (POST `{license_key, site_hash, site_url, plugin_version, component}` an `/verify`) → zwei authentifizierte GETs für `bundle_url` und `signature_url` mit dem frisch erhaltenen Bearer-Token → Signaturverifikation über die **rohen** Bundle-Bytes → Schema-Validierung → `content_bundle`. Jeder Fehlerpfad wirft eine spezifische `content_source_exception` mit einem eigenen `contenterror_eledia_*`-String, damit der Admin im Sync-Log sofort sieht, ob Key, Lizenz, HTTP, Signatur oder Schema Schuld sind.
- `site_hash` wird plugin-seitig als `sha256(wwwroot + '|' + siteidentifier)` berechnet. `siteidentifier` kommt aus Moodles `get_site_identifier()` und ist stabil, aber nicht PII-tragend — der Server sieht also niemals die echte URL als Primärschlüssel, er kann Installs nur zählen und (bei Bedarf) die optional übertragene `site_url` für Support-Zwecke mitloggen.

Die Content-Source wird in `content_source_registry::build_default_sources()` als dritter Eintrag nach `bundled_content_source` und `git_content_source` registriert. Die beiden neuen Admin-Settings `licenseserverurl` (PARAM_URL, Default `https://licenses.eledia.de`) und `licensekey` (`admin_setting_configpasswordunmask`) werden per `hide_if` **nur** sichtbar, wenn die Inhaltsquelle tatsächlich auf `eledia_premium` steht. Damit bleibt der Admin-Screen für Bundled- und Git-Nutzer:innen aufgeräumt.

**Server-Seite — `license_server/`.** Bewusst als minimaler, dependency-freier PHP-Stack gehalten (PHP 8.1+, PDO/SQLite, Kern-libsodium — kein Composer, kein Framework). Darum passt der ganze Server in ~350 Zeilen verteilt auf fünf `src/*.php` + drei `bin/*.php`:

- **`public/index.php`** ist der Front-Controller, drei Routen: `GET /health`, `POST /verify`, `GET /bundle/{v}` und `GET /signature/{v}`. Routing per `preg_match`, keine Router-Lib. Für lokales Testen reicht `php -S 127.0.0.1:8787 public/index.php`.
- **`src/TokenMinter.php`** mintet stateless Bearer-Tokens als HMAC-SHA256 über `{lid, bv, iat, exp}` mit `APP_SECRET` als Key. Format: `base64url(payload) . '.' . base64url(hmac)` — kein JWT, keine Lib. Hash-Verify via `hash_equals`, Expiry wird serverseitig beim `/bundle`-Zugriff erneut geprüft. Ein Token ist damit an genau eine Bundle-Version gebunden; wer versucht, ein altes Token auf eine neuere Version zu lenken, fliegt mit `token_bundle_mismatch`.
- **`src/Database.php`** legt beim ersten Connect drei Tabellen an (`licenses`, `installs`, `usage_log`) und kapselt `register_install()` mit `(license_id, site_hash)`-Unique: existiert der Hash schon, wird nur `last_seen` aktualisiert; ist er neu, wird `max_installs` hart gegen `COUNT(*)` geprüft. Dadurch bleibt eine Demo-Lizenz mit `max_installs=3` robust gegen Re-Keying und Clock-Drift, solange es wirklich dieselben drei Sites sind.
- **`src/VerifyController.php`** führt die Policy: UUID-Format-Check, 64-hex-Site-Hash, unbekannt/revoked/expired → 401, max_installs überschritten → 403, sonst Token + URLs + `expires_at`. Jeder Fall wird in `usage_log` protokolliert — das ist das erste Audit-Artefakt für Lizenzmissbrauch.
- **`src/BundleController.php`** validiert Bearer-Token, matcht `bv` mit dem URL-Pfad, whitelistet den Versionsstring gegen `^[0-9a-zA-Z.\-]{1,32}$` (verhindert Path-Traversal), und streamt `data/bundles/premium-v{version}.json` bzw. `.sig` mit passenden Cache-Headern. `bundle.fetch`- und `signature.fetch`-Events werden mit Lizenz-ID protokolliert, sodass Ops nachvollziehen kann, welche Site welche Version wann gezogen hat.
- **`bin/generate-keypair.php`** legt ein ED25519-Keypair in `data/keys/<name>.{secret,public}.key` ab (base64-Secret, hex-Public, 0600/0644), **`bin/sign-bundle.php`** signiert eine `bundle.json` mit einem gegebenen Key und schreibt `.sig` als base64 daneben, **`bin/create-license.php`** legt einzelne Lizenzen an, **`bin/seed-demo.php`** ist die One-Shot-Wiederherstellung: Keypair + Demo-License + Demo-Bundle (Rewrite von `mod_elediacheckin/db/content/default.json` mit `bundle_id=eledia-premium-demo`) + frische Signatur, alles idempotent. `seed-demo.php` druckt am Ende Lizenz-Key und Public-Key-Hex, direkt zum Copy-Paste in Plugin-Setting und `bundle_signature_verifier.php`.

**Trust-Modell.** Der Private Key gehört in Produktion nicht auf den License-Server, sondern ausschließlich in den GitHub-Actions-Secret des Premium-Content-Repos: die Action baut das Bundle, signiert es, und lädt Bundle + Signatur auf den Server hoch. Der Server kennt den Private Key nie, verteilt also nur, was ihm aus dem Redaktions-Pipeline-Tree zugespielt wurde. Das bedeutet: selbst eine Kompromittierung des License-Servers kann **keine** gefälschten Inhalte an die Plugins ausliefern — der Plugin-seitige Verifier würde jede Signatur mit einem anderen Schlüssel ablehnen. Im Demo-Setup sitzt der Private Key vorübergehend auf dem gleichen Rechner (für reines Testen), aber das ist ausdrücklich als Dev-only markiert.

**Runtime-Trennung.** `license_server/data/` (SQLite + Keys + signierte Bundles) ist komplett gitignored, nur leere Placeholder-Verzeichnisse sind committet. `.env` wird nie committet, `.env.example` schon. Damit kann ein frischer Checkout durch `php bin/seed-demo.php && php -S 127.0.0.1:8787 public/index.php` in 30 Sekunden fahrbereit sein.

**Was damit in der Gap-Analyse verschoben ist.** Die bisher als „Phase 2 / später" geführten Punkte License-Server-Stack, Public-Key-Rotation-Strategie und Signing-Verfahren (alle in Abschnitt 11 gelistet) sind konzeptionell und implementatorisch **jetzt** abgearbeitet. Was noch fehlt, ist rein betrieblich: echter Produktions-Host, echtes Signing-Keypair, echte Lizenz-Provisionierung aus dem Shopsystem, Logging-Abfluss und Rate-Limits. Keiner dieser Punkte blockiert mehr den Weg in die Plugin-Directory.

### 10.18 Build-Time-Feature-Flag für Premium (April 2026, Version 2026040523)

**Motivation.** Die erste Einreichung ins Moodle Plugins Directory soll bewusst ohne die „eLeDia Premium"-Option ausgeliefert werden: der License-Server-Pfad ist zwar technisch komplett (§10.17), aber das Produkt drumherum — Shop, Rechnung, Support-Flow, `licenses.eledia.de` — existiert noch nicht. Reviewer und frühe Installer sollen kein halbfertiges UI sehen, während wir intern gleichzeitig unverändert an den Premium-Features weiterbauen können.

**Lösung: ein Compile-Time-Flag in einer einzigen Datei.** `classes/feature_flags.php` definiert eine `final class feature_flags` mit einer einzigen Konstanten `PREMIUM_ENABLED` (plus Convenience-Methode `premium_enabled(): bool`). Keine Setting, kein DB-Lookup, kein Runtime-Toggle — der Wert steht im Quellcode, damit ein Release-Build-Script ihn deterministisch umschalten kann, ohne dass Serverzustand oder Admin-Aktion dazwischenfunken.

**Zwei Call-Sites.** Das Flag wird an genau zwei Stellen respektiert:

1. `content_source_registry::build_default_sources()` — fügt `eledia_premium_content_source` der Registry nur dann hinzu, wenn das Flag `true` ist. Fällt das Flag weg, ist die ID `eledia_premium` schlicht unbekannt und `sync_service` fällt über den bereits vorhandenen `get_fallback()`-Pfad automatisch auf `bundled` zurück, falls ein Altbestand das noch in der DB stehen hatte.
2. `settings.php` — die Option `eledia_premium` im Dropdown `contentsource` wird konditional eingehängt, und der gesamte Premium-Konfigurationsblock (Heading, `licenseserverurl`, `licensekey`, die beiden `hide_if`-Aufrufe) sitzt in einem `if (\mod_elediacheckin\feature_flags::premium_enabled()) { … }`.

**Explizit NICHT geflaggt.** Die Premium-Klassen selbst (`bundle_signature_verifier`, `eledia_premium_content_source`) bleiben jederzeit ladbar. Das ist Absicht: PHPUnit soll sie auch im Release-Build-Zustand ausführen können, und der Autoloader soll nicht an Feature-Flags herumraten müssen. Das Flag kontrolliert ausschließlich die **Sichtbarkeit** (Registry-Eintrag + Admin-UI).

**Release-Build-Workflow.** Um vor einer Plugins-Directory-Einreichung das Flag umzuschalten, reicht ein einzeiliger `sed`-Aufruf:

```sh
sed -i '' 's/PREMIUM_ENABLED = true/PREMIUM_ENABLED = false/' \
    classes/feature_flags.php
```

(Unter Linux ohne das leere Argument hinter `-i`.) Danach `git archive`-ZIP bauen, hochladen. Das Flag ist bewusst so formuliert, dass genau eine Zeile ersetzt werden muss — Grep-freundlich, Merge-Conflict-arm, und jeder, der die Datei liest, sieht in den Doc-Comments sofort wie der Build-Flow funktioniert. Sobald der Premium-Backend-Stack produktiv ist, entfällt der `sed`-Schritt einfach (oder es wird auf eine dedizierte `bin/build-release.sh` umgezogen, die diesen und eventuelle Folgeschritte kapselt).

**Warum nicht via Admin-Setting / Site-Config / Environment-Variable.** Alle drei würden dasselbe Ziel erreichen, aber mit höherer Angriffsfläche: Admin-Setting ist per Hand togglebar (Reviewer könnte es anschalten), Environment-Variable hängt vom Deployment-Kontext ab (in Docker-Compose vs. Bare-Metal unterschiedlich), und eine Site-Config-Zeile müsste in `config.php` gepflegt werden, was wir bei Kunden nicht garantieren können. Eine PHP-Konstante im Plugin-Tarball ist dagegen **immer** der Zustand, den wir tatsächlich ausgeliefert haben — was beim Release exakt dieses Ziel erfüllt.

### 10.19 UX-Runde April 2026 (Version 2026040524 / 2026040525)

Dieser Abschnitt bündelt eine Serie kleiner, aber semantisch relevanter UX-Änderungen aus der Testing-Inbox. Sie verändern kein Schema substanziell und keine Content-Distribution-Semantik; sie werden aber hier dokumentiert, damit die Begründungen später nachvollziehbar sind (viele davon sind „kleine Dinge mit großem ‚warum‘").

**„Lernreflexion" statt Lexikon-Content (Ziel `learning`).** Ursprünglich war das Ziel `learning` als Micro-Learning-Modul gedacht — methode/theorie/tool/modell-Kategorien mit Frage **und** Musterantwort. In der Praxis vermischt das zwei sehr unterschiedliche Lernszenarien: „Wissen abfragen" vs. „Reflexion anregen". Johannes hat das beim Testen sauber getrennt: Wissensabfragen mit Antworten sollen über das reguläre Quiz-Modul laufen; der Check-in soll für die zweite Hälfte — **offene Reflexionsimpulse** — zuständig sein. `learning` trägt jetzt das Label „Lernreflexion" (DE) / „Learning reflection" (EN), die sechs Bundle-Fragen sind auf `hat_antwort: false` umgestellt, und die Kategorien sind zu `tagesreflexion`, `transfer`, `aha`, `hindernis`, `meta` umgebaut. Die alten Kategorien (methode/theorie/tool/modell) sind ersatzlos raus; die Enum-Definitionen liegen jetzt synchron in `schema_validator.php` + `schema.json` (mod + content-repo).

**Tri-state „Eigene Fragen" (§10.15-Update).** Der ursprüngliche Yes/No-Toggle `onlyownquestions` hatte eine versteckte dritte Option, die niemand benennen konnte: „Ich habe eigene Fragen ins Textarea geschrieben, **möchte aber gerade nur das Bundle sehen**." Mit „Nein" wurden sie trotzdem gemischt, mit „Ja" wurde das Bundle ganz abgeschaltet — die „Bundle-only trotz gefülltem Textarea"-Variante gab es nicht. Neues Feld `ownquestionsmode` mit drei sauberen Werten: `0 = mixed` (Default; bisheriges „Nein"), `1 = only_own` (bisheriges „Ja"), `2 = none` (neu — Bundle only, eigene Fragen werden ignoriert). Die Umbenennung läuft über `$dbman->rename_field()`, damit vorhandene 0/1-Werte aus `onlyownquestions` semantisch identisch erhalten bleiben. `activity_pool::build_pool()` liest primär das neue Feld, fällt aber auf das alte zurück. `mod_form.php` reiht die Abschnitte jetzt: *Allgemein → Check-in → Anzeigeoptionen → Eigene Fragen*. `hideIf` blendet das Textarea aus, wenn `none` gewählt ist.

**Block `block_elediacheckin` auf Startseite / Frontpage.** `applicable_formats()` setzt jetzt sowohl `site-index` (Moodle-core-Kanonform) als auch `site` (Kurzform) auf `true`, zusätzlich zu `course-view`. Moodle behandelt die Frontpage als Kurs mit `SITEID`; `get_fast_modinfo($COURSE)` im Edit-Form löst korrekt auf. Damit kann ein Admin eine Check-in-Aktivität auf der Frontpage platzieren und den Block beliebig anheften — ideal für site-weite „Motivations-Karte des Tages". Dashboard/My-Page bleiben bewusst aus (`my => false`).

**Zitate mit Autor-Attribution.** Zitate sind das einzige Ziel, bei dem die Autorschaft integral zum Inhalt gehört — „Culture eats strategy for breakfast" ohne *— Peter Drucker* ist keine Quelle. Die Templates (embedded `view.mustache`, Fullscreen-Overlay, Popup `present.mustache`) rendern bei `ziel === 'zitat'` einen zweiten Absatz mit `— {{question.author}}` unter dem Zitat plus eine `--quote`-Klasse (italic serif, zentriert). Template-Kontexte in view.php + present.php tragen die drei Flags `isquote`, `hasauthor`, `author`. Das `autor`-Feld im Bundle hatte bisher den Platzhalter „eLeDia Redaktion"; bereinigt auf den tatsächlichen Urheber (Henry Ford, Steve Jobs, Simon Sinek, „Afrikanisches Sprichwort", …), `quelle` bleibt als Bibliographie-Feld daneben.

**Info-Link im Activity Chooser (`eledia.de/mod_elediacheckin`).** Moodles Chooser rendert `modulename_help` als HTML-Block unter dem Aktivitätsnamen. Wir hängen am Ende des Strings (DE + EN) ein `<a href="https://www.eledia.de/mod_elediacheckin" target="_blank" rel="noopener">` an — direkteste Art, einen „Mehr erfahren"-Link neben die Beschreibung zu setzen, ohne Custom-Chooser-Hook. Alternativen (`modulename_link`, `FEATURE_MOD_PURPOSE`, `mod_xxx_get_coursemodule_info()`) bieten entweder nur Pfade relativ zu docs.moodle.org oder Plätze an anderer Stelle.

**User-Tour für Lehrkräfte.** `db/tours/teacher_checkin_tour.json` definiert eine fünfstufige `tool_usertours`-Tour (Welcome → Karte → Ziel-Picker → Nächste-Frage-Button → Popup/Fullscreen-Launchers), gefiltert auf `editingteacher`/`teacher`/`manager` und `pathmatch=/mod/elediacheckin/view.php%`. `tool_usertours` importiert Tours aus Plugin-Verzeichnissen nicht automatisch — `db/install.php` ruft `mod_elediacheckin_install_bundled_tours()` auf, die jede JSON-Datei durch `\tool_usertours\manager::import_tour_from_json()` schickt. Upgrade-Pfad zu `2026040525` ruft dieselbe Funktion auf, damit bestehende Instanzen die Tour nachträglich bekommen. Duplikate kann ein Admin unter *Site admin → Appearance → User tours* manuell löschen.

**Firefox: `window.open` ohne `popup=yes`.** Firefox ≥ 109 behandelt `window.open(url, name, 'width=…,height=…,…')` ohne explizites `popup=yes` als Tab-Request — `width`/`height` werden ignoriert. Fix: `popup=yes` als erste Feature-Flag in `POPUP_FEATURES` (`amd/src/view.js` **und** `amd/build/view.min.js`, weil Moodle mit aktivem Cache ausschließlich die minifierte Variante lädt).

**Duplikat der Aktivitätsbeschreibung entfernt.** `view.php` rief vor dem Template-Render explizit `$OUTPUT->box(format_module_intro(…))` auf — Überbleibsel aus Moodle-3.x. Seit Moodle 4.x rendert `$PAGE->activityheader` den Intro automatisch. Die explizite `$OUTPUT->box()`-Zeile ist entfernt; ein Kommentar hält fest, warum hier bewusst kein zweiter Intro gerendert wird.

### 10.20 Barrierefreiheits-Pass + Release-Finalisierung (April 2026, Version 2026040527)

Mit dem ersten Release im Blick wurde die View-Seite einmal gegen die WAI-ARIA-Authoring-Practices-Checkliste gezogen. Drei Ebenen wurden angefasst:

**Semantik in den Templates.** Der Ziel-Picker (view + present) war ein `<div class="elediacheckin-ziel-picker">` — technisch Links, semantisch aber ohne Gruppenrolle. Jetzt: `<nav aria-label="{{strzielnavlabel}}">` mit `aria-current="page"` auf dem aktiven Link. Der frühere `role="tablist"`/`role="tab"`-Versuch wurde **wieder entfernt**, weil `tablist` Pfeil-Tasten-Navigation verspricht, die wir nicht implementieren — falsche Versprechen an Screenreader-Nutzer sind schlimmer als gar keine Rolle. Die Fragekarte hat ein `lang`-Attribut, sobald eine Frage über `question.lang` die Sprache mitliefert (wichtig für englische Zitate auf einer deutschen Instanz — ohne `lang` lesen Screenreader „Culture eats strategy for breakfast" mit deutscher Phonetik vor).

**Vollbild-Dialog mit Focus-Management.** Die `.elediacheckin-fullscreen`-Overlay ist jetzt ein echtes Modal: `role="dialog"`, `aria-modal="true"`, `aria-labelledby` auf einen visuell versteckten `<h2 class="sr-only">`-Titel, `tabindex="-1"` damit es programmgesteuert Fokus annehmen kann. `amd/src/view.js` speichert beim Öffnen das vorige `document.activeElement`, setzt den Fokus auf den Close-Button, trappt `Tab`/`Shift+Tab` innerhalb des Dialogs (inkl. Fallback auf das Dialog-Element selbst, wenn kein fokussierbares Kind vorhanden ist) und restauriert den Fokus beim Schließen. `Esc` schließt wie bisher. Ohne diese Schritte war die Overlay für Tastatur-only-Nutzer eine Sackgasse: man konnte aus dem Dialog heraustabben, ohne zu merken, dass der fokussierte Link hinter einer visuellen Vollbild-Schicht lag.

**Kontrast + sichtbarer Fokus.** Die `styles.css` hat jetzt eine zentrale `:focus-visible`-Regel für alle interaktiven Plugin-Controls (Icon-Buttons, Ziel-Buttons, „Nächste Frage", Launchers, Fullscreen-Close): 3 px orange Outline + 5 px halbtransparenter Ring. Das überschreibt Boost-Defaults, die bei farbigen Buttons teils zu schwachen Fokus-Ring liefern. Zusätzlich ein `.sr-only`-Fallback für die Dialog-Titel-Elemente, falls das Theme die Utility-Klasse nicht selbst mitbringt.

**Premium-Flag für Release auf `false`.** `classes/feature_flags.php::PREMIUM_ENABLED` ist für den ersten Submit-Build auf `false` gestellt. Die License-Server-UI erscheint damit nicht in den Plugin-Settings, und das Dropdown „Inhaltsquelle" zeigt nur *Bundled* + *Git*. Die Klassen (`bundle_signature_verifier`, `eledia_premium_content_source`) bleiben lauffähig, damit PHPUnit sie weiterhin ausführen kann. Johannes schaltet nach der ersten Testrunde intern wieder auf `true`.

**`$CFG`-Scope-Bug im Verbindungstest gefixt.** `git_content_source::fetch_raw()` lud `filelib.php` über `require_once($GLOBALS['CFG']->libdir . '/filelib.php');`. Das lädt zwar die Datei selbst, aber deren Top-Level-Code enthält `require_once($CFG->libdir . '/filestorage/file_exceptions.php');` — und `$CFG` als bare Variable ist im Methoden-Scope des Aufrufers nicht definiert. Folge: `Warning: Undefined variable $CFG`, danach `require_once(/filestorage/file_exceptions.php): Failed to open stream`, und der Verbindungstest bricht komplett ab. Fix: `global $CFG;` **vor** dem `require_once`, dann normale Variable statt `$GLOBALS[]`. Ein ausführlicher Kommentar direkt darüber erklärt die Fußangel, damit das nicht wieder rausrefactored wird.

**Save-Changes-Button oberhalb des Sync-Panels.** Core-Moodle hängt den Submit-Button einer `admin_settingpage` immer an das Form-Ende, d.h. nach dem letzten Setting — und der `dashboard_renderer`-Output ist technisch das letzte Setting (ein `admin_setting_heading`). Das Ergebnis war Panel → Save-Button, Johannes wollte Save-Button → Panel. Lösung ohne Umbau auf `admin_externalpage`: der `dashboard_renderer::render()`-String enthält am Ende einen kleinen `<script>`-Block, der `#admin-dashboardpanel` im DOM hinter den `<form>`-direkten-Child des Submit-Inputs verschiebt. Graceful Degradation: ohne JS steht das Panel oberhalb — weiterhin vollständig nutzbar, nur nicht in der bevorzugten Reihenfolge. Das ist weniger sauber als eine echte Struktur-Änderung, aber admin_externalpage hätte bedeutet, die gesamte Konfig-Seite mit MoodleQuickForm neu zu schreiben — zu viel Aufwand für eine Reihenfolgefrage.

### 10.21 Frontpage-Block — welche Check-in-Aktivität wird ausgewählt? (April 2026, block-version 2026040504)

**Die Frage.** Seit `applicable_formats()` neben `course-view` auch `site`/`site-index` erlaubt (§10.19), kann `block_elediacheckin` direkt auf die Moodle-Startseite gezogen werden. Johannes' Inbox-Frage: *„Welche Check-in-Aktivität wird da ausgewählt? Braucht es noch ein Konzept?"*

**Mechanik, die wir heute haben.** Das Edit-Form baut die Dropdown-Liste aus `get_fast_modinfo($COURSE)->get_instances_of('elediacheckin')`. Moodle behandelt die Startseite als regulären Kurs mit der ID `SITEID` (meist `1`); `$COURSE` ist auf der Startseite dieser SITEID-Kurs. Das heißt konkret: **das Dropdown listet auf der Startseite exakt die Check-in-Aktivitäten, die auf der Startseite selbst angelegt wurden** — dieselbe Logik wie in jedem anderen Kurs, nur mit `SITEID` als Kurs. Es gibt keinen impliziten „Such eine beliebige Aktivität aus dem ganzen Moodle"-Modus.

**Warum keine kursübergreifende Verknüpfung.** Die intuitive Alternative wäre: *„Auf der Startseite soll das Dropdown alle Check-in-Aktivitäten aus allen Kursen anzeigen."* Dagegen sprechen drei harte Gründe:

1. **Enrolment/Capability-Mismatch.** `mod/elediacheckin:view` wird per Default an Rollen innerhalb eines Kurses gebunden (student, teacher, …). Ein Besucher der Startseite ist im Zielkurs nicht eingeschrieben — `has_capability()` liefert `false`, der Block würde leer bleiben. Workaround wäre eine `can_access_course`-Emulation, die aber konsequent über sämtliche `require_login()`-Aufrufe in `view.php`/`present.php` ausgehebelt werden müsste. Das ist ein Security-Minenfeld.
2. **Sichtbarkeits-Annahmen.** Kursmodule dürfen verborgen, terminiert, zielgruppen-restriktiert sein (`availability_*`). Ein Launcher auf der Startseite, der an eine Aktivität in einem hidden course gekoppelt ist, wäre eine Leak-Oberfläche: der Block-Preview würde die Frage zeigen, der Klick führt zu „Access denied".
3. **Backup-/Kurslöschung.** Wird der Zielkurs gelöscht oder migriert, zeigt der Startseiten-Block ins Leere. Der heutige Modus (Block + Aktivität im selben Kurs bzw. auf derselben Startseite) ist gegen Kurslöschung implizit robust, weil Moodle beides zusammen löscht.

**Was also tun, wenn jemand auf der Startseite einen Check-in-Block möchte?** Die korrekte Vorgehensweise ist: **Zuerst eine Check-in-Aktivität auf der Startseite anlegen** (*Site administration → Front page → Front page settings → Turn editing on → Add activity*), dann den Block auf der Startseite platzieren und im Dropdown diese Aktivität wählen. Das ist exakt dasselbe Muster wie in einem Kurs — nur der „Kurs" ist eben die Frontpage selbst. Moodle unterstützt das seit jeher, es ist nur vielen Admins nicht bewusst.

**Mehrere Aktivitäten nebeneinander.** Weil sowohl `instance_allow_multiple()` als auch jede zusätzliche Check-in-Aktivität auf der Startseite unabhängig konfiguriert wird, lassen sich sinnvolle Kombinationen bauen:
- Eine Aktivität mit `ziele = ['checkin']` + `zielgruppen = ['team']` für die Team-Kachel, eine zweite mit `['zitat']` für eine „Zitat des Tages"-Spalte. Beide liegen auf der Frontpage, jeder Block verlinkt auf eine eigene.
- Unterschiedliche Sprachfallbacks (DE vs. EN) pro Block — praktisch für mehrsprachige Portale.
- Unterschiedliche „eigene Fragen"-Pools (§10.15-Tri-state), die Startseiten-Admin kann also ein Set kuratieren, ohne dass irgendein Kurskonstrukt aufgemacht werden muss.

**UX-Folge für das Edit-Form: Empty-State-Hinweis.** Solange auf der Startseite keine Aktivität existiert, sah das Dropdown bisher nur wie ein trauriges „Choose…" aus, ohne Hinweis warum es leer ist. Das war vermutlich der Kern von Johannes' „braucht es ein Konzept?". Fix: wenn `$activities` leer ist **und** der aktuelle Nutzer `moodle/course:manageactivities` im aktuellen Kurs hat, rendert das Edit-Form eine `alert-warning`-Zeile mit Direkt-Link auf `course/modedit.php?add=elediacheckin&course=<SITEID>`. Einmal geklickt, Aktivität angelegt, zurück zum Block, Dropdown ist nicht mehr leer. Der Hilfetext des `linkedactivity`-Feldes (DE + EN) erklärt zusätzlich, dass auf der Startseite nur dort angelegte Aktivitäten erscheinen, und dass mehrere Check-in-Aktivitäten parallel betrieben werden können.

**Backend-Code bleibt unverändert.** `get_content()`, `activity_pool::pick_random()`, Capability-Check — alles funktioniert auf der Startseite genauso wie in einem Kurs, weil Moodle die Frontpage intern als Kurs führt. Es musste also außer dem `applicable_formats()`-Eintrag und dem Empty-State-Hint im Edit-Form nichts angefasst werden.

**Nicht-Ziele.** Was wir bewusst **nicht** bauen:
- Kein „site-weites" Check-in, das ohne zugrundeliegende Aktivität rendert (würde `activity_pool`-Interface zerbrechen).
- Kein Auto-Create-Trick, der beim ersten Block-Placement magisch eine Hidden-Aktivität anlegt (erklärt sich dem Admin nicht und ist undebuggbar, wenn später etwas schiefläuft).
- Kein Cross-Course-Picker (siehe oben, drei Gründe gegen).

### 10.22 Sync-Fehler-Diagnostik für Git-Content-Source (April 2026, Version 2026040528)

Beim ersten Release-Test hat Johannes „Sync jetzt ausführen" mit diesem Fehler gesehen:

> Missing or non-string bundle field: schema_version. | Missing or non-string bundle field: bundle_id. | Missing or non-string bundle field: bundle_version. | Missing or non-string bundle field: language. | Missing or invalid "questions" array.

Die bundle.json im Content-Repo (`jmoskaliuk/content_elediacheckin`) enthält alle Felder korrekt — an der Quelle liegt es nicht. Dass **alle vier** Header-Felder **und** das `questions`-Array als fehlend gemeldet werden, ist ein Fingerabdruck dafür, dass `$decoded` ein Array ist (sonst würde vorher `contenterror_gitparse` greifen), aber keines der erwarteten Schlüssel enthält. Das ist das typische Muster, wenn die konfigurierte URL auf einen **anderen JSON-Endpunkt** zeigt als auf die rohe Datei:

- `https://api.github.com/repos/<o>/<r>/contents/bundle.json` → Antwort: `{name, path, sha, url, content: "base64…", encoding, …}` — kein einziges Bundle-Feld.
- `https://api.github.com/repos/<o>/<r>/contents/` → Antwort: **indiziertes Array** von File-Entries.
- Eine selbstgehostete Wrapping-API: `{"status": "ok", "data": {…bundle…}}`.

`schema_validator::validate_bundle_header()` sieht in all diesen Fällen exakt denselben „alles fehlt"-Fingerabdruck, den Johannes bekommen hat. Die bisherige Fehlermeldung sagt **was fehlt**, aber nicht **was stattdessen ankam** — diagnostisch zu wenig, wenn der Admin nicht weiß, welche URL er wirklich fetcht.

**Fix: diagnostische Verbesserung in `git_content_source::fetch_bundle()`** (keine Auto-Korrektur, nur mehr Kontext in der Exception):

1. **Top-Level-Keys werden mitgeloggt.** Wenn die Validierung fehlschlägt, hängt `fetch_bundle()` die ersten 12 Schlüssel des empfangenen Objekts an die Exception-Message: `top-level keys received: [name, path, sha, size, url, html_url, …]`. Wer den Fehler sieht, erkennt sofort „Aha, das ist die GitHub-API-Contents-Antwort".
2. **Body-Preview bei JSON-Parse-Fehlern.** Der `contenterror_gitparse`-Zweig zeigt jetzt die ersten 200 Zeichen der Response (mit zusammengefaltetem Whitespace), damit man bei HTML-Antworten sofort `<!DOCTYPE html>` oder eine Fehlermeldung sieht.
3. **URL-Heuristik.** Wenn die konfigurierte URL einem der vier häufigen Fehlermuster entspricht, kommt ein konkreter Hinweis dazu:
   - `github.com/.../blob/...` → „`/blob/` liefert HTML, nimm `raw.githubusercontent.com/...`."
   - `api.github.com/.../contents/...` → „Metadaten-Objekt, nicht die rohe Datei."
   - URL endet auf `.git` → „Clone-URL, nicht die rohe Datei."
   - URL endet nicht auf `.json` → „Prüfe ob die URL wirklich auf bundle.json zeigt."

Diese Hinweise landen im Sync-Log (`elediacheckin_sync_log.message`-Spalte) und damit auch im Sync-Status-Panel unter den letzten Sync-Versuchen. Bei der nächsten fehlgeschlagenen Sync-Runde ist damit sofort klar, was zu tun ist.

**Warum keine Auto-Korrektur.** Ich könnte `github.com/.../blob/...`-URLs beim Speichern automatisch zu raw-URLs umschreiben. Absichtlich nicht gemacht: das verschleiert Intent (was, wenn ein Admin tatsächlich eine eigene URL mit `/blob/` im Pfad benutzt?), und die Fehlermeldung ist jetzt klar genug, dass der Admin die URL einmalig richtig setzt. Explicit > magisch.

**Nicht betroffen: `test_connection()`.** Der Verbindungstest in `admin/actions.php` prüft nur `fetch_raw()` (HTTP-Status + nicht-leere Antwort), nicht das Schema. Wer grün testet und dann rot syncht, hat genau diese Kategorie von Problem — die bessere Diagnostik in `fetch_bundle()` ist der richtige Ort, weil der Schema-Validator dort läuft. Perspektivisch könnte `test_connection()` zusätzlich einen Probe-Decode mit Top-Level-Key-Check machen; das wäre eine kleine zweite Iteration.

### 10.23 Karten-Stage auf Volle Breite + Lehrkräfte-Tour repariert (April 2026, Version 2026040529)

**Kontext.** Zwei Items aus der Testing-Inbox, die beide optisch/funktional sichtbar waren, aber unterschiedliche Ursachen hatten.

**Problem 1 — Karte wirkte „eingerückt".** Die `.elediacheckin-stage`-Klasse hatte bis jetzt `max-width: 760px; margin: 0 auto;`. Das war ursprünglich als Lesbarkeits-Cap gedacht, führte aber dazu, dass die Karte optisch nach rechts gerückt wirkte, während der Activity-Header und die Intro-Alert oben in der vollen Content-Spalte saßen. Im direkten Vergleich mit LeitnerFlow (volle Breite) wirkte das wie ein Layout-Bug, nicht wie eine bewusste Entscheidung.

**Entscheidung.** `max-width: none; margin: 0;` — die Karte nimmt jetzt die komplette Content-Spalte ein, konsistent mit allen anderen eLeDia-Plugins. Falls die Lesbarkeit im Langtext-Fall leidet, ziehen wir den Cap nicht wieder auf die äußere Stage, sondern auf die innere `.elediacheckin-question-text` — dann bleibt der Kartenrahmen breit und nur der Textkörper wird schmaler.

**Problem 2 — Lehrkräfte-Tour war im Admin-UI leer.** Die in §10.19 eingeführte bundled Tour (`db/tours/teacher_checkin_tour.json`) wurde zwar beim Install/Upgrade importiert, zeigte aber im Site-Admin → Tours einen Datensatz mit 0 Schritten. Ursache: Das JSON enthielt `configdata` sowohl auf Tour- als auch auf Step-Ebene als verschachteltes Objekt. `tool_usertours\tour::reload_from_record()` ruft intern `$this->config = json_decode($record->configdata)` auf — ein stdClass-Input wirft in PHP 8 einen TypeError, der im import_tour_from_json-Aufruf stumm scheitert und die Step-Inserts abbricht. Zusätzlich lag `filtervalues` auf Top-Level statt innerhalb von `configdata`.

**Fix.** Tour-JSON an das tatsächlich erwartete Moodle-Format angepasst: `configdata` als JSON-encoded String, `filtervalues` in `configdata` verschachtelt, `contentformat: "1"` (FORMAT_MOODLE) pro Step. Referenz: `moodle/public/admin/tool/usertours/tours/40_tour_navigation_course_teacher.json` aus dem Core. Zusätzlich ein Upgrade-Step, der alle Tours mit `pathmatch LIKE '/mod/elediacheckin/%'` löscht und die bundled Tour aus dem reparierten JSON neu importiert — sonst behalten Upgrader die leere Version.

**Lehre.** Bei jedem „Feature-Skelett, das nichts tut" zuerst gegen ein funktionierendes Core-Beispiel diffen, statt am eigenen Code zu suchen. Der Format-Drift zum Core wäre bei einem Side-by-Side sofort aufgefallen; das Schreiben nach Schema-Gefühl hat zehn Minuten länger gedauert als der spätere Fix.

### 10.24 Save-Button-Reorder-Bug, Premium-Flip-Commit + Tour-Sichtbarkeits-Bug (April 2026, Version 2026040530/2026040531)

Drei Folgefehler aus der Verifikationsrunde direkt nach §10.23, alle mit demselben Muster: „Feature war implementiert, aber an einer Stelle unterbrochen, die niemandem auffiel, bis der User den Screenshot geschickt hat".

**Fehler 1 — `PREMIUM_ENABLED` nie ins Repo committet (v2026040530).** Die in §10.18 beschriebene Abschaltung des Premium-Dropdowns war lokal im Workspace auf `false`, im Konzept-Doc als erledigt dokumentiert und in der Testing-Inbox abgehakt — aber die Konstante selbst stand auf `main` noch auf `true`. Johannes' deployed Instanz zeigte deshalb weiter den Dropdown-Eintrag „eLeDia Premium-Fragen" und die komplette „eLeDia Premium (license server)"-Section. Fix ist ein einzeiliger Flip, der die Gates (Settings-Heading + Dropdown + Registry-Registrierung) alle gleichzeitig scharfschaltet, weil sie schon seit v2026040523 auf `feature_flags::premium_enabled()` hängen. Lehre: nach lokalen Edits immer `git log -- <file>` verifizieren, nicht nur den Workspace-Zustand. Ich halte das in einer Memory fest.

**Fehler 2 — Save-Button-Reorder-JS suchte eine nicht existierende ID (v2026040531).** Die in §10.19 eingeführte DOM-Reorder-Technik für den Save-Changes-Button sollte `document.getElementById('admin-dashboardpanel')` finden und hinter den Submit-Container verschieben. Die Annahme: `admin_setting_heading` bekäme automatisch ein Wrapper-Element mit ID `admin-<name>`, wie es das normale `setting.mustache` tut (core_admin setting.php Zeile 9334: `$context->id = 'admin-' . $setting->name`). Tatsächlich rendert `setting_heading.mustache` aber überhaupt keinen Wrapper mit ID — nur `<h3 class="main">` + optional `<div class="box generalbox formsettingheading">`. `getElementById` returnte `null`, der Script-Block brach beim ersten Check ab, und der Save-Button blieb unterhalb des Panels. Fix: der `dashboard_renderer::render()`-Output wird jetzt explizit in einen `<div id="elediacheckin-dashboardpanel">` gewrappt, und die JS sucht die neue ID. Als Bonus: das Script nutzt jetzt einen `MutationObserver` mit 5-Sekunden-Timeout als Fallback, falls ein Theme-Override den Submit-Button asynchron rendert. Idempotent via `MOVED`-Flag. Lehre: beim Einfügen von JS-Hooks in Moodle-Admin-UIs immer den tatsächlichen Template-Output checken, nicht die Core-API-Namen — `setting_heading.mustache` und `setting.mustache` haben unterschiedliche Strukturen.

**Fehler 3 — User-Tour unsichtbar + einsprachig (v2026040531).** Nach dem JSON-Format-Fix aus §10.23 stand die Tour korrekt mit 5 Schritten im Admin-UI, wurde aber beim Besuch von `/mod/elediacheckin/view.php` nie angezeigt. Zwei Ursachen:

Erstens, der Rollen-Filter enthielt nur `["editingteacher","teacher","manager"]`. Moodles `tool_usertours` behandelt den Site-Admin-Status aber als Sentinel-Wert `"-1"` — wenn `-1` nicht in der Rollenliste steht, matcht auch ein Site-Admin die Tour nicht, selbst wenn er im Moment den Kurs im Rollen-Switch als „editingteacher" sieht. Core-Referenz-Tours setzen deshalb immer `["−1","coursecreator","manager","teacher","editingteacher"]` (`40_tour_navigation_course_teacher.json`). Unsere Tour hatte `-1` nicht in der Liste; Johannes testete als Site-Admin und sah nichts. Fix: `-1` + `coursecreator` in den Rollen-Filter aufgenommen.

Zweitens, die Tour-Texte lagen hartcodiert auf Deutsch im JSON. Das funktionierte auf der deutschen Oberfläche, aber englischsprachige Nutzer sahen dieselben deutschen Texte. `tool_usertours` unterstützt pro Text-Feld einen Lang-String-Referenz-Syntax `stringid,component`, den die Klasse `helper::get_string_from_input()` mit einem Regex erkennt und bei Anzeige durch `get_string()` auflöst. Alle Text-Felder (Name, Description, endtourlabel, je Step Title + Content) sind jetzt auf dieses Format umgestellt; die Strings selbst liegen parallel in `lang/de/elediacheckin.php` + `lang/en/elediacheckin.php` als `checkintour_*`. Der Vorteil: die Übersetzungen leben jetzt im normalen Moodle-Sprachpaket-Workflow, AMOS kann sie picken, Crowdsourcing-Übersetzer kommen ran.

Weil beide Tour-Fixes nur wirken, wenn die Tour in der DB komplett neu angelegt wird (`persist()` auf einer bestehenden Tour würde die Schritte nicht erneut einfügen), gibt es einen neuen Upgrade-Step 2026040531, der — wie schon 2026040529 — alle Tours mit `pathmatch LIKE '/mod/elediacheckin/%'` löscht und die reparierte JSON neu importiert.

### 10.25 Save-Button-Spacing + Heading-Orphan-Fix (April 2026, Version 2026040532)

Nach Deploy von v2026040531 war der Save-Button endlich oberhalb der Panel-Karte (Reorder griff), aber zwei kosmetische Nebeneffekte wurden sichtbar: (1) die `<h3>Sync status</h3>`-Überschrift aus dem `admin_setting_heading` stand nach wie vor **oberhalb** des Save-Buttons — getrennt vom Panel-Content, der unten auftauchte — weil das Reorder-JS nur das innere `#elediacheckin-dashboardpanel`-Wrapper-Div verschob, nicht aber den gesamten Form-Item-Container, der auch die h3 enthält. (2) der Abstand zwischen dem Save-Button-Row und der „Current state"-Karte war zu eng, die beiden klebten optisch zusammen.

Beides mit einem Patch im selben Reorder-Script gelöst: statt `form.insertBefore(panel, submitContainer.nextSibling)` zu rufen, läuft das Script jetzt auch vom `panel`-Element ausgehend per `while`-Loop hoch bis zum direkten Form-Kind (also dem Form-Item-Wrapper, der `<h3>` + description-div enthält), und verschiebt **diesen Container** statt nur des Panel-Divs. Dadurch wandern h3 + Card-Content gemeinsam an die neue Position. Zusätzlich setzt das Script nach erfolgreichem Reorder ein Inline-`style.marginTop = '2.5rem'` auf den verschobenen Container, damit visuell Luft zwischen Save-Button-Row und der neu angehängten Heading entsteht. Der Inline-Style wird nur angewandt, wenn der Reorder tatsächlich durchläuft — ohne JS (graceful degradation) bleibt der Default-Abstand.

Einziger Nachteil: der Spacing-Fix hängt an der JS-Ausführung. Ein reines CSS-Äquivalent ginge nicht, weil im DOM-Layout ohne JS das Panel oberhalb des Save-Buttons steht und dort kein Top-Margin nötig ist. Den Kompromiss nehme ich, solange der Reorder-Pfad der Primärpfad ist.

**Nachtrag v2026040533 — Reorder-Regression.** Der §10.25-Patch walkte im ersten Wurf sowohl `submitContainer` als auch `panelContainer` bis zum direkten Kind des `<form>`-Elements hoch. Auf Moodle-Admin-Settings-Seiten ist das Kind des Forms aber das `<fieldset>`, das den kompletten Settingpage-Inhalt (inkl. Save-Button und Panel) als Geschwister enthält. Beide Walks landeten also beim selben Fieldset, und `form.insertBefore(fieldset, fieldset.nextSibling)` war ein No-op — der Reorder griff nicht mehr, Save-Button rutschte in die native Reihenfolge ganz nach unten, unter die Log-Tabelle. Fix: statt zum Form-Level-Kind hochlaufen, läuft das Script jetzt vom Submit-Button aus hoch, bis sein Parent das Panel-Element enthält (`submitContainer.parentNode.contains(panel)`) — dieser Parent ist der eigentliche gemeinsame Container (innerhalb des Fieldsets), in dem Submit-Row und Heading-/Description-Siblings als direkte Kinder liegen. `panelContainer` läuft dann vom Panel aus hoch, bis sein Parent genau dieser Shared-Parent ist. `sharedParent.insertBefore(panelContainer, submitContainer.nextSibling)` schiebt das Panel an die richtige Stelle, und die vorangehende `<h3>Sync status</h3>` wird als Extra-Schritt ebenfalls mitgenommen. Lehre: „direktes Kind des Form-Elements" ist auf Admin-Settings-Seiten **nicht** die sinnvolle Granularität, weil das Fieldset alles zusammenfasst. Immer vom Ziel-Element (`panel`) rückwärts zum Submit-Button arbeiten, nicht umgekehrt.

### 10.26 Admin-Settings-User-Tour (April 2026, Version 2026040534)

Zweite User-Tour für `mod_elediacheckin`, diesmal für die Plugin-Einstellungsseite. Motivation: Lehrkräfte-Tour (§10.23/§10.24) führt durch die View-Seite einer konkreten Check-in-Aktivität, erklärt also die Nutzersicht. Admins brauchen aber einen anderen Einstieg — sie landen als erstes in Site admin → Plugins → Check-in und müssen dort verstehen, wo sie die Inhaltsquelle wählen, wie sie speichern und wo sie den Sync-Status einsehen. Johannes hatte das Ende März als offenen Inbox-Punkt notiert („Kannst Du auch noch eine User-Tour für die Settings erstellen?").

**Aufbau.** Fünf Schritte, parallel zur Lehrkräfte-Tour:

1. **Welcome (unattached).** Kurze Einführung: „Diese Seite ist der zentrale Einstiegspunkt für Admins. In fünf Schritten zeige ich dir …". Zeigt sich als orphan in der Seitenmitte, damit der erste Eindruck nicht an einem zufälligen Element klebt.
2. **Inhaltsquelle wählen.** Targetiert `#admin-contentsource` — das ist der DOM-Wrapper, den `admin_setting_configselect` via `setting.mustache` mit `id="admin-<name>"` rendert. Erklärt die Bedeutung von `Bundled default` vs. `Custom git repository` in einem Satz pro Option.
3. **Änderungen speichern.** Targetiert `#adminsettings form input[type="submit"]` — absichtlich kein spezifischer Button-Name, damit die Tour auch funktioniert, wenn Core oder Theme den Button umbaut. Der Selector nutzt den `#adminsettings`-Wrapper der Moodle-Admin-Settingpage, um Kollisionen mit eventuellen anderen Forms auf der Seite zu vermeiden. Hinweistext erklärt, dass der Sync-Status sich erst nach Save + neuem Sync aktualisiert.
4. **Sync-Zustand.** Targetiert `#elediacheckin-dashboardpanel .card` — die Bootstrap-Card im Panel, die die aktive Quelle zeigt + zwei Quick-Action-Buttons (Run sync now / Test connection) enthält. Nach dem §10.25-Reorder liegt die Card unterhalb des Save-Buttons, die Tour-Position (placement: top) zeigt also nach oben weg von der Card, damit das Popup nicht den Save-Button verdeckt.
5. **Letzte Sync-Läufe.** Targetiert `#elediacheckin-dashboardpanel table` — die Log-Tabelle. Hinweistext erwähnt, dass der stündliche Cron-Task automatisch im Hintergrund läuft.

**Rollen-Filter.** `["-1","manager"]`. Die Settings-Seite ist erreichbar für Site-Admins (Sentinel `-1`) und Manager mit der Capability `moodle/site:config`. Coursecreator, Editingteacher etc. sehen die Settings-Seite gar nicht erst, brauchen also auch keinen Tour-Filter-Eintrag. Enger als die Teacher-Tour (`-1, coursecreator, manager, teacher, editingteacher`), weil die Zielseite enger ist.

**Pathmatch.** `/admin/settings.php?section=modsettingelediacheckin%`. Das Suffix-`%` fängt Query-String-Anhänge wie `&return=...` oder `&sesskey=...` ab, die Moodle an die Settings-Seite hängt, wenn man aus einer Sub-Seite zurückkommt. Section-Key `modsettingelediacheckin` ist die Standardkonvention für Activity-Module-Settingpages (`modsetting<modname>`).

**i18n.** Alle Textfelder als `settingstour_*,mod_elediacheckin`-Referenzen, 13 neue Strings in `lang/de` und `lang/en` (Name, Description, End-Label, je 5× Title + Content). Selbe Mechanik wie bei der Teacher-Tour nach dem i18n-Fix aus §10.24 — damit ist auch diese Tour über das normale AMOS-Sprachpaket-Workflow übersetzbar.

**Upgrade-Step.** Weil die Einführung einer neuen JSON-Datei im `db/tours/`-Ordner alleine auf bestehenden Installationen nichts auslöst — `install.php` rennt nur beim Fresh-Install, und `tool_usertours` scannt die Ordner ausschließlich auf Core-Upgrades — braucht es Upgrade-Step 2026040534. Der löscht alle Tours mit pathmatch LIKE `/mod/elediacheckin/%` ODER `/admin/settings.php?section=modsettingelediacheckin%` und ruft danach `mod_elediacheckin_install_bundled_tours()`, das beide JSON-Dateien frisch importiert. Wichtig: der Step **muss** auch die Teacher-Tour vorher löschen, weil `import_tour_from_json()` keinen Upsert macht — jeder Call legt einen neuen Record an. Ohne Delete vorher hätten wir nach dem Upgrade eine doppelte Teacher-Tour in der DB.

### 10.27 Companion-Block-Health-Check auf der Settings-Seite (April 2026, Version 2026040535)

Hintergrund: Johannes hat mehrfach gemeldet, dass `block_elediacheckin` im „Block hinzufügen"-Dropdown verschwunden ist — einmal ohne Deploy und ohne nachvollziehbare Ursache (später haben wir bestätigt: `mdl_block.name='elediacheckin'`, `visible=1`, auf Disk und in der DB vorhanden, wahrscheinlich eine Cache-Race-Condition nach einem Upgrade). Das Problem: solange niemand aktiv versucht, den Block hinzuzufügen, fällt so ein Zustand nicht auf. Der Admin glaubt, alles sei okay, bis die erste Lehrkraft sich meldet.

Gegenmaßnahme: eine kleine Health-Strip-Komponente direkt oben im `dashboard_renderer::render()`-Output, vor der Summary-Card. Sie fragt einmal pro Seitenaufruf `mdl_block` ab und schreibt eine Bootstrap-Alert:

- **`alert-success` (grün)** — Block-Record existiert und `visible=1`. Zeigt zusätzlich die installierte Version als `badge bg-success-subtle` aus `core_plugin_manager::get_plugin_info('block_elediacheckin')->versiondb`. Absichtlich eine kurze grüne Zeile statt kompletter Stille, damit der Admin sieht: „Der Check läuft, Alles klar", und nicht befürchtet, die Prüfung sei vielleicht selbst kaputt.
- **`alert-warning` (gelb)** — Block-Record existiert, aber `visible=0`. Typischer Trigger: jemand hat in Site admin → Plugins → Blöcke → Blöcke verwalten das Auge-Icon deaktiviert. Der Block ist dann installiert, aber aus dem Add-Dropdown ausgefiltert. Alert enthält einen Direktlink auf `/admin/blocks.php` mit „Jetzt sichtbar schalten →".
- **`alert-danger` (rot)** — kein Block-Record in `mdl_block`. Bedeutet: das Plugin-Verzeichnis fehlt entweder auf Disk, oder es liegt auf Disk aber die Registrierung ist nie gelaufen. Alert verlinkt auf `/admin/index.php`, weil ein Besuch der Admin-Notifications Moodle dazu zwingt, Plugin-Ordner neu zu scannen und nicht registrierte Plugins anzumelden. Wenn die Plugin-Files komplett fehlen, landet der Admin dort an der „plugin missing from disk"-Warnung — auch das ist die richtige nächste Aktion.

Die Prüfung verwendet bewusst kein Caching: `$DB->get_record('block', ['name' => 'elediacheckin'], 'id, name, visible')` kostet im Zweifel einen indexierten Primary-Lookup, vernachlässigbar. Caching wäre kontraproduktiv, weil genau der Moment, in dem der Block-Zustand kippt (Plugin-Manager toggelt Visibility, Upgrade läuft), die interessanteste Sichtbarkeit ist.

Die Version-Badge fängt `core_plugin_manager`-Exceptions mit `try/catch`, damit ein defekter Plugin-Manager-State (kommt bei inkonsistenten Upgrades vor) nicht die Dashboard-Seite selbst tötet. Im Fehlerfall fehlt einfach die Version, der grüne Strip bleibt.

Lang-Strings: sechs neue `blockhealth_*`-Keys in `lang/de` + `lang/en` (Title, Ok, Hidden + CTA, Missing + CTA). Emojis (`✓`, `⚠`) sind inline im Markup, nicht in den Strings — die Strings bleiben Übersetzer-freundlich.

---

### 10.28 Prechecks + PHPUnit + Behat Scaffold (April 2026, Version 2026040536)

Bis v2026040535 hatte `mod_elediacheckin` keine einzige Testdatei und keinen CI-Workflow. Die Plugin-Submission an die Moodle Plugins Directory erfordert aber sowohl bestandene Prechecks (phpcs, phpdoc, mustache, savepoints, validate) als auch mindestens rudimentäre PHPUnit- und Behat-Abdeckung, damit die QA-Bots nicht sofort rot werfen. Dieser Schritt bringt das Scaffold in einem Rutsch, direkt inspiriert vom Standard-LeitnerFlow-Layout.

**CI-Pipeline:** `.github/workflows/moodle-ci.yml` nutzt `moodlehq/moodle-plugin-ci@^4` als Meta-Tool. Die Matrix läuft auf zwei Stacks: PHP 8.2 + `MOODLE_405_STABLE` (der aktuell von eLeDia-Kunden am meisten installierte LTS) und PHP 8.3 + `MOODLE_500_STABLE` (Submission-Ziel). Beide Jobs gegen Postgres, weil Moodle-Core-Behat-Steps auf Postgres zuverlässiger laufen als auf MySQL. Die Pipeline führt der Reihe nach aus: phplint, phpcpd (continue-on-error, weil Copy-Paste-Detektor bei legitimen Parallelstrukturen nervt), phpmd (ebenfalls continue-on-error), Moodle Code Checker (hart, `--max-warnings 0`), PHPDoc Checker, Validate, Savepoints, Mustache Lint, Grunt, PHPUnit mit `--fail-on-warning` und zuletzt Behat auf Chrome. Die `continue-on-error`-Markierungen sind bewusst gesetzt: wir wollen, dass `phpcs` und `phpdoc` blockieren, aber nicht gleich auf dem ersten Run wegen stilistischer Meckerei von `phpmd` den ganzen Workflow killen.

**PHPUnit-Tests:** Vier Klassen unter `tests/`, jeweils im `mod_elediacheckin`-Namespace. Bewusst dünn gehalten, keine Mocking-Frameworks, keine externen Fixtures.

- `schema_validator_test` erbt von `basic_testcase` (kein Resetting nötig — der Validator ist rein in-memory). 16 Test-Methoden: valid baseline, alle Top-Level-Header-Fehler, jede `ziel`/`status`/`zielgruppe`/`kontext`-Enum-Verletzung, `hat_antwort`-Koppelung mit `antwort`, `kategorie`-Ziel-Konsistenz, Slug-Pattern auf `id`, URL-Validierung auf `link`, plus die statischen Getter. Ein `minimal_bundle()`-Helper in der Testklasse liefert einen garantiert validen Baseline und erlaubt überschreibbare Overrides — ähnlich wie der Builder-Pattern in LeitnerFlows Test-Datensätzen.
- `bundle_signature_verifier_test` erbt ebenfalls von `basic_testcase`. Erzeugt im `setUp()` ein frisches ED25519-Schlüsselpaar via `sodium_crypto_sign_keypair()`, damit kein Testfixture gepflegt werden muss. Testet: gültige Signatur akzeptiert, manipulierter Payload abgewiesen, falscher Public Key abgewiesen, zu kurze Signatur abgewiesen, malformed Public Key abgewiesen, `decode_signature()` akzeptiert Base64, akzeptiert Hex, gibt `null` auf Müll, und `has_production_key()` liefert `false`, solange der Demo-Key die Konstante belegt. Der `markTestSkipped`-Guard für `sodium_crypto_sign_keypair` ist defensiv — Moodle 5 verlangt PHP 8.1+ mit Sodium, aber wenn jemand mal in einer Sandbox ohne Sodium testet, sollen die anderen Testklassen nicht mitleiden.
- `feature_flags_test` hat zwei Methoden: `premium_enabled()` spiegelt die Konstante, und ein Invariant-Test, der die Release-Regel festzurrt: `PREMIUM_ENABLED` muss `false` sein, sonst bricht der Build. Das ist explizit so gewollt — der sed-Flip im Release-Pipeline ist inzwischen zu oft vergessen worden (siehe §10.18, v2026040530). Wenn jemand die Konstante beim Premium-Launch auf `true` dreht, soll dieser Test zusammen mit §10.18 manuell entsperrt werden.
- `activity_pool_test` erbt von `advanced_testcase`, weil die modusabhängigen `build_pool()`-Pfade Moodle-Session-State (`$SESSION`) und `resetAfterTest()` brauchen. Fokus: die deterministischen Helper, die ohne Bundle-DB-Daten verifizierbar sind — `parse_own_questions()` in allen Varianten (leer, CRLF/LF/CR, leerzeilen, virtual-category-marker, externalid-pattern `own-N`), `ownquestionsmode=1` (only-own) bzw. `=2` (none), und der `resolve_navigation`-State-Machine-Kern: initial load resettet History, Next-Click pusht + aktiviert `hasprev`, Back-Click popt + setzt `hasprev` zurück. Die DB-Pfade über `question_provider` decken wir nicht mit PHPUnit ab, weil das einen kompletten Bundle-Import-Fixture erfordern würde; dafür gibt es die Behat-Features.

**Behat-Features:** Drei `.feature`-Files unter `tests/behat/`, alle mit `@javascript`-Scenarios (weil View-Rendering und User-Tours JS brauchen).

- `golden_path.feature` deckt den wichtigsten User-Flow ab: Teacher legt Aktivität an (mit Default-Modul-Picker + `I fill the form with`), Student öffnet die Aktivität und sieht eine eigene Frage, Student klickt „Nächste Frage" und sieht weiterhin irgendeine Frage. Das ist die Golden-Path-Absicherung: wenn dieser Feature-Run grün ist, kann das Plugin grundsätzlich eingebaut und genutzt werden.
- `settings_dashboard.feature` prüft, dass die Admin-Settings-Seite rendert, den Dashboard-Panel-Titel zeigt, den Block-Health-Alert (wenn der Begleit-Block installiert ist) anzeigt und dass `content source`-Änderungen persistiert werden. Das fängt Regressions bei allen Reorder/Spacing-Bugs ab, die wir in §10.25–10.27 gefixt haben.
- `block_and_tour.feature` prüft zwei separate Integrations: Teacher fügt den `block_elediacheckin` zu einer Kurseite hinzu und sieht im Block eine eigene Frage aus der Check-in-Aktivität; außerdem, dass beide User-Tours (§10.26 + die bestehende Lehrkräfte-Tour) auf ihren jeweiligen Seiten automatisch starten und die erwarteten Schritttexte zeigen.

**Kein Code-Change im Runtime:** Dieses Scaffold ist bewusst additiv. Keine Zeile in `classes/`, `lib.php`, `view.php` wird angefasst. Das hält den Blast-Radius klein und erlaubt es, beim ersten CI-Run das wirkliche Signal zu sehen (welche phpcs/phpdoc-Regeln brechen wir aktuell?) ohne gleichzeitig Runtime-Bugs mit einzufangen.

**Nächster Schritt:** Sobald der erste CI-Run da ist, wird `docs/testing-inbox.md` um einen Punkt ergänzt, der die konkreten phpcs/phpdoc-Verletzungen listet, die wir jetzt in v2026040537+ aufräumen müssen.

### §10.29 Bundled Fixes auf Basis des ersten PHPUnit-Runs (v2026040537, 2026-04-05)

Der erste PHPUnit-Init-Durchlauf auf Johannes' Docker-Stack hat nach dem Scaffold aus §10.28 drei konkrete Baustellen offenbart, die zusammen mit drei länger offenen UX-Punkten in einem Bundle abgearbeitet wurden.

**1 — PHPUnit-11-Attribute statt @covers-Docblocks.** Die vier neuen Test-Klassen aus §10.28 wurden aus dem LeitnerFlow-Template 1:1 kopiert, inklusive des alten PHPdoc-Metadaten-Stils (`@covers \…\manager`). Moodle 5.x liefert aber PHPUnit 11 aus, und PHPUnit 11 deprecated sämtliche Docblock-Metadaten (`@covers`, `@dataProvider`, `@group`, `@runInSeparateProcess` — in PHPUnit 12 komplett entfernt). Ergebnis: 38 Tests grün, aber vier `PHPUnit Deprecations`, eine pro Klasse. Das bricht `moodle-plugin-ci phpunit --fail-on-warning` vollständig. Migration: `use PHPUnit\Framework\Attributes\CoversClass;` + `#[CoversClass(xxx::class)]` über der Klasse, Docblock entfernt. Für neue Tests ab dieser Version ist die Attribut-Form verpflichtend, und sie ist als Skill-Reference (`reference_moodle_phpunit11_attributes.md` in Claudes Auto-Memory) zusätzlich dokumentiert, damit in Folge-Sessions nicht wieder die alten LeitnerFlow-Docblocks kopiert werden.

**2 — XMLDB `DEFAULT=""` auf CHAR NOT NULL.** Vier Spalten in `elediacheckin_question` (`categories`, `zielgruppe`, `kontext`, `license`) waren mit `NOTNULL="true" DEFAULT=""` angelegt. Moodle 5.x XMLDB frisst das zur Laufzeit, logged aber für jede Spalte eine `debugging()`-Warnung („Invalid default value for CHAR NOT NULL …"). Diese Warnungen brechen ebenfalls `moodle-plugin-ci --fail-on-warning`. Fix: alle vier Spalten auf `NOTNULL="false"` ohne expliziten DEFAULT umgestellt. Semantisch ändert das nichts — die Felder waren ohnehin „leere CSV = untagged", und das App-Code-Verhalten (`sync_service` schreibt immer `implode(',', …)`, was für leere Arrays `""` ergibt) bleibt identisch. Aus Sicht der DB ist der einzige sichtbare Unterschied, dass in der sehr unwahrscheinlichen Situation, in der `sync_service` eine Row schreibt ohne die Spalte zu setzen, jetzt NULL statt `""` landet. Das ist robuster, weil der Reader (`activity_pool`, `question_provider`) ohnehin beide Fälle als „keine Filter-Einschränkung" interpretiert.

**3 — Install-time Crash bei `tool_usertours`-Import.** `db/install.php::mod_elediacheckin_install_bundled_tours()` rief auf frischer Site direkt `tool_usertours\manager::import_tour_from_json()` auf. Auf einer Fresh-Install-Situation — und damit auch beim `phpunit init` — sind die `tool_usertours_*`-Tabellen aber noch nicht angelegt, weil `mod_*`-Plugins VOR `tool_*`-Plugins installiert werden. Ergebnis: eine Exception pro Tour-JSON, die zwar gecatcht und nur geloggt wurde, aber die Tour damit beim ersten Install nie ankam. Fix: vor dem Aufruf `$DB->get_manager()->table_exists('tool_usertours_tours')` prüfen und im Zweifel silent return. `tool_usertours` importet Plugin-bundled Tours beim eigenen Install-Schritt ohnehin selbst, also gehen durch den Skip keine Tours verloren. Exakt derselbe Fehler steckt im LeitnerFlow-Plugin; der ist aus dieser Session ausgeklammert und wandert in dessen eigene Testing-Inbox. Für mod_elediacheckin ist der Fix ab jetzt der Referenz-Pattern, dokumentiert in `reference_moodle_install_time_pitfalls.md` (Auto-Memory).

**4 — Save-Button-Fix ohne DOM-Reorder.** Johannes' Screenshot vom 2026-04-04 zeigte den Save-Button der Einstellungsseite alleine am Seitenanfang mit großer Lücke, das Dashboard-Panel einsam am Seitenende — die §10.27-Reorder-Regression, die wir seit v2026040532 über mehrere Iterationen zu reparieren versucht haben, war nach dem §10.28-Push immer noch kaputt. Die grundsätzliche Idee „Panel soll VISUELL nach dem Save-Button stehen" kollidiert fundamental mit Moodle's Admin-Settings-Layout (Fieldset wrapt alles, Save-Button kommt immer als letztes Element nach dem letzten Setting). Jede Reorder-Variante war entweder zu aggressiv (Save-Button floss allein nach oben) oder zu konservativ (kein Reorder, Panel klebte am Ende).

**Neue Strategie, v2026040537:** Reorder komplett aufgegeben. Stattdessen rendert `dashboard_renderer` oben im Panel eine eigenständige `alert alert-light`-Zeile, die einen `<button type="submit" name="elediacheckin_earlysave">Save changes</button>` enthält. Weil die Zeile innerhalb des `admin_setting_description` liegt, die Teil des Admin-Settings-`<form>` ist, ist dieser Button semantisch ein ganz normaler Form-Submit — klickt man ihn, speichert Moodle die Settings wie gewohnt. Der originale Moodle-Save-Button bleibt an seiner natürlichen Position am Seitenende stehen (für Nutzer:innen, die lieber scrollen). Kein JavaScript, kein MutationObserver, kein Walk-up, kein Race-Condition. Die gesamte `reorder_script()`-Methode und die zwei `reorder_script`-Aufrufe sind entfernt.

Lehre: Wenn mehrere Iterationen eines DOM-Reorders scheitern, ist die Frage nicht „wie repariere ich den Reorder", sondern „brauche ich den Reorder überhaupt". Einen zweiten Submit-Button hinzufügen löst das UX-Problem, ohne am Moodle-Layout zu rütteln. Dieser Pattern — „inline clone of submit inside a panel" — ist für andere eLeDia-Plugins ebenfalls vormerkbar.

**5 — Dritte User-Tour für das Aktivitäts-Formular.** Wir haben seit §10.24 zwei Tours: eine für die View-Seite (Lehrkräfte beim Einsatz der Aktivität) und eine für die Admin-Settings-Seite (§10.26). Was bisher fehlte, war die Tour für den Moment, in dem Lehrkräfte die Aktivität tatsächlich KONFIGURIEREN — also `/course/modedit.php?add=elediacheckin%`. Dort landen sie direkt nach dem Klick auf „Aktivität hinzufügen" im mod_form, und gerade die Check-in-spezifischen Felder (Ziele, Kategorien, Zielgruppe, eigene Fragen) sind nicht selbsterklärend.

Die neue Tour `activity_settings_tour.json` hat 7 Schritte: Welcome (orphan), `#id_checkinsettings` (Header des Fieldsets), `#fitem_id_ziele`, `#fitem_id_categories`, `#fitem_id_zielgruppe`, `#fitem_id_ownquestionsmode`, und zum Abschluss `#id_submitbutton2` (der Moodle-Standard-Speichern-Button von `moodleform`). `fitem_id_*`-Selektoren sind Moodle-Form-Convention — jedes `addElement`-Call rendert seine Row als `<div id="fitem_id_<name>">`. Das ist stabiler als CSS-Klassen-basierte Selektoren, weil die Klassen zwischen Moodle-Versionen und Themes variieren, die `fitem_id_*`-IDs aber eine Konstante des quickforms-Output sind. Pathmatch ist `/course/modedit.php%` (ohne explizite Plugin-Bedingung, weil ein CSS-Selector-Filter für `#id_checkinsettings` den Scope automatisch auf eLeDia-Check-in-Activities einschränkt — alle anderen Aktivitätstypen haben diese ID nicht). Rollen-Filter: `-1, coursecreator, manager, editingteacher` (kein `teacher`, weil non-editing Teachers `/course/modedit.php` nicht aufrufen).

Sprachstrings: je 18 neue Keys in `lang/de` und `lang/en` (`activitytour_*`), konsequent im gleichen Stil wie Tour 1 und 2.

**6 — Lang-String-Audit.** Johannes hatte gefragt, ob wir wo möglich Core-Strings verwenden statt eigene zu ziehen. Bestandsaufnahme (204 Plugin-Strings): Die meisten Strings sind domain-spezifisch (Kategorien, Ziele, Zielgruppen-Labels, Sync-Log-Terminologie) und haben kein passendes Core-Pendant. Ein Fall wurde aber gefunden: `$string['close'] = 'Close'` war in `view.php` und `present.php` über `get_string('close', 'elediacheckin')` verwendet — Moodle hat denselben Text aber als `closebuttontitle` im `moodle`-Component. Refactoring: beide Template-Kontexte nutzen jetzt `get_string('closebuttontitle')`, die Plugin-eigene `close`-Zeile ist aus `lang/en` und `lang/de` entfernt (und durch einen erklärenden Kommentar ersetzt, damit in der nächsten Session niemand versehentlich wieder einen Eigenstring anlegt). Der neue Dashboard-Save-Button nutzt `get_string('savechanges')` (ebenfalls Core). Alle anderen Form-Header wie `general` und Feld-Labels wie `name` waren ohnehin schon sauber über Core angeschlossen. Gesamtgewinn: eine redundante Übersetzung gespart, plus eine dokumentierte Konvention „wenn Core-String existiert, IMMER Core bevorzugen" als Baseline für alle zukünftigen eLeDia-Plugins.

**Zusammenfassung v2026040537:** 38 Tests weiterhin grün, die vier PHPUnit-Deprecations sind weg, die install.xml-Warnings sind weg, der Tour-Import-Crash beim Install ist weg, Save-Button sitzt an einer Stelle, die visuell funktioniert, eine dritte User-Tour schließt das letzte Onboarding-Loch, und die Sprachdateien sind um einen Core-String-Redundanz-Eintrag ärmer.

### §10.30 UX-Feedback-Runde nach v2026040537-Deploy (v2026040538, 2026-04-05)

Nach dem ersten Live-Test von v2026040537 hat Johannes eine zusammenhängende UX-Feedback-Liste gemeldet, die zum Bundle v2026040538 geworden ist. Acht Punkte, die jeweils einzeln trivial, zusammen aber ein Signal sind: Die erste Runde hat das „technische Scaffold" fertig — jetzt geht es darum, dass sich die Bedienoberfläche für Lehrkräfte und Lernende wirklich rund anfühlt.

**1 — Block auf Aktivitätsseiten sichtbar halten.** `block_elediacheckin::applicable_formats()` hatte `mod => false`, mit der expliziten Begründung „Dashboard/admin/other mod pages stay off to avoid noise". Das stimmt für fremde Aktivitätstypen (Quiz, Forum etc.), greift aber in der Praxis zu weit: Lehrkräfte springen beim Testen einer Check-in-Aktivität zwischen Kursseite und Aktivität hin und her, und wenn der Block auf der Aktivitätsseite verschwindet, wirkt das wie ein Bug („der Block kann ja nicht einmal auf seiner eigenen Aktivität bleiben"). Fix: `mod => true`. Damit bleibt der Block in der rechten Spalte verfügbar und kann auch auf Mod-Pages neu platziert werden. Dashboard und Admin bleiben ausgenommen.

**2 — Block-Kartentext-Regression.** Johannes meldete, der Preview-Text in der Block-Karte sei nach v2026040537 „nicht mehr angezeigt". Ursachenanalyse: `format_text()` ohne expliziten Context fällt auf `$PAGE->context` zurück, was im Block-Render-Pfad je nach Page-Typ variiert und die Filter-Cache-Keys beeinflusst. Defensive-Fix: Block speichert das Modul-Context-Objekt auf der Instanz, reicht es in `format_text(..., ['context' => $ctx, 'para' => false, 'newlines' => false])` durch, und hat einen Last-Resort-Fallback auf `s($rawfrage)`, falls die Filter-Chain trotzdem einen Empty String zurückgibt. Damit ist garantiert, dass die Block-Karte immer Text zeigt, solange das `frage`-Feld nicht leer ist.

**3 — Block-Launch-Button: „Öffnen" statt „Check-in öffnen".** Das Label war redundant, weil der Blocktitel bereits „Check-in" heißt. Fix: `$string['openactivity'] = 'Öffnen'` bzw. `'Open'`.

**4 — Navigations-Labels: „Weiter" / „Zurück" statt „Nächste Frage" / „Zur vorherigen Frage".** Problem: Das Wort „Frage" passt nicht auf alle Ziele. Bei `ziel = zitat` ist die Karte eine Weisheit, bei `funfact` ein Fakt. Lösung: die Labels auf neutrale Navigationsbegriffe reduziert. Das Wort „Frage" ist damit nur noch im Einstellungstext präsent, wo es seinen etablierten Moodle-Kontext behält.

**5 — History-Stack-Redesign: vollständiger Session-Verlauf statt One-Step-Back.** v2026040537's `resolve_navigation()` hielt einen 2-Element-Stack (`[prev, current]`), weshalb der „Zurück"-Button nach einem einzigen Rückschritt verschwand. Johannes erwartete aber normales Back/Forward-Verhalten: zurück darf beliebig oft gedrückt werden, Back-Button erst auf der allerersten Karte wieder weg. Refactoring: Der Session-State ist jetzt ein Cursor-basierter History-Stack (`elediacheckin_nav[$cmid] = ['history' => [...], 'pos' => int, 'seen' => [extid => true], 'exhausted' => bool]`). „Weiter" pusht auf History (falls am Tail) oder wandert vorwärts im Stack (falls der Nutzer vorher zurück gegangen war); „Zurück" dekrementiert `pos`, bis `pos === 0`; `hasprev = pos > 0`. `seen` ist ein separater Set, der auch erhalten bleibt, wenn die History am `HISTORY_MAX = 500`-Cap abgeschnitten wird — weil die Pool-Exhausted-Logik auf `seen` operiert, nicht auf `history`. Tests: fünf PHPUnit-Fälle decken Initial-Load, Next-pushes-history, Back-keeps-prev (Regressionsguard für das v2026040537-One-Step-Verhalten), Exhausted-Restart und Exhausted-Empty ab.

**6 — Setting „Wenn alle Fragen durch sind".** Mit dem neuen History-Stack tracken wir `seen`, und der Exhausted-State ist klar definiert: „Jede Karte im Pool wurde in dieser Session einmal gezeigt". Zwei Behavior-Varianten sind pro Aktivität konfigurierbar: `restart` (Default, still Seen-Set leeren und weiterziehen) und `empty` (stoppen, Hinweiskarte „Alle Fragen durch, komm später wieder" anzeigen). Die neue XMLDB-Spalte `exhaustedbehavior CHAR(16)` hält den Wert, `mod_form` bietet ein zweistufiges Dropdown, die beiden Verhalten sind in je einer PHPUnit-Test-Klasse abgedeckt. Default auf `restart`, weil Check-in-Pools typischerweise klein sind (20–50 Karten).

**7 — Save-Button-Größe.** Der in v2026040537 eingeführte Dashboard-Save-Button nutzte `btn btn-primary btn-sm`. Der visuelle Eindruck „zweitklassige Alternative" war ungewollt. Fix: `btn-sm` entfernt. Der Button hat jetzt den gleichen visuellen Rang wie der untere Moodle-Save-Button.

**8 — Aktivitäts-Settings-Tour Upgrade-Step.** Showstopper des Releases: Die in §10.29.5 neu dazugekommene `activity_settings_tour.json` tauchte bei Johannes nie auf. Ursache: `install.php::mod_elediacheckin_install_bundled_tours()` wird nur bei Fresh-Installs aufgerufen; für Upgrades wird dieser Aufruf in `db/upgrade.php` pro Version manuell getriggert — und zwar bislang nur bis `2026040534`. v2026040537 hatte schlicht keinen Upgrade-Step. Fix: Upgrade-Block für 2026040538 hinzugefügt, der (a) die neue `exhaustedbehavior`-Spalte anlegt und (b) alle vom Plugin bundled Tours löscht und neu importiert. Der Lösch-Filter prüft zusätzlich auf den Tour-Namen (`Check-In`), um auf der generischen `/course/modedit.php%`-Pathmatch keine fremden Tours zu erwischen. Lehre: Jede neue Tour-JSON braucht einen eigenen Upgrade-Step — Fresh-Install-Pfad und Upgrade-Pfad sind zwei unabhängige Code-Pfade.

**Zusammenfassung v2026040538:** Block bleibt auf Aktivitätsseiten, Block-Karte zeigt wieder Text, Button-Labels sind kürzer und ziel-agnostisch, der Back-Button verhält sich endlich wie erwartet, Lehrkräfte können pro Aktivität entscheiden, was bei leerem Pool passiert, Dashboard-Save-Button hat die richtige Größe, und die dritte User-Tour taucht nach dem Upgrade-Step auch bei bestehenden Installationen auf.

---

## Zusammenfassung in einem Satz

Drei austauschbare Content-Source-Implementierungen hinter einem gemeinsamen Plugin-Interface, wobei der bezahlpflichtige eLeDia-Modus über einen schlanken License-Server plus signierte CDN-Bundles realisiert wird, die wiederum aus einem git-basierten Redaktions-Workflow via GitHub Action automatisch gebaut und verteilt werden — mit der Option für Kunden, zusätzlich eigene Git-Repos anzuschließen oder einfach den mitgelieferten Starter-Content zu nutzen.
