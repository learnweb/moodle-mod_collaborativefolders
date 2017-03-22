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
 * @package mod_collaborativefolders
 * @copyright 2016 Projektseminar WWU
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_collaborativefolders_owncloud_testcase extends \advanced_testcase {

    /** @var null|\mod_collaborativefolders\owncloud_access  */
    private $oc = null;

    protected function setUp() {
        $this->resetAfterTest(true);
        $url = new \moodle_url('/');
        $this->oc = new \mod_collaborativefolders\owncloud_access($url);
    }

    public function test_generate_share() {
        $this->resetAfterTest(true);
        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->once())
            ->method('check_login')
            ->will($this->returnValue(false));

        $refclient = new ReflectionClass($this->oc);
        $private = $refclient->getProperty('owncloud');
        $private->setAccessible(true);
        $private->setValue($this->oc, $mock);

        $this->assertFalse($this->oc->generate_share('path', '0'));
    }
}