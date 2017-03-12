#moodle-mod_collaborativefolders *(beta_candidate)*
# English
[![Build Status](https://travis-ci.org/pssl16/moodle-mod_collaborativefolders.svg?branch=master)]
(https://travis-ci.org/pssl16/moodle-mod_collaborativefolders)</br>
This Activity allows course admins to create Folders in [ownCloud](https://www.sciebo.de/) 
allocate them to groups of students who can then voluntarily subscribe 
to work collaborative in these folders.. and Students can subscribe to work in those folders.
Written by the
[ProjectsSeminar Sciebo@Leanrweb of the University of Muenster](https://github.com/pssl16).

## Information
This Plugin is a subplugin of [oauth2owncloud Plugin](https://github.com/pssl16/moodle-tool_oauth2sciebo).
This plugin can not be used separately.

## Installation
This Plugin should go into `mod/collaborativefolders`.
Bevor using the Plugin a technical users has to be authenticated. The user can be authenticated in 
`Site administration ► Plugins ► Activity modules ► collaborativefolders`. The technical user is needed 
to store data at a neutral place.
While using the plugin the technical user MUST NOT be changed. This will result in synchronization problems.
Activities that were generated beforehand can not be used any longer.

## Teacher View

Firstly the teacher has to add a collaborativefolder_activity. 
In the Activity settings the teacher gives the folder a name.
This name is merely used in Moodle. 
Secondly the teacher can decide whether he has access to the folder(s).
All other settings are comparable to the usual activity setting. 
When the groupmode is enabled every group has a individual folder. 

## User View

After the folder was created by the cronjob users are requested to give the folder a individual name.

 ![filepickerlogin](pix/GiveFolderName.png)
 
 Afterwards users can change the name and logout from their owncloud account. 
 Last but not least users can add the folder to their account. 
 
 ![filepickerlogin](pix/Overview.png)
 
The folder can then be accessed through a link or through the owncloud login.

For additional information *(only available in german)* please visit our [documentation page](https://pssl16.github.io).

# German

## Information

Dieses Plugin ermöglicht Lehrenden einem Kurs eine Aktivität zu erstellen die für Studenten oder für Gruppen von Studenten 
Ordner in einer ownCloud Instanz erstellt. In diesen Ordnern können Studierende nun kollaborativ arbeiten. 
Es können auch für separate Gruppen Ordner erstellt werden. Zur Nutzung dieses Plugins wird zuerst das
[tool_oauth2owncloud Plugin](https://github.com/pssl16/moodle-tool_oauth2sciebo) benötigt. 
Die Installation ist nicht möglich, bevor das admin_tool installiert wurde.

## Installation

Für die Aktivität collaborative_folders wird ein technischer Nutzer der ownCloud Instanz benötigt.
Bei diesem Nutzer werden alle Ordner die erstellt werden gespeichert. 
Um den Nutzer festzulegen muss in `Website-Administration>Plugins>Aktivitäten>collaborativefolders`
ein technischer Nutzer mit Hilfe des OAuth2 Protokolls authentifiziert werden.
Über einen Login Button werden Sie aufgefordert sich in ownCloud zu authentifizieren. 
Falls Sie nicht richtig weitergeleitet werden, sind die Einstellungen im Admin tool oauth2sciebo
fehlerhaft, bitte überprüfen Sie diese. 
Achten Sie darauf, dass Sie sich nicht mit Ihrem normalen Account sondern mit dem 
technischen Nutzer anmelden.

## Sicht des Lehrenden

Die Aktivität ist in jedem Moodle Kurs verfügbar. Wenn ein Lehrender die Aktivität dem Kurs hinzufügt muss er dem Ordner einen Namen für die Moodle Instanz und einen für die ownCloud Instanz geben. Danach kann festlegt werden ob Lehrende des Kurses Zugriff auf alle erstellten Ordner haben. Eins der wichtigsten Integrationsszenarien ist, dass nur für Gruppen von Studierenden ein Ordner erstellt wird. Dies ist möglich, wenn der Lehrende den Zugriff auf bestimmte Gruppen beschränkt. In diesem Fall werden nur für die gewählten Gruppen einzelne Ordner erstellt.
## Sicht der Studierenden

Nachdem der Ordner vom Cronjob erstellt wurde, wird der Nutzer aufgefordert dem Ordner einen individuellen Namen 
zu geben.

 ![filepickerlogin](pix/GiveFolderName.png)
 
 Danach kann der Nutzer dieses Namen ändern sich mit einem anderen owncloud Accoutn authentifizieren
 und wenn er authentifiziert ist, den Ordner seinem Accoutn hinzufügen. 
 
 ![filepickerlogin](pix/Overview.png)
 
 Danach kann der Nutzer über einen Link in Moodle oder über den normalen ownCloud Login auf 
 den Ordner zugreifen.

Für genauere Informationen besuchen sie unsere [Website Dokumentation](https://pssl16.github.io).