<?php
/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */

$templates = array( 'archive.twig', 'index.twig' );

$context = Timber::get_context();

$context['title'] = 'Archive';
if ( is_day() ) {
	$context['title'] = 'Archive: '.get_the_date( 'D M Y' );
} else if ( is_month() ) {
	$context['title'] = 'Archive: '.get_the_date( 'M Y' );
} else if ( is_year() ) {
	$context['title'] = 'Archive: '.get_the_date( 'Y' );
} else if ( is_tag() ) {
	$context['title'] = single_tag_title( '', false );
} else if ( is_category() ) {
	$context['title'] = single_cat_title( '', false );
	array_unshift( $templates, 'archive-' . get_query_var( 'cat' ) . '.twig' );
} else if ( is_tax() ) {
	$context['title'] = single_term_title( null, false );
	$term = get_queried_object();
	$collection_description = get_field('collection_description', $term);
	$args = array(
	    // Get post type project
	    'post_type' => 'story',
	    // Get all posts
	    'posts_per_page' => 1,
	    // Order by post date
	    'orderby' => array(
	        'date' => 'DESC'
	    ),
		'tax_query' => array(
    		array(
        		'taxonomy' => 'collection',
        		'terms' => $term->term_id
        	)
    	)
	);
	$context['story'] = Timber::get_posts($args);
	$context['post_count'] = $GLOBALS['wp_query']->found_posts;
	$context['term'] = $term;
	$context['collection_description'] = $collection_description;
	array_unshift( $templates, 'taxonomy.twig' );
} else if ( is_post_type_archive() ) {
	$context['title'] = post_type_archive_title( '', false );
	array_unshift( $templates, 'archive-' . get_post_type() . '.twig' );
}

$context['pagination'] = Timber::get_pagination();


Timber::render( $templates, $context );
