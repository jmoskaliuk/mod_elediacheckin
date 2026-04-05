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
 * Activity-level question pool helper.
 *
 * Bundles the bundle-sourced questions (via question_provider) with the
 * optional per-activity "Eigene Fragen" (one line = one card), then picks
 * a random card from the merged pool. See concept doc §10.13.
 *
 * Kept out of question_provider on purpose: the provider stays fachlich
 * pure ("bundle-sourced content only"), and the mixing logic lives in one
 * place — here — so view.php and present.php do not need to duplicate it.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\local\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Builds and samples the combined question pool for one activity.
 */
final class activity_pool {

    /** Virtual category marker used for teacher-authored own questions. */
    public const VIRTUAL_CATEGORY_OWN = 'eigene';

    /**
     * Returns a random question for the given activity and active ziel,
     * mixing bundle questions and the teacher's own questions additively.
     *
     * Language fallback:
     *  - Bundle questions go through the same fallback chain used before:
     *    try each candidate in order, first non-empty hit wins.
     *  - Own questions are language-agnostic and always joined onto the
     *    winning bundle list. If no bundle candidate yielded hits, the
     *    pool consists of own questions only — they still show up.
     *
     * @param \stdClass $instance  Row from the {elediacheckin} table.
     * @param string    $activeziel Single ziel key to draw for.
     * @param string[]  $langcandidates Ordered list of lang codes, null for "any".
     * @return \stdClass|null Pool element or null if the merged pool is empty.
     */
    public static function pick_random(
        \stdClass $instance,
        string $activeziel,
        array $langcandidates
    ): ?\stdClass {
        $pool = self::build_pool($instance, $activeziel, $langcandidates);
        if (empty($pool)) {
            return null;
        }
        return $pool[array_rand($pool)];
    }

    /**
     * Looks up a specific question from the merged pool by externalid.
     *
     * Used when the block preview passes the currently-displayed question
     * into the launch URL (view.php / present.php) via ?q=<externalid>, so
     * clicking "Open Check-in" or "Open as popup" shows the same card the
     * user was just looking at instead of rolling a new one. Falls back to
     * null if the id is unknown in the current pool — the caller then rolls
     * a random, which is the correct behaviour e.g. after the bundle was
     * resynced between block render and click.
     *
     * @param \stdClass $instance
     * @param string $externalid  The externalid to match (e.g. 'checkin-0001' or 'own-3').
     * @param string $activeziel  The ziel to build the pool for (bundle filter).
     * @param string[] $langcandidates
     * @return \stdClass|null
     */
    public static function pick_by_externalid(
        \stdClass $instance,
        string $externalid,
        string $activeziel,
        array $langcandidates
    ): ?\stdClass {
        if ($externalid === '') {
            return null;
        }
        $pool = self::build_pool($instance, $activeziel, $langcandidates);
        foreach ($pool as $item) {
            if (((string) ($item->externalid ?? '')) === $externalid) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Resolves the next question to display including one-step history
     * tracking for the "Zur vorherigen Frage"-Button.
     *
     * Shared helper for view.php and present.php so the navigation state
     * machine lives in one place. History is persisted in $SESSION under
     * `elediacheckin_history[$cmid]` as a bounded list of at most 2
     * externalids, newest at the tail.
     *
     * Semantics:
     *  - If `$pinnedexternalid` is non-empty, try to lock onto that card
     *    (used by the block launcher via `?q=`). History is reset to just
     *    that entry so the user has a clean starting point and the prev
     *    button stays hidden.
     *  - Else if `$goback` is true AND history has at least 2 entries,
     *    pop the current top and return the previous card. History
     *    shrinks back to size 1 — the button disappears until the user
     *    draws a new card, which avoids ping-pong between two cards.
     *  - Else if `$isnext` is true (explicit „Nächste Frage"-click), draw
     *    a new random and push it on the history stack, keeping at most
     *    2 entries (shift the oldest out). This is the only path that
     *    makes the prev button appear.
     *  - Else (fresh page load, e.g. direct navigation to the activity),
     *    draw a new random and *reset* the history to just that entry.
     *    Per-viewing-session state should not leak across navigations —
     *    Johannes wanted the button to appear only once the user has
     *    actively moved forward at least once.
     *
     * @param \stdClass $instance        Row from the {elediacheckin} table.
     * @param int       $cmid            Course module id (session namespace).
     * @param string    $activeziel      Active ziel to build the pool for.
     * @param string[]  $langcandidates  Ordered language fallback chain.
     * @param string    $pinnedexternalid  Externalid to lock onto, or ''.
     * @param bool      $goback          True = handle "back" button click.
     * @param bool      $isnext          True = handle "next" button click.
     * @return array{question: ?\stdClass, hasprev: bool}
     */
    public static function resolve_navigation(
        \stdClass $instance,
        int $cmid,
        string $activeziel,
        array $langcandidates,
        string $pinnedexternalid,
        bool $goback,
        bool $isnext = false
    ): array {
        global $SESSION;

        $prop = 'elediacheckin_history';
        if (!isset($SESSION->{$prop}) || !is_array($SESSION->{$prop})) {
            $SESSION->{$prop} = [];
        }
        $all = $SESSION->{$prop};
        $history = isset($all[$cmid]) && is_array($all[$cmid]) ? $all[$cmid] : [];

        $question = null;

        if ($pinnedexternalid !== '') {
            // Pinned from block launcher: reset the history stack.
            $question = self::pick_by_externalid(
                $instance, $pinnedexternalid, $activeziel, $langcandidates
            );
            if (!$question) {
                // Unknown externalid (e.g., bundle resynced). Fall back to random.
                $question = self::pick_random($instance, $activeziel, $langcandidates);
            }
            $history = $question ? [(string) $question->externalid] : [];
        } else if ($goback && count($history) >= 2) {
            // Pop current, reveal previous.
            array_pop($history);
            $topid = (string) end($history);
            $question = self::pick_by_externalid(
                $instance, $topid, $activeziel, $langcandidates
            );
            if (!$question) {
                // Previous card disappeared (bundle resynced?). Draw fresh.
                $question = self::pick_random($instance, $activeziel, $langcandidates);
                $history = $question ? [(string) $question->externalid] : [];
            }
        } else if ($isnext) {
            // Explicit "Next"-click: draw a random and push on top.
            $question = self::pick_random($instance, $activeziel, $langcandidates);
            if ($question) {
                $history[] = (string) $question->externalid;
                while (count($history) > 2) {
                    array_shift($history);
                }
            }
        } else {
            // Fresh page load / first entry: draw a random and *reset*
            // the history stack. Keeps the prev button hidden until the
            // user has actively clicked "Next" at least once in this
            // page-viewing session.
            $question = self::pick_random($instance, $activeziel, $langcandidates);
            $history = $question ? [(string) $question->externalid] : [];
        }

        $all[$cmid] = $history;
        $SESSION->{$prop} = $all;

        return [
            'question' => $question,
            'hasprev'  => count($history) >= 2,
        ];
    }

    /**
     * Builds the merged pool without sampling — exposed for tests and
     * potential future "avoid repeat" logic that needs the full list.
     *
     * @param \stdClass $instance
     * @param string $activeziel
     * @param string[] $langcandidates
     * @return \stdClass[]
     */
    public static function build_pool(
        \stdClass $instance,
        string $activeziel,
        array $langcandidates
    ): array {
        // „Eigene Fragen"-Modus (Konzept §10.15 + §10.19). Tri-state:
        //   0 = mixed     — Bundle + eigene additiv (Default).
        //   1 = only_own  — NUR eigene Fragen, Bundle komplett ueberspringen.
        //   2 = none      — eigene Fragen komplett ignorieren, auch wenn das
        //                   Textfeld gefuellt ist (nuetzlich, wenn Teacher
        //                   eine Aktivitaet temporaer „aus dem Mix" nehmen
        //                   moechte, ohne die eingetragenen Fragen zu loeschen).
        // Fallback auf 0, falls das alte Feld `onlyownquestions` noch in
        // der DB steckt (wird durch Upgrade-Step 2026040524 umbenannt, aber
        // defensive Programmierung schadet nicht).
        $mode = isset($instance->ownquestionsmode)
            ? (int) $instance->ownquestionsmode
            : (isset($instance->onlyownquestions) ? (int) $instance->onlyownquestions : 0);

        $own = $mode === 2 ? [] : self::parse_own_questions($instance);

        if ($mode === 1) {
            return $own;
        }

        $provider = new question_provider();

        $bundle = [];
        $candidates = array_values(array_unique(array_filter(
            $langcandidates,
            static fn($v) => $v !== ''
        ), SORT_REGULAR));
        // Always keep a final "any language" fallback.
        if (!in_array(null, $candidates, true)) {
            $candidates[] = null;
        }

        foreach ($candidates as $lang) {
            $hits = $provider->get_questions_by_filter([
                'ziele'      => [$activeziel],
                'categories' => $instance->categories,
                'zielgruppe' => $instance->zielgruppe ?? null,
                'kontext'    => $instance->kontext ?? null,
                'lang'       => $lang,
            ]);
            if (!empty($hits)) {
                $bundle = $hits;
                break;
            }
        }

        return array_merge($bundle, $own);
    }

    /**
     * Parses the ownquestions TEXT column into stdClass records that look
     * like {elediacheckin_question} rows — but with id=0 and the virtual
     * category marker. The shape matches what view.php/present.php read
     * off a question object (frage, antwort, hasanswer, lang).
     *
     * One line of the textarea becomes one card. Empty lines and lines
     * consisting only of whitespace are skipped. No markdown, no
     * HTML-escaping here — format_text() in the view applies FORMAT_PLAIN
     * on this content (see view.php) to keep teacher input safe.
     *
     * @param \stdClass $instance
     * @return \stdClass[]
     */
    public static function parse_own_questions(\stdClass $instance): array {
        $raw = (string) ($instance->ownquestions ?? '');
        if ($raw === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $records = [];
        $idx = 0;
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            $r = new \stdClass();
            $r->id            = 0;
            $r->externalid    = 'own-' . $idx;
            $r->ziel          = '';
            $r->categories    = self::VIRTUAL_CATEGORY_OWN;
            $r->frage         = $trimmed;
            $r->antwort       = null;
            $r->hasanswer     = 0;
            $r->lang          = '';
            $r->author        = null;
            $r->quelle        = null;
            $r->license       = '';
            $r->qversion      = '1';
            $r->qstatus       = 'published';
            $r->link          = null;
            $r->media         = null;
            $r->bundleid      = '';
            $r->bundleversion = '';
            $r->zielgruppe    = '';
            $r->kontext       = '';
            $r->extcreated    = null;
            $r->extmodified   = null;
            $r->isown         = true;

            $records[] = $r;
            $idx++;
        }
        return $records;
    }
}
