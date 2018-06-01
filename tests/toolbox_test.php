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
 * Tests for the general-purpose helper functions.
 *
 * @package    mod_collaborativefolders
 * @group      mod_collaborativefolders
 * @copyright  2018 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\oauth2\endpoint;
use core\oauth2\issuer;
use mod_collaborativefolders\issuer_management;

defined('MOODLE_INTERNAL') || die();

class mod_collaborativefolders_toolbox_testcase extends \advanced_testcase {

    public function setUp() {
        $this->resetAfterTest(true);
    }

    /**
     * Test fake course group creation
     */
    public function test_fake_course_group() {
        $fakegroup = \mod_collaborativefolders\toolbox::fake_course_group('fakename');
        $this->assertEquals('fakename', $fakegroup->name);
        $this->assertEquals(0, $fakegroup->id);
    }

}