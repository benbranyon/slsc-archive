<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class PMAI_Compatibility{

    /**
     * @param $file
     * @return mixed|string
     */
    public static function basename( $file ){
		// wp_all_import_basename references the WP All Import plugin.
        return function_exists('wp_all_import_basename') ? wp_all_import_basename($file) : basename($file);
    }

}