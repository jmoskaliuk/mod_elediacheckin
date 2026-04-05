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
 * Content source that returns the default bundle shipped inside the plugin.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\content;

defined('MOODLE_INTERNAL') || die();

/**
 * Reads db/content/default.json from the plugin directory and returns it as
 * a validated content_bundle.
 *
 * This source always succeeds if the plugin is installed correctly — it is
 * the guaranteed fallback for the registry.
 */
final class bundled_content_source implements content_source_interface {

    /**
     * Path to the shipped bundle, relative to the plugin root.
     */
    private const BUNDLE_RELPATH = '/db/content/default.json';

    /**
     * @inheritDoc
     */
    public function get_id(): string {
        return 'bundled';
    }

    /**
     * @inheritDoc
     */
    public function get_display_name(): string {
        return get_string('contentsource_bundled', 'elediacheckin');
    }

    /**
     * @inheritDoc
     */
    public function test_connection(): bool {
        return is_readable($this->get_bundle_path());
    }

    /**
     * @inheritDoc
     */
    public function fetch_bundle(): content_bundle {
        $path = $this->get_bundle_path();

        if (!is_readable($path)) {
            throw new content_source_exception(
                'contenterror_bundlemissing',
                "Default bundle not readable at {$path}"
            );
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new content_source_exception(
                'contenterror_bundleread',
                "file_get_contents failed for {$path}"
            );
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new content_source_exception(
                'contenterror_bundleparse',
                'json_decode error: ' . json_last_error_msg()
            );
        }

        $validator = new schema_validator();
        if (!$validator->validate($decoded)) {
            throw new content_source_exception(
                'contenterror_bundleinvalid',
                implode(' | ', $validator->get_errors())
            );
        }

        return content_bundle::from_array($decoded);
    }

    /**
     * Absolute path to the bundled default file.
     *
     * @return string
     */
    private function get_bundle_path(): string {
        global $CFG;
        return $CFG->dirroot . '/mod/elediacheckin' . self::BUNDLE_RELPATH;
    }
}
