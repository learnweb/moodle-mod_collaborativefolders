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
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_collaborativefolders\handleform;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'collaborativefolders');
$collaborativefolders = $DB->get_record('collaborativefolders', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url(new moodle_url('/mod/collaborativefolders/view.php', array('id' => $cm->id)));

$userid = $USER->id;
$arrayofgroups = groups_get_user_groups($course->id, $userid);
$groupmode = $DB->get_records('collaborativefolders_group', array('modid' => $collaborativefolders->id));

$shouldsee = false;
if (!empty($groupmode) && !empty($arrayofgroups[0])) {
    foreach ($groupmode as $modgroup) {
        foreach ($arrayofgroups[0] as $key => $membergroup) {
            if ($modgroup->id == $membergroup) {
                $shouldsee = true;
                break;
            }
        }
        if ($shouldsee == true) {
            break;
        }
    }
}

if (empty($groupmode)) {
    $shouldsee = true;
}

$PAGE->set_title(format_string($collaborativefolders->name));
$PAGE->set_heading(format_string($course->fullname));

$renderer = $PAGE->get_renderer('mod_collaborativefolders');
echo $renderer->create_header();

$myinstance = $DB->get_record('collaborativefolders', array('id' => $cm->id));
$availability = $shouldsee;
echo $renderer->render_view_page($collaborativefolders->externalurl, $cm->id, $collaborativefolders->id, $availability);

$formhandler = new handleform();
$myform = $formhandler->handle_my_form($cm->id, $collaborativefolders->id);
// Finish the page.
echo $renderer->create_footer();


