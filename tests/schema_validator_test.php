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
 * Unit tests for the content schema validator.
 *
 * @package    mod_elediacheckin
 * @category   test
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin;

use mod_elediacheckin\content\schema_validator;

/**
 * Tests for the schema_validator class.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(schema_validator::class)]
final class schema_validator_test extends \basic_testcase {
    // phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing
    /**
     * Build a minimally valid bundle with a single well-formed question.
     *
     * @param array $overrides Replace or add top-level bundle fields.
     * @param array $questionoverrides Replace or add fields inside the first question.
     * @return array
     */
    private function minimal_bundle(array $overrides = [], array $questionoverrides = []): array {
        $question = [
            'id'           => 'checkin-0001',
            'ziel'         => 'checkin',
            'kategorie'    => ['kennenlernen'],
            'frage'        => 'Wie geht es dir heute?',
            'hat_antwort'  => false,
            'sprache'      => 'de',
            'lizenz'       => 'CC-BY-4.0',
            'version'      => '1',
            'status'       => 'published',
            'created_at'   => '2026-01-01T00:00:00Z',
            'updated_at'   => '2026-01-01T00:00:00Z',
        ];
        $question = array_merge($question, $questionoverrides);

        $bundle = [
            'schema_version' => '1.0',
            'bundle_id'      => 'demo',
            'bundle_version' => '1.0.0',
            'language'       => 'de',
            'questions'      => [$question],
        ];
        return array_merge($bundle, $overrides);
    }

    public function test_minimal_bundle_is_valid(): void {
        $validator = new schema_validator();
        $this->assertTrue($validator->validate($this->minimal_bundle()));
        $this->assertSame([], $validator->get_errors());
    }

    public function test_top_level_must_be_array(): void {
        $validator = new schema_validator();
        $this->assertFalse($validator->validate('not-an-array'));
        $this->assertNotEmpty($validator->get_errors());
    }

    public function test_missing_bundle_header_fields_are_reported(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle();
        unset($bundle['bundle_id'], $bundle['language']);
        $this->assertFalse($validator->validate($bundle));
        $errors = implode("\n", $validator->get_errors());
        $this->assertStringContainsString('bundle_id', $errors);
        $this->assertStringContainsString('language', $errors);
    }

    public function test_unsupported_major_schema_version_is_rejected(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle(['schema_version' => '2.0']);
        $this->assertFalse($validator->validate($bundle));
        $this->assertStringContainsString(
            'Unsupported schema_version',
            implode("\n", $validator->get_errors())
        );
    }

    public function test_language_must_be_two_letter_iso(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle(['language' => 'deu']);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_missing_questions_array_is_reported(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle();
        unset($bundle['questions']);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_invalid_ziel_enum_rejected(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], ['ziel' => 'bogus-ziel']);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_invalid_status_enum_rejected(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], ['status' => 'archived']);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_hat_antwort_true_requires_antwort_field(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], ['hat_antwort' => true]);
        $this->assertFalse($validator->validate($bundle));
        $this->assertStringContainsString('antwort', implode("\n", $validator->get_errors()));
    }

    public function test_hat_antwort_true_with_answer_is_valid(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], [
            'hat_antwort' => true,
            'antwort'     => 'Eine Beispielantwort.',
        ]);
        $this->assertTrue($validator->validate($bundle));
    }

    public function test_category_must_belong_to_ziel(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], [
            'ziel'      => 'retro',
            'kategorie' => ['kennenlernen'], // Belongs to 'checkin', not 'retro'.
        ]);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_kategorie_must_be_non_empty_array(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], ['kategorie' => []]);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_id_must_match_slug_pattern(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], ['id' => 'INVALID ID!']);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_link_must_be_valid_url_when_set(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], ['link' => 'not-a-url']);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_zielgruppe_must_be_valid_enum_values(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], ['zielgruppe' => ['nonsense']]);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_kontext_must_be_valid_enum_values(): void {
        $validator = new schema_validator();
        $bundle = $this->minimal_bundle([], ['kontext' => ['nowhere']]);
        $this->assertFalse($validator->validate($bundle));
    }

    public function test_static_enum_accessors_return_expected_keys(): void {
        $this->assertContains('checkin', schema_validator::get_ziel_enum());
        $this->assertContains('retro', schema_validator::get_ziel_enum());
        $this->assertContains('team', schema_validator::get_zielgruppe_enum());
        $this->assertContains('arbeit', schema_validator::get_kontext_enum());
        $catalogue = schema_validator::get_categories_by_ziel();
        $this->assertArrayHasKey('checkin', $catalogue);
        $this->assertContains('kennenlernen', $catalogue['checkin']);
    }
}
