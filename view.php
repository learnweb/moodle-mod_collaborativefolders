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

// The renderer is not user to its full potential yet (for testing reasons).
$renderer = $PAGE->get_renderer('mod_collaborativefolders');

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


// Check whether the groupmode is on and calculate the path to the folder.

// A new table has to be found, which does not get deleted, when the plugin is uninstalled.
$instance = $DB->get_record('collaborativefolders', array('id' => $cm->instance), '*', MUST_EXIST);

$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));

// Checks if the groupmode is on.
// If no group id is given to a modid, it must mean, that this instance was made without any groups.
// Hence the groupmode must have been inactive.
$groupmode = $DB->get_record('collaborativefolders_group', array('modid' => $instance->id), 'groupid')->groupid;

$folderpath = '/' . $instance->id;
$ingroup = null;

// If the groupmode is active and the current user is part of one of the chosen groups,
// his particular group id is saved and the folderpath in ownCloud extended by the group id.
if ($groupmode != null) {
    $groups = $DB->get_records('collaborativefolders_group', array('modid' => $instance->id));
    foreach ($groups as $modgroup) {
        if (groups_is_member($modgroup->groupid, $userid)) {
            $ingroup = $modgroup->groupid;
            $folderpath .= '/' . $ingroup;
        }
    }
}


// Decide, what has to be shown to the user depending on multiple parameters.

echo $renderer->create_header('Overview of Collaborativefolders Activity');

$context = context_module::instance($cm->id);

if(has_capability('mod/collaborativefolders:addinstance', $context)) {
    if ($groupmode != null) {
        $teachergroups = $DB->get_records('collaborativefolders_group', array('modid' => $instance->id), 'groupid');
        $groupinformation = array();
        foreach ($teachergroups as $key => $teachergroup) {
            $fullgroup = groups_get_group($teachergroup->groupid);
            $participants = count(groups_get_members($teachergroup->groupid));
            $row['name'] = $fullgroup->name;
            $row['numberofparticipants'] = $participants;
            // TODO when OC API is available.
            $row['linktofolder'] = html_writer::link('not.yet.implemented', 'not.yet.implementet');
            $groupinformation[$key] = $row;
        }
        echo $renderer->render_view_table($groupinformation);
    }
    if ($groupmode == null) {
        echo html_writer::div(get_string('infotextnogroups', 'mod_collaborativefolders'));
    }

}
echo html_writer::div('<h3>' .'Acces to Folder' . '</h3>', 'header');
// If the groupmode is active but the current user is not part of one of the chosen groups,
// a default dialog is shown on this page.
if (($ingroup == null) && ($groupmode != null)) {
    echo html_writer::div(get_string('notallowed', 'mod_collaborativefolders'));
} else {

    // The link to the folder is saved in the user specific settings.
    // Thus, when the link is not set there, a share has to be created for the user
    // and the link fetched from the server.
    if (get_user_preferences('cf_link' . $instance->id) == null) {

        // If the user logged in via OAuth 2.0, he possess an Access Token. Hes specific username in ownCloud
        // can then be fetched from the token and a share can be created for exactly this user.
        if ($sciebo->is_logged_in()) {

            $user = $sciebo->get_accesstoken()->token;
            $link = $ocs->generate_share($folderpath, $user);
            set_user_preference('cf_link' . $instance->id, $link);

            // Display the Link.
            global $OUTPUT;

            $output = '';

            $output .= $OUTPUT->heading('Link to collaborative Folder');
            $output .= html_writer::div(get_string('downloadfolder', 'mod_collaborativefolders',
                    html_writer::link($link . '&download', 'hier')));
            $output .= html_writer::div(' ');
            $output .= html_writer::div(get_string('accessfolder', 'mod_collaborativefolders',
                    html_writer::link($link, 'hier')));
            echo $output;

        } else {

            // If no Access Token was received, a login link has to be provided.
            $url = $sciebo->get_login_url();
            echo html_writer::link($url, 'Login', array('target' => '_blank'));

        }
    } else {

        // If the link is already saved within the user preferences, if only has to be displayed.
        $link = get_user_preferences('cf_link' . $instance->id);

        $output = $OUTPUT->heading('Link to collaborative Folder');
        $output .= html_writer::div(get_string('downloadfolder', 'mod_collaborativefolders',
                html_writer::link($link . '&download', 'hier')));
        $output .= html_writer::div(' ');
        $output .= html_writer::div(get_string('accessfolder', 'mod_collaborativefolders',
                html_writer::link($link, 'hier')));
        echo $output;

    }
}

echo $renderer->create_footer();