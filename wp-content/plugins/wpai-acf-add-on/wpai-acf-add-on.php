<?php
/*
Plugin Name: WP All Import - ACF Add-On Pro
Plugin URI: http://www.wpallimport.com/
Description: Import to Advanced Custom Fields. Requires WP All Import, ACF Import Add-On Free, & Advanced Custom Fields.
Version: 4.0.1
Author: Soflyy
*/

namespace wpai_acf_add_on_pro;
use PMXI\AddonInstaller\AddonInstaller;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'acf' ) ) {
	?>
	<div class="error"><p>
			<?php
			// Translators: %1$s is the plugin name. The message includes bold text and a link to the Advanced Custom Fields plugin.
			printf(wp_kses(__('<b>%1$s Plugin</b>: <a target="_blank" href="http://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a> must be installed', 'wp_all_import_acf_add_on'),
				array(
					'a' => array(
						'href' => array(),
						'target' => array()
					),
					'b' => array()
				)),
				'WP All Import - ACF Add-On Pro'
			);
			?>
		</p></div>
	<?php

	deactivate_plugins(plugin_basename(__FILE__));

}

const PMAI_PRO_VERSION = '4.0.1';

require __DIR__ . '/vendor/autoload.php';

// Initialize the PMXI Addon Installer SDK for ACF Add-On Free
$wpai_addon_installer = new AddonInstaller([
    'addon_name' => 'WP All Import - ACF Add-On Free',
    'addon_slug' => 'csv-xml-import-for-acf',
    'addon_author' => 'Soflyy',
    'minimum_version' => '1.0.4',
    'pro_plugin_name' => 'WP All Import - ACF Add-On Pro',
    'pro_plugin_file' => __FILE__,
    'textdomain' => 'wp_all_import_acf_add_on',
    'version_constant' => 'PMAI_VERSION',
    'edition_constant' => 'PMAI_EDITION',
    'expected_edition' => 'free',
	'free_plugin_file' => 'plugin.php',
    'disable_deactivation' => true
]);

$wpai_addon_installer->install_addon_from_repository();


// Load Pro fields as needed.
add_filter('wp_all_import_acf_field_class', function($class , $fieldData, $post, $fieldName, $fieldParent){

    if(!class_exists($class)) {
	    $class_suffix = str_replace( " ", "", ucwords( str_replace( array( "_", "-" ), " ", $fieldData['type'] ) ) );
	    $class        = '\\wpai_acf_add_on_pro\\fields\\acf\\Field' . $class_suffix;
	    if ( ! class_exists( $class ) ) {
		    $class = '\\wpai_acf_add_on_pro\\fields\\acf\\' . $fieldData['type'] . '\\Field' . $class_suffix;

		    // If class still doesn't exist check using alternate field name in namespace. Ensure the class isn't
		    // using a version specific name before using the alternate.
		    if ( ! class_exists( $class ) && ! class_exists( $class . 'V5' ) && ! class_exists( $class . 'V4' ) ) {
			    $class = '\\wpai_acf_add_on_pro\\fields\\acf\\' . 'field_' . $fieldData['type'] . '\\Field' . $class_suffix;
		    }
	    }
    }

    return $class;
},10,5);

// Load Pro views directory as needed.
add_filter('wp_all_import_acf_field_view_dir', function($fieldDir, $field){

	if(!is_dir($fieldDir)){
		$fieldDir = __DIR__ . '/src/fields/views/'. $field->type;
	}

	return $fieldDir;
}, 10, 2);

// Load Pro views as needed.
add_filter('wp_all_import_acf_field_view_path', function($filePath, $field){

	if(!(file_exists($filePath) && is_readable($filePath))){
		switch ($field->supportedVersion){
			case 'v4':
			case 'v5':
				$fieldDir = __DIR__ . '/src/fields/views/'. $field->type;
				$filePath = $fieldDir . DIRECTORY_SEPARATOR . $field->type . '-' . $field->supportedVersion . '.php';
				break;
			default:
				$filePath = __DIR__ . '/src/fields/views/'. $field->type .'.php';
				break;
		}
	}

	return $filePath;
},10,2);

// Retrieve plugin option details from WP All Import.
$wpai_acf_addon_options = get_option('PMXI_Plugin_Options');

// Favor new API URL, but fallback to old if needed.
if( !empty($wpai_acf_addon_options['info_api_url_new'])){
	$api_url = $wpai_acf_addon_options['info_api_url_new'];
}elseif( !empty($wpai_acf_addon_options['info_api_url'])){
	$api_url = $wpai_acf_addon_options['info_api_url'];
}else{
	$api_url = null;
}

if (!empty($api_url)){
	// Initialize updater.
	$updater = new Updater( $api_url, __FILE__, array(
			'version' 	=> PMAI_PRO_VERSION,
			'license' 	=> false,
			'item_name' => 'ACF Add-On',
			'author' 	=> 'Soflyy'
		)
	);
}