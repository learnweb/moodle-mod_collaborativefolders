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
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

// Page and parameter setup.
$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'collaborativefolders');
$PAGE->set_url(new moodle_url('/mod/collaborativefolders/view.php', array('id' => $id)));
$context = context_module::instance($id);
$capteacher = has_capability('mod/collaborativefolders:viewteacher', $context);
$capstudent = has_capability('mod/collaborativefolders:viewstudent', $context);
$instanceid = $cm->instance;

// Indicators for name reset, logout from current ownCloud user and link generation.
$reset = optional_param('reset', false, PARAM_BOOL);
$logout = optional_param('logout', false, PARAM_BOOL);
$generate = optional_param('generate', false, PARAM_BOOL);


// User needs to be logged in to proceed.
require_login($course, true, $cm);


// If the user does not have the permission to view this activity instance,
// he gets redirected.
require_capability('mod/collaborativefolders:view', $context);


// The owncloud_access object will be used to access both, the technical and the current user.
// The return URL leads back to the current page.
$returnurl = new moodle_url('/mod/collaborativefolders/view.php', [
        'id' => $id,
        'callback'  => 'yes',
        'sesskey'   => sesskey(),
]);

$ocs = new \mod_collaborativefolders\owncloud_access($returnurl);


// If the reset link was used, the chosen foldername is reset.
if ($reset == true) {
    $record = array();
    $record['userid'] = $USER->id;
    $record['cmid'] = $cm->id;
    $record['link'] = null;
    if ($DB->record_exists('collaborativefolders_link', array('userid' => $USER->id, 'cmid' => $cm->id))) {
        $DB->update_record('collaborativefolders_link', $record);
    }
    set_user_preference('cf_link ' . $id . ' name', null);
    redirect(qualified_me(), get_string('resetpressed', 'mod_collaborativefolders'));
}

// If the user wishes to logout from his current ownCloud account, his Access Token is
// set to null and so is the client's.
if ($logout == true) {
    $ocs->logout_user();
    redirect(qualified_me(), get_string('logoutpressed', 'mod_collaborativefolders'));
}

// Get form data and check whether the submit button has been pressed.
$mform = new mod_collaborativefolders\name_form(qualified_me(), array('namefield' => $cm->name));

if ($fromform = $mform->get_data()) {
    if (isset($fromform->enter)) {
        $record = array();
        $record['userid'] = $USER->id;
        $record['cmid'] = $cm->id;
        $record['link'] = $fromform->namefield;
        if ($DB->record_exists('collaborativefolders_link', array('userid' => $USER->id, 'cmid' => $cm->id))) {
            $DB->update_record('collaborativefolders_link', $record);
        } else {
            $DB->insert_record('collaborativefolders_link', $record);
        }
        // If a name has been submitted, it gets stored in the user preferences.
        set_user_preference('cf_link ' . $id . ' name', $fromform->namefield);
    }
}


// Indicates, whether the teacher is allowed to have access to the folder or not.
$paramsteacher = array('id' => $instanceid);
$teacherallowed = $DB->get_record('collaborativefolders', $paramsteacher)->teacher;


// Renderer is initialized.
$renderer = $PAGE->get_renderer('mod_collaborativefolders');


// Indicator for groupmode.
$gm = false;

// Two separate paths are needed, one for the share and another to rename the shared folder.
$sharepath = '/' . $id;
$finalpath = $sharepath;

// Tells, which group the current user is part of.
$ingroup = null;

// Checks if the groupmode is active. Does not differentiate between VISIBLE and SEPERATE.
if (groups_get_activity_groupmode($cm) != 0) {
    // If the groupmode is set by the creator, $gm is set to true.
    $gm = true;
    // If the current user is a student and participates in one ore more of the chosen
    // groups, $ingroup is set to the group, which was created first.
    $ingroup = groups_get_activity_group($cm);

    // If a groupmode is used and the current user is not a teacher, the sharepath is
    // extended by the group ID of the student.
    // The path for renaming the folder, becomes the group ID, because only the groupfolder
    // is shared with the concerning user.
    if ($ingroup != 0) {

        $sharepath .= '/' . $ingroup;
        $finalpath = '/' . $ingroup;
    }
}


// Checks if the adhoc task for the folder creation was successful.
$adhoc = $DB->get_records('task_adhoc', array('classname' => '\mod_collaborativefolders\task\collaborativefolders_create'));
$folderscreated = true;

foreach ($adhoc as $element) {

    $content = json_decode($element->customdata);
    $cmidoftask = $content->cmid;

    // As long as at least one ad-hoc task exist, that has the same cm->id as the current cm the folders were not created.
    if ($id == $cmidoftask) {
        $folderscreated = false;
    }
}


// Set up further page properties and print the header and heading for this page.
$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('activityoverview', 'mod_collaborativefolders'));


// If the folders are not created yet, display the concerning message to the user.
if (!$folderscreated) {

    $output = '';
    $output .= html_writer::div(get_string('foldernotcreatedyet', 'mod_collaborativefolders'));
    echo $output;
}


// Does the teacher have access to this activity?
$teacheraccess = $capteacher && $teacherallowed == true;

// Does the current user have access to this activity, be it teacher or student?
$hasaccess = ($teacheraccess || $capstudent) && $folderscreated;


// Does a table of all participating groups have to be shown? The teacher does not need to have
// access to the Collaborative Folder to see the table.
$showtable = $folderscreated && $capteacher && $gm;

// If the folders are created, the current user is a teacher and the groupmode is active,
// show a table of all participating groups.
if ($showtable) {

    $grid = $cm->groupingid;
    $groups = groups_get_all_groups($course->id, 0, $grid);
    echo $renderer->render_view_table($groups);
}


// Fetch a stored link belonging to this particular activity instance.
if ($DB->record_exists('collaborativefolders_link', array('userid' => $USER->id, 'cmid' => $cm->id))) {
    $newlink = $DB->get_field('collaborativefolders_link', 'link', array('userid' => $USER->id, 'cmid' => $cm->id));
}
$privatelink = get_user_preferences('cf_link ' . $id);

// Does the user have a link to this Collaborative Folder and access to this activity?
$haslink = $privatelink != null && $hasaccess;

// If the current user already received a link to the Collaborative Folder, display it.
if ($haslink) {

    echo $renderer->print_link($privatelink, 'access');
}


// The name of the folder, chosen by the user.
$name = get_user_preferences('cf_link ' . $id . ' name');

// Does the user have access but no link has been stored yet?
$nolink = $hasaccess && $privatelink == null;

// Does the user wish to generate a link and has not already stored one?
$cangenerate = $nolink && $generate;

if ($cangenerate) {

    // If no personal name has been stored for the folder, no link can be generated yet.
    if ($name == null) {

        $generate = false;
    } else {

        // Otherwise, try to share and rename the folder.
        $sharerename = $ocs->share_and_rename($sharepath, $finalpath, $id);

        // Check, if the sharing and renaming operations were successful.
        if ($sharerename['status'] === true) {

            // Display the Link.
            echo $renderer->print_link($sharerename['content'], 'access');

            // Event data is gathered.
            $params = array(
                    'context'  => $context,
                    'objectid' => $instanceid
            );

            // And the link_generated event is triggered.
            $generatedevent = \mod_collaborativefolders\event\link_generated::create($params);
            $generatedevent->trigger();
        } else {

            // Share or rename were unsuccessful.
            echo $renderer->print_error($sharerename['type'], $sharerename['content']);
        }
    }
}


// No link has been stored yet and no order to generate one has been received.
$nogenerate = $nolink && !$generate;

if ($nogenerate) {

    // If the user already has set a name for the folder, proceed.
    if ($name != null) {

        // A reset parameter has to be passed on redirection.
        $reseturl = qualified_me() . '&reset=1';
        echo $renderer->print_name_and_reset($name, $reseturl);

        if ($ocs->user_loggedin()) {

            // Print the logout text and link.
            $logouturl = qualified_me() . '&logout=1';
            echo $renderer->print_link($logouturl, 'logout');

            $genurl = qualified_me() . '&generate=1';
            echo $renderer->print_link($genurl, 'generate');
        } else {

            // If no Access Token was received, a login link has to be provided.
            $url = $ocs->get_login_url();
            echo html_writer::link($url, 'Login', array('target' => '_blank', 'rel' => 'noopener noreferrer'));
        }
    } else {

        // Otherwise, show a form for the user to enter a name into.
        $mform->display();
    }
}


$params = array(
        'context' => $context,
        'objectid' => $instanceid
);

$cmviewed = \mod_collaborativefolders\event\course_module_viewed::create($params);
$cmviewed->trigger();


echo $OUTPUT->footer();