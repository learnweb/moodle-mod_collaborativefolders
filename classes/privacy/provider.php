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
 * GDPR information
 *
 * @package   mod_collaborativefolders
 * @copyright 2018 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\helper;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

class provider implements \core_privacy\local\metadata\provider,
                          \core_privacy\local\request\plugin\provider {
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'collaborativefolders_link',
            [
                'userid' => 'privacy:metadata:collaborativefolders_link:userid',
                'cmid' => 'privacy:metadata:collaborativefolders_link:cmid',
                'groupid' => 'privacy:metadata:collaborativefolders_link:groupid',
                'link' => 'privacy:metadata:collaborativefolders_link:link',
                'owncloudusername' => 'privacy:metadata:collaborativefolders_link:owncloudusername',
            ],
            'privacy:metadata:collaborativefolders_link'
        );
        return $collection;
    }

    public static function get_contexts_for_userid(int $userid) : \core_privacy\local\request\contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        $sql = "
           SELECT DISTINCT ctx.id
             FROM {context} ctx
             JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
             JOIN {collaborativefolders_link} l ON l.cmid = cm.id
            WHERE l.userid = :userid
        ";
        $params = ['contextlevel' => CONTEXT_MODULE, 'userid' => $userid];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    public static function export_user_data(\core_privacy\local\request\approved_contextlist $contextlist) {
        global $DB;
        if (!$contextlist->count()) {
            return;
        }
        $user = $contextlist->get_user();
        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "
            SELECT cm.id AS cmid, cl.groupid, cl.link, cl.owncloudusername
              FROM {context} ctx
              JOIN {course_modules} cm ON cm.id = ctx.instanceid
              JOIN {modules} m ON m.id = cm.module AND m.name = 'collaborativefolders'
              JOIN {collaborativefolders} cf ON cf.id = cm.instance
              JOIN {collaborativefolders_link} cl ON cl.cmid = cm.id

             WHERE ctx.id $contextsql AND ctx.contextlevel = :contextmodule
               AND cl.userid = :userid
             ORDER BY cm.id, cl.groupid
        ";
        $params = ['contextmodule' => CONTEXT_MODULE, 'userid' => $user->id] + $contextparams;
        $lastcmid = null;
        $linkdata = [];

        $links = $DB->get_recordset_sql($sql, $params);
        foreach ($links as $link) {
            if ($lastcmid !== $link->cmid) {
                if ($linkdata) {
                    self::export_collaborativefolders_data_for_user($linkdata, $lastcmid, $user);
                }
                $linkdata = [];
                $lastcmid = $link->cmid;
            }

            $linkdata[] = (object)[
                'groupid' => $link->groupid,
                'link' => $link->link,
                'owncloudusername' => $link->owncloudusername,
            ];
        }
        $links->close();
        if ($linkdata) {
            self::export_collaborativefolders_data_for_user($linkdata, $lastcmid, $user);
        }
    }

    private static function export_collaborativefolders_data_for_user(array $links, int $cmid, \stdClass $user) {
        // Fetch the generic module data for the choice.
        $context = \context_module::instance($cmid);
        $contextdata = helper::get_context_data($context, $user);

        // Merge with checklist data and write it.
        $contextdata = (object)array_merge((array)$contextdata, ['links' => $links]);
        writer::with_context($context)->export_data([], $contextdata);

        // Write generic module intro files.
        helper::export_context_files($context, $user);
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if (!$context) {
            return;
        }
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }
        if (!$cm = get_coursemodule_from_id('collaborativefolders', $context->instanceid)) {
            return;
        }
        $DB->delete_records_select('collaborativefolders_link', 'cmid = :cmid',
                                   ['cmid' => $cm->id]);
    }

    public static function delete_data_for_user(\core_privacy\local\request\approved_contextlist $contextlist) {
        global $DB;
        if (!$contextlist->count()) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }
            if (!$cm = get_coursemodule_from_id('collaborativefolders', $context->instanceid)) {
                continue;
            }
            $DB->delete_records_select('collaborativefolders_link', 'cmid = :cmid AND userid = :userid',
                                       ['cmid' => $cm->id, 'userid' => $userid]);
        }
    }
}