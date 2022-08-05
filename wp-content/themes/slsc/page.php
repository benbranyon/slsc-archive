<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * To generate specific templates for your pages you can use:
 * /mytheme/templates/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = new Timber\Post();
$context['post'] = $post;
// Define generic templates.
$templates = array( 
	'page-' . $post->post_name . '.twig', 
	'page-' . $post->ID . '.twig', 
	'page.twig' 
);
// Set the Homepage template.
if ( is_front_page() ) {
	$args = array(
	    // Get post type project
	    'post_type' => 'get_involved',
	    // Get all posts
	    'posts_per_page' => 2,
	    // Order by post date
	    'orderby' => array(
	        'date' => 'DESC'
	    )
	);
	$context['get_involved'] = Timber::get_posts($args);
	$args = array(
	    // Get post type project
	    'post_type' => 'story',
	    // Get all posts
	    'posts_per_page' => 5,
	    // Order by post date
	    'orderby' => array(
	        'date' => 'DESC'
	    )
	);
	$context['stories'] = Timber::get_posts($args);
	$terms = get_terms( array(
		'taxonomy' => array('topic', 'collection', 'file_type'),
    	'hide_empty' => false,
    	'number' => 15,
	) );
	$context['related'] = $terms;
	array_unshift( $templates, 'front-page.twig' );
} 
if ( is_page('collections') ) {
	$collections = Timber::get_terms('collection');
	$context['collections'] = $collections;
}
// Render twig template.
if ( post_password_required( $post->ID ) ) {
	Timber::render( 'components/password-form.twig', $context );
} else {
	Timber::render( $templates, $context );
}
