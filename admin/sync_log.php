<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Deprecated compatibility redirect.
 *
 * The former stand-alone sync-log admin page has been merged into the
 * plugin's main admin settings page at
 * /admin/settings.php?section=modsettingelediacheckin.
 *
 * This stub exists only so old bookmarks still resolve. The page itself
 * no longer renders anything — it just redirects.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

redirect(
    new moodle_url(
        '/admin/settings.php',
        ['section' => 'modsettingelediacheckin']
    )
);
