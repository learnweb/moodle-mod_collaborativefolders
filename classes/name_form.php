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

namespace mod_collaborativefolders;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


/**
 * Form class for the name insertion in collaborative folders.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class name_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        // Name field.
        $mform->addElement('text', 'namefield', get_string('namefield', 'mod_collaborativefolders'), array('size' => '64'));
        $mform->addRule('namefield', get_string('required'), 'required', null, 'client');
        // The default value is the name of the activity, chosen by it's creator.
        $mform->setDefault('namefield', $this->_customdata['namefield']);
        $mform->setType('namefield', PARAM_RAW_TRIMMED);

        $mform->addElement('submit', 'enter', get_string('getaccess', 'mod_collaborativefolders'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['namefield'] !== clean_param($data['namefield'], PARAM_PATH)) {
            $errors['namefield'] = get_string('error_illegalpathchars', 'mod_collaborativefolders');
        }

        return $errors;
    }

    protected function get_form_identifier() {
        $formid = $this->_customdata['id'] . '_' . get_class($this);
        return $formid;
    }

}