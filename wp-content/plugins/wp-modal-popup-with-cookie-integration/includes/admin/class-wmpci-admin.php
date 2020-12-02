<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Wmpci_Admin {
	
	function __construct() {

		// Action to register admin menu
		add_action( 'admin_menu', array($this, 'wmpci_register_menu') );

		// Action to register plugin settings
		add_action ( 'admin_init', array($this,'wmpci_register_settings') );
	}

	/**
	 * Function to register admin menus
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0
	 */
	function wmpci_register_menu() {
		add_menu_page ( __('WP PopUp', 'wp-modal-popup-with-cookie-integration'), __('WP PopUp', 'wp-modal-popup-with-cookie-integration'), 'manage_options', 'wmpci-settings', array($this, 'wmpci_settings_page'), 'dashicons-feedback' );
		}

	/**
	 * Function to handle the setting page html
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0
	 */
	function wmpci_settings_page() {
		include_once( WMPCI_DIR . '/includes/admin/form/wmpci-settings.php' );
	}


	/**
	 * Function register setings
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0
	 */
	function wmpci_register_settings(){
		register_setting( 'wmpci_plugin_options', 'wmpci_options', array($this, 'wmpci_validate_options') );

		// If plugin notice is dismissed
	    if( isset($_GET['message']) && $_GET['message'] == 'wmpci-plugin-notice' ) {
	    	set_transient( 'wmpci_install_notice', true, 604800 );
	    }		
	    
	}

	/**
	 * Validate Settings Options
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0
	 */
	function wmpci_validate_options( $input ) {

		$input['wmpci_popup_cnt']		= isset($input['wmpci_popup_cnt']) ? wmpci_slashes_deep( $input['wmpci_popup_cnt'], true ) : '';
		$input['wmpci_mainheading'] 	= isset($input['wmpci_mainheading']) 	? trim($input['wmpci_mainheading']) 		: '';
		$input['wmpci_subheading']   	= isset($input['wmpci_subheading']) 	? trim($input['wmpci_subheading']) 		: '';
		$input['wmpci_popup_delay']		= (is_numeric($input['wmpci_popup_delay'])) 	? trim($input['wmpci_popup_delay']) 	: 0;
		$input['wmpci_popup_exp']		= (is_numeric($input['wmpci_popup_exp'])) 		? trim($input['wmpci_popup_exp']) 		: 0;
		$input['wmpci_popup_disappear']	= (is_numeric($input['wmpci_popup_disappear'])) ? trim($input['wmpci_popup_disappear']) : 0;
		$input['close_on_esc'] 			= isset($input['close_on_esc']) 	? 1 : 0;
		$input['hide_close_btn'] 		= isset($input['hide_close_btn']) 	? 1 : 0;
		$input['enable_popup'] 			= isset($input['enable_popup']) 	? 1 : 0;
		$input['popup_height'] 			= isset($input['popup_height']) 	? trim($input['popup_height']) 		: '';
		$input['popup_width'] 			= isset($input['popup_width']) 		? trim($input['popup_width']) 		: '';
		$input['popup_bgcolor'] 		= isset($input['popup_bgcolor'])	? trim($input['popup_bgcolor']) 	: '';
		$input['popup_fontcolor'] 		= isset($input['popup_fontcolor'])	? trim($input['popup_fontcolor']) 	: '';
		$input['popup_border_width']	= (is_numeric($input['popup_border_width'])) ? trim($input['popup_border_width']) : '';
		$input['popup_border_color']	= (isset($input['popup_border_color'])) ? trim($input['popup_border_color']) : '';
		$input['popup_border_radius']	= (is_numeric($input['popup_border_radius'])) ? trim($input['popup_border_radius']) : '';
		$input['popup_design']			= isset($input['popup_design']) ? trim($input['popup_design']) : '';
		
		return $input;
	}
}

$wmpci_admin = new Wmpci_Admin();