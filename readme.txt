=== MarcTV XBOX 360Voice Blog ===
Contributors: marcdk
Tags: xbox360, blog, 360voice, gamerdna, marctv, php5
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 1.9.5

Displays the latest entries of your 360 voice gamerdna blog either as a widget for your sidebar or as a customizable function.

== Description ==

Displays the latest entries of your 360 voice gamerdna blog either as a widget for your sidebar or as a customizable function. The xml data from 360voice is being pulled twice daily with wp_cron.

== Installation ==

= Server Requirements =

This script requires at least PHP 5.0 and support for 'simplexml_load_file'. In the case that this won't work for the majority of people out there I will rewrite the script to support cURL.

= Basic =

* Register a 360Voice Blog for your XBOX Live Gamertag: http://www.360voice.com/forum/register-full.asp
* Upload the "MarcTV XBOX 360Voice Blog" plugin to your plugin folder or install from within wordpress
* Activate it.
* Enter your XBOX Live Gamertag in the "XBOX 360 Voice Blog" option page under settings.
* Customize the settings according to your needs
* Save your settings
* Go to "Appearance" -> "Widgets"
* Drag the "XBOX360Voice"- Widget into your sidebar

Note: If you don't have a theme with sidebar support skip to the "Advanced" tutorial below.

= Advanced =

If you are a little bit more experienced and want to customize the output just use the template function of this plugin:

'$xbox360voice_plugin->get_xbox360voice_blog($username = "", $rl_name = "", $class_title = "", $class_list = "", $class_desc = "", $class_item = "",$class_clist = "",$class_citem = "")'

You can use it in your template like this:

`<?php $xbox360voice_plugin->get_xbox360voice_blog('MarcTV', 'Marc', 'title', 'clr container_12', '', 'grid_4', 'clr container_12', 'grid_8'); ?>

The first two parameters are the XBOX Live Username and your reallife name. The others are the various css classes you can define.

Have fun!

== Changelog ==

= 1.0 =

First version.

= 1.1 =

Fixed admin backend message bugs

= 1.2 =

Fixed wording

= 1.3 =

* Fixed a wp_cron bug. The event "do_this_hourly" was scheduled everytime the plugin has been activated. Please deactivate und activate the plugin to fix this issue.
* Changed the scheduling time to "twicedaily".
* Added options to disable credits

= 1.3.1 =

Fixed small issues. Added screenshots

= 1.4 =

* Added avatar image support

= 1.4.1 =

* added new plugin homepage
* Fixed wording

= 1.5 =

* rewrote option functions from scratch
* added HAL 9000 mode
* added option to customize the number of shown items

= 1.5.1 =

* fixed real name option not working

= 1.5.2 =

* fixed migration bug. Should work fine now for users who use the plugin for the first time.

= 1.6 =

* wp update fix

= 1.6.1 =

* added clean up routine of activation to prevent double scheduling of wp cron

= 1.7 =

* Added Tutorial
* Added Icon

= 1.8 =

* Better detection for the correct xml format from 360voice. This should fix the problem for some people with a brand new 360voice account.

= 1.8.1 =

* added detection for PHP5 on activation.

= 1.8.2 =

* fixed output if the 360voiceblog got less items then defined in the options panel

= 1.8.3 =

* Added CSS for widget in footer of twentyten

= 1.9 =

* added new 360voice API xml source
* added option to disable weekly recap
* rewrote templating

= 1.9.1 =

* added date format for items older than a week.

= 1.9.2

* admin menu is now only visible to super admins.

= 1.9.5

* Added .first and .last classes to list

== Screenshots ==

1. The plugin in action as a widget
2. The plugin in action as a template tag with custom css classes.
3. Avatar images