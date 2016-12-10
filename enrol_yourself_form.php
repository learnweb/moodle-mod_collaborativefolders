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
// namespace mod_collaborativefolders;
global $CFG;
require_once("$CFG->libdir/formslib.php");

defined('MOODLE_INTERNAL') || die();

class enrol_yourself_form extends moodleform {

    private $id;
    private $modid;

    public function __construct($id, $modid){
        $this->id = $id;
        $this->modid = $modid;
        parent::__construct();
    }

    public function definition() {
        $mform = $this->_form;
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $this->id);
        $mform->addElement('hidden', 'modid');
        $mform->setType('modid', PARAM_INT);
        $mform->setDefault('modid', $this->modid);
        $mform->addElement('text', 'name', get_string('kennung', 'collaborativefolders'), array('size' => '80'));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', get_string('maximumchars', '', 80), 'maxlength', 80, 'client');
        $mform->setDefault('name', get_string('addtosciebo', 'collaborativefolders'));
        $mform->addHelpButton('name','Sciebo-email','collaborativefolders');
        $this->add_action_buttons(true);
    }
    function validation($data, $files) {
        return array();
    }
    public function to_html() {
        $o = '';
        $o .= $this->_form->toHtml();
        return $o;
    }
}
