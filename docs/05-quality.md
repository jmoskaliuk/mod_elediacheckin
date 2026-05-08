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
- `tests/schema_validator_test.php`
- `tests/behat/golden_path.feature`
- `tests/behat/settings_dashboard.feature`
- `tests/behat/block_and_tour.feature`
- `tests/behat/behat_mod_elediacheckin.php`
- `tests/generator/lib.php`

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
- **Coverage:** `activity_pool::describe_pool()` plus notices in normal and
  presentation templates.
- **Status:** PHP syntax verified; Moodle UI smoke test not run.

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

- DevFlow documentation was added without running Moodle PHPUnit or Behat.
- The local tree contains release/prototype artefacts that need explicit
  cleanup decisions before a public commit.
- Version metadata and remote tags need reconciliation before release.
- `thirdpartylibs.xml` is deleted locally while it exists on `origin/main`;
  this needs a release decision before staging.
- The companion block has its own DevFlow and must be tested together with the
  module when service or URL behaviour changes.

## Verification Log

### 2026-05-08 DevFlow refresh

- **Action:** Checked `git status --short --branch` and `git diff --name-status`
  for the module working copy.
- **Result:** Local diff and open decisions documented in `04-tasks.md`.
- **Not run:** Moodle PHPUnit, Behat, install smoke test.

### 2026-05-08 Review feedback implementation

- **Action:** Ran `php -l` on changed backup/restore, service, privacy, view,
  presentation and language files.
- **Result:** No PHP syntax errors detected.
- **Not run:** Moodle PHPUnit, Behat, course backup/restore smoke test,
  browser/UI verification.
