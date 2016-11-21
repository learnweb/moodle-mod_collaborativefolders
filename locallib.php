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

require_once ($CFG->dirroot.'/repository/sciebo/lib.php');
require_once ($CFG->dirroot.'/repository/sciebo/mywebdavlib.php');
require_once ($CFG->dirroot.'/lib/setuplib.php');

class manage_webdavclient {
    public function make_folder($foldername, $intention){
        $mywebdavclient = new sciebo_webdav_client('uni-muenster.sciebo.de', 'n_herr03@uni-muenster.de',
            'password', 'basic', 'ssl://');
        $mywebdavclient->port = 443;
        $mywebdavclient->path = 'remote.php/webdav/';

        $mywebdavclient->open();
        $webdavpath = rtrim('/'.ltrim('remote.php/webdav/', '/ '), '/ ');
        if ($intention == 'make') {
            $mywebdavclient->mkcol($webdavpath . '/' . $foldername);
        }
        if ($intention == 'delete') {
            $mywebdavclient->delete($webdavpath . '/' . $foldername);
        } else {
//            TODO: right exception
        }
        $mywebdavclient->debug = false;
        $mywebdavclient->close();
    }
    public function delete_folder(){

    }
    public function set_up_client(){

    }

}
