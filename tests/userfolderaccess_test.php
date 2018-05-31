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
 * Tests for the user client class.
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

class mod_collaborativefolders_userfolderaccess_testcase extends \advanced_testcase {

    /**
     * Data generator
     * @var mod_collaborativefolders_generator
     */
    private $generator;

    public function setUp() {
        $this->resetAfterTest(true);
        $this->generator = $this->getDataGenerator()->get_plugin_generator('mod_collaborativefolders');
    }

    /**
     * Test correct response if configuration is erroneous.
     */
    public function test_erroneous_configuration() {
        // First: No issuer exists.
        $this->expectException(\mod_collaborativefolders\configuration_exception::class);
        new \mod_collaborativefolders\local\clients\user_folder_access(\oauth2_client::callback_url());

        // Second: Issuer exists, but is not configured.
        $nextcloud = $this->generator->create_test_issuer('nextcloud');
        $this->expectException(\mod_collaborativefolders\configuration_exception::class);
        new \mod_collaborativefolders\local\clients\user_folder_access(\oauth2_client::callback_url());

        // Third: Issuer is configured, but removed afterwards.
        set_config("issuerid", $nextcloud->get('id'), "collaborativefolders");
        assertTrue($nextcloud->delete());
        $this->expectException(\mod_collaborativefolders\configuration_exception::class);
        new \mod_collaborativefolders\local\clients\user_folder_access(\oauth2_client::callback_url());

        // Fourth: Wrong issuer is configured.
        $facebook = \core\oauth2\api::create_standard_issuer('facebook');
        set_config("issuerid", $facebook->get('id'), "collaborativefolders");
        $this->expectException(\mod_collaborativefolders\configuration_exception::class);
        new \mod_collaborativefolders\local\clients\user_folder_access(\oauth2_client::callback_url());
    }

    /**
     * Test correct response if configuration is valid.
     */
    public function test_correct_configuration() {
        $nextcloud = $this->generator->create_test_issuer('nextcloud');
        set_config("issuerid", $nextcloud->get('id'), "collaborativefolders");
        $ufa = new \mod_collaborativefolders\local\clients\user_folder_access(\oauth2_client::callback_url());
        $this->assertInstanceOf(\moodle_url::class, $ufa->get_login_url());
    }

}