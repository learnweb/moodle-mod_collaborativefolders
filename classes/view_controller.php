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
//

/**
 * Controller for view.php
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_collaborativefolders;

use mod_collaborativefolders\local\clients\system_folder_access;
use mod_collaborativefolders\local\clients\user_folder_access;
use mod_collaborativefolders\output\statusinfo;
use mod_collaborativefolders_renderer;

defined('MOODLE_INTERNAL') || die();

/**
 * Controller for view.php
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_controller {
    /**
     * Handle an incoming request
     *
     * @param \stdClass $collaborativefolder Collaborativefolder instance
     * @param \cm_info $cm Corresponding course module
     * @param \context_module $context Module context
     * @param mod_collaborativefolders_renderer $renderer Plugin-specific renderer
     */
    public static function handle_request($collaborativefolder, \cm_info $cm, \context_module $context,
                                          mod_collaborativefolders_renderer $renderer) {
        global $OUTPUT, $USER;

        \mod_collaborativefolders\toolbox::coursemodule_viewed($context, $cm);

        // Check whether viewer is considered as non-student, because their access may be restricted. Admin override is ignored.
        // Note: This is a restriction, not a capability. Don't use for deciding what someone MAY do, consider as MAY NOT instead.
        $isteacher = has_capability('mod/collaborativefolders:isteacher', $context, null, false);

        $statusinfo = self::get_instance_status($collaborativefolder, $cm, $isteacher);
        $userfolders = self::obtain_folders($statusinfo, $cm, $isteacher);
        $userclient = new user_folder_access(new \moodle_url('/mod/collaborativefolders/authorise.php', [
                'action' => 'login',
                'id' => $cm->id,
                'sesskey' => sesskey()
            ])
        );
        try {
            $systemclient = new system_folder_access();
        } catch (configuration_exception $e) {
            $systemclient = null;
        }

        if ($systemclient !== null) {
            // If a folder form (that is inside $userfolders) is submitted, validate it and maybe create the share.
            // Redirects to self if something interesting has happened.
            self::handle_folder_form_submitted($userfolders, $cm, $userclient, $systemclient, $context);
        }

        // Start output.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('activityoverview', 'mod_collaborativefolders'));
        if (!empty($collaborativefolder->intro)) {
            echo $OUTPUT->box(format_module_intro('collaborativefolder', $collaborativefolder, $cm->id), 'generalbox', 'intro');
        }

        // Show notice if there is a general problem with the system account.
        // Show to someone who can add/configure this instance (i.e. teachers).
        if ($systemclient === null && has_capability('mod/collaborativefolders:addinstance', $context)) {
            echo $renderer->render_widget_noconnection();
        }

        // Show status info table.
        echo $renderer->render($statusinfo);

        // Login / logout form.
        echo $OUTPUT->heading(get_string('remotesystem', 'mod_collaborativefolders'), 3);
        if ($userclient->check_login()) {
            echo $renderer->render(new \single_button(
                new \moodle_url('/mod/collaborativefolders/authorise.php', [
                    'action' => 'logout',
                    'id' => $cm->id,
                    'sesskey' => sesskey()
                ]), get_string('btnlogout', 'mod_collaborativefolders')));
        } else {
            echo $renderer->render_widget_login($userclient->get_login_url());
        }

        // Interaction with instance.
        if ($userclient->check_login()) {
            echo $OUTPUT->heading(get_string('accessfolders', 'mod_collaborativefolders'), 3);
            if ($statusinfo->creationstatus === 'created') {
                echo self::share_and_view_folders($cm->id, $userfolders, $statusinfo, $renderer, $isteacher,
                    $systemclient !== null, $userclient);
            } else {
                // Folders are not yet created and can therefore not be shared.
                echo $renderer->render_widget_notcreatedyet();
            }
        }

        echo $OUTPUT->footer();
    }

    /**
     * Aggregate information about the current instance. Focus on information that is relevant to the current user.
     *
     * @param \stdClass $collaborativefolder Collaborativefolder instance
     * @param \cm_info $cm Corresponding course module
     * @param bool $asteacher Assume a teacher's perspective (additional output).
     * @return statusinfo
     */
    private static function get_instance_status($collaborativefolder, \cm_info $cm, $asteacher) {
        global $USER;

        // Check if folders are per-group.
        $groupmode = groups_get_activity_groupmode($cm) != 0;

        $groups = array();
        // If in groupmode, find out which groups are relevant (own groups, except if teacher, then all groups).
        if ($groupmode) {
            if ($asteacher) {
                $groups = groups_get_all_groups($cm->course, 0, $cm->groupingid);
            } else {
                $groups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid);
            }
        }

        // Check the state of asynchronous folder creation.
        $creationstatus = \mod_collaborativefolders\toolbox::is_create_task_running($cm->id) ? 'pending' : 'created';

        // Determine whether teachers may also access the share.
        $teachermayaccess = $collaborativefolder->teacher;

        return new statusinfo($creationstatus, $teachermayaccess, $groupmode, $groups);
    }

    /**
     * Render the view that is used for interactions with folders. Per applicable folder:
     * # Defining a user-local name and generating a share
     * # Display the selected name, a link, and a button for problem solving (aka re-share).
     *
     * @param int $cmid Course module ID
     * @param array $folderforms
     * @param statusinfo $statusinfo
     * @param mod_collaborativefolders_renderer $renderer
     * @param bool $isteacher true if the viewing user is a teacher
     * @param bool $systemclientcanshare true if there is a connected system account that could create a share
     * @param user_folder_access $userclient connected client for the current user.
     * @return string Rendered view
     * @internal param user_folder_access $userclient
     */
    private static function share_and_view_folders(int $cmid, $folderforms, statusinfo $statusinfo,
                                                   mod_collaborativefolders_renderer $renderer, bool $isteacher,
                                                   bool $systemclientcanshare, user_folder_access $userclient) {
        global $USER;

        if ($isteacher && !$statusinfo->teachermayaccess) {
            return $renderer->render_widget_teachermaynotaccess();
        }

        // TODO replace echoes by string variable concatenations (and return that string).

        // Counter for sharing forms that were suppressed because no sysaccount was connected.
        $sharessuppressed = 0;

        // Per group/folder: Either define user-local name or access share.
        foreach ($folderforms as $groupid => $form) {
            $foldername = $userclient->get_link($cmid, $groupid, $USER->id);
            $group = $groupid === 0 ? toolbox::fake_course_group() : $statusinfo->groups[$groupid];
            if ($foldername === null) {
                // User does not have a share yet; create it now.

                // Show notice if there is a general problem with the system account (and skip form).
                if (!$systemclientcanshare) {
                    $sharessuppressed++;
                    continue;
                }

                // Show form to define user-local name.
                $renderer->output_name_form($group, $form);
            } else {
                // XOR Access share.
                echo $renderer->output_shared_folder($group, $cmid, $foldername,
                    $userclient->link_from_foldername($foldername));
            }
        }

        // Show notice if there is a general problem with the system account.
        if ($sharessuppressed > 0) {
            echo $renderer->render_widget_noconnection_suppressed_share($sharessuppressed);
        }

    }

    private static function obtain_folders(statusinfo $statusinfo, \cm_info $cm, $isteacher) {
        // Get applicable groups from $statusinfo.
        $folders = array();
        if ($isteacher && !$statusinfo->teachermayaccess) {
            // Refuse access for teacher.
            return array();
        } else if ($isteacher || !$statusinfo->groupmode) {
            // One folder for the entire course.
            $fakegroup = toolbox::fake_course_group();
            $folders = [$fakegroup];
        } else {
            // Student; one folder per applicable group.
            $folders = $statusinfo->groups;
        }

        $forms = array();
        // Per group: Either define user-local name or access share.
        foreach ($folders as $f) {
            $form = new name_form(qualified_me(), [
                    'id' => $f->id,
                    'namefield' => sprintf("%s (%s)", $cm->name, $f->name),
                ]
            );
            $forms[$f->id] = $form;
        }
        return $forms;
    }

    /**
     * @param array $userfolders array of folders applicable for the user
     * @param \cm_info $cm current coursemodule
     * @param user_folder_access $userclient connected client of the user
     * @param system_folder_access $systemclient connected system client
     * @param int|Name $currentuserid Name of the user that the form will be shared with
     * @param \context_module $context context of the current coursemodule
     */
    public static function handle_folder_form_submitted($userfolders, \cm_info $cm, user_folder_access $userclient,
                                                        system_folder_access $systemclient,
                                                        \context_module $context) {
        foreach ($userfolders as $groupid => $form) {
            /* @var name_form $form */
            // Iterate over forms to find the submitted one (is_submitted() is implicit in get_data()).
            if ($fromform = $form->get_data()) {
                self::share_folder_with_user($groupid, $fromform->namefield, $systemclient,
                                                   $userclient, $cm->id);

                $generatedevent = \mod_collaborativefolders\event\link_generated::create([
                    'context' => $context,
                    'objectid' => $cm->instance
                ]);
                $generatedevent->trigger();

                redirect(new \moodle_url('/mod/collaborativefolders/view.php#folder-' . $groupid, [
                    'id' => $cm->id,
                ]), get_string('foldershared', 'mod_collaborativefolders'), null, \core\output\notification::NOTIFY_SUCCESS);
                exit;
            }
        }
    }

    private static function share_folder_with_user($groupid, $chosenname, system_folder_access $systemclient,
                                                   user_folder_access $userclient, int $cmid) {
        global $USER;

        // Derive $sharepath (original path) from $groupid.
        $sharepath = '/'.$cmid;
        if ($groupid !== toolbox::fake_course_group()->id) {
            $sharepath .= '/'.$groupid;
        }
        // Share from system to user.
        $userinfo = $userclient->get_userinfo();
        $shareusername = $userinfo['username'];
        $shared = $systemclient->generate_share($sharepath, $shareusername);
        if ($shared === false) {
            // Share was unsuccessful.
            // TODO reason can be that it was already shared before!
            throw new share_failed_exception(get_string('ocserror', 'mod_collaborativefolders'));
        }

        // Get newly created share (in user space) and move it to the chosen location.
        $finalpath = (string)$shared->file_target;
        $renamed = $userclient->rename($finalpath, $chosenname);
        if ($renamed['status'] === false) {
            // Rename was unsuccessful.
            // TODO rollback sharing (unless successfully shared and moved before).
            throw new share_failed_exception($renamed['content']);
        }
        // Sharing and renaming operations were successful.
        // Store resulting path for future reference.
        $userclient->store_link($cmid, $groupid, $USER->id, $chosenname);
    }
}