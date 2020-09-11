=== Plugin Name ===
Contributors: Seolus
Donate link: https://www.seolus.com
Tags: metadata, Dublin Core, Open Graph, SEO, Search Engine Optimisation, DC, keywords, Meta Tags, MetaData, Generator, Search Engine Optimization, SEM
Requires at least: 4.2.3
Tested up to: 5.4.1
Stable tag: 5.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A very lightweight plugin that adds the Dublin Core metadata to your WP website.

== Description ==

The plugin autogenerates the Dublin Core meta tags for all pages, posts and custom taxonomies, including Woocommerce products and categories. As a bonus, I also added the option to autogenerate Open Graph metadata, which can be turned on and off with a single click, and the additional option to have a default featured image for posts and pages that normally don't have any featured image or for the situation where your main page is generated from a list of the latest posts or a simillarly custom-made solution.

Additionally, if the keywords meta tag option is active, it also has the capability of autogenerating keywords from post tags and titles (including Woocommerce products), join them with the general keywords as defined in the plugin settings, and then write them in the page's "keywords" meta tag.

And lastly, it comes with a neat extra option that, if checked, cleans up your code by eliminating all useless meta tags and advertising comments such as the 'Generators' left by Wordpress, WooCommerce, Slider Revolution, LayerSlider, WPML, WPBakery Page Builder (Visual Composer) and Yoast SEO. For each of them, there are various methods, plugins and bits of code that work to eliminate these comments, but I never saw them all in one place and within the same plugin, so I thought having an option to clean all metas, generators and comments added by other plugins with just one click would be a nice addition.

Very very lightweight and easy to use.

== Installation ==

1. Upload the decompressed 'dublin-core-metadata-generator.zip' folder to the '/wp-content/plugins/' directory, or upload the zip file via the WordPress dashboard: Plugins > Add New
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Adjust your settings through the 'Settings' panel menu

== Frequently Asked Questions ==

= What should I do if there is a bug? =

Due to the well-written code and the simplicity of this plugin, chances are it will behave flawlessly in all WordPress installations. I've tested the plugin in different environments and I have all confidence it has no bugs or errors. However, if you spot any issues, feel free to send a message to seo(at)webgraphic.ro

== Screenshots ==


== Changelog ==

= 1.3.2 = Updated the code to remove Yoast HTML Comments from version 14.1, plus other minor improvements. Removed some outdated or redundant metas.
= 1.3.1 = Minor update. User KTS915 pointed out there's a warning on PHP 7.3, which is now fixed.
= 1.3 = John Jackson from johnridesa.bike fixed a couple of PHP notices that were being shown on PHP 7.1. Thank you, John!
= 1.2 = Added the option to remove more additional generator meta tags left by other plugins: in this version you can now also clean the comments bloating the public source code left by LayerSlider, Slider Revolution, WPML, WPBakery and Yoast SEO. This option, if activated, will work only when any of those plugins are installed, otherwise it doesn't change anything. Fixed a small bug in which "lang" was removed when OpenGraph wasn't on.
= 1.1 = Fixed a small bug showing empty string when tags were not present. Cleaned up the code and removed some obsolete metadata. Removed the outdated keywords meta. Added support for Yoast titles when present.
= 1.0 = Dublin Core Metadata Generator. WooCommerce compatible.