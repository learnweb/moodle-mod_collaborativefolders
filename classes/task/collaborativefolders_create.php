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
 * @copyright  2016 Westfälische Wilhelms-Universität Münster (WWU Münster)
 * @author     Projektseminar Uni Münster
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders\task;
use mod_collaborativefolders\owncloud_access;

class collaborativefolders_create extends \core\task\adhoc_task {

    public function execute() {
        $oc = new owncloud_access();
        $data = $this->get_custom_data();

        foreach ($data as $key => $value) {
            if (!$oc->handle_folder('make', $value)) {
                throw new \coding_exception('Folder not created. Name: ' . $value);
            }
        }
    }
}