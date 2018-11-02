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
 * DB upgrade steps
 *
 * @package   mod_collaborativefolders
 * @copyright 2018 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_collaborativefolders_upgrade($oldversion = 0) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2018110200) {

        // Define field owncloudusername to be added to collaborativefolders_link.
        $table = new xmldb_table('collaborativefolders_link');
        $field = new xmldb_field('owncloudusername', XMLDB_TYPE_CHAR, '254', null, null, null, null, 'link');

        // Conditionally launch add field owncloudusername.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Collaborativefolders savepoint reached.
        upgrade_mod_savepoint(true, 2018110200, 'collaborativefolders');
    }

    if ($oldversion < 2018110201) {

        // Changing type of field teacher on table collaborativefolders to int.
        $table = new xmldb_table('collaborativefolders');
        $field = new xmldb_field('teacher', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'timemodified');

        // Launch change of type for field teacher.
        $dbman->change_field_type($table, $field);

        // Collaborativefolders savepoint reached.
        upgrade_mod_savepoint(true, 2018110201, 'collaborativefolders');
    }

    return true;
}
