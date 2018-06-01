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
 * Tests for the system account client class.
 *
 * @package    mod_collaborativefolders
 * @group      mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/webdavlib.php');

class mod_collaborativefolders_system_folder_access_testcase extends \advanced_testcase {
    /**
     * Data generator
     * @var mod_collaborativefolders_generator
     */
    private $generator;

    protected function setUp() {
        $this->resetAfterTest(true);
        $this->generator = $this->getDataGenerator()->get_plugin_generator('mod_collaborativefolders');
    }


    /**
     * Test correct response if configuration is erroneous.
     */
    public function test_erroneous_configuration() {
        // First: No issuer exists.
        $this->expectException(\mod_collaborativefolders\configuration_exception::class);
        new \mod_collaborativefolders\local\clients\system_folder_access();

        // Second: Issuer exists, but is not configured.
        $nextcloud = $this->generator->create_test_issuer('nextcloud');
        $this->expectException(\mod_collaborativefolders\configuration_exception::class);
        new \mod_collaborativefolders\local\clients\system_folder_access();

        // Third: Issuer is configured, but removed afterwards.
        set_config("issuerid", $nextcloud->get('id'), "collaborativefolders");
        assertTrue($nextcloud->delete());
        $this->expectException(\mod_collaborativefolders\configuration_exception::class);
        new \mod_collaborativefolders\local\clients\system_folder_access();

        // Fourth: Wrong issuer was configured.
        $facebook = \core\oauth2\api::create_standard_issuer('facebook');
        set_config("issuerid", $facebook->get('id'), "collaborativefolders");
        $this->expectException(\mod_collaborativefolders\configuration_exception::class);
        new \mod_collaborativefolders\local\clients\system_folder_access();

        // Fifth: Issuer is configured correctly, but no system account is connected.
        $nextcloud2 = $this->generator->create_test_issuer('nextcloud');
        set_config("issuerid", $nextcloud2->get('id'), "collaborativefolders");
        $this->expectException(\mod_collaborativefolders\configuration_exception::class);
        new \mod_collaborativefolders\local\clients\system_folder_access();

        // Testing a correct validation would require that an access token could be redeemed. That's hard.
        // Let's assume that the Moodle OAuth API does that part correctly without us testing it.
    }

}