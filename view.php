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
$PAGE->set_url(new moodle_url('/mod/collaborativefolders/view.php', array('id' => $cm->id)));

// Indicators for name reset, logout from current ownCloud user and link generation.
$reset = optional_param('reset', null, PARAM_RAW_TRIMMED);
$logout = optional_param('logout', null, PARAM_RAW_TRIMMED);
$generate = optional_param('generate', null, PARAM_RAW_TRIMMED);

// User needs to be logged in to proceed.
require_login($course, true, $cm);

// Indicates, whether the teacher is allowed to have access to the folder or not.
$teacher = $DB->get_record('collaborativefolders', array('id' => $cm->instance))->teacher;

// Renderer is initialized.
$renderer = $PAGE->get_renderer('mod_collaborativefolders');


// The owncloud_access object will be used to access both, the technical and the current user.
// The return URL leads back to the current page.
$returnurl = new moodle_url('/mod/collaborativefolders/view.php', [
        'id' => $cm->id,
        'callback'  => 'yes',
        'sesskey'   => sesskey(),
]);

$ocs = new \mod_collaborativefolders\owncloud_access($returnurl);


// Checks if the groupmode is active. Does not differentiate between VISIBLE and SEPERATE.
$gm = false;
$folderpath = '/' . $id;
$ingroup = null;

if (groups_get_activity_groupmode($cm) != 0) {
    $gm = true;
    $ingroup = groups_get_activity_group($cm);

    // If the groupmode is used and the current user is not a teacher, the folderpath is
    // extended by the group ID of the student.
    if ($ingroup != 0) {

        $folderpath .= '/' . $ingroup;
    }
}


// If the reset link was used, the chosen foldername is reset.
if ($reset != null) {
    set_user_preference('cf_link ' . $id . ' name', null);
}

// If the user wishes to logout from his current ownCloud account, his Access Token is
// set to null and so is the client's.
if ($logout != null) {

    $ocs->owncloud->log_out();
    set_user_preference('oC_token', null);
}


// The action URL for the form needs to be specified, because otherwise the CMID would be
// missing.
$actionurl = new moodle_url('/mod/collaborativefolders/view.php?id=' . $cm->id);

// Get form data and check whether the submit button has been pressed.
$mform = new mod_collaborativefolders\name_form($actionurl, array(
        'namefield' => $cm->name
));

if ($fromform = $mform->get_data()) {
    if (isset($fromform->enter)) {

        // If a name has been submitted, it gets stored in the user preferences.
        set_user_preference('cf_link ' . $id . ' name', $fromform->namefield);
    }
}


// Checks if the adhoc task for the folder creation was successful.
$adhoc = $DB->get_records('task_adhoc', array('classname' => '\mod_collaborativefolders\task\collaborativefolders_create'));
$created = true;

foreach ($adhoc as $element) {

    $content = json_decode($element->customdata);
    $cmid = $content->cmid;

    if ($id == $cmid) {
        $created = false;
    }
}


$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading(format_string($course->fullname));
echo $renderer->create_header('Overview of Collaborativefolders Activity');


// If the folders were not created successfully, an error message has to be printed.
if (!$created) {

    $output = '';
    $output .= html_writer::div(get_string('foldercouldnotbecreated', 'mod_collaborativefolders'));
    echo $output;

} else {

    $context = context_module::instance($cm->id);

    // It has to be checked, if the current user is a teacher.
    if (has_capability('mod/collaborativefolders:addinstance', $context) && $gm) {

        // If the current user is a teacher, a table of all participating groups
        // should be printed.
        $grid = $cm->groupingid;
        $groups = groups_get_all_groups($course->id, 0, $grid);
        $renderer->render_view_table($groups);
    }

    $access = has_capability('mod/collaborativefolders:addinstance', $context) && $teacher == '1';

    // If the current user is a teacher who has access to the folder OR a student from the participating
    // grouping(s), he/she gains access to the folder.
    if ($access xor !has_capability('mod/collaborativefolders:addinstance', $context)) {

        // If the link is already stored, display it. Otherwise proceed.
        if (get_user_preferences('cf_link ' . $id) == null) {

            // If any client data is missing, we cannot proceed.
            if ($ocs->owncloud->check_data()) {

                // Since the user shall be able to choose an individual name for the folder, is has to be checked
                // if a name has been entered.
                if (get_user_preferences('cf_link ' . $id . ' name') == null) {

                    // If not, the concerning form is displayed.
                    $mform->display();

                } else {

                    // If the generation link was not used, the user still has the possibility to view his chosen
                    // name and to reset it.
                    if ($generate == null) {

                        $name = get_user_preferences('cf_link ' . $id . ' name');
                        // A reset parameter has to be passed on redirection.
                        $reseturl = new moodle_url('/mod/collaborativefolders/view.php?id=' . $cm->id, [
                                'reset' => 'true'
                        ]);

                        echo $renderer->print_name_and_reset($name, $reseturl);
                    }

                    // Now the login status of the user has to be checked. Therefore the owncloud_access
                    // object's owncloud object gets called without the $technical parameter.
                    if ($ocs->owncloud->check_login()) {

                        // If the user used the generation link, proceed.
                        if ($generate != null) {

                            // First, the ownCloud user ID is fetched from the current user's Access Token.
                            $user = $ocs->owncloud->get_accesstoken()->user_id;
                            // Thereafter, a share for this specific user can be created with the technical user and
                            // his Access Token.
                            $status = $ocs->generate_share($folderpath, $user);

                            // If the process was successful, try to rename the folder.
                            if ($status) {

                                // The folderpath needs to be adjusted to the path of the shared folder.
                                // E.g. 1/2 becomes 2, bacause only 2 was shared with the user.
                                if ($gm && !has_capability('mod/collaborativefolders:addinstance', $context)) {
                                    $folderpath = '/' . $ingroup;
                                }

                                $renamed = false;
                                // The Access Token of the user needs to be switched to the ownCloud client, first.
                                $ocs->owncloud->check_login();
                                if ($ocs->owncloud->open()) {
                                    // After the socket's opening, the WebDAV MOVE method has to be performed in
                                    // order to rename the folder.
                                    $renamed = $ocs->owncloud->move($folderpath, '/' .
                                            get_user_preferences('cf_link ' . $id . ' name'), false);
                                }

                                if ($renamed == 201) {

                                    // After the folder having been renamed, a specific link has been generated, which is to
                                    // be stored for each user individually.
                                    $pref = get_config('tool_oauth2owncloud', 'protocol') . '://';

                                    $p = str_replace('remote.php/webdav/', '', get_config('tool_oauth2owncloud', 'path'));

                                    $link = $pref . get_config('tool_oauth2owncloud', 'server') . '/' . $p .
                                            'index.php/apps/files/?dir=' . '/' .
                                            get_user_preferences('cf_link ' . $id . ' name');

                                    set_user_preference('cf_link ' . $id, $link);

                                    // Display the Link.
                                    echo $renderer->print_link($link, 'access');

                                    // Event data is gathered.
                                    $params = array(
                                            'context'  => context_module::instance($cm->id),
                                            'objectid' => $cm->instance
                                    );

                                    // And the link_generated event is triggered.
                                    $generatedevent = \mod_collaborativefolders\event\link_generated::create($params);
                                    $generatedevent->trigger();

                                } else {
                                    // MOVE was unsuccessful.
                                    echo $renderer->print_error('renamed', $renamed);
                                }
                            } else {
                                // The share was unsuccessful.
                                $renderer->print_error('shared', $status);
                            }

                        } else {

                            // Print the logout text and link.
                            $logouturl = new moodle_url('/mod/collaborativefolders/view.php?id=' . $cm->id, array(
                                    'logout' => true));
                            echo $renderer->print_link($logouturl, 'logout');

                            // Print the generation text and link.
                            $genurl = new moodle_url('/mod/collaborativefolders/view.php?id=' . $cm->id, array(
                                    'generate' => true));
                            echo $renderer->print_link($genurl, 'generate');
                        }

                    } else {

                        // If no Access Token was received, a login link has to be provided.
                        $url = $ocs->owncloud->get_login_url();
                        echo html_writer::link($url, 'Login', array('target' => '_blank', 'rel' => 'noopener noreferrer'));
                    }
                }
            }

        } else {

            // If the link is already saved within the user preferences, it only has to be displayed.
            $link = get_user_preferences('cf_link ' . $id);

            echo $renderer->print_link($link, 'access');
        }
    } else {
        // The current user has no access to the folder.
        echo html_writer::div(get_string('notallowed', 'mod_collaborativefolders'));
    }
}

$params = array(
        'context' => context_module::instance($cm->id),
        'objectid' => $cm->instance
);

$cmviewed = \mod_collaborativefolders\event\course_module_viewed::create($params);
$cmviewed->trigger();

echo $renderer->create_footer();