# DevFlow Master - mod_elediacheckin

> Project-specific DevFlow for `mod_elediacheckin`. Framework reference:
> [eLeDia.OS_DevFlow](https://github.com/jmoskaliuk/eLeDia.OS_DevFlow).

## Identity

- **Name:** eLeDia Check-In
- **Moodle component:** `mod_elediacheckin`
- **Type:** Moodle activity module
- **Repository:** `https://github.com/jmoskaliuk/mod_elediacheckin`
- **Companion plugin:** `block_elediacheckin`
- **Current local release metadata:** `0.2.0`, `MATURITY_ALPHA`
- **Target Moodle:** Moodle 4.5+
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

As of 2026-05-08 this working copy is based on `origin/main` commit `fd31aa2`,
with local modifications and additional working artefacts. DevFlow work should
not assume that the local tree is already release-clean.

Latest DevFlow refresh: 2026-05-08 after splitting the local project into two
Git working copies and adding this DevFlow set.

Tracked module files currently differ from `origin/main` in these groups:

- repository metadata and release notes: `.gitattributes`, `CHANGES.md`,
  `version.php`, `thirdpartylibs.xml`
- content and schema services
- activity pool, question provider and sync service
- presentation/view templates
- AMD category filter build artefacts
- PHPUnit and Behat tests
- `README.md` and DevFlow documentation

Important local artefacts outside the module repository boundary include:

- `block_elediacheckin/` - separate Git working copy
- `content_elediacheckin/` - content bundle working material
- `license_server/` - local premium/license server prototype
- `mod_elediacheckin/` - older or packaged nested copy
- release ZIP files and deployment helpers

## Working Rules

- Keep the six DevFlow files aligned when behaviour changes.
- Document product intent in `01-features.md` before or with implementation.
- Document technical decisions in `03-dev-doc.md`.
- Track operational work in `04-tasks.md`.
- Add or update verification evidence in `05-quality.md` for every risky
  change.
- Keep `block_elediacheckin` compatibility visible when touching URLs,
  capabilities, card selection or templates.
