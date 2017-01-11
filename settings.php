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

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('collaborativefolders', get_string('generalconfig', 'chat'),
        get_string('description', 'collaborativefolders')));
    $returnurl = new moodle_url('/admin/settings.php?section=modsettingcollaborativefolders', [
        'callback'  => 'yes',
        'sesskey'   => sesskey(),
    ]);

    $sciebo = new \tool_oauth2sciebo\sciebo($returnurl);

        if (empty(get_config('mod_collaborativefolders', 'token'))) {

            $url = $sciebo->get_login_url();
            $settings->add(new admin_setting_heading('LinkGenerator', 'Link',
                html_writer::link($url, 'Login', array('target' => '_blank'))));
            $sciebo->is_logged_in();
            $token = $sciebo->get_accesstoken();
            set_config('token', $token->token, 'mod_collaborativefolders');
        } else {
            $settings->add(new admin_setting_heading('collaborativefolders', 'Already Logged in',
                get_string('adminloggedininfo', 'collaborativefolders')));
        // Add logout option here. Will be done later today.
        }
}