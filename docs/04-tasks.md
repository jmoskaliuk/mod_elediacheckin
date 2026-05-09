# Tasks - mod_elediacheckin

Operational task list for the current DevFlow state.

## Current Status

- Base repository: `origin/main` at `d3fe170`
- Working tree clean of intentional product changes; only build/deploy
  helpers and supporting prototypes remain untracked.
- Plugin metadata: `version = 2026040603`, `release = '0.9.0'`,
  `MATURITY_BETA`.
- Latest DevFlow refresh: 2026-05-09.

## Active Tasks

### MOD-12 Plugin-Directory submission vorbereiten

- **Status:** open
- **Context:** Plugin code review and CI cycle wrapped on 2026-05-09; the
  remaining work before submission is metadata + screenshots, not code.
- **Acceptance criteria** (from `moodle-framework/04-submission.md`):
  - Decide STABLE vs. BETA maturity and bump release tag accordingly.
  - Refresh `CHANGES.md` so the top entry matches the version actually
    on `origin/main` (currently has stale `v2026040607` reference).
  - Build the release ZIP via `git archive --prefix=elediacheckin/`.
  - Prepare short / long description, tags, screenshots in
    `.submission-draft.md` (export-ignored).
- **Next:** Confirm maturity decision, then sequence the release work.

### MOD-13 Deploy-Pfad gegen install.xml-Drift sichern

- **Status:** open
- **Context:** The 2026-05-08 install warning chain was caused by a stale
  nested `mod_elediacheckin/db/install.xml` with `NOTNULL="true"
  DEFAULT=""` columns. The folder was deleted and a regression test added.
- **Acceptance:** Document — or harden via deploy script — that the deploy
  source is always the repo root, not any sibling/nested folder. Optional:
  extend `bin/precheck.sh` to grep for the bad pattern in any install.xml
  reachable from the deploy source.

### MOD-04 Companion-Kompatibilität halten

- **Status:** ongoing
- **Context:** `block_elediacheckin` calls module services and launches
  module URLs.
- **Acceptance:** Any module changes that touch card selection, URLs,
  capabilities or template context are tested with the block.

### MOD-05 Tests auf Live-Moodle laufen lassen

- **Status:** open
- **Context:** PHPUnit and Behat files exist and the latest CI run is
  expected green; local execution against the OrbStack Moodle has not
  been verified after the 2026040603 changes.
- **Acceptance:** PHPUnit and Behat run cleanly on the local OrbStack
  container; results recorded in `05-quality.md`.

### MOD-14 Backup/Restore Smoke Test

- **Status:** open
- **Context:** Backup definition was repaired in v2026040601; restore
  defaults added in the same step. No actual round-trip executed yet.
- **Acceptance:** Manual Moodle course backup / restore round-trip
  preserves `zielgruppe`, `kontext`, `ownquestions`, `ownquestionsmode`,
  `showprevbutton`, `exhaustedbehavior`, `contentlang`. Result in
  `05-quality.md` Q08.

## Done History

### 2026-05-09 — XMLDB CHAR NOT NULL DEFAULT '' warning permanently silenced

- **Severity:** critical (blocked DEBUG_DEVELOPER installs)
- **Trigger:** Stack trace showed the warning fires from
  `xmldb_field::setDefault` during `arr2xmldb_field` parsing of the
  deployed install.xml, not from upgrade.php. Root cause was a stale
  nested `mod_elediacheckin/db/install.xml` snapshot in the working
  tree carrying `NOTNULL="true" DEFAULT=""` for `categories`,
  `zielgruppe`, `kontext`, `license`.
- **Outcome:**
  - Stale nested folder deleted.
  - Step `2026040603` uses raw `ALTER TABLE … ALTER COLUMN … DROP
    DEFAULT` via `$DB->execute()` to bypass the self-defeating xmldb
    introspection of `change_field_default()`.
  - Step `2026040602` neutered to just `unset_config('reporef')`.
  - Historical step `<2026040501` no longer creates `bundleid`,
    `bundleversion`, `externalid`, `ziel`, `lang` as CHAR NOT NULL
    DEFAULT ''.
  - Regression test `tests/install_xml_test.php` parses install.xml
    and fails the run if the offending pattern reappears.
- **Commits:** `38c0069`, `d4a28dd`, `d3fe170`.

### 2026-05-09 — CI run #25554594690 gefixt

- **Severity:** important
- **Context:** PHPCS flagged 3 errors / 2 warnings in `db/upgrade.php`
  (comment capitalization + comma alignment) and PHPDoc flagged 9
  errors for generic `array<...>` types in @param tags that
  `moodle-local_moodlecheck` cannot parse.
- **Outcome:** Generic types stripped to plain `array` across 6 class
  files; comment capitalization and comma-spacing fixed; remaining
  CHAR NOT NULL DEFAULT '' patterns in historical step also cleaned up.
- **Commit:** `05d3466`.

### 2026-05-08 — Code-Review-Feedback umgesetzt (`38c0069`)

- Wired `avoidrepeat` flag into `activity_pool::resolve_navigation`
  (was DB+form-only no-op).
- Removed dead `reporef` admin setting (never read by
  `git_content_source`); upgrade step purges leftover config row.
- Hardened `mod_form` `<script>` injection with
  `JSON_HEX_TAG|HEX_AMP|HEX_APOS|HEX_QUOT`.
- `view.php` / `present.php`: redundant `clean_text()` removed,
  defence-in-depth comment explains `format_text(FORMAT_HTML)` trust
  model.
- `privacy/provider` docblock now notes that
  `$SESSION->elediacheckin_nav` is transient and intentionally not
  declared.
- `analyze_pool()` consolidates `build_pool` + `describe_pool` into a
  single pass; skips the without-audience-filter query when no
  audience filter is set.

### 2026-05-08 — Initial v2026040601 review-feedback batch (`3c6ecb4`)

- Filter-warning surfaced when audience filters mask the entire bundle
  pool.
- `question_provider` / `category_provider` now populate caches
  defined in `db/caches.php` instead of only purging them after sync.
- Privacy provider rewritten as metadata provider for `name`,
  `intro`, `ownquestions` only.
- Backup includes `zielgruppe`, `kontext`, `ownquestions`,
  `ownquestionsmode`, `showprevbutton`, `exhaustedbehavior`; restore
  supplies defaults and strips legacy XML fields.
- Generic-type docblocks added to bundle / schema_validator /
  question_provider for PHPStan readability (later reverted to plain
  `array` for moodle-local_moodlecheck compatibility — see CI fix).

### 2026-05-08 — Working-tree revert cleanup

- Restored unwanted reverts of recent April commits in the mod and
  block working trees: version downgrade (back to 2026040601 BETA
  0.9.0), `block_elediacheckin/*` functionality (specialization,
  hide_header, mod page-type, format_text context, quote
  attribution), PHPCS test markers, Behat tour-installer, AMD build,
  `thirdpartylibs.xml`, `CHANGES.md` `v2026040607` entry.

### 2026-05-08 — DevFlow set added (`ec6e479`)

- Added `docs/00-master.md` through `docs/05-quality.md`,
  `docs/content-{example,schema}.json`, mockup HTMLs,
  `docs/testing-PHPCS.md`, `docs/testing-results.md`.
