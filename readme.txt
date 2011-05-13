=== Map Cap ===
Contributors: thenbrent
Tags: capabilities, roles, custom post types
Requires at least: 3.1
Tested up to: 3.1
Stable tag: 1.1

Control who can publish, edit and delete custom post types.  Silly name, useful code.

== Description ==

Using custom post types on your site? 

Install this plugin to control which roles can publish, edit and delete posts of each custom type.

= For Plugin Developers =

Prior to version 3.1, WordPress did not map meta capbilities for custom post types. This plugin offered a way to do that. 

If you're developing a plugin that uses custom post types, WordPress 3.1 and later includes the functionality as explained in the [Capbiltiies](http://codex.wordpress.org/Function_Reference/register_post_type) section of the [Register Post Type](http://codex.wordpress.org/Function_Reference/register_post_type) codex article. 

== Installation ==

1. Download and unzip the plugin
1. Upload the `map-cap` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Navigate to `Map Cap` under the `Settings` menu to set capabilities

== Frequently Asked Questions ==

= Why aren't all my custom post types listed on the options page? =

Only post types marked as public and not built-in (eg. page) with a custom capability type are included in the list.

If you are using the [Custom Post Type UI plugin](http://wordpress.org/extend/plugins/custom-post-type-ui/), when adding a custom post type, you must click *View Advanced Options* and change *Capability Type* to something other than post. 

If registering your own custom post type in code, the `$args` array you pass to `register_post_type` function must have `capability_type =>` set to something other than post.

= Where can I make feature requests, get support & report bugs? =

Add a new post in the WordPress.org [Plugin's Support Forum](http://wordpress.org/tags/map-cap).

== Screenshots ==

1. **Admin Map Cap Settings Page** - Site administrators can choose the capabilities each role has for each custom post type.

== Changelog ==

= 1.1 =
* Changing capabilities for custom posts with "post" capability type no longer allowed
* When changing a shared capability, a warning is shown
* Readme changes to help resolve common issues.
* Fixing bug in where a post without the 'author' feature could not be trashed while having Draft status.

= 1.0 =
* Initial release.
