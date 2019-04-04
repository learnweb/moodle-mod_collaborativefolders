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
 * The class contains a test script for the mod_collaborativefolders activity module's
 * events.
 *
 * @package    mod_collaborativefolders
 * @group      mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_collaborativefolders_events_testcase extends \advanced_testcase {

    /** @var null|array data array containing groupings, course and instance information. */
    private $data = null;

    public function setUp() {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_collaborativefolders');
        $this->data = $generator->create_preparation(0, 0);
    }

    /**
     * Test the build in course_module_instance_list_viewed event.
     */
    public function test_instance_list_viewed() {
        $context = context_course::instance($this->data['course']->id);

        $params = array(
                'context' => $context
        );

        $event = \mod_collaborativefolders\event\course_module_instance_list_viewed::create($params);
        $event = $this->get_event_result($event);

        $this->assertInstanceOf('\mod_collaborativefolders\event\course_module_instance_list_viewed', $event);
        $this->assertEquals($context->id, $event->contextid);
        $this->assertEquals($this->data['course']->id, $event->courseid);
        $this->assertNotEmpty($event->get_name());
    }

    /**
     * Test the build in course_module_viewed event.
     */
    public function test_module_viewed() {
        $cmid = $this->data['instance']->cmid;
        $context = context_module::instance($cmid);
        $instanceid = $this->data['instance']->id;

        $params = array(
                'context' => $context,
                'objectid' => $instanceid
        );

        $event = \mod_collaborativefolders\event\course_module_viewed::create($params);
        $event = $this->get_event_result($event);

        $this->assertInstanceOf('\mod_collaborativefolders\event\course_module_viewed', $event);
        $this->assertEquals($context->id, $event->contextid);
        $this->assertEquals($this->data['course']->id, $event->courseid);
        $this->assertEquals('collaborativefolders', $event->objecttable);
        $this->assertEquals($instanceid, $event->objectid);
    }

    /**
     * Tests for the implemented link_generated event for collaborativefolders.
     */
    public function test_link_generated() {
        $cmid = $this->data['instance']->cmid;
        $context = context_module::instance($cmid);
        $instanceid = $this->data['instance']->id;

        $params = array(
                'context' => $context,
                'objectid' => $instanceid
        );

        $event = \mod_collaborativefolders\event\link_generated::create($params);
        $event = $this->get_event_result($event);

        $this->assertInstanceOf('\mod_collaborativefolders\event\link_generated', $event);

        // Verify the event's correctness by testing its fields.
        $this->assertEquals($context->id, $event->contextid);
        $this->assertEquals($this->data['course']->id, $event->courseid);
        $this->assertEquals('collaborativefolders', $event->objecttable);
        $this->assertEquals($instanceid, $event->objectid);
        $this->assertEquals('u', $event->crud);
        $this->assertEquals(2, $event->edulevel);

        // Test the event methods, which were implemented within the event.
        $this->assertNotEmpty($event->get_name());
        $this->assertNotEmpty($event->get_description());

        $url = new \moodle_url('/mod/collaborativefolders/view.php', array('id' => $cmid));
        $this->assertEquals($url, $event->get_url());
    }

    /**
     * Helper method to fetch the results from a triggered event.
     *
     * @param $event \core\event\base event, which needs to be triggered.
     * @return \core\event\base|mixed the caught event data.
     */
    protected function get_event_result($event) {
        $sink = $this->redirectEvents();
        $event->trigger();
        $result = $sink->get_events();
        $event = reset($result);
        $sink->close();

        return $event;
    }
}