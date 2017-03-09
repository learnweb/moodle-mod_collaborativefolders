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

use moodle_url;
use tool_oauth2owncloud\owncloud;

defined('MOODLE_INTERNAL') || die();

class owncloud_access {

    /** @var \tool_oauth2owncloud\owncloud client instance for server access. */
    public $owncloud;

    /**
     * owncloud_access constructor. The OAuth 2.0 client is initialized within it.
     */
    public function __construct () {
        $returnurl = new moodle_url('/admin/settings.php?section=modsettingcollaborativefolders', [
            'callback'  => 'yes',
            'sesskey'   => sesskey(),
        ]);
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
        // Fetch the Token from the DB and store it within the client.
        $token = unserialize(get_config('mod_collaborativefolders', 'token'));
        $this->owncloud->set_access_token($token);

        // If the Token is not accepted or cannot be fetched from the ownCloud Server, false is returned.
        // Further failure resolution has to be provided in near future.
        if (!$this->owncloud->is_logged_in()) {
            return false;
        }

        $output = $this->owncloud->get_link($path, $userid);

        $xml = simplexml_load_string($output);

        if (($xml->meta->statuscode == 100 && $xml->meta->status == 'ok') || $xml->meta->statuscode == 403) {

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
        // Fetch the Token from the DB and store it within the client.
        $token = unserialize(get_config('mod_collaborativefolders', 'token'));
        $this->owncloud->set_access_token($token);

        // If the Token is not accepted or cannot be fetched from the ownCloud Server, false is returned.
        // Further failure resolution has to be provided in near future.
        if (!$this->owncloud->is_logged_in()) {
            return false;
        }

        if (!$this->owncloud->open()) {
            return false;
        }

        // WebDAV path is generated from the required admin settings for the ownCloud Server.
        $webdavpath = '/' . get_config('tool_oauth2owncloud', 'path') . $path;

        if ($intention == 'make') {

            $code = $this->owncloud->make_folder($webdavpath);
            return $code;

        } else if ($intention == 'delete') {

            $code = $this->owncloud->delete_folder($webdavpath);
            return $code;

        } else {
            return false;
        }
    }
}