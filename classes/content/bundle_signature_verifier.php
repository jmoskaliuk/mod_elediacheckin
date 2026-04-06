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
 * ED25519 bundle signature verification.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\content;

/**
 * Verifies detached ED25519 signatures on content bundles.
 *
 * The Phase-2 licensing model assumes that:
 *  - eLeDia signs every published bundle with a private ED25519 key that
 *    lives ONLY in the GitHub Actions secret of the premium content repo
 *    (never on the license server itself).
 *  - The matching public key is hardcoded into this class (see
 *    ELEDIA_PREMIUM_PUBLIC_KEY). Rotating it requires a plugin release,
 *    which is intentional: it's the root of trust.
 *  - The license server hands out the bundle URL plus the matching
 *    detached-signature URL via the /verify endpoint. The plugin
 *    downloads both, verifies the signature over the raw bundle bytes,
 *    and only then parses + stages the JSON.
 *
 * This means a fully compromised license server cannot feed the plugin
 * fake content: without the private key, no forged bundle verifies.
 *
 * We use PHP's bundled libsodium (`sodium_crypto_sign_verify_detached`),
 * available on PHP 7.2+ as a first-class extension. Moodle 5.x requires
 * PHP 8.1+, so this is always present — no composer dependency.
 */
final class bundle_signature_verifier {
    /**
     * Public key of the eLeDia content-signing key (ED25519, 32 bytes).
     *
     * This is a DEMO key paired with the local license server in
     * /license_server/data/keys/demo.secret.key. For a real production
     * deployment, this constant MUST be replaced before cutting a
     * release build.
     *
     * Hex-encoded so the source file stays printable. The verifier
     * decodes it on first use.
     *
     * @var string
     */
    public const ELEDIA_PREMIUM_PUBLIC_KEY_HEX =
        // Demo key — matches license_server/data/keys/demo.public.key.
        // Replace before cutting a production release.
        '35154cbd66ea05bdf504224db4764b06d31b87d5a48460d2a657aa34e8d3e2c0';

    /**
     * Verify a detached signature over the given payload.
     *
     * @param string $payload          The raw bundle bytes (NOT the decoded JSON array).
     * @param string $signaturebinary  The raw 64-byte signature (NOT hex, NOT base64).
     * @param string|null $publickeyhex Optional override for tests.
     * @return bool True if the signature is valid.
     */
    public static function verify(string $payload, string $signaturebinary, ?string $publickeyhex = null): bool {
        if (!function_exists('sodium_crypto_sign_verify_detached')) {
            // libsodium is part of core PHP since 7.2; Moodle 5 requires 8.1+.
            // If we still end up here the host is misconfigured — fail closed.
            return false;
        }

        $keyhex = $publickeyhex ?? self::ELEDIA_PREMIUM_PUBLIC_KEY_HEX;
        if (!self::is_valid_hex_key($keyhex)) {
            return false;
        }
        $keybinary = hex2bin($keyhex);
        if ($keybinary === false || strlen($keybinary) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            return false;
        }

        if (strlen($signaturebinary) !== SODIUM_CRYPTO_SIGN_BYTES) {
            return false;
        }

        try {
            return sodium_crypto_sign_verify_detached($signaturebinary, $payload, $keybinary);
        } catch (\SodiumException $e) {
            return false;
        }
    }

    /**
     * Accepts a base64- or hex-encoded signature string and returns the raw binary form, or null if malformed.
     *
     * @param string $encoded The encoded signature string.
     * @return string|null The decoded binary signature, or null if malformed.
     */
    public static function decode_signature(string $encoded): ?string {
        $encoded = trim($encoded);
        if ($encoded === '') {
            return null;
        }

        // Try base64 (what the license server returns by default).
        if (preg_match('#^[A-Za-z0-9+/=]+$#', $encoded) && strlen($encoded) >= 86) {
            $decoded = base64_decode($encoded, true);
            if ($decoded !== false && strlen($decoded) === SODIUM_CRYPTO_SIGN_BYTES) {
                return $decoded;
            }
        }

        // Fallback: hex.
        if (preg_match('#^[0-9a-fA-F]+$#', $encoded) && strlen($encoded) === SODIUM_CRYPTO_SIGN_BYTES * 2) {
            $decoded = hex2bin($encoded);
            return $decoded !== false ? $decoded : null;
        }

        return null;
    }

    /**
     * Check if a string is a valid 64-character hex string.
     *
     * @param string $hex The hex string to validate.
     * @return bool True if the string is valid hex.
     */
    private static function is_valid_hex_key(string $hex): bool {
        return preg_match('#^[0-9a-fA-F]{64}$#', $hex) === 1;
    }

    /**
     * Exposes whether the hardcoded public key has been filled in. Used by
     * the admin dashboard to warn when a dev build still ships the all-zero
     * placeholder.
     *
     * @return bool
     */
    public static function has_production_key(): bool {
        // All-zero = uninitialised placeholder.
        // Demo key ships with dev builds but is explicitly not production.
        return self::ELEDIA_PREMIUM_PUBLIC_KEY_HEX
                !== '0000000000000000000000000000000000000000000000000000000000000000'
            && self::ELEDIA_PREMIUM_PUBLIC_KEY_HEX
                !== '35154cbd66ea05bdf504224db4764b06d31b87d5a48460d2a657aa34e8d3e2c0';
    }
}
