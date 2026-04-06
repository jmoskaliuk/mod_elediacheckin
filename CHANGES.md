# Changelog — mod_elediacheckin

All notable changes to this plugin are documented here.
The format follows [Keep a Changelog](https://keepachangelog.com/).

## [Unreleased] — v2026040541

### Changed
- Extracted tour import logic into autoloaded `tour_installer` class
  (`classes/local/tour_installer.php`) — eliminates all `require_once`
  calls from `db/install.php` and `db/upgrade.php`.
- Rebuilt AMD bundle (`amd/build/view.min.js`) with Babel + Terser.
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
