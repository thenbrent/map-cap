=== Map Cap ===
Contributors: thenbrent
Tags: capabilities, roles, custom post types
Requires at least: 3.1
Tested up to: 3.1
Stable tag: 2.1

Control who can publish, edit and delete custom post types.  Silly name, useful code.

== Description ==

Using custom post types on your site? 

Install this plugin to control which roles can publish, edit and delete posts of each custom type. 

For this plugin to work, your custom post type must meet a number of requirements as outlined in the [FAQ](http://wordpress.org/extend/plugins/map-cap/faq/).


== Installation ==

1. Download and unzip the plugin
1. Upload the `map-cap` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress
1. Navigate to `Map Cap` under the `Settings` menu to set capabilities


== Frequently Asked Questions ==

= Why aren't all my custom post types listed on the options page? =

There are four requirements for a custom post type to show up in Map Cap's settings page.

The post type must:

1. be public
1. not be a built-in post type eg. page & post
1. use a custom capability type (not the default capability of *post*). This is done when registering the Custom Post Type. In the `$args` array you pass to the `register_post_type` function, your plugin must have `capability_type =>` set to something other than post.
1. be registered with the `map_meta_cap` argument set to `true` - the default is `false`. Without this parameter set to `true`, WordPress does not map any meta capabilities.

= Force Mapping =

As many custom post types do not set the `map_meta_cap` to true, Map Cap offers an option to change the value of the `map_meta_cap`. 

For this to work, the plugin must register the post type on the `init` hook with a priority less than 10,000. 

This feature works with the [Custom Post Type UI plugin](http://wordpress.org/extend/plugins/custom-post-type-ui/) plugin but is not guaranteed to work with any other plugins.

= Using the Custom Post Type UI plugin? =

If you are using the [Custom Post Type UI plugin](http://wordpress.org/extend/plugins/custom-post-type-ui/), when adding a custom post type, you must click *View Advanced Options* and change *Capability Type* to something other than *post*. For example, for a custom post type of *Stories* the capability could be *story*.

You then need select the custom post type under the *Force Mapping* section of the Map Cap settings page.

= Where can I report bugs? =

Add a new post in the WordPress.org [Plugin's Support Forum](http://wordpress.org/tags/map-cap).


== Screenshots ==

1. **Admin Map Cap Settings Page** - Site administrators can choose the capabilities each role has for each custom post type.

== Changelog ==

= 2.1 =
* Fix 'Invalid argument supplied for foreach() in map-cap.php on line 268' when deactivating a plugin or theme which registered the custom post type Map Cap was operating on

= 2.0 =
* Changing capabilities for custom posts with "post" capability type no longer allowed
* Option to change the `map_meta_cap` flag at run-time
* When changing a shared capability, a warning is shown
* Readme changes to help resolve common issues.
* Fixing bug where a post without the 'author' feature could not be trashed while having Draft status.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 2.1 =
* Important Upgrade to fix potential 'Invalid argument supplied for foreach() in map-cap.php on line 268' notices

= 2.0 =
* Important Upgrade to fix a variety of issues with deleting posts and drafts. Requires WordPress 3.1+
