# eLeDia Check-In (mod_elediacheckin)

Activity module for Moodle 4.5+ that displays didactic Check-in and Check-out
impulse cards — short questions, reflections, or quotes — in courses. Ideal
for session openers, retrospectives, or learning reflections.

Questions are sourced from a bundled content pack or a custom Git repository
and synchronised into Moodle on a schedule (not fetched live at runtime).

For a compact sidebar launcher, see the companion plugin
[block_elediacheckin](https://github.com/jmoskaliuk/block_elediacheckin).

## Features

- Random impulse cards drawn from a configurable pool per activity
- Multiple content goals: Check-in, Check-out, Retrospective, Learning
  reflection, Quotes
- Category and audience filters per activity
- Teacher-authored questions alongside or instead of the bundled pool
- Fullscreen and popup presentation modes for screen-sharing
- Cursor-based session history with back/forward navigation
- Configurable behaviour when all cards have been shown (restart or stop)
- Three bundled user tours (activity view, activity settings, admin settings)
- Companion block for course and frontpage sidebar launchers
- Bundled default content source or custom Git repository

## Requirements

- Moodle 4.5 or later (tested up to 5.1)
- PHP 8.1+
- PostgreSQL or MySQL/MariaDB

## Installation

1. Download the latest release ZIP from
   [GitHub Releases](https://github.com/jmoskaliuk/mod_elediacheckin/releases).
2. In Moodle, go to *Site administration → Plugins → Install plugins* and
   upload the ZIP.
3. Follow the on-screen upgrade prompts.
4. Optionally install the companion block plugin
   (`block_elediacheckin`) for sidebar launchers.

## Configuration

After installation, visit *Site administration → Plugins → Activity modules →
eLeDia Check-In* to configure the content source (bundled default or custom
Git repository) and run the initial content sync.

## Dependencies

None. `block_elediacheckin` depends on this plugin, not the other way round.

## Bug tracker

[GitHub Issues](https://github.com/jmoskaliuk/mod_elediacheckin/issues)

## License

GNU GPL v3 or later — see [COPYING](https://www.gnu.org/licenses/gpl-3.0.html).

## Credits

Developed by [eLeDia GmbH](https://www.eledia.de) (info@eledia.de).
