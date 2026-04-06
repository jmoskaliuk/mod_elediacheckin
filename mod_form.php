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
        // As a CSV string; conversion happens in data_preprocessing() and
        // Get_data() below.
        $zieloptions = [
            'impuls'   => get_string('ziel_impuls', 'elediacheckin'),
            'checkin'  => get_string('ziel_checkin', 'elediacheckin'),
            'checkout' => get_string('ziel_checkout', 'elediacheckin'),
            'retro'    => get_string('ziel_retro', 'elediacheckin'),
            'learning' => get_string('ziel_learning', 'elediacheckin'),
            'funfact'  => get_string('ziel_funfact', 'elediacheckin'),
            'zitat'    => get_string('ziel_zitat', 'elediacheckin'),
        ];
        $mform->addElement(
            'autocomplete',
            'ziele',
            get_string('ziele', 'elediacheckin'),
            $zieloptions,
            [
                'multiple' => true,
                'noselectionstring' => get_string('ziele_all', 'elediacheckin'),
            ]
        );
        $mform->setDefault('ziele', ['checkin', 'checkout']);
        $mform->addHelpButton('ziele', 'ziele', 'elediacheckin');

        // Categories: autocomplete multi-select of bare category ids.
        // The visible option set is filtered client-side based on the
        // Currently selected ziele (see mod_elediacheckin/category_filter).
        $categoryoptions = $this->build_category_options();
        $mform->addElement(
            'autocomplete',
            'categories',
            get_string('categories', 'elediacheckin'),
            $categoryoptions,
            [
                'multiple' => true,
                'noselectionstring' => get_string('categories_all', 'elediacheckin'),
            ]
        );
        $mform->addHelpButton('categories', 'categories', 'elediacheckin');

        // Zielgruppe: Single-Select-Dropdown mit "Alle Zielgruppen" als
        // Erstem, explizit wählbarem Eintrag (leerer Wert = keine
        // Einschränkung, "oder untagged" in question_provider). War früher
        // Ein Multi-Select-Autocomplete; aus UX-Gründen umgebaut — der
        // Häufige Fall "Alle" soll sofort sichtbar sein, statt durch das
        // Noselectionstring-Label in Autocomplete versteckt zu werden.
        // Siehe docs/testing-inbox.md Kommentar vom 2026-04-05.
        $zgoptions = ['' => get_string('zielgruppe_all', 'elediacheckin')];
        foreach (schema_validator::get_zielgruppe_enum() as $zg) {
            $zgoptions[$zg] = get_string('zielgruppe_' . $zg, 'elediacheckin');
        }
        $mform->addElement(
            'select',
            'zielgruppe',
            get_string('zielgruppe', 'elediacheckin'),
            $zgoptions
        );
        $mform->setDefault('zielgruppe', '');
        $mform->addHelpButton('zielgruppe', 'zielgruppe', 'elediacheckin');

        // Kontext: gleiches Pattern wie Zielgruppe — Single-Select mit
        // "Alle Kontexte" als erstem Eintrag.
        $kxoptions = ['' => get_string('kontext_all', 'elediacheckin')];
        foreach (schema_validator::get_kontext_enum() as $kx) {
            $kxoptions[$kx] = get_string('kontext_' . $kx, 'elediacheckin');
        }
        $mform->addElement(
            'select',
            'kontext',
            get_string('kontext', 'elediacheckin'),
            $kxoptions
        );
        $mform->setDefault('kontext', '');
        $mform->addHelpButton('kontext', 'kontext', 'elediacheckin');

        // Wire the dynamic filter: hides categories that do not belong to
        // Any of the currently selected ziele. The category→ziel map can be
        // Fairly large (> 1 KB) so we stash it in a hidden JSON <script>
        // Element instead of passing it as js_call_amd argument (Moodle warns
        // Above 1024 chars). The AMD module reads it from the DOM on init.
        $mapjson = json_encode($this->build_category_ziel_map());
        $mform->addElement(
            'html',
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
        // Plus all installed languages.
        $langoptions = [
            self::LANG_AUTO   => get_string('lang_auto', 'elediacheckin'),
            self::LANG_COURSE => get_string('lang_course', 'elediacheckin'),
        ];
        $installed = get_string_manager()->get_list_of_translations();
        foreach ($installed as $code => $name) {
            $langoptions[$code] = $name;
        }
        $mform->addElement(
            'select',
            'contentlang',
            get_string('contentlang', 'elediacheckin'),
            $langoptions
        );
        $mform->setDefault('contentlang', self::LANG_AUTO);
        $mform->addHelpButton('contentlang', 'contentlang', 'elediacheckin');

        // Anzeigeoptionen.
        // Kommt VOR dem "Eigene Fragen"-Block, weil Display-Einstellungen
        // Zur Kernkonfiguration gehören, während "Eigene Fragen" ein
        // Optionales Extra sind. Siehe testing-inbox 2026-04-05
        // ("in den einstellungen eigene Fragen nach Display options").
        $mform->addElement(
            'header',
            'displaysettings',
            get_string('displaysettings', 'elediacheckin')
        );

        $mform->addElement('selectyesno', 'avoidrepeat', get_string('avoidrepeat', 'elediacheckin'));
        $mform->setDefault('avoidrepeat', 1);
        $mform->addHelpButton('avoidrepeat', 'avoidrepeat', 'elediacheckin');

        // Single-step back button ("Zur vorherigen Frage"), kein vor/zurueck-
        // Paar. Zustand pro cmid im $SESSION, siehe view.php/present.php.
        $mform->addElement(
            'selectyesno',
            'showprevbutton',
            get_string('showprevbutton', 'elediacheckin')
        );
        $mform->setDefault('showprevbutton', 1);
        $mform->addHelpButton('showprevbutton', 'showprevbutton', 'elediacheckin');

        // Per-activity exhausted behavior ("Wenn alle Fragen durch sind") —
        // Verhalten. 'restart' = Seen-Set zurücksetzen und weiter neu
        // Ziehen (Default, weil die meisten Check-in-Pools klein sind
        // Und der Lernende sowieso zeitlich versetzt wieder auftaucht).
        // 'empty' = eine Hinweiskarte "Alle Fragen durch" anzeigen und
        // Nicht mehr weiterziehen. Siehe activity_pool::resolve_navigation.
        $exhausteoptions = [
            \mod_elediacheckin\local\service\activity_pool::EXHAUSTED_RESTART
                => get_string('exhaustedbehavior_restart', 'elediacheckin'),
            \mod_elediacheckin\local\service\activity_pool::EXHAUSTED_EMPTY
                => get_string('exhaustedbehavior_empty', 'elediacheckin'),
        ];
        $mform->addElement(
            'select',
            'exhaustedbehavior',
            get_string('exhaustedbehavior', 'elediacheckin'),
            $exhausteoptions
        );
        $mform->setDefault(
            'exhaustedbehavior',
            \mod_elediacheckin\local\service\activity_pool::EXHAUSTED_RESTART
        );
        $mform->addHelpButton('exhaustedbehavior', 'exhaustedbehavior', 'elediacheckin');

        // Eigene Fragen (per-Aktivität, siehe Konzept §10.13 + §10.19).
        // Additiver Zusatzpool zu den Bundle-Fragen: eine Zeile = eine
        // Karte. Wird als TEXT-Spalte persistiert und bei jedem Draw von
        // Activity_pool zusammengemerged. Reine Impulskarten, keine
        // Rückseite.
        $mform->addElement(
            'header',
            'ownquestionsheader',
            get_string('ownquestions', 'elediacheckin')
        );

        // Tri-state-Dropdown statt frueherem Yes/No-Toggle. Die drei
        // Modi sind bewusst explizit ausformuliert, weil "Nein" fuer
        // "Nur eigene Fragen" mehrdeutig war (hiess es "eigene Fragen
        // Ignorieren" oder "mit Bundle mischen"?). Default: mixed.
        $modeoptions = [
            0 => get_string('ownquestionsmode_mixed', 'elediacheckin'),
            1 => get_string('ownquestionsmode_onlyown', 'elediacheckin'),
            2 => get_string('ownquestionsmode_none', 'elediacheckin'),
        ];
        $mform->addElement(
            'select',
            'ownquestionsmode',
            get_string('ownquestionsmode', 'elediacheckin'),
            $modeoptions
        );
        $mform->setDefault('ownquestionsmode', 0);
        $mform->addHelpButton('ownquestionsmode', 'ownquestionsmode', 'elediacheckin');

        $mform->addElement(
            'textarea',
            'ownquestions',
            get_string('ownquestions', 'elediacheckin'),
            [
                'rows' => 6,
                'cols' => 60,
                'style' => 'font-family: inherit;',
            ]
        );
        $mform->setType('ownquestions', PARAM_TEXT);
        $mform->addHelpButton('ownquestions', 'ownquestions', 'elediacheckin');
        // Wenn der Modus auf "Keine eigenen Fragen" steht, ist das Textfeld
        // Nutzlos. Wir blenden es dann aus (ohne es zu loeschen), damit
        // Der Teacher jederzeit zurueck in "mixed" oder "nur eigene"
        // Wechseln kann, ohne die Eintraege neu tippen zu muessen.
        $mform->hideIf('ownquestions', 'ownquestionsmode', 'eq', 2);

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
     * @param array $defaultvalues The values from the database.
     * @return void
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

        // Zielgruppe + Kontext sind seit 2026-04 Single-Select. Falls die
        // DB noch CSV aus der Multi-Select-Ära enthält, nehmen wir den
        // Ersten Eintrag als sinnvollen Default für den Edit-Dialog — der
        // Teacher kann beim Speichern bewusst auf "Alle" zurückgehen.
        foreach (['zielgruppe', 'kontext'] as $tagfield) {
            if (isset($defaultvalues[$tagfield]) && is_string($defaultvalues[$tagfield])) {
                $parts = array_values(array_filter(
                    array_map('trim', explode(',', $defaultvalues[$tagfield])),
                    'strlen'
                ));
                $defaultvalues[$tagfield] = $parts[0] ?? '';
            }
        }
    }

    /**
     * Form → DB: fold the arrays back into CSV strings.
     *
     * @return \stdClass|null The form data as an object, or null if not submitted.
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

        // Single-Select: Wert kommt bereits als Skalar-String aus dem
        // Form-Element. Absicherung gegen exotische Fälle (null, Array).
        foreach (['zielgruppe', 'kontext'] as $tagfield) {
            if (!isset($data->{$tagfield})) {
                continue;
            }
            if (is_array($data->{$tagfield})) {
                // Defensive: falls irgendwo noch ein Array reinkommt.
                $first = reset($data->{$tagfield});
                $data->{$tagfield} = $first !== false ? (string) $first : '';
            } else if (!is_string($data->{$tagfield})) {
                $data->{$tagfield} = '';
            }
        }

        return $data;
    }
}
