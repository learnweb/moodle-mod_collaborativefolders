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
 * The class contains a test script for the moodle mod_collaborativefolders
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_collaborativefolders_events_testcase extends \advanced_testcase {

    /** @var null|array data array containing groupings, course and instance information. */
    private $data = null;

    public function setUp() {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_collaborativefolders');
        $this->data = $generator->create_preparation();
    }

    public function test_instance_list_viewed() {
        $context = context_course::instance($this->data['course']->id);

        $params = array(
                'context' => $context
        );

        $sink = $this->redirectEvents();
        $event = \mod_collaborativefolders\event\course_module_instance_list_viewed::create($params);
        $event->trigger();
        $result = $sink->get_events();
        $event = reset($result);
        $sink->close();

        $this->assertEquals($context->id, $event->contextid);
        $this->assertEquals($this->data['course']->id, $event->courseid);
    }
}