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
 * Renderer for the Web interface of collaborativefolders module.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use mod_collaborativefolders\output\statusinfo;

defined('MOODLE_INTERNAL') || die;

/**
 * Class of the mod_collaborativefolders renderer.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_collaborativefolders_renderer extends plugin_renderer_base {

    public function print_error($text, $code) {
        global $OUTPUT;
        $output = '';
        $output .= $OUTPUT->heading(get_string('error', 'mod_collaborativefolders'), 4);
        if ($text === 'rename') {
            $output .= html_writer::div(get_string('retry_rename', 'mod_collaborativefolders'));
        }
        if ($text === 'share') {
            $output .= html_writer::div(get_string('retry_shared', 'mod_collaborativefolders'));
        } else {
            $output .= html_writer::div(get_string('retry', 'mod_collaborativefolders'));
        }
        $output .= html_writer::div(get_string('code', 'mod_collaborativefolders', $code));
        return $output;
    }

    public function render_view_table($groups) {
        global $OUTPUT;
        $output = '';
        $output .= $OUTPUT->heading(get_string('introoverview', 'mod_collaborativefolders'), 4);
        $table = new html_table();
        $table->head = array('groupid' => get_string('groupid', 'mod_collaborativefolders'),
                             'groupname' => get_string('groupname', 'mod_collaborativefolders'),
                             'members' => get_string('members', 'mod_collaborativefolders'));

        $table->attributes['class'] = 'admintable collaborativefolder generaltable';

        foreach ($groups as $group) {

            $memberlist = '';
            $members = groups_get_members($group->id);

            foreach ($members as $member) {
                $memberlist .= $member->firstname . ' ' . $member->lastname . ', ';
            }

            $table->data[] = array($group->id, $group->name, $memberlist);
        }
        $output .= html_writer::table($table);
        return $output;
    }

    public function print_link($url, $action) {
        global $OUTPUT;
        $output = '';
        $output .= $OUTPUT->heading(get_string($action.'_heading', 'mod_collaborativefolders'), 4);
        $output .= html_writer::div(get_string($action, 'mod_collaborativefolders',
                html_writer::link($url, 'Link')));
        return $output;
    }

    public function print_name_and_reset($name, $url) {
        global $OUTPUT;
        $output = '';
        $output .= $OUTPUT->heading(get_string('generate_change_name', 'mod_collaborativefolders'), 4);
        $output .= html_writer::div(get_string('folder_name', 'mod_collaborativefolders', $name));
        $output .= html_writer::div(get_string('reset', 'mod_collaborativefolders',
                html_writer::link($url, get_string('here', 'mod_collaborativefolders'))));
        return $output;
    }

    /**
     * Render an informational table.
     *
     * @param statusinfo $statusinfo
     * @return string
     */
    public function render_statusinfo(statusinfo $statusinfo) {
        return $this->render_from_template('mod_collaborativefolders/statusinfo', $statusinfo);
    }

    /**
     * Render a login button (needed because of MDL-59902)
     *
     * @param \moodle_url $loginurl URL of the remote system that handles login
     * @return string
     */
    public function render_widget_login(\moodle_url $loginurl) {
        /* TODO change to the following line as soon as MDL-59902 is resolved.
         * $this->render(new \single_button($loginurl, '@login'));
         * ... Maybe this function can even be inlined and removed then.
         */
        return html_writer::link($loginurl, '@login', ['class' => 'btn btn-primary']);
    }

    public function render_widget_notcreatedyet() {
        $notification = new notification('@plswait', notification::NOTIFY_INFO);
        $notification->set_show_closebutton(false);
        return $this->render($notification);
    }

    public function render_widget_teachermaynotaccess() {
        $notification = new notification('@teachermaynotaccess', notification::NOTIFY_INFO);
        $notification->set_show_closebutton(false);
        return $this->render($notification);
    }

}