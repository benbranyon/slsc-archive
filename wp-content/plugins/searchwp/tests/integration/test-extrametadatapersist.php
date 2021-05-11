<?php

class ExtraMetadataPersistTest extends WP_UnitTestCase {
	protected static $extra_meta_key;
	protected static $post;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$extra_meta_key = 'forced_extra_meta_key';

		self::$post = $factory->post->create(
			array(
				'post_title' => 'Title ipsum title quoted search',
				'meta_input' => array(
					'swp_custom_field' => 'meta ipsum meta quoted search',
				)
			)
		);

		// Add Extra Meta during indexing.
		add_filter( 'searchwp_extra_metadata', function( $extra_meta ) {
			$extra_meta[ self::$extra_meta_key ] = array( 'lorem', 'ipsum', 'persist extra meta test!' );

			return $extra_meta;
		} );

		// Persist Extra Meta.
		add_filter( 'searchwp_persist_extra_metadata', '__return_true' );

		// Add Extra Meta to default engine.
		SWP()->settings['engines']['default']['post']['weights']['cf'] = array(
			uniqid( 'swppv' ) => array(
				'metakey' => self::$extra_meta_key,
				'weight'  => 1
			)
		);

		// Index the post with Extra Meta.
		$indexer = new SearchWPIndexer();
		$indexer->unindexedPosts = get_posts( array(
			'include' => array(
				self::$post,
			)
		) );
		$indexer->index();
	}

	public static function wpTearDownAfterClass() {
		SWP()->purge_index();
		SWP()->settings['engines']['default']['post']['weights']['cf'] = array();
	}

	public function test_persisted_extra_meta_quoted_search() {
		// Enable quoted search support.
		add_filter( 'searchwp_allow_quoted_phrase_search', '__return_true' );

		// Add our forced Extra Meta key.
		add_filter( 'searchwp_custom_field_keys', function ( $keys ) {
			return array_merge( $keys, array( self::$extra_meta_key ) );
		} );

		// Perform a quoted search targeting our Extra Meta.
		$search_results = new SWP_Query(
			array(
				's'      => '"ist extra meta te"',
				'fields' => 'ids',
			)
		);

		remove_all_filters( 'searchwp_allow_quoted_phrase_search' );
		remove_all_filters( 'searchwp_custom_field_keys' );

		$this->assertEqualSets(
			array( self::$post ),
			$search_results->posts
		);
	}

	public function test_persisted_extra_meta_quoted_search_mismatch() {
		// Enable quoted search support.
		add_filter( 'searchwp_allow_quoted_phrase_search', '__return_true' );

		// Removes Extra Meta from engine config which should prevent any results.
		SWP()->settings['engines']['default']['post']['weights']['cf'] = array();

		// Perform a quoted search targeting our Extra Meta.
		$search_results = new SWP_Query(
			array(
				's'      => '"ist extra meta te"',
				'fields' => 'ids',
			)
		);

		remove_all_filters( 'searchwp_allow_quoted_phrase_search' );

		$this->assertEmpty( $search_results->posts );
	}
}
