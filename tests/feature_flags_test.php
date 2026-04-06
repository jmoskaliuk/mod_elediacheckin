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
 * Unit tests for the compile-time feature flag helper.
 *
 * @package    mod_elediacheckin
 * @category   test
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin;

/**
 * Tests for the feature_flags utility class.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(feature_flags::class)]
final class feature_flags_test extends \basic_testcase {
    public function test_premium_enabled_matches_constant(): void {
        $this->assertSame(feature_flags::PREMIUM_ENABLED, feature_flags::premium_enabled());
    }

    public function test_premium_disabled_in_release_build(): void {
        // Protects the invariant that the plugin-directory submission ships
        // with the premium pathway turned off. If this test starts failing
        // because someone flipped the flag, make sure it was intentional.
        $this->assertFalse(
            feature_flags::PREMIUM_ENABLED,
            'Release build must ship with PREMIUM_ENABLED = false. '
            . 'See classes/feature_flags.php for the release sed step.'
        );
    }
}
