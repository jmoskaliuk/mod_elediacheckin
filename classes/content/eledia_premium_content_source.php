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
 * eLeDia Premium content source (Phase 2 — licensed / signed bundles).
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\content;

use mod_elediacheckin\local\service\config_service;

/**
 * Fetches signed premium content from the eLeDia license server.
 *
 * Flow:
 *   1. POST /verify  with {license_key, site_hash, plugin_version}
 *        → returns {token, bundle_version, bundle_url, signature_url}
 *   2. GET bundle_url  (Bearer: token)            → raw JSON bytes
 *   3. GET signature_url (Bearer: token)          → ED25519 signature
 *   4. verify signature over raw bundle bytes against the hardcoded
 *      ELEDIA_PREMIUM_PUBLIC_KEY_HEX (bundle_signature_verifier).
 *   5. json_decode + schema_validator → content_bundle
 *
 * Step 4 is the hinge of the security model: a fully compromised license
 * server can hand out arbitrary URLs, but without the private key nothing
 * downstream verifies. See Konzept § 6 (Sicherheitsmodell).
 *
 * Configuration (plugin-level, see settings.php):
 *  - licenseserverurl : Base URL of the license server, no trailing slash.
 *                       Default points at the local MVP bundled in
 *                       /license_server/ at the top of the workspace.
 *  - licensekey       : UUID-formatted key, customer-specific.
 *
 * The class is resilient to partial failures — any single HTTP or
 * validation issue produces a content_source_exception with a translatable
 * string id so the sync_service can log and fall back cleanly.
 */
final class eledia_premium_content_source implements content_source_interface {
    /** HTTP timeout for each call (verify, bundle, signature) in seconds. */
    private const TIMEOUT_SECONDS = 30;

    /** Verbatim current plugin component, reported to the license server. */
    private const COMPONENT = 'mod_elediacheckin';

    /** @var config_service */
    private $config;

    /**
     * Constructor.
     *
     * @param config_service|null $config Injectable config service for tests.
     */
    public function __construct(?config_service $config = null) {
        $this->config = $config ?? new config_service();
    }

    /**
     * Returns the unique identifier for this content source.
     *
     * @return string The source identifier.
     */
    public function get_id(): string {
        return 'eledia_premium';
    }

    /**
     * Returns a human-readable name for this content source.
     *
     * @return string The human-readable display name.
     */
    public function get_display_name(): string {
        return get_string('contentsource_eledia', 'elediacheckin');
    }

    /**
     * Probes connectivity to the license server without downloading the bundle.
     *
     * @return bool True if the license server is reachable.
     */
    public function test_connection(): bool {
        try {
            $this->verify_license();
            return true;
        } catch (content_source_exception $e) {
            return false;
        }
    }

    /**
     * Fetches and validates a signed premium bundle from the eLeDia license server.
     *
     * @return content_bundle The validated content bundle.
     * @throws content_source_exception If verification, signature, or validation fails.
     */
    public function fetch_bundle(): content_bundle {
        $ticket = $this->verify_license();

        $bundleraw = $this->http_get_authenticated(
            $ticket['bundle_url'],
            $ticket['token'],
            'accept: application/json'
        );

        $signatureencoded = trim($this->http_get_authenticated(
            $ticket['signature_url'],
            $ticket['token'],
            'accept: text/plain'
        ));

        $signaturebinary = bundle_signature_verifier::decode_signature($signatureencoded);
        if ($signaturebinary === null) {
            throw new content_source_exception(
                'contenterror_eledia_sigmalformed',
                'Signature payload could not be decoded (expected base64 or hex).'
            );
        }

        if (!bundle_signature_verifier::verify($bundleraw, $signaturebinary)) {
            // Never fall through on a bad signature — this is the root of trust.
            throw new content_source_exception(
                'contenterror_eledia_sigfailed',
                'ED25519 signature verification failed for bundle v' . $ticket['bundle_version']
            );
        }

        $decoded = json_decode($bundleraw, true);
        if (!is_array($decoded)) {
            throw new content_source_exception(
                'contenterror_eledia_parse',
                'json_decode error: ' . json_last_error_msg()
            );
        }

        $validator = new schema_validator();
        if (!$validator->validate($decoded)) {
            throw new content_source_exception(
                'contenterror_eledia_schema',
                implode(' | ', $validator->get_errors())
            );
        }

        return content_bundle::from_array($decoded);
    }

    // ---------------------------------------------------------------------
    // Internal helpers
    // ---------------------------------------------------------------------

    /**
     * POSTs {license_key, site_hash, plugin_version} to /verify and returns
     * the decoded ticket array. Throws a translatable exception for any
     * of: no key configured, server unreachable, non-2xx, malformed body.
     *
     * @return array{token: string, bundle_version: string, bundle_url: string, signature_url: string}
     * @throws content_source_exception
     */
    private function verify_license(): array {
        global $CFG;

        $serverurl = rtrim((string) $this->config->get('licenseserverurl', ''), '/');
        $licensekey = trim((string) $this->config->get('licensekey', ''));

        if ($serverurl === '') {
            throw new content_source_exception(
                'contenterror_eledia_nourl',
                'License server URL is not configured.'
            );
        }
        if ($licensekey === '') {
            throw new content_source_exception(
                'contenterror_eledia_nokey',
                'License key is not configured.'
            );
        }

        $payload = [
            'license_key'     => $licensekey,
            'site_hash'       => self::compute_site_hash(),
            'site_url'        => (string) $CFG->wwwroot,
            'plugin_version'  => self::current_plugin_version(),
            'component'       => self::COMPONENT,
        ];

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_FOLLOWLOCATION'  => true,
            'CURLOPT_MAXREDIRS'       => 3,
            'CURLOPT_CONNECTTIMEOUT'  => 10,
            'CURLOPT_TIMEOUT'         => self::TIMEOUT_SECONDS,
            'CURLOPT_SSL_VERIFYPEER'  => true,
        ]);
        $curl->setHeader([
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        $response = $curl->post($serverurl . '/verify', json_encode($payload));
        $info = $curl->get_info();
        $errno = $curl->get_errno();
        $httpcode = isset($info['http_code']) ? (int) $info['http_code'] : 0;

        if ($errno || $response === false) {
            throw new content_source_exception(
                'contenterror_eledia_http',
                "curl error {$errno}: " . $curl->error
            );
        }
        if ($httpcode === 401 || $httpcode === 403) {
            throw new content_source_exception(
                'contenterror_eledia_rejected',
                "License server rejected key (HTTP {$httpcode})"
            );
        }
        if ($httpcode < 200 || $httpcode >= 300) {
            throw new content_source_exception(
                'contenterror_eledia_http',
                "HTTP {$httpcode} from /verify: " . (string) $response
            );
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            throw new content_source_exception(
                'contenterror_eledia_parse',
                '/verify returned non-JSON body'
            );
        }

        foreach (['token', 'bundle_version', 'bundle_url', 'signature_url'] as $required) {
            if (empty($decoded[$required]) || !is_string($decoded[$required])) {
                throw new content_source_exception(
                    'contenterror_eledia_parse',
                    "/verify response missing field: {$required}"
                );
            }
        }

        return [
            'token'          => (string) $decoded['token'],
            'bundle_version' => (string) $decoded['bundle_version'],
            'bundle_url'     => (string) $decoded['bundle_url'],
            'signature_url'  => (string) $decoded['signature_url'],
        ];
    }

    /**
     * Simple authenticated GET that throws on non-2xx.
     *
     * @param string $url The URL to fetch.
     * @param string $token Bearer token from /verify endpoint.
     * @param string $accept Accept header value.
     * @return string Raw response body.
     * @throws content_source_exception On HTTP error or empty response.
     */
    private function http_get_authenticated(string $url, string $token, string $accept): string {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_FOLLOWLOCATION'  => true,
            'CURLOPT_MAXREDIRS'       => 3,
            'CURLOPT_CONNECTTIMEOUT'  => 10,
            'CURLOPT_TIMEOUT'         => self::TIMEOUT_SECONDS,
            'CURLOPT_SSL_VERIFYPEER'  => true,
        ]);
        $curl->setHeader([
            $accept,
            'Authorization: Bearer ' . $token,
        ]);

        $response = $curl->get($url);
        $info = $curl->get_info();
        $errno = $curl->get_errno();
        $httpcode = isset($info['http_code']) ? (int) $info['http_code'] : 0;

        if ($errno || $response === false) {
            throw new content_source_exception(
                'contenterror_eledia_http',
                "curl error {$errno} on {$url}: " . $curl->error
            );
        }
        if ($httpcode < 200 || $httpcode >= 300) {
            throw new content_source_exception(
                'contenterror_eledia_http',
                "HTTP {$httpcode} on {$url}"
            );
        }
        if (!is_string($response) || $response === '') {
            throw new content_source_exception(
                'contenterror_eledia_http',
                "Empty response body from {$url}"
            );
        }
        return $response;
    }

    /**
     * Stable SHA-256 of the site, used by the license server to enforce `max_installs` without storing wwwroot.
     *
     * Derived from wwwroot + siteidentifier, both constant for a Moodle installation.
     *
     * @return string 64 hex character SHA-256 hash.
     */
    public static function compute_site_hash(): string {
        global $CFG;
        $identifier = (string) get_site_identifier();
        return hash('sha256', ((string) $CFG->wwwroot) . '|' . $identifier);
    }

    /**
     * Returns the numeric plugin version from version.php.
     *
     * Used as user-agent telemetry for the license server.
     *
     * @return string The plugin version as a string.
     */
    private static function current_plugin_version(): string {
        $plugin = new \stdClass();
        require(__DIR__ . '/../../version.php');
        return isset($plugin->version) ? (string) $plugin->version : '0';
    }
}
