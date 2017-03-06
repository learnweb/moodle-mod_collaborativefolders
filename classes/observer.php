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
 * Observers for the collaborative folders plugin.
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders;

defined('MOODLE_INTERNAL') || die();

use mod_collaborativefolders\task\collaborativefolders_create;

class observer {

    public static function collaborativefolders_created(\core\event\course_module_created $event) {

        global $DB;

        $data = $event->get_data();
        $other = $data['other'];

        if ($other['modulename'] == 'collaborativefolders') {

            $module = $DB->get_record('modules', array('name' => 'collaborativefolders'), 'id')->id;
            $cmid = $DB->get_record('course_modules', array('module' => $module, 'instance' => $other['instanceid']), 'id')->id;

            $paths = array();
            $paths['cmid'] = $cmid;

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