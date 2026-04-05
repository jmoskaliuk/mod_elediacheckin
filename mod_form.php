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

/**
 * Activity settings form.
 */
class mod_elediacheckin_mod_form extends moodleform_mod {

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

        $modes = [
            'both'     => get_string('mode_both', 'elediacheckin'),
            'checkin'  => get_string('mode_checkin', 'elediacheckin'),
            'checkout' => get_string('mode_checkout', 'elediacheckin'),
        ];
        $mform->addElement('select', 'mode', get_string('mode', 'elediacheckin'), $modes);
        $mform->setDefault('mode', 'both');
        $mform->addHelpButton('mode', 'mode', 'elediacheckin');

        $mform->addElement('text', 'categories', get_string('categories', 'elediacheckin'), ['size' => '48']);
        $mform->setType('categories', PARAM_TEXT);
        $mform->addHelpButton('categories', 'categories', 'elediacheckin');

        $mform->addElement('text', 'contentlang', get_string('contentlang', 'elediacheckin'), ['size' => '8']);
        $mform->setType('contentlang', PARAM_LANG);
        $mform->setDefault('contentlang', '');
        $mform->addHelpButton('contentlang', 'contentlang', 'elediacheckin');

        $mform->addElement('selectyesno', 'randomstart', get_string('randomstart', 'elediacheckin'));
        $mform->setDefault('randomstart', 1);
        $mform->addHelpButton('randomstart', 'randomstart', 'elediacheckin');

        // Anzeigeoptionen.
        $mform->addElement('header', 'displaysettings', get_string('displaysettings', 'elediacheckin'));

        $mform->addElement('selectyesno', 'shownav', get_string('shownav', 'elediacheckin'));
        $mform->setDefault('shownav', 1);

        $mform->addElement('selectyesno', 'showother', get_string('showother', 'elediacheckin'));
        $mform->setDefault('showother', 1);

        $mform->addElement('selectyesno', 'showfilter', get_string('showfilter', 'elediacheckin'));
        $mform->setDefault('showfilter', 0);

        $mform->addElement('selectyesno', 'avoidrepeat', get_string('avoidrepeat', 'elediacheckin'));
        $mform->setDefault('avoidrepeat', 1);
        $mform->addHelpButton('avoidrepeat', 'avoidrepeat', 'elediacheckin');

        // Standard course module elements.
        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}
