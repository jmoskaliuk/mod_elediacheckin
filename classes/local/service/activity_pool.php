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
     * Upper bound on the in-session history stack. Caps memory so a
     * learner who clicks "Weiter" several thousand times does not blow
     * up their PHP session record. The oldest entry is shifted out when
     * the stack grows past this value — learners can still navigate
     * backwards through the last HISTORY_MAX cards, which is plenty for
     * any realistic check-in session (typical pool sizes are 20–200).
     */
    public const HISTORY_MAX = 500;

    /** Exhausted-pool behaviour: restart and re-shuffle. */
    public const EXHAUSTED_RESTART = 'restart';

    /** Exhausted-pool behaviour: stop and show empty state. */
    public const EXHAUSTED_EMPTY   = 'empty';

    /**
     * Resolves the next question to display including full in-session
     * history tracking for the "Zurück"-Button and pool-exhausted
     * handling.
     *
     * Shared helper for view.php and present.php so the navigation
     * state machine lives in one place. Session state is persisted
     * under `elediacheckin_nav[$cmid]` as a small record:
     *
     *   ['history' => [extid, extid, …], // newest at tail
     *    'pos'     => int,               // current index inside history
     *    'seen'    => [extid => true],   // everything drawn this session
     *    'exhausted' => bool]
     *
     * Semantics:
     *  - `$pinnedexternalid` non-empty → lock onto that card, reset
     *    history to just that entry. Used by the block launcher.
     *  - `$goback` → decrement `pos` (clamped at 0). Returns the card
     *    at the new position from the history. No new draw happens.
     *    „Previous" button stays hidden only when `pos === 0`.
     *  - `$isnext` → if `pos` is not at the tail (user had stepped
     *    back and now wants to go forward), just increment and return
     *    the existing history entry. If `pos` is at the tail, draw a
     *    *fresh* card (excluding everything in `seen`), append, move
     *    `pos` forward. If no fresh card is available, apply the
     *    configured exhausted behaviour:
     *      • 'restart' → clear `seen`, draw again; the newly drawn
     *        card is pushed onto history as a normal new entry.
     *      • 'empty'   → return no question and set `exhausted=true`.
     *  - Fresh page load (neither flag) → draw a random card and
     *    *reset* the whole navigation state for this cmid. This makes
     *    the Previous-button stay hidden until the learner has
     *    actively clicked "Weiter" at least once in this viewing
     *    session, which matches Johannes' product expectation.
     *
     * @param \stdClass $instance        Row from the {elediacheckin} table.
     * @param int       $cmid            Course module id (session namespace).
     * @param string    $activeziel      Active ziel to build the pool for.
     * @param string[]  $langcandidates  Ordered language fallback chain.
     * @param string    $pinnedexternalid  Externalid to lock onto, or ''.
     * @param bool      $goback          True = handle "back" button click.
     * @param bool      $isnext          True = handle "next" button click.
     * @return array{question: ?\stdClass, hasprev: bool, exhausted: bool}
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

        $prop = 'elediacheckin_nav';
        if (!isset($SESSION->{$prop}) || !is_array($SESSION->{$prop})) {
            $SESSION->{$prop} = [];
        }
        $all = $SESSION->{$prop};
        $state = (isset($all[$cmid]) && is_array($all[$cmid])) ? $all[$cmid] : [];
        $history = isset($state['history']) && is_array($state['history']) ? $state['history'] : [];
        $pos     = isset($state['pos']) ? (int) $state['pos'] : 0;
        $seen    = isset($state['seen']) && is_array($state['seen']) ? $state['seen'] : [];

        $exhaustedmode = self::normalise_exhausted_behavior($instance);
        $question = null;
        $exhausted = false;

        if ($pinnedexternalid !== '') {
            // Pinned from block launcher: clean slate.
            $question = self::pick_by_externalid(
                $instance,
                $pinnedexternalid,
                $activeziel,
                $langcandidates
            );
            if (!$question) {
                $question = self::pick_random(
                    $instance,
                    $activeziel,
                    $langcandidates
                );
            }
            if ($question) {
                $extid = (string) $question->externalid;
                $history = [$extid];
                $pos = 0;
                $seen = [$extid => true];
            } else {
                $history = [];
                $pos = 0;
                $seen = [];
            }
        } else if ($goback) {
            // Step backwards in the in-session history.
            if ($pos > 0) {
                $pos--;
            }
            if (!empty($history[$pos])) {
                $question = self::pick_by_externalid(
                    $instance,
                    (string) $history[$pos],
                    $activeziel,
                    $langcandidates
                );
            }
            if (!$question) {
                // Previous card disappeared (bundle resynced?). Draw fresh.
                $question = self::pick_random_excluding(
                    $instance,
                    $activeziel,
                    $langcandidates,
                    $seen
                );
                if ($question) {
                    $extid = (string) $question->externalid;
                    $history = [$extid];
                    $pos = 0;
                    $seen[$extid] = true;
                }
            }
        } else if ($isnext) {
            // Forward: either walk ahead in the stored history (if the
            // Learner had stepped back) or draw a fresh card.
            if ($pos + 1 < count($history)) {
                $pos++;
                $question = self::pick_by_externalid(
                    $instance,
                    (string) $history[$pos],
                    $activeziel,
                    $langcandidates
                );
            }
            if (!$question) {
                $question = self::pick_random_excluding(
                    $instance,
                    $activeziel,
                    $langcandidates,
                    $seen
                );
                if (!$question) {
                    // Pool exhausted.
                    if ($exhaustedmode === self::EXHAUSTED_RESTART) {
                        $seen = [];
                        $question = self::pick_random(
                            $instance,
                            $activeziel,
                            $langcandidates
                        );
                    } else {
                        $exhausted = true;
                    }
                }
                if ($question) {
                    $extid = (string) $question->externalid;
                    // Truncate history forward of current pos (we branch
                    // Off the existing trail) then append.
                    if ($pos + 1 < count($history)) {
                        $history = array_slice($history, 0, $pos + 1);
                    }
                    $history[] = $extid;
                    $pos = count($history) - 1;
                    $seen[$extid] = true;
                    // Cap memory.
                    if (count($history) > self::HISTORY_MAX) {
                        $drop = count($history) - self::HISTORY_MAX;
                        $history = array_slice($history, $drop);
                        $pos = count($history) - 1;
                    }
                }
            }
        } else {
            // Fresh page load (no ?q=, ?next=, ?prev=). If the session
            // Already holds a question for this cmid, keep showing it —
            // The card should stay stable until the user explicitly
            // Clicks "Weiter". Only reset the history stack so the
            // Previous-button stays hidden until Weiter is clicked.
            $reused = false;
            if (!empty($history) && isset($history[$pos])) {
                $question = self::pick_by_externalid(
                    $instance,
                    (string) $history[$pos],
                    $activeziel,
                    $langcandidates
                );
                if ($question) {
                    // Keep the same card; just flatten history to one entry
                    // So Previous is hidden.
                    $extid = (string) $question->externalid;
                    $history = [$extid];
                    $pos = 0;
                    $seen = [$extid => true];
                    $reused = true;
                }
            }
            if (!$reused) {
                $question = self::pick_random(
                    $instance,
                    $activeziel,
                    $langcandidates
                );
                if ($question) {
                    $extid = (string) $question->externalid;
                    $history = [$extid];
                    $pos = 0;
                    $seen = [$extid => true];
                } else {
                    $history = [];
                    $pos = 0;
                    $seen = [];
                }
            }
        }

        $all[$cmid] = [
            'history'   => $history,
            'pos'       => $pos,
            'seen'      => $seen,
            'exhausted' => $exhausted,
        ];
        $SESSION->{$prop} = $all;

        return [
            'question'  => $question,
            'hasprev'   => $pos > 0,
            'exhausted' => $exhausted,
        ];
    }

    /**
     * Like `pick_random()` but filters out every question whose externalid already sits in `$seen`.
     *
     * Returns null when the remaining pool is empty. The caller is responsible for applying the
     * configured exhausted-behavior (restart vs. empty card).
     *
     * @param \stdClass $instance Row from the {elediacheckin} table.
     * @param string $activeziel Single ziel key to draw for.
     * @param string[] $langcandidates Ordered list of lang codes.
     * @param array<string,bool> $seen Map of externalid → true for seen questions.
     * @return \stdClass|null The randomly selected question, or null if pool is empty.
     */
    public static function pick_random_excluding(
        \stdClass $instance,
        string $activeziel,
        array $langcandidates,
        array $seen
    ): ?\stdClass {
        $pool = self::build_pool($instance, $activeziel, $langcandidates);
        if (empty($pool)) {
            return null;
        }
        if (empty($seen)) {
            return $pool[array_rand($pool)];
        }
        $remaining = [];
        foreach ($pool as $row) {
            $extid = (string) ($row->externalid ?? '');
            if ($extid === '' || !isset($seen[$extid])) {
                $remaining[] = $row;
            }
        }
        if (empty($remaining)) {
            return null;
        }
        return $remaining[array_rand($remaining)];
    }

    /**
     * Reads and normalises the instance-level exhausted-behavior selector.
     *
     * Falls back to the documented default 'restart' on unknown or empty values so an upgrade
     * from an older schema never produces a broken runtime state.
     *
     * @param \stdClass $instance The activity instance.
     * @return string One of self::EXHAUSTED_RESTART | self::EXHAUSTED_EMPTY.
     */
    public static function normalise_exhausted_behavior(\stdClass $instance): string {
        $raw = (string) ($instance->exhaustedbehavior ?? self::EXHAUSTED_RESTART);
        if ($raw !== self::EXHAUSTED_EMPTY && $raw !== self::EXHAUSTED_RESTART) {
            return self::EXHAUSTED_RESTART;
        }
        return $raw;
    }

    /**
     * Builds the merged pool without sampling.
     *
     * Exposed for tests and potential future "avoid repeat" logic that needs the full list.
     *
     * @param \stdClass $instance The activity instance.
     * @param string $activeziel Single ziel key to filter by.
     * @param string[] $langcandidates Ordered language fallback chain.
     * @return \stdClass[] Array of question records.
     */
    public static function build_pool(
        \stdClass $instance,
        string $activeziel,
        array $langcandidates
    ): array {
        /*
         * "Eigene Fragen"-Modus (Konzept §10.15 + §10.19). Tri-state:
         *   0 = mixed     — Bundle + eigene additiv (Default).
         *   1 = only_own  — NUR eigene Fragen, Bundle komplett ueberspringen.
         *   2 = none      — Eigene Fragen komplett ignorieren, auch wenn das
         *                   Textfeld gefuellt ist (nuetzlich, wenn Teacher
         *                   Eine Aktivitaet temporaer "aus dem Mix" nehmen
         *                   Moechte, ohne die eingetragenen Fragen zu loeschen).
         *
         * Fallback auf 0, falls das alte Feld `onlyownquestions` noch in
         * Der DB steckt (wird durch Upgrade-Step 2026040524 umbenannt, aber
         * Defensive Programmierung schadet nicht.
         */
        $mode = isset($instance->ownquestionsmode)
            ? (int) $instance->ownquestionsmode
            : (isset($instance->onlyownquestions) ? (int) $instance->onlyownquestions : 0);

        $own = $mode === 2 ? [] : self::parse_own_questions($instance);

        if ($mode === 1) {
            return $own;
        }

        $provider = new question_provider();

        $bundle = [];
        $candidates = array_values(
            array_unique(
                array_filter(
                    $langcandidates,
                    static fn($v) => $v !== ''
                ),
                SORT_REGULAR
            )
        );
        // Always keep a final "any language" fallback.
        if (!in_array(null, $candidates, true)) {
            $candidates[] = null;
        }

        foreach ($candidates as $lang) {
            $hits = $provider->get_questions_by_filter(
                [
                    'ziele' => [$activeziel],
                    'categories' => $instance->categories,
                    'zielgruppe' => $instance->zielgruppe ?? null,
                    'kontext' => $instance->kontext ?? null,
                    'lang' => $lang,
                ]
            );
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
