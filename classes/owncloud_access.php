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
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders;

use tool_oauth2owncloud\authentication_exception;
use tool_oauth2owncloud\owncloud;
use tool_oauth2owncloud\socket_exception;

defined('MOODLE_INTERNAL') || die();

class owncloud_access {

    /** @var \tool_oauth2owncloud\owncloud client instance for server access. */
    private $owncloud;

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
        // First, the technical user's Access Token needs to be checked.
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
     * Method for creation and deletion of folders for collaborative work. It is only meant to be called by the
     * concerning ad hoc task from collaborativefolders.
     *
     * @param $path string specific path of the groupfolder.
     * @param $intention string 'make' for creating and 'delete' for deletion.
     * @return int status code received from the client.
     * @throws \invalid_parameter_exception
     * @throws authentication_exception
     * @throws socket_exception
     */
    public function handle_folder($intention, $path) {
        // First, the technical user's Access Token needs to checked.
        // If it is invalid, no access to ownCloud can be granted.
        if (!$this->owncloud->check_login('mod_collaborativefolders')) {
            throw new authentication_exception(get_string('technicalnotloggedin', 'mod_collaborativefolders'));
        }

        // If no socket could be opened, no connection to the ownCloud server is available
        // via WebDAV.
        if (!$this->owncloud->open()) {
            throw new socket_exception(get_string('socketerror', 'mod_collaborativefolders'));
        }

        // WebDAV path is handed over.
        $webdavpath = '/' . $path;

        if ($intention == 'make') {

            return $this->owncloud->make_folder($webdavpath);
        } else if ($intention == 'delete') {

            return $this->owncloud->delete_folder($webdavpath);
        } else {

            // No other operations, except make and delete, are allowed.
            throw new \invalid_parameter_exception(get_string('wrongintention', 'mod_collaborativefolders', $intention));
        }
    }

    /**
     * A method, which attempts to rename a given, privately shared, folder.
     *
     * @param $pathtofolder string path, which leads to the folder that needs to be renamed.
     * @param $newname string the name which needs to be set instead of the old.
     * @param $cmid int course module ID, which is used to identify specific user preferences.
     * @param $userid string the ID of the current user. Needed to set the concerning table entry.
     * @return array contains status (true means success, false failure) and content (generated
     *              link or error message) of the result.
     */
    public function rename($pathtofolder, $newname, $cmid, $userid) {
        $renamed = null;

        $ret = array();

        if (!$this->user_loggedin()) {
            // If the user is not logged in, a suitable error message is returned.
            $ret['status'] = false;
            $ret['content'] = get_string('usernotloggedin', 'mod_collaborativefolders');
            return $ret;
        }

        if ($this->owncloud->open()) {

            // After the socket's opening, the WebDAV MOVE method has to be performed in
            // order to rename the folder.
            $renamed = $this->owncloud->move($pathtofolder, '/' . $newname, false);
        } else {

            // If the socket could not be opened, a socket error needs to be returned.
            $ret['status'] = false;
            $ret['content'] = get_string('socketerror', 'mod_collaborativefolders');
            return $ret;
        }

        if ($renamed == 201) {

            // After the folder having been renamed, a specific link has been generated, which is to
            // be stored for each user individually.
            $link = $this->owncloud->get_path('private', $newname);
            $this->set_entry('link', $cmid, $userid, $link);

            // Afterwards, the generated link is returned.
            $ret['status'] = true;
            $ret['content'] = $link;
            return $ret;
        } else {

            // If the WebDAV operation failed, a error message, containing the specific response code,
            // is returned.
            $ret['status'] = false;
            $ret['content'] = get_string('webdaverror', 'mod_collaborativefolders', $renamed);
            return $ret;
        }
    }

    /**
     * Passes the result of the check_login method from the private owncloud client.
     *
     * @return bool user login status.
     */
    public function user_loggedin() {
        return $this->owncloud->check_login();
    }

    /**
     * Releases the personal Access Token of the current user and the owncloud client.
     */
    public function logout_user() {
        set_user_preference('oC_token', null);
        $this->owncloud->log_out();
    }

    /**
     * Passes the result of the get_login_url method from the private owncloud client.
     *
     * @return \moodle_url URL to the authentication interface in ownCloud.
     */
    public function get_login_url() {
        return $this->owncloud->get_login_url();
    }

    /**
     * Passes the result of the check_data method from the private owncloud client.
     *
     * @return bool false, if any configuration data is missing. Otherwise, true.
     */
    public function check_data() {
        return $this->owncloud->check_data();
    }

    /**
     * This method first shares a folder from a technical user account with the current user.
     * Thereafter the folder is renamed to the user chosen name and the resulting private
     * link is returned. In case something goes wrong along the way, an error message is
     * returned.
     *
     * @param $sharepath string the path to the folder which has to be shared.
     * @param $renamepath string path to the folder, which needs to be renamed.
     * @param $newname string new name of the folder, chosen by the user.
     * @param $cmid int the course module ID, which is used to identify specific user preferences.
     * @param $userid string the id of the current user. Needed for rename method.
     * @return array returns an array, which contains the results of the share and rename operations.
     */
    public function share_and_rename($sharepath, $renamepath, $newname, $cmid, $userid) {
        $ret = array();
        // First, the ownCloud user ID is fetched from the current user's Access Token.
        $user = $this->owncloud->get_accesstoken()->user_id;

        // Thereafter, a share for this specific user can be created with the technical user and
        // his Access Token.
        $status = $this->generate_share($sharepath, $user);

        // If the process was successful, try to rename the folder.
        if ($status) {

            $renamed = $this->rename($renamepath, $newname, $cmid, $userid);

            if ($renamed['status'] === true) {

                $ret['status'] = true;
                $ret['content'] = $renamed['content'];
                return $ret;
            } else {

                // Renaming operation was unsuccessful.
                $ret['status'] = false;
                $ret['type'] = 'rename';
                $ret['content'] = $renamed['content'];
                return $ret;
            }
        } else {

            // The share was unsuccessful.
            $ret['status'] = false;
            $ret['type'] = 'share';
            $ret['content'] = get_string('ocserror', 'mod_collaborativefolders');
            return $ret;
        }
    }

    /**
     * This method attempts to get a specific field from an entry in the collaborativefolders_link
     * database table. It is used to get a stored folder name or link for a specific user and course
     * module.
     *
     * @param $field string the field, which value has to be returned.
     * @param $cmid int the course module ID. Needed to specify the concrete activity instance.
     * @param $userid string ID of the user, which the value needs to be gotten for.
     * @return mixed null, if the record does not exist or the field is null. Otherwise, the field's value.
     */
    public function get_entry($field, $cmid, $userid) {
        global $DB;

        $params = array(
                'userid' => $userid,
                'cmid' => $cmid
        );

        $record = $DB->get_record('collaborativefolders_link', $params);

        if (!$record) {
            return null;
        } else {
            return $record->$field;
        }
    }

    /**
     * This method is used to set a field for a specific user and course module in the collaborativefolders_link
     * database table. If the specific record already exists, it gets updated in the concerning field. Otherwise,
     * a new record is inserted into the table.
     *
     * @param $field string the specific field, which value needs to be set.
     * @param $cmid int ID of the course module, which the value needs to be set for.
     * @param $userid string ID of the user, which the value needs to be set for.
     * @param $value string the specific value, which needs to be set or updated.
     */
    public function set_entry($field, $cmid, $userid, $value) {
        global $DB;

        $params = array(
                'userid' => $userid,
                'cmid' => $cmid
        );

        $record = $DB->get_record('collaborativefolders_link', $params);

        $params[$field] = $value;
        $params = (object) $params;

        // If the record already exists, it gets updated. Otherwise, a new record is inserted.
        if (!$record) {
            $DB->insert_record('collaborativefolders_link', $params);
        } else {
            $params->id = $record->id;
            $DB->update_record('collaborativefolders_link', $params);
        }
    }
}