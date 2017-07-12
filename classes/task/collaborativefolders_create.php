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

namespace mod_collaborativefolders\task;

defined('MOODLE_INTERNAL') || die;

use mod_collaborativefolders\event\folders_created;
use mod_collaborativefolders\owncloud_access;
use moodle_url;
use tool_oauth2owncloud\configuration_exception;
use tool_oauth2owncloud\webdav_response_exception;

/**
 * Ad hoc task for the creation of group folders in ownCloud.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collaborativefolders_create extends \core\task\adhoc_task {

    public function execute() {

        // Get issuer and system account client. Fail early, if needed.
        $selectedissuer = get_config("collaborativefolders", "issuerid");
        if (empty($selectedissuer)) {
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }
        $issuer = \core\oauth2\api::get_issuer($selectedissuer);
        if (!$issuer->is_system_account_connected()) {
            throw new configuration_exception(get_string('incompletedata', 'mod_collaborativefolders'));
        }

        $systemaccount = \core\oauth2\api::get_system_oauth_client($issuer);
        if (!$systemaccount) {
            throw new configuration_exception(get_string('technicalnotloggedin', 'mod_collaborativefolders'));
        }

        $customdata = $this->get_custom_data();

        foreach ($customdata['paths'] as $key => $path) {
            // If any non-responsetype related errors occur, a fitting exception is thrown beforehand.
            $code = $oc->handle_folder('make', $path);
            mtrace('Folder: ' . $path . ', Code: ' . $code);

            if (($code != 201) && ($code != 405)) {

                // If the folder could not be created, an exception is thrown.
                $error = get_string('notcreated', 'mod_collaborativefolders', $path) .
                        get_string('unexpectedcode', 'mod_collaborativefolders');
                throw new webdav_response_exception($error);
            }
        }

        $cm = get_coursemodule_from_instance('collaborativefolders', $customdata['instance']);

        $params = array(
                'objectid' => $customdata['instance'],
                'context' => \context_module::instance($cm->id)
        );

        $done = folders_created::create($params);
        $done->trigger();
    }
}