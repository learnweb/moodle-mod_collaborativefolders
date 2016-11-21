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
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/repository/sciebo/lib.php');
require_once($CFG->dirroot.'/repository/sciebo/mywebdavlib.php');
require_once($CFG->dirroot.'/lib/setuplib.php');
///home/nina/Entwicklung/ps/psmoodle/lib/setuplib.php

/**
 * Module instance settings form
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_collaborativefolders_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $PAGE, $COURSE;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('collaborativefoldersname', 'collaborativefolders'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'collaborativefoldersname', 'collaborativefolders');

        $mywebdavclient = new sciebo_webdav_client('uni-muenster.sciebo.de', 'n_herr03@uni-muenster.de',
            'passwort', 'basic', 'ssl://');
        $mywebdavclient->port = 443;
        $mywebdavclient->path = 'remote.php/webdav/';
        $probepath = 'Folder';
        $mywebdavclient->open();
        $webdavpath = rtrim('/'.ltrim('remote.php/webdav/', '/ '), '/ ');
        $localpath = sprintf('%s/%s', make_request_directory(), 'Folder');
        $mywebdavclient->get_file($webdavpath . '/Photos/Paris.jpg', $localpath);
        $mywebdavclient->debug = false;
        echo '<p>';
        echo print_r($mywebdavclient->get_file($webdavpath . '/Photos/Paris.jpg', $localpath));
        echo '</p>';
        /*$webdav_type = 'ssl://';
        $webdav_port = 443;
        $webdav_path = 'remote.php/webdav/';
        $type = 'sciebo';
        $typeid = 9;
        $options['webdav_server'] = 'uni-muenster.sciebo.de';
        $port = ':443';
        $webdav_host = $webdav_type.$options['webdav_server'].$port;

        $context = context_course::instance($COURSE->id);

        $scieborepository = new repository_sciebo('9', $context);
        $scieborepository->webdav_path= 'remote.php/webdav/';
        $scieborepository->type = $type;
        $scieborepository->typeid = $typeid;
//        $scieborepository->instance =
        $scieborepository->instance->name = 'Sciebo';
        $scieborepository->instance->typeid = 9;
        $scieborepository->instance->repositorytype = 'sciebo';
        $scieborepository->instance->sortorder = 9;

        $scieborepository->webdav_type = $webdav_type;
        $scieborepository->webdav_port = $webdav_port;
        $scieborepository->options['webdav_server'] = $options['webdav_server'];
        $scieborepository->webdav_host = $webdav_host;

        $scieborepository->options['webdav_auth'] = 'basic';
        $scieborepository->dav = new sciebo_webdav_client($scieborepository->options['webdav_server'], $scieborepository->options['webdav_user'],
            $scieborepository->options['webdav_password'], $scieborepository->options['webdav_auth'], $scieborepository->webdav_type);
        $scieborepository->dav->port = $scieborepository->webdav_port;

        $scieborepository->dav->debug = false;
        $probepath = 'https://uni-muenster.sciebo.de/index.php/apps/files/Folder';
        $scieborepository->make_new_file('Folder', $probepath);*/
        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }
        $renderer = $PAGE->get_renderer('mod_collaborativefolders');
        $mform->addElement('header', 'groupmodus', get_string('createforall', 'collaborativefolders'));
        $arrayofgroups = $this->get_relevant_fields();
        $tableofallgroups = $renderer->render_table_of_existing_groups($arrayofgroups);
        $htmltableofgroups = html_writer::table($tableofallgroups);
        $mform->addElement('static', 'table', $htmltableofgroups);
        $mform->addElement('text', 'foldername', get_string('fieldsetgroups', 'collaborativefolders'));
        $mform->setType('foldername', PARAM_RAW_TRIMMED);
        $mform->addRule('foldername', null, 'required', null, 'client');
        $mform->addElement('url', 'externalurl', get_string('externalurl', 'collaborativefolders'), array('size' => '60'), array('usefilepicker' => true));
        $mform->setType('externalurl', PARAM_RAW_TRIMMED);
        $mform->addRule('externalurl', null, 'required', null, 'client');

//    TODO    More specific when link should be shared with groups
        /*$mform->addElement('header', 'groupmodus', get_string('fieldsetgroups', 'collaborativefolders'));

        foreach($arrayofgroups as $group) {
            $mform->addElement('static', 'table', $group['name']);
//            TODO only works if file picker works!
            $mform->addElement('url', 'externalurl', get_string('externalurl', 'collaborativefolders'), array('size' => '60'), array('usefilepicker' => true));
        }*/

        // TODO do we need Grades for colaborative Folders?
        $this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
    public function get_all_groups(){
        global $DB;
        //TODO for Performance reasons only get neccessary record
        return $DB->get_records('groups');
    }
    public function get_relevant_fields(){
        $allgroups = $this->get_all_groups();
        $relevantinformation = array();
        foreach($allgroups as $key => $group){
            $relevantinformation[$key]['name']= $group->name;
            $relevantinformation[$key]['id'] = $group->id;
            $numberofparticipants = count(groups_get_members($group->id));
            $relevantinformation[$key]['numberofparticipants'] = $numberofparticipants;
        }
        return $relevantinformation;

    }
}
