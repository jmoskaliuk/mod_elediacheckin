# Developer Documentation - mod_elediacheckin

## Component Layout

- `version.php` - Moodle plugin metadata
- `db/install.xml` - activity, question and sync log tables
- `db/upgrade.php` - schema migrations
- `db/tasks.php` - scheduled sync task registration
- `settings.php` - admin configuration and embedded sync dashboard
- `mod_form.php` - activity instance form
- `view.php` - normal activity view
- `present.php` - popup/presentation view
- `classes/content/*` - content source, bundle and validation layer
- `classes/local/service/*` - runtime and sync services
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
- repeat/session state
- exhausted-pool behaviour

The block plugin calls into this layer for preview cards, so method signatures
and returned template context should be changed carefully.

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
- an older nested copy of the module

Before release commits, decide explicitly which of these artefacts belong in
the repo and which should remain excluded.

## Compatibility Notes

Keep compatibility with `block_elediacheckin` when changing:

- `view.php` or `present.php` URL parameters
- activity capabilities
- `activity_pool` and `question_provider`
- template context keys used by launcher or preview flows
- language string identifiers shared by the block

