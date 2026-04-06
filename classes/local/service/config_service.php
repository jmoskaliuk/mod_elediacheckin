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
 * Config service - thin wrapper around get_config() / set_config().
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\local\service;

/**
 * Reads plugin-level settings.
 */
class config_service {
    /** @var string */
    public const COMPONENT = 'mod_elediacheckin';

    /**
     * Returns a single setting value, or the supplied default if unset.
     *
     * @param string $name The setting name.
     * @param mixed $default The default value if not configured.
     * @return mixed The setting value or default.
     */
    public function get(string $name, $default = null) {
        $value = get_config(self::COMPONENT, $name);
        return ($value === false || $value === null || $value === '') ? $default : $value;
    }

    /**
     * Persists a setting value.
     *
     * @param string $name The setting name.
     * @param mixed $value The value to set.
     * @return void
     */
    public function set(string $name, $value): void {
        set_config($name, $value, self::COMPONENT);
    }
}
