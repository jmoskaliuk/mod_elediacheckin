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
 * Static lint test for db/install.xml.
 *
 * @package    mod_elediacheckin
 * @category   test
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elediacheckin;

/**
 * Regression tests for the plugin's install.xml.
 *
 * Catches schema patterns that pass XSD validation but trigger Moodle's
 * runtime XMLDB warnings — specifically the "CHAR NOT NULL column with
 * '' as DEFAULT" notice that fired on every install/upgrade and aborted
 * the upgrade in DEBUG_DEVELOPER mode.
 */
#[\PHPUnit\Framework\Attributes\CoversNothing]
final class install_xml_test extends \basic_testcase {
    // phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing
    /**
     * Loads and parses db/install.xml.
     *
     * @return \SimpleXMLElement Parsed root element.
     */
    private function load_install_xml(): \SimpleXMLElement {
        $path = __DIR__ . '/../db/install.xml';
        $this->assertFileExists($path, 'db/install.xml must exist');
        $xml = simplexml_load_file($path);
        $this->assertNotFalse($xml, 'db/install.xml must be valid XML');
        return $xml;
    }

    /**
     * No CHAR NOT NULL column may declare DEFAULT="" — Moodle's xmldb_field
     * setDefault() fires a debugging() notice for that pattern, which is
     * escalated to a fatal ErrorException in DEBUG_DEVELOPER mode and
     * aborts the upgrade. The fix is to either drop NOTNULL or replace the
     * default with something meaningful (or omit the DEFAULT attribute).
     */
    public function test_no_char_notnull_with_empty_default(): void {
        $xml = $this->load_install_xml();
        $offenders = [];
        foreach ($xml->xpath('//FIELD') as $field) {
            $type = (string) ($field['TYPE'] ?? '');
            $notnull = strtolower((string) ($field['NOTNULL'] ?? ''));
            $hasdefault = isset($field['DEFAULT']);
            $default = $hasdefault ? (string) $field['DEFAULT'] : null;
            if ($type === 'char' && $notnull === 'true' && $hasdefault && $default === '') {
                $offenders[] = (string) $field['NAME'];
            }
        }
        $this->assertSame(
            [],
            $offenders,
            'These CHAR NOT NULL columns have DEFAULT="" which triggers a Moodle '
            . 'XMLDB notice on every install/upgrade: ' . implode(', ', $offenders) . '. '
            . 'Either drop NOTNULL="true" (make the column NULL-allowed) or replace '
            . 'the DEFAULT="" with a meaningful default (or omit the DEFAULT attribute).'
        );
    }
}
