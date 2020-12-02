<?php
/**
 * Script Class
 *
 * Handles the script and style functionality of plugin
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Wmpci_Script {
	
	function __construct() {
		
		// Action to add style on frontend
		add_action( 'wp_enqueue_scripts', array($this, 'wmpci_front_end_style') );

		// Action to add script on frontend
		add_action( 'wp_enqueue_scripts', array($this, 'wmpci_front_end_script'), 15 );

		// Action to add style in backend
		add_action( 'admin_enqueue_scripts', array($this, 'wmpci_admin_style') );

		// Action to add script in backend
		add_action( 'admin_enqueue_scripts', array($this, 'wmpci_admin_script') );
	}
	
	/**
	 * Enqueue front end styles
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0
	 */
	function wmpci_front_end_style(){

		// Registring Public Style
		wp_register_style( 'wmpci-public-style', WMPCI_URL.'assets/css/wmpci-public.css', null, WMPCI_VERSION );
		wp_enqueue_style('wmpci-public-style');
	}

	/**
	 * Enqueue front script
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0
	 */
	function wmpci_front_end_script(){
		
		global $wmpci_options;

		// Registring Popup script
		wp_register_script( 'wmpci-popup-js', WMPCI_URL.'assets/js/wmpci-popup.js', array('jquery'), WMPCI_VERSION, true );
		wp_localize_script( 'wmpci-popup-js', 'Wmpci_Popup', array(
																	'enable'		=> $wmpci_options['enable_popup'],
																	'delay'			=> $wmpci_options['wmpci_popup_delay'],
																	'exp_time'		=> $wmpci_options['wmpci_popup_exp'],
																	'close_on_esc'	=> (!empty($wmpci_options['close_on_esc'])) ? true : false,
																	'hide_time'		=> $wmpci_options['wmpci_popup_disappear'],
																));
		wp_enqueue_script('wmpci-popup-js');
	}

	/**
	 * Enqueue admin styles
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0
	 */
	function wmpci_admin_style( $hook ) {

		// Pages array
		$pages_array = array( 'toplevel_page_wmpci-settings' );

		// If page is plugin setting page then enqueue script
		if( in_array($hook, $pages_array) ) {

			// Enqueu built in style for color picker
			if( wp_style_is( 'wp-color-picker', 'registered' ) ) { // Since WordPress 3.5
				wp_enqueue_style( 'wp-color-picker' );
			} else {
				wp_enqueue_style( 'farbtastic' );
			}
		}
	}

	/**
	 * Enqueue admin script
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0
	 */
	function wmpci_admin_script( $hook ) {

		global $wp_version;

		$new_ui = $wp_version >= '3.5' ? '1' : '0'; // Check wordpress version for older scripts

		// Pages array
		$pages_array = array( 'toplevel_page_wmpci-settings' );

		// If page is plugin setting page then enqueue script
		if( in_array($hook, $pages_array) ) {

			// Enqueu built-in script for color picker
			if( wp_script_is( 'wp-color-picker', 'registered' ) ) { // Since WordPress 3.5
				wp_enqueue_script( 'wp-color-picker' );
			} else {
				wp_enqueue_script( 'farbtastic' );
			}

			// Registring admin script
			wp_register_script( 'wmpci-admin-js', WMPCI_URL.'assets/js/wmpci-admin.js', array('jquery'), WMPCI_VERSION, true );
			wp_localize_script( 'wmpci-admin-js', 'Wmpci_Admin', array(
																	'new_ui' =>	$new_ui
																));
			wp_enqueue_script( 'wmpci-admin-js' );
		}
	}
}

$wmpci_script = new Wmpci_Script();