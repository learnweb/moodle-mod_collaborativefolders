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
 * Privacy provider tests
 *
 * @package   mod_collaborativefolders
 * @copyright 2018 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_privacy\local\metadata\collection;
use mod_collaborativefolders\privacy\provider;

defined('MOODLE_INTERNAL') || die();

class mod_collaborativefolders_privacy_provider_testcase extends \core_privacy\tests\provider_testcase {
    /** @var stdClass The student objects. */
    protected $students = [];

    /** @var stdClass[] The collaborativefolders objects. */
    protected $collaborativefolders = [];

    /** @var stdClass The course object. */
    protected $course;

    /** @var array groups in the course */
    protected $groups = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->resetAfterTest();

        global $DB;
        $gen = self::getDataGenerator();
        $this->course = $gen->create_course();

        // Create 3 collaborativefolders.
        /** @var mod_collaborativefolders_generator $plugingen */
        $plugingen = $gen->get_plugin_generator('mod_collaborativefolders');
        $params = [
            'course' => $this->course->id,
        ];
        $this->collaborativefolders = [];
        $this->collaborativefolders[1] = $plugingen->create_instance($params);
        $this->collaborativefolders[2] = $plugingen->create_instance($params + ['groupmode' => VISIBLEGROUPS]);
        $this->collaborativefolders[3] = $plugingen->create_instance($params);

        // Create 2 students who will link to these collaborativefolders.
        $studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $this->students[1] = $gen->create_user();
        $gen->enrol_user($this->students[1]->id, $this->course->id, $studentrole->id);
        $this->students[2] = $gen->create_user();
        $gen->enrol_user($this->students[2]->id, $this->course->id, $studentrole->id);

        $this->groups = [];
        $this->groups[1] = $gen->create_group(['name' => 'Group 1', 'courseid' => $this->course->id]);
        $this->groups[2] = $gen->create_group(['name' => 'Group 2', 'courseid' => $this->course->id]);

        $gen->create_group_member(['userid' => $this->students[1]->id, 'groupid' => $this->groups[1]->id]);
        $gen->create_group_member(['userid' => $this->students[1]->id, 'groupid' => $this->groups[2]->id]);
        $gen->create_group_member(['userid' => $this->students[2]->id, 'groupid' => $this->groups[1]->id]);

        // The first collaborativefolder includes a links from both students.
        $DB->insert_record('collaborativefolders_link', (object)[
            'userid' => $this->students[1]->id,
            'cmid' => $this->collaborativefolders[1]->cmid,
            'groupid' => 0,
            'link' => 'Test link',
            'owncloudusername' => 'OC1',
        ]);
        $DB->insert_record('collaborativefolders_link', (object)[
            'userid' => $this->students[2]->id,
            'cmid' => $this->collaborativefolders[1]->cmid,
            'groupid' => 0,
            'link' => 'Test link (for student 2)',
            'owncloudusername' => 'OC2',
        ]);

        // The second collaborativefolder includes a links from both students (student 1 for both groups).
        $DB->insert_record('collaborativefolders_link', (object)[
            'userid' => $this->students[1]->id,
            'cmid' => $this->collaborativefolders[2]->cmid,
            'groupid' => $this->groups[1]->id,
            'link' => 'Test link (group 1)',
            'owncloudusername' => 'OC1',
        ]);
        $DB->insert_record('collaborativefolders_link', (object)[
            'userid' => $this->students[1]->id,
            'cmid' => $this->collaborativefolders[2]->cmid,
            'groupid' => $this->groups[2]->id,
            'link' => 'Test link (group 2)',
            'owncloudusername' => 'OC1',
        ]);
        $DB->insert_record('collaborativefolders_link', (object)[
            'userid' => $this->students[2]->id,
            'cmid' => $this->collaborativefolders[2]->cmid,
            'groupid' => $this->groups[1]->id,
            'link' => 'Test link (group 1)',
            'owncloudusername' => 'OC2',
        ]);

        // The third collaborativefolder includes no links from either student.
    }

    /**
     * Test for provider::get_metadata().
     */
    public function test_get_metadata() {
        $collection = new collection('mod_collaborativefolders');
        $newcollection = provider::get_metadata($collection);
        $itemcollection = $newcollection->get_collection();
        $this->assertCount(1, $itemcollection);

        $table = array_shift($itemcollection);
        $this->assertEquals('collaborativefolders_link', $table->get_name());
        $privacyfields = $table->get_privacy_fields();
        $this->assertArrayHasKey('userid', $privacyfields);
        $this->assertArrayHasKey('cmid', $privacyfields);
        $this->assertArrayHasKey('groupid', $privacyfields);
        $this->assertArrayHasKey('link', $privacyfields);
        $this->assertArrayHasKey('owncloudusername', $privacyfields);
        $this->assertEquals('privacy:metadata:collaborativefolders_link', $table->get_summary());
        foreach ($privacyfields as $field) {
            get_string($field, 'mod_collaborativefolders');
        }
        get_string($table->get_summary(), 'mod_collaborativefolders');
    }

    /**
     * Test for provider::get_contexts_for_userid().
     */
    public function test_get_contexts_for_userid() {
        $cms = [
            1 => get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[1]->id),
            2 => get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[2]->id),
            3 => get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[3]->id),
        ];
        $expectedctxs = [
            context_module::instance($cms[1]->id),
            context_module::instance($cms[2]->id),
        ];
        $expectedctxids = [];
        foreach ($expectedctxs as $ctx) {
            $expectedctxids[] = $ctx->id;
        }
        $contextlist = provider::get_contexts_for_userid($this->students[1]->id);
        $this->assertCount(2, $contextlist);
        $uctxids = [];
        foreach ($contextlist as $uctx) {
            $uctxids[] = $uctx->id;
        }
        $this->assertEmpty(array_diff($expectedctxids, $uctxids));
        $this->assertEmpty(array_diff($uctxids, $expectedctxids));
    }

    /**
     * Test for provider::export_user_data().
     */
    public function test_export_for_context() {
        $cms = [
            1 => get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[1]->id),
            2 => get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[2]->id),
            3 => get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[3]->id),
        ];
        $ctxs = [
            1 => context_module::instance($cms[1]->id),
            2 => context_module::instance($cms[2]->id),
            3 => context_module::instance($cms[3]->id),
        ];

        // Export all of the data for the context.
        $this->export_context_data_for_user($this->students[1]->id, $ctxs[1], 'mod_collaborativefolders');
        $writer = \core_privacy\local\request\writer::with_context($ctxs[1]);
        $this->assertTrue($writer->has_any_data());

        $this->export_context_data_for_user($this->students[1]->id, $ctxs[2], 'mod_collaborativefolders');
        $writer = \core_privacy\local\request\writer::with_context($ctxs[2]);
        $this->assertTrue($writer->has_any_data());

        $this->export_context_data_for_user($this->students[1]->id, $ctxs[3], 'mod_collaborativefolders');
        $writer = \core_privacy\local\request\writer::with_context($ctxs[3]);
        $this->assertFalse($writer->has_any_data());
    }

    /**
     * Test for provider::delete_data_for_all_users_in_context().
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        // Before deletion, we should have 5 links.
        $this->assertEquals(5, $DB->count_records('collaborativefolders_link'));

        // Delete data from the first collaborativefolders.
        $cm = get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[1]->id);
        $cmcontext = context_module::instance($cm->id);
        provider::delete_data_for_all_users_in_context($cmcontext);
        // After deletion, there should be 3 links.
        $this->assertEquals(3, $DB->count_records('collaborativefolders_link'));

        // Delete data from the second collaborativefolders.
        $cm = get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[2]->id);
        $cmcontext = context_module::instance($cm->id);
        provider::delete_data_for_all_users_in_context($cmcontext);
        // After deletion, there should be 0 links.
        $this->assertEquals(0, $DB->count_records('collaborativefolders_link'));
    }

    /**
     * Test for provider::delete_data_for_user().
     */
    public function test_delete_data_for_user() {
        global $DB;

        $cms = [
            1 => get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[1]->id),
            2 => get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[2]->id),
            3 => get_coursemodule_from_instance('collaborativefolders', $this->collaborativefolders[3]->id),
        ];
        $ctxs = [];
        foreach ($cms as $idx => $cm) {
            $ctxs[$idx] = context_module::instance($cm->id);
        }

        // Before deletion, we should have 5 links.
        $this->assertEquals(5, $DB->count_records('collaborativefolders_link'));

        // Delete the data for the first student, but only for the first collaborativefolders.
        $contextlist = new \core_privacy\local\request\approved_contextlist($this->students[1], 'collaborativefolders',
                                                                            [$ctxs[1]->id]);
        provider::delete_data_for_user($contextlist);

        // After deletion, we should have 4 links.
        $this->assertEquals(4, $DB->count_records('collaborativefolders_link'));
        // Confirm the remaining link is for the second student.
        $this->assertEquals($this->students[2]->id, $DB->get_field('collaborativefolders_link', 'userid', ['cmid' => $cms[1]->id]));

        // Delete the data for the first student, for all collaborativefolderss.
        $contextids = [$ctxs[1]->id, $ctxs[2]->id, $ctxs[3]->id];
        $contextlist = new \core_privacy\local\request\approved_contextlist($this->students[1], 'collaborativefolders',
                                                                            $contextids);
        provider::delete_data_for_user($contextlist);

        // After deletion, we should have 2 links.
        $this->assertEquals(2, $DB->count_records('collaborativefolders_link'));
        // Confirm the remaining links are for the second student.
        $this->assertEquals($this->students[2]->id, $DB->get_field('collaborativefolders_link', 'userid', ['cmid' => $cms[1]->id]));
        $this->assertEquals($this->students[2]->id, $DB->get_field('collaborativefolders_link', 'userid', ['cmid' => $cms[2]->id]));
    }
}