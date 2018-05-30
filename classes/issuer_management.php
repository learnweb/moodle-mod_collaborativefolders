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
 * Provide static functions for creating and validating issuers.
 *
 * @package    mod_collaborativefolders
 * @copyright  2018 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_collaborativefolders;

defined('MOODLE_INTERNAL') || die();
use core\oauth2\issuer;

/**
 * Provide static functions for creating and validating issuers.
 *
 * @package    mod_collaborativefolders
 * @copyright  2018 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class issuer_management {
    /**
     * Create an ownCloud/Nextcloud ready OAuth 2 service. Generates all required endpoints and field mappings.
     * @param string $issuername Name of the issuer (need not be unique)
     * @param string $baseurl Base URL of the issuer
     * @return \core\oauth2\issuer
     */
    public static function create_issuer(string $issuername, string $baseurl): issuer {
        // Add trailing slash to baseurl, if needed.
        if (substr($baseurl, -1) !== '/') {
            $baseurl .= '/';
        }

        // Custom issuer setup.
        $record = (object) [
            'name' => $issuername,
            'baseurl' => $baseurl,
            'image' => '',
            'basicauth' => 1,
        ];

        $issuer = new \core\oauth2\issuer(0, $record);
        $issuer->create();

        $endpoints = [
            // Baseurl will be prepended later.
            'authorization_endpoint' => 'index.php/apps/oauth2/authorize',
            'token_endpoint' => 'index.php/apps/oauth2/api/v1/token',
            'userinfo_endpoint' => 'ocs/v2.php/cloud/user?format=json',
            'webdav_endpoint' => 'remote.php/webdav/',
            'ocs_endpoint' => 'ocs/v1.php/apps/files_sharing/api/v1/shares?format=xml',
        ];

        foreach ($endpoints as $name => $url) {
            $record = (object) [
                'issuerid' => $issuer->get('id'),
                'name' => $name,
                'url' => $baseurl . $url,
            ];
            $endpoint = new \core\oauth2\endpoint(0, $record);
            $endpoint->create();
        }

        // Create the field mappings.
        $mapping = [
            'ocs-data-email' => 'email',
            'ocs-data-id' => 'username',
        ];
        foreach ($mapping as $external => $internal) {
            $record = (object) [
                'issuerid' => $issuer->get('id'),
                'externalfield' => $external,
                'internalfield' => $internal
            ];
            $userfieldmapping = new \core\oauth2\user_field_mapping(0, $record);
            $userfieldmapping->create();
        }
        return $issuer;
    }

    /**
     * Check if an issuer provides all endpoints that are required by mod_collaborativefolders.
     * @param \core\oauth2\issuer $issuer An issuer.
     * @return bool True, if all endpoints exist; false otherwise.
     */
    public static function is_valid_issuer(\core\oauth2\issuer $issuer): bool {
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
}