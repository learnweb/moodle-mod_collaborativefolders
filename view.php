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
 * Prints a particular instance of collaborativefolders
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot.'/mod/collaborativefolders/handleform.php');

$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'collaborativefolders');
$collaborativefolders = $DB->get_record('collaborativefolders', array('id'=> $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url(new moodle_url('/mod/collaborativefolders/view.php', array('id' => $cm->id)));

$formhandler = new handleform();
$myform = $formhandler->handle_my_form($cm->id);

// TODO does not work yet : Coding error detected, it must be fixed by a programmer: The course_module_viewed event must define objectid and object table.

/*$event = \mod_collaborativefolders\event\course_module_viewed::create(array(
        'objectid' => 18,
        'context' => context_module::instance(123),
    ));
/*    $event->add_record_snapshot('course', $PAGE->course);
    $event->add_record_snapshot($PAGE->cm->modname, $collaborativefolders);*/
    /*$event->trigger();*/

// Print the page header.


    $PAGE->set_title(format_string($collaborativefolders->name));
    $PAGE->set_heading(format_string($course->fullname));
//    $PAGE->set_cacheable(false);

    /*
     * Other things you may want to set - remove if not needed.
     * $PAGE->set_cacheable(false);
     * $PAGE->set_focuscontrol('some-html-id');
     * $PAGE->add_body_class('collaborativefolders-'.$somevar);
     */
    $renderer = $PAGE->get_renderer('mod_collaborativefolders');
    echo $renderer->create_header();
    // Output starts here.


    // Conditions to show the intro can change to look for own settings or whatever.
 /*   if ($collaborativefolders->intro) {
        echo $OUTPUT->box(format_module_intro('collaborativefolders', $collaborativefolders, $cm->id), 'generalbox mod_introbox', 'collaborativefoldersintro');
    }*/

    $myinstance = $DB->get_record('collaborativefolders', array('id' => $cm->id));
    echo $renderer->render_view_page($collaborativefolders->externalurl, $cm->id);

// Finish the page.
    echo $renderer->create_footer();


