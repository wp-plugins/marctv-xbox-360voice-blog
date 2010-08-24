=== MarcTV XBOX 360Voice Blog ===
Contributors: marcdk
Tags: xbox360, blog, 360voice, gamerdna, marctv
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 1.4

Displays the latest entries of your 360 voice gamerdna blog either as a widget for your sidebar or as a customizable function.

== Description ==

Displays the latest entries of your 360 voice gamerdna blog either as a widget for your sidebar or as a customizable function. The xml data from 360voice is being pulled twice daily with wp_cron.

== Installation ==

Upload the "MarcTV XBOX 360Voice Blog" plugin to your blog, Activate it.

You can use the widget to simply drag the blog into your sidebar. Don't forget to set your XBOX Live username in the settings.

If you are a little bit experienced and what to customize the output a but more just use the template function of this plugin:

'$xbox360voice_plugin->get_xbox360voice_blog($username = "", $rl_name = "", $class_title = "", $class_list = "", $class_desc = "", $class_item = "",$class_clist = "",$class_citem = "")'

You can use it in your template like this:

'<?php $xbox360voice_plugin->get_xbox360voice_blog('MarcTV', 'Marc', 'title', 'clr container_12', '', 'grid_4', 'clr container_12', 'grid_8'); ?>'

The first two parameters are the XBOX Live Username and your reallife name. The others are the various css classes you can define.

Have fun!

= Server Requirements =

This script requires support for 'simplexml_load_file'. In the case that this won't work for the majority of people out there I will rewrite the script to support cURL.

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


== Screenshots ==

1. The plugin in action as a widget
2. The plugin in action as a template tag with custom css classes.
3. Avatar images