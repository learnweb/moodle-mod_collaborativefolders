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
 * The class contains a test script for the issuer management helper functions.
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

class mod_collaborativefolders_issuer_management_testcase extends \advanced_testcase {

    public function setUp() {
        $this->resetAfterTest(true);
    }

    /**
     * Test creating issuers, either correct or incorrect
     */
    public function test_create_issuer() {
        // Add a correct issuer to database.
        $issuer = issuer_management::create_issuer('nextcloud', 'https://nextcloud.local');
        $this->assertEquals($issuer->get('name'), 'nextcloud');

        // Check whether issuer from database is actually the same.
        $dbissuer = new issuer($issuer->get('id'));
        $this->assertEquals($issuer->get('id'), $dbissuer->get('id'));
        $this->assertEquals($dbissuer->get('name'), 'nextcloud');

        // Check whether endpoints and user field mappings are present.
        $endpoints = endpoint::get_records([
            'issuerid' => $dbissuer->get('id'),
        ]);
        $this->assertCount(5, $endpoints);
        $mappings = \core\oauth2\user_field_mapping::get_records([
            'issuerid' => $dbissuer->get('id'),
        ]);
        $this->assertCount(2, $mappings);
    }

    /**
     * Test issuer validation
     */
    public function test_validate_issuer() {
        static::setAdminUser();
        /* @var mod_collaborativefolders_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_collaborativefolders');

        // Validate a correct issuer first.
        $issuer1 = $generator->create_test_issuer('nextcloud');
        $this->assertTrue(issuer_management::is_valid_issuer($issuer1), 'Validation of a known-to-be-correct issuer.');

        // Validate some other issuer.
        $issuer2 = \core\oauth2\api::create_standard_issuer('facebook');
        $this->assertFalse(issuer_management::is_valid_issuer($issuer2), 'Validation of a known-to-be-wrong issuer.');
    }

}