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

use repository_owncloud\ocs_client;

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

    /**
     * client instance for server access using the system account
     * @var \repository_owncloud\owncloud_client
     */
    private $webdav = null;

    /**
     * OCS Rest client for a system account
     * @var \repository_owncloud\ocs_client
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
     * Additional scopes needed for the repository. Currently, ownCloud does not actually support/use scopes, so
     * this is intended as a hint at required functionality and will help declare future scopes.
     */
    const SCOPES = 'files ocs';

    /**
     * Construct the wrapper and initialise the clients.
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

        if (!$this->issuer->is_system_account_connected()) {
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }

        try {
            // Returns a client on success, otherwise false or throws an exception.
            $this->systemclient = \core\oauth2\api::get_system_oauth_client($this->issuer);
        } catch (\moodle_exception $e) {
            $this->systemclient = false;
        }
        if (!$this->systemclient) {
            throw new configuration_exception(get_string('technicalnotloggedin', 'mod_collaborativefolders'));
        }

        initiate_webdavclient();
        $this->ocsclient = new ocs_client($this->systemclient);
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
            $this->systemclient, $webdavendpoint['path']);

        $this->webdav->port = $webdavport;
        $this->webdav->debug = false;
        return $this->webdav;
    }

    /**
     * Method for share creation in ownCloud. A folder is shared privately with a specific user.
     *
     * @param $path string path to the folder (relative to sharing private storage).
     * @param $userid string Receiving username.
     * @return bool Success/Failure of sharing.
     */
    public function generate_share($path, $userid) {
        $response = $this->ocsclient->call('create_share', [
            'path' => $path,
            'shareType' => SHARE_TYPE_USER,
            'shareWith' => $userid,
        ]); // TODO consider permissions (default vs. wanted).

        $xml = simplexml_load_string($response);

        if ($xml === false) {
            return false;
        }

        if ((string)$xml->meta->status === 'ok') {
            // Share successfully created.
            return true;
        } else if ((string)$xml->meta->code === 403) {
            // Already shared with the specific user.
            return true;
        }

        return false;

    }

    /**
     * Method for creation of folders for collaborative work. It is only meant to be called by the
     * concerning ad hoc task from collaborativefolders.
     *
     * @param $path string specific path of the groupfolder.
     * @return int status code received from the client.
     */
    public function make_folder($path) {
        if (!$this->webdav->open()) {
            throw new socket_exception(get_string('socketerror', 'mod_collaborativefolders'));
        }
        $result = $this->webdav->mkcol($this->prefixwebdav . $path);
        $this->webdav->close();
        return $result;
    }

}