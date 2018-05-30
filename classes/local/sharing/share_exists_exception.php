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
 * Exception for when sharing has failed because a folder has *already* been shared prior to that.
 *
 * @package    mod_collaborativefolders
 * @copyright  2018 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders\local\sharing;

defined('MOODLE_INTERNAL') || die();

/**
 * Exception for when sharing has failed because a folder has *already* been shared prior to that.
 *
 * @package    mod_collaborativefolders
 * @copyright  2018 Jan Dageförde (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class share_exists_exception extends \moodle_exception {

    /**
     * Constructor
     * @param string $hint optional param for additional information of the problem
     * @param string $debuginfo detailed information how to fix problem
     */
    public function __construct($hint = '', $debuginfo = null) {
        parent::__construct('share_exists_exception', 'mod_collaborativefolders', '', $hint, $debuginfo);
    }
}