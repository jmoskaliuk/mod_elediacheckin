# Tasks - mod_elediacheckin

Operational task list for the current DevFlow state.

## Current Status

- Base repository: `origin/main` at `fd31aa2`
- Local working copy contains modifications and additional artefacts.
- DevFlow files were added and refreshed on 2026-05-08.
- Companion block is now a separate Git working copy.

Current changed tracked files against `origin/main`:

- `.gitattributes`
- `CHANGES.md`
- `README.md`
- `amd/build/category_filter.min.js`
- `amd/build/category_filter.min.js.map`
- `classes/content/content_bundle.php`
- `classes/content/schema_validator.php`
- `classes/local/service/activity_pool.php`
- `classes/local/service/question_provider.php`
- `classes/local/service/sync_service.php`
- `docs/testing-inbox.md`
- `templates/present.mustache`
- `templates/view.mustache`
- `tests/activity_pool_test.php`
- `tests/behat/behat_mod_elediacheckin.php`
- `tests/bundle_signature_verifier_test.php`
- `tests/feature_flags_test.php`
- `tests/generator/lib.php`
- `tests/schema_validator_test.php`
- `thirdpartylibs.xml` deleted locally
- `version.php`

Current new documentation files:

- `docs/00-master.md`
- `docs/01-features.md`
- `docs/02-user-doc.md`
- `docs/03-dev-doc.md`
- `docs/04-tasks.md`
- `docs/05-quality.md`

## Active Tasks

### MOD-00 DevFlow einrichten

- **Status:** done
- **Date:** 2026-05-08
- **Outcome:** Added and refreshed `docs/00-master.md` through
  `docs/05-quality.md`; README now links to the DevFlow start document.

### MOD-01 Lokalen Stand gegen GitHub pruefen

- **Status:** done
- **Context:** The working copy differs from `origin/main` and contains extra
  local material.
- **Outcome:** Current tracked diff and untracked repo-boundary artefacts are
  documented in this DevFlow.
- **Next:** Decide which local changes are intended product changes and which
  are generated/prototype artefacts.

### MOD-06 DevFlow aktuell halten

- **Status:** done
- **Date:** 2026-05-08
- **Context:** User requested a DevFlow refresh after creating separate Git
  working copies.
- **Outcome:** Updated master, task and quality files with the current Git
  snapshot and remaining decisions.

### MOD-07 Backup/Restore Feldverlust beheben

- **Status:** done
- **Date:** 2026-05-08
- **Severity:** critical
- **Context:** Backup used removed fields (`randomstart`, `shownav`,
  `showother`, `showfilter`) and omitted current activity settings.
- **Outcome:** Backup now includes `zielgruppe`, `kontext`, `contentlang`,
  `ownquestions`, `ownquestionsmode`, `showprevbutton` and
  `exhaustedbehavior`. Restore supplies defaults for missing fields and strips
  obsolete backup XML fields defensively.
- **Follow-up:** Verify with a Moodle course backup/restore smoke test.

### MOD-08 Fragen-Cache beim Lesen nutzen

- **Status:** done
- **Date:** 2026-05-08
- **Severity:** important
- **Context:** Question and category caches were purged after sync but never
  populated during reads.
- **Outcome:** `question_provider` now caches filtered question results and
  id lookups; `category_provider` caches language-specific category lists.

### MOD-09 Bundle-HTML explizit bereinigen

- **Status:** done
- **Date:** 2026-05-08
- **Severity:** important
- **Context:** Bundle questions were rendered as `FORMAT_HTML` based on a
  trust assumption.
- **Outcome:** Bundle question and answer HTML is passed through Moodle
  `clean_text(..., FORMAT_HTML)` and rendered with `trusted=false` and
  `noclean=false`; own questions remain plain text.

### MOD-10 Privacy Provider vervollstaendigen

- **Status:** done
- **Date:** 2026-05-08
- **Severity:** important
- **Context:** Privacy provider declared a null provider even though activity
  configuration includes teacher-entered free text.
- **Outcome:** Provider now declares stored activity, synced question and sync
  log metadata explicitly.

### MOD-11 Feedback bei leergefiltertem Bundle-Pool

- **Status:** done
- **Date:** 2026-05-08
- **Context:** In mixed mode, target group/context filters can remove all
  bundle questions while own questions still appear, which looked like a bug.
- **Outcome:** Views now show an explanatory notice when own questions are
  shown but the active target group/context filter removes all bundle hits.

### MOD-02 Repo-Grenzen bereinigen

- **Status:** open
- **Context:** The root contains separate or supporting material such as
  `block_elediacheckin/`, `content_elediacheckin/`, `license_server/`,
  `mod_elediacheckin/` and ZIP files.
- **Acceptance:** Public module commits contain only intentional module files
  and required documentation.

### MOD-03 Versionsgeschichte klaeren

- **Status:** open
- **Context:** Remote tag is `v0.9.0`, while local `version.php` reports
  release `0.2.0`.
- **Acceptance:** Release metadata, changelog and Git tags tell one coherent
  story.

### MOD-04 Companion-Kompatibilitaet halten

- **Status:** ongoing
- **Context:** `block_elediacheckin` calls module services and launches module
  URLs.
- **Acceptance:** Any module changes that touch card selection, URLs,
  capabilities or template context are tested with the block.

### MOD-05 Tests aktualisieren

- **Status:** open
- **Context:** PHPUnit and Behat files exist, but local execution has not been
  run in this DevFlow pass.
- **Acceptance:** Relevant PHPUnit/Behat checks are run in a Moodle test
  environment and results are recorded in `05-quality.md`.

## Done History

- 2026-05-08: Split local setup into two Git working copies:
  `mod_elediacheckin` and `block_elediacheckin`.
- 2026-05-08: Added DevFlow documentation set for the module.
- 2026-05-08: Refreshed DevFlow with the current local Git snapshot.
- 2026-05-08: Incorporated plugin review feedback for backup/restore, caching,
  HTML cleaning, privacy metadata and empty-filter feedback.
