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
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class of the mod_collaborativefolders renderer.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class mod_collaborativefolders_renderer extends plugin_renderer_base {

    public function print_error($text, $code) {
        global $OUTPUT;
        $output = '';
        $output .= $OUTPUT->heading(get_string('error', 'mod_collaborativefolders'), 4);
        if ($text === 'rename') {
            $output .= html_writer::div(get_string('retry_rename', 'mod_collaborativefolders'));
        }
        if ($text === 'shared') {
            $output .= html_writer::div(get_string('retry_shared', 'mod_collaborativefolders'));
        } else {
            $output .= html_writer::div(get_string('retry', 'mod_collaborativefolders'));
        }
        $output .= html_writer::div(get_string('code', 'mod_collaborativefolders', $code));
        return $output;
    }

    public function create_header($title = null) {
        global $OUTPUT;
        $output = '';
        $output .= $OUTPUT->header();
        if ($title != null) {
            $output .= $OUTPUT->heading($title);
        }
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
        $table->head = array('Groupname' => 'Groupname' , 'Number of Participants' => 'Number of Participants');
        $table->attributes['class'] = 'admintable collaborativefolder generaltable';
        foreach ($groups as $group) {
            $table->data[] = $group;
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
        $output .= $OUTPUT->heading(get_string('generate_heading', 'mod_collaborativefolders'), 4);
        $output .= html_writer::div(get_string('folder_name', 'mod_collaborativefolders', $name));
        $output .= html_writer::div(get_string('reset', 'mod_collaborativefolders',
                html_writer::link($url, 'here')));
        return $output;
    }
}