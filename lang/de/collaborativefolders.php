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
 * German strings for collaborativefolders module.
 *
 * @package    mod_collaborativefolders
 * @copyright  2017 Project seminar (Learnweb, University of Münster)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General.
$string['modulename'] = 'Kollaborativer Ordner';
$string['modulenameplural'] = 'Kollaborative Ordner';
$string['modulename_help'] = 'Verwenden Sie kollaborative Ordner, um Ordner in der Cloud (ownCloud, Nextcloud) für Teilnehmer für kollaboratives Arbeiten zu erstellen. Der Ordner wird individuell mit den Mitgliedern der ausgewählten Gruppen geteilt. Sie müssen keine E-Mail-Adressen von Ihren Teilnehmern sammeln, alles ist automatisiert!';
$string['cachedef_token'] = 'OAuth-Token';
$string['cachedef_userinfo'] = 'OAuth-Benutzerinformation';
$string['collaborativefolders:addinstance'] = 'Füge einen neuen kollaborativen Ordner hinzu';
$string['collaborativefolders:view'] = 'Zeige einen kollaborativen Ordner an';
$string['collaborativefolders:isteacher'] = 'Bei der Anzeige, alle außer Teilnehmer (mit eingeschränktem Zugang) berücksichtigen';
$string['collaborativefolders'] = 'Kollaborative Ordner';
$string['nocollaborativefolders'] = 'In diesem Kurs ist keine Instanz eines kolloborativen Ordners aktiv.';
$string['pluginadministration'] = 'Administration eines kollaborativen Ordners';
$string['pluginname'] = 'Kollaborative Ordner';

// View: Overview.
$string['activityoverview'] = 'Kollaborativer Ordner';
$string['overview'] = 'Überblick';
$string['creationstatus'] = 'Ordner Status';
$string['creationstatus_created'] = 'Ordner erstellt';
$string['creationstatus_pending'] = 'Ordner werden/wird in Kürze erstellt';
$string['teacheraccess_yes'] = 'Trainer haben Zugriff auf alle Ordner';
$string['teacheraccess_no'] = 'Die Ordner sind privat und Trainer haben keinen Zugriff';
$string['groupmode'] = 'Modus';
$string['groupmode_on'] = 'Ein Ordner pro Gruppe';
$string['groupmode_off'] = 'Ein Ordner für den gesamten Kurs';
$string['groups'] = 'Gruppen';
$string['nogroups'] = 'Keine Gruppen';
$string['notingroup'] = 'Sie sind in keiner Gruppe, deshalb haben Sie keinen Zugriff auf diese Ordner.';
$string['teachersnotallowed'] = 'Leider ist es Trainern nicht erlaubt, diese Inhalte zu sehen.';

// View: remote system (+authorise.php).
$string['remotesystem'] = 'Verbinden mit {$a}';
$string['btnlogin'] = 'Anmelden';
$string['btnlogout'] = 'Abmelden ({$a})';
$string['logoutsuccess'] = 'Erfolgreich Verbindung zu {$a} getrennt.';
$string['loginsuccess'] = 'Erfolgreich verbunden mit {$a}.';
$string['loginfailure'] = 'Es ist ein Problem aufgetreten: Nicht berechtigt, sich mit {$a} zu verbinden.';

// View: access folders.
$string['accessfolders'] = 'Ordnerzugriff';
$string['grouplabel'] = 'Gruppe: {$a}';

// View: name_form.
$string['namefield'] = 'Name';
$string['namefield_explanation'] = 'Wählen Sie einen Namen, unter dem der gemeinsame Ordner in Ihrem {$a} gespeichert wird.';
$string['getaccess'] = 'Zugriff erhalten';
$string['error_illegalpathchars'] = 'Es muss ein gültiger Ordner- oder Pfadname eingegeben werden. Verwenden Sie \'/\' (slash), um Verzeichnisse eines Pfades zu begrenzen.';
$string['foldershared'] = 'Der Ordner wurde erfolgreich für Ihre {$a} freigegeben.';

// View: information about shared folder.
$string['sharedtoowncloud'] = 'Dieser Ordner wurde bereits für Ihre {$a} freigegeben.';
$string['folder'] = 'Ordner';
$string['cannotaccessheader'] = 'Kein Zugriff?';
$string['cannotaccess'] = 'Wenn der obige Link nicht funktioniert und Sie den Ordner nicht finden können, klicken Sie auf die Schaltfläche auf der linken Seite, um die Freigabe zurückzusetzen. Auf diese Weise erhalten Sie wieder Zugriff, ohne Änderungen an den Dateien in diesem Ordner vorzunehmen.';
$string['namemismatch'] = 'Warnung: Dieser Ordner wurde mit \'{$a->link}\' geteilt, aber Sie sind als \'{$a->current}\' angemeldet - Sie müssen möglicherweise die Anmeldungen wechseln, um auf die Dateien zugreifen zu können.';
$string['openinowncloud'] = 'Öffnen in {$a}';
$string['solveproblems'] = 'Probleme lösen';
$string['resetpressed'] = 'Zurücksetzen der Freigabe. Sie können nun wieder Zugriff auf Ihren Ordner erhalten.';

// Systemic error messages.
$string['problem_nosystemconnection'] = 'Das Systemkonto kann sich nicht mit {$a} verbinden, so dass Ordner für diese Aktivität nicht erstellt werden. Bitte informieren Sie den Administrator über dieses Problem.';
$string['problem_misconfiguration'] = 'Das Plugin ist nicht korrekt konfiguriert oder der Server ist nicht erreichbar. Bitte wenden Sie sich an Ihren Administrator, um dieses Problem zu beheben.';
$string['problem_sharessuppressed'] = 'Das Systemkonto kann sich nicht mit {$a->servicename} verbinden, so dass {$a->sharessuppressed} Ordner nicht angezeigt wurden. Bitte informieren Sie den Administrator über dieses Problem.';

// Configuration/connection error messages.
$string['usernotloggedin'] = 'Sie sind derzeit nicht am Remote-System angemeldet.';
$string['webdaverror'] = 'WebDAV Fehler Code {$a}';
$string['socketerror'] = 'Der WebDAV Socket konnte nicht geöffnet werden.';
$string['ocserror'] = 'Ein Fehler mit der OCS-Sharing-API ist aufgetreten.';
$string['notcreated'] = 'Ordner {$a} konnte nicht erstellt werden. ';
$string['unexpectedcode'] = 'Ein unerwarteter Response Status Code ({$a}) wurde empfangen.';
$string['technicalnotloggedin'] = 'Das Systemkonto ist nicht angemeldet oder hat keine Berechtigung im Remote-System.';
$string['incompletedata'] = 'Bitte überprüfen Sie die Moduleinstellungen. Entweder ist kein OAuth 2-Service ausgewählt oder es ist kein entsprechendes Systemkonto verbunden.';

// Settings.
$string['chooseissuer'] = 'Service';
$string['oauth2serviceslink'] = '<a href="{$a}" title="Link zur Konfiguration der OAuth 2 Dienste">Konfiguration der OAuth 2 Dienste</a>';
$string['issuervalidation_without'] = 'Sie haben noch keinen OAuth 2-Service gewählt.';
$string['issuervalidation_valid'] = 'Derzeit ist der {$a} Service gültig und aktiv.';
$string['issuervalidation_invalid'] = 'Derzeit ist der {$a} Service aktiv, implementiert aber nicht alle notwendigen Endpunkte. Das Plugin funktioniert nicht. Bitte wählen Sie einen gültigen Issuer aus.';
$string['issuervalidation_notconnected'] = 'Derzeit ist der gültige {$a} Service aktiv, aber kein Systemkonto ist verbunden. Das Plugin funktioniert nicht. Bitte verbinden Sie ein Systemkonto.';
$string['right_issuers'] = 'Die folgenden Service implementieren die erforderlichen Endpunkte: {$a}';
$string['no_right_issuers'] = 'Keiner der bestehenden Services implementiert alle erforderlichen Endpunkte. Bitte registrieren Sie einen geeigneten Service.';
$string['issuer_choice_unconfigured'] = '(unkonfiguriert)';
$string['servicename'] = 'Name des Dienstes';

// Adding an instance (mod_form).
$string['collaborativefoldersname'] = 'Name des kollaborativen Ordners';
$string['collaborativefoldersname_help'] = 'Geben Sie einen neuen Namen ein, der auf der Kursseite angezeigt werden soll.';
$string['teacher_access'] = 'Trainerzugriff';
$string['teacher_mode'] = 'Dem Trainer Zugriff auf den Ordner gewähren.';
$string['teacher_mode_help'] = 'In der Regel haben nur Teilnehmer Zugriff auf ihre Ordner. Wenn dieses Kontrollkästchen jedoch aktiviert ist, erhalten Trainer auch Zugriff. Beachten Sie, dass diese Einstellung nach dem Anlegen nicht mehr geändert werden kann.';
$string['edit_after_creation'] = 'Bitte beachten Sie, dass der Zugang des Trainers und die gruppenbezogenen Einstellungen nach dem Anlegen dieser Aktivität nicht mehr geändert werden können.';

// Events.
$string['eventlinkgenerated'] = 'Eine benutzerspezifische Freigabe für einen gemeinsamen Ordner wurde erfolgreich erstellt.';

// Exceptions.
$string['configuration_exception'] = 'Es ist ein Fehler in der Konfiguration des OAuth 2 Clients aufgetreten: {$a}';
$string['webdav_response_exception'] = 'WebDAV antwortete mit einem Fehler: {$a}';
$string['share_failed_exception'] = 'Der Ordner kann nicht für Sie freigegeben werden: {$a}';
$string['share_exists_exception'] = 'Der Ordner ist bereits für Sie freigegeben. {$a}';

// Privacy data.
$string['privacy:metadata:collaborativefolders_link'] = 'Informationen über Ordner, die für Benutzer freigegeben wurden';
$string['privacy:metadata:collaborativefolders_link:cmid'] = 'Das Kursmodul, dem diese Ordnerfreigabe zugeordnet ist';
$string['privacy:metadata:collaborativefolders_link:groupid'] = 'Die Moodle-Kursgruppe, auf die sich der gemeinsame Ordner bezieht';
$string['privacy:metadata:collaborativefolders_link:link'] = 'Der Name, der dem Ordner bei der Freigabe gegeben wurde.';
$string['privacy:metadata:collaborativefolders_link:owncloudusername'] = 'Der OwnCloud-Benutzer, für den der Ordner freigegeben wurde';
$string['privacy:metadata:collaborativefolders_link:userid'] = 'Der Moodle-Benutzer, für den der Ordner freigegeben wurde';
