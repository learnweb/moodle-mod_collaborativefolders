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

// Page and parameter setup.
$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'collaborativefolders');
$PAGE->set_url(new moodle_url('/mod/collaborativefolders/view.php', array('id' => $cm->id)));
require_login($course, true, $cm);
$userid = $USER->id;

$renderer = $PAGE->get_renderer('mod_collaborativefolders');

// Test for groupmode.
$grm = groups_get_activity_groupmode($cm);
$grp = groups_get_activity_group($cm);
$alowed = groups_get_activity_allowed_groups($cm);

// Initialize an OAuth 2.0 client and an owncloud_access object for user login and share generation.
$returnurl = new moodle_url('/mod/collaborativefolders/view.php', [
        'id' => $cm->id,
        'callback'  => 'yes',
        'sesskey'   => sesskey(),
]);

$sciebo = new \tool_oauth2sciebo\sciebo($returnurl);
$ocs = new \mod_collaborativefolders\owncloud_access();

// If the user already logged in into his personal account, an authorization code is now available for upgrade.
$sciebo->callback();

// Checks if the groupmode is on.
$gm = $DB->get_records('collaborativefolders_group', array('modid' => $cm->instance), 'groupid');
$folderpath = '/' . $id;
$ingroup = null;

// If the groupmode is active and the current user is part of one of the chosen groups,
// his particular group id is saved and the folderpath in ownCloud extended by the group id.
if (!empty($gm)) {
    foreach ($gm as $modgroup) {
        if (groups_is_member($modgroup->groupid, $userid)) {
            $ingroup = $modgroup->groupid;
            $folderpath .= '/' . $ingroup;
            break;
        }
    }
}

// Checks if the adhoc task for the folder creation was successful.
$adhoc = $DB->get_records('task_adhoc', array('classname' => '\mod_collaborativefolders\task\collaborativefolders_create'));
// TODO: Single Create for every Folder?
$created = true;

foreach ($adhoc as $element) {

    $content = json_decode($element->customdata);
    $cmid = $content->cmid;

    if ($id == $cmid) {
        $created = false;
    }

}

// The DB entry for the current activity instance is needed for page information.
$instance = $DB->get_record('collaborativefolders', array('id' => $cm->instance));

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
    if (has_capability('mod/collaborativefolders:addinstance', $context)) {
        // If folders were created for separate groups, those groups are shown in a table.
        if (!empty($gm)) {

            $groupinformation = array();

            foreach ($gm as $key => $teachergroup) {

                $fullgroup = groups_get_group($teachergroup->groupid);
                $participants = count(groups_get_members($teachergroup->groupid));
                $row['name'] = $fullgroup->name;
                $row['numberofparticipants'] = $participants;
                $groupinformation[$key] = $row;

            }

            echo $renderer->render_view_table($groupinformation);
            
        }

        $allow = $instance->teacher;

        // If the checkbox for teachers' access to the folders was checked, a link to the folder is generated.
        if ($allow == '1') {

            if (get_user_preferences('cf_link ' . $instance->id) == null) {

                if ($sciebo->is_logged_in()) {

                    $folderpath = '/' . $id;

                    $user = $sciebo->get_accesstoken()->user_id;

                    $status = $ocs->generate_share($folderpath, $user);

                    if ($status) {

                        $pref = get_config('tool_oauth2sciebo', 'type') . '://';

                        $p = str_replace('remote.php/webdav/', '', get_config('tool_oauth2sciebo', 'path'));

                        $link = $pref . get_config('tool_oauth2sciebo', 'server') . '/' . $p .
                                'index.php/apps/files/?dir=' . $folderpath;

                        set_user_preference('cf_link ' . $instance->id, $link);

                        // Display the Link.
                        echo $renderer->loggedin_generate_share($link);

                    } else {
                        $problem = 'status';
                        echo $renderer->get_error($problem);
                    }

                } else {

                    // If no Access Token was received, a login link has to be provided.
                    $url = $sciebo->get_login_url();
                    echo html_writer::link($url, 'Login', array('target' => '_blank'));

                }

            } else {

                // If the link is already saved within the user preferences, if only has to be displayed.
                $link = get_user_preferences('cf_link ' . $instance->id);

                echo $renderer->loggedin_generate_share($link);

            }

        } else {

            echo html_writer::div(get_string('notallowed', 'mod_collaborativefolders'));

        }

    } else {

        // If the current user is a student, it has to be checked whether the groumode is active and
        // if the user is part of one of the groups.
        if (($ingroup == null) && ($gm != null)) {

            echo html_writer::div(get_string('notallowed', 'mod_collaborativefolders'));

        } else {

            // If this is the case, the link to the specific folder is generated.
            if (get_user_preferences('cf_link ' . $instance->id) == null) {

                if ($sciebo->is_logged_in()) {

                    $user = $sciebo->get_accesstoken()->user_id;

                    $status = $ocs->generate_share($folderpath, $user);

                    if ($status) {

                        $pref = get_config('tool_oauth2sciebo', 'type') . '://';

                        $p = str_replace('remote.php/webdav/', '', get_config('tool_oauth2sciebo', 'path'));

                        if($ingroup != null) {
                            $link = $pref . get_config('tool_oauth2sciebo', 'server') . '/' . $p .
                                'index.php/apps/files/?dir=' . '/' . $ingroup;
                        } else {
                            $link = $pref . get_config('tool_oauth2sciebo', 'server') . '/' . $p .
                                'index.php/apps/files/?dir=' . '/' . $id;
                        }

                        set_user_preference('cf_link ' . $instance->id, $link);

                        // Display the Link.
                        echo $renderer->loggedin_generate_share($link);

                    } else {
                        $renderer->get_error('status');
                    }

                } else {

                    // If no Access Token was received, a login link has to be provided.
                    $url = $sciebo->get_login_url();
                    echo html_writer::link($url, 'Login', array('target' => '_blank'));

                }

            } else {

                // If the link is already saved within the user preferences, if only has to be displayed.
                $link = get_user_preferences('cf_link ' . $instance->id);

                echo $renderer->loggedin_generate_share($link);

            }
        }
    }
}

echo $renderer->create_footer();