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
 * English strings for collaborativefolders module.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General.
$string['modulename'] = 'Collaborative folders';
$string['modulenameplural'] = 'Collaborative folders';
$string['modulename_help'] = 'Use collaborative folders to create folders in ownCloud for students for collaborative work. The folder is shared individually with members of the chosen groups as soon as they like. You do not need to collect ownCloud email addresses from your participants, everything is automated!';
$string['collaborativefolders:addinstance'] = 'Add a new collaborative folder';
$string['collaborativefolders:view'] = 'View a collaborative folder';
$string['collaborativefolders:isteacher'] = 'When viewing, be considered a non-student (with restricted access)';
$string['collaborativefolders'] = 'Collaborative folders';
$string['nocollaborativefolders'] = 'No instance of collaborative folders is active in this course.';
$string['pluginadministration'] = 'Administration of collaborative folders';
$string['pluginname'] = 'Collaborative folders';

// View.php.
$string['activityoverview'] = 'Collaborative folder';
$string['notallowed'] = 'Sorry, you are currently not allowed to view this content.';
$string['introoverview'] = 'Overview of all participating groups';
$string['infotextnogroups'] = 'This activity is available for all participants of the course.';
$string['foldernotcreatedyet'] = 'The folder has not been created in ownCloud, yet. Please contact the administrator if this message remains in a few hours.';
$string['logout'] = 'If you wish to logout from the ownCloud account you are currently logged in to, use this {$a}.';
$string['logout_heading'] = 'Logout from ownCloud';
$string['logoutpressed'] = 'You are now logged out from your ownCloud account.';
$string['generate'] = 'To generate a link to the collaborative folder, please use this {$a}.';
$string['generate_heading'] = 'Generate link to folder';
$string['generate_change_name'] = 'Change folder name';
$string['access'] = 'Click {$a} to access the folder.';
$string['access_heading'] = 'Access to the folder';
$string['folder_name'] = 'Your chosen foldername is {$a}.';
$string['naming_folder'] = 'Choose a folder name';
$string['namefield'] = 'Name';
$string['reset'] = 'You may reset your chosen foldername {$a}.';
$string['resetpressed'] = 'Your chosen name is set back to default.';
$string['save'] = 'Save name';
$string['groupid'] = 'Group ID';
$string['groupname'] = 'Group name';
$string['members'] = 'Members';
$string['here'] = 'here';

// Error messages.
$string['retry'] = 'An error occured. Please logout from your ownCloud account and try again later.';
$string['retry_rename'] = 'The concerning folder could not be renamed. Please logout from your ownCloud account and try again later.';
$string['retry_shared'] = 'The concerning folder could not be shared with you. Please check if you already have a copy of the folder. Otherwise, logout from your ownCloud account and try again later.';
$string['code'] = 'Error message: {$a}';
$string['error'] = 'An error occurred';
$string['noviewpermission'] = 'You are currently not allowed to see that content.';
$string['usernotloggedin'] = 'You are currently not logged in at ownCloud.';
$string['webdaverror'] = 'WebDAV error code {$a}';
$string['socketerror'] = 'The WebDAV socket could not be opened.';
$string['ocserror'] = 'An error with the OCS sharing API occurred.';
$string['wrongintention'] = 'The intention argument \'{$a}\' is not valid';
$string['notcreated'] = 'Folder {$a} not created. ';
$string['unexpectedcode'] = 'An unexpected status code was received.';
$string['technicalnotloggedin'] = 'The system account is not logged in or does not have authorisation in the remote system.';
$string['incompletedata'] = 'Please check the module settings. Either no OAuth 2 issuer is selected or no corresponding system account is connected.';

// Settings.
$string['chooseissuer'] = 'Issuer';
$string['oauth2serviceslink'] = '<a href="{$a}" title="Link to OAuth 2 services configuration">OAuth 2 services configuration</a>';
$string['issuervalidation_without'] = 'You have not selected an ownCloud server as the OAuth 2 issuer yet.';
$string['issuervalidation_valid'] = 'Currently the {$a} issuer is valid and active.';
$string['issuervalidation_invalid'] = 'Currently the {$a} issuer is active, however it does not implement all necessary endpoints. The repository will not work. Please choose a valid issuer.';
$string['issuervalidation_notconnected'] = 'Currently the valid {$a} issuer is active, but no system account is connected. The repository will not work. Please connect a system account.';
$string['right_issuers'] = 'The following issuers implement the required endpoints: {$a}';
$string['no_right_issuers'] = 'None of the existing issuers implement all required endpoints. Please register an appropriate issuer.';


// Adding an instance (mod_form).
$string['collaborativefoldersname'] = 'Collaborative folder name';
$string['collaborativefoldersname_help'] = 'Enter a new name that will be shown in the course homepage.';
$string['teacher_access'] = 'Teacher access';
$string['teacher_mode'] = 'Enable the teacher to have access to the folder.';
$string['teacher_mode_help'] = 'Usually, only students have access to their folders. However, if this checkbox is checked, teachers will also be granted access. Note that this setting cannot be changed after creation.';
$string['edit_after_creation'] = 'Please consider that teacher access and group-related settings cannot be changed after this activity is created.';

// Events.
$string['eventfolderscreated'] = 'The ad-hoc task successfully created all necessary folders for a collaborativefolders instance.';
$string['eventlinkgenerated'] = 'A user-specific share to a collaborative folder was created successfully.';
$string['eventloggedout'] = 'The technical user of collaborativefolders logged out.';

// Exceptions.
$string['configuration_exception'] = 'An error in the configuration of the OAuth 2 client occurred: {$a}';
$string['webdav_response_exception'] = 'WebDAV responded with an error: {$a}';
