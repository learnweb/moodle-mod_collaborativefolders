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
 * Helper class, which performs ownCloud access functions for collaborative folders.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class, which performs ownCloud access functions for collaborative folders.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toolbox {
    /**
     * Checks if the adhoc task for the folder creation has completed for the given instance.
     *
     * @param int $cmid Coursemodule ID of the instance
     * @return bool false if task is running or scheduled
     */
    public static function is_create_task_running($cmid) {
        global $DB;
        $adhoc = $DB->get_records('task_adhoc', array('classname' => '\mod_collaborativefolders\task\collaborativefolders_create'));

        foreach ($adhoc as $element) {
            $content = json_decode($element->customdata);
            $cmidoftask = $content->cmid;

            // As long as at least one ad-hoc task exist, that has the same cm->id as the current cm the folders were not created.
            if ($cmid == $cmidoftask) {
                return true;
            }
        }
        return false;
    }

    /**
     * Store a course_module_viewed event.
     *
     * @param \context_module $context
     * @param \cm_info $cm
     */
    public static function coursemodule_viewed(\context_module $context, \cm_info $cm) {
        $params = array(
            'context' => $context,
            'objectid' => $cm->instance
        );

        $cmviewed = \mod_collaborativefolders\event\course_module_viewed::create($params);
        $cmviewed->trigger();
    }

    /**
     * Create a fake group. Used for representing course-wide groups of users.
     * @param string $coursetitle Name of the course. Could be e.g. the shortname attribute.
     * @return \stdClass faking a simple group object; with attributes id=0 and name as passed.
     */
    public static function fake_course_group(string $coursetitle): \stdClass {
        $group = new \stdClass();
        $group->name = $coursetitle;
        $group->id = 0;
        return $group;
    }
}