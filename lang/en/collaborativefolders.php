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
$string['modulename'] = 'Collaborativefolders';
$string['modulenameplural'] = 'Collaborativefolders';
$string['modulename_help'] = 'Use the collaborativefolders module to create folders for students for collaborative Work.';
$string['collaborativefolders:addinstance'] = 'Add a new collaborativefolders';
$string['collaborativefolders:submit'] = 'Submit collaborativefolders';
$string['collaborativefolders:view'] = 'View collaborativefolders';
$string['collaborativefoldersname'] = 'Name for Folder in Moodle:';
$string['collaborativefoldersname_help'] = 'Enter a new, which will be displayed for this collaborativefolders instance.';
$string['collaborativefolders'] = 'collaborativefolders';
$string['nocollaborativefolders'] = 'No instance of collaborativefolders is active in this course.';
$string['pluginadministration'] = 'collaborativefolders administration';
$string['pluginname'] = 'collaborativefolders';

// View.php.
$string['notallowed'] = 'Sadly your are currently not allowed to view this content.';
$string['introoverview'] = 'This is an overview for all groups that have a collaborativefolder.';
$string['infotextnogroups'] = 'This activity is available for all participants of the course.';
$string['foldercouldnotbecreated'] = 'The Folder was not yet created. If this message remains in the next days please contact the administrator.';
$string['logout'] = 'If you wish to logout from the ownCloud account you are currently logged in to, use this {$a}.';
$string['logout_heading'] = 'Logout from ownCloud';
$string['generate'] = 'To generate a link to the collaborative folder, please use this {$a}.';
$string['generate_heading'] = 'Generate Link to folder';
$string['access'] = 'Click {$a} to access the folder.';
$string['access_heading'] = 'Access to the folder';
$string['folder_name'] = 'Your chosen foldername is {$a}.';
$string['naming_folder'] = 'Choose a folder name';
$string['namefield'] = 'Name';
$string['reset'] = 'You may reset your chosen foldername {$a}.';
$string['retry'] = 'An Error occured. Please logout from your ownCloud account and try again later.';
$string['retry_rename'] = 'The concerning folder could not be renamed. Please logout from your ownCloud account and try again later.';
$string['retry_shared'] = 'The concerning folder could not be shared with you. Please check if you already have a copy of the folder.
Otherwise logout from your ownCloud account and try again later.';
$string['code'] = 'Error code: {$a}';
$string['error'] = 'An error occured';

// Global technical user.
$string['strong_recommendation'] = 'If you log out with a technical user, although there are instances of the activity, this might lead to diverse problems with synchronization.
 It is recommended that you change the technical user as infrequently as possible.';
$string['informationtechnicaluser'] = 'You can login and logout the technical user on this page. All Folders that will be created will be saved
in the storage space of the technical user. Therefore, please consider the available memory of the user.';
$string['areyousure'] = 'Are you sure you want to proceed?';
$string['logouttechnicaluser'] = 'Logout the technical user';
$string['logoutlabel'] = 'Logout';

// Adding an instance (mod_form).
$string['teacher_mode'] = 'Enable the teacher to have access to the folder.';
$string['edit_groups'] = 'Please consider, that the groupsettings for this activity cannot be changed after its creation.';

// Events.
$string['eventfolderscreated'] = 'The ad hoc task successfully created all necessary folders for an collaborativefolders instance.';
$string['eventlinkgenerated'] = 'A user specific link to a collaborative folder was created successfully.';
$string['eventloggedout'] = 'The technical user of collaborativefolders logged out.';