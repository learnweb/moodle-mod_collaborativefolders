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
 * ownCloud client wrapper, intended for operations on a system account.
 * No interaction for login is supported; instead it assumes that the system account
 * was connected by an administrator in the "OAuth 2 services" settings page.
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
use mod_collaborativefolders\local\sharing\share_exists_exception;
use mod_collaborativefolders\local\sharing\share_failed_exception;
use mod_collaborativefolders\ocs_client;

/**
 * ownCloud client wrapper, intended for operations on a system account.
 * No interaction for login is supported; instead it assumes that the system account
 * was connected by an administrator in the "OAuth 2 services" settings page.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class system_folder_access {
    use webdav_client_trait;

    /**
     * client instance for server access using the system account
     * @var \webdav_client
     */
    private $webdav = null;

    /**
     * Basepath for WebDAV operations
     * @var string
     */
    private $davbasepath;

    /**
     * OCS Rest client for a system account
     * @var \mod_collaborativefolders\ocs_client
     */
    private $ocsclient = null;

    /**
     * OAuth 2 system account client
     * @var \core\oauth2\client
     */
    private $systemclient;

    /**
     * OAuth 2 issuer
     * @var \core\oauth2\issuer
     */
    private $issuer;

    /**
     * Additional scopes needed by the system account. Currently, ownCloud does not actually support/use scopes, so
     * this is intended as a hint at required functionality and will help declare future scopes.
     */
    const SCOPES = 'files ocs';

    /**
     * Construct the wrapper and initialise the clients.
     * @throws \mod_collaborativefolders\configuration_exception if essential data is missing.
     */
    public function __construct () {
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

        if (!$this->issuer->is_system_account_connected()) {
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }

        try {
            // Returns a client if access token valid (or successfully redeems refresh token), otherwise false/exception.
            $this->systemclient = self::get_system_oauth_client($this->issuer);
        } catch (\moodle_exception $e) {
            $this->systemclient = false;
        }
        if (!$this->systemclient) {
            throw new configuration_exception(get_string('technicalnotloggedin', 'mod_collaborativefolders'));
        }

        $this->ocsclient = new ocs_client($this->systemclient);
    }

    /**
     * Get an authenticated oauth2 client using the system account.
     * This call uses the refresh token to get an access token.
     *
     * Modified from \core\oauth2\api::get_system_oauth_client() to create a
     * \mod_collaborateivefolders\local\client\system_client() instance, that stores the access token in the
     * application cache, to be shared by all users (not just linked to the current user).
     *
     * @param \core\oauth2\issuer $issuer
     * @return \core\oauth2\client|false An authenticated client (or false if the token could not be upgraded)
     * @throws \moodle_exception Request for token upgrade failed for technical reasons
     */
    private static function get_system_oauth_client(\core\oauth2\issuer $issuer) {
        $systemaccount = \core\oauth2\api::get_system_account($issuer);
        if (empty($systemaccount)) {
            return false;
        }
        // Get all the scopes!
        $scopes = \core\oauth2\api::get_system_scopes_for_issuer($issuer);

        $client = new system_client($issuer, null, $scopes, true);

        if (!$client->is_logged_in()) {
            if (!$client->upgrade_refresh_token($systemaccount)) {
                return false;
            }
        }
        return $client;
    }

    /**
     * The cron process may have refreshed the system token in the background without telling us,
     * so check to see if we need to generate a new token.
     */
    private function verify_system_access() {
        $response = $this->ocsclient->call('get_shares', [
            'path' => '/',
            'reshares' => false,
            'subfiles' => false,
        ]);
        $xml = simplexml_load_string($response);
        if ($xml === false || (string)$xml->meta->status !== 'ok') {
            // Connection not working - try refreshing the token.
            $systemaccount = \core\oauth2\api::get_system_account($this->issuer);
            $this->systemclient->upgrade_refresh_token($systemaccount);
        }
    }

    /**
     * Method for share creation in ownCloud. A folder is shared privately with a specific user.
     *
     * @param string $path path to the folder (relative to sharing private storage).
     * @param string $username Receiving ownCloud username.
     * @param string $chosenname (optional) the name of the shared folder within the user's ownCloud
     * @return \SimpleXMLElement Excerpt from the XML response on success.
     * @throws share_exists_exception If the folder had already been shared prior.
     * @throws share_failed_exception If calling the OCS API resulted in an unknown state.
     */
    public function generate_share(string $path, string $username, string $chosenname = null) {
        $this->verify_system_access();

        if (!$this->make_folder($path)) {
            throw new share_failed_exception('webdaverror', 'mod_collaborativefolders');
        }

        $params = [
            'path' => $path,
            'shareType' => ocs_client::SHARE_TYPE_USER,
            'shareWith' => $username,
            'permissions' => ocs_client::SHARE_PERMISSION_ALL, // (Read, update, create, delete, share).
        ];
        if ($chosenname !== null) {
            $chosenname = \core_text::substr($chosenname, 0, 64); // Make sure the name is <= 64 characters.
            $params['name'] = $chosenname;
        }
        $response = $this->ocsclient->call('create_share', $params);

        $xml = simplexml_load_string($response);

        if ($xml === false) {
            throw new share_failed_exception(get_string('ocserror', 'mod_collaborativefolders'));
        }

        if ((string)$xml->meta->status === 'ok') {
            // Share successfully created.
            return $xml->data;
        }

        if ((string)$xml->meta->statuscode === '403') {
            // Already shared with the specific user; require calling code to find out its name.
            throw new share_exists_exception();
        }

        throw new share_failed_exception(get_string('ocserror', 'mod_collaborativefolders'));

    }

    /**
     * Get the existing share for the given path to the given user
     * @param string $path
     * @param string $username
     * @return string|null
     */
    public function get_existing_share_path(string $path, string $username) {

        // Get all the shares for the given path.
        $response = $this->ocsclient->call('get_shares', [
            'path' => $path,
            'reshares' => false,
            'subfiles' => false,
        ]);
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            return null;
        }
        if ((string)$xml->meta->status !== 'ok') {
            return null;
        }

        // Loop through each of the shares.
        foreach ($xml->data->element as $share) {
            if ((string)$share->share_with !== $username) {
                // Share isn't to the user we're interested in - skip it.
                continue;
            }
            // We've found the share we wanted - return the path it is shared to.
            return (string)$share->file_target;
        }
        // Not found the requested user in the list of shares.
        return null;
    }

    /**
     * Method for creation of folders for collaborative work. It is only meant to be called by the
     * concerning ad hoc task from collaborativefolders.
     *
     * @param string $path specific path of the groupfolder.
     * @param bool $recursive set to true to check + create all folders in the path
     * @return int status code received from the client.
     * @throws \moodle_exception on connection error.
     */
    public function make_folder($path, $recursive = true) : int {
        $this->initiate_webdavclient($this->systemclient);
        if (!$this->webdav->open()) {
            throw new \moodle_exception(get_string('socketerror', 'mod_collaborativefolders'));
        }
        if ($recursive) {
            $result = true;
            $parts = array_filter(explode('/', $path));
            $currpath = rtrim($this->davbasepath, '/');
            foreach ($parts as $part) {
                $currpath .= '/'.$part;
                if (!$this->webdav->is_dir($currpath)) {
                    // Folder doesn't already exist.
                    if (!$this->rename_by_id($currpath)) {
                        // Couldn't fix by renaming an existing folder - create a new folder.
                        $result = $result && $this->webdav->mkcol($currpath);
                    }
                }
            }
        } else {
            $result = $this->webdav->mkcol($this->davbasepath.$path);
        }
        $this->webdav->close();
        return $result;
    }

    /**
     * See if the given folder does exist, but the course / activity has been renamed.
     * If it has been renamed, then rename the webdav folder.
     * @param string $path the path to check
     * @return bool true if we managed to locate a suitable folder
     */
    public function rename_by_id($path) {
        $idregex = '| \(id (\d+)\)$|';
        if (!preg_match($idregex, $path, $matches)) {
            return false;
        }
        list(, $id) = $matches;
        $idmatch = '| \(id '.$id.'\)$|';
        $legacyidmatch = '|_id_'.$id.'$|'; // For folders created with earlier versions of mod_collaborativefolders.
        $dir = dirname($path);
        $files = $this->webdav->ls($dir);
        if (!$files) {
            return false;
        }
        foreach ($files as $file) {
            $filepath = urldecode(rtrim($file['href'], '/'));
            if ($filepath === $dir) {
                continue;
            }
            if ($file['resourcetype'] !== 'collection') {
                continue;
            }

            if (substr($filepath, -strlen($idmatch)) === $idmatch ||
                substr($filepath, -strlen($legacyidmatch)) === $legacyidmatch) {
                // We've found a folder with the same id, but a different name - rename the folder.
                if (!$this->webdav->move($filepath, $path, false)) {
                    return false;
                }
                return true;
            }
        }

        return false;
    }
}