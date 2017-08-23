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
//

/**
 * Helper class, which performs ownCloud access functions for collaborative folders.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class, which performs ownCloud access functions for collaborative folders.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toolbox {


    /**
     * Check if an issuer provides all endpoints that are required by mod_collaborativefolders.
     * @param \core\oauth2\issuer $issuer An issuer.
     * @return bool True, if all endpoints exist; false otherwise.
     */
    public static function is_valid_issuer($issuer) {
        $endpointwebdav = false;
        $endpointocs = false;
        $endpointtoken = false;
        $endpointauth = false;
        $endpointuserinfo = false;
        $endpoints = \core\oauth2\api::get_endpoints($issuer);
        foreach ($endpoints as $endpoint) {
            $name = $endpoint->get('name');
            switch ($name) {
                case 'webdav_endpoint':
                    $endpointwebdav = true;
                    break;
                case 'ocs_endpoint':
                    $endpointocs = true;
                    break;
                case 'token_endpoint':
                    $endpointtoken = true;
                    break;
                case 'authorization_endpoint':
                    $endpointauth = true;
                    break;
                case 'userinfo_endpoint':
                    $endpointuserinfo = true;
                    break;
            }
        }
        return $endpointwebdav && $endpointocs && $endpointtoken && $endpointauth && $endpointuserinfo;
    }

    /**
     * Checks if the adhoc task for the folder creation has completed for the given instance.
     *
     * @param int $cmid Coursemodule ID of the instance
     * @return bool false if task is running or scheduled
     */
    public static function is_create_task_running($cmid) {
        global $DB;
        $adhoc = $DB->get_records('task_adhoc', array('classname' => '\mod_collaborativefolders\task\collaborativefolders_create'));

        foreach ($adhoc as $element) {
            $content = json_decode($element->customdata);
            $cmidoftask = $content->cmid;

            // As long as at least one ad-hoc task exist, that has the same cm->id as the current cm the folders were not created.
            if ($cmid == $cmidoftask) {
                return true;
            }
        }
        return false;
    }
}