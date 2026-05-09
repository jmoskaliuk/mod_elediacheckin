# DevFlow Master - mod_elediacheckin

> Project-specific DevFlow for `mod_elediacheckin`. Framework reference:
> [eLeDia.OS_DevFlow](https://github.com/jmoskaliuk/eLeDia.OS_DevFlow).

## Identity

- **Name:** eLeDia Check-In
- **Moodle component:** `mod_elediacheckin`
- **Type:** Moodle activity module
- **Repository:** `https://github.com/jmoskaliuk/mod_elediacheckin`
- **Companion plugin:** `block_elediacheckin`
- **Current release metadata:** `0.9.0`, `MATURITY_BETA`, `version = 2026040603`
- **Target Moodle:** Moodle 4.5+ (CI matrix covers 4.5, 5.0, 5.1)
- **License:** GNU GPL v3 or later

## Purpose

The plugin gives teachers a lightweight way to open, close or reflect on
learning situations with short impulse cards. It should feel simple in course
use while still allowing admins to manage reusable, synchronised card bundles.

The core promise is:

1. Teachers configure a Check-In activity for a course context.
2. The activity draws fitting cards from synced content and optional
   teacher-authored questions.
3. Learners and facilitators can use the card in normal, popup or presentation
   contexts without needing to understand the content backend.

## DevFlow Files

- `00-master.md` - project identity, scope and operating rules
- `01-features.md` - product and feature map
- `02-user-doc.md` - user-facing behaviour and workflows
- `03-dev-doc.md` - technical architecture and implementation notes
- `04-tasks.md` - operational task list and current status
- `05-quality.md` - tests, risks and verification matrix

## System Boundary

`mod_elediacheckin` owns:

- activity instance configuration
- synced content tables and sync log
- content source abstraction for bundled, Git and hidden premium sources
- schema validation and bundle import
- runtime card selection and session navigation
- activity and presentation templates
- admin settings and sync status dashboard
- user tours and automated tests

It does not own:

- sidebar placement or compact launchers; that belongs to `block_elediacheckin`
- the external content repository itself
- the license server prototype in `license_server/`
- packaged ZIP artefacts

## Companion Relationship

`block_elediacheckin` depends on this module and reuses its service layer. The
dependency is intentionally one-way: the module must work without the block.
Changes to card resolution, URL parameters, capabilities or template context
must be checked against the block because it launches and previews module
activities.

## Current Local State

As of 2026-05-09 this working copy is on `origin/main` at commit `d3fe170`.
Working tree is clean of intentional product changes; only build/deployment
helpers and supporting prototypes remain untracked.

Latest DevFlow refresh: 2026-05-09 after the v2026040601 → v2026040603
release window which addressed the plugin code review and the Moodle 4.5+
XMLDB CHAR-NOT-NULL-DEFAULT-empty-string warning.

Important local artefacts outside the module repository boundary include:

- `block_elediacheckin/` - separate Git working copy
- `content_elediacheckin/` - content bundle working material
- `license_server/` - local premium/license server prototype
- release ZIP files and deployment helpers

The previously documented nested `mod_elediacheckin/` snapshot has been
removed because it carried a stale `db/install.xml` with the
`NOTNULL="true" DEFAULT=""` pattern that triggered Moodle's runtime XMLDB
warning when accidentally deployed.

## Working Rules

- Keep the six DevFlow files aligned when behaviour changes.
- Document product intent in `01-features.md` before or with implementation.
- Document technical decisions in `03-dev-doc.md`.
- Track operational work in `04-tasks.md`.
- Add or update verification evidence in `05-quality.md` for every risky
  change.
- Keep `block_elediacheckin` compatibility visible when touching URLs,
  capabilities, card selection or templates.
