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
//

/**
 * Controller for view.php
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_collaborativefolders;

use core\output\notification;
use mod_collaborativefolders\local\clients\system_folder_access;
use mod_collaborativefolders\local\clients\user_folder_access;
use mod_collaborativefolders\output\statusinfo;
use mod_collaborativefolders_renderer;

defined('MOODLE_INTERNAL') || die();

/**
 * Controller for view.php
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_controller {
    /**
     * Handle an incoming request
     *
     * @param \stdClass $collaborativefolder Collaborativefolder instance
     * @param \cm_info $cm Corresponding course module
     * @param \context_module $context Module context
     * @param mod_collaborativefolders_renderer $renderer Plugin-specific renderer
     */
    public static function handle_request($collaborativefolder, \cm_info $cm, \context_module $context,
                                          mod_collaborativefolders_renderer $renderer) {
        global $OUTPUT, $USER;

        \mod_collaborativefolders\toolbox::coursemodule_viewed($context, $cm);

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('activityoverview', 'mod_collaborativefolders'));
        if (!empty($collaborativefolder->intro)) {
            echo $OUTPUT->box(format_module_intro('collaborativefolder', $collaborativefolder, $cm->id), 'generalbox', 'intro');
        }

        // Check whether viewer is considered as non-student, because their access may be restricted. Admin override is ignored.
        // Note: This is a restriction, not a capability. Don't use for deciding what someone MAY do, consider as MAY NOT instead.
        $isteacher = has_capability('mod/collaborativefolders:isteacher', $context, null, false);

        $statusinfo = self::get_instance_status($collaborativefolder, $cm, $isteacher);
        $userclient = new user_folder_access(new \moodle_url('/mod/collaborativefolders/authorise.php', [
                'action' => 'login',
                'id' => $cm->id,
                'sesskey' => sesskey()
            ])
        );
        try {
            $systemclient = new system_folder_access();
        } catch (configuration_exception $e) {
            $systemclient = null;
        }

        // Start output.

        // Show notice if there is a general problem with the system account.
        // Show to someone who can add/configure this instance (i.e. teachers).
        if ($systemclient === null && has_capability('mod/collaborativefolders:addinstance', $context)) {
            echo $renderer->render_widget_noconnection();
        }

        // Show status info table.
        echo $renderer->render($statusinfo);

        // Login / logout form.
        echo $OUTPUT->heading('@remote system', 3);
        if ($userclient->check_login()) {
            echo $renderer->render(new \single_button(
                new \moodle_url('/mod/collaborativefolders/authorise.php', [
                    'action' => 'logout',
                    'id' => $cm->id,
                    'sesskey' => sesskey()
                ]), '@logout'));
        } else {
            echo $renderer->render_widget_login($userclient->get_login_url());
        }

        // Interaction with instance.
        if ($userclient->check_login()) {
            echo $OUTPUT->heading('@access', 3);
            if ($statusinfo->creationstatus === 'created') {
                if ($isteacher) {
                    echo self::view_folder_teacher($statusinfo, $userclient, $renderer, $cm);
                } else {
                    echo self::view_folders_student($statusinfo, $userclient, $renderer, $cm);
                }
            } else {
                // Folders are not yet created and can therefore not be shared.
                echo $renderer->render_widget_notcreatedyet();
            }
        }

        echo $OUTPUT->footer();
    }

    /**
     * Aggregate information about the current instance. Focus on information that is relevant to the current user.
     *
     * @param \stdClass $collaborativefolder Collaborativefolder instance
     * @param \cm_info $cm Corresponding course module
     * @param bool $asteacher Assume a teacher's perspective (additional output).
     * @return output\statusinfo
     */
    private static function get_instance_status($collaborativefolder, \cm_info $cm, $asteacher) {
        global $USER;

        // Check if folders are per-group.
        $groupmode = groups_get_activity_groupmode($cm) != 0;

        $groups = array();
        // If in groupmode, find out which groups are relevant (own groups, except if teacher, then all groups).
        if ($groupmode) {
            if ($asteacher) {
                $groups = groups_get_all_groups($cm->course, 0, $cm->groupingid);
            } else {
                $groups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid);
            }
        }

        // Check the state of asynchronous folder creation.
        $creationstatus = \mod_collaborativefolders\toolbox::is_create_task_running($cm->id) ? 'pending' : 'created';

        // Determine whether teachers may also access the share.
        $teachermayaccess = $collaborativefolder->teacher;

        return new statusinfo($creationstatus, $teachermayaccess, $groupmode, $groups);
    }

    /**
     * Render the view that is used for interactions with folders. Per applicable folder:
     * # Defining a user-local name and generating a share
     * # Display the selected name, a link, and a button for problem solving (aka re-share).
     *
     * @param statusinfo $statusinfo
     * @param user_folder_access $userclient
     * @param mod_collaborativefolders_renderer $renderer
     * @return string Rendered view
     */
    private static function view_folders_student(statusinfo $statusinfo, user_folder_access $userclient,
                                                 mod_collaborativefolders_renderer $renderer, \cm_info $cm) {
        // Get applicable groups from $statusinfo.
        $folders = array();
        if (!$statusinfo->groupmode) {
            $folders = [0 => 'coursemodule-root']; // TODO Might change.
        } else {
            // One folder per applicable group.
            $folders = $statusinfo->groups;
        }

        // Per group: Either define user-local name or access share.
        foreach ($folders as $f) {
            // TODO Define name.
            // TODO Access share.
            var_dump($f);
        }
    }

    /**
     * Render the view that is used for interactions with a folder as a teacher.
     * # Defining a user-local name and generating a share
     * # Display the selected name, a link, and a button for problem solving (aka re-share).
     *
     * @param statusinfo $statusinfo
     * @param user_folder_access $userclient
     * @param mod_collaborativefolders_renderer $renderer
     * @return string Rendered view
     */
    private static function view_folder_teacher(statusinfo $statusinfo, user_folder_access $userclient,
                                                mod_collaborativefolders_renderer $renderer, \cm_info $cm) {
        if (!$statusinfo->teachermayaccess) {
            echo $renderer->render_widget_teachermaynotaccess();
            return;
        }

        // TODO Define name / access share.
    }
}