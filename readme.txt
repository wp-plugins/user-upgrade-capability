=== Plugin Name ===
Contributors: justinticktock
Tags: multisite, role, user, cms, groups, teams, access, capability, permission, security
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Link multiple network sites/blogs together - Maintain only one site list of users.

== Description ==

'User Upgrade Capability' is a plugin to help with a multi-site network and helps with the administration of users and their roles. When you start using a multi-site WordPress installation you soon realise the power of having a separate site for a different function (e.g. main site, separate blog, separate calendar …etc).

You might think this is not required, however, one example comes from use of plugins that you wish run multiple times for a single site.  A common example is calendar plugins which use a fixed database table name, this means that you can’t install two calendars for different purposes on the same site.  Without 'User Upgrade Capabilities' or other methods this is a problem, but with the plugin you can create a new site and point back to the first re-using its user listing.

A common example is calendar plugins which use a fixed database table name, this means that you can't install two calendars for different purposes on the same site.  Without  'User Upgrade Capabilities' or other methods this is a problem,  but with the plugin you can create a new site and point back to the first re-using its user listing, you can see an example in the picture here.  If one of the auto en-role extension plugins, available in the WordPress plugin repository is used then the user doesn't even know that the second calendar is a different site.

Extensions:

If you select the options for extending functionality through other plugins the following are available for ease of installing..

* [User Role Editor](http://wordpress.org/plugins/user-role-editor/) for Admins to control user access/capability.
* [Join My Multisite](http://wordpress.org/plugins/join-my-multisite/) for auto en-role of users.
* [Blog Copier](http://wordpress.org/plugins/blog-copier/) for ease of duplicating sites.


[Plugin site](http://justinandco.com/plugins/user-upgrade-capabilities/).

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Goto the "Users" Menu and "Upgrade Capabillity" sub menu.


== Frequently Asked Questions ==

== Screenshots ==

1. The Settings Screen.

== Changelog ==

= 1.0 =
* Release into the wild.

== Upgrade Notice ==
