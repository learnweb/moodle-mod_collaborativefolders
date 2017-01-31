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
 * Renderer for the Web interface of deprovisionuser
 *
 * @package    mod_collaborativefolders
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class of the tool_deprovisionuser renderer.
 *
 * @package    tool_deprovisionuser
 * @copyright  2016 Nina Herrmann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
use mod_collaborativefolders\enrol_handler;

class mod_collaborativefolders_renderer extends plugin_renderer_base{

    public function render_column_for_existing_groups($onegroup) {
        $table = new html_table();
        $table->attributes['class'] = 'admintable deprovisionuser generaltable';
        $table->data[] = $onegroup;
        return $table;
    }

    public function get_link_view($link, $name) {
        $textandlink = html_writer::div(get_string('textview.php', 'mod_collaborativefolders'));

        $textandlink .= html_writer::link($link . $name, 'folder');

        return $textandlink;
    }

    public function render_view_page_student_bottom($externalurl, $cmid, $instanceid, $availability) {
        global $OUTPUT;
        $output = '';

        if ($availability == true) {

            $output .= $OUTPUT->heading('Link to collaborative Folder');
            $output .= html_writer::div(get_string('downloadfolder', 'mod_collaborativefolders',
                    html_writer::link($externalurl . '&download', 'hier')));
            $output .= html_writer::div(' ');
            $output .= html_writer::div(get_string('accessfolder', 'mod_collaborativefolders',
                    html_writer::link($externalurl, 'hier')));
            $formhandler = new enrol_handler();
            $myform = $formhandler->handle_my_form($cmid, $instanceid);
            $output .= html_writer::div($myform->display(), 'myform');

        } else {
            $output .= html_writer::div(get_string('notallowed', 'mod_collaborativefolders'));
        }

        return $output;
    }
    public function logggedin_generate_share($link) {
        global $OUTPUT;
        $output = '';

        $output .= $OUTPUT->heading('Link to collaborative Folder');
        $output .= html_writer::div(get_string('accessfolder', 'mod_collaborativefolders',
            html_writer::link($link, 'hier')));
        echo $output;
    }

    public function create_header($title = null) {
        global $OUTPUT;
        $output = '';
        $output .= $OUTPUT->header();
        $output .= $OUTPUT->heading($title);
        echo $output;
    }
    public function create_footer() {
        global $OUTPUT;
        $output = '';
        $output .= $OUTPUT->footer();
        echo $output;
    }
    public function render_view_table($groups) {
        $output = '';
        $output .= html_writer::div(get_string('introoverview', 'mod_collaborativefolders'));
        $table = new html_table();
        $table->head = array('Groupname' => 'Groupname' , 'Number of Participants' => 'Number of Participants',
            'Link to Folder' => 'Link to Folder');
        $table->attributes['class'] = 'admintable collaborativefolder generaltable';
        foreach ($groups as $group) {
            $table->data[] = $group;
        }
        $output .= html_writer::table($table);
        return $output;
    }
}

