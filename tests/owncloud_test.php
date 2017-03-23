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

    /** @var null|\mod_collaborativefolders\owncloud_access owncloud_access object. */
    private $oc = null;

    protected function setUp() {
        $this->resetAfterTest(true);
        $url = new \moodle_url('/');
        $this->oc = new \mod_collaborativefolders\owncloud_access($url);
    }

    /**
     * Tests for the generate_share function of the owncloud_access class.
     */
    public function test_generate_share() {
        // Technical user is not logged in.
        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(false));

        $private = $this->set_private_oc($mock);

        $this->assertFalse($this->oc->generate_share('path', '0'));

        // Technical user is logged in and the response is accepted.
        $response = array(
                'code' => 100,
                'status' => 'ok'
        );

        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->any())->method('get_link')->will($this->returnValue($response));
        $private->setValue($this->oc, $mock);

        $this->assertTrue($this->oc->generate_share('path', '0'));

        // Alternative accepted response.
        $response['code'] = 403;

        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->any())->method('get_link')->will($this->returnValue($response));
        $private->setValue($this->oc, $mock);

        $this->assertTrue($this->oc->generate_share('path', '0'));

        // Not an accepted response.
        $response['code'] = 404;

        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->any())->method('get_link')->will($this->returnValue($response));
        $private->setValue($this->oc, $mock);

        $this->assertFalse($this->oc->generate_share('path', '0'));
    }

    /**
     * Test, if authentication_exception is thrown in handle_folder, when the technical user is not
     * logged in.
     */
    public function test_authentication_exception() {
        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->once())->method('check_login')->will($this->returnValue(false));
        $this->set_private_oc($mock);

        $this->expectException(\tool_oauth2owncloud\authentication_exception::class);
        $this->oc->handle_folder('make', 'path');
    }

    /**
     * Test, if socket_exception is thrown in handle_folder, when the WebDAV socket cannot be
     * opened.
     */
    public function test_socket_exception() {
        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->once())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->once())->method('open')->will($this->returnValue(false));
        $this->set_private_oc($mock);

        $this->expectException(\tool_oauth2owncloud\socket_exception::class);
        $this->oc->handle_folder('make', 'path');
    }

    /**
     * Test, if invalid_parameter_exception is thrown in handle_folder, when an argument, other then 'make' or
     * 'delete', has been inserted as $intention.
     */
    public function test_invalid_exception() {
        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->once())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->once())->method('open')->will($this->returnValue(true));
        $this->set_private_oc($mock);

        $this->expectException(invalid_parameter_exception::class);
        $this->oc->handle_folder('do', 'path');
    }

    /**
     * Tests successful runs for handle_folder with $intention set to 'make', as well as 'delete'.
     */
    public function test_handle_folder() {
        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->exactly(2))->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->exactly(2))->method('open')->will($this->returnValue(true));
        $mock->expects($this->exactly(1))->method('make_folder')->will($this->returnValue(201));
        $mock->expects($this->exactly(1))->method('delete_folder')->will($this->returnValue(201));
        $this->set_private_oc($mock);

        $this->assertEquals(201, $this->oc->handle_folder('make', 'path'));
        $this->assertEquals(201, $this->oc->handle_folder('delete', 'path'));
    }

    /**
     * Tests for rename method from the owncloud_access class.
     */
    public function test_rename() {
        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(false));
        $private = $this->set_private_oc($mock);

        // User is not logged in.
        $path = 'path';
        $name = 'name';
        $cmid = 10;
        $userid = '0';

        $ret = array(
                'status' => false,
                'content' => get_string('usernotloggedin', 'mod_collaborativefolders')
        );

        $this->assertEquals($ret, $this->oc->rename($path, $name, $cmid, $userid));

        // Socket could not be opened.
        $ret['content'] = get_string('socketerror', 'mod_collaborativefolders');

        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->any())->method('open')->will($this->returnValue(false));
        $private->setValue($this->oc, $mock);

        $this->assertEquals($ret, $this->oc->rename($path, $name, $cmid, $userid));

        // Wrong response status code.
        $ret['content'] = get_string('webdaverror', 'mod_collaborativefolders', 404);

        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->any())->method('open')->will($this->returnValue(true));
        $mock->expects($this->any())->method('move')->will($this->returnValue(404));
        $private->setValue($this->oc, $mock);

        $this->assertEquals($ret, $this->oc->rename($path, $name, $cmid, $userid));

        // Successful access to ownCloud, status code is accepted.
        $ret['status'] = true;
        $ret['content'] = 'https://example.com';

        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->any())->method('open')->will($this->returnValue(true));
        $mock->expects($this->any())->method('move')->will($this->returnValue(201));
        $mock->expects($this->any())->method('get_path')->will($this->returnValue('https://example.com'));
        $private->setValue($this->oc, $mock);

        $this->assertEquals($ret, $this->oc->rename($path, $name, $cmid, $userid));
    }

    /**
     * Helper method, which inserts a given owncloud mock object into the owncloud_access object.
     *
     * @param $mock object mock object, which needs to be inserted.
     * @return ReflectionProperty the resulting reflection property.
     */
    protected function set_private_oc($mock) {
        $refclient = new ReflectionClass($this->oc);
        $private = $refclient->getProperty('owncloud');
        $private->setAccessible(true);
        $private->setValue($this->oc, $mock);

        return $private;
    }
}