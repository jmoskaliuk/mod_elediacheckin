# Quality - mod_elediacheckin

## Quality Goals

- Teachers can configure and use the activity without understanding the
  content backend.
- Admin content sync is predictable and inspectable.
- Runtime card selection respects filters, language and own-question settings.
- Presentation and block-launched flows show the expected card.
- Public releases do not expose local secrets, prototypes or generated clutter.

## Existing Automated Coverage

Current test files in this working copy:

- `tests/activity_pool_test.php`
- `tests/bundle_signature_verifier_test.php`
- `tests/feature_flags_test.php`
- `tests/install_xml_test.php`
- `tests/schema_validator_test.php`
- `tests/behat/golden_path.feature`
- `tests/behat/settings_dashboard.feature`
- `tests/behat/block_and_tour.feature`
- `tests/behat/behat_mod_elediacheckin.php`
- `tests/generator/lib.php`

CI pipeline (`.github/workflows/moodle-ci.yml`) runs `moodle-plugin-ci`
checks (phplint, phpmd, phpcs, phpdoc, validate, savepoints, mustache,
grunt, phpunit, behat) across a 4-cell matrix:
Moodle 4.5 / 5.0 / 5.1 × PHP 8.1 / 8.3 × pgsql / mariadb.

## Test Matrix

### Q01 Schema Validation

- **Risk:** Invalid bundle JSON enters synced content.
- **Coverage:** `schema_validator_test.php`
- **Status:** covered by file, execution not verified in this pass.

### Q02 Activity Pool and Navigation

- **Risk:** Repeat avoidance, previous button or exhausted behaviour selects
  wrong cards.
- **Coverage:** `activity_pool_test.php`
- **Status:** covered by file, execution not verified in this pass.

### Q03 Feature Flags

- **Risk:** Premium UI/source leaks into public builds.
- **Coverage:** `feature_flags_test.php`
- **Status:** covered by file, execution not verified in this pass.

### Q04 Bundle Signature Verification

- **Risk:** Premium or signed bundles are accepted incorrectly.
- **Coverage:** `bundle_signature_verifier_test.php`
- **Status:** covered by file, execution not verified in this pass.

### Q05 Admin Settings and Sync Dashboard

- **Risk:** Admins cannot save settings or understand sync state.
- **Coverage:** `settings_dashboard.feature`
- **Status:** covered by file, execution not verified in this pass.

### Q06 End-to-End Course Use

- **Risk:** Teachers cannot add and use a Check-In activity in Moodle.
- **Coverage:** `golden_path.feature`
- **Status:** covered by file, execution not verified in this pass.

### Q07 Block Compatibility

- **Risk:** Companion block launches or previews the wrong card.
- **Coverage:** `block_and_tour.feature` plus manual compatibility review.
- **Status:** covered by file, execution not verified in this pass.

### Q08 Backup and Restore Field Fidelity

- **Risk:** Restored activities silently lose target group, context, own
  questions or display options.
- **Coverage:** Backup definition and restore defaults updated.
- **Status:** PHP syntax verified; Moodle backup/restore smoke test still
  required.

### Q09 Cached Question Reads

- **Risk:** Every page view runs full SQL reads and PHP post-filtering.
- **Coverage:** `question_provider` and `category_provider` now populate the
  caches that sync already purges.
- **Status:** PHP syntax verified; cache hit behaviour not measured in Moodle.

### Q10 Clean Bundle HTML Rendering

- **Risk:** Compromised bundle content injects unsafe HTML.
- **Coverage:** Bundle `frage` and `antwort` are cleaned before `format_text()`
  in normal and presentation views.
- **Status:** PHP syntax verified; browser XSS smoke test not run.

### Q11 Filtered-Out Bundle Feedback

- **Risk:** Users think mixed mode is broken when target group/context filters
  remove every bundle question and only own questions remain.
- **Coverage:** `activity_pool::describe_pool()` (delegates to
  `analyze_pool()`) plus notices in normal and presentation templates.
- **Status:** PHP syntax verified; Moodle UI smoke test not run.

### Q12 install.xml CHAR NOT NULL DEFAULT '' regression

- **Risk:** A `NOTNULL="true" DEFAULT=""` CHAR field in `db/install.xml`
  triggers Moodle's `xmldb_field::setDefault` warning during install.xml
  parsing, which `DEBUG_DEVELOPER` escalates to a fatal `ErrorException`
  that aborts the upgrade before any cleanup step can run. PHPCS / PHPDoc
  / `validate` do not catch this because the warning is runtime-only at
  `debugging()` level; the standard PHPUnit configuration logs the notice
  without failing.
- **Coverage:** `tests/install_xml_test.php` parses the install.xml on
  every PHPUnit run and asserts no offending field exists. Smoke-tested
  against both the canonical (clean) and a known-bad fixture.
- **Status:** Test in place; CI pickup verified after commit `d3fe170`.

### Q13 Avoid-Repeat Toggle Honoured

- **Risk:** The `avoidrepeat` activity setting was a DB+form-only no-op;
  toggling it had no effect on navigation behaviour.
- **Coverage:** `activity_pool::resolve_navigation` now reads
  `$instance->avoidrepeat`. When off, the seen-set never persists to
  session and `pick_random_excluding` always draws from the full pool.
- **Status:** PHP syntax verified; behavioural test in PHPUnit not yet
  added.

## Manual Release Checks

Before publishing:

1. Run `git status` and confirm only intended module files are staged.
2. Confirm secrets are not tracked.
3. Confirm `block_elediacheckin/` remains a separate working copy.
4. Run PHPUnit tests in a Moodle test environment.
5. Run relevant Behat scenarios.
6. Install into a clean Moodle 4.5+ site.
7. Run content sync from bundled source.
8. Create a course activity and test normal, popup and presentation views.
9. Test companion block launcher against the same activity.

## Current Quality Notes

- The local tree contains release/prototype artefacts (license server,
  content bundle drafts, deploy helpers) that need explicit cleanup
  decisions before public commits.
- Version metadata is on `2026040603 / BETA / 0.9.0`; the maturity bump
  to `STABLE / 1.0.0` for first Plugins-Directory submission is a release
  decision pending in MOD-12.
- The companion block has its own DevFlow and must be tested together
  with the module when service or URL behaviour changes.

## Verification Log

### 2026-05-09 DevFlow refresh

- **Action:** Aligned the six DevFlow files with the v2026040603 release
  state; refreshed `00-master.md` (release metadata + current state),
  `01-features.md` (added F08 filter feedback, F09 cache layer),
  `03-dev-doc.md` (cache_service, analyze_pool, XSS / trust model, XMLDB
  conventions), `04-tasks.md` (full refresh, new task list),
  `05-quality.md` (Q12 install.xml lint, Q13 avoidrepeat).
- **Result:** Documentation reflects shipped behaviour.
- **Not run:** Moodle PHPUnit, Behat, install smoke test, course
  backup/restore round-trip.

### 2026-05-09 Install.xml regression test added (`d3fe170`)

- **Action:** Smoke-tested the new `tests/install_xml_test.php` logic
  against both the canonical `db/install.xml` (clean) and the known-bad
  nested snapshot (4 offenders detected).
- **Result:** Test correctly distinguishes good from bad fixtures.
- **Not run:** Live PHPUnit execution against Moodle test environment;
  pickup verified only via the GitHub-Actions CI run on push.

### 2026-05-09 CI run #25554594690 fixed (`05d3466`)

- **Action:** Stripped generic `array<...>` types from @param/@return
  tags in 6 class files (PHPDoc Checker incompatibility); fixed PHPCS
  comment capitalization and comma alignment in `db/upgrade.php`;
  removed remaining `XMLDB_NOTNULL ..., ''` patterns in historical
  upgrade step.
- **Result:** All highlighted CI errors / warnings addressed; pushed for
  re-run.

### 2026-05-08 Review feedback implementation (`38c0069`, `3c6ecb4`)

- **Action:** Ran `php -l` on changed backup/restore, service, privacy,
  view, presentation and language files for both review batches.
- **Result:** No PHP syntax errors detected.
- **Not run:** Moodle PHPUnit, Behat, course backup/restore smoke test,
  browser/UI verification.
