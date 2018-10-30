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
 * Library of interface functions and constants for module collaborativefolders.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/setuplib.php');

/**
 * Returns the information on whether the module supports a feature.
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function collaborativefolders_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the collaborativefolders into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $collaborativefolders Submitted data from the form in mod_form.php
 * @param mod_collaborativefolders_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted collaborativefolders record
 */
function collaborativefolders_add_instance(stdClass $collaborativefolders, mod_collaborativefolders_mod_form $mform = null) {
    global $DB;

    $collaborativefolders->timecreated = time();
    $collaborativefolders->timemodified = time();
    $collaborativefolders->id = $DB->insert_record('collaborativefolders', $collaborativefolders);

    return $collaborativefolders->id;
}

/**
 * Updates an instance of the collaborativefolders in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $collaborativefolders An object from the form in mod_form.php
 * @param mod_collaborativefolders_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function collaborativefolders_update_instance(stdClass $collaborativefolders, mod_collaborativefolders_mod_form $mform = null) {
    global $DB;

    $collaborativefolders->timemodified = time();
    $collaborativefolders->id = $collaborativefolders->instance;

    $update = $DB->update_record('collaborativefolders', $collaborativefolders);

    return $update;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every collaborativefolders event in the site is checked, else
 * only collaborativefolders events belonging to the course specified are checked.
 * This is only required if the module is generating calendar events.
 *
 * @param int $courseid Course ID
 * @return bool
 */
function collaborativefolders_refresh_events($courseid = 0) {
    global $DB;

    if ($courseid == 0) {
        if (!$DB->get_records('collaborativefolders')) {
            return true;
        }
    } else {
        if (!$DB->get_records('collaborativefolders', array('course' => $courseid))) {
            return true;
        }
    }

    return true;
}

/**
 * Removes an instance of the collaborativefolders from the database.
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function collaborativefolders_delete_instance($id) {
    global $DB;
    $cm = get_coursemodule_from_instance('collaborativefolders', $id);
    if (!empty($cm->id)) {
        $DB->delete_records('collaborativefolders_link', ['cmid' => $cm->id]);
    }
    $DB->delete_records('collaborativefolders', array('id' => $id));
    return true;
}


/**
 * Callback to get additional scopes required for system account.
 * Currently, ownCloud does not actually support/use scopes, so this is intended as a hint at required
 * functionality and will help declare future scopes.
 *
 * @param \core\oauth2\issuer $issuer
 * @return string
 */
function collaborativefolders_oauth2_system_scopes(\core\oauth2\issuer $issuer) {
    if ($issuer->get('id') == get_config('collaborativefolders', 'issuerid')) {
        return \mod_collaborativefolders\local\clients\system_folder_access::SCOPES;
    }
    return '';
}