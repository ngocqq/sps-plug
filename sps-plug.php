<?php
/*
Plugin Name: SPS PlUG
Plugin URI: http://sps.vn/
Description: Thay doi mot chut xiu Wordpress CMS. Phu hop voi tat ca website.
Version: 0.4
Author: ngocqq
*/

if ( ! defined( 'WPINC' ) ) {
  die;
}

define('SPS_PLUG_URL', untrailingslashit(plugin_dir_url(__FILE__)));
define('SPS_PLUG_DIR', untrailingslashit(plugin_dir_path(__FILE__)));

register_activation_hook( __FILE__, 'sps_slug_activate' );
register_deactivation_hook( __FILE__, 'sps_lugin_deactive' );

function sps_plug_init() {
	load_plugin_textdomain( 'sps-plug', false, dirname( plugin_basename( __FILE__ ) ) );
}
#add_action( 'plugins_loaded', 'sps_plug_init' );


add_action( 'deprecated_constructor_trigger_error', '__return_false' );

/*
 * set default option when plugin activate
 */
function sps_slug_activate() {
  add_option( 'sps_plug_login_header_logo', str_replace(home_url('/'), '', SPS_PLUG_URL).'/logo-re-b.png', false, false );
  add_option( 'sps_plug_login_header_url', 'http://sps.vn/' );
  add_option( 'sps_plug_login_header_title', 'SPS Development Website', false, false );

  add_option( 'sps_plug_link_manage', '', false, false );
  add_option( 'sps_plug_front_adminbar', '', false, false );
  add_option( 'sps_plug_maintenance_mode', '', false, false );
  add_option( 'sps_plug_turn_xmlrpc', '', false, false );
  add_option( 'sps_plug_maintenance_mode_message', 'Website is being maintained. Please come back later.' );

}

function sps_lugin_deactive() {
	delete_option( 'sps_plug_login_header_logo' );
}

if(!is_admin()):
// remove WP version from css
add_filter( 'style_loader_src', 'sps_plug_remove_wp_ver_css_js', 99 );
// remove Wp version from scripts
add_filter( 'script_loader_src', 'sps_plug_remove_wp_ver_css_js', 99 );
endif;

function sps_plug_remove_wp_ver_css_js($src) {
  if ( strpos( $src, 'ver=' ) )
    $src = remove_query_arg( 'ver', $src );
  return $src;
}

/*
 * custom login header url
 */
add_filter( 'login_headerurl', 'sps_plug_login_headerurl' );
function sps_plug_login_headerurl() {
  return get_option( 'sps_plug_login_header_url', '' );
}

/*
 * custom login header attribute title
 */
add_filter( 'login_headertitle', 'sps_plug_login_headertitle' );
function sps_plug_login_headertitle() {
  return get_option( 'sps_plug_login_header_title', '' );
}

/*
 * print login header style
 */
add_action( 'login_head', 'sps_plug_login_print_style' );
function sps_plug_login_print_style() {
  ?>
  <style type="text/css" media="screen">
    <?php
    $login_logo = get_option( 'sps_plug_login_header_logo', '' );
    if(''!=$login_logo){
    ?>
    .login h1 a {
      background-image: url(<?php echo home_url('/').$login_logo; ?>);
      -webkit-background-size: auto 84px;
      background-size: auto 84px;
      width: auto;
    }
    <?php } ?>
  </style>
  <?php
}

$front_end_adminbar = get_option( 'sps_plug_front_adminbar', '' );
if('hide'==$front_end_adminbar) {
  show_admin_bar( false );
}

$maintenance_mode = get_option( 'sps_plug_maintenance_mode' );
if('on'==$maintenance_mode) {
  if(!is_admin()) {
    add_action( 'template_redirect', 'sps_plug_do_maintenance', -1 );
    function sps_plug_do_maintenance() {
      if(!is_user_logged_in()) {
          sps_plug_do_maintenance_message();
      } else {
        $user = wp_get_current_user();
        if(!$user->allcaps['administrator']) {
          sps_plug_do_maintenance_message();
        }
      }
    }
  }
}

function sps_plug_do_maintenance_message() {
  $maintenance_mode_message = get_option( 'sps_plug_maintenance_mode_message' );
  if ( ! did_action( 'admin_head' ) ) :
    if ( !headers_sent() ) {
      status_header( 503 );
      nocache_headers();
      header( 'Content-Type: text/html; charset=utf-8' );
    }
    ?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="viewport" content="width=device-width">
      <?php
      if ( function_exists( 'wp_no_robots' ) ) {
        wp_no_robots();
      }
      ?>
      <title><?php bloginfo( 'name' ); ?>: Maintenance</title>
      <style type="text/css">
        * {
          padding: 0;
          margin: 0;
        }
        html {
          background: url(<?php echo SPS_PLUG_URL.'/imgs/slider_1_optimized.jpg'; ?>) center top no-repeat;
          height: 100%;
        }
        body {
          color: #fff;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
          margin: 2em auto;
          padding: 1em 2em;
          max-width: 700px;
        }
        h1 {
          border-bottom: 1px solid #dadada;
          clear: both;
          color: #666;
          font-size: 24px;
          margin: 30px 0 0 0;
          padding: 0;
          padding-bottom: 7px;
        }
        #error-page {
          margin-top: 50px;
        }
      </style>
    </head>
    <body id="error-page">
  <?php endif; // ! did_action( 'admin_head' ) ?>
      <h1><?php echo $maintenance_mode_message; ?></h1>
    </body>
    </html>
    <?php

  die();
}

$wp_generator = get_option( 'sps_plug_wp_generator', '' );
if('hide'==$wp_generator) {
  remove_action( 'wp_head', 'wp_generator' );
  remove_action( 'wp_head', 'wlwmanifest_link' );
}

$turn_xmlrpc = get_option('sps_plug_turn_xmlrpc', '');
if('off'==$turn_xmlrpc) {
  // Disable XML-RPC

  remove_action( 'wp_head', 'rsd_link' );
  remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );

  add_filter('xmlrpc_enabled', '__return_false');
}

/*
 * admin
 */
if(is_admin()):

  /*
   *  Ẩn chính nó đi
   */
  add_filter( 'all_plugins', 'sps_plug_plugins', 999, 1 );
  function sps_plug_plugins($plugins) {
    if(array_key_exists('sps-plug/sps-plug.php', $plugins)) {
      unset($plugins['sps-plug/sps-plug.php']);
    }
    return $plugins;
  }

  /*
   * remove admin bar wp logo
   */
  add_action( 'admin_bar_menu', 'sps_plug_remove_wp_logo', 999 );
  function sps_plug_remove_wp_logo( $wp_admin_bar ) {
    $wp_admin_bar->remove_node( 'wp-logo' );
  }

  if('on'==get_option( 'sps_plug_link_manage', '' ))
    add_filter( 'pre_option_link_manager_enabled', '__return_true' );

  /*
   * remove admin dashboard widgets
   */
  add_action('wp_dashboard_setup', 'sps_plug_remove_dashboard_widgets');
  function sps_plug_remove_dashboard_widgets(){
    // remove_meta_box('dashboard_right_now', 'dashboard', 'normal');   // Right Now
    // remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // Recent Comments
    // remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');  // Incoming Links
    // remove_meta_box('dashboard_plugins', 'dashboard', 'normal');   // Plugins
    // remove_meta_box('dashboard_quick_press', 'dashboard', 'side');  // Quick Press
    // remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');  // Recent Drafts
    remove_meta_box('dashboard_primary', 'dashboard', 'side');   // WordPress blog
    remove_meta_box('dashboard_secondary', 'dashboard', 'side');   // Other WordPress News
	// use 'dashboard-network' as the second parameter to remove widgets from a network dashboard.
  }

  /*
   * remove hooks
   */
  add_action( 'admin_init', 'sps_plug_hook_remove' );
  function sps_plug_hook_remove() {
    remove_action( 'welcome_panel', 'wp_welcome_panel' );
  }

  /*
   * custom admin footer text
   */
  add_filter( 'admin_footer_text', 'sps_plug_admin_footer_text', 999 );
  function sps_plug_admin_footer_text() {
    $text = 'Thank you for creating with <a href="http://sps.vn/">SPS</a> and Wordpress CMS.';
    return '<span id="footer-thankyou">' . $text . '</span>';
  }

  /*
   * print footer javascript
   */
  add_action( "admin_footer-dashboard_page_sps-plug/sps-plug", 'sps_plug_admin_print_footer_scripts' );
  function sps_plug_admin_print_footer_scripts() {
    ?>
    <script type="text/javascript">
      jQuery(function($) {
        /* user clicks button on custom field, runs below code that opens new window */
        $(document).on('click','#sps_plug_login_header_logo_upload',function() {

          /*Thickbox function aimed to show the media window. This function accepts three parameters:
          *
          * Name of the window: "In our case Upload a Image"
          * URL : Executes a WordPress library that handles and validates files.
          * ImageGroup : As we are not going to work with groups of images but just with one that why we set it false.
          */
          tb_show('Upload a Image', 'media-upload.php?referer=media_page&type=image&TB_iframe=true&post_id=0', false);
          return false;
        });
        // window.send_to_editor(html) is how WP would normally handle the received data. It will deliver image data in HTML format, so you can put them wherever you want.

        window.send_to_editor = function(html) {
          var image_url = $(html).attr('src');
          var home_url = '<?php echo home_url( '/' ); ?>';
          image_url = image_url.replace(home_url, '');
          $('#sps_plug_login_header_logo').val(image_url);
          tb_remove(); // calls the tb_remove() of the Thickbox plugin
          //$('#submit_button').trigger('click');
        }
      });
    </script>
    <?php
  }

  /*
   * sps plug admin setting media upload thickbox
   */
  add_action( 'admin_enqueue_scripts', 'sps_plug_admin_scripts' );
  function sps_plug_admin_scripts($hook) {

    if($hook=='dashboard_page_sps-plug/sps-plug') {
      wp_enqueue_script('media-upload'); //Provides all the functions needed to upload, validate and give format to files.
      wp_enqueue_script('thickbox'); //Responsible for managing the modal window.
      wp_enqueue_style('thickbox'); //Provides the styles needed for this window.
    }

  }

  /*
   * create admin menu link
   */
  add_action( 'admin_menu', 'sps_plug_create_admin_menu' );
  function sps_plug_create_admin_menu() {
    add_dashboard_page('SPS Plug Page', 'SPS Plug', 'manage_options', __FILE__, 'sps_plug_render_admin_page');
    add_action( 'admin_init', 'sps_plug_register_settings' );
  }

  /*
   * render admin page
   */
  function sps_plug_render_admin_page() {
    if ( !current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    ?>
    <div class="wrap">
      <h2>SPS Plug Settings</h2>
      <form method="post" action="options.php">
        <?php settings_fields( 'sps-plug' ); ?>
        <?php do_settings_sections( 'sps-plug' ); ?>

        <table class="form-table">
            <tr valign="top">
            <th scope="row">Login header logo</th>
            <td>
              <input type="text" id="sps_plug_login_header_logo" name="sps_plug_login_header_logo" value="<?php echo esc_attr( get_option('sps_plug_login_header_logo') ); ?>" />
              <input type="button" class="button" id="sps_plug_login_header_logo_upload" value="Upload logo">

              </td>
            </tr>

            <tr valign="top">
            <th scope="row">Login header URL</th>
            <td><input type="text" name="sps_plug_login_header_url" value="<?php echo esc_attr( esc_url(get_option('sps_plug_login_header_url')) ); ?>" /></td>
            </tr>

            <tr valign="top">
            <th scope="row">Login header title</th>
            <td><input type="text" name="sps_plug_login_header_title" value="<?php echo esc_attr( get_option('sps_plug_login_header_title') ); ?>" /></td>
            </tr>

            <tr valign="top">
            <th scope="row">Link manage</th>
            <td>
            <?php
            $link_manage = get_option('sps_plug_link_manage', '');
            ?>
              <select name="sps_plug_link_manage">
                <option value="on" <?php selected( $link_manage, 'on', true ); ?>>On</option>
                <option value="" <?php selected( $link_manage, '', true ); ?>>Off</option>
              </select>
            </tr>

            <tr valign="top">
            <th scope="row">Front-End adminbar</th>
            <td>
            <?php
            $front_end_adminbar = get_option('sps_plug_front_adminbar', '');
            ?>
              <select name="sps_plug_front_adminbar">
                <option value="" <?php selected( $front_end_adminbar, '', true ); ?>>Show</option>
                <option value="hide" <?php selected( $front_end_adminbar, 'hide', true ); ?>>Hide</option>
              </select>
            </tr>

            <tr valign="top">
            <th scope="row">Maintenance Mode</th>
            <td>
            <?php
            $maintenance_mode = get_option('sps_plug_maintenance_mode', '');
            ?>
              <select name="sps_plug_maintenance_mode">
                <option value="on" <?php selected( $maintenance_mode, 'on', true ); ?>>On</option>
                <option value="" <?php selected( $maintenance_mode, '', true ); ?>>Off</option>
              </select>
            </tr>

            <tr valign="top">
            <th scope="row">Maintenance Mode Message</th>
            <td><textarea name="sps_plug_maintenance_mode_message" rows="2" cols="40"><?php echo esc_textarea( get_option('sps_plug_maintenance_mode_message', '') ); ?></textarea></td>
            </tr>

            <tr valign="top">
            <th scope="row">WP generator meta tag?</th>
            <td>
            <?php
            $wp_generator = get_option('sps_plug_wp_generator', '');
            ?>
              <select name="sps_plug_wp_generator">
                <option value="" <?php selected( $wp_generator, '', true ); ?>>Show</option>
                <option value="hide" <?php selected( $wp_generator, 'hide', true ); ?>>Hide</option>
              </select>
            </tr>

            <tr valign="top">
            <th scope="row">Turn XML-RPC</th>
            <td>
            <?php
            $turn_xmlrpc = get_option('sps_plug_turn_xmlrpc', '');
            ?>
              <select name="sps_plug_turn_xmlrpc">
                <option value="" <?php selected( $turn_xmlrpc, '', true ); ?>>On</option>
                <option value="off" <?php selected( $turn_xmlrpc, 'off', true ); ?>>Off</option>
              </select>
            </tr>

        </table>

        <?php submit_button(); ?>

      </form>
    </div>
    <?php
  }
  /*
   * register options
   */
  function sps_plug_register_settings() {
    register_setting( 'sps-plug', 'sps_plug_login_header_logo', 'sanitize_text_field' );
    register_setting( 'sps-plug', 'sps_plug_login_header_url', 'esc_url' );
    register_setting( 'sps-plug', 'sps_plug_login_header_title', 'sanitize_text_field' );
    register_setting( 'sps-plug', 'sps_plug_link_manage', 'sanitize_text_field' );
    register_setting( 'sps-plug', 'sps_plug_front_adminbar', 'sanitize_text_field' );
    register_setting( 'sps-plug', 'sps_plug_maintenance_mode', 'sanitize_text_field' );
    register_setting( 'sps-plug', 'sps_plug_maintenance_mode_message', 'sanitize_text_field' );
    register_setting( 'sps-plug', 'sps_plug_wp_generator', 'sanitize_text_field' );
    register_setting( 'sps-plug', 'sps_plug_turn_xmlrpc', 'sanitize_text_field' );
  }

endif; // end if is admin
