=== Multisite User Management ===
Contributors: thenbrent
Tags: capabilities, roles, custom post types
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 0.1

Recreate a WordPress role, like contributor, for your custom posts. Silly name, useful code.

== Description ==

Using custom post types on your site? 

Now you can control who can publish, edit and delete custom posts. 

For the technically endowed, this plugin takes care of mapping meta capabilities for custom post types. If you're developing a plugin that uses custom post types, check out the code to help manage who can do what with those posts.

== Installation ==

Please follow these instructions carefully. The plugin uses a special Multisite so it requires a slightly different installation to your vanilla WordPress plugin. 

1. Upload the `ms-user-management.php` file to the `wp-content/mu-plugins/` directory (you can discard the directory and its contents). 
1. Once uploaded to `mu-plugins`, the plugin will be activated automatically.
1. Navigate to the **Multisite User Management** section of the *Super Admin | Options* page and set the default role for each of your sites.

Note: WordPress does not create the `mu-plugins` directory by default so you may need to create it. This plugin will not work in the `wp-content/plugins` directory. 

== Frequently Asked Questions ==

= Does the plugin require a Multisite installation? =

Yes, WordPress takes care of the default role on non-Multisite installations.

= Why can't I install this plugin in the wp-content/plugins directory? =

WordPress accesses the standard plugins directory after a user is activated.

= Why aren't all my sites listed on the options page? =

Only blogs marked as public and flagged as safe (mature flag off) are included in the list.

= Does this plugin assign the default role to existing users? =

Yes, existing users will receive the default role. If you change the default role, existing users with the old default role will receive the new default role.

= Will default roles be allocated to new users who are also registering a new site? =

Yes, users registering with a site will receive all the existing default roles. 

The new site will not have a default role until it is manually set. Once set, all existing users will receive that role for the new site.

= Why are new users registering with a site not given the default role for the dashboard site? =

This is by design in the WordPress core.

= Where can I make feature requests, get support & report bugs? =

Submit an Issue on the plugin's [Github page](http://github.com/thenbrent/multisite-user-management/issues).

== Screenshots ==

1. **Super Admin Options** - Super admins can choose the default role for each site. New users be allocated this role when activating their account.

== Changelog ==

= 0.1 =
* Initial release.
