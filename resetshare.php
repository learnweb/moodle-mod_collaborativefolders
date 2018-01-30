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
 * Resets a given share, i.e. tries to unshare it in ownCloud and removes stored information
 * about a link from Moodle.
 *
 * @package    mod_collaborativefolders
 * @copyright  2018 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_sesskey();

// Parameters.
$cmid = required_param('id', PARAM_INT);
$groupid = required_param('groupid', PARAM_INT);

// Headers to make it not cacheable.
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Wait as long as it takes for this script to finish.
core_php_time_limit::raise();

// Predefine URL used for all redirects.
$nextpage = new \moodle_url('/mod/collaborativefolders/view.php#folder-' . $groupid, ['id' => $cmid]);
$userclient = new \mod_collaborativefolders\local\clients\user_folder_access(
    new \moodle_url('/mod/collaborativefolders/resetshare.php', [
        'action' => 'login',
        'id' => $cmid,
        'sesskey' => sesskey()])
);

// TODO: also (try to) unshare from ownCloud.

$userclient->store_link($cmid, $groupid, $USER->id, null);
redirect($nextpage, get_string('resetpressed', 'mod_collaborativefolders'));

