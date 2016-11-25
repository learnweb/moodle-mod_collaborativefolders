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
 * Internal library of functions for module collaborativefolders
 *
 * All the collaborativefolders specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/repository/sciebo/lib.php');
require_once($CFG->dirroot.'/lib/setuplib.php');
require_once($CFG->dirroot.'/lib/webdavlib.php');

class mylocallib {
    public function make_folder($foldername, $intention) {
        $mywebdavclient = new webdav_client('uni-muenster.sciebo.de', 'collaborativefolder.pbox@uni-muenster.de',
            '', 'basic', 'ssl://');
        $mywebdavclient->port = 443;
        $mywebdavclient->path = 'remote.php/webdav/';

        $mywebdavclient->open();
        $webdavpath = rtrim('/' . ltrim('remote.php/webdav/', '/ '), '/ ');
        if ($intention == 'make') {
            $mywebdavclient->mkcol($webdavpath . '/' . $foldername);
        }
        if ($intention == 'delete') {
            $mywebdavclient->delete($webdavpath . '/' . $foldername);
        }
        $mywebdavclient->debug = false;
        $mywebdavclient->close();
    }

    public function get_link($url) {
        // Hardcoded user data here. Has to be replaced as soon as OAuth is ready.
        // TODO How can requests be send without user data in clear text?
        $username = 'collaborativefolder.pbox@uni-muenster.de';
        $password = '';
        $pref = 'https://';

        $ch = new curl();
        $output = $ch->post($pref.$this->options['webdav_server'].'/ocs/v1.php/apps/files_sharing/api/v1/shares',
            http_build_query(array('path' => $url,
                'shareType' => 3,
                'publicUpload' => false,
                'permissions' => 31,
            ), null, "&"),
            array('CURLOPT_USERPWD' => "$username:$password"));

        $xml = simplexml_load_string($output);
        $fields = explode("/s/", $xml->data[0]->url[0]);
        $fileid = $fields[1];
        $this->logout();
        return $pref.$this->options['webdav_server'].'/public.php?service=files&t='.$fileid.'&download';
    }
}



