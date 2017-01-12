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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 WWU
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders;

use moodle_url;
use tool_oauth2sciebo\sciebo_client;
use webdav_client;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/repository/sciebo/lib.php');
require_once($CFG->dirroot.'/lib/setuplib.php');

class folder_generator{
    private $sciebo;
    public function __construct () {
        $returnurl = new moodle_url('/admin/settings.php?section=modsettingcollaborativefolders', [
            'callback'  => 'yes',
            'sesskey'   => sesskey(),
        ]);
        $this->sciebo = new \tool_oauth2sciebo\sciebo($returnurl);
    }

    public function add_to_personal_account($url, $scieboidentifier, $id) {
        // Hardcoded user data here. Has to be replaced as soon as OAuth is ready.
        // TODO How can requests be send without user data in clear text?
        $username = 'collaborativefolder.pbox@uni-muenster.de';
        $password = '';
        $pref = 'https://';

        $ch = curl_init();
        // A POST request creating a share for the chosen file is generated here.
        curl_setopt($ch, CURLOPT_URL, $pref . 'uni-muenster.sciebo.de' . '/ocs/v1.php/apps/files_sharing/api/v1/shares');
        curl_setopt($ch, CURLOPT_POST, 1);
        // http_build_query additionally needs a new arg_separator ("&" instead of "&amp;")
        // to be able to create the message body.
        // Additional POST arguments can be edited.
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            http_build_query(array('path' => $url,
                'shareType' => 0,
                'shareWith' => $scieboidentifier,
                'publicUpload' => true,
                'permissions' => 4,
            ), null, "&"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        $output = curl_exec($ch);
        // The output has to be transformed into an xml file to be able to extract specific arguments
        // of the response from the owncloud Server.
        $xml = simplexml_load_string($output);
        curl_close($ch);
        if ($xml->meta->statuscode == 100 && $xml->meta->status == 'ok') {
            notice(get_string('successtoaddfolder', 'mod_collaborativefolders'), new moodle_url('/mod/collaborativefolders/view.php', array('id' => $id)));
        } else {
            notice($xml->meta->message, new moodle_url('/mod/collaborativefolders/view.php', array('id' => $id)));
        }
        return $xml;

    }
    public function make_folder($foldername, $intention, $id) {
        global $DB;

        if (!$this->sciebo->dav->open()) {
            return false;
        }
        $webdavpath = rtrim('/' . ltrim('owncloud9.2/remote.php/webdav/', '/ '), '/ ');
        if ($intention == 'make') {
            $path = $webdavpath . '/' . $id;
            $namepath = $webdavpath . '/' . $id . '/' . $foldername;
            $token = get_config('mod_collaborativefolders', 'token');
            $this->sciebo->make_folder($token, $path, $namepath);


        }
        if ($intention == 'delete') {
//            $mywebdavclient->delete($webdavpath . '/' . $id . '/' . $foldername);
        }

       /* $mywebdavclient = $this->make_webdavclient();

        if ($intention == 'delete') {
            $mywebdavclient->delete($webdavpath . '/' . $id . '/' . $foldername);
        }
        $mywebdavclient->debug = false;
        $mywebdavclient->close();*/
    }

    public function get_link($url) {
        // Hardcoded user data here. Has to be replaced as soon as OAuth is ready.
        // TODO How can requests be send without user data in clear text?
//        $sciebo = new \tool_oauth2sciebo\sciebo($returnurl);

//        $sciebo->
//        $username = 'collaborativefolder.pbox@uni-muenster.de';
//        $password = '';
//        $pref = 'https://';
//
//        $ch = curl_init();
//
//        // A POST request creating a share for the chosen file is generated here.
//        curl_setopt($ch, CURLOPT_URL, $pref . 'uni-muenster.sciebo.de' . '/ocs/v1.php/apps/files_sharing/api/v1/shares');
//        curl_setopt($ch, CURLOPT_POST, 1);
//
//        // http_build_query additionally needs a new arg_separator ("&" instead of "&amp;")
//        // to be able to create the message body.
//        // Additional POST arguments can be edited.
//        curl_setopt($ch, CURLOPT_POSTFIELDS,
//            http_build_query(array('path' => $url,
//                'shareType' => 3,
//                'publicUpload' => true,
//                'permissions' => 31,
//            ), null, "&"));
//
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
//        $output = curl_exec($ch);
//
//        // The output has to be transformed into an xml file to be able to extract specific arguments
//        // of the response from the owncloud Server.
//        $xml = simplexml_load_string($output);
//
//        curl_close($ch);
//
//        // The unique fileID is extracted from the given shared link.
//        $fields = explode("/s/", $xml->data[0]->url[0]);
//        $fileid = $fields[1];
//
//        // And then its inserted into a dynamic link that will be provided to the user.
//        // WARNING: if you wish to generate a link for a local instance of owncloud, the path has to be edited
//        // in the namespace of the concerning window (e.g. http://localhost/owncloud/...).
//        return $pref . 'uni-muenster.sciebo.de' . '/public.php?service=files&t=' . $fileid;
    }

    /**
     * @param $path
     * @return bool true when 404 error is returned
     */
    /*public function check_for_404_error($foldername) {
        $mywebdavclient = $this->make_webdavclient();
        $webdavpath = rtrim('/' . ltrim('remote.php/webdav/', '/ '), '/ ');
        $result = $mywebdavclient->get($webdavpath . '/' . $foldername, $buffer);
        if ($result == 404) {
            return true;
        } else {
            return false;
        }
    }

    private function make_webdavclient() {
        $mywebdavclient = new sciebo_client('uni-muenster.sciebo.de', 'collaborativefolder.pbox@uni-muenster.de',
            '', 'basic', 'ssl://');
        $mywebdavclient->port = 443;
        $mywebdavclient->path = 'remote.php/webdav/';
        return $mywebdavclient;
    }*/
}