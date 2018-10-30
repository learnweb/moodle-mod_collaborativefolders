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
 * Data class that is used to aggregate relevant information for a given user and context.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_collaborativefolders\output;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use stdClass;

/**
 * Data class that is used to aggregate relevant information for a given user and context.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class statusinfo implements \renderable, \templatable {

    /** @var int 0 if teacher may not access folder; 1 otherwise */
    public $teachermayaccess;
    /** @var int 0 if whole course (NOGROUPS), >0 otherwise */
    public $groupmode;
    /** @var array Key-value array of Group ID => Group Name (empty if $groupmode == NOGROUPS) */
    public $groups;

    /**
     * statusinfo constructor.
     * @param int $teachermayaccess
     * @param int $groupmode
     * @param array $groups
     */
    public function __construct($teachermayaccess, $groupmode, array $groups) {
        $this->teachermayaccess = $teachermayaccess;
        $this->groupmode = $groupmode;
        $this->groups = $groups;
    }


    /**
     * This is required because, although $groups is an array, Mustache does not consider it as one. This is due to the fact
     * that it is indexed by group ID number (i.e. sparse keys), whereas Mustache requires consecutive IDs as keys.
     *
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $export = (object)(array)$this;
        $export->groups = array();
        foreach ($this->groups as $group) {
            $export->groups[] = $group;
        }
        return $export;
    }
}