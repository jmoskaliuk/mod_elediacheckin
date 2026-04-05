# mod_elediacheckin

Activity module by eLeDia GmbH that displays didactic Check-in and Check-out questions
in Moodle courses. Questions are sourced from an external, Git-managed content repository
and synchronised into Moodle on a schedule — not fetched live at runtime.

This plugin provides the full activity experience. For a compact sidebar variant, see the
companion plugin `block_elediacheckin`, which depends on this module.

## Status

Scaffold only. See `doc/fach-und-technikkonzept.md` for the specification this plugin
is being built against.

## Features (planned, per concept)

- Separate Check-in and Check-out question modes
- Category filtering
- Random question selection with "avoid repeat" option
- Keyboard / swipe navigation on mobile
- Multi-language question content (resolved via activity → course → user → fallback)
- Git-based content source with manual and scheduled synchronisation
- Staging import with rollback on validation failure

## Dependencies

None. `block_elediacheckin` depends on this plugin (not the other way round).

## Installation

Copy the `elediacheckin` directory (i.e. the contents of this scaffold folder) to
`mod/elediacheckin/` in your Moodle installation and visit
*Site administration → Notifications* to trigger the install.

## License

GNU GPL v3 or later — see file headers.
