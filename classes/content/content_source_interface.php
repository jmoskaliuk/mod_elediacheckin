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
 * Content source strategy interface.
 *
 * Implementations provide content_bundle instances from different backends
 * (bundled default JSON, customer git repo, eLeDia license server, …).
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\content;

defined('MOODLE_INTERNAL') || die();

/**
 * Strategy interface implemented by every content source.
 *
 * A content source is responsible for fetching a bundle from its backend,
 * validating it against the JSON schema, and returning it as a content_bundle
 * value object. Failures MUST be signalled by throwing content_source_exception
 * so the caller can log and skip them uniformly.
 */
interface content_source_interface {

    /**
     * Stable identifier for this source type. Used as a key in the registry
     * and as the value stored in the plugin settings.
     *
     * Must match /^[a-z][a-z0-9_]{1,31}$/.
     *
     * @return string
     */
    public function get_id(): string;

    /**
     * Human-readable name, translated via get_string(). Shown in admin UI.
     *
     * @return string
     */
    public function get_display_name(): string;

    /**
     * Quick connectivity / availability probe. Implementations should be
     * cheap (HEAD request, file_exists, …) and MUST NOT modify any state.
     *
     * @return bool True if the source is reachable and appears usable.
     */
    public function test_connection(): bool;

    /**
     * Fetch and validate the current bundle from the source.
     *
     * Contract:
     *  - Throws content_source_exception on any failure (network, parse,
     *    validation, signature) — the caller will treat this as a no-op sync.
     *  - Never partially mutates global state; returning a bundle means the
     *    caller is free to stage and swap it atomically.
     *
     * @return content_bundle
     * @throws content_source_exception
     */
    public function fetch_bundle(): content_bundle;
}
