# Features - mod_elediacheckin

## Product Goal

eLeDia Check-In helps teachers and facilitators add short reflective moments to
courses. The module should be quick to configure, reliable in live teaching
settings and flexible enough to support reusable curated card content.

## Feature Map

### F01 Activity-Based Check-In Cards

Teachers add a Moodle activity that displays one card at a time. Cards can be
used as session openers, check-outs, retrospectives, learning reflections,
quotes, fun facts or general impulses.

Current implementation:

- activity module `mod_elediacheckin`
- standard Moodle module form
- normal view in `view.php`
- presentation/popup view in `present.php`

### F02 Content Goals and Filters

Each activity can filter the card pool by:

- `ziele` such as check-in, check-out, retro, learning, quote or impulse
- categories
- target group (`zielgruppe`)
- context (`kontext`)
- content language

Categories are dynamically filtered client-side by selected `ziele`.

### F03 Teacher-Authored Questions

Teachers can add own questions per activity. The activity can use them mixed
with bundled content, use only own questions or ignore them.

### F04 Session Navigation

The activity avoids immediate repeats and can show a previous-card button.
When the pool is exhausted, the activity can either restart the session pool or
show an empty-state card.

### F05 Content Synchronisation

Admins configure a content source and synchronise cards into Moodle. Runtime
views read local Moodle tables instead of fetching remote content live.

Supported source model:

- bundled default content
- raw Git-hosted bundle JSON
- premium source behind a build-time feature flag

### F06 Admin Sync Dashboard

The admin settings page includes source configuration, language fallback
settings and a sync status panel. The current UX intent is a single admin
screen with the save action above sync status.

### F07 Presentation Modes

Cards can be shown in normal view, popup view or fullscreen/presentation mode.
The companion block can pass the currently previewed card to the activity so
the launched view opens the same card.

### F08 Filter Feedback

When mixed mode is active and the configured target group / context filter
removes every bundle question while own questions still render, the views
show an explanatory inline notice instead of leaving the user to debug the
empty pool. Powered by `activity_pool::analyze_pool()` which produces both
the merged pool and the diagnostic counts in a single pass.

### F09 Read-Through Cache

`question_provider` and `category_provider` populate Moodle application
caches (defined in `db/caches.php`) on every read instead of only purging
them after sync. Filter results are keyed by a normalised SHA-1 of the
filter array; id lookups are keyed by raw id. The sync service purges both
caches on every successful sync run.

### F10 Moodle Integration

The module follows Moodle conventions for:

- capabilities
- backup and restore
- privacy provider
- scheduled tasks
- cache definitions
- language strings
- Mustache templates
- Behat and PHPUnit coverage
- user tours

## Non-Goals

- The module is not a real-time polling or chat tool.
- The module does not evaluate answers or grade learners.
- The module does not own sidebar rendering; that is the companion block.
- The module should not require the premium content path for public releases.

## Open Product Questions

- Should the release jump to `MATURITY_STABLE` / `1.0.0` for the first
  Moodle Plugins Directory submission, or stay at `MATURITY_BETA` / `0.9.0`
  for one more iteration cycle?
- Which local documentation and prototype artefacts belong in the module
  repo, and which should remain separate working material?
- Should premium content remain compiled out by default, or become a
  documented optional integration later?

