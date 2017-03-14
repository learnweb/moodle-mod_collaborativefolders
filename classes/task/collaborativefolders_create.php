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
 * Ad hoc task for the creation of group folders in ownCloud.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders\task;

defined('MOODLE_INTERNAL') || die;

use mod_collaborativefolders\event\folders_created;
use mod_collaborativefolders\owncloud_access;
use moodle_url;
// Not the final exception type.
use repository_dropbox\authentication_exception;

class collaborativefolders_create extends \core\task\adhoc_task {

    public function execute() {

        $context = \context_system::instance();

        $returnurl = new moodle_url('/admin/settings.php?section=modsettingcollaborativefolders', [
                'callback'  => 'yes',
                'sesskey'   => sesskey(),
        ]);

        $oc = new owncloud_access($returnurl);
        $folderpaths = $this->get_custom_data();

        if (!$oc->owncloud->check_data()) {
            throw new authentication_exception('The technical user of collaborativefolders is not logged in.');
        }

        foreach ($folderpaths as $key => $path) {
            if ($key != 'instance') {
                $code = $oc->handle_folder('make', $path);
                if ($code == false) {
                    throw new \coding_exception('Folder ' . $path . ' not created. The WebDAV socket could not be
                     opened.');
                } else {
                    mtrace('Folder: ' . $path . ', Code: ' . $code);
                    if (($code != 201) && ($code != 405)) {
                        throw new \coding_exception('Folder ' . $path . ' not created. An unexpected status code
                         was received.');
                    }
                }
            }
        }

        $params = array(
                'objectid' => $folderpaths->instance,
                'context' => $context
        );

        $done = folders_created::create($params);
        $done->trigger();
    }
}