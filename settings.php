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

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Firstly all issuers are considered.
    $issuers = core\oauth2\api::get_all_issuers();
    $types = array();

    // Validates which issuers implement the right endpoints. WebDav is necessary for ownCloud.
    $validissuers = [];
    foreach ($issuers as $issuer) {
        if (false) { // TODO actually validate the issuer! (cf. repo plugin).
            $validissuers[] = $issuer->get('name');
        }
        $types[$issuer->get('id')] = $issuer->get('name');
    }

    // All issuers that are valid are displayed seperately (if any).
    if (count($validissuers) === 0) {
        $issuershint = get_string('no_right_issuers', 'mod_collaborativefolders');
    } else {
        $issuershint = get_string('right_issuers', 'mod_collaborativefolders', implode(', ', $validissuers));
    }

    // Indicate quality of the chosen issuer.
    // In case no issuer is chosen there appears a warning.
    // Additionally when the chosen issuer is invalid there appears a strong warning.
    $selectedissuer = get_config("collaborativefolders", "issuerid");
    $issuer = \core\oauth2\api::get_issuer($selectedissuer);
    // TODO Use $issuer->is_system_account_connected()) in validation hints.
    if (empty($selectedissuer)) {
        $issuervalidation = get_string('issuervalidation_without', 'mod_collaborativefolders');
    } else if (!in_array($types[$selectedissuer], $validissuers)) {
        $issuervalidation = get_string('issuervalidation_invalid', 'mod_collaborativefolders', $types[$selectedissuer]);
    } else {
        $issuervalidation = get_string('issuervalidation_valid', 'mod_collaborativefolders', $types[$selectedissuer]);
    }

    // Render the form.
    $url = new \moodle_url('/admin/tool/oauth2/issuers.php');
    $settings->add(new admin_setting_configselect('collaborativefolders/issuerid',
        get_string('chooseissuer', 'mod_collaborativefolders'),
        join('<br>', [get_string('oauth2serviceslink', 'mod_collaborativefolders', $url->out()),
            $issuershint,
            $issuervalidation,
            $selectedissuer, // TODO remove this dummy output.
            // TODO add hint at whether a technical user is logged in!
        ]),
        0, $types));



}