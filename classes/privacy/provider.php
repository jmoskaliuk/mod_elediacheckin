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
 * Privacy API provider for mod_elediacheckin.
 *
 * The plugin does not store any user-identifiable data on its own: question content
 * comes from an external repository and is rendered read-only. No answers are captured
 * in the MVP. We therefore implement the null provider to declare "no personal data".
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\privacy;

/**
 * Null provider - this plugin does not store personal data.
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Returns the language string identifier explaining why no data is stored.
     *
     * @return string The language string identifier for privacy reason.
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
