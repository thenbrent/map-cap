=== Map Cap ===
Contributors: thenbrent
Tags: capabilities, roles, custom post types
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 1.0

Control who can publish, edit and delete custom post types.  Silly name, useful code.

== Description ==

Using custom post types on your site? 

Install this plugin to control which roles can publish, edit and delete posts of each custom type.

For the technically endowed, this plugin takes care of mapping meta capabilities for custom post types. If you're developing a plugin that uses custom post types, check out the mc_map_meta_cap function to learn how to control who can do what with your custom post type.

== Installation ==

1. Download and unzip the plugin
1. Upload the `map-cap` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Navigate to `Map Cap` under the `Settings` menu to set capabilities

== Frequently Asked Questions ==

= Why aren't all my custom post types listed on the options page? =

Only post types marked as public and not built-in (eg. page) are included in the list.

= Where can I make feature requests, get support & report bugs? =

Submit an Issue on the plugin's [Github page](http://github.com/thenbrent/map-cap/issues).

Or in the Plugin's [WordPress forum](http://wordpress.org/tags/map-cap?forum_id=10).

== Screenshots ==

1. **Admin Map Cap Settings Page** - Site administrators can choose the custom post type capabilities for each role. The capabilities assigned in this screen recreate the post capabilities for the custom post type "Issues". 

== Changelog ==

= 1.0 =
* Initial release.
