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
 * Lightweight structural validator for content bundles.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\content;

/**
 * Validates decoded JSON against the mod_elediacheckin content schema.
 *
 * This is a hand-rolled validator so the plugin has zero runtime
 * dependencies — Moodle plugins cannot pull in Composer packages at
 * install-time. The authoritative schema lives in db/content/schema.json
 * and is used in CI to lint the demo content repo; this class implements
 * the same rules in PHP.
 *
 * Supported major schema version: 1.x
 */
final class schema_validator {
    /** Major schema version this validator understands. */
    public const SUPPORTED_MAJOR = 1;

    /**
     * Public accessor for the ziel → categories catalogue.
     *
     * Exposed so that the mod_form picker can render a labelled
     * multi-select without duplicating the category list. Returns a copy,
     * not a reference, so callers cannot mutate the catalogue.
     *
     * @return array<string, string[]>
     */
    public static function get_categories_by_ziel(): array {
        return self::CATEGORIES_BY_ZIEL;
    }

    /**
     * Public accessor for the ziel enum.
     *
     * @return string[]
     */
    public static function get_ziel_enum(): array {
        return self::ZIEL_ENUM;
    }

    /**
     * Public accessor for the zielgruppe enum.
     *
     * @return string[]
     */
    public static function get_zielgruppe_enum(): array {
        return self::ZIELGRUPPE_ENUM;
    }

    /**
     * Public accessor for the kontext enum.
     *
     * @return string[]
     */
    public static function get_kontext_enum(): array {
        return self::KONTEXT_ENUM;
    }

    /** Allowed values for the "ziel" field. */
    private const ZIEL_ENUM = [
        'impuls', 'checkin', 'checkout', 'retro',
        'learning', 'funfact', 'zitat',
    ];

    /**
     * Allowed values for the optional "zielgruppe" field.
     *
     * Tags the audience a question is crafted for. Used by the activity-level
     * filter with "or untagged" semantics: a question without any zielgruppe
     * tag always matches, a tagged question must share at least one value.
     */
    private const ZIELGRUPPE_ENUM = [
        'fuehrungskraefte', 'team', 'grundschule',
    ];

    /**
     * Allowed values for the optional "kontext" field.
     *
     * Tags the setting the question is meant for. Same "or untagged"
     * semantics as zielgruppe.
     */
    private const KONTEXT_ENUM = [
        'arbeit', 'schule', 'hochschule', 'privat',
    ];

    /** Allowed values for the "status" field. */
    private const STATUS_ENUM = ['draft', 'published', 'deprecated'];

    /** Allowed categories per ziel. Must stay in sync with db/content/schema.json. */
    private const CATEGORIES_BY_ZIEL = [
        'checkin' => [
            'kennenlernen', 'eisbrecher', 'arbeitsmodus', 'fokus',
            'persoenliche-entwicklung', 'stimmung', 'energie', 'beziehung',
        ],
        'checkout' => [
            'beziehung', 'feedback', 'verbesserung', 'aktion',
            'ausblick', 'stimmung', 'energie', 'wertschaetzung',
        ],
        'retro' => [
            'was-lief-gut', 'was-lief-schlecht', 'lernen', 'aktion',
            'zusammenarbeit', 'prozess', 'stimmung',
        ],
        'impuls' => [
            'kreativitaet', 'perspektivwechsel', 'reflexion', 'fokus',
            'entscheidung', 'werte',
        ],
        // Lernreflexion: offene Reflexionsfragen zum eigenen Lernen, KEINE
        // Wissensfragen. Kategorien bilden Reflexionsperspektiven ab (Tag,
        // Transfer, Aha-Moment, Hürde, Meta) statt fachdidaktischer Formate.
        'learning' => [
            'tagesreflexion', 'transfer', 'aha', 'hindernis', 'meta',
        ],
        'funfact' => [
            'wissenschaft', 'geschichte', 'sprache', 'natur', 'technik', 'alltag',
        ],
        'zitat' => [
            'fuehrung', 'motivation', 'wandel', 'lernen',
            'zusammenarbeit', 'lebensweisheit', 'humor',
        ],
    ];

    /** @var string[] Accumulated human-readable error messages. */
    private array $errors = [];

    /**
     * Validate a decoded JSON array and return whether it conforms.
     *
     * Call get_errors() afterwards to retrieve detailed messages.
     *
     * @param mixed $decoded Result of json_decode(..., true).
     * @return bool
     */
    public function validate($decoded): bool {
        $this->errors = [];

        if (!is_array($decoded)) {
            $this->errors[] = 'Top-level JSON must be an object.';
            return false;
        }

        $this->validate_bundle_header($decoded);

        if (!isset($decoded['questions']) || !is_array($decoded['questions'])) {
            $this->errors[] = 'Missing or invalid "questions" array.';
            return false;
        }

        foreach ($decoded['questions'] as $index => $question) {
            if (!is_array($question)) {
                $this->errors[] = "Question #{$index} is not an object.";
                continue;
            }
            $this->validate_question($question, $index);
        }

        return empty($this->errors);
    }

    /**
     * Get the accumulated validation error messages.
     *
     * @return string[] Array of human-readable error messages.
     */
    public function get_errors(): array {
        return $this->errors;
    }

    /**
     * Validate the bundle header fields.
     *
     * @param array<string, mixed> $decoded The decoded bundle.
     * @return void
     */
    private function validate_bundle_header(array $decoded): void {
        foreach (['schema_version', 'bundle_id', 'bundle_version', 'language'] as $field) {
            if (empty($decoded[$field]) || !is_string($decoded[$field])) {
                $this->errors[] = "Missing or non-string bundle field: {$field}.";
            }
        }

        if (!empty($decoded['schema_version']) && is_string($decoded['schema_version'])) {
            $parts = explode('.', $decoded['schema_version']);
            $major = (int) ($parts[0] ?? 0);
            if ($major !== self::SUPPORTED_MAJOR) {
                $this->errors[] = "Unsupported schema_version: {$decoded['schema_version']} (supported major: "
                    . self::SUPPORTED_MAJOR . ').';
            }
        }

        if (
            !empty($decoded['language']) && is_string($decoded['language'])
            && !preg_match('/^[a-z]{2}$/', $decoded['language'])
        ) {
            $this->errors[] = "Field 'language' must be a 2-letter ISO-639-1 code.";
        }
    }

    /**
     * Validate a single question object.
     *
     * @param array<string, mixed> $q The question object to validate.
     * @param int $index The index of the question in the array.
     * @return void
     */
    private function validate_question(array $q, int $index): void {
        $required = [
            'id', 'ziel', 'kategorie', 'frage', 'hat_antwort',
            'sprache', 'lizenz', 'version', 'status',
            'created_at', 'updated_at',
        ];
        foreach ($required as $field) {
            if (!array_key_exists($field, $q)) {
                $this->errors[] = "Question #{$index}: missing required field '{$field}'.";
            }
        }

        if (isset($q['id']) && (!is_string($q['id']) || !preg_match('/^[a-z0-9][a-z0-9-]{2,63}$/', $q['id']))) {
            $this->errors[] = "Question #{$index}: invalid 'id' format.";
        }

        if (isset($q['ziel']) && !in_array($q['ziel'], self::ZIEL_ENUM, true)) {
            $this->errors[] = "Question #{$index}: invalid 'ziel' value '{$q['ziel']}'.";
        }

        if (isset($q['status']) && !in_array($q['status'], self::STATUS_ENUM, true)) {
            $this->errors[] = "Question #{$index}: invalid 'status' value.";
        }

        if (isset($q['hat_antwort'])) {
            if (!is_bool($q['hat_antwort'])) {
                $this->errors[] = "Question #{$index}: 'hat_antwort' must be boolean.";
            } else if ($q['hat_antwort'] === true && empty($q['antwort'])) {
                $this->errors[] = "Question #{$index}: 'antwort' required when 'hat_antwort' is true.";
            }
        }

        if (isset($q['kategorie'])) {
            if (!is_array($q['kategorie']) || empty($q['kategorie'])) {
                $this->errors[] = "Question #{$index}: 'kategorie' must be a non-empty array.";
            } else if (isset($q['ziel']) && isset(self::CATEGORIES_BY_ZIEL[$q['ziel']])) {
                $allowed = self::CATEGORIES_BY_ZIEL[$q['ziel']];
                foreach ($q['kategorie'] as $cat) {
                    if (!in_array($cat, $allowed, true)) {
                        $this->errors[] = "Question #{$index}: category '{$cat}' not allowed for ziel '{$q['ziel']}'.";
                    }
                }
            }
        }

        if (isset($q['sprache']) && (!is_string($q['sprache']) || !preg_match('/^[a-z]{2}$/', $q['sprache']))) {
            $this->errors[] = "Question #{$index}: 'sprache' must be a 2-letter ISO-639-1 code.";
        }

        if (isset($q['link']) && $q['link'] !== '' && filter_var($q['link'], FILTER_VALIDATE_URL) === false) {
            $this->errors[] = "Question #{$index}: 'link' must be a valid URL when set.";
        }

        // Optional zielgruppe[] — must be array of enum values when set.
        if (array_key_exists('zielgruppe', $q)) {
            if (!is_array($q['zielgruppe'])) {
                $this->errors[] = "Question #{$index}: 'zielgruppe' must be an array when present.";
            } else {
                foreach ($q['zielgruppe'] as $zg) {
                    if (!in_array($zg, self::ZIELGRUPPE_ENUM, true)) {
                        $this->errors[] = "Question #{$index}: invalid zielgruppe value '{$zg}'.";
                    }
                }
            }
        }

        // Optional kontext[] — must be array of enum values when set.
        if (array_key_exists('kontext', $q)) {
            if (!is_array($q['kontext'])) {
                $this->errors[] = "Question #{$index}: 'kontext' must be an array when present.";
            } else {
                foreach ($q['kontext'] as $kx) {
                    if (!in_array($kx, self::KONTEXT_ENUM, true)) {
                        $this->errors[] = "Question #{$index}: invalid kontext value '{$kx}'.";
                    }
                }
            }
        }
    }
}
