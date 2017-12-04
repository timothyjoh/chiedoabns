=== LearnDash Content Cloner ===
Current Version: 1.2.0
Author:  WisdmLabs
Author URI: https://wisdmlabs.com/
Plugin URI: https://wisdmlabs.com/course-content-cloner-for-learndash/
Tags: LearnDash Add-on, Course Cloner LearnDash, Content Cloner LearnDash, LearnDash Content Cloner
Required WordPress version at least: 4.1.1
Tested up to: 4.8.3
License: GNU General Public License v2 or later

Tested with LearnDash version: 2.5.2


== Description ==

With the Content Cloner extension for LearnDash, duplicating courses becomes a cake walk. Just install the plugin, and use the ‘clone’ option. The entire course hierarchy (course, lesson, topics) are duplicated and added to your LearnDash LMS. Also can clone the LearnDash Groups and its associated settings.


== Installation ==

1. Once you download the plugin using the download link, go to Plugin-> Add New menu in your dashboard and click on the ‘Upload’ tab.
Choose the ‘ld-content-cloner.zip’ file to be uploaded and click on ‘Install Now’.
2. After the plugin has installed successfully, click on the Activate Plugin link or activate the plugin from your Plugins page.
3. Alternately, you can upload the plugin manually, by unzipping the downloaded plugin file, and adding the plugin folder to wp-content/plugins on your server, using an FTP client of your choice.


== User Guide ==

Content Cloner User Guide
Upon installing and activating the Content Cloner extension for LearnDash, you should notice a ‘Clone’ option under every Course and Groups in LearnDash admin settings.

How to Clone a LearnDash Course
In your WordPress admin panel, go to LearnDash LMS -> Courses. The list of courses on your LMS should be displayed. Upon hovering over a course name, you should notice a ‘Clone Course’ option.

Upon clicking this option, the course should be completely cloned along with associated lessons and topics, and you should receive a course successfully cloned notification.

At this point, you can either edit the title of the course cloned, or bulk edit the course, lesson, topic titles.

All course content duplicated, is categorized in the same way as the original content and published. Only the course is saved as draft.

How to Clone a LearnDash Group
It is the same as the Course, but only on the all groups list. The cloned group will maintain all settings that were associated with the original group.

Does LDCC clone a course when shared steps setting is disabled
The latest version of LDCC will not clone the courses in the following scenario:
If the shared steps setting is enabled and created the course and then cloned course by disabling the shared steps setting.(We are not cloning the course in the previously mentioned scenario because LearnDash version 2.5.2 does not support association of courses with its contents in such a scenario)

== Frequently Asked Questions ==

= Does the Content Cloner plugin have any prerequisites? =

Nope! Just LearnDash

= What version of LearnDash does the Content Cloner plugin need? =

The LearnDash Content Cloner works with the latest version of LearnDash (currently 2.2.1).

= Can I clone a lesson or topic? =

At the moment you can clone the entire course, not specific lessons or topics. When a course is cloned, the associated lessons and topics are duplicated.

= How do I clone a course? =

You can clone a course by heading over to LearnDash LMS -> Courses. Upon hovering over a course title, you should notice a ‘Clone Course’ option. Use this option to clone a course.

= How do I contact you for support? =

You can direct your support request to us, using the Support form.

= Which other plugins do you recommend for my LearnDash LMS? =

Along with the Content Cloner plugin, you can use the Quiz Reporting Extension, and the Instructor Role Extension plugins for your LMS.


== Changelog ==

= 1.2.0 =
* Compatibility with LearnDash version 2.5.2
* Cloned course having content shared with other course(s), duplicated the shared content as well.

= 1.1.0 =
* Published all course contents on cloning and saved the cloned course as draft.
* Provided edit post link for course contents listed on bulk rename page.
* Cloned every post meta of course and its contents.

= 1.0.4 =
* Added License template to give updates for plugin.

= 1.0.1 =
* Group Cloning
* Fixed the Lesson and Topic Order data for cloned course.

= 1.0.0 =
* Plugin Launched