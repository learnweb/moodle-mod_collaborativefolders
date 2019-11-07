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
 * Settings.php for collaborativefolders activity module. Manages the login to an ownCloud account.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_collaborativefolders\issuer_management;

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Collect available issuers.
    $issuers = core\oauth2\api::get_all_issuers();
    $availableissuers = [0 => get_string('issuer_choice_unconfigured', 'collaborativefolders')];

    // Validates which issuers implement the needed endpoints.
    $validissuers = [];
    foreach ($issuers as $issuer) {
        if (issuer_management::is_valid_issuer($issuer)) {
            $validissuers[] = $issuer->get('id');
        }
        $availableissuers[$issuer->get('id')] = $issuer->get('name');
    }

    // All issuers that are valid are displayed seperately (if any).
    if (count($validissuers) === 0) {
        $issuershint = get_string('no_right_issuers', 'mod_collaborativefolders');
    } else {
        $validissuerstext = array_map(function($id) use ($availableissuers) {
                return $availableissuers[$id];
        }, $validissuers);
        $issuershint = get_string('right_issuers', 'mod_collaborativefolders', implode(', ', $validissuerstext));
    }

    // Indicate quality of the chosen issuer.
    // Warning if no issuer chosen / issuer invalid / issuer valid but no system account connected.
    $issuerid = get_config("collaborativefolders", "issuerid");

    if (empty($issuerid) || !array_key_exists($issuerid, $availableissuers)) {
        $issuervalidation = get_string('issuervalidation_without', 'mod_collaborativefolders');
    } else if (!in_array($issuerid, $validissuers)) {
        $issuervalidation = get_string('issuervalidation_invalid', 'mod_collaborativefolders', $availableissuers[$issuerid]);
    } else {
        $issuer = \core\oauth2\api::get_issuer($issuerid);
        if (!$issuer->is_system_account_connected()) {
            $issuervalidation = get_string('issuervalidation_notconnected', 'mod_collaborativefolders', $availableissuers[$issuerid]);
        } else {
            $issuervalidation = get_string('issuervalidation_valid', 'mod_collaborativefolders', $availableissuers[$issuerid]);
        }
    }

    // Render the form.
    $url = new \moodle_url('/admin/tool/oauth2/issuers.php');
    $settings->add(new admin_setting_configselect('collaborativefolders/issuerid',
        get_string('chooseissuer', 'mod_collaborativefolders'),
        implode('<br>', [
            get_string('oauth2serviceslink', 'mod_collaborativefolders', $url->out()),
            $issuershint,
            $issuervalidation,
        ]),
        0, $availableissuers));
    $settings->add(new admin_setting_configtext('collaborativefolders/servicename',
        get_string('servicename', 'mod_collaborativefolders'), '', 'ownCloud'));
}