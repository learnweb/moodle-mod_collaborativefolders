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
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General.
$string['modulename'] = 'Collaborative Folder';
$string['modulenameplural'] = 'Collaborative Folders';
$string['modulename_help'] = 'Use Collaborative Folders to create folders in ownCloud for students for collaborative Work. The folder is created on a technical user\'s ownCloud account and then shared with members of the chosen groups, as soon as they like.';
$string['collaborativefolders:addinstance'] = 'Add a new Collaborative Folder';
$string['collaborativefolders:view'] = 'View a Collaborative Folder';
$string['collaborativefolders:viewnotteacher'] = 'View a Collaborative Folder as a student';
$string['collaborativefoldersname'] = 'Name for the Collaborative Folder in Moodle:';
$string['collaborativefoldersname_help'] = 'Enter a new name, which will be displayed for this Collaborative Folder instance.';
$string['collaborativefolders'] = 'collaborativefolders';
$string['nocollaborativefolders'] = 'No instance of Collaborative Folders is active in this course.';
$string['pluginadministration'] = 'Administration of Collaborative Folders';
$string['pluginname'] = 'collaborativefolders';

// View.php.
$string['activityoverview'] = 'Overview of Collaborative Folders';
$string['notallowed'] = 'Sadly your are currently not allowed to view this content.';
$string['introoverview'] = 'Overview of all participating groups';
$string['infotextnogroups'] = 'This activity is available for all participants of the course.';
$string['foldernotcreatedyet'] = 'The Folder was not yet created. If this message remains in the next days please contact the administrator.';
$string['logout'] = 'If you wish to logout from the ownCloud account you are currently logged in to, use this {$a}.';
$string['logout_heading'] = 'Logout from ownCloud';
$string['logoutpressed'] = 'You now are logged out from your ownCloud account.';
$string['generate'] = 'To generate a link to the Collaborative Folder, please use this {$a}.';
$string['generate_heading'] = 'Generate Link to folder';
$string['generate_change_name'] = 'Change Folder Name';
$string['access'] = 'Click {$a} to access the folder.';
$string['access_heading'] = 'Access to the folder';
$string['folder_name'] = 'Your chosen foldername is {$a}.';
$string['naming_folder'] = 'Choose a folder name';
$string['namefield'] = 'Name';
$string['reset'] = 'You may reset your chosen foldername {$a}.';
$string['resetpressed'] = 'Your chosen name is set back to default.';
$string['save'] = 'Save name';
$string['groupid'] = 'Group ID';
$string['groupname'] = 'Groupname';
$string['members'] = 'Members';
$string['here'] = 'here';

// Error messages.
$string['retry'] = 'An Error occured. Please logout from your ownCloud account and try again later.';
$string['retry_rename'] = 'The concerning folder could not be renamed. Please logout from your ownCloud account and try again later.';
$string['retry_shared'] = 'The concerning folder could not be shared with you. Please check if you already have a copy of the folder.
Otherwise logout from your ownCloud account and try again later.';
$string['code'] = 'Error message: {$a}';
$string['error'] = 'An error occurred';
$string['noviewpermission'] = 'You are currently not allowed to see that content.';
$string['usernotloggedin'] = 'You are currently not logged in at ownCloud.';
$string['webdaverror'] = 'WebDAV error code {$a}';
$string['ocserror'] = 'An error with the OCS Share API occurred.';

// Global technical user.
$string['strong_recommendation'] = 'If you log out with a technical user, although there are instances of the activity, this might lead to diverse problems with synchronization.
 It is recommended that you change the technical user as infrequently as possible.';
$string['informationtechnicaluser'] = 'You can login and logout the technical user on this page. All Folders that will be created will be saved
in the storage space of the technical user. Therefore, please consider the available memory of the user.';
$string['areyousure'] = 'Are you sure you want to proceed?';
$string['logouttechnicaluser'] = 'Logout the technical user';
$string['logoutlabel'] = 'Logout';
$string['loginlabel'] = 'Login';
$string['manageheading'] = 'Manage the technical user';

// Adding an instance (mod_form).
$string['teacher_mode'] = 'Enable the teacher to have access to the folder.';
$string['edit_groups'] = 'Please consider, that the groupsettings for this activity cannot be changed after its creation.';

// Events.
$string['eventfolderscreated'] = 'The ad hoc task successfully created all necessary folders for an collaborativefolders instance.';
$string['eventlinkgenerated'] = 'A user specific link to a Collaborative Folder was created successfully.';
$string['eventloggedout'] = 'The technical user of collaborativefolders logged out.';