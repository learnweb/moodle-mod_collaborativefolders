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
 * Utility to create a Nextcloud/ownCloud issuer.
 *
 * @copyright 2018 Jan Dageförde <jan.dagefoerde@ercis.uni-muenster.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require_once(__DIR__.'/../../../config.php');
require_once("$CFG->libdir/clilib.php");

// Define the input options.
$longparams = array(
    'help' => false,
    'issuer' => '',
    'baseurl' => ''
);

$shortparams = array(
    'h' => 'help',
    'i' => 'issuer',
    'b' => 'baseurl',
);

// now get cli options
list($options, $unrecognized) = cli_get_params($longparams, $shortparams);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
        "
Options:
-h, --help                       Print out this help
-i, --issuer=issuername          Specify name of the new issuer
-b, --baseurl=https://host...    Specify issuer base URL

Example:
\$sudo -u www-data /usr/bin/php mod/collaborativefolders/cli/create_issuer.php
\$sudo -u www-data /usr/bin/php mod/collaborativefolders/cli/create_issuer.php --issuer=example --baseurl=https://owncloud.example.com:8000/oc/
";

    echo $help;
    die;
}

cli_heading('Create issuer');
if ($options['issuer'] == '') {
    $prompt = "Enter name of the new issuer";
    $issuer = cli_input($prompt);
} else {
    $issuer = $options['issuer'];
}

if ($options['baseurl'] == '' ) {
    $prompt = "Enter base URL (including protocol)";
    $baseurl = cli_input($prompt);
} else {
    $baseurl = $options['baseurl'];
}

// Create issuer.
$issuer = \mod_collaborativefolders\issuer_management::create_issuer($issuer, $baseurl);

// Output success info.
cli_writeln("Issuer created.");
$params = ['action' => 'edit', 'id' => $issuer->get('id')];
$editurl = new moodle_url('/admin/tool/oauth2/issuers.php', $params);
cli_writeln(sprintf("Set client ID and secret at: %s", $editurl->out(false)));
