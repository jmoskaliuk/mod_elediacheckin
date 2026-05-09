# Developer Documentation - mod_elediacheckin

## Component Layout

- `version.php` - Moodle plugin metadata
- `db/install.xml` - activity, question and sync log tables
- `db/upgrade.php` - schema migrations
- `db/caches.php` - application cache definitions for questions / categories
- `db/tasks.php` - scheduled sync task registration
- `settings.php` - admin configuration and embedded sync dashboard
- `mod_form.php` - activity instance form
- `view.php` - normal activity view
- `present.php` - popup/presentation view
- `classes/content/*` - content source, bundle and validation layer
- `classes/local/service/*` - runtime and sync services
  (incl. `cache_service` thin wrapper around `\cache::make()`)
- `classes/local/admin/dashboard_renderer.php` - admin sync status UI
- `templates/*` - Mustache templates
- `amd/src/*` and `amd/build/*` - client-side behaviour
- `tests/*` - PHPUnit and Behat coverage

## Data Model

The main tables are:

- `elediacheckin` - one row per activity instance
- `elediacheckin_question` - locally synced card content
- `elediacheckin_sync_log` - sync attempt protocol

`elediacheckin_question.stage` separates staging rows from live rows during
sync. Runtime providers read live rows only.

## Content Source Layer

The content source abstraction lets the sync service read from different
origins while keeping runtime reads local.

Current source classes include:

- bundled content source
- Git/raw URL content source
- premium content source behind a feature flag

The schema validator is the boundary that protects DB import from malformed
content bundles.

## Runtime Card Selection

Runtime card selection lives in the service layer, especially
`activity_pool` and `question_provider`. The selection logic combines:

- activity filters
- content language resolution
- own questions mode
- repeat/session state (gated by the `avoidrepeat` activity setting; when
  off the seen-set never persists and `pick_random_excluding` always draws
  from the full pool)
- exhausted-pool behaviour
- audience-filter diagnostics (`analyze_pool()` returns pool + counts in a
  single pass; `build_pool()` and `describe_pool()` are thin wrappers)

The block plugin calls into this layer for preview cards, so method signatures
and returned template context should be changed carefully.

## XSS / Trust Model

- Bundle JSON is signed and verified at import time
  (`bundle_signature_verifier`) before rows ever hit
  `elediacheckin_question`.
- At render time, bundle question / answer HTML is still passed through
  `format_text(..., FORMAT_HTML)` with default `noclean = false` and
  `trusted = false` so Moodle's filter chain strips scripts and unsafe
  attributes even from a hypothetically compromised bundle. Defence in
  depth, deliberate.
- Own questions render with `FORMAT_PLAIN`.
- The `mod_form` category-ziel JSON map is embedded inside a
  `<script type="application/json">` element and `json_encode`d with
  `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT` so a
  hypothetical `</script>` substring cannot break out of the container.
- Privacy provider declares only the three teacher-input fields on
  `elediacheckin` (`name`, `intro`, `ownquestions`); session navigation
  state lives in `$SESSION->elediacheckin_nav` and is intentionally not
  declared because it is transient and never persisted.

## XMLDB Conventions

Moodle 4.5+ raises a `debugging()` notice for any CHAR NOT NULL column
that declares `DEFAULT=""`. In `DEBUG_DEVELOPER` mode that notice is
escalated to a fatal `ErrorException`, aborting the upgrade before any
cleanup step can run. Conventions for this plugin:

- `db/install.xml`: never declare `DEFAULT=""` on a CHAR field. Either
  drop `NOTNULL="true"` (NULL-allowed) or omit the `DEFAULT` attribute
  for required fields populated explicitly at insert time.
- `db/upgrade.php`: same rule for `add_field` / `change_field_*` calls.
- A static regression test (`tests/install_xml_test.php`) parses the
  install.xml on every PHPUnit run and fails on offending fields.
- Step `2026040603` uses raw `$DB->execute("ALTER TABLE … ALTER COLUMN …
  DROP DEFAULT")` on MySQL/MariaDB and PostgreSQL to clean up the
  leftover empty-string defaults left by step `2026040543`'s
  `change_field_notnull` calls. Raw SQL bypasses the xmldb layer
  entirely so the introspection itself cannot fire the notice.

## Admin UX

`settings.php` intentionally embeds configuration and sync status on one admin
page. The current design keeps source settings and language fallback fields
above the sync panel, with save actions visible before operational sync
controls.

## Build and Assets

AMD sources live under `amd/src/`; built files live under `amd/build/`.
Any change to AMD source should be accompanied by regenerated build artefacts
according to the Moodle plugin build workflow used for this project.

## Related Local Material

The root working directory currently contains additional material that is not
necessarily part of the public module repository:

- content bundle drafts
- license server prototype
- release ZIP artefacts

Before release commits, decide explicitly which of these artefacts belong in
the repo and which should remain excluded.

## Compatibility Notes

Keep compatibility with `block_elediacheckin` when changing:

- `view.php` or `present.php` URL parameters
- activity capabilities
- `activity_pool` and `question_provider`
- template context keys used by launcher or preview flows
- language string identifiers shared by the block

