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
 * Course module settings form for mod_elediacheckin.
 *
 * @package    mod_elediacheckin
 * @copyright  2026 eLeDia GmbH <info@eledia.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

use mod_elediacheckin\content\schema_validator;

/**
 * Activity settings form.
 *
 * UX notes (see docs/content-distribution-konzept.md §10.9):
 *  - 'ziele' is a multi-select; CSV in the DB column, array in the form.
 *  - 'categories' is an autocomplete multi-select populated from the
 *    schema-validator's CATEGORIES_BY_ZIEL catalogue. Each option is
 *    labelled "Ziel: Kategorie" so the editor can see which categories
 *    belong to which ziel without digging into the schema file.
 *  - 'contentlang' is a select with two sentinels ('_auto_' → user
 *    language, '_course_' → course language) plus all installed Moodle
 *    languages. Default is '_auto_'. The sentinels are resolved in
 *    view.php / present.php when building the question filter.
 *  - 'randomstart' has been removed (was dead code — never read anywhere).
 */
class mod_elediacheckin_mod_form extends moodleform_mod {

    /** Sentinel for "use the current user's language". */
    public const LANG_AUTO   = '_auto_';

    /** Sentinel for "use the course's language". */
    public const LANG_COURSE = '_course_';

    /**
     * Defines the form.
     */
    public function definition(): void {
        $mform = $this->_form;

        // General section.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // Fachliche Parameter.
        $mform->addElement('header', 'checkinsettings', get_string('checkinsettings', 'elediacheckin'));
        $mform->setExpanded('checkinsettings');

        // Ziele: multi-select over all available content types. Persisted
        // as a CSV string; conversion happens in data_preprocessing() and
        // get_data() below.
        $zieloptions = [
            'impuls'   => get_string('ziel_impuls', 'elediacheckin'),
            'checkin'  => get_string('ziel_checkin', 'elediacheckin'),
            'checkout' => get_string('ziel_checkout', 'elediacheckin'),
            'retro'    => get_string('ziel_retro', 'elediacheckin'),
            'learning' => get_string('ziel_learning', 'elediacheckin'),
            'funfact'  => get_string('ziel_funfact', 'elediacheckin'),
            'zitat'    => get_string('ziel_zitat', 'elediacheckin'),
        ];
        $mform->addElement('autocomplete', 'ziele',
            get_string('ziele', 'elediacheckin'), $zieloptions, [
                'multiple'            => true,
                'noselectionstring'   => get_string('ziele_all', 'elediacheckin'),
            ]);
        $mform->setDefault('ziele', ['checkin', 'checkout']);
        $mform->addHelpButton('ziele', 'ziele', 'elediacheckin');

        // Categories: autocomplete multi-select with labels grouped by ziel.
        // Options are pulled from the schema validator so the form and the
        // validator never drift. Empty selection = all categories allowed.
        $categoryoptions = $this->build_category_options();
        $mform->addElement('autocomplete', 'categories',
            get_string('categories', 'elediacheckin'),
            $categoryoptions, [
                'multiple' => true,
                'noselectionstring' => get_string('categories_all', 'elediacheckin'),
            ]);
        $mform->addHelpButton('categories', 'categories', 'elediacheckin');

        // Content language: select with sentinels for user/course language
        // plus all installed languages.
        $langoptions = [
            self::LANG_AUTO   => get_string('lang_auto', 'elediacheckin'),
            self::LANG_COURSE => get_string('lang_course', 'elediacheckin'),
        ];
        $installed = get_string_manager()->get_list_of_translations();
        foreach ($installed as $code => $name) {
            $langoptions[$code] = $name;
        }
        $mform->addElement('select', 'contentlang',
            get_string('contentlang', 'elediacheckin'), $langoptions);
        $mform->setDefault('contentlang', self::LANG_AUTO);
        $mform->addHelpButton('contentlang', 'contentlang', 'elediacheckin');

        // Anzeigeoptionen.
        $mform->addElement('header', 'displaysettings', get_string('displaysettings', 'elediacheckin'));

        $mform->addElement('selectyesno', 'avoidrepeat', get_string('avoidrepeat', 'elediacheckin'));
        $mform->setDefault('avoidrepeat', 1);
        $mform->addHelpButton('avoidrepeat', 'avoidrepeat', 'elediacheckin');

        // Standard course module elements.
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Builds the labelled category options array.
     *
     * Output shape:  ['stimmung' => 'Check-in: Stimmung', ...]
     *
     * A few categories appear under multiple ziele (e.g. "stimmung" is
     * valid for check-in, check-out, retro). In that case we list the
     * category once per ziel with a ziel-prefixed label, but they all map
     * to the same category id — the filter in question_provider matches
     * on id, not on ziel-scoped key, so this is still consistent.
     *
     * @return array<string, string>
     */
    private function build_category_options(): array {
        $options = [];
        $byziel  = schema_validator::get_categories_by_ziel();
        foreach ($byziel as $ziel => $cats) {
            $zlabel = get_string_manager()->string_exists('ziel_' . $ziel, 'elediacheckin')
                ? get_string('ziel_' . $ziel, 'elediacheckin')
                : ucfirst($ziel);
            foreach ($cats as $cat) {
                $key = $ziel . '__' . $cat;
                $clabel = get_string_manager()->string_exists('cat_' . $cat, 'elediacheckin')
                    ? get_string('cat_' . $cat, 'elediacheckin')
                    : ucfirst(str_replace('-', ' ', $cat));
                $options[$key] = "{$zlabel}: {$clabel}";
            }
        }
        return $options;
    }

    /**
     * DB → form:
     *  - 'ziele' CSV → array for the multi-select
     *  - 'categories' CSV (bare category ids) → array of ziel__cat composite
     *    keys so the autocomplete pre-selects the right rows.
     *
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues): void {
        if (isset($defaultvalues['ziele']) && is_string($defaultvalues['ziele'])) {
            $defaultvalues['ziele'] = $defaultvalues['ziele'] === ''
                ? []
                : explode(',', $defaultvalues['ziele']);
        }

        if (isset($defaultvalues['categories']) && is_string($defaultvalues['categories'])) {
            $raw = $defaultvalues['categories'] === ''
                ? []
                : explode(',', $defaultvalues['categories']);
            // Expand each bare category id to every (ziel__cat) key where it
            // appears so the picker lights up for all matching ziele.
            $expanded = [];
            $byziel   = schema_validator::get_categories_by_ziel();
            foreach ($raw as $cat) {
                $cat = trim($cat);
                if ($cat === '') {
                    continue;
                }
                foreach ($byziel as $ziel => $cats) {
                    if (in_array($cat, $cats, true)) {
                        $expanded[] = $ziel . '__' . $cat;
                    }
                }
            }
            $defaultvalues['categories'] = array_values(array_unique($expanded));
        }
    }

    /**
     * Form → DB:
     *  - 'ziele' array → CSV
     *  - 'categories' array of ziel__cat keys → CSV of unique bare category ids
     *
     * @return \stdClass|null
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }

        if (isset($data->ziele) && is_array($data->ziele)) {
            $data->ziele = implode(',', $data->ziele);
        }

        if (isset($data->categories)) {
            if (is_array($data->categories)) {
                $bare = [];
                foreach ($data->categories as $key) {
                    // Split 'checkin__energie' → 'energie'.
                    $pos = strpos($key, '__');
                    $bare[] = $pos === false ? $key : substr($key, $pos + 2);
                }
                $data->categories = implode(',', array_values(array_unique($bare)));
            } else if (!is_string($data->categories)) {
                $data->categories = '';
            }
        }

        return $data;
    }
}
