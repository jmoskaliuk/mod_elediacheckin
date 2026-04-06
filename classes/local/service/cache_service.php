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
 * Cache service - thin wrapper around the Moodle Cache API.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\local\service;

/**
 * Centralises cache access so providers don't duplicate cache wiring.
 */
class cache_service {
    /**
     * Returns the questions cache.
     *
     * @return \cache The questions cache instance.
     */
    public function questions(): \cache {
        return \cache::make('mod_elediacheckin', 'questions');
    }

    /**
     * Returns the categories cache.
     *
     * @return \cache The categories cache instance.
     */
    public function categories(): \cache {
        return \cache::make('mod_elediacheckin', 'categories');
    }

    /**
     * Purges both plugin caches.
     *
     * @return void
     */
    public function purge_all(): void {
        $this->questions()->purge();
        $this->categories()->purge();
    }
}
