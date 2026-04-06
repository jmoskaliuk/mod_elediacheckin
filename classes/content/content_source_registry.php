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
 * Content source registry.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\content;

use mod_elediacheckin\feature_flags;

defined('MOODLE_INTERNAL') || die();

/**
 * Maps source IDs to content_source_interface implementations.
 *
 * Ships with bundled + git + eledia_premium. Third parties could in theory
 * add their own implementations via a Moodle hook, but that is explicitly
 * out of scope for the first release.
 */
final class content_source_registry {

    /** @var array<string, content_source_interface>|null */
    private static ?array $sources = null;

    /**
     * Lazily build and return the registry.
     *
     * @return array<string, content_source_interface>
     */
    public static function all(): array {
        if (self::$sources === null) {
            self::$sources = [];
            foreach (self::build_default_sources() as $source) {
                self::$sources[$source->get_id()] = $source;
            }
        }
        return self::$sources;
    }

    /**
     * Look up a source by id. Returns null if unknown so callers can fall
     * back to the bundled source cleanly.
     *
     * @param string $id
     * @return content_source_interface|null
     */
    public static function get(string $id): ?content_source_interface {
        $all = self::all();
        return $all[$id] ?? null;
    }

    /**
     * Returns the source that should always be available, regardless of
     * configuration. Used as the fallback when the configured source fails.
     *
     * @return content_source_interface
     */
    public static function get_fallback(): content_source_interface {
        return self::get('bundled') ?? new bundled_content_source();
    }

    /**
     * Reset the cached registry for unit tests only.
     *
     * @return void
     */
    public static function reset_for_testing(): void {
        self::$sources = null;
    }

    /**
     * Instantiate the default set of sources shipped with the plugin.
     *
     * @return array<int, content_source_interface>
     */
    private static function build_default_sources(): array {
        $sources = [
            new bundled_content_source(),
            new git_content_source(),
        ];
        // Premium source is gated behind a build-time feature flag. In the
        // first Plugins-Directory release we ship with the flag OFF so users
        // don't see a half-finished license-server option; the classes stay
        // loadable so unit tests keep working regardless of the flag state.
        if (feature_flags::premium_enabled()) {
            $sources[] = new eledia_premium_content_source();
        }
        return $sources;
    }
}
