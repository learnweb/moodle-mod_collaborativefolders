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

/**
 * ownCloud class for oauth2owncloud admin tool. Handles all access to ownCloud via WebDAV
 * and the OCS Share API. Uses OAuth 2.0 as the authentication and authorization protocol.
 *
 * @package    tool_oauth2owncloud
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders;
use \stdClass;

defined('MOODLE_INTERNAL') || die();

class owncloud extends \core\oauth2\client {

    /** @var null|owncloud_client webdav client which is used for webdav operations. */
    private $dav = null;

    /** @var mixed|null webdav port, either set by the user or by a default value. */
    private $webdavport = null;

    /** @var null|string type of webdav connection. Depending on the chosen protocol. */
    private $webdavtype = null;

    /** @var null|string prefix for webdav paths.*/
    private $prefixwebdav = null;

    /** @var null|string prefix for paths to owncloud directories and interfaces. */
    private $prefixoc = null;

    /**
     * Create the ownCloud OAuth 2.0 and WebDAV clients. The required data for both clients is fetched from the
     * oauth2owncloud admin settings entered before by the user.
     *
     * @param \moodle_url $callback The callback URL, which the user gets redirected to after authorization.
     */
    public function __construct($callback) {
        $server = get_config('tool_oauth2owncloud', 'server');
        $clientid = get_config('tool_oauth2owncloud', 'clientid');
        $secret = get_config('tool_oauth2owncloud', 'secret');
        $protocol = get_config('tool_oauth2owncloud', 'protocol');
        $port = get_config('tool_oauth2owncloud', 'port');
        $path = get_config('tool_oauth2owncloud', 'path');

        parent::__construct($clientid, $secret, $callback, '');

        // The WebDAV attributes are set beforehand.
        if (empty($server)) {
            return;
        }
        if ('http' == $protocol) {
            $this->webdavtype = '';
        } else {
            $this->webdavtype = 'ssl://';
        }
        if (empty($port)) {
            if (empty($this->webdavtype)) {
                $this->webdavport = 80;
                set_config('port', 80, 'tool_oauth2owncloud');
            } else {
                $this->webdavport = 443;
                set_config('port', 443, 'tool_oauth2owncloud');
            }
        } else {
            $this->webdavport = $port;
        }

        // Authentication method is set to Bearer, since we use OAuth 2.0.
        $this->dav = new owncloud_client($server, '', '', 'bearer', $this->webdavtype);
        $this->dav->port = $this->webdavport;
        $this->dav->debug = false;

        $this->prefixwebdav = rtrim('/'.ltrim($path, '/ '), '/ ');

        $p = str_replace('remote.php/webdav/', '', $path);

        $this->prefixoc = $protocol . '://' . $server . ':' . $this->webdavport . '/' . $p;
    }

    /**
     * Helper method, that checks the admin settings regarding the OAuth 2.0 and WebDAV clients required for this
     * plugin. If at least one of the settings is empty, false is returned.
     *
     * @return bool false, if data is missing. Otherwise, true.
     */
    public function check_data() {
        if (empty(get_config('tool_oauth2owncloud', 'clientid')) ||
            empty(get_config('tool_oauth2owncloud', 'secret')) ||
            empty(get_config('tool_oauth2owncloud', 'server')) ||
            empty(get_config('tool_oauth2owncloud', 'path')) ||
            empty(get_config('tool_oauth2owncloud', 'protocol'))) {

            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns the auth url for OAuth 2.0 request
     *
     * @return string the auth url
     */
    protected function auth_url() {
        // Dynamically generated from the admin tool settings.
        return $this->prefixoc . 'index.php/apps/oauth2/authorize';
    }

    /**
     * Returns the token url for OAuth 2.0 request
     *
     * @return string the token url
     */
    protected function token_url() {
        return $this->prefixoc . 'index.php/apps/oauth2/api/v1/token';
    }

    /**
     * Setter method for the Access Token.
     *
     * @param $token \stdClass which is to be stored inside the Client.
     */
    public function set_access_token($token) {
        $this->store_token($token);
    }

    /**
     * Sets up a new Access Token after redirection from ownCloud. Therefore the old Token has to be discarded and a
     * new one requested with the authorization code.
     */
    public function callback() {
        $this->log_out();
        $this->is_logged_in();
    }

    /**
     * Checks whether or not the current user or a specific module (e.g. in form of a
     * technical user) possesses a valid Access Token. If it can be upgraded from an
     * Refresh Token the new Access Token gets stored in the settings.
     *
     * @param null|string $modulename name of the plugin, which stores a seperate Access Token.
     * @return bool false, if the Access Token is not valid. Otherwise, true.
     */
    public function check_login($modulename = null) {
        // If $modulename is null, a personal token has to be checked (current user).
        if ($modulename == null) {

            $usertoken = unserialize(get_user_preferences('oC_token'));
            $this->set_access_token($usertoken);

        } else {

            // Otherwise, the Access Token is fetched from the module's settings.
            $moduletoken = unserialize(get_config($modulename, 'token'));
            $this->set_access_token($moduletoken);

        }

        // If an Access Token is available or can be refreshed, it is stored within the user specific
        // preferences or the module settings (depending on $modulename).
        if ($this->is_logged_in()) {

            // In both cases the Access Token needs to be serialized before it can be stored in the DB.
            $tok = serialize($this->get_accesstoken());

            if ($modulename == null) {
                set_user_preference('oC_token', $tok);
            } else {
                set_config('token', $tok, $modulename);
            }

            return true;

        } else {

            // Otherwise it is set to null.
            if ($modulename == null) {
                set_user_preference('oC_token', null);
            } else {
                set_config('token', null, $modulename);
            }

            return false;
        }
    }

    /**
     * Redirects to the parent method after checking the Refresh Token.
     *
     * @return bool true, if Access Token is set.
     */
    public function is_logged_in() {
        // Has the token expired?
        if (isset($this->get_accesstoken()->expires) && time() >= $this->get_accesstoken()->expires) {

            // If the Access Token has expired and we possess a Refresh Token, a new Access Token is requested.
            try {
                if ((isset($this->get_accesstoken()->refresh_token)) &&
                    $this->upgrade_token($this->get_accesstoken()->refresh_token, true)
                ) {
                    return true;
                } else {
                    $this->log_out();
                    return false;
                }
            } catch (\Exception $e) {
                // Error during upgrade_token, e.g. if access rights were revoked.
                $this->log_out();
                return false;
            }
        }
        // If the token has not expired yet, the parent method checks if an Access Token or Authorization
        // Code is available.
        return parent::is_logged_in();
    }

    /**
     * Overwrites the parent method, since a possibility to request an Access Token from a Refresh Token is needed.
     * Furthermore the Access Token Object, besides access_token and expires_in, has the properties user_id and
     * refresh_token, which are used by the implemented clients.
     *
     * @param string $code Authorization Code or Refresh Token.
     * @param bool $refresh indicates whether a Refresh Token has been passed.
     * @return bool true is Access Token is fetched from the server, false if not.
     */
    public function upgrade_token($code, $refresh = false) {
        $callbackurl = self::callback_url();

        if ($refresh == false) {
            $grant = 'authorization_code';
            $type = 'code';
        } else {
            $grant = 'refresh_token';
            $type = 'refresh_token';
        }

        $params = array(
                'grant_type' => $grant,
                $type => $code,
                'redirect_uri' => $callbackurl->out(false),
        );

        $response = $this->post($this->token_url(), $params);

        if ($this->info['http_code'] !== 200) {
            throw new \moodle_exception('Could not upgrade oauth token');
        }

        $r = json_decode($response);

        if (!isset($r->access_token)) {
            return false;
        }

        // Store the token, expiry time, the user id and refresh token.
        $accesstoken = new stdClass;
        $accesstoken->token = $r->access_token;
        $accesstoken->expires = (time() + ($r->expires_in - 10));
        $accesstoken->user_id = $r->user_id;
        $accesstoken->refresh_token = $r->refresh_token;

        $this->store_token($accesstoken);

        return true;
    }

    /**
     * This method calls the open() function of the webdav client.
     *
     * @return bool true on success. Otherwise false.
     */
    public function open() {
        return $this->dav->open();
    }

    /**
     * The WebDav listing function is encapsulated into this helper function. Before the WebDAV function is called,
     * an Access Token is set within the Client to enable OAuth 2.0 authentication.
     *
     * @param $path string relative path to the file or directory.
     * @return array information about the file or directory.
     */
    public function get_listing($path) {
        $this->dav->set_token($this->get_accesstoken()->token);
        return $this->dav->ls($this->prefixwebdav . $path);
    }

    /**
     * The WebDav function get_file is encapsulated into this helper function. Before the WebDAV function is called,
     * an Access Token is set within the Client to enable OAuth 2.0 authentication.
     *
     * @param $source string sourcepath of the file.
     * @param $local string local path in which the file shall be stored.
     * @return bool true on success, false otherwise.
     */
    public function get_file($source, $local) {
        $this->dav->set_token($this->get_accesstoken()->token);
        return $this->dav->get_file($this->prefixwebdav . $source, $local);
    }

    /**
     * The WebDav function mkcol is encapsulated into this helper function. Before the WebDAV function is called,
     * an Access Token is set within the Client to enable OAuth 2.0 authentication.
     *
     * @param $path string path in which the collection shall be created.
     * @return int status code retrieved from server response.
     */
    public function make_folder($path) {
        $this->dav->set_token($this->get_accesstoken()->token);
        return $this->dav->mkcol($this->prefixwebdav . $path);
    }

    /**
     * The WebDav function get_file is encapsulated into this helper function. Before the WebDAV function is called,
     * an Access Token is set within the Client to enable OAuth 2.0 authentication.
     *
     * @param $path string path to the folder which shall be deleted.
     * @return int status code retrieved from the server response.
     */
    public function delete_folder($path) {
        $this->dav->set_token($this->get_accesstoken()->token);
        return $this->dav->delete($this->prefixwebdav . $path);
    }

    /**
     * The WebDav function move is encapsulated into this helper function. Before the WebDAV function is called,
     * an Access Token is set within the Client to enable OAuth 2.0 authentication.
     *
     * @param $src string path to the folder/file which shall be moved.
     * @param $dst string path to the folder/file which shall be moved.
     * @param $overwrite bool true, if an existing folder/file should be overwritten.
     * @return int status code retrieved from the server response.
     */
    public function move($src, $dst, $overwrite) {
        $this->dav->set_token($this->get_accesstoken()->token);

        $source = $this->prefixwebdav . $src;

        $destination = $this->prefixwebdav . $dst;

        return $this->dav->move($source, $destination, $overwrite);
    }

    /**
     * This function fetches a link to a specific folder or file in ownCloud through the OCS Share API. Therefore the
     * API had to be extended to support authentication via an Access Token.
     *
     * @param $path string path to the file or folder in ownCloud.
     * @param null $user string specific user to be shared with (optional).
     * @return array response from ownCloud server including status, code and link to the file.
     */
    public function get_link($path, $user = null) {
        // Depending on whether a public share or a specific user share is requested, the POST parameters are set.
        if ($user == null) {
            $query = http_build_query(array('path' => $path,
                                            'shareType' => 3,
                                            'publicUpload' => false,
                                            'permissions' => 31
                                            ), null, "&");
        } else {
            $query = http_build_query(array('path' => $path,
                                            'shareType' => 0,
                                            'shareWith' => $user,
                                            ), null, "&");
        }

        // The share request gets POSTed.
        $response = $this->post($this->prefixoc . 'ocs/v1.php/apps/files_sharing/api/v1/shares',
                $query, array(), true);

        $ret = array();

        $xml = simplexml_load_string($response);
        $ret['code'] = $xml->meta->statuscode;
        $ret['status'] = $xml->meta->status;

        // The link is generated, only if it is a public share.
        if ($user == null) {

            $fields = explode("/s/", $xml->data[0]->url[0]);
            $fileid = $fields[1];

            $ret['link'] = $this->get_path('public', $fileid);
        }

        return $ret;
    }

    /**
     * This method is used to generate file and folder paths to ownCloud after a successful share.
     * Depending on the share type (public or private share), it returns the path to the shared
     * file or folder.
     *
     * @param $type string either personal or private. Depending on share type.
     * @param $id string file or folder id of the concerning content.
     * @return bool|string returns the generated path, if $type it personal or private. Otherwise, false.
     */
    public function get_path($type, $id) {
        switch ($type) {
            case 'public':
                return $this->prefixoc . 'public.php?service=files&t=' . $id . '&download';
            case 'private':
                return $this->prefixoc . 'index.php/apps/files/?dir=' . $id;
            default:
                return false;
        }
    }

    /**
     * Due to the fact, that the user credentials for client authentication in ownCloud need to be provided
     * by an Basic Authorization Header instead of POST parameters, the cURL function post is extended by
     * an option to set such header.
     * This header is needed for Access Token requests with an Authorization Code or Refresh Token.
     *
     * @param string $url URL which the request has to be sent to.
     * @param string|array $params POST parameters.
     * @param array $options cURL options for the request.
     * @param bool $auth indicates whether a Basic Authentication Header has to be added to the request.
     * @return mixed response from ownCloud server or error message.
     */
    public function post($url, $params = '', $options = array(), $auth = false) {

        if ($auth == false) {

            // A basic auth header has to be added to the request for client authentication in ownCloud.
            $this->setHeader(array(
                    'Authorization: Basic ' . base64_encode($this->get_clientid() . ':' . $this->get_clientsecret())
            ));

            // If an Access Token is stored within the Client, it has to be deleted to prevent the addidion
            // of an Bearer Authorization Header in the request method.
            $this->log_out();

        }

        return parent::post($url, $params, $options);
    }
}