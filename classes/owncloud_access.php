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
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders;

use tool_oauth2owncloud\owncloud;

defined('MOODLE_INTERNAL') || die();

class owncloud_access {

    /** @var \tool_oauth2owncloud\owncloud client instance for server access. */
    public $owncloud;

    /**
     * owncloud_access constructor. The OAuth 2.0 client is initialized within it.
     *
     * @param $returnurl
     */
    public function __construct ($returnurl) {
        $this->owncloud = new owncloud($returnurl);
    }

    /**
     * Method for share creation in ownCloud. A share for a specific user and folder is generated.
     *
     * @param $path string path to the folder.
     * @param $userid string username in ownCloud.
     * @return string link to the folder.
     */
    public function generate_share($path, $userid) {
        // First, the technical user's Access Token needs to checked.
        // If it is invalid, no access to ownCloud can be granted.
        if (!$this->owncloud->check_login('mod_collaborativefolders')) {
            return false;
        }

        $response = $this->owncloud->get_link($path, $userid);

        // Only if the link was created or already shared with the specific user, true is returned.
        if (($response['code'] == 100 && $response['status'] == 'ok') || $response['code'] == 403) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Method for creation and deletion of folders for collaborative work.
     *
     * @param $path string specific path of the groupfolder.
     * @param $intention string 'make' for creating and 'delete' for deletion.
     * @return bool false if an error occurred.
     */
    public function handle_folder($intention, $path) {
        // First, the technical user's Access Token needs to checked.
        // If it is invalid, no access to ownCloud can be granted.
        if (!$this->owncloud->check_login('mod_collaborativefolders')) {
            return false;
        }

        // If no socket could be opened, no connection to the ownCloud server is available
        // via WebDAV.
        if (!$this->owncloud->open()) {
            return false;
        }

        // WebDAV path is handed over.
        $webdavpath = '/' . $path;

        if ($intention == 'make') {

            $code = $this->owncloud->make_folder($webdavpath);
            return $code;

        } else if ($intention == 'delete') {

            $code = $this->owncloud->delete_folder($webdavpath);
            return $code;

        } else {
            // No other operations, except make and delete, are allowed.
            return false;
        }
    }
}