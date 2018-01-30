# Do not use in production
Please be aware that this is a pre-release. **  Do not use in production! **  Plugins and their structure **will** be subject to change. We will **NOT** support any upgrade paths from this release.

Nevertheless, we are actively working on a release. We would be extremely happy for (non-production) test users and developer contributions!

# Moodle Activity Module `collaborativefolders`

[![Build Status](https://travis-ci.org/learnweb/moodle-mod_collaborativefolders.svg?branch=master)](https://travis-ci.org/learnweb/moodle-mod_collaborativefolders)
[![codecov](https://codecov.io/gh/learnweb/moodle-mod_collaborativefolders/branch/master/graph/badge.svg)](https://codecov.io/gh/learnweb/moodle-mod_collaborativefolders)

# English

This Activity Module allows teachers to create folders in [ownCloud](https://owncloud.org/), 
which can be allocated to groups of students who can then voluntarily subscribe work collaboratively in these folders.

Written by project seminar sciebo@Learnweb of the University of Münster.

## Information

This module is depending on the [`oauth2owncloud` plugin](https://github.com/learnweb/moodle-tool_oauth2owncloud) and
can not be used separately.

## Installation

Please place this plugin under `mod/collaborativefolders` in your Moodle directory.
Before being able to use the plugin, a technical user account has to be authenticated in ownCloud. The regarding settings can be found under 
`Site administration ► Plugins ► Activity modules ► collaborativefolders`. 
While using the plugin the technical user should not be changed, because it could result in synchronization problems.
Activities that were generated beforehand could become unusable.

## Teacher View

Firstly, the teacher has to add a `collaborativefolders` instance to a course. 
Upon creation the teacher chooses a name for the instance, which is displayed in
the course context. Furthermore, the teacher can decide whether he has access to the collaborative folder(s).
At last, the teacher can choose, whether or not he wants to activate the groupmode for the
concerning instance. Activation of the groupmode leads to the creation of separate group folders in ownCloud.

## User View

After the folder(s) was created by a cron task, users are requested to choose an individual name for the folder.

 ![Choose a folder name](https://user-images.githubusercontent.com/432117/27693591-28c99eda-5cea-11e7-9214-62d736d45273.png)
 
Afterwards, users can change the name and logout from their ownCloud account. 
If a user entered a valid name for the folder and already is authenticated in ownCloud,
he is able to generate a Share for the collaborative folder. Thereafter, the folder is accessible either from
the activity instance or from the personal ownCloud directory.
 
 ![Student view](https://user-images.githubusercontent.com/432117/27693597-2b2f0106-5cea-11e7-8f40-705980c8e055.png)

Further information can be found in our [documentation](https://pssl16.github.io).
