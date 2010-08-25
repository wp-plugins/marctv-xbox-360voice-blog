<?php
/*
  Plugin Name: MarcTV 360Voice Blog
  Plugin URI: http://www.marctv.de/blog/2010/08/25/marctv-wordpress-plugins
  Description: Displays your XBOX360 GamerDNA Blog either in your sidebar as a widget or with a configurable template tag.
  Author: Marc Tönsing
  Version: 1.4.1
  Author URI: http://marctv.de
  License: GPL2
 */

class XBOX360_Voice {
    const VOICEURL = 'http://360voice.gamerdna.com/rss/';
    var $username       = '';
    var $rl_name        = '';
    var $avatarsize     = '';
    var $count          = 3; //will be custmizable in future releases
    var $class_list     = 'x3v_list';
    var $class_item     = 'x3v_item';
    var $class_clist    = 'x3v_list';
    var $class_citem    = 'x3v_item';
    var $class_desc     = 'x3v_desc';
    var $class_title    = 'x3v_title';
    var $displaycredits = "true";
    var $displayavatar  = "true";

   
    var $cachename = 'XBOX360Voice_cache';

    function XBOX360Voice() {
        $this->__construct();
    }

    function __construct() {
        add_action('get_xbox360voice_blog', array(&$this, 'get_xbox360voice_blog'));
        register_activation_hook(__FILE__, array(&$this, 'my_activation'));
        register_deactivation_hook(__FILE__, array(&$this, 'my_deactivation'));
        add_action('pull_xbox360voice_xml', array(&$this, 'do_this_twicedaily'));
        add_action('plugins_loaded', array(&$this, 'marctv_xbox360voice_loaded'));
        add_action('admin_menu', array(&$this, 'add_admin_menu'));
        add_action('wp_print_styles', array(&$this, 'add_styles'));

        $this->username = get_option('xbox360voice_username');
        $this->rl_name = get_option('xbox360voice_rl_name');
        $this->avatarsize = get_option('xbox360voice_avatarsize');

        if(get_option('xbox360voice_displaycredits')==''){
            update_option('xbox360voice_displaycredits', trim(stripslashes($this->displaycredits)));
        }else{
            $this->displaycredits = get_option('xbox360voice_displaycredits');
        }

        if(get_option('xbox360voice_displayavatar')==''){
            update_option('xbox360voice_displayavatar', trim(stripslashes($this->displayavatar)));
        }else{
            $this->displayavatar = get_option('xbox360voice_displayavatar');
        }

    }

    function get_xbox360voice_blog($username = "", $rl_name = "", $class_title = "", $class_list = "", $class_desc = "", $class_item = "",$class_clist = "",$class_citem = "") {

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
        wp_enqueue_style(
                "marctv-admin-settings", WP_PLUGIN_URL . "/marctv-xbox-360voice-blog/admin.css",
                false, "1.4");
        add_options_page($this->__('XBOX 360 Voice'), $this->__('XBOX 360 Voice'), 'edit_posts', 'xbox360voice', array(&$this, 'menu'));
    }

    function add_styles(){
        wp_enqueue_style(
                "marctv-xbox360voice", WP_PLUGIN_URL . "/marctv-xbox-360voice-blog/styles.css",
                false, "1.0");
    }

    function my_activation() {
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
        if (!$this->username == "" AND !get_option($this->cachename) == "") {
            $xmlobj = @new SimpleXMLElement(get_option($this->cachename));
            echo $this->generateList($xmlobj);
        } else {
            echo '<ul><li><p>No Username provided!</p><p>Please go to the <a href="' . home_url() . '/wp-admin/options-general.php?page=xbox360voice">plugin settings</a> and enter your username.</p></li></ul>';
        }
    }

    function getXMLObj($username) {
        try {
            $sxe = @new SimpleXMLElement('http://360voice.gamerdna.com/rss.asp?tag=' . $username, NULL, TRUE);
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

        if($this->avatarsize == 'l'){
            $size = '50';
        }else if($this->avatarsize == 's'){
            $size = '32';
        }else{
            $size = '50';
            $this->avatarsize = 'l';
        }
        
        $avatar_img = '';

        if($this->displayavatar=="true"){
            $avatar_img = '<img class="avatar_left" height="' . $size . '" width="' . $size . '" src="http://avatar.xboxlive.com/avatar/' . $this->username . '/avatarpic-' . $this->avatarsize . '.png" >';
        }

        $output = "<ul class=\"" . $this->class_list . "\">\n";
        for ($i = 0; $i < $this->count; $i++) {
            $output .= "<li class=\"" . $this->class_item . "\">\n
                <strong class=\"" . $this->class_title . "\">" . $this->extractDate($xmlobj->channel[0]->item[$i]->title) . "</strong>\n
                <p class=\"" . $this->class_desc . "\">" . $avatar_img . $this->filterOutput($xmlobj->channel[0]->item[$i]->description) . "</p>
                </li>\n";
        }
        if($this->displaycredits=="true"){
            $output .= "</ul>\n";
            $output .= "<ul class=\"" . $this->class_clist . "\">";
            $output .= "<li class=\"" . $this->class_citem . "\"><small><a href=\"http://www.marctv.de/blog/2010/08/25/marctv-wordpress-plugins\">MarcTV XBOX360Voice Plugin</a> powered by <a href=\"http://360voice.gamerdna.com/\">360voice.gamerdna.com</a></small></li>\n";
            $output .= "</ul>";
        }
        return $output;
    }

    function extractDate($html_str) {
        $arr = explode(' - ', $html_str);
        $date = $this->timeAgo(strtotime($arr[1]), 1);

        if(preg_match("/Weekly Recap/i", $arr[0])){
            return "Weekly Recap - ".$date;
        }

        return $date;
    }

    function timeAgo($timestamp, $granularity=2, $format='Y-m-d H:i:s') {
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
            return date($format, $timestamp);
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
            if (count($xmlobj->channel->item) > 3) {
                update_option($this->cachename, $xmlobj->asXML());
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function menu() {
        // this is kind of ugly. Why doesn't wp implement a function for all this? Or did I miss something?
        $msg = '';
        if (isset($_POST['xbox360voice-settings'])) {
            check_admin_referer('xbox360voice-settings' . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

            if (update_option('xbox360voice_username', trim(stripslashes($_POST['xbox360voice-username'])))) {
                $msg .= '<p>' . $this->__('XBOX Live username saved.') . '</p>';
            }

            if (update_option('xbox360voice_rl_name', trim(stripslashes($_POST['xbox360voice-rl-name'])))) {
                $msg .= '<p>' . $this->__('Realname saved.') . '</p>';
            }

            if (update_option('xbox360voice_avatarsize', trim(stripslashes($_POST['xbox360voice-avatarsize'])))) {
                $msg .= '<p>' . $this->__('Avatar size saved.') . '</p>';
            }
           
            if($_POST['xbox360voice-displaycredits']==true){
               if(get_option('xbox360voice_displaycredits')!='true'){
                   $msg .= '<p>' . $this->__('Credits enabled') . '</p>';
               }
               update_option('xbox360voice_displaycredits', 'true');
            }else{
               if(get_option('xbox360voice_displaycredits')!='false'){
                   $msg .= '<p>' . $this->__('Credits disabled') . '</p>';
               }
               update_option('xbox360voice_displaycredits', 'false');
            }


            if($_POST['xbox360voice-displayavatar']==true){
               if(get_option('xbox360voice_displayavatar')!='true'){
                   $msg .= '<p>' . $this->__('Avatar enabled') . '</p>';
               }
               update_option('xbox360voice_displayavatar', 'true');
            }else{
               if(get_option('xbox360voice_displayavatar')!='false'){
                   $msg .= '<p>' . $this->__('Avatar disabled') . '</p>';
               }
               update_option('xbox360voice_displayavatar', 'false');
            }


            if (empty($msg)) {
                $msg .= '<p>' . $this->__('No changes made.') . '</p>';
            }
        }

        $this->username         = get_option('xbox360voice_username');
        $this->rl_name          = get_option('xbox360voice_rl_name');
        $this->displaycredits   = get_option('xbox360voice_displaycredits');
        $this->displayavatar    = get_option('xbox360voice_displayavatar');
        $this->avatarsize       = get_option('xbox360voice_avatarsize');

        if ($this->username !='' && $this->do_this_twicedaily() == false) {
            $msg .= '<strong class="warning">' . $this->__('There seems to be problem with the GamerDNA Feed. Please check your 360voice blog:') . ' </strong> <a href="http://360voice.gamerdna.com/tag/' . $this->username . '">360Voice Blog</a>';
        }

        if ($this->username ==''){
            $msg .= '<strong class="warning">Please enter you XBOX Live Gamertag</strong>';
        }

        if (!empty($msg)) {
            echo '<div id="message">' . $msg . '</div>';
        }
?>
        <div class="wrap">
            <h2><?php $this->_e('XBOX 360 Voice settings') ?></h2>
            <form method="post" action="">
                <input type="hidden" value="1" name="xbox360voice-settings" id="xbox360voice-settings" />
                <?php wp_nonce_field('xbox360voice-settings' . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']); ?>
                <fieldset class="options"><legend><?php $this->_e('XBOX Live Username:') ?></legend>
                    <label for="xbox360voice-username"> <?php $this->_e('Enter your Gamertag:') ?>
                    <input class="form_elem" size="30" type="text" value="<?php echo htmlentities(trim(stripslashes($this->username))); ?>" name="xbox360voice-username" id="xbox360voice-username" />
                    </label>
                </fieldset>

                <fieldset class="options"><legend><?php $this->_e('Realname (optional):') ?></legend>
                    <label for="xbox360voice-rl-name"> <?php $this->_e('Enter your real first name:') ?>
                    <input class="form_elem" size="30" type="text" value="<?php echo htmlentities(trim(stripslashes($this->rl_name))); ?>" name="xbox360voice-rl-name" id="xbox360voice-rl-name" />
                    </label>
                </fieldset>
                
                <fieldset class="options"><legend><?php $this->_e('Credits:') ?></legend>
                    <label for="xbox360voice-displaycredits"> <?php $this->_e('Display credits link?') ?>
                    <input class="form_elem" type="checkbox" <?php if($this->displaycredits=='true'){echo 'checked="checked"';} ?> value="true" name="xbox360voice-displaycredits" id="xbox360voice-displaycredits" />
                    </label>
                </fieldset>

                <fieldset class="options"><legend><?php $this->_e('Avatar:') ?></legend>
                    <label for="xbox360voice-displayavatar"> <?php $this->_e('Display avatar image?') ?>
                    <input class="form_elem" type="checkbox" <?php if($this->displayavatar=='true'){echo 'checked="checked"';} ?> value="true" name="xbox360voice-displayavatar" id="xbox360voice-displayavatar" />
                    </label>
                </fieldset>

                <fieldset class="options"><legend><?php $this->_e('Avatar Size:') ?></legend>
                    <label for="xbox360voice-avatarsize"> <?php $this->_e('Size of the avatar image:') ?>
                    <select class="form_elem" name="xbox360voice-avatarsize" id="xbox360voice-avatarsize">
                      <option <?php if(htmlentities(trim(stripslashes($this->avatarsize)))=="l"){echo 'selected="selected"';} ?> value="l"><?php $this->_e('large - 50px') ?></option>
                      <option <?php if(htmlentities(trim(stripslashes($this->avatarsize)))=="s"){echo 'selected="selected"';} ?> value="s"><?php $this->_e('small - 32px') ?></option>
                    </select>
                    </label>
                </fieldset>


                <p class="submit"><input type="submit" name="submit" value="<?php $this->_e('Save &raquo;') ?>" /></p>
            </form>
        </div>
<?php
    }
}
$xbox360voice_plugin = new XBOX360_Voice();
?>