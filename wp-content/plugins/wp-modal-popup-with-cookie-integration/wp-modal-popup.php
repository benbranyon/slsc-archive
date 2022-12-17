<?php
/**
 * Plugin Name: WP Modal Popup with Cookie Integration
 * Plugin URI: https://astoundify.com/products/modalify/
 * Description: Show Popup on your blog with desired content.
 * Text Domain: wp-modal-popup-with-cookie-integration
 * Domain Path: /languages/
 * Author: Astoundify
 * Version: 2.4
 * Author URI: https://astoundify.com/
 *
 * @package WordPress
 * @author Astoundify
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
if( !defined( 'WMPCI_VERSION' ) ) {
	define( 'WMPCI_VERSION', '2.4' );	// Version of plugin
}
if( !defined( 'WMPCI_DIR' ) ) {
	define( 'WMPCI_DIR', dirname( __FILE__ ) );	// Plugin dir
}
if( !defined( 'WMPCI_URL' ) ) {
	define( 'WMPCI_URL', plugin_dir_url( __FILE__ ) );	// Plugin url
}
if( !defined( 'WMPCI_POPUP_POST_TYPE' ) ) {
	define( 'WMPCI_POPUP_POST_TYPE', 'wpo_popup' );	// Plugin meta prefix
}
if( !defined( 'WMPCI_META_PREFIX' ) ) {
	define( 'WMPCI_META_PREFIX', '_wmpci_' );	// Plugin meta prefix
}

/**
 * Load Text Domain
 * This gets the plugin ready for translation
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
function wmpci_load_textdomain() {
	load_plugin_textdomain( 'wp-modal-popup-with-cookie-integration', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
}

// Action to load plugin text domain
add_action('plugins_loaded', 'wmpci_load_textdomain');

/**
 * Activation Hook
 *
 * Register plugin activation hook.
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'wmpci_install' );

/**
 * Deactivation Hook
 *
 * Register plugin deactivation hook.
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
register_deactivation_hook( __FILE__, 'wmpci_uninstall');

/**
 * Plugin Activation Function
 * Does the initial setup, sets the default values for the plugin options
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
function wmpci_install(){

	// Get settings for the plugin
	$wmpci_options = get_option( 'wmpci_options' );

	if( empty( $wmpci_options ) ) { // Check plugin version option

		// set default settings
		wmpci_default_settings();

		// Update plugin version to option
		update_option( 'wmpci_plugin_version', '1.3' );
	}

	if( is_plugin_active('modal-popup-with-cookie-pro/modal-popup-with-cookie-pro.php') ){
		 add_action('update_option_active_plugins', 'deactivate_wmpci_version');
	}


}


function deactivate_wmpci_version() {
   deactivate_plugins('modal-popup-with-cookie-pro/modal-popup-with-cookie-pro.php',true);
}

function wmpci_admin_notice() {

	global $pagenow;

	$dir 				= WP_PLUGIN_DIR . '/modal-popup-with-cookie-pro/modal-popup-with-cookie-pro.php';
	$notice_link        = add_query_arg( array('message' => 'wmpci-plugin-notice'), admin_url('plugins.php') );
	$notice_transient   = get_transient( 'wmpci_install_notice' );

	if( $notice_transient == false && $pagenow == 'plugins.php' && file_exists($dir) && current_user_can( 'install_plugins' ) ) {
		echo '<div class="updated notice" style="position:relative;">
			<p>
				<strong>'.sprintf( __('Thank you for activating %s', 'wp-modal-popup-with-cookie-integration'), 'WP Modal Popup with Cookie Integration').'</strong>.<br/>
				'.sprintf( __('It looks like you had PRO version %s of this plugin activated. To avoid conflicts the extra version has been deactivated and we recommend you delete it.', 'wp-modal-popup-with-cookie-integration'), '<strong>(<em>WP Modal Popup with Cookie Integration PRO</em>)</strong>' ).'
			</p>
			<a href="'.esc_url( $notice_link ).'" class="notice-dismiss" style="text-decoration:none;"></a>
		</div>';
	}
}

add_action( 'admin_notices', 'wmpci_admin_notice');


/**
 * Plugin Deactivation Function
 * Delete  plugin options
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
function wmpci_uninstall(){
}

// Global Variables
global $wmpci_options;

// Function File
require_once( WMPCI_DIR . '/includes/wmpci-functions.php' );
$wmpci_options = wmpci_get_settings();

// Script Class
require_once( WMPCI_DIR . '/includes/class-wmpci-script.php' );

// Admin Class
require_once( WMPCI_DIR . '/includes/admin/class-wmpci-admin.php' );

// Public Class
require_once( WMPCI_DIR . '/includes/class-wmpci-public.php' );
