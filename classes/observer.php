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

namespace mod_collaborativefolders;

defined('MOODLE_INTERNAL') || die();

use mod_collaborativefolders\task\collaborativefolders_create;

/**
 * Observers for the collaborative folders plugin.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    public static function collaborativefolders_created(\core\event\course_module_created $event) {

        global $DB;

        $data = $event->get_data();
        $other = $data['other'];

        if ($other['modulename'] == 'collaborativefolders') {

            // First, the module ID of collaborativefolders needs to be fetched.
            $module = $DB->get_record('modules', array('name' => 'collaborativefolders'), 'id', MUST_EXIST)->id;

            // To identify the specific course module, that is needed, the module name (collaborative
            // folder) and the instance ID of the specific module have to be passed as select statements.
            $paramscm = array('module' => $module, 'instance' => $other['instanceid']);

            // And after that, the exact course module ID can be gotten from the DB, which we need
            // as an unique identifier for the foldername.
            $cmid = $DB->get_record('course_modules', $paramscm, 'id', MUST_EXIST)->id;

            $paths = array();
            $paths['cmid'] = $cmid;
            $paths['instance'] = $other['instanceid'];

            list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'collaborativefolders');

            if (groups_get_activity_groupmode($cm) != 0) {

                $grid = $cm->groupingid;

                $groups = groups_get_all_groups($course->id, 0, $grid);

                foreach ($groups as $group) {

                    $path = $cmid . '/' . $group->id;
                    $paths[$group->id] = $path;
                }
            }

            $creator = new collaborativefolders_create();
            $creator->set_custom_data($paths);
            \core\task\manager::queue_adhoc_task($creator);
        }
    }
}