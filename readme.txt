=== HTML Plus Recent Posts by Category Widget ===
Contributors: linux4me2
Tags: recent posts, category, widget
Requires at least: 5.0
Tested up to: 5.8
Requires PHP: 7.0
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.opensource.org/licenses/GPL-2.0

Like the Legacy Recent Posts widget, but adds the ability to select the category and add text or HTML before the list of posts.

== Description ==

This widget allows you to insert arbitrary Text and/or HTML code (optional) to precede a list of recent posts by category. It makes it easy to add introductory comments and to achieve interesting effects like wrapping your posts list around an image. It's designed to be lightweight and easy on resources.

> Note: Widget Blocks, added in WordPress 5.8, make this plugin no longer necessary. The same results can be obtained by using a Widget Group, Heading Block, Custom HTML Block, and the  Latest Posts Block. The latter also allows the use of multiple categories, which is not available with this plugin. Unless something major happens to change things, I won't be developing the plugin beyond the current version (2.0.0).

== Installation ==

1. Upload the plugin by using Plugins -> Add New.
1. Activate the plugin through the "Plugins" menu in WordPress.
1. Add the widget to the desired location in Appearance -> Widgets.
1. Set the title, content, and category.
1. Save your changes.

== Frequently Asked Questions ==

= Can this widget display more than one category of posts? =

You can specify only one post category for each instance of the widget. If you want to display more than one category of recent posts, you can use more than one instance of the widget.

= What if I just want to display recent posts by category, and no HTML? =

If you don't need to display any HTML, just leave the Content section blank.

= Can I include an image in the Content field? =

Yes, you can include images or any other HTML allowed in the default custom HTML widget in the Content field.

= How can I customize the display of the widget? =

You can use the class "htmlplusrecpostsbycat", which wraps the widget, to customize the CSS, as well as the classes assigned by WordPress and your theme.

= Is it compatible with PHP 8.0? =

Yes.

= What happened to the "Automatically Add Paragraphs" option? =

The automatic paragraph option and functionaility was removed in versiion 2.0.0 in order to take advantage of the features incorporated in the WordPress default custom HTML UI. If you were relying on the automatic paragraph option for existing widgets, you can either stick with version 1.3.2, or modify your widgets to use HTML paragraph tags and upgrade the plugin to version 2.x.

== Screenshots ==

1. This is the user interface of the HTML Plus Recent Posts by Category Widget.

== Changelog ==

= 2.0.0 =
* Added custom HTML coding aids and syntax coloring via the default Custom HTML Widget.
* Removed auto-paragraph capability. (Update your existing widgets that used this with paragraph tags before upgrading!)

= 1.3.2 =
* PHP cleanup.
* Verify PHP 8.0 compatibility.
* Change translation functions to provide defaults.
* Update plugin details.

= 1.3.1 =
* Code streamlining.
* Add PHP requirement.

= 1.3.0 =
* PHP 7.2 compatibility: removed create_function() call
* WordPress version testing to 4.9.6

= 1.2 =
* Fixed POT file location.

= 1.1 =
* Added internationalization.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.3 =
PHP 7.2 compatibility, WordPress version testing to 4.9.6.

= 1.2 =
Fixed POT file location.

= 1.1 =
Added internationalization.
