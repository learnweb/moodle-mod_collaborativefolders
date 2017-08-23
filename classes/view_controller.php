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
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('activityoverview', 'mod_collaborativefolders'));

        // Check whether viewer is considered as non-student, because their access may be restricted. Admin override is ignored.
        $isteacher = has_capability('mod/collaborativefolders:isteacher', $context, false);

        $statusinfo = self::get_instance_status($collaborativefolder, $cm, $isteacher);
        $userclient = new user_folder_access(
            new \moodle_url('/mod/collaborativefolders/authorise.php', [
                'action' => 'login',
                'id' => $cm->id,
                'sesskey' => sesskey()
            ])
        );

        // TODO Show notice to teacher if there is a problem with system account.

        // Start output.
        // Show status info table.
        echo $renderer->render($statusinfo);

        // Login / logout form.
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
            if ($statusinfo->creationstatus === 'created') {
                if ($isteacher) {
                    echo self::view_folder_teacher($statusinfo, $userclient, $renderer);
                } else {
                    echo self::view_folders_student($statusinfo, $userclient, $renderer);
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

        $groupmode = groups_get_activity_groupmode($cm) != 0;
        $groups = array();
        if ($groupmode) {
            if ($asteacher) {
                $groups = groups_get_all_groups($cm->course, 0, $cm->groupingid);
            } else {
                $groups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid);
            }
        }

        // TODO Change to actual instance status.
        return new statusinfo('pending', $collaborativefolder->teacher, $groupmode, $groups);
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
                                                 mod_collaborativefolders_renderer $renderer) {
        // TODO Get applicable groups from $statusinfo.
        // TODO Per group: Define name / access share.
    }

    /**
     * Render the view that is used for interactions with student folders. Per applicable folder:
     * # Defining a user-local name and generating a share
     * # Display the selected name, a link, and a button for problem solving (aka re-share).
     *
     * @param statusinfo $statusinfo
     * @param user_folder_access $userclient
     * @param mod_collaborativefolders_renderer $renderer
     * @return string Rendered view
     */
    private static function view_folder_teacher(statusinfo $statusinfo, user_folder_access $userclient,
                                                mod_collaborativefolders_renderer $renderer) {
        if (!$statusinfo->teachermayaccess) {
            echo $renderer->render_widget_teachermaynotaccess();
            return;
        }

        // TODO Define name / access share.
    }
}