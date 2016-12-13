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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/repository/sciebo/lib.php');
require_once($CFG->dirroot.'/lib/setuplib.php');
require_once($CFG->dirroot.'/lib/webdavlib.php');

class folder_generator{

    public function add_to_personal_account($url, $scieboidentifier, $id) {
        // Hardcoded user data here. Has to be replaced as soon as OAuth is ready.
        // TODO How can requests be send without user data in clear text?
        $username = 'collaborativefolder.pbox@uni-muenster.de';
        $password = '';
        $pref = 'https://';

        $ch = curl_init();
        // A POST request creating a share for the chosen file is generated here.
        curl_setopt($ch, CURLOPT_URL, $pref . 'uni-muenster.sciebo.de' . '/ocs/v1.php/apps/files_sharing/api/v1/shares');
//        TODO uni-muenster.de replace with webdavserver
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
        if($xml->meta->statuscode == 100 && $xml->meta->status == 'ok'){
            notice(get_string('successtoaddfolder', 'mod_collaborativefolders'), new moodle_url('/mod/collaborativefolders/view.php', array('id' => $id)));
        } else {
            notice($xml->meta->message, new moodle_url('/mod/collaborativefolders/view.php', array('id' => $id)));
        }
        return $xml;

    }
}