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
 * The main collaborativefolders configuration form.
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_collaborativefolders_mod_form extends moodleform_mod {

    public function definition() {
        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name of the activity, which is chosen by the teacher.
        $mform->addElement('text', 'name', get_string('collaborativefoldersname', 'mod_collaborativefolders'), ['size' => '64']);
        $mform->setType('name', PARAM_RAW_TRIMMED);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'collaborativefoldersname', 'collaborativefolders');

        $this->standard_intro_elements();

        // Reminder for groupsettings.
        $mform->addElement('warning', null, 'notifyproblem', get_string('edit_after_creation', 'mod_collaborativefolders'));

        // Checkbox, which indicates whether the course's teacher(s) should have access to the folder.
        $mform->addElement('advcheckbox', 'teacher',
            get_string('teacher_access', 'mod_collaborativefolders'),
            get_string('teacher_mode', 'mod_collaborativefolders'),
            [], array(0, 1));
        $mform->addHelpButton('teacher', 'teacher_mode', 'collaborativefolders');

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Make changing certain settings impossible (except at creation time)
        // Being able to change them would lead to undefined behaviour, so it is forbidden.
        if ($this->current->instance) { // Only set if editing an existing instance.
            $mform->hardFreeze(['groupmode', 'groupingid', 'teacher']);
        }

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

}
