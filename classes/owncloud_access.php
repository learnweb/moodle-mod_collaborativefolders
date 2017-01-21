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
 *
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders;

global $CFG;
//require_once($CFG->dirroot.'/lib/setuplib.php');

use moodle_url;
use tool_oauth2sciebo\sciebo;

defined('MOODLE_INTERNAL') || die();

class owncloud_access {

    /** @var \tool_oauth2sciebo\sciebo client instance for server access. */
    private $sciebo;

    /**
     * owncloud_access constructor. The OAuth 2.0 client is initialized within it.
     */
    public function __construct () {
        $returnurl = new moodle_url('/admin/settings.php?section=modsettingcollaborativefolders', [
            'callback'  => 'yes',
            'sesskey'   => sesskey(),
        ]);
        $this->sciebo = new sciebo($returnurl);
    }
    public function get_link(){

    }

    /**
     * Method for share creation in ownCloud. A share for a specific user and folder is generated.
     * @param $path string path to the folder.
     * @param $userid string username in ownCloud.
     * @return string link to the folder.
     */
    public function generate_share($path, $userid) {
        if (get_config('tool_oauth2sciebo', 'path') === 'http') {
            $pref = 'http://';
        } else {
            $pref = 'https://';
        }

        $output = $this->sciebo->get_link($path, $userid);

        $xml = simplexml_load_string($output);

        if ($xml->meta->statuscode == 100 && $xml->meta->status == 'ok') {

            notice(get_string('successtoaddfolder', 'mod_collaborativefolders'),
                    new moodle_url('/mod/collaborativefolders/view.php'));
            $fields = explode("/s/", $xml->data[0]->url[0]);
            $fileid = $fields[1];

            return $pref . get_config('tool_oauth2sciebo', 'server').'/public.php?service=files&t=' . $fileid;

        } else {
            notice($xml->meta->message,
                    new moodle_url('/mod/collaborativefolders/view.php'));

            return false;
        }
    }

    /**
     * Method for creation and deletion of folders for collaborative work.
     * @param $foldername string specific name of the groupfolder.
     * @param $intention string 'make' for creating and 'delete' for deletion.
     * @param $id int identifier of the parent group.
     * @return bool false if an error occurred.
     */
    public function handle_folder($intention, $path) {
        global $DB;

        // Fetch the Token from the DB and store it within the client.
        $token = unserialize(get_config('mod_collaborativefolders', 'token'));
        $this->sciebo->set_access_token($token);

        // If the Token is not accepted or cannot be fetched from the ownCloud Server, false is returned.
        // Further failure resolution has to be provided in near future.
        if (!$this->sciebo->is_logged_in()) {
            return false;
        }

        if (!$this->sciebo->dav->open()) {
            return false;
        }

        // WebDAV path is generated from the required admin settings for the ownCloud Server.
        $webdavpath = '/' . get_config('tool_oauth2sciebo', 'path') . $path;

        if ($intention == 'make') {

            // If one of the folders could not be created, false is returned.
            if (($this->sciebo->make_folder($webdavpath)) != 201) {
                return false;
            }

        } else if ($intention == 'delete') {

            $this->sciebo->delete_folder($webdavpath);

        } else {
            return false;
        }
    }
}