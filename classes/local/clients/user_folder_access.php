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

require_once($CFG->libdir . '/webdavlib.php');
use mod_collaborativefolders\configuration_exception;
use mod_collaborativefolders\issuer_management;

/**
 * ownCloud client wrapper, intended for operations on a user's private storage.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_folder_access {
    use webdav_client_trait;
    /**
     * Additional scopes needed by the user account. Currently, ownCloud does not actually support/use scopes, so
     * this is intended as a hint at required functionality and will help declare future scopes.
     */
    const SCOPES = 'files';

    const USERINFO_CACHE_KEY = 1;

    /**
     * client instance for server access using the user's personal account
     * @var \webdav_client
     */
    private $webdav = null;

    /**
     * Basepath for WebDAV operations
     * @var string
     */
    private $davbasepath;

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
     * @param \moodle_url $oauthloginreturnurl URL that will be redirected to after the login callback has succeeded.
     * @throws configuration_exception If essential data is missing.
     */
    public function __construct (\moodle_url $oauthloginreturnurl) {
        // Get issuer and system account client. Fail early, if needed.
        $selectedissuer = get_config("collaborativefolders", "issuerid");
        if (empty($selectedissuer)) {
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }
        try {
            $this->issuer = \core\oauth2\api::get_issuer($selectedissuer);
        } catch (\dml_missing_record_exception $e) {
            // Issuer does not exist anymore.
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }

        if (!issuer_management::is_valid_issuer($this->issuer)) {
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }

        $this->userclient = $this->get_user_oauth_client($oauthloginreturnurl);
        if (!$this->userclient) {
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }

    }

    /**
     * Get a cached user authenticated oauth client.
     * @param \moodle_url $oauthloginreturnurl URL that will be redirected to after the login callback has succeeded.
     * @return \core\oauth2\client
     */
    protected function get_user_oauth_client($oauthloginreturnurl) {
        if ($this->userclient !== null) {
            return $this->userclient;
        }

        $returnurl = clone($oauthloginreturnurl);
        $returnurl->param('sesskey', sesskey());

        $this->userclient = \core\oauth2\api::get_user_oauth_client($this->issuer, $returnurl, self::SCOPES);
        return $this->userclient;
    }

    /**
     * A method, which attempts to rename a given, privately shared, folder.
     *
     * @param $pathtofolder string path, which leads to the folder that needs to be renamed.
     * @param $newname string the name which needs to be set instead of the old.
     * @return array contains status (true means success, false failure) and content (generated
     *              link or error message) of the result.
     */
    public function rename($pathtofolder, $newname) {
        $this->initiate_webdavclient($this->userclient);

        $renamed = null;

        $ret = array();

        if ($this->webdav->open()) {

            // After the socket's opening, the WebDAV MOVE method has to be performed in
            // order to rename the folder.
            // TODO check number of slashes in `dst_path`.
            $renamed = $this->webdav->move($this->davbasepath . $pathtofolder,  $this->davbasepath . '/' . $newname, false);
        } else {

            // If the socket could not be opened, a socket error needs to be returned.
            $ret['status'] = false;
            $ret['content'] = get_string('socketerror', 'mod_collaborativefolders');
            return $ret;
        }

        if ($renamed == 201) {
            // Folder successfully renamed. Name not returned because it is already known ($newname).
            $ret['status'] = true;
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
     * Deletes the held access token.
     */
    public function log_out() {
        self::clear_userinfo_cache();
        $this->userclient->log_out();
    }

    /**
     * Returns the login link for this oauth request
     *
     * @return \moodle_url login url
     */
    public function get_login_url() {
        return $this->userclient->get_login_url();
    }

    /**
     * Function which checks whether the user is logged in on the ownCloud instance.
     *
     * @return bool false, if no Access Token is set or can be requested.
     */
    public function check_login() : bool {
        if (!$this->userclient->is_logged_in()) {
            self::clear_userinfo_cache();
            return false;
        }
        return true;
    }

    /**
     * This method is used to set a field for a specific user and course module in the collaborativefolders_link
     * database table. If the specific record already exists, it gets updated in the concerning field. Otherwise,
     * a new record is inserted into the table.
     *
     * @param $cmid int ID of the course module. Needed to specify the concrete activity instance.
     * @param $groupid int the group ID to specify one particular folder.
     * @param $userid string ID of the user, which the value needs to be set for.
     * @param $value string the specific value, which needs to be set or updated.
     */
    public function store_link($cmid, $groupid, $userid, $value) {
        // TODO use persistent API instead.
        global $DB;

        $params = array(
                'cmid' => $cmid,
                'groupid' => $groupid,
                'userid' => $userid,
        );

        $record = $DB->get_record('collaborativefolders_link', $params);

        $params['link'] = $value;
        $params['owncloudusername'] = $this->get_username();
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
     * @param $cmid int the course module ID. Needed to specify the concrete activity instance.
     * @param $groupid int the group ID to specify one particular folder.
     * @param $userid string ID of the user, which the value needs to be gotten for.
     * @return object|false
     */
    public function get_link($cmid, $groupid, $userid) {
        // TODO use persistent API instead.
        global $DB;

        $params = array(
            'cmid' => $cmid,
            'groupid' => $groupid,
            'userid' => $userid,
        );

        return $DB->get_record('collaborativefolders_link', $params);
    }

    /**
     * Get the session cache that stores the user info, to save on repeated webservice calls.
     * @return \cache
     */
    private static function get_userinfo_cache() : \cache {
        return \cache::make('mod_collaborativefolders', 'userinfo');
    }

    /**
     * Clear the cached user info.
     */
    private static function clear_userinfo_cache() {
        self::get_userinfo_cache()->delete(self::USERINFO_CACHE_KEY);
    }

    /**
     * Query ownCloud for information about the authorised user (cf. the /cloud/user endpoint of ownCloud OCS),
     * using the userinfo mappings defined in Moodle.
     * @return array|false Mapped information from the /cloud/user OCS endpoint (or false on error).
     */
    public function get_userinfo() {
        $cache = self::get_userinfo_cache();
        if ($user = $cache->get(self::USERINFO_CACHE_KEY)) {
            return $user;
        }
        $user = $this->userclient->get_userinfo();
        $cache->set(self::USERINFO_CACHE_KEY, $user);
        return $user;
    }

    public function get_username() {
        $userinfo = $this->get_userinfo();
        return $userinfo['username'];
    }

    /**
     * For a given folder name, generate an HTTP URL where a browser would find the folder.
     * It is not checked whether that folder would actually exist.
     * @param string $foldername Name of a folder
     * @return string URL where the folder should be found
     */
    public function link_from_foldername($foldername) : string {
        $baseurl = rtrim($this->issuer->get('baseurl'), '/').'/';
        return sprintf('%sindex.php/apps/files/?dir=/%s', $baseurl, urlencode($foldername));
    }
}