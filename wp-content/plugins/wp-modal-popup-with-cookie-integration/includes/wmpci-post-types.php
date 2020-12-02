<?php
/**
 * Post Type Functions
 *
 * Handles all custom post types of plugin
 * 
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0 
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Setup Popup Post Type
 * 
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0 
 **/
function wmpci_register_post_types() {
	
	// Popup post type
	$popup_post_type_labels = array(
								    'name'				=> __('PopUps','wmpci'),
								    'singular_name' 	=> __('PopUp','wmpci'),
								    'add_new' 			=> __('Add New Popup','wmpci'),
								    'add_new_item' 		=> __('Add New Popup','wmpci'),
								    'edit_item' 		=> __('Edit Popup','wmpci'),
								    'new_item' 			=> __('New Popup','wmpci'),
								    'all_items' 		=> __('All Popups','wmpci'),
								    'view_item' 		=> __('View Popup','wmpci'),
								    'search_items' 		=> __('Search Popup','wmpci'),
								    'not_found' 		=> __('No Popup found','wmpci'),
								    'not_found_in_trash'=> __('No Popup found in Trash','wmpci'),
								    'parent_item_colon' => '',
								    'menu_name' 		=> __('PopUps','wmpci'),
								);
	$popup_post_type_args = array(
						    'labels' 				=> $popup_post_type_labels,
						    'public' 				=> false,
						    'query_var' 			=> false,
						    'rewrite' 				=> false,
						    'show_ui'				=> true,
						    'capability_type' 		=> 'post',
						    'menu_icon'				=> 'dashicons-megaphone',
						    'map_meta_cap'    		=> true,
						    'supports' 				=> array( 'title', 'editor', 'thumbnail' )
					 	);
	
	// Filter to modify popup post type arguments
	$popup_post_type_args = apply_filters( 'wmpci_register_popup__post_type', $popup_post_type_args );
	
	// Register popup post type
	register_post_type( WMPCI_POPUP_POST_TYPE, $popup_post_type_args );
}

// Action to register post type
add_action( 'init', 'wmpci_register_post_types' );