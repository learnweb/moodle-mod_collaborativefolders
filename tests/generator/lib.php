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
 * Data generator for the collaborativefolders module tests.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds a function to the parent class, which creates a course and an activity instance
 * of collaborativefolders.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_collaborativefolders_generator extends testing_module_generator {
    /**
     * Create an issuer for testing purposes.
     * @param string $name Name; used as a name in the testing issuer and as its mock URL
     * @return \core\oauth2\issuer
     */
    public function create_test_issuer(string $name): \core\oauth2\issuer {
        return \mod_collaborativefolders\issuer_management::create_issuer($name, sprintf('https://%s.local', $name));
    }

    /**
     * Creates Course, course members, groups and groupings to test the module.
     * @param $groupmode
     * @param $grouping
     * @return array
     * @throws coding_exception
     */
    public function create_preparation ($groupmode, $grouping): array {
        $generator = advanced_testcase::getDataGenerator();
        $data = array();
        $course = $generator->create_course(array('name' => 'A course'));
        $data['course'] = $course;

        // Creates groups.
        $group1 = $generator->create_group(array('courseid' => $course->id));
        $data['group1'] = $group1;
        $group2 = $generator->create_group(array('courseid' => $course->id));
        $data['group2'] = $group2;
        $group21 = $generator->create_group(array('courseid' => $course->id));
        $data['group21'] = $group21;
        // Create 3 groupings in course 2.
        $grouping1 = $generator->create_grouping(array('courseid' => $course->id));
        $data['grouping1'] = $grouping1;
        $grouping2 = $generator->create_grouping(array('courseid' => $course->id));
        $data['grouping2'] = $grouping2;
        $grouping3 = $generator->create_grouping(array('courseid' => $course->id));
        $data['grouping3'] = $grouping3;
        // Add Grouping to groups.
        $generator->create_grouping_group(array('groupingid' => $grouping1->id, 'groupid' => $group1->id));
        $generator->create_grouping_group(array('groupingid' => $grouping2->id, 'groupid' => $group2->id));
        $generator->create_grouping_group(array('groupingid' => $grouping2->id, 'groupid' => $group21->id));

        // Initiates the groupings and grouping members.
        // Creates 4 Users, enrols them in course.
        for ($i = 1; $i <= 4; $i++) {
            $user = $generator->create_user();
            $generator->enrol_user($user->id, $course->id);
            $data['user' . $i] = $user;
        }
        $generator->create_group_member(array('groupid' => $group1->id, 'userid' => $data['user1']->id));
        $generator->create_group_member(array('groupid' => $group1->id, 'userid' => $data['user2']->id));
        $generator->create_group_member(array('groupid' => $group2->id, 'userid' => $data['user3']->id));
        $generator->create_group_member(array('groupid' => $group21->id, 'userid' => $data['user4']->id));
        $generator->create_group_member(array('groupid' => $group21->id, 'userid' => $data['user3']->id));
        $generator->create_group_member(array('groupid' => $group2->id, 'userid' => $data['user4']->id));
        $generator->create_group_member(array('groupid' => $group21->id, 'userid' => $data['user2']->id));

        $params = array(
                'course' => $data['course']->id,
                'groupmode' => $groupmode,
                'groupingid' => $grouping
        );

        $data["instance"] = $this->create_instance($params);
        return $data; // Return the user, course and group objects.
    }
}