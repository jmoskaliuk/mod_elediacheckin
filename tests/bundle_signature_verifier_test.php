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
 * Unit tests for the ED25519 bundle signature verifier.
 *
 * @package    mod_elediacheckin
 * @category   test
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin;

use mod_elediacheckin\content\bundle_signature_verifier;

/**
 * @covers \mod_elediacheckin\content\bundle_signature_verifier
 */
final class bundle_signature_verifier_test extends \basic_testcase {

    /** @var string */
    private string $secret;

    /** @var string */
    private string $publichex;

    protected function setUp(): void {
        parent::setUp();
        if (!function_exists('sodium_crypto_sign_keypair')) {
            $this->markTestSkipped('libsodium extension not available.');
        }
        $keypair = sodium_crypto_sign_keypair();
        $this->secret = sodium_crypto_sign_secretkey($keypair);
        $this->publichex = bin2hex(sodium_crypto_sign_publickey($keypair));
    }

    public function test_verify_accepts_valid_signature(): void {
        $payload = 'some-raw-bundle-bytes';
        $signature = sodium_crypto_sign_detached($payload, $this->secret);
        $this->assertTrue(
            bundle_signature_verifier::verify($payload, $signature, $this->publichex)
        );
    }

    public function test_verify_rejects_tampered_payload(): void {
        $signature = sodium_crypto_sign_detached('original', $this->secret);
        $this->assertFalse(
            bundle_signature_verifier::verify('tampered', $signature, $this->publichex)
        );
    }

    public function test_verify_rejects_wrong_public_key(): void {
        $payload = 'bundle';
        $signature = sodium_crypto_sign_detached($payload, $this->secret);
        $otherkey = bin2hex(sodium_crypto_sign_publickey(sodium_crypto_sign_keypair()));
        $this->assertFalse(
            bundle_signature_verifier::verify($payload, $signature, $otherkey)
        );
    }

    public function test_verify_rejects_signature_with_wrong_length(): void {
        $this->assertFalse(
            bundle_signature_verifier::verify('bundle', 'too-short', $this->publichex)
        );
    }

    public function test_verify_rejects_malformed_public_key(): void {
        $signature = sodium_crypto_sign_detached('x', $this->secret);
        $this->assertFalse(
            bundle_signature_verifier::verify('x', $signature, 'not-hex')
        );
    }

    public function test_decode_signature_accepts_base64(): void {
        $raw = sodium_crypto_sign_detached('payload', $this->secret);
        $encoded = base64_encode($raw);
        $this->assertSame($raw, bundle_signature_verifier::decode_signature($encoded));
    }

    public function test_decode_signature_accepts_hex(): void {
        $raw = sodium_crypto_sign_detached('payload', $this->secret);
        $encoded = bin2hex($raw);
        $this->assertSame($raw, bundle_signature_verifier::decode_signature($encoded));
    }

    public function test_decode_signature_returns_null_for_garbage(): void {
        $this->assertNull(bundle_signature_verifier::decode_signature(''));
        $this->assertNull(bundle_signature_verifier::decode_signature('@@@'));
    }

    public function test_has_production_key_is_false_for_demo_key(): void {
        // The shipped constant is still the demo key, so this should be false.
        $this->assertFalse(bundle_signature_verifier::has_production_key());
    }
}
