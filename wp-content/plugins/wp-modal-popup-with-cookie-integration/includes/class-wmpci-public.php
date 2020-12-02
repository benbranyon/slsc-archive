<?php
/**
 * Public Class
 *
 * Handles the public side functionality of plugin
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Wmpci_Public {
	
	function __construct() {
		
		// Action to add popup
		add_action( 'wp_footer', array($this, 'wmpci_add_popup') );
	}

	/**
	 * Function to add popup to site
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0 
	 **/
	function wmpci_add_popup() {
		
		global $wmpci_options;

		$design = !empty($wmpci_options['popup_design']) ? $wmpci_options['popup_design'] : 'wmpci-design1';
		$design = file_exists( WMPCI_DIR . '/templates/' . $design.'.php' ) ? $design : 'wmpci-design1';

		echo $this->wmpci_popup_css(); // Popup custom css

		include_once( WMPCI_DIR . "/templates/{$design}.php" );
	}

	/**
	 * Function for popup css to site
	 * 
	 * @package WP Modal Popup with Cookie Integration
	 * @since 1.0.0 
	 **/
	function wmpci_popup_css() {
		
		global $wmpci_options;

		$html = '<style type="text/css">';

		$html .= '.wmpci-popup-wrp .wmpci-popup-body{';
		if( !empty($wmpci_options['popup_bgcolor']) ) {
			$html .= "background-color: {$wmpci_options['popup_bgcolor']};";
		}

		if( ($wmpci_options['popup_border_width']) != '' ) {
			$html .= "border-width: {$wmpci_options['popup_border_width']}px; border-style: solid;";
		}

		if( !empty($wmpci_options['popup_border_color']) ) {
			$html .= "border-color: {$wmpci_options['popup_border_color']};";
		}

		if( !empty($wmpci_options['popup_width']) ) {
			$html .= "max-width: {$wmpci_options['popup_width']};";
		}

		if( !empty($wmpci_options['popup_border_radius']) ) {
			$html .= "border-radius: {$wmpci_options['popup_border_radius']}px;";
		}
		$html .= '}';
		
		$html .= ".wpmci-popup-cnt-inr-wrp{";
		if( !empty($wmpci_options['popup_height']) ) {
			$html .= "height:{$wmpci_options['popup_height']};";
		}
		if( !empty($wmpci_options['popup_fontcolor']) ) {
			$html .= "color:{$wmpci_options['popup_fontcolor']};";
		}		
		$html .= '}';
		
		$html .= ".wpmci-popup-cnt-inr-wrp h2, .wpmci-popup-cnt-inr-wrp h4{";		
		if( !empty($wmpci_options['popup_fontcolor']) ) {
			$html .= "color:{$wmpci_options['popup_fontcolor']};";
		}		
		$html .= '}';

		$html .='</style>';

		return $html;
	}
}

$wmpci_public = new Wmpci_Public();