=== Plugin Name ===
Contributors: justinticktock
Tags: multisite, role, user, cms, groups, teams, access, capability, permission, security
Requires at least: 3.5
Tested up to: 4.3
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Link multiple network sites/blogs together - Maintain only one site list of users.

== Description ==

'User Upgrade Capability' is a plugin to help with a multi-site network and helps with the administration of users and their roles. When you start using a multi-site WordPress installation you soon realise the power of having the ability to use a separate site for different functions (e.g. main site, separate blog, separate calendar …etc) each can then be handled separately and even with different themes.

However, without 'User Upgrade Capabilities' or other methods you would need to maintain user lists on each site and if it is the same group of users this can be an unwelcome overhead for administrators.  'User Upgrade Capability' helps with this admin task and allows you to create a new site and point back to a reference site re-using its user listing.

One example of where this is helpful is for the case where you want multiple calendars for different purposes on the same site.  Calendar plugins generally use a fixed database table name, this means that you can't install two calendars on the same site.  With 'User Upgrade Capabilities' you can create a new site for each calendar and point back to the reference site re-using its user listing.  Also if one of the auto en-role extension plugins, available in the WordPress plugin repository is used then the user doesn't even know that the calendars are on a different site.

You can find more detail on the plugin at the [Plugin site](http://justinandco.com/plugins/user-upgrade-capabilities/).

= WARNING = Activating this plugin on a site will replace the available user roles/capabilities with a copy from the reference site you will not be able to undo.

Translation:

* English
* Serbo-Croatian, sr_RS ( props Borisa Djuraskovic [webhostinghub.com](http://webhostinghub.com) )

Extensions:

If you select the options for extending functionality through other plugins the following are available for ease of installing..

* [User Role Editor](http://wordpress.org/plugins/user-role-editor/) for Admins to control user access/capability.
* [Join My Multisite](http://wordpress.org/plugins/join-my-multisite/) for auto en-role of users.
* [Blog Copier](http://wordpress.org/plugins/blog-copier/) for ease of duplicating sites.


== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Goto the "Users" Menu and "Upgrade Capabillity" sub menu.


== Frequently Asked Questions ==

== Screenshots ==

1. General Settings Screen.
2. Additional User Capabilities Settings Screen.
3. Plugin Suggestions Settings Screen.

== Changelog ==

Change log is maintained on [the plugin website](http://justinandco.com/plugins/user-upgrade-capability-change-log/ "User Upgrade Capability – Change Log")

== Upgrade Notice ==
