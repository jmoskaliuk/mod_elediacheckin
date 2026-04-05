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
 * Unit tests for activity_pool (parse_own_questions + navigation resolver).
 *
 * The DB-dependent paths (build_pool with bundle hits) are covered by the
 * Behat feature tests; here we focus on the pure helpers that can run
 * without a full Moodle bootstrap database write.
 *
 * @package    mod_elediacheckin
 * @category   test
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\local\service;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(activity_pool::class)]
final class activity_pool_test extends \advanced_testcase {

    /**
     * Build a fake instance row with only the fields activity_pool reads.
     */
    private function fake_instance(array $overrides = []): \stdClass {
        $i = new \stdClass();
        $i->id               = 1;
        $i->ziele            = 'checkin';
        $i->categories       = '';
        $i->zielgruppe       = '';
        $i->kontext          = '';
        $i->ownquestions     = '';
        $i->ownquestionsmode = 0;
        foreach ($overrides as $k => $v) {
            $i->{$k} = $v;
        }
        return $i;
    }

    public function test_parse_own_questions_empty_returns_empty_array(): void {
        $this->assertSame([], activity_pool::parse_own_questions($this->fake_instance()));
    }

    public function test_parse_own_questions_one_line_per_card(): void {
        $instance = $this->fake_instance(['ownquestions' => "Frage A\nFrage B\nFrage C"]);
        $pool = activity_pool::parse_own_questions($instance);
        $this->assertCount(3, $pool);
        $this->assertSame('Frage A', $pool[0]->frage);
        $this->assertSame('Frage B', $pool[1]->frage);
        $this->assertSame('Frage C', $pool[2]->frage);
    }

    public function test_parse_own_questions_skips_blank_lines(): void {
        $instance = $this->fake_instance([
            'ownquestions' => "Frage A\n\n   \nFrage B\n",
        ]);
        $pool = activity_pool::parse_own_questions($instance);
        $this->assertCount(2, $pool);
        $this->assertSame('Frage A', $pool[0]->frage);
        $this->assertSame('Frage B', $pool[1]->frage);
    }

    public function test_parse_own_questions_uses_virtual_category_marker(): void {
        $instance = $this->fake_instance(['ownquestions' => 'Eine Frage']);
        $pool = activity_pool::parse_own_questions($instance);
        $this->assertSame(activity_pool::VIRTUAL_CATEGORY_OWN, $pool[0]->categories);
        $this->assertTrue($pool[0]->isown);
        $this->assertSame('own-0', $pool[0]->externalid);
    }

    public function test_parse_own_questions_handles_crlf_and_cr(): void {
        $instance = $this->fake_instance(['ownquestions' => "A\r\nB\rC"]);
        $pool = activity_pool::parse_own_questions($instance);
        $this->assertCount(3, $pool);
    }

    public function test_mode_only_own_returns_only_own(): void {
        $this->resetAfterTest();
        $instance = $this->fake_instance([
            'ownquestions'     => "eins\nzwei",
            'ownquestionsmode' => 1,
        ]);
        $pool = activity_pool::build_pool($instance, 'checkin', ['de']);
        $this->assertCount(2, $pool);
        foreach ($pool as $item) {
            $this->assertTrue($item->isown);
        }
    }

    public function test_mode_none_ignores_own_questions_field(): void {
        $this->resetAfterTest();
        $instance = $this->fake_instance([
            'ownquestions'     => "eins\nzwei",
            'ownquestionsmode' => 2,
        ]);
        $pool = activity_pool::build_pool($instance, 'checkin', ['de']);
        // No bundle content in the test DB, own questions explicitly off →
        // pool is empty and pick_random returns null.
        $this->assertSame([], array_values(array_filter(
            $pool,
            static fn($q) => !empty($q->isown)
        )));
    }

    public function test_resolve_navigation_initial_load_resets_history(): void {
        global $SESSION;
        $this->resetAfterTest();
        $instance = $this->fake_instance([
            'ownquestions'     => 'only-one',
            'ownquestionsmode' => 1,
        ]);
        $SESSION->elediacheckin_history = [999 => ['stale-a', 'stale-b']];
        $result = activity_pool::resolve_navigation(
            $instance, 999, 'checkin', ['de'], '', false, false
        );
        $this->assertNotNull($result['question']);
        $this->assertFalse($result['hasprev']);
    }

    public function test_resolve_navigation_next_click_grows_history(): void {
        global $SESSION;
        $this->resetAfterTest();
        $instance = $this->fake_instance([
            'ownquestions'     => "a\nb\nc\nd",
            'ownquestionsmode' => 1,
        ]);
        $SESSION->elediacheckin_history = [];
        // First load.
        activity_pool::resolve_navigation($instance, 42, 'checkin', ['de'], '', false, false);
        // Explicit Next click should push onto history → hasprev becomes true.
        $result = activity_pool::resolve_navigation(
            $instance, 42, 'checkin', ['de'], '', false, true
        );
        $this->assertTrue($result['hasprev']);
    }

    public function test_resolve_navigation_back_pops_to_previous(): void {
        global $SESSION;
        $this->resetAfterTest();
        $instance = $this->fake_instance([
            'ownquestions'     => "a\nb\nc\nd\ne",
            'ownquestionsmode' => 1,
        ]);
        $SESSION->elediacheckin_history = [];
        activity_pool::resolve_navigation($instance, 7, 'checkin', ['de'], '', false, false);
        activity_pool::resolve_navigation($instance, 7, 'checkin', ['de'], '', false, true);
        // Now history has 2 entries — back should return the older one and
        // hide the prev button (history shrinks to 1).
        $result = activity_pool::resolve_navigation(
            $instance, 7, 'checkin', ['de'], '', true, false
        );
        $this->assertNotNull($result['question']);
        $this->assertFalse($result['hasprev']);
    }
}
