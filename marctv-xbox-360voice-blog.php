<?php

/*
  Plugin Name: MarcTV 360Voice Blog
  Plugin URI: http://www.marctv.de/blog/2010/08/25/marctv-wordpress-plugins/
  Description: Displays your XBOX360 GamerDNA Blog either in your sidebar as a widget or with a configurable template tag.
  Author: MarcDK
  Version: 1.9.5
  Author URI: http://marctv.de
  License: GPL2
 */

class XBOX360_Voice {

  var $xmlurl = 'http://www.360voice.com/api/blog-getentries.asp?tag=';
  var $namespace = 'xbox360voice';
  var $username = '';
  var $rl_name = '';
  var $avatarsize = '';
  var $displaycredits = '';
  var $displayavatar = '';
  var $displayweekly = '';
  var $hal_mode = '';
  var $count = '';
  var $error_msg = '';
  var $class_list = 'x3v_list';
  var $class_item = 'x3v_item';
  var $class_clist = 'x3v_list';
  var $class_citem = 'x3v_item';
  var $class_desc = 'x3v_desc';
  var $class_title = 'x3v_title';
  var $cachename = 'xbox360voice_cache';

  function XBOX360Voice() {
    $this->__construct();
  }

  function __construct() {
    $this->username = get_option($this->namespace . '_username');
    $this->rl_name = get_option($this->namespace . '_rl-name');
    $this->avatarsize = get_option($this->namespace . '_avatarsize');
    $this->displayweekly = get_option($this->namespace . '_displayweekly');
    $this->displayavatar = get_option($this->namespace . '_displayavatar');
    $this->displaycredits = get_option($this->namespace . '_displaycredits');
    $this->hal_mode = get_option($this->namespace . '_hal_mode');
    $this->count = get_option($this->namespace . '_count');
    add_action('get_xbox360voice_blog', array(&$this, 'get_xbox360voice_blog'));
    register_activation_hook(__FILE__, array(&$this, 'my_activation'));
    register_deactivation_hook(__FILE__, array(&$this, 'my_deactivation'));
    add_action('pull_xbox360voice_xml', array(&$this, 'do_this_twicedaily'));
    add_action('plugins_loaded', array(&$this, 'marctv_xbox360voice_loaded'));
    add_action('admin_menu', array(&$this, 'add_admin_menu'));
    add_action('wp_print_styles', array(&$this, 'add_styles'));
  }

  function get_xbox360voice_blog($username = "", $rl_name = "", $class_title = "", $class_list = "", $class_desc = "", $class_item = "", $class_clist = "", $class_citem = "") {

    if ($class_title != "") {
      $this->class_title = $class_title;
    }
    if ($class_desc != "") {
      $this->class_desc = $class_desc;
    }
    if ($class_list != "") {
      $this->class_list = $class_list;
    }
    if ($class_item != "") {
      $this->class_item = $class_item;
    }
    if ($class_citem != "") {
      $this->class_citem = $class_citem;
    }
    if ($class_clist != "") {
      $this->class_clist = $class_clist;
    }
    if ($rl_name != "") {
      $this->rl_name = $rl_name;
    }
    if ($username != "") {
      $this->username = $username;
    }

    $this->renderXBOX360VoiceBlog();
  }

  function __($text = '') {
    return __($text, 'xbox360voice-plugin');
  }

  function _e($text = '') {
    echo $this->__($text);
  }

  function add_admin_menu() {
    if (is_super_admin()) {
      wp_enqueue_style(
              "marctv-admin-settings", WP_PLUGIN_URL . "/marctv-xbox-360voice-blog/admin.css", false, "1.4");

      wp_enqueue_script(
              "jquery.xbox360voice_setup", WP_PLUGIN_URL . "/marctv-xbox-360voice-blog/admin.js", array("jquery"), "", 1);


      add_options_page($this->__('XBOX 360 Voice'), '<img src="' . WP_PLUGIN_URL . '/marctv-xbox-360voice-blog/icon.png' . '" width="10" height="10" alt="MarcTV XBOX 360 Voice - Icon" /> ' . $this->__(' XBOX 360 Voice'), 'edit_posts', 'xbox360voice', array(&$this, 'menu'));
    }
  }

  function add_styles() {
    wp_enqueue_style(
            "marctv-xbox360voice", WP_PLUGIN_URL . "/marctv-xbox-360voice-blog/styles.css", false, "1.0");
  }

  function my_activation() {
    wp_clear_scheduled_hook('pull_xbox360voice_xml');
    wp_schedule_event(time(), 'twicedaily', 'pull_xbox360voice_xml');
    add_option($this->cachename, '', "XBOX 360voice XML Cache", "no");
  }

  function my_deactivation() {
    wp_clear_scheduled_hook('pull_xbox360voice_xml');
    delete_option($this->cachename);
  }

  function marctv_xbox360voice_loaded() {
    $widget_ops = array('classname' => 'xbox360voice_blog', 'description' => "Displays the latest entries of your 360 voice gamerdna blog. ");
    wp_register_sidebar_widget('xbox360voice_blog', 'XBOX360Voice', array(&$this, 'marctv_xbox360voice_widget'), $widget_ops);
  }

  function marctv_xbox360voice_widget($args) {
    extract($args); // extracts before_widget,before_title,after_title,after_widget

    if (!$this->rl_name == '') {
      $blog_title = $this->rl_name . "'s Xbox 360 Blog";
    } else if (!$this->username == '') {
      $blog_title = $this->username . "'s Xbox 360 Blog";
    } else {
      $blog_title = "Xbox 360 Blog";
    }

    echo $before_widget . $before_title . $blog_title . $after_title;
    $this->renderXBOX360VoiceBlog();
    echo $after_widget;
  }

  function renderXBOX360VoiceBlog() {
    $xmlobj = get_option($this->cachename);


    if (!$this->username == "" AND !$xmlobj == "") {
      $xmlobj = @new SimpleXMLElement($xmlobj);

      // update fix
      if (isset($xmlobj->channel)) {
        $this->do_this_twicedaily();
        $xmlobj = get_option($this->cachename);
        $xmlobj = @new SimpleXMLElement($xmlobj);
      }

      echo $this->generateList($xmlobj);
    } else {
      echo '<ul><li><p>An error occurred!</p><p>Please go to the <a href="' . home_url() . '/wp-admin/options-general.php?page=xbox360voice">plugin settings</a></p></li></ul>';
    }
  }

  function getXMLObj($username) {
    $errormsg = '';
    $num = get_option($this->namespace . '_count') + 1;
    try {
      $sxe = @new SimpleXMLElement($this->xmlurl . $username . "&num=" . $num, NULL, TRUE);
    } catch (Exception $e) {
      $errormsg = $e->getMessage();
    }

    if (!$errormsg) {
      return $sxe;
    } else {
      return false;
    }
  }

  function generateList($xmlobj) {
    if ($this->avatarsize == 'l') {
      $size = '50';
    } else if ($this->avatarsize == 's') {
      $size = '32';
    } else {
      $size = '50';
      $this->avatarsize = 'l';
    }
    $avatar_img = '';

    if ($this->displayavatar == 'enabled') {
      if ($this->hal_mode == 'enabled') {
        $imagepath = WP_PLUGIN_URL . "/marctv-xbox-360voice-blog/avatarpic-" . $this->avatarsize . ".jpg";
      } else {
        $imagepath = 'http://avatar.xboxlive.com/avatar/' . $this->username . '/avatarpic-' . $this->avatarsize . '.png';
      }
      $avatar_img = '<img class="avatar" height="' . $size . '" width="' . $size . '" src="' . $imagepath . '" >';
    }

    $listcount = $this->count;

    if (count($xmlobj->blog->entry) < $this->count) {
      $listcount = count($xmlobj->blog->entry);
    }

    $output = "<ul class=\"" . $this->class_list . "\">\n";

    $i = 1;
    foreach ($xmlobj->blog->entry as $entry) {
      $showitem = true;
      $text = $this->filterOutput($entry->body);
      $title = $this->timeAgo(strtotime($entry->date), 1);

      if ($entry->attributes()->type > 0) {
        $title = 'Weeky Recap';
        if ($this->displayweekly == "disabled") {
          $showitem = false;
        }
      }

      if ($showitem && $i <= $listcount) {
        if ($i == 1) {
          $class = 'first ';
        }else if ($i == $listcount) {
          $class = 'last ';
        }else {
          $class = '';
        }
        
        $output .= "<li class=\"" . $class . $this->class_item . "\">\n
                    <strong class=\"" . $this->class_title . "\">" . $title . "</strong>\n
                    <p class=\"" . $this->class_desc . "\">" . $avatar_img . $text . "</p>
                    </li>\n";
        $i++;
      }
    }

    $output .= "</ul>\n";
    if ($this->displaycredits == 'enabled') {
      $output .= "<ul class=\"" . $this->class_clist . "\">";
      $output .= "<li class=\"" . $this->class_citem . "\"><small><a href=\"http://www.marctv.de/blog/2010/08/25/marctv-wordpress-plugins/\">MarcTV XBOX360Voice Plugin</a> powered by <a href=\"http://360voice.gamerdna.com/\">360voice.gamerdna.com</a></small></li>\n";
      $output .= "</ul>";
    }
    return $output;
  }

  function timeAgo($timestamp, $granularity=2, $format='D m Y') {
    $difference = time() - $timestamp;
    if ($difference < 0)
      return '0 seconds ago';
    elseif ($difference < 864000) {
      $periods = array('week' => 604800, 'day' => 86400, 'hr' => 3600, 'min' => 60, 'sec' => 1);
      $output = '';
      foreach ($periods as $key => $value) {
        if ($difference >= $value) {
          $time = round($difference / $value);
          $difference %= $value;
          $output .= ( $output ? ' ' : '') . $time . ' ';
          $output .= ( ($time > 1 && $key == 'day') ? $key . 's' : $key);
          $granularity--;
        }
        if ($granularity == 0)
          break;
      }
      if ($output == "1 day") {
        return "Yesterday";
      }
      return ($output ? $output : '0 seconds') . ' ago';
    }
    else
      return date_i18n($format, $timestamp);
  }

  /* Filters html and replaces the name */

  function filterOutput($html_str) {

    if (!$this->rl_name == '') {

      $html_str = str_replace($this->username, $this->rl_name, $html_str);
    }
    return strip_tags($html_str);
  }

  function do_this_twicedaily() {
    if (!$this->username == '') {
      $xmlobj = $this->getXMLObj($this->username);

      if ($xmlobj->error) {
        $this->error_msg = $xmlobj->error;
        return false;
      }

      if (count($xmlobj->blog->entry) > 0) {
        update_option($this->cachename, $xmlobj->asXML());
        return true;
      } else {
        $this->error_msg = "There seems to be a problem with the XML API. Try again later!";
        return false;
      }
    } else {
      return false;
    }
  }

  function renderOption($POST, $type, $name, $legend, $label, $default_value = '') {
    $msg = '';
    switch ($type) {
      case 'checkbox':
        if (get_option($this->namespace . '_' . $name) == '') {
          update_option($this->namespace . '_' . $name, $default_value);
        } else {
          if (isset($POST[$this->namespace . '-settings'])) {
            if (isset($POST[$this->namespace . '-' . $name])) {
              if (update_option($this->namespace . '_' . $name, 'enabled')) {
                $msg = '<p>' . $legend . ' enabled!</p>';
              }
            } else {

              if (update_option($this->namespace . '_' . $name, 'disabled')) {
                $msg = '<p>' . $legend . ' disabled!</p>';
              }
            }
          }
        }
        $input = '<input class="form_elem" type="checkbox" ';
        if (get_option($this->namespace . '_' . $name) == 'enabled') {
          $input .= 'checked="checked" ';
        }
        $input .= ' value="enabled" ';
        $input .= ' name="' . $this->namespace . '-' . $name . '" id="' . $this->namespace . '-' . $name . '" />';
        break;
      case 'text':
        if (get_option($this->namespace . '_' . $name) == '') {
          update_option($this->namespace . '_' . $name, $default_value);
        }
        if (isset($POST[$this->namespace . '-settings'])) {
          if (update_option($this->namespace . '_' . $name, trim(stripslashes($_POST[$this->namespace . '-' . $name])))) {
            $msg = '<p>' . $this->__($legend . ' saved.') . '</p>';
          }
        }
        $input = '<input  value="' . htmlentities(trim(stripslashes(get_option($this->namespace . '_' . $name)))) . '" name="' . $this->namespace . '-' . $name . '" id="' . $this->namespace . '-' . $name . '" ';
        $input .= 'class="form_elem" size="30" type="text" />';

        break;
      default; //Dropdown
        if (is_array($type)) {
          if (get_option($this->namespace . '_' . $name) == '') {
            update_option($this->namespace . '_' . $name, $default_value);
          }
          if (isset($POST[$this->namespace . '-settings'])) {
            if (update_option($this->namespace . '_' . $name, trim(stripslashes($_POST[$this->namespace . '-' . $name])))) {
              $msg = '<p>' . $this->__($legend . ' saved.') . '</p>';
            }
          }
          $input = '<select class="form_elem" name="' . $this->namespace . '-' . $name . '" id="' . $this->namespace . '-' . $name . '">';
          foreach ($type as $k => $v) {
            $input .= '<option ';
            if (get_option($this->namespace . '_' . $name) == $k) {
              $input .= 'selected="selected"';
            }
            $input .= ' value=' . $k . '>' . $v . '</option>';
          }
          $input .= '</select>';
        }
        break;
    }
    $output = '<fieldset class="options"><legend>' . $this->__($legend) . '</legend>';
    $output .= '<label for="' . $this->namespace . '-' . $name . '">' . $this->__($label) . ' ';
    $output .= $input;
    $output .= '</label>';
    $output .= '</fieldset>';
    echo $output;

    return $msg;
  }

  function menu() {
    $msg = '';
    if (isset($_POST['xbox360voice-settings'])) {
      check_admin_referer('xbox360voice-settings' . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }

    echo '<div class="wrap">';
    echo '<h2>' . $this->__('XBOX 360 Voice settings') . '</h2>';
    echo '<form method="post" action="">';
    echo '<input type="hidden" value="1" name="' . $this->namespace . '-settings" id="' . $this->namespace . '-settings" />';
    wp_nonce_field($this->namespace . '-settings' . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    $msg .= $this->renderOption($_POST, 'text', 'username', 'XBOX Live Username', 'Enter your Gamertag:');



    $msg .= $this->renderOption($_POST, 'text', 'rl-name', 'Realname (optional)', 'This will be displayed instead of your gametag name');
    $msg .= $this->renderOption($_POST, 'checkbox', 'displaycredits', 'Credits', 'Display credits link?', 'enabled');
    $msg .= $this->renderOption($_POST, 'checkbox', 'hal_mode', 'HAL 9000 mode', 'Display HAL 9000 image instead of avatar?', 'disabled');
    $msg .= $this->renderOption($_POST, 'checkbox', 'displayavatar', 'Avatar', 'Display avatar image?', 'enabled');
    $msg .= $this->renderOption($_POST, 'checkbox', 'displayweekly', 'Weekly Recap', 'Display weekly summary?', 'disabled');
    $msg .= $this->renderOption($_POST, array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7'), 'count', 'Item count', 'Number of items to be shown:', '3');
    $msg .= $this->renderOption($_POST, array('s' => '32px small', 'l' => '50px large'), 'avatarsize', 'Avatar Size', 'Size of the avatar image:', '1');
    echo '<p class="submit"><input class="button-primary" type="submit" name="submit" value="' . $this->__("Save &raquo;") . '" /></p>';
    echo '</form>';

    $this->username = get_option('xbox360voice_username');

    if ($this->username != '' && $this->do_this_twicedaily() == false) {
      $msg .= '<div class="warning">' . $this->__('Is "<strong>' . $this->username . '</strong>" signed up for a blog at XBOX360 Voice?') . ' <a href="http://360voice.gamerdna.com/tag/' . get_option('xbox360voice_username') . '"> Visit your 360Voice Blog or sign-up</a></div> ';
    }

    if (get_option('xbox360voice_username') == '') {
      $msg .= '<strong class="warning">Please enter you XBOX Live Gamertag</strong>';
    }
    if (empty($msg) && isset($_POST['xbox360voice-settings'])) {
      $msg .= '<p>' . $this->__('No changes made.') . '</p>';
    }



    if (!empty($msg)) {
      echo '<div id="message">';
      if (!empty($this->error_msg)) {
        echo '<p class="warning bold">' . $this->error_msg . '</p>';
      }
      echo $msg . '</div>';
    }

    echo '</div>';
  }

}

$xbox360voice_plugin = new XBOX360_Voice();
?>