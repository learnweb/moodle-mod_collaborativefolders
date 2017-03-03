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
 * Form class for the name insertion in collaborative folders.
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require($CFG->libdir . '/formslib.php');

class mod_collaborativefolders_name_form extends moodleform {

    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('header', 'name', get_string('oauthlegend', 'tool_oauth2sciebo'));
        // Name field.
        $mform->addElement('text', 'namefield', get_string('clientid', 'tool_oauth2sciebo'), array('size' => '64'));
        $mform->addRule('namefield', get_string('required'), 'required', null, 'client');
        $mform->addRule('namefield', get_string('err_alphanumeric'), 'alphanumeric', null, 'client');
        $mform->setDefault('namefield', $this->_customdata['namefield']);
        $mform->setType('namefield', PARAM_ALPHANUM);

        $mform->addElement('submit', 'enter', 'Save');
    }
}