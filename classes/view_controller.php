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

        echo $renderer->render($statusinfo);

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

        return new \mod_collaborativefolders\output\statusinfo('pending', $collaborativefolder->teacher, $groupmode, $groups);
    }
}