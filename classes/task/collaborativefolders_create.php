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

use mod_collaborativefolders\configuration_exception;
use mod_collaborativefolders\event\folders_created;
use mod_collaborativefolders\local\clients\system_folder_access;

/**
 * Ad hoc task for the creation of group folders in ownCloud.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class collaborativefolders_create extends \core\task\adhoc_task {

    /**
     * Create one folder per group, as specified by \mod_collaborativefolders\observer::collaborativefolders_created.
     */
    public function execute() {
        // Get a client logged in as the system user.
        try {
            $ocaccess = new system_folder_access();
        } catch (configuration_exception $e) {
            mtrace(sprintf('System client not configured: %s', $e->getMessage()));
            // Re-throw so that task is not marked as finished. End.
            throw $e;
        }

        $errors = array();
        $customdata = $this->get_custom_data();

        foreach ($customdata->paths as $path) {
            // If a request or something along the way fails badly, make_folders throws an exception.
            // TODO treat this identical to wrong status codes!
            $statuscode = $ocaccess->make_folder($path);
            mtrace('Folder: ' . $path . ', Code: ' . $statuscode);

            // Legend:
            // 201: Created. (Expected regularly.)
            // 405: Method not allowed. Will be raised if folder already exists. Also expected, although less regularly.
            $requestok = $statuscode == 201 || $statuscode == 405;

            // Request failed; record.
            if (!$requestok) {
                // If the folder could not be created, record it for later logging.
                $errors[] = get_string('notcreated', 'mod_collaborativefolders', $path) .
                        get_string('unexpectedcode', 'mod_collaborativefolders', $statuscode);
            }
        }

        if (!empty($errors)) {
            // TODO Not happy with this! Handle such cases appropriately! e.g. new task, message to someone, ...?
            $errorsformatted = implode('; ', $errors);
            mtrace(sprintf('The following errors occurred: %s', $errorsformatted));
            throw new \moodle_exception($errorsformatted);
        }

        // Record successful run.
        $cm = get_coursemodule_from_instance('collaborativefolders', $customdata->instance);
        $done = folders_created::create([
            'objectid' => $customdata->instance,
            'context' => \context_module::instance($cm->id)
            ]
        );
        $done->trigger();
    }
}