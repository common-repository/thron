=== THRON  ===
Contributors: thronspa, websolutedev
Tags: DAM
Stable tag: 1.3.3
Requires at least: 5.9
Tested up to: 6.4
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

THRON is a cloud solution used by leading companies to maximize efficiency and reduce costs related to digital content management.

== Description ==

THRON Connector for Wordpress allows you to publish and update the contents of your website quickly, intuitively and automatically. Editors have access to a library of up-to-date and approved content, directly within the CMS, without the need to create copies.

Content can be embedded using THRON Universal Player or the Gutenberg editor.

The connector supports THRON DELIVERY and all its features: from Universal Player to our Real-Time Image Editor, you can publish your content in a centralized and direct way, without duplications and always at maximum performance.

= Features =

- Supports images, videos, audio files and any other type of content
- Automatic update: every time you edit a content in THRON, your site is automatically updated, with no need of manual updating activities
- Optimize images for the web: with the Real-Time Image Editor, you can optimize images during publication without creating new copies.
    - Manage the automatic cropping of images directly from the connector, choosing between different cropping modes: Automatic, Centered, Product and Manual
    - Independently adjust image parameters such as quality, brightness, contrast, sharpness and color saturation, directly from the plug-in and with no need of post-production.
- Maximum performance: guarantee the best performance for your users wherever they are thanks to our Universal Player and its features. The player dynamically adapts the quality of the content according to the user's conditions, supports lazy loading and automatically optimizes the reproduced content for SEO purposes.
- 100% compliant with the Gutenberg editor.

= Benefits = 

- Eliminate content duplication
- Eliminate integration costs
- Improve page loading performance and peak load management with THRON's infrastructure
- Automatically gather insights into the usage of your published content with our Content Analytics tools.

= Requirements =

THRON Connector for WordPress is free of charge. If you already use THRON DAM Platform, please contact your Success Manager or email us at marketplace@thron.com to learn how to install the connector.

Would you like to request a demo of THRON DAM Platform? [Click here](https://www.thron.com/en/book-a-demo).

= About THRON =

- [Our Site](https://www.thron.com/en)
- [THRON DAM Platform](https://www.thron.com/en/dam-platform)
- [THRON Connectors](https://www.thron.com/en/connettori-thron)
- [THRON Blog](https://blog.thron.com/en)
- [Help Portal](https://help.thron.com/hc/en-us)

= Contributors & Developers =

[THRON spa](https://profiles.wordpress.org/thronspa/)
[Websolute spa](https://profiles.wordpress.org/websolutedev/)

== Installation ==

= Install from within wordpress =
* Visit the plugins page within your dashboard and select `Add New`.
* Search for `THRON`.
* Select `THRON` from the list.
* Activate it from your Plugins page.
* Go to `Setting up` below.

= Install THRON plugin manually =
1. Upload `thron.zip` to the `/wp-content/plugins/` directory
2. Extract files
3. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

= Setting up =
* Once the plugin is activated, go to the `THRON` settings.
* You'll be prompted to your THRON clientId, AppId and AppKey that you can find on the Wordpress Connector page in THRON.
* After saving, the plugin is active and connected to the THRON platform.

== Screenshots ==
1. The Attachment page detail
2. Content selction from THRON
3. Embedding images using the gutenberg block
4. THRON Plugin settings
5. THRON Player gutenberg block


== Changelog ==

= 1.3.3 =
- Check url where media queries already exist before adding others 

= 1.3.2 =
- Add new feature in settings, possibility to set balance between image weight and resolution
- Fixed some PHP warnings

= 1.3.1 =
- Fixed some PHP warnings
- Fixed media endpoint in REST API

= 1.3.0 =
- Extended support to PHP 8

= 1.2.2 =
- Fix for WordPress.com Hosting

= 1.2.1 =
- THRON Player
 - Enhance parameters
 - Crop feature (Manual & THRON-powered)
 - Various improvements
- Manual crop on native Gutenberg Image block
- Buttons for “Edit on Thron” and “Update on Thron”
- Translations improvements
- Various fix

= 1.0.1 =
- Bugfixes

= 1.0.0 =
- Initial version
