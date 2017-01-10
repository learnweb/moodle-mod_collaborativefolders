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
 * index.php for oauth2sciebo admin tool. The client settings are managed in here. The main advantage of this is, that the
 * required settings are checked by the moodleform before saving them in the Admin Tree.
 *
 * @package    tool_oauth2sciebo
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require('../../lib/adminlib.php');

admin_externalpage_setup('collab');

echo $OUTPUT->header();

$returnurl = new moodle_url('/mod/collaborativefolders/init.php', [
        'callback'  => 'yes',
        'sesskey'   => sesskey(),
]);

$sciebo = new \tool_oauth2sciebo\sciebo($returnurl);

$url = $sciebo->get_login_url();

echo html_writer::link($url, 'Test Link', array('target' => '_blank'));

$sciebo->is_logged_in();

echo $sciebo->get_accesstoken()->token;

echo $OUTPUT->footer();