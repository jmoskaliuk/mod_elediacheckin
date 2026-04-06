# Changelog — mod_elediacheckin

All notable changes to this plugin are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/).

## v2026040601 — 2026-04-06

### Fixed
- Behat feature files: pluginname typo `eLeDia Check-in` → `eLeDia Check-In`
  in all admin navigation steps.
- Behat golden_path: `I add an "..."` → `I add a "..."` (Moodle 5.x step name).
- Block name in feature file corrected to `"Check-in"` (matching block pluginname).
- Removed undefined custom Behat step `the "elediacheckin" block exists`.

### Added
- `tests/generator/lib.php` — data generator enabling `the following "activity" exists:`
  fixture syntax in Behat and PHPUnit tests.

### Changed
- `version.php`: bumped to 2026040601, maturity ALPHA → BETA, release 0.2.0 → 0.9.0.

## v2026040545 — 2026-04-06

### Added
- BroadcastChannel-based bidirectional synchronisation between activity view
  and popup window: navigating in one window updates the other in real time.
- Multi-target (Ziel) picker in popup mode.

## v2026040544 — 2026-04-06

### Added
- Popup remote control (postMessage groundwork; superseded by
  BroadcastChannel in v2026040545).

## v2026040543 — 2026-04-06

### Fixed
- Author attribution now rendered for `ziel === 'zitat'` questions.
- Popup close button triggers navigation refresh in parent view.
- `check_database_schema` — schema clean, no differences.

## v2026040542 — 2026-04-06

### Fixed
- Block preview renders question text correctly.
- Popup shows the same question as the activity view (no independent draw).
- F5 / page reload no longer triggers a new question draw (PRG pattern).
- Block title no longer renders as empty heading when not set.

## v2026040541 — 2026-04-06

### Changed
- Extracted tour import logic into autoloaded `tour_installer` class
  (`classes/local/tour_installer.php`) — eliminates all `require_once`
  calls from `db/install.php` and `db/upgrade.php`.
- Rebuilt AMD bundle (`amd/build/view.min.js`) with Moodle Grunt toolchain.
- Rewrote `README.md` for Moodle Plugins Directory submission.

### Added
- `.gitattributes` — keeps `lang/de/`, `docs/`, `tests/`, `.github/`
  out of the release ZIP produced by `git archive`.
- This `CHANGES.md` file.

## v2026040540 — 2026-04-05

### Fixed
- Fullscreen overlay now persists across back / forward navigation
  (`fs=1` URL parameter).

### Changed
- All German (`lang/de`) strings converted to formal address
  (Sie / Ihnen / Ihre) per eLeDia B2B convention.

## v2026040539 — 2026-04-05

### Fixed
- Save-changes bar moved above the "Sync status" dashboard heading
  (was hidden below the fold).

## v2026040538 — 2026-04-05

### Fixed
- Eight UX issues from manual review: card-stage spacing, category
  badge alignment, fullscreen button visibility, popup overlay z-index,
  empty-state message wording, history navigation edge cases, tour
  step positioning, admin-dashboard scroll anchor.

## v2026040537 — 2026-04-05

### Changed
- Migrated all PHPUnit tests to PHPUnit 11 attribute syntax
  (`#[CoversClass]`, `#[DataProvider]`).
- Fixed XMLDB `install.xml` column defaults to silence Moodle
  debugging warnings (`DEFAULT ""` on `CHAR NOT NULL`).
- Added `tool_usertours` table-existence guard to `db/install.php`.

## v2026040536 — 2026-04-04

### Added
- PHPUnit unit-test scaffold (`tests/`).
- Behat acceptance-test features (`tests/behat/`).
- GitHub Actions CI workflow (`.github/workflows/ci.yml`).

## v2026040535 — 2026-04-04

### Added
- Companion-block health-check strip in admin dashboard panel.

## v2026040534 — 2026-04-04

### Added
- Bundled user tour for admin settings page.

## v2026040528–v2026040532 — 2026-04-03 / 2026-04-04

### Added
- Git-based content-source synchronisation with error diagnostics.
- Bundled teacher onboarding user tour.
- Full-width card stage layout.
- Premium-feature toggle (disabled by default).

### Fixed
- Save-button reorder wrapper regressions.
- Tour visibility and i18n corrections.
- Sync-status heading placement.
