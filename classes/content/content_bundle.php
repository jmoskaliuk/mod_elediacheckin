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
 * Immutable value object representing a validated content bundle.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\content;

defined('MOODLE_INTERNAL') || die();

/**
 * A content bundle is the unit of synchronisation: bundle metadata plus an
 * array of question records. Instances are always schema-validated before
 * being constructed — downstream code may rely on the structure.
 */
final class content_bundle {

    /** @var string */
    private string $schemaversion;

    /** @var string */
    private string $bundleid;

    /** @var string */
    private string $bundleversion;

    /** @var string */
    private string $language;

    /** @var int|null */
    private ?int $generatedat;

    /** @var array<int, array<string, mixed>> */
    private array $questions;

    /**
     * Constructor. Prefer content_bundle::from_array() for typical usage.
     *
     * @param string $schemaversion
     * @param string $bundleid
     * @param string $bundleversion
     * @param string $language
     * @param int|null $generatedat Unix timestamp or null if missing.
     * @param array<int, array<string, mixed>> $questions
     */
    public function __construct(
        string $schemaversion,
        string $bundleid,
        string $bundleversion,
        string $language,
        ?int $generatedat,
        array $questions
    ) {
        $this->schemaversion = $schemaversion;
        $this->bundleid      = $bundleid;
        $this->bundleversion = $bundleversion;
        $this->language      = $language;
        $this->generatedat   = $generatedat;
        $this->questions     = $questions;
    }

    /**
     * Build a bundle from a decoded JSON array. Does NOT validate — call the
     * schema_validator first.
     *
     * @param array<string, mixed> $decoded
     * @return self
     */
    public static function from_array(array $decoded): self {
        $generatedat = null;
        if (!empty($decoded['generated_at'])) {
            $parsed = strtotime((string) $decoded['generated_at']);
            $generatedat = $parsed === false ? null : $parsed;
        }

        return new self(
            (string) ($decoded['schema_version'] ?? ''),
            (string) ($decoded['bundle_id'] ?? ''),
            (string) ($decoded['bundle_version'] ?? ''),
            (string) ($decoded['language'] ?? ''),
            $generatedat,
            array_values($decoded['questions'] ?? [])
        );
    }

    /**
     * Get the schema version.
     *
     * @return string The schema version string.
     */
    public function get_schema_version(): string {
        return $this->schemaversion;
    }

    /**
     * Get the bundle identifier.
     *
     * @return string The bundle id.
     */
    public function get_bundle_id(): string {
        return $this->bundleid;
    }

    /**
     * Get the bundle version.
     *
     * @return string The bundle version.
     */
    public function get_bundle_version(): string {
        return $this->bundleversion;
    }

    /**
     * Get the language code.
     *
     * @return string The language code.
     */
    public function get_language(): string {
        return $this->language;
    }

    /**
     * Get the generation timestamp.
     *
     * @return int|null The Unix timestamp when the bundle was generated, or null.
     */
    public function get_generated_at(): ?int {
        return $this->generatedat;
    }

    /**
     * Get all questions in the bundle.
     *
     * @return array<int, array<string, mixed>> Array of question objects.
     */
    public function get_questions(): array {
        return $this->questions;
    }

    /**
     * Convenience: number of questions in the bundle.
     *
     * @return int The count of questions.
     */
    public function count_questions(): int {
        return count($this->questions);
    }
}
