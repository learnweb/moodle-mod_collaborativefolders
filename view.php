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
 * Prints a particular instance of collaborativefolders. What is shown depends
 * on the current user.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// Page and parameter setup.
$cmid = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'collaborativefolders');
require_login($course, false, $cm);
$context = context_module::instance($cmid);
$collaborativefolder = $DB->get_record('collaborativefolders', ['id' => $cm->instance]);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/mod/collaborativefolders/view.php', array('id' => $cmid)));
$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading(format_string($course->fullname));

require_capability('mod/collaborativefolders:view', $context);

\mod_collaborativefolders\view_controller::handle_request(
    $collaborativefolder, $cm, $context, $PAGE->get_renderer('mod_collaborativefolders'));