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
 * Confirmation window for technical user logout.
 *
 * @codeCoverageIgnore
 * @package    mod_collaborativefolders
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

defined('MOODLE_INTERNAL') || die;

$url = new moodle_url('/mod/collaborativefolders/technicallogout.php');

$PAGE->set_url($url);
require_login(null, false);

if (is_siteadmin()) {

    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('logouttechnicaluser', 'mod_collaborativefolders'));
    $PAGE->set_heading(get_string('logoutlabel', 'mod_collaborativefolders'));
    echo $OUTPUT->header();

    $confirm = get_string('strong_recommendation', 'mod_collaborativefolders') . "<p><b>"
            . get_string('areyousure', 'mod_collaborativefolders') . "</b></p>";
    $link = '/admin/settings.php?section=modsettingcollaborativefolders';
    $options = array('technicallogout' => 1);

    echo $OUTPUT->confirm($confirm, new moodle_url($link, $options), new moodle_url($link));

    echo $OUTPUT->footer();

}