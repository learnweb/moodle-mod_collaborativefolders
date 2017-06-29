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
 * This file keeps track of upgrades to the collaborativefolders module.
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project Seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute collaborativefolders upgrade from the given old version.
 *
 * @return bool
 */
function xmldb_collaborativefolders_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2017032002) {

        $table = new xmldb_table('collaborativefolders_link');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, true, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, false, null, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', true, XMLDB_NOTNULL, false, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, false, null, null);
        $table->add_field('link', XMLDB_TYPE_CHAR, '255', null, null, false, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'), null, null);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    return true;
}
