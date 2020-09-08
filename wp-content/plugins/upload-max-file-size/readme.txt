=== Increase Maximum Upload File Size ===
Contributors: imagify, wp_media
Tags: max upload file size, increase upload limit, increase file size limit, upload limit, post max size, upload file size, upload_max_filesize
Requires at least: 3.0
Requires PHP: 5.3
Tested up to: 5.3
Stable tag: 2.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Increase maximum upload file size limit to any value. Increase upload limit - upload large files.


== Description==

**Increase upload file size limit to any value with one click.**

Plugin automatically detects upload limits set by WordPress and by the server / hosting provider, and displays them.

Access plugin's settings from the main WP admin menu.

> Please read the maximum possible values displayed in the plugin and if needed contact your hosting provider.

If you need help, please use the <a href="https://wordpress.org/support/plugin/upload-max-file-size/">official plugin support forum</a>. We reply to all messages ASAP!


== Installation ==

The usual, automatic way;

1. Open WordPress admin, go to Plugins, click Add New
2. Enter "increase maximum upload" in search and hit Enter
3. Plugin will show up as the first on the list, click "Install Now"
4. Activate & open plugin's settings page located in the main admin menu

Or if needed, install manually;

1. Download the plugin.
2. Unzip it and upload to _/wp-content/plugins/_
3. Open WordPress admin - Plugins and click "Activate" next to the plugin
4. Activate & open plugin's settings page located in the main admin menu


== Screenshots ==

1. Increase maximum file upload size with one click


== Frequently Asked Questions ==

= Does this plugin work with all servers and hosting providers? =

Yes, it works with all servers. But, please know that server adjusted limits can't be changed from a WordPress plugin. If the server set limit is 16MB you can't increase it to 128MB via WordPress. You have to email your hosting provider and ask them to increase the limit. Install the plugin and it'll tell you what the limits are and what to do.


== Changelog ==

= v2.0.4 =
* 2019/11/16
* Fix: Avoid plugin to display twice in the plugin listing.

= v2.0.3 =
* 2019/11/13
* Fix: The plugin does not have a valid header.

= v2.0.1 =
* 2019/11/13
* Regression fix: Avoid auto-deactivation after upgrading to 2.0.

= v2.0 =
* 2019/11/04
* Revamp the settings page.

= v1.35 =
* 2019/08/12
* better messages to clearly communicate max upload file size limits
* minor changes
* 309,800 downloads

= v1.3 =
* 2019/05/23
* almost complete plugin rewrite
* 50k installations, 257k downloads

= v1.0 =
* 2014/09/28
* initial release
