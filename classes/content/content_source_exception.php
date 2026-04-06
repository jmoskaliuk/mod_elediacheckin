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
 * Exception raised by content sources on any fetch / validation failure.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\content;

/**
 * Domain-specific exception for all content-source failures.
 *
 * Kept deliberately simple — the caller only needs to distinguish source
 * failures from truly exceptional PHP errors.
 */
class content_source_exception extends \moodle_exception {
    /**
     * Create a new content source exception.
     *
     * @param string $errorcode Language string identifier.
     * @param string $debuginfo Additional debug detail (not shown to users).
     */
    public function __construct(string $errorcode, string $debuginfo = '') {
        parent::__construct($errorcode, 'elediacheckin', '', null, $debuginfo);
    }
}
