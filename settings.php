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
 * Settings.php for oauth2sciebo admin tool. Registrates the redirection to the external setting page.
 *
 * @package    tool_oauth2sciebo
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('moodle_internal not defined');

// Settings for the OAuth 2.0 and WebDAV clients are managed on an external page.

$modcollabfolders = new admin_category('modcollab', new lang_string('pluginname', 'mod_collaborativefolders'), $module->is_enabled() === false);
$ADMIN->add('modsettings', $modcollabfolders);
$external = new admin_externalpage('collab',
        'Collaborative Folders',
        new moodle_url('/mod/collaborativefolders/init.php'));
$ADMIN->add('modcollab', $external);