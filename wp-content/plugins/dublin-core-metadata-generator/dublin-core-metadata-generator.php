<?php
/*
Plugin Name: Dublin Core Metadata Generator
Plugin URI: https://www.seolus.com
Description: Automatically generate Dublin Core and Open Graph metadata in your website pages.
Version: 1.3.2
Author: Seolus
Author URI: https://www.seolus.com
License: GPL2
Dublin Core Metadata Generator is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. Dublin Core Metadata Generator is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*/

if ( ! defined( 'ABSPATH' ) ) exit; 

//Settings admin

if ( is_admin() ) :
	require_once('dcmg_options.php' );
endif;

if ( !is_admin() ) :
	require_once('dcmg_metadata.php' );
endif;
?>