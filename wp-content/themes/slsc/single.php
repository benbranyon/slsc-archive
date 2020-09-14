<?php
/**
 * The Template for displaying all single posts
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = Timber::query_post();
$context['post'] = $post;
// Define generic templates.
$templates = array( 
	'single-' . $post->post_type . '-' . $post->slug . '.twig', 
	'single-' . $post->ID . '.twig', 
	'single-' . $post->post_type . '.twig',
	'single.twig'
);

if(get_post_type($post) == 'archive') {
	$referer = wp_get_referer();
	if (strpos($referer, 'archive') == false) {
		$referer = '/archive/';
	}
	$context['referer'] = $referer;
}

if(get_post_type($post) == 'story') {
	foreach($post->terms as $term) {
		if($term->taxonomy == 'collection') {
			$context['collection_slug'] = $term->slug;
		}
	}
}

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'components/password-form.twig', $context );
} else {
	Timber::render( $templates, $context );
}
