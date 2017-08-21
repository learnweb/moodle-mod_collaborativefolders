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
 * ownCloud client wrapper, intended for operations on a user's private storage.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_collaborativefolders\local\clients;

defined('MOODLE_INTERNAL') || die();

/**
 * ownCloud client wrapper, intended for operations on a user's private storage.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_folder_access {

    /**
     * client instance for server access using the user's personal account
     * @var \repository_owncloud\owncloud_client
     */
    private $webdav = null;

    /**
     * OAuth 2 user account client
     * @var \core\oauth2\client
     */
    private $userclient = null;

    /**
     * OAuth 2 issuer
     * @var \core\oauth2\issuer
     */
    private $issuer;

    /**
     * Construct the wrapper and initialise the user WebDAV client.
     */
    public function __construct () {
        // Get issuer and system account client. Fail early, if needed.
        $selectedissuer = get_config("collaborativefolders", "issuerid");
        if (empty($selectedissuer)) {
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }
        try {
            $this->issuer = \core\oauth2\api::get_issuer($selectedissuer);
        } catch (dml_missing_record_exception $e) {
            // Issuer does not exist anymore.
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }

        if (!$this->get_user_oauth_client()) {
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }

        $this->userclient = $this->get_user_oauth_client();

        $this->initiate_webdavclient();
    }

    /**
     * Get a cached user authenticated oauth client.
     * @return \core\oauth2\client
     */
    protected function get_user_oauth_client() {
        if ($this->userclient !== null) {
            return $this->userclient;
        }

        // TODO change repo URL and store authorisation if provided. Separate callback file?
        $returnurl = new moodle_url('/repository/repository_callback.php');
        $returnurl->param('callback', 'yes');
        $returnurl->param('repo_id', $this->id);
        $returnurl->param('sesskey', sesskey());

        $this->userclient = \core\oauth2\api::get_user_oauth_client($this->issuer, $returnurl);
        return $this->userclient;
    }

    /**
     * Initiates the webdav client.
     * @return \repository_owncloud\owncloud_client An initialised WebDAV client for ownCloud.
     * @throws \configuration_exception If configuration is missing (endpoints).
     */
    public function initiate_webdavclient() {
        if ($this->webdav !== null) {
            return $this->webdav;
        }

        $url = $this->issuer->get_endpoint_url('webdav');
        if (empty($url)) {
            throw new configuration_exception('Endpoint webdav not defined.');
        }
        $webdavendpoint = parse_url($url);

        // Selects the necessary information (port, type, server) from the path to build the webdavclient.
        $server = $webdavendpoint['host'];
        if ($webdavendpoint['scheme'] === 'https') {
            $webdavtype = 'ssl://';
            $webdavport = 443;
        } else if ($webdavendpoint['scheme'] === 'http') {
            $webdavtype = '';
            $webdavport = 80;
        }

        // Override default port, if a specific one is set.
        if (isset($webdavendpoint['port'])) {
            $webdavport = $webdavendpoint['port'];
        }

        // Authentication method is `bearer` for OAuth 2. Pass oauth client from which WebDAV obtains the token when needed.
        $this->webdav = new \repository_owncloud\owncloud_client($server, '', '', 'bearer', $webdavtype,
            $this->userclient, $webdavendpoint['path']);

        $this->webdav->port = $webdavport;
        $this->webdav->debug = false;
        return $this->webdav;
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

        if (!$this->user_loggedin()) { // TODO supposed to be done elsewhere (we may assume that user is logged in)
            // If the user is not logged in, a suitable error message is returned.
            $ret['status'] = false;
            $ret['content'] = get_string('usernotloggedin', 'mod_collaborativefolders');
            return $ret;
        }

        if ($this->webdav->open()) {

            // After the socket's opening, the WebDAV MOVE method has to be performed in
            // order to rename the folder.
            $renamed = $this->webdav->move($pathtofolder, '/' . $newname, false);
        } else {

            // If the socket could not be opened, a socket error needs to be returned.
            $ret['status'] = false;
            $ret['content'] = get_string('socketerror', 'mod_collaborativefolders');
            return $ret;
        }

        if ($renamed == 201) {

            // After the folder having been renamed, a specific link has been generated, which is to
            // be stored for each user individually.
            $link = $this->issuer->get('baseurl') . 'index.php/apps/files/?dir=' . $newname;
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
        // TODO use persistent API instead.
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
        // TODO use persistent API instead.
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
}