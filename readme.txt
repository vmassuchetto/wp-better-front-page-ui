=== Force Front Page ===

Contributors: viniciusmassuchetto, leogermani
Tags: rewrite rules, templates, home page
Requires at least: 3.0
Tested up to: 3.4
Stable tag: 0.01
License: GPLv2 or later

Force the front page without any user interference or dummy pages. The site home page will always load the `front-page.php` template, and you will be asked in the reading settings to define what URL will be used to the posts home page and fallback to `home.php` template.

== Description ==

This plugin came from a discussion about how WordPress should deal with the site home page. The supported way today is:

1. Define a `front-page.php` for the site home and a `home.php` for the posts home.
2. Go to the site admin and create a dummy page.
3. Set this page as the front page in the reading settings.

This approach is too sensible, and will let users delete this dummy home page, possibily breaking the site.

With this plugin, the same procedure will be:

1. Define a `front-page.php` for the site home and a `home.php` for the posts home.
2. Set what URL will point to the posts home and fallback in the `home.php` template.

== Installation ==

Upload the to your `wp-content/plugins` directory and configure it on `Settings -> Reading`.

== TODO ==

* Load correct body classes
* Flush rules on reading settings save

== Changelog ==

= 0.01 =

* First version with some functional code.
