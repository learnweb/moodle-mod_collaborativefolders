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
 * Handles login / logout of the user client.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_sesskey();

// Parameters.
$cmid = required_param('id', PARAM_INT);
$action = required_param('action', PARAM_TEXT);

// Headers to make it not cacheable.
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Wait as long as it takes for this script to finish.
core_php_time_limit::raise();

// Predefine URL used for all redirects.
$nextpage = new \moodle_url('/mod/collaborativefolders/view.php', ['id' => $cmid]);
$userclient = new \mod_collaborativefolders\local\clients\user_folder_access(
    new \moodle_url('/mod/collaborativefolders/authorise.php', [
        'action' => 'login',
        'id' => $cmid,
        'sesskey' => sesskey()])
);

$servicename = get_config('collaborativefolders', 'servicename');

// Handle actions.
if ($action === 'logout') {
    // Remove access token.
    $userclient->log_out();
    redirect($nextpage, get_string('logoutsuccess', 'mod_collaborativefolders', $servicename), null,
        \core\output\notification::NOTIFY_SUCCESS);
    exit;
}

if ($action === 'login') {
    // Callback from remote system. Use received authorisation code to convert it into an access token.
    if ($userclient->check_login()) {
        // Token received! Continuing...
        redirect($nextpage,  get_string('loginsuccess', 'mod_collaborativefolders', $servicename), null,
            \core\output\notification::NOTIFY_SUCCESS);
    } else {
        // Authorisation failed for some reason.
        redirect($nextpage,  get_string('loginfailure', 'mod_collaborativefolders', $servicename), null,
            \core\output\notification::NOTIFY_ERROR);
    }
    exit;
}

// We got here with some unknown action. If caused by a script it has to be fixed by a programmer.
// If caused by a human who just likes to mess around with parameters, we don't actually care.
throw new \coding_exception(sprintf('Unsupported action: %s', $action));
