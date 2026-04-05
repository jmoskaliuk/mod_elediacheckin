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
 * Content source that fetches a bundle JSON from an HTTPS URL (typically a
 * raw file in a git repository or a release asset).
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin\content;

use mod_elediacheckin\local\service\config_service;

defined('MOODLE_INTERNAL') || die();

/**
 * Pulls bundle.json from a configured HTTPS URL, validates it against the
 * content schema and returns it as a content_bundle.
 *
 * Configuration (plugin-level, see settings.php):
 *  - repourl   : HTTPS URL to the raw bundle JSON.
 *  - repotoken : Optional bearer/PAT for private repositories.
 *
 * Intentionally does NOT shell out to `git` — we only need a single file and
 * Moodle servers often can't run git. A plain HTTPS GET keeps hosting simple
 * (GitHub raw, GitLab raw, any static webserver).
 */
final class git_content_source implements content_source_interface {

    /** HTTP timeout for the fetch, in seconds. */
    private const TIMEOUT_SECONDS = 30;

    /** @var config_service */
    private $config;

    /**
     * @param config_service|null $config injectable for tests
     */
    public function __construct(?config_service $config = null) {
        $this->config = $config ?? new config_service();
    }

    /**
     * @inheritDoc
     */
    public function get_id(): string {
        return 'git';
    }

    /**
     * @inheritDoc
     */
    public function get_display_name(): string {
        return get_string('contentsource_git', 'elediacheckin');
    }

    /**
     * @inheritDoc
     */
    public function test_connection(): bool {
        try {
            $this->fetch_raw();
            return true;
        } catch (content_source_exception $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function fetch_bundle(): content_bundle {
        $raw = $this->fetch_raw();

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new content_source_exception(
                'contenterror_gitparse',
                'json_decode error: ' . json_last_error_msg()
            );
        }

        $validator = new schema_validator();
        if (!$validator->validate($decoded)) {
            throw new content_source_exception(
                'contenterror_gitinvalid',
                implode(' | ', $validator->get_errors())
            );
        }

        return content_bundle::from_array($decoded);
    }

    /**
     * Downloads the configured URL and returns the raw response body.
     *
     * @return string
     * @throws content_source_exception
     */
    private function fetch_raw(): string {
        $url = (string)$this->config->get('repourl', '');
        if ($url === '') {
            throw new content_source_exception(
                'contenterror_gitnourl',
                'No repository URL configured'
            );
        }

        $token = (string)$this->config->get('repotoken', '');
        $headers = ['Accept: application/json'];
        if ($token !== '') {
            // GitHub, GitLab and Gitea all accept the Bearer form for PATs.
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        // Use Moodle's curl wrapper so proxy / cert settings are honoured.
        // `global $CFG` is required because filelib.php's top-level code
        // references bare $CFG (e.g. require_once($CFG->libdir . '/filestorage/…')).
        // Inside a method scope, $GLOBALS['CFG'] only resolves the first require;
        // the nested ones in filelib.php fail with "Undefined variable $CFG" and
        // break the Verbindung-testen admin action.
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_FOLLOWLOCATION'  => true,
            'CURLOPT_MAXREDIRS'       => 5,
            'CURLOPT_CONNECTTIMEOUT'  => 10,
            'CURLOPT_TIMEOUT'         => self::TIMEOUT_SECONDS,
            'CURLOPT_SSL_VERIFYPEER'  => true,
        ]);
        $curl->setHeader($headers);

        $response = $curl->get($url);
        $info = $curl->get_info();
        $errno = $curl->get_errno();
        $httpcode = isset($info['http_code']) ? (int)$info['http_code'] : 0;

        if ($errno || $response === false) {
            throw new content_source_exception(
                'contenterror_githttp',
                "curl error {$errno}: " . $curl->error
            );
        }
        if ($httpcode < 200 || $httpcode >= 300) {
            throw new content_source_exception(
                'contenterror_githttp',
                "HTTP {$httpcode} while fetching {$url}"
            );
        }
        if (!is_string($response) || $response === '') {
            throw new content_source_exception(
                'contenterror_gitempty',
                "Empty response body from {$url}"
            );
        }

        return $response;
    }
}
