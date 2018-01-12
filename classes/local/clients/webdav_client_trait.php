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
 * Trait to centralise webdav client initialisation.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_collaborativefolders\local\clients;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/webdavlib.php');
use mod_collaborativefolders\configuration_exception;

/**
 * Trait to centralise webdav client initialisation.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait webdav_client_trait {
    /**
     * Initiates the webdav client.
     * @param \core\oauth2\client $client User or system account client
     * @return \webdav_client An initialised WebDAV client for ownCloud.
     * @throws configuration_exception If configuration is missing (endpoints).
     */
    private function initiate_webdavclient($client) : \webdav_client {
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
        } else {
            throw new configuration_exception(sprintf('Unsupported protocol type: %s.', $webdavendpoint['scheme']));
        }

        // Override default port, if a specific one is set.
        if (isset($webdavendpoint['port'])) {
            $webdavport = $webdavendpoint['port'];
        }

        // Authentication method is `bearer` for OAuth 2. Pass oauth client from which WebDAV obtains the token when needed.
        $this->webdav = new \webdav_client($server, '', '', 'bearer', $webdavtype,
            $client->get_accesstoken()->token);

        $this->davbasepath = $webdavendpoint['path'];

        $this->webdav->port = $webdavport;
        $this->webdav->debug = false;
        return $this->webdav;
    }
}