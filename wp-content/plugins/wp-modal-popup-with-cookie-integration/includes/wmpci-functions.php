<?php
/**
 * Functions File
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Update default settings
 * 
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
function wmpci_default_settings(){
	
	global $wmpci_options;
	
	$wmpci_options = array(
								'enable_popup'			=> '0',
								'wmpci_popup_cnt' 		=>	'',
								'wmpci_popup_delay'		=> '1',
								'wmpci_popup_exp'		=> '',
								'close_on_esc'			=> '1',
								'hide_close_btn'		=> '0',
								'wmpci_popup_disappear'	=> '0',
								'popup_height'			=> '',
								'popup_width'			=> '',
								'popup_bgcolor'			=> '',
								'popup_border_width'	=> '',
								'popup_border_color'	=> '',
								'popup_border_radius'	=> '',
								'popup_design'			=> 'wmpci-design1',
								'wmpci_mainheading'		=> '',
								'wmpci_subheading' 		=> '',
								'popup_fontcolor'       => '',
							);
	
	$default_options = apply_filters('wmpci_options_default_values', $wmpci_options );
	
	// Update default options
	update_option( 'wmpci_options', $default_options );
	
	// Overwrite global variable when option is update
	$wmpci_options = wmpci_get_settings();
}

/**
 * Get Settings From Option Page
 * 
 * Handles to return all settings value
 * 
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
function wmpci_get_settings() {
	
	$options = get_option('wmpci_options');

	$settings = is_array($options) 	? $options : array();
	
	return $settings;
}

/**
 * Get an option
 * Looks to see if the specified setting exists, returns default if not
 * 
 * @package WP Modal Popup with Cookie Integration
 * @since 1.2
 */
function wmpci_get_option( $key = '', $default = false ) {
	global $wmpci_options;
	
	$value = ! empty( $wmpci_options[ $key ] ) ? $wmpci_options[ $key ] : $default;
	$value = apply_filters( 'wmpci_get_option', $value, $key, $default );
	return apply_filters( 'wmpci_get_option_' . $key, $value, $key, $default );
}

/**
 * Escape Tags & Slashes
 *
 * Handles escapping the slashes and tags
 *
 * @package  WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
function wmpci_escape_attr($data) {
	return esc_attr( stripslashes($data) );
}

/**
 * Strip Slashes From Array
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
function wmpci_slashes_deep($data = array(), $flag = false) {
	
	if($flag != true) {
		$data = wmpci_nohtml_kses($data);
	}
	$data = stripslashes_deep($data);
	return $data;
}

/**
 * Strip Html Tags 
 * 
 * It will sanitize text input (strip html tags, and escape characters)
 * 
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
function wmpci_nohtml_kses($data = array()) {
	
	if ( is_array($data) ) {
		
		$data = array_map('wmpci_nohtml_kses', $data);
		
	} elseif ( is_string( $data ) ) {
		
		$data = wp_filter_nohtml_kses($data);
	}
	
	return $data;
}

/**
 * Plugin Popup designs
 * 
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */
function wmpci_popup_designs(){

	$design_arr = array(
						'wmpci-design1' => 'Simple',
						'wmpci-design2' => 'Top Bar Design',
					);

	return $design_arr;
}