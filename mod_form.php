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
 * The main collaborativefolders configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/lib/setuplib.php');

/**
 * Module instance settings form
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_collaborativefolders_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));
        // Name of the folder, which is chosen by the teacher. A folder with the same name will be created in ownCloud.
        $mform->addElement('text', 'name', get_string('collaborativefoldersname', 'collaborativefolders'), array('size' => '64'));
        $mform->setType('name', PARAM_RAW_TRIMMED);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'collaborativefoldersname', 'collaborativefolders');
        // Name of this instance.

        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding checkboxes for the groups, whom additional folders shall be created for.
        $mform->addElement('header', 'group', get_string('createforall', 'collaborativefolders'));
        $mform->addElement('html', 'Consider that we do not support to <b>update</b> folders for groups.
        Please create another instance if you want to change your groups settings.<p>');
        $mform->addElement('checkbox', 'mode', 'Enable groupmode');

        // All relevant group fields in the DB are fetched and a specific checkbox is added for each.
        $arrayofgroups = $this->get_group_fields();

        // Those checkboxes are only activated, if the groupmode is checked.
        foreach ($arrayofgroups as $id => $group) {
            $mform->addElement('advcheckbox', $group['id'], $group['name'], ' Number of participants: ' . $group['numberofparticipants'], array(), array(0, 1));
            $mform->disabledIf($group['id'], 'mode');
        }

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Helper function for fetching group information from the active course.
     * @return array course group fields.
     */
    private function get_group_fields() {
        global $DB;

        $allgroups = $DB->get_records('groups');
        $relevantinformation = array();
        foreach ($allgroups as $key => $group) {
            $relevantinformation[$key]['name'] = $group->name;
            $relevantinformation[$key]['id'] = $group->id;
            $numberofparticipants = count(groups_get_members($group->id));
            $relevantinformation[$key]['numberofparticipants'] = $numberofparticipants;
        }

        return $relevantinformation;
    }
}
