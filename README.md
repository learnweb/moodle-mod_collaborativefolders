# Collaborative folders

[![Build Status](https://travis-ci.org/learnweb/moodle-mod_collaborativefolders.svg?branch=master)](https://travis-ci.org/learnweb/moodle-mod_collaborativefolders)
[![codecov](https://codecov.io/gh/learnweb/moodle-mod_collaborativefolders/branch/master/graph/badge.svg)](https://codecov.io/gh/learnweb/moodle-mod_collaborativefolders)

This Activity Module allows teachers to create folders in [ownCloud](https://owncloud.org/), 
which may be allocated to groups of students who can then work collaboratively in these folders.

This plugin has originally been developed with ownCloud in mind, but works with Nextcloud just as well. For simplicity we will refer to both as "ownCloud".


**Acknowledgement:** This plugin was originally created by Information Systems students of the project seminar sciebo@Learnweb 
at the University of Münster in 2016-17; see https://github.com/pssl16 for an archive(!) of their great work.
Learnweb (University of Münster) took over maintenance in 2017.
  
## Installation

1. Place this plugin at `mod/collaborativefolders` in your Moodle directory.
2. Configure ownCloud and Moodle as described in https://docs.moodle.org/en/OAuth_2_Nextcloud_service. 
A repository and this plugin may share an issuer -- you do not need to configure two issuers for the same system! 
3. Also, make sure that a system account is connected.
4. In the plugin settings at 
`Site administration ► Plugins ► Activity modules ► collaborativefolders`, select the issuer. You can also define what display name will be used to describe the remote system. 


While the plugin is in use, the system account should not be changed, because it could result in synchronization problems.
Activities that were generated beforehand would become unusable.
Should the system account disconnect, make sure to re-connect the same one.

## Teacher view

Firstly, teachers add a `Collaborativefolders` instance to a course. 
Upon creation, teachers choose an activity name for the course.
Furthermore, teachers decide whether they have access to the collaborative folder(s); otherwise, they will be private for the students. This setting cannot be changed.
Finally, teachers choose whether they want to activate groupmode, resulting in the creation of separate group folders in the cloud storage.

Folders will not be created automatically. They are created on-demand once a student requests access to them.

## Student view

Before students can use a collaborative folder, they need to access it once via Moodle.
This serves two purposes. First, we do not just create folders in their personal spaces without them knowing; second, they can only request access if they have sufficient permission (as determined by Moodle).

When an instance is first opened, students are prompted to authorise Moodle to access the remote cloud system.

![Authorise Moodle to access the cloud system ("login")](https://user-images.githubusercontent.com/432117/56906717-19687380-6aa3-11e9-890e-a28f40f8c82f.png)

After authorising Moodle via the OAuth 2 flow, they are able to request access to the folder.
Access will be granted immediately.
They are able to choose a name for that folder, too:

![Choose a name to get access](https://user-images.githubusercontent.com/432117/56906716-19687380-6aa3-11e9-9e91-e826bd3491dc.png)

Once the folder is shared, the view in Moodle changes, providing the name and a link to the actual folder.

![Moodle provides a reference to the folder](https://user-images.githubusercontent.com/432117/56906714-19687380-6aa3-11e9-883d-27e02cdcbc69.png)

From this point onwards, this student does not need to use Moodle to access the files -- they can also go through the remote system directly, as the folder is permanently shared with them.
Offline work is possible, too (but will only be synchronised to the cloud folder once back online, of course).

![The folder is permanently shared in the remote system](https://user-images.githubusercontent.com/432117/56906715-19687380-6aa3-11e9-9568-3ad78e1aa597.png)

