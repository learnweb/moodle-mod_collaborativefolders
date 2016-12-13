#moodle-mod_collaborativefolders *(alpha_candidate)*
[![Build Status](https://travis-ci.org/pssl16/moodle-mod_collaborativefolders.svg?branch=master)]
(https://travis-ci.org/pssl16/moodle-mod_collaborativefolders)</br>
This Activity allows Lecturer to create Folders in [Sciebo](https://www.sciebo.de/) and Students can subscribe to work in those folders.
Written and maintained by
[ProjectsSeminar of the University of Muenster](https://github.com/pssl16).
## Information
This Plugin will later become a subplugin of [OAuth2Sciebo Plugin](https://github.com/pssl16/moodle-tool_oauth2sciebo). By now it uses WebDAV.
Additionally by now the url is hardcoded to the Sciebo Server that is used by the University of Muenster. Students are able
to enter their name to add the folder to their personal account. Later the [OAuth2Sciebo Plugin](https://github.com/pssl16/moodle-tool_oauth2sciebo) will 
identify users automatically therefore users will not be forced to enter their name.
## TODO
- [ ] Connect the Plugin to the [OAuth2Sciebo Plugin](https://github.com/pssl16/moodle-tool_oauth2sciebo)
  - [ ] Implement OAuth2.0
- [ ] Implement the Group Mode for the Plugin to create only folders for individual groups
- [ ] Writing PhpUnit test
