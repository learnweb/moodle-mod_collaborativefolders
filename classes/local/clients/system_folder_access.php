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
            // Returns a client on success, otherwise false or throws an exception.
            $this->systemclient = \core\oauth2\api::get_system_oauth_client($this->issuer);
        } catch (\moodle_exception $e) {
            $this->systemclient = false;
        }
        if (!$this->systemclient) {
            throw new configuration_exception(get_string('technicalnotloggedin', 'mod_collaborativefolders'));
        }

        $this->ocsclient = new ocs_client($this->systemclient);
    }

    /**
     * Method for share creation in ownCloud. A folder is shared privately with a specific user.
     *
     * @param $path string path to the folder (relative to sharing private storage).
     * @param $userid string Receiving username.
     * @return \SimpleXMLElement Excerpt from the XML response on success.
     * @throws share_exists_exception If the folder had already been shared prior.
     * @throws share_failed_exception If calling the OCS API resulted in an unknown state.
     */
    public function generate_share($path, $userid) {
        $response = $this->ocsclient->call('create_share', [
            'path' => $path,
            'shareType' => ocs_client::SHARE_TYPE_USER,
            'shareWith' => $userid,
        ]); // TODO consider permissions (default vs. wanted).

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
     * Method for creation of folders for collaborative work. It is only meant to be called by the
     * concerning ad hoc task from collaborativefolders.
     *
     * @param string $path specific path of the groupfolder.
     * @return int status code received from the client.
     * @throws \moodle_exception on connection error.
     */
    public function make_folder($path) : int {
        $this->initiate_webdavclient($this->systemclient);
        if (!$this->webdav->open()) {
            throw new \moodle_exception(get_string('socketerror', 'mod_collaborativefolders'));
        }
        $result = $this->webdav->mkcol($this->davbasepath . $path);
        $this->webdav->close();
        return $result;
    }

}