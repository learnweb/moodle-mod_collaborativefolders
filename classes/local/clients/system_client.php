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
 * Extend oauth\client to store token in application cache, not in the SESSION
 *
 * @package   mod_collaborativefolders
 * @copyright 2018 Davo Smith, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_collaborativefolders\local\clients;

defined('MOODLE_INTERNAL') || die();

class system_client extends \core\oauth2\client {
    protected function store_token($token) {
        // Call parent function, as $this->accesstoken is private, so can't set directly.
        parent::store_token($token);

        $name = $this->get_tokenname();
        $cache = \cache::make('mod_collaborativefolders', 'token');
        if ($token !== null) {
            $cache->set($name, $token);
        } else {
            $cache->delete($name);
        }
    }

    protected function get_stored_token() {
        $name = $this->get_tokenname();
        $cache = \cache::make('mod_collaborativefolders', 'token');
        $token = $cache->get($name);
        if ($token) {
            return $token;
        }
        return null;
    }

    public function upgrade_refresh_token(\core\oauth2\system_account $systemaccount) : bool {
        $this->store_token(null); // Make sure the existing token is cleared, before calling refresh.
        return parent::upgrade_refresh_token($systemaccount);
    }
}