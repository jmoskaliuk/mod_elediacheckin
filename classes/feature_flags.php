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
 * Build-time feature flags for mod_elediacheckin.
 *
 * These are plain PHP constants — no Moodle settings, no DB lookups, no
 * runtime toggles. The values live in source code so that a single sed
 * step during the release-build pipeline can flip them off without any
 * server-side configuration drift.
 *
 * The premium/license-server pathway is fully implemented and unit-tested
 * in the dev tree, but the first Plugins-Directory-Submission ZIP ships
 * with PREMIUM_ENABLED = false: we want to iterate on the premium product
 * without exposing half-finished license-server settings to users who
 * install the stock build. Once licenses.eledia.de is live and the billing
 * flow exists, the release build simply drops the sed step and the option
 * appears.
 *
 * IMPORTANT: do NOT wrap the premium classes themselves (verifier,
 * eledia_premium_content_source) in the flag. They stay loadable at all
 * times so unit tests can exercise them regardless of the release build.
 * The flag only controls UI exposure + registry registration.
 *
 * To toggle for a release:
 *     sed -i '' 's/PREMIUM_ENABLED = true/PREMIUM_ENABLED = false/' \
 *         classes/feature_flags.php
 * …or run `./bin/build-release.sh` once we have it.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin;

/**
 * Single source of truth for compile-time feature toggles.
 */
final class feature_flags {
    /**
     * When true, the "eLeDia Premium" content source is registered and its
     * admin settings are visible. Flipped to false in release builds until
     * the premium backend is productised.
     */
    public const PREMIUM_ENABLED = false;

    /**
     * Convenience wrapper — kept as a method so call sites can be found via grep without matching the constant definition itself.
     *
     * @return bool True if the premium content source is enabled.
     */
    public static function premium_enabled(): bool {
        return self::PREMIUM_ENABLED;
    }
}
