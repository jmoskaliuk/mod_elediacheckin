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
 * UX notes (see docs/content-distribution-konzept.md §10.9 + §10.11):
 *  - 'ziele' is an autocomplete multi-select; CSV in the DB column, array in the form.
 *  - 'categories' is an autocomplete multi-select of bare category ids,
 *    dynamically filtered by the selected ziele via an AMD module
 *    (mod_elediacheckin/category_filter). The backend passes a
 *    category→ziele map to the module so it can hide non-matching options
 *    client-side without a round-trip.
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
        global $PAGE;
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

        // Categories: autocomplete multi-select of bare category ids.
        // The visible option set is filtered client-side based on the
        // currently selected ziele (see mod_elediacheckin/category_filter).
        $categoryoptions = $this->build_category_options();
        $mform->addElement('autocomplete', 'categories',
            get_string('categories', 'elediacheckin'),
            $categoryoptions, [
                'multiple' => true,
                'noselectionstring' => get_string('categories_all', 'elediacheckin'),
            ]);
        $mform->addHelpButton('categories', 'categories', 'elediacheckin');

        // Zielgruppe: optional multi-select, "or untagged" semantics at read
        // time (see question_provider). Empty means "no restriction".
        $zgoptions = [];
        foreach (schema_validator::get_zielgruppe_enum() as $zg) {
            $zgoptions[$zg] = get_string('zielgruppe_' . $zg, 'elediacheckin');
        }
        $mform->addElement('autocomplete', 'zielgruppe',
            get_string('zielgruppe', 'elediacheckin'), $zgoptions, [
                'multiple' => true,
                'noselectionstring' => get_string('zielgruppe_all', 'elediacheckin'),
            ]);
        $mform->addHelpButton('zielgruppe', 'zielgruppe', 'elediacheckin');

        // Kontext: same pattern as zielgruppe.
        $kxoptions = [];
        foreach (schema_validator::get_kontext_enum() as $kx) {
            $kxoptions[$kx] = get_string('kontext_' . $kx, 'elediacheckin');
        }
        $mform->addElement('autocomplete', 'kontext',
            get_string('kontext', 'elediacheckin'), $kxoptions, [
                'multiple' => true,
                'noselectionstring' => get_string('kontext_all', 'elediacheckin'),
            ]);
        $mform->addHelpButton('kontext', 'kontext', 'elediacheckin');

        // Wire the dynamic filter: hides categories that do not belong to
        // any of the currently selected ziele. The category→ziel map can be
        // fairly large (> 1 KB) so we stash it in a hidden JSON <script>
        // element instead of passing it as js_call_amd argument (Moodle warns
        // above 1024 chars). The AMD module reads it from the DOM on init.
        $mapjson = json_encode($this->build_category_ziel_map());
        $mform->addElement('html',
            '<script type="application/json" id="elediacheckin_catziel_map">'
            . $mapjson
            . '</script>'
        );
        $PAGE->requires->js_call_amd(
            'mod_elediacheckin/category_filter',
            'init',
            ['id_ziele', 'id_categories', 'elediacheckin_catziel_map']
        );

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
     * Builds the category options array with bare category ids as keys.
     * Categories that belong to multiple ziele appear only once.
     *
     * Output shape:  ['stimmung' => 'Stimmung', 'fokus' => 'Fokus', ...]
     *
     * @return array<string, string>
     */
    private function build_category_options(): array {
        $options = [];
        foreach (schema_validator::get_categories_by_ziel() as $ziel => $cats) {
            foreach ($cats as $cat) {
                if (isset($options[$cat])) {
                    continue;
                }
                $options[$cat] = get_string_manager()->string_exists('cat_' . $cat, 'elediacheckin')
                    ? get_string('cat_' . $cat, 'elediacheckin')
                    : ucfirst(str_replace('-', ' ', $cat));
            }
        }
        asort($options, SORT_LOCALE_STRING);
        return $options;
    }

    /**
     * Builds a map of bare category id → list of ziele it belongs to.
     * Consumed by the category_filter AMD module to hide/show options
     * based on the user's current ziel selection.
     *
     * @return array<string, string[]>
     */
    private function build_category_ziel_map(): array {
        $map = [];
        foreach (schema_validator::get_categories_by_ziel() as $ziel => $cats) {
            foreach ($cats as $cat) {
                if (!isset($map[$cat])) {
                    $map[$cat] = [];
                }
                if (!in_array($ziel, $map[$cat], true)) {
                    $map[$cat][] = $ziel;
                }
            }
        }
        return $map;
    }

    /**
     * DB → form: split CSV fields into arrays for the multi-selects.
     *
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues): void {
        if (isset($defaultvalues['ziele']) && is_string($defaultvalues['ziele'])) {
            $defaultvalues['ziele'] = $defaultvalues['ziele'] === ''
                ? []
                : array_values(array_filter(array_map('trim', explode(',', $defaultvalues['ziele'])), 'strlen'));
        }

        if (isset($defaultvalues['categories']) && is_string($defaultvalues['categories'])) {
            $defaultvalues['categories'] = $defaultvalues['categories'] === ''
                ? []
                : array_values(array_filter(array_map('trim', explode(',', $defaultvalues['categories'])), 'strlen'));
        }

        foreach (['zielgruppe', 'kontext'] as $tagfield) {
            if (isset($defaultvalues[$tagfield]) && is_string($defaultvalues[$tagfield])) {
                $defaultvalues[$tagfield] = $defaultvalues[$tagfield] === ''
                    ? []
                    : array_values(array_filter(array_map('trim', explode(',', $defaultvalues[$tagfield])), 'strlen'));
            }
        }
    }

    /**
     * Form → DB: fold the arrays back into CSV strings.
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

        if (isset($data->categories) && is_array($data->categories)) {
            $data->categories = implode(',', array_values(array_unique($data->categories)));
        } else if (isset($data->categories) && !is_string($data->categories)) {
            $data->categories = '';
        }

        foreach (['zielgruppe', 'kontext'] as $tagfield) {
            if (isset($data->{$tagfield}) && is_array($data->{$tagfield})) {
                $data->{$tagfield} = implode(',',
                    array_values(array_unique($data->{$tagfield})));
            } else if (isset($data->{$tagfield}) && !is_string($data->{$tagfield})) {
                $data->{$tagfield} = '';
            }
        }

        return $data;
    }
}
