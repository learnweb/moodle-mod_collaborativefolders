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

$string['accessfolders'] = 'Folder access';
$string['activityoverview'] = 'Collaborative folder';
$string['btnlogin'] = 'Login';
$string['btnlogout'] = 'Logout ({$a})';
$string['cachedef_token'] = 'OAuth system client token';
$string['cachedef_userinfo'] = 'OAuth user client user info';
$string['cannotaccess'] = 'If the above link does not work, and you cannot find the folder, click the button on the left to reset the share. This helps you regain access without making changes to the files within that folder.';
$string['cannotaccessheader'] = 'No access?';
$string['chooseissuer'] = 'Issuer';
$string['collaborativefolders'] = 'Collaborative folder';
$string['collaborativefolders:addinstance'] = 'Add a new collaborative folder';
$string['collaborativefolders:isteacher'] = 'When viewing, be considered a non-student (with restricted access)';
$string['collaborativefolders:view'] = 'View a collaborative folder';
$string['collaborativefoldersname'] = 'Collaborative folder name';
$string['collaborativefoldersname_help'] = 'Enter a new name that will be shown in the course homepage.';
$string['configuration_exception'] = 'An error in the configuration of the OAuth 2 client occurred: {$a}';
$string['creationstatus'] = 'Folder status';
$string['creationstatus_created'] = 'Folder(s) created';
$string['creationstatus_pending'] = 'Folder(s) will be created soon';
$string['edit_after_creation'] = 'Please consider that teacher access and group-related settings cannot be changed after this activity is created.';
$string['error_illegalpathchars'] = 'A valid folder or path name must be entered. Use \'/\' (slash) to delimit directories of a path.';
$string['eventlinkgenerated'] = 'A user-specific share to a collaborative folder was created successfully.';
$string['folder'] = 'Folder';
$string['foldershared'] = 'The folder was successfully shared to your {$a}.';
$string['getaccess'] = 'Get access';
$string['grouplabel'] = 'Group: {$a}';
$string['groupmode'] = 'Mode';
$string['groupmode_off'] = 'One folder for the entire course';
$string['groupmode_on'] = 'One folder per group';
$string['groups'] = 'Groups';
$string['incompletedata'] = 'Please check the module settings. Either no OAuth 2 issuer is selected or no corresponding system account is connected.';
$string['issuer_choice_unconfigured'] = '(unconfigured)';
$string['issuervalidation_invalid'] = 'Currently the {$a} issuer is active, however it does not implement all necessary endpoints. The plugin will not work. Please choose a valid issuer.';
$string['issuervalidation_notconnected'] = 'Currently the valid {$a} issuer is active, but no system account is connected. The plugin will not work. Please connect a system account.';
$string['issuervalidation_valid'] = 'Currently the {$a} issuer is valid and active.';
$string['issuervalidation_without'] = 'You have not selected an OAuth 2 issuer yet.';
$string['loginfailure'] = 'A problem occurred: Not authorised to connect to {$a}.';
$string['loginsuccess'] = 'Successfully authorised to connect to {$a}.';
$string['logoutsuccess'] = 'Successfully logged out from {$a}.';
$string['modulename'] = 'Collaborative folder';
$string['modulename_help'] = 'Use collaborative folders to create folders in the cloud (ownCloud, Nextcloud) for students for collaborative work. The folder is shared individually with members of the chosen groups as soon as they like. You do not need to collect any email addresses from your participants, everything is automated!';
$string['modulenameplural'] = 'Collaborative folders';
$string['namefield'] = 'Name';
$string['namefield_explanation'] = 'Choose a name under which the shared folder will be stored in your {$a}.';
$string['namemismatch'] = 'Warning: this folder was shared with \'{$a->link}\' but you are logged in as \'{$a->current}\' - you may need to switch logins to access the files.';
$string['no_right_issuers'] = 'None of the existing issuers implement all required endpoints. Please register an appropriate issuer.';
$string['nocollaborativefolders'] = 'There are no collaborative folders in this course.';
$string['nogroups'] = 'No groups';
$string['notcreated'] = 'Folder {$a} not created. ';
$string['notingroup'] = 'You are not in any groups, so you do not have access to any folders.';
$string['oauth2serviceslink'] = '<a href="{$a}" title="Link to OAuth 2 services configuration">OAuth 2 services configuration</a>';
$string['ocserror'] = 'An error with the OCS sharing API occurred.';
$string['openinowncloud'] = 'Open in {$a}';
$string['overview'] = 'Overview';
$string['pluginadministration'] = 'Collaborative folder administration';
$string['pluginname'] = 'Collaborative folder';
$string['privacy:metadata:collaborativefolders_link'] = 'Information about folders that have been shared to users';
$string['privacy:metadata:collaborativefolders_link:cmid'] = 'The course module this folder share is associated with';
$string['privacy:metadata:collaborativefolders_link:groupid'] = 'The Moodle course group the shared folder relates to';
$string['privacy:metadata:collaborativefolders_link:link'] = 'The name given to the folder when it was shared';
$string['privacy:metadata:collaborativefolders_link:owncloudusername'] = 'The OwnCloud user that the folder was shared with';
$string['privacy:metadata:collaborativefolders_link:userid'] = 'The Moodle user the folder was shared with';
$string['problem_misconfiguration'] = 'The plugin is not configured correctly or the server is not reachable. Please contact your administrator to resolve this issue.';
$string['problem_nosystemconnection'] = 'The system account is unable to connect to {$a}, so folders for this activity will not be created. Please inform the administrator about this.';
$string['problem_sharessuppressed'] = 'The system account is unable to connect to {$a->servicename}, so {$a->sharessuppressed} folders were not displayed. Please inform the administrator about this.';
$string['remotesystem'] = 'Connection to {$a}';
$string['resetpressed'] = 'Share reset. You can now obtain access to your folder again.';
$string['right_issuers'] = 'The following issuers implement the required endpoints: {$a}';
$string['servicename'] = 'Service display name';
$string['share_exists_exception'] = 'The folder is already shared with you. {$a}';
$string['share_failed_exception'] = 'Unable to share the folder with you: {$a}';
$string['sharedtoowncloud'] = 'This folder has already been shared to your {$a}.';
$string['socketerror'] = 'The WebDAV socket could not be opened.';
$string['solveproblems'] = 'Solve problems';
$string['teacher_access'] = 'Teacher access';
$string['teacher_mode'] = 'Enable the teacher to have access to the folder.';
$string['teacher_mode_help'] = 'Usually only students have access to their folders. However, if this checkbox is checked, teachers will also be granted access. Note that this setting cannot be changed after creation.';
$string['teacheraccess_no'] = 'Folders remain private from teachers';
$string['teacheraccess_yes'] = 'Teachers have access to all folders';
$string['teachersnotallowed'] = 'Sorry, teachers are not allowed to view this content.';
$string['technicalnotloggedin'] = 'The system account is not logged in or does not have authorisation in the remote system.';
$string['unexpectedcode'] = 'An unexpected response status code ({$a}) was received.';
$string['usernotloggedin'] = 'You are currently not logged in at the remote system.';
$string['webdav_response_exception'] = 'WebDAV responded with an error: {$a}';
$string['webdaverror'] = 'WebDAV error code {$a}';
