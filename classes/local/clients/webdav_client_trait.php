<?php
/**
 * Created by IntelliJ IDEA.
 * User: j_dage01
 * Date: 29.08.17
 * Time: 02:41
 */

namespace mod_collaborativefolders\local\clients;


use mod_collaborativefolders\configuration_exception;

trait webdav_client_trait {
    /**
     * Initiates the webdav client.
     * @param \core\oauth2\client $client User or system account client
     * @return \repository_owncloud\owncloud_client An initialised WebDAV client for ownCloud.
     * @throws configuration_exception If configuration is missing (endpoints).
     */
    private function initiate_webdavclient($client) {
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
        $this->webdav = new \repository_owncloud\owncloud_client($server, '', '', 'bearer', $webdavtype,
            $client, $webdavendpoint['path']);

        $this->webdav->port = $webdavport;
        $this->webdav->debug = false;
        return $this->webdav;
    }
}