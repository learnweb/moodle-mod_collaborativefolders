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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require(__DIR__ . '/name_form.php');

// Page and parameter setup.
$id = required_param('id', PARAM_INT);
$reset = optional_param('reset', null, PARAM_RAW_TRIMMED);
$logout = optional_param('logout', null, PARAM_RAW_TRIMMED);
$generate = optional_param('generate', null, PARAM_RAW_TRIMMED);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'collaborativefolders');
$PAGE->set_url(new moodle_url('/mod/collaborativefolders/view.php', array('id' => $cm->id)));
require_login($course, true, $cm);

$userid = $USER->id;
$instance = $DB->get_record('collaborativefolders', array('id' => $cm->instance));


$renderer = $PAGE->get_renderer('mod_collaborativefolders');

// Initialize an OAuth 2.0 client and an owncloud_access object for user login and share generation.
$returnurl = new moodle_url('/mod/collaborativefolders/view.php', [
        'id' => $cm->id,
        'callback'  => 'yes',
        'sesskey'   => sesskey(),
]);

$sciebo = new \tool_oauth2sciebo\sciebo($returnurl);
$ocs = new \mod_collaborativefolders\owncloud_access();

$user_token = unserialize(get_user_preferences('oC_token'));
$sciebo->set_access_token($user_token);

if (!$sciebo->is_logged_in()) {

    set_user_preference('oC_token', null);
    $sciebo->callback();

}

if ($sciebo->is_logged_in()) {

    $tok = serialize($sciebo->get_accesstoken());
    set_user_preference('oC_token', $tok);

}

// Checks if the groupmode is on.
$gm = false;
$folderpath = $id;
$ingroup = null;

if(groups_get_activity_groupmode($cm) != 0) {
    $gm = true;
    $ingroup = groups_get_activity_group($cm);

    // If the groupmode is used and the current user is not a teacher, the folderpath is
    // extended by the group ID of the student.
    if ($ingroup != 0) {

        $folderpath .= '/' . $ingroup;

    }
}

if ($reset != null) {

    set_user_preference('cf_link ' . $instance->id . ' name', null);

}

if ($logout != null) {

    $sciebo->log_out();
    set_user_preference('oC_token', null);

}

$actionurl = new moodle_url('/mod/collaborativefolders/view.php?id=' . $cm->id);

// Get form data and check whether the submit button has been pressed.
$mform = new mod_collaborativefolders_name_form($actionurl, array(
        'namefield' => 'Beispiel'
));

if ($fromform = $mform->get_data()) {
    if (isset($fromform->enter)) {

        // If a name has been submitted, it gets stored in the user preferences.
        set_user_preference('cf_link ' . $instance->id . ' name', $fromform->namefield);

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

$PAGE->set_title(format_string($instance->name));
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

        $grid = $cm->groupingid;
        $groups = groups_get_all_groups($course->id, 0, $grid);
        $renderer->render_view_table($groups);

    }

    $allow = $instance->teacher;

    $access = has_capability('mod/collaborativefolders:addinstance', $context) && $allow == '1';

    // If the current user is a teacher who has access to the folder OR a student from the participating
    // grouping(s), he/she gains access to the folder.
    if ($access xor !has_capability('mod/collaborativefolders:addinstance', $context)) {

        // If the link is already stored, display it. Otherwise proceed.
        if (get_user_preferences('cf_link ' . $instance->id) == null) {

            // Since the user shall be able to choose an individual name for the folder, is has to be checked
            // if a name has been entered.
            if (get_user_preferences('cf_link ' . $instance->id . ' name') == null) {

                // If not, the concerning form is displayed.
                $mform->display();

            } else {

                if ($generate == null) {

                    $name = get_user_preferences('cf_link ' . $instance->id . ' name');
                    $reseturl = new moodle_url('/mod/collaborativefolders/view.php?id=' . $cm->id, [
                            'reset' => 'true'
                    ]);

                    echo $renderer->print_name_and_reset($name, $reseturl);

                }


                if ($sciebo->is_logged_in()) {

                    if ($generate != null) {

                        $user = $sciebo->get_accesstoken()->user_id;

                        $status = $ocs->generate_share('/' . $folderpath, $user);

                        if ($status) {

                            if ($gm && !has_capability('mod/collaborativefolders:addinstance', $context)) {

                                $folderpath = $ingroup;

                            }

                            $renamed = false;

                            if ($sciebo->dav->open()) {
                                $renamed = $sciebo->move($folderpath,
                                        get_user_preferences('cf_link ' . $instance->id . ' name'), false);
                            }

                            if ($renamed == 201) {

                                // After the folder having been renamed, a specific link has been generated, which is to
                                // be stored for each user individually.
                                $pref = get_config('tool_oauth2sciebo', 'type') . '://';

                                $p = str_replace('remote.php/webdav/', '', get_config('tool_oauth2sciebo', 'path'));

                                $link = $pref . get_config('tool_oauth2sciebo', 'server') . '/' . $p .
                                        'index.php/apps/files/?dir=' . '/' .
                                        get_user_preferences('cf_link ' . $instance->id . ' name');

                                set_user_preference('cf_link ' . $instance->id, $link);

                                // Display the Link.
                                echo $renderer->print_link($link, 'access');

                            } else {
                                $renderer->get_error('status');
                            }
                        } else {
                            $renderer->get_error('status');
                        }

                    } else {

                        // LOGOUT
                        $logouturl = new moodle_url('/mod/collaborativefolders/view.php?id=' . $cm->id, array(
                                'logout' => true));
                        echo $renderer->print_link($logouturl, 'logout');

                        // GENERATE
                        $genurl = new moodle_url('/mod/collaborativefolders/view.php?id=' . $cm->id, array(
                                'generate' => true));
                        echo $renderer->print_link($genurl, 'generate');

                    }

                } else {

                    // If no Access Token was received, a login link has to be provided.
                    $url = $sciebo->get_login_url();
                    echo html_writer::link($url, 'Login', array('target' => '_blank'));
                }
            }

        } else {

            // If the link is already saved within the user preferences, it only has to be displayed.
            $link = get_user_preferences('cf_link ' . $instance->id);

            echo $renderer->print_link($link, 'access');

        }
    } else {

        echo html_writer::div(get_string('notallowed', 'mod_collaborativefolders'));

    }
}

echo $renderer->create_footer();