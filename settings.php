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
 * Settings.php for collaborativefolders activity module. Manages the login to an ownCloud account.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('moodle_internal not defined');

if ($ADMIN->fulltree) {

    // A OAuth 2.0 and WebDAV client is needed in order to login to ownCloud.
    $returnurl = new moodle_url('/admin/settings.php?section=modsettingcollaborativefolders', [
        'callback'  => 'yes',
        'sesskey'   => sesskey(),
    ]);

    $owncloud = new \tool_oauth2owncloud\owncloud($returnurl);

    // If the logout Button was pressed, the stored Access Token has to be deleted and a login link shown.
    if (isset($_GET['out'])) {

        set_config('token', null, 'mod_collaborativefolders');
        $owncloud->set_access_token(null);

        $url = $owncloud->get_login_url();
        $settings->add(new admin_setting_heading('in1', 'Login',
                html_writer::link($url, 'Login', array('target' => '_blank'))));

    } else {

        // If the technical user already has an Access Token or an upgradeable Authorization Code,
        // the token is stored a logout link shown.
        if ($owncloud->check_login('mod_collaborativefolders')) {

            $url = new moodle_url('/admin/settings.php?section=modsettingcollaborativefolders',
                    array('out' => 1));

            // Link for and warning about the logout of the technical user.
            $settings->add(new admin_setting_heading('out1', 'Change the technical user account',
                    html_writer::div(get_string('informationtechnicaluser', 'mod_collaborativefolders')) .
                    html_writer::div(get_string('strong_recommendation', 'mod_collaborativefolders'), 'warning') .
                    html_writer::link($url, 'Logout', array('onclick' => 'return confirm(\'Are you sure?\');'))));

        } else {

            // Otherwise, a login link is shown.
            set_config('token', null, 'mod_collaborativefolders');
            $url = $owncloud->get_login_url();
            $settings->add(new admin_setting_heading('in2', 'Change the technical user account',
                    html_writer::div(get_string('informationtechnicaluser', 'mod_collaborativefolders')) .
                    html_writer::link($url, 'Login', array('target' => '_blank'))));

        }
    }
}