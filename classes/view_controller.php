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
     * @param \mod_collaborativefolders_renderer $renderer Plugin-specific renderer
     */
    public static function handle_request($collaborativefolder, \cm_info $cm, \context_module $context,
                                          \mod_collaborativefolders_renderer $renderer) {
        global $OUTPUT, $USER;
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('activityoverview', 'mod_collaborativefolders'));

        $statusinfo = self::get_instance_status($collaborativefolder, $cm);
        $userclient = new \mod_collaborativefolders\local\clients\user_folder_access(new \moodle_url(qualified_me()));

        // TODO Show notice to teacher if there is a problem with system account.

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
                // TODO Share form / View link.
            } else {
                // Folders are not yet created and can therefore not be shared.
                $notification = new \core\output\notification('@plswait', \core\output\notification::NOTIFY_INFO);
                $notification->set_show_closebutton(false);
                echo $renderer->render($notification);
            }
        }

        echo $OUTPUT->footer();
    }

    /**
     * Aggregate information about the current instance. Focus on information that is relevant to the current user.
     *
     * @param \stdClass $collaborativefolder Collaborativefolder instance
     * @param \cm_info $cm Corresponding course module
     * @return output\statusinfo
     */
    private static function get_instance_status($collaborativefolder, \cm_info $cm) {
        global $USER;

        $groupmode = groups_get_activity_groupmode($cm) != 0;
        $groups = array();
        if ($groupmode) {
            $groups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid);
        }

        // TODO Change to actual instance status.
        return new \mod_collaborativefolders\output\statusinfo('pending', $collaborativefolder->teacher, $groupmode, $groups);
    }
}