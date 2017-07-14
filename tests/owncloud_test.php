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
 * This testcase contains tests for the owncloud_access class, which is part of the
 * collaborativefolders activity module.
 *
 * @package    mod_collaborativefolders
 * @group      mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_collaborativefolders_owncloud_testcase extends \advanced_testcase {

    /** @var null|\mod_collaborativefolders\folder_access owncloud_access object. */
    private $oc = null;

    protected function setUp() {
        $this->resetAfterTest(true);
        $url = new \moodle_url('/');
        $this->oc = new \mod_collaborativefolders\folder_access($url);
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
        $this->oc->make_folder('make', 'path');
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
        $this->oc->make_folder('make', 'path');
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
        $this->oc->make_folder('do', 'path');
    }

    /**
     * Tests successful runs for handle_folder with $intention set to 'make', as well as 'delete'.
     */
    public function test_handle_folder() {
        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->exactly(2))->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->exactly(2))->method('open')->will($this->returnValue(true));
        $mock->expects($this->exactly(1))->method('make_folder')->will($this->returnValue(201));
        $this->set_private_oc($mock);

        $this->assertEquals(201, $this->oc->make_folder('make', 'path'));
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
     * Tests for the share_and_rename method from the owncloud_access class.
     */
    public function test_share_and_rename() {
        // Dummy data.
        $accesstoken = new stdClass();
        $accesstoken->user_id = 'admin';
        $share = 'share';
        $rename = 'ren';
        $name = 'name';
        $cmid = 10;
        $userid = '0';

        // The sharing operation was unsuccessful.
        $ret = array(
                'status' => false,
                'type' => 'share',
                'content' => get_string('ocserror', 'mod_collaborativefolders')
        );

        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(false));
        $mock->expects($this->any())->method('get_accesstoken')->will($this->returnValue($accesstoken));
        $private = $this->set_private_oc($mock);

        $this->assertEquals($ret, $this->oc->share_and_rename($share, $rename, $name, $cmid, $userid));

        // Renaming was unsuccessful.
        $response = array(
                'code' => 100,
                'status' => 'ok'
        );

        $ret['type'] = 'rename';
        $ret['content'] = get_string('socketerror', 'mod_collaborativefolders');

        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->any())->method('move')->will($this->returnValue(404));
        $mock->expects($this->any())->method('get_accesstoken')->will($this->returnValue($accesstoken));
        $mock->expects($this->any())->method('get_link')->will($this->returnValue($response));
        $private->setValue($this->oc, $mock);

        $this->assertEquals($ret, $this->oc->share_and_rename($share, $rename, $name, $cmid, $userid));

        // Sharing, as well as renaming, were successful.
        $ret = array(
                'status' => true,
                'content' => 'https://example.com'
        );

        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->any())->method('check_login')->will($this->returnValue(true));
        $mock->expects($this->any())->method('open')->will($this->returnValue(true));
        $mock->expects($this->any())->method('move')->will($this->returnValue(201));
        $mock->expects($this->any())->method('get_accesstoken')->will($this->returnValue($accesstoken));
        $mock->expects($this->any())->method('get_link')->will($this->returnValue($response));
        $mock->expects($this->any())->method('get_path')->will($this->returnValue('https://example.com'));
        $private->setValue($this->oc, $mock);

        $this->assertEquals($ret, $this->oc->share_and_rename($share, $rename, $name, $cmid, $userid));
    }

    /**
     * Test logout_user method from owncloud_access class.
     */
    public function test_log_out() {
        set_user_preference('oC_token', 'token');
        $this->oc->logout_user();
        $this->assertNull(get_user_preferences('oC_token'));
    }

    /**
     * Test get_login_url method from owncloud_access class.
     */
    public function test_login_url() {
        $mock = $this->createMock(\tool_oauth2owncloud\owncloud::class);
        $mock->expects($this->once())->method('get_login_url')->will($this->returnValue('url'));
        $this->set_private_oc($mock);

        $this->assertEquals('url', $this->oc->get_login_url());
    }

    /**
     * Test set_entry method from the owncloud_access class.
     */
    public function test_set_entry() {
        global $DB;

        $this->oc->set_entry('name', 10, '0', 'somename');

        $params = array(
                'userid' => '0',
                'cmid' => 10,
                'name' => 'somename',
                'link' => null
        );

        $exists = $DB->record_exists('collaborativefolders_link', $params);

        $this->assertTrue($exists);

        $this->oc->set_entry('name', 10, '0', null);

        $params['name'] = null;

        $exists = $DB->record_exists('collaborativefolders_link', $params);

        $this->assertTrue($exists);
    }

    /**
     * Test get_entry method from the owncloud_access class.
     */
    public function test_get_entry() {
        global $DB;

        $this->assertNull($this->oc->get_entry('link', 10, '0'));

        $params = array(
                'userid' => '0',
                'cmid' => 10,
                'name' => 'somename',
                'link' => 'linkname'
        );

        $DB->insert_record('collaborativefolders_link', (object) $params);

        $this->assertEquals('linkname', $this->oc->get_entry('link', 10, '0'));
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