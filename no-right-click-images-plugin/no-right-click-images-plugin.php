<?php
/*
Plugin Name: No Right Click Images
Description: Uses JavaScript to prevent right clicking on images to help keep leaches from copying images
Version: 4.0
Tested up to: 6.7
Author: WebFactory Ltd
Author URI: https://www.webfactoryltd.com/

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('NO_RIGHT_CLICK_IMAGES_URL', plugin_dir_url(__FILE__));
define('NO_RIGHT_CLICK_IMAGES_DIR', dirname(__FILE__));
define('NO_RIGHT_CLICK_IMAGES_OPTIONS', 'no_right_click_images_options');

require_once NO_RIGHT_CLICK_IMAGES_DIR . '/wf-flyout/wf-flyout.php';

class No_Right_Click_Images
{
  static $version;
  static $options;

  static function init()
  {
    self::$version = self::get_plugin_version();
    self::load_options();

    if (is_admin()) {
      new wf_flyout(__FILE__);

      add_action('admin_menu',  array(__CLASS__, 'admin_menu'));
      add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts'));
      add_action('admin_action_no_right_click_images_install_wp301', array(__CLASS__, 'install_wp301'));
      add_action('admin_action_no_right_click_images_install_wpcaptcha', array(__CLASS__, 'install_wpcaptcha'));
      add_filter('admin_footer_text', array(__CLASS__, 'admin_footer_text'));
      add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'settings_link'));
    } else {
      add_action('wp_enqueue_scripts', array(__CLASS__, 'frontend_scripts'));
    }
  } // init

  static function settings_link($links)
  {
    $settings_link = '<a href="options-general.php?page=no-right-click-images-plugin">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
  } // settings_link

  static function frontend_scripts()
  {
    $options = self::get_options();

    if (function_exists('is_user_logged_in')) { // for non wp versions
      if (is_user_logged_in()) {
        if (current_user_can('editor') || current_user_can('administrator')) {
          if ($options['admins'] == 1) {
            return;
          }
        }
        if ($options['allowforlogged'] == 1) {
          return;
        }
      }
    }

    $js_localize = array(
      'gesture' => $options['gesture'],
      'drag' => $options['drag'],
      'touch' => $options['touch'],
      'admin' => $options['admins']
    );

    wp_enqueue_script('no-right-click-images-admin', NO_RIGHT_CLICK_IMAGES_URL . 'js/no-right-click-images-frontend.js', array('jquery'), self::$version, true);
    wp_localize_script('no-right-click-images-admin', 'nrci_opts', $js_localize);

    if ($options['ios'] == 1) {
      wp_enqueue_style('no-right-click-images-frontend', NO_RIGHT_CLICK_IMAGES_URL . 'css/no-right-click-images-frontend.css', array(), self::$version);
    }
  }

  static function admin_enqueue_scripts($hook)
  {
    if ('settings_page_no-right-click-images-plugin' == $hook) {
      wp_enqueue_style('wp-jquery-ui-dialog');
      wp_enqueue_style('no-right-click-images-admin', NO_RIGHT_CLICK_IMAGES_URL . 'css/no-right-click-images.css', array(), self::$version);

      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script('jquery-ui-position');
      wp_enqueue_script('jquery-effects-core');
      wp_enqueue_script('jquery-effects-blind');
      wp_enqueue_script('jquery-ui-dialog');

      $js_localize = array(
        'wp301_install_url' => add_query_arg(array('action' => 'no_right_click_images_install_wp301', '_wpnonce' => wp_create_nonce('install_wp301'), 'rnd' => rand()), admin_url('admin.php')),
        'wpcaptcha_install_url' => add_query_arg(array('action' => 'no_right_click_images_install_wpcaptcha', '_wpnonce' => wp_create_nonce('install_wpcaptcha'), 'rnd' => rand()), admin_url('admin.php')),
        'site_url' => site_url()
      );

      wp_enqueue_script('no-right-click-images-admin', NO_RIGHT_CLICK_IMAGES_URL . 'js/no-right-click-images.js', array('jquery'), self::$version, true);
      wp_localize_script('no-right-click-images-admin', 'no_right_click_images_vars', $js_localize);
    }
  } // admin_enqueue_scripts

  static function is_plugin_page()
  {
    $current_screen = get_current_screen();

    if ($current_screen->id == 'settings_page_no-right-click-images-plugin') {
      return true;
    } else {
      return false;
    }
  } // is_plugin_page

  static function admin_footer_text($text)
  {
    if (!self::is_plugin_page()) {
      return $text;
    }

    $text = '<i class="no-right-click-images-footer">No Right Click Images v' . self::$version . ' <a href="https://www.webfactoryltd.com/" title="Visit No Right Click Images page for more info" target="_blank">WebFactory Ltd</a>. Please <a target="_blank" href="https://wordpress.org/support/plugin/no-right-click-images-plugin/reviews/#new-post" title="Rate the plugin">rate the plugin <span>★★★★★</span></a> to help us spread the word. Thank you 🙌 from the WebFactory team!</i>';

    return $text;
  } // admin_footer_text

  static function load_options()
  {
    $options = get_option(NO_RIGHT_CLICK_IMAGES_OPTIONS, array());
    $change = false;

    if (!isset($options['meta'])) {
      $options['meta'] = array('first_version' => self::$version, 'first_install' => current_time('timestamp', true));
      $change = true;
    }
    if (!isset($options['dismissed_notices'])) {
      $options['dismissed_notices'] = array();
      $change = true;
    }

    if (!isset($options['options'])) {
      $options['options'] = array();


      $old_options = get_option('kpg_no_right_click_image');

      if (!is_array($old_options)) {
        $old_options = array();
      }

      $options['options']['gesture'] = isset($old_options['gesture']) && $old_options['gesture'] == 'Y' ? 1 : 0;
      $options['options']['drag'] = isset($old_options['drag']) && $old_options['drag'] == 'Y' ? 1 : 0;
      $options['options']['touch'] = isset($old_options['touch']) && $old_options['touch'] == 'Y' ? 1 : 0;
      $options['options']['allowforlogged'] = isset($old_options['allowforlogged']) && $old_options['allowforlogged'] == 'Y' ? 1 : 0;
      $options['options']['ios'] = isset($old_options['ios']) && $old_options['ios'] == 'Y' ? 1 : 0;
      $options['options']['admins'] = isset($old_options['admin']) && $old_options['admin'] == 'Y' ? 1 : 0;

      $change = true;
    }

    if (isset($_POST['submit']) && isset($_POST['norightclickimages_update_admin_options_nonce'])) {
      if (!wp_verify_nonce($_POST['norightclickimages_update_admin_options_nonce'], 'norightclickimages_update_admin_options')) {
        echo '<div id="message" class="updated fade">
                    <p><strong>' . esc_html__('Sorry, your nonce did not verify.', 'no-right-click-images') . '</strong></p>
                </div>';
      } else {
        $options['options']['gesture'] = isset($_POST['gesture']) && intval($_POST['gesture']) === 1 ? 1 : 0;
        $options['options']['drag'] = isset($_POST['drag']) && intval($_POST['drag']) === 1 ? 1 : 0;
        $options['options']['touch'] = isset($_POST['touch']) && intval($_POST['touch']) === 1 ? 1 : 0;
        $options['options']['allowforlogged'] = isset($_POST['allowforlogged']) && intval($_POST['allowforlogged']) === 1 ? 1 : 0;
        $options['options']['ios'] = isset($_POST['ios']) && intval($_POST['ios']) === 1 ? 1 : 0;
        $options['options']['admins'] = isset($_POST['admins']) && intval($_POST['admins']) === 1 ? 1 : 0;
        $change = true;


        echo '<div id="message" class="updated fade">
                    <p><strong>' . esc_html__('Options saved.', 'no-right-click-images') . '</strong></p>
                </div>';
      }
    }


    if ($change) {
      update_option(NO_RIGHT_CLICK_IMAGES_OPTIONS, $options, true);
    }

    self::$options = $options;
    return $options['options'];
  } // load_options

  static function get_options()
  {
    return self::$options['options'];
  } // get_options

  static function update_options($key, $data)
  {
    if (false === in_array($key, array('meta', 'dismissed_notices', 'options'))) {
      user_error('Unknown options key.', E_USER_ERROR);
      return false;
    }

    self::$options[$key] = $data;
    $tmp = update_option(NO_RIGHT_CLICK_IMAGES_OPTIONS, self::$options);

    return $tmp;
  } // update_options

  static function get_plugin_version()
  {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');

    return $plugin_data['version'];
  } // get_plugin_version

  static function admin_menu()
  {
    add_options_page(
      esc_html__('No Right Click Images'),
      esc_html__('No Right Click Images'),
      'manage_options',
      'no-right-click-images-plugin',
      array(__CLASS__, 'options_page')
    );
  } // admin_menu

  static function create_toggle_switch($name, $options = array(), $output = true)
  {
    $default_options = array('value' => '1', 'saved_value' => '', 'option_key' => $name);
    $options = array_merge($default_options, $options);

    $out = "\n";
    $out .= '<div class="toggle-wrapper">';
    $out .= '<input type="checkbox" id="' . $name . '" ' . self::checked($options['value'], $options['saved_value']) . ' type="checkbox" value="' . $options['value'] . '" name="' . $options['option_key'] . '">';
    $out .= '<label for="' . $name . '" class="toggle"><span class="toggle_handler"></span></label>';
    $out .= '</div>';

    if ($output) {
      self::wp_kses_wf($out);
    } else {
      return $out;
    }
  } // create_toggle_switch

  static function checked($value, $current, $echo = false)
  {
    $out = '';

    if (!is_array($current)) {
      $current = (array) $current;
    }

    if (in_array($value, $current)) {
      $out = ' checked="checked" ';
    }

    if ($echo) {
      self::wp_kses_wf($out);
    } else {
      return $out;
    }
  } // checked

  static function options_page()
  {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
    }

    $options = self::get_options();

    echo '<div class="wrap">';

    echo '<h1><img src="' . esc_url(NO_RIGHT_CLICK_IMAGES_URL . '/images/no-right-click-images-logo.png') . '" alt="Login Lockdown" title="Login Lockdown" style="width: 48px; vertical-align: middle;"> No Right Click Images</h1>';
    echo '<div id="no_right_click_images_settings">';

    echo '<form action="" method="POST">';
    wp_nonce_field('norightclickimages_update_admin_options', 'norightclickimages_update_admin_options_nonce');
    echo '<table class="form-table">';

    echo '<tr>';
    echo '<td><label for="allowforlogged">Allow Right Click for Logged Users:</label></td>';
    echo '<td>';
    self::create_toggle_switch('allowforlogged', array('saved_value' => $options['allowforlogged'], 'option_key' => 'allowforlogged'));
    echo '<p>You may wish to allow logged in users to copy images. You can do this by checking this box.</p></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><label for="drag">Disable Dragging of images:</label></td>';
    echo '<td>';
    self::create_toggle_switch('drag', array('saved_value' => $options['drag'], 'option_key' => 'drag'));
    echo '<p>This will prevent images from being dragged to the desktop or image software.</p></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><label for="touch">Disable Touch events:</label></td>';
    echo '<td>';
    self::create_toggle_switch('touch', array('saved_value' => $options['touch'], 'option_key' => 'touch'));
    echo '<p>Prevents touch events on images, but if images are used as links or buttons this may cause problems.</p></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><label for="gesture">Disable Gesture events:</label></td>';
    echo '<td>';
    self::create_toggle_switch('gesture', array('saved_value' => $options['gesture'], 'option_key' => 'gesture'));
    echo '<p>Prevents some gestures. If you site uses image gestures for images this may cause problems.</p></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><label for="ios">Disable context menu on Apple devices:</label></td>';
    echo '<td>';
    self::create_toggle_switch('ios', array('saved_value' => $options['ios'], 'option_key' => 'ios'));
    echo '<p>Adds a style to images on Apple IOS devices to prevent the context menu.</p></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><label for="admins">Admin can always right click images:</label></td>';
    echo '<td>';
    self::create_toggle_switch('admins', array('saved_value' => $options['admins'], 'option_key' => 'admins'));
    echo '<p>Admins can always right click images even if logged in users cannot.</p></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td colspan="2">';
    submit_button();
    echo '</td>';
    echo '</tr>';

    echo '</table>';
    echo '</form>';

    echo '</div>';

    echo '<div id="no_right_click_images_sidebar">';

    if (!defined('WPCAPTCHA_PLUGIN_FILE')) {
      echo '<div class="sidebar-box pro-ad-box box-wpcaptcha">
                    <h3 class="textcenter"><b>With WP Captcha you can easily add Google reCAPTCHA to WordPress comment form, login form and other forms.<br><br><u>Safeguard your WordPress site from spam comments and brute force attacks!</u></b></h3>

                    <p class="text-center"><a href="#" class="install-wpcaptcha">
                    <img src="' . esc_url(NO_RIGHT_CLICK_IMAGES_URL . '/images/wp-captcha-logo.png') . '" alt="WP Recaptcha" title="WP Recaptcha"></a></p>

                    <p class="text-center"><a href="#" class="button button-buy install-wpcaptcha">Install and activate the <u>free</u> WP Recaptcha plugin</a></p>

                    <p><a href="https://wordpress.org/plugins/advanced-google-recaptcha/" target="_blank">WP Recaptcha</a> is a free WP plugin maintained by the same team as this No Right Click Images plugin. It has <b>+100,000 users, 5-star rating</b>, and is hosted on the official WP repository.</p>
                    </div>';
    }

    if (!defined('EPS_REDIRECT_VERSION') && !defined('WF301_PLUGIN_FILE')) {
      echo '<div class="sidebar-box pro-ad-box box-301">
                    <h3 class="textcenter"><b>Problems with redirects?<br>Moving content around or changing posts\' URL?<br>Old URLs giving you problems?<br><br><u>Improve your SEO &amp; manage all redirects in one place!</u></b></h3>

                    <p class="text-center"><a href="#" class="install-wp301">
                    <img src="' . esc_url(NO_RIGHT_CLICK_IMAGES_URL . '/images/wp-301-logo.png') . '" alt="WP 301 Redirects" title="WP 301 Redirects"></a></p>

                    <p class="text-center"><a href="#" class="button button-buy install-wp301">Install and activate the <u>free</u> WP 301 Redirects plugin</a></p>

                    <p><a href="https://wordpress.org/plugins/eps-301-redirects/" target="_blank">WP 301 Redirects</a> is a free WP plugin maintained by the same team as this No Right Click Images plugin. It has <b>+250,000 users, 5-star rating</b>, and is hosted on the official WP repository.</p>
                    </div>';
    }

    echo '<div class="sidebar-box" style="margin-top: 35px; margin-bottom: 35px;">
            <p>Need support? Post your problem on the <a href="https://wordpress.org/support/plugin/no-right-click-images-plugin/" target="_blank">official forum</a> and we\'ll get back to you ASAP.</p>
                    <p>Please <a href="https://wordpress.org/support/plugin/no-right-click-images-plugin/reviews/#new-post" target="_blank">rate the plugin ★★★★★</a> to <b>keep it up-to-date &amp; maintained</b>. It only takes a second to rate. Thank you! 👋</p>
                    </div>';
    echo '</div>';

    echo '</div>';
  } // options_page

  static function install_wp301()
  {
    check_ajax_referer('install_wp301');

    if (false === current_user_can('administrator')) {
      wp_die('Sorry, you have to be an admin to run this action.');
    }

    $plugin_slug = 'eps-301-redirects/eps-301-redirects.php';
    $plugin_zip = 'https://downloads.wordpress.org/plugin/eps-301-redirects.latest-stable.zip';

    @include_once ABSPATH . 'wp-admin/includes/plugin.php';
    @include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    @include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    @include_once ABSPATH . 'wp-admin/includes/file.php';
    @include_once ABSPATH . 'wp-admin/includes/misc.php';
    echo '<style>
		body{
			font-family: sans-serif;
			font-size: 14px;
			line-height: 1.5;
			color: #444;
		}
		</style>';

    echo '<div style="margin: 20px; color:#444;">';
    echo 'If things are not done in a minute <a target="_parent" href="' . esc_url(admin_url('plugin-install.php?s=301%20redirects%20webfactory&tab=search&type=term')) . '">install the plugin manually via Plugins page</a><br><br>';
    echo 'Starting ...<br><br>';

    wp_cache_flush();
    $upgrader = new Plugin_Upgrader();
    echo 'Check if WP 301 Redirects is already installed ... <br />';
    if (self::is_plugin_installed($plugin_slug)) {
      echo 'WP 301 Redirects is already installed! <br /><br />Making sure it\'s the latest version.<br />';
      $upgrader->upgrade($plugin_slug);
      $installed = true;
    } else {
      echo 'Installing WP 301 Redirects.<br />';
      $installed = $upgrader->install($plugin_zip);
    }
    wp_cache_flush();

    if (!is_wp_error($installed) && $installed) {
      echo 'Activating WP 301 Redirects.<br />';
      $activate = activate_plugin($plugin_slug);

      if (is_null($activate)) {
        echo 'WP 301 Redirects Activated.<br />';

        echo '<script>setTimeout(function() { top.location = "' . esc_url(admin_url('options-general.php?page=eps_redirects')) . '"; }, 1000);</script>';
        echo '<br>If you are not redirected in a few seconds - <a href="' . esc_url(admin_url('options-general.php?page=eps_redirects')) . '" target="_parent">click here</a>.';
      }
    } else {
      echo 'Could not install WP 301 Redirects. You\'ll have to <a target="_parent" href="' . esc_url(admin_url('plugin-install.php?s=301%20redirects%20webfactory&tab=search&type=term')) . '">download and install manually</a>.';
    }

    echo '</div>';
  } // install_wp301

  static function install_wpcaptcha()
  {
    check_ajax_referer('install_wpcaptcha');

    if (false === current_user_can('administrator')) {
      wp_die('Sorry, you have to be an admin to run this action.');
    }

    $plugin_slug = 'advanced-google-recaptcha/advanced-google-recaptcha.php';
    $plugin_zip = 'https://downloads.wordpress.org/plugin/advanced-google-recaptcha.latest-stable.zip';

    @include_once ABSPATH . 'wp-admin/includes/plugin.php';
    @include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    @include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    @include_once ABSPATH . 'wp-admin/includes/file.php';
    @include_once ABSPATH . 'wp-admin/includes/misc.php';
    echo '<style>
		body{
			font-family: sans-serif;
			font-size: 14px;
			line-height: 1.5;
			color: #444;
		}
		</style>';

    echo '<div style="margin: 20px; color:#444;">';
    echo 'If things are not done in a minute <a target="_parent" href="' . esc_url(admin_url('plugin-install.php?s=advanced%20google%20recaptcha%20webfactory&tab=search&type=term')) . '">install the plugin manually via Plugins page</a><br><br>';
    echo 'Starting ...<br><br>';

    wp_cache_flush();
    $upgrader = new Plugin_Upgrader();
    echo 'Check if WP Captcha is already installed ... <br />';
    if (self::is_plugin_installed($plugin_slug)) {
      echo 'WP Captcha is already installed! <br /><br />Making sure it\'s the latest version.<br />';
      $upgrader->upgrade($plugin_slug);
      $installed = true;
    } else {
      echo 'Installing WP Captcha.<br />';
      $installed = $upgrader->install($plugin_zip);
    }
    wp_cache_flush();

    if (!is_wp_error($installed) && $installed) {
      echo 'Activating WP Captcha.<br />';
      $activate = activate_plugin($plugin_slug);

      if (is_null($activate)) {
        echo 'WP Captcha Activated.<br />';

        echo '<script>setTimeout(function() { top.location = "' . esc_url(admin_url('options-general.php?page=wpcaptcha')) . '"; }, 1000);</script>';
        echo '<br>If you are not redirected in a few seconds - <a href="' . esc_url(admin_url('options-general.php?page=wpcaptcha')) . '" target="_parent">click here</a>.';
      }
    } else {
      echo 'Could not install WP Captcha. You\'ll have to <a target="_parent" href="' . esc_url(admin_url('plugin-install.php?s=advanced%20google%20recaptcha%20webfactory&tab=search&type=term')) . '">download and install manually</a>.';
    }

    echo '</div>';
  } // install_wp301

  static function is_plugin_installed($slug)
  {
    if (!function_exists('get_plugins')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();

    if (!empty($all_plugins[$slug])) {
      return true;
    } else {
      return false;
    }
  } // is_plugin_installed

  static function wp_kses_wf($html)
  {
    add_filter('safe_style_css', function ($styles) {
      $styles_wf = array(
        'text-align',
        'margin',
        'color',
        'float',
        'border',
        'background',
        'background-color',
        'border-bottom',
        'border-bottom-color',
        'border-bottom-style',
        'border-bottom-width',
        'border-collapse',
        'border-color',
        'border-left',
        'border-left-color',
        'border-left-style',
        'border-left-width',
        'border-right',
        'border-right-color',
        'border-right-style',
        'border-right-width',
        'border-spacing',
        'border-style',
        'border-top',
        'border-top-color',
        'border-top-style',
        'border-top-width',
        'border-width',
        'caption-side',
        'clear',
        'cursor',
        'direction',
        'font',
        'font-family',
        'font-size',
        'font-style',
        'font-variant',
        'font-weight',
        'height',
        'letter-spacing',
        'line-height',
        'margin-bottom',
        'margin-left',
        'margin-right',
        'margin-top',
        'overflow',
        'padding',
        'padding-bottom',
        'padding-left',
        'padding-right',
        'padding-top',
        'text-decoration',
        'text-indent',
        'vertical-align',
        'width',
        'display',
      );

      foreach ($styles_wf as $style_wf) {
        $styles[] = $style_wf;
      }
      return $styles;
    });

    $allowed_tags = wp_kses_allowed_html('post');
    $allowed_tags['input'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'data-*' => true,
      'size' => true,
      'disabled' => true
    );

    $allowed_tags['textarea'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'data-*' => true,
      'cols' => true,
      'rows' => true,
      'disabled' => true,
      'autocomplete' => true
    );

    $allowed_tags['select'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'data-*' => true,
      'multiple' => true,
      'disabled' => true
    );

    $allowed_tags['option'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'selected' => true,
      'data-*' => true
    );

    $allowed_tags['optgroup'] = array(
      'type' => true,
      'style' => true,
      'class' => true,
      'id' => true,
      'checked' => true,
      'disabled' => true,
      'name' => true,
      'size' => true,
      'placeholder' => true,
      'value' => true,
      'selected' => true,
      'data-*' => true,
      'label' => true
    );

    $allowed_tags['a'] = array(
      'href' => true,
      'data-*' => true,
      'class' => true,
      'style' => true,
      'id' => true,
      'target' => true,
      'data-*' => true,
      'role' => true,
      'aria-controls' => true,
      'aria-selected' => true,
      'disabled' => true
    );

    $allowed_tags['div'] = array(
      'style' => true,
      'class' => true,
      'id' => true,
      'data-*' => true,
      'role' => true,
      'aria-labelledby' => true,
      'value' => true,
      'aria-modal' => true,
      'tabindex' => true
    );

    $allowed_tags['li'] = array(
      'style' => true,
      'class' => true,
      'id' => true,
      'data-*' => true,
      'role' => true,
      'aria-labelledby' => true,
      'value' => true,
      'aria-modal' => true,
      'tabindex' => true
    );

    $allowed_tags['span'] = array(
      'style' => true,
      'class' => true,
      'id' => true,
      'data-*' => true,
      'aria-hidden' => true
    );

    $allowed_tags['style'] = array(
      'class' => true,
      'id' => true,
      'type' => true,
      'style' => true
    );

    $allowed_tags['fieldset'] = array(
      'class' => true,
      'id' => true,
      'type' => true,
      'style' => true
    );

    $allowed_tags['link'] = array(
      'class' => true,
      'id' => true,
      'type' => true,
      'rel' => true,
      'href' => true,
      'media' => true,
      'style' => true
    );

    $allowed_tags['form'] = array(
      'style' => true,
      'class' => true,
      'id' => true,
      'method' => true,
      'action' => true,
      'data-*' => true,
      'style' => true
    );

    $allowed_tags['script'] = array(
      'class' => true,
      'id' => true,
      'type' => true,
      'src' => true,
      'style' => true
    );

    $allowed_tags['table'] = array(
      'class' => true,
      'id' => true,
      'type' => true,
      'cellpadding' => true,
      'cellspacing' => true,
      'border' => true,
      'style' => true
    );

    $allowed_tags['canvas'] = array(
      'class' => true,
      'id' => true,
      'style' => true
    );

    echo wp_kses($html, $allowed_tags);

    add_filter('safe_style_css', function ($styles) {
      $styles_wf = array(
        'text-align',
        'margin',
        'color',
        'float',
        'border',
        'background',
        'background-color',
        'border-bottom',
        'border-bottom-color',
        'border-bottom-style',
        'border-bottom-width',
        'border-collapse',
        'border-color',
        'border-left',
        'border-left-color',
        'border-left-style',
        'border-left-width',
        'border-right',
        'border-right-color',
        'border-right-style',
        'border-right-width',
        'border-spacing',
        'border-style',
        'border-top',
        'border-top-color',
        'border-top-style',
        'border-top-width',
        'border-width',
        'caption-side',
        'clear',
        'cursor',
        'direction',
        'font',
        'font-family',
        'font-size',
        'font-style',
        'font-variant',
        'font-weight',
        'height',
        'letter-spacing',
        'line-height',
        'margin-bottom',
        'margin-left',
        'margin-right',
        'margin-top',
        'overflow',
        'padding',
        'padding-bottom',
        'padding-left',
        'padding-right',
        'padding-top',
        'text-decoration',
        'text-indent',
        'vertical-align',
        'width'
      );

      foreach ($styles_wf as $style_wf) {
        if (($key = array_search($style_wf, $styles)) !== false) {
          unset($styles[$key]);
        }
      }
      return $styles;
    });
  } // wp_kses_wf
} // class No_Right_Click_Images


add_action('init', array('No_Right_Click_Images', 'init'));
