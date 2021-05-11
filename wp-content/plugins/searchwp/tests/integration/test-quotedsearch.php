<?php

class QuotedSearchTest extends WP_UnitTestCase {
	protected static $post_valid;
	protected static $post_invalid_title;
	protected static $post_invalid_meta;
	protected static $post_invalid_taxonomy;
	protected static $post_valid_taxonomy_suffix;
	
	protected static $tax_term_valid;
	protected static $tax_term_invalid;
	protected static $tax_term_valid_suffixed;

	public static function wpSetUpBeforeClass( $factory ) {
		self::set_tax_terms( $factory );
		self::set_posts( $factory );
		self::setup_searchwp();
	}

	public static function setup_searchwp() {
		// Make sure our engine will index All Custom Fields.
		SWP()->settings['engines']['default']['post']['weights']['cf'] = array(
			uniqid( 'swppv' ) => array(
				'metakey' => 'searchwpcfdefault',
				'weight'  => 1
			)
		);

		// Make sure our engine will index Tags.
		SWP()->settings['engines']['default']['post']['weights']['tax']['category'] = 1;

		$indexer = new SearchWPIndexer();
		$indexer->unindexedPosts = get_posts( array(
			'include' => array(
				self::$post_valid,
				self::$post_invalid_title,
				self::$post_invalid_meta,
				self::$post_invalid_taxonomy,
				self::$post_valid_taxonomy_suffix,
			)
		) );
		$indexer->index();
	}

	public static function set_posts( $factory ) {
		self::$post_valid = $factory->post->create( 
			array( 
				'post_title' => 'Title ipsum title quoted search',
				'meta_input' => array(
					'swp_custom_field' => 'meta ipsum meta quoted search',
				),
				'post_category' => array( self::$tax_term_valid )
			)
		);

		self::$post_invalid_title = $factory->post->create( 
			array( 
				'post_title' => 'Title ipsum title INVALID quoted search',
			)
		);

		self::$post_invalid_meta = $factory->post->create( 
			array( 
				'post_title' => 'Quoted search meta invalid post',
				'meta_input' => array(
					'swp_custom_field' => 'meta ipsum INAVLID meta quoted search',
				),
			)
		);

		self::$post_invalid_taxonomy = $factory->post->create( 
			array( 
				'post_title'    => 'Quoted search taxonomy INVALID post',
				'post_category' => array( self::$tax_term_invalid ),
			)
		);

		// This post SHOULD be included in results because it has a match with a suffix.
		self::$post_valid_taxonomy_suffix = $factory->post->create( 
			array( 
				'post_title' => 'Quoted search taxonomy post with suffix',
				'post_category' => array( self::$tax_term_valid_suffixed ),
			)
		);
	}

	public static function set_tax_terms( $factory ) {
		self::$tax_term_valid = $factory->term->create( 
			array( 
				'name'     => 'Lorem Term Ipsum', 
				'taxonomy' => 'category',
				'slug'     => 'lorem-term-ipsum'
			) 
		);

		self::$tax_term_invalid = $factory->term->create( 
			array( 
				'name'     => 'Lorem Term INVALID Ipsum', 
				'taxonomy' => 'category',
				'slug'     => 'lorem-term-invalid-ipsum'
			) 
		);
		
		self::$tax_term_valid_suffixed = $factory->term->create( 
			array( 
				'name'     => 'Lorem Term Ipsum Suffixed', 
				'taxonomy' => 'category',
				'slug'     => 'lorem-term-ipsum-suffixed'
			) 
		);
	}

	public static function wpTearDownAfterClass() {
		SWP()->purge_index();
		SWP()->settings['engines']['default']['post']['weights']['cf'] = array();
		SWP()->settings['engines']['default']['post']['weights']['tax']['category'] = 0;
	}

	public function test_quoted_search_no_match() {
		// Enable quoted search support.
		add_filter( 'searchwp_allow_quoted_phrase_search', '__return_true' );

		// Perform a quoted search with no matches.
		$query_with_mismatch = new SWP_Query( 
			array(
				's'      => '"no phrase match"',
				'fields' => 'ids',
			)
		);

		remove_all_filters( 'searchwp_allow_quoted_phrase_search' );

		$this->assertEmpty( $query_with_mismatch->posts );
	}

	public function test_quoted_search_match_in_title() {
		// Enable quoted search support.
		add_filter( 'searchwp_allow_quoted_phrase_search', '__return_true' );

		// Search query matches our valid post but NOT our invalid post.
		$query_with_match_in_title = new SWP_Query( 
			array(
				's'      => '"um title quot"',
				'fields' => 'ids',
			)
		);

		remove_all_filters( 'searchwp_allow_quoted_phrase_search' );

		// Assert that the results contain only the valid post.
		$this->assertEqualSets( 
			array( self::$post_valid ), 
			$query_with_match_in_title->posts
		);
	}

	public function test_quoted_search_in_meta() {
		// Enable quoted search support.
		add_filter( 'searchwp_allow_quoted_phrase_search', '__return_true' );

		// Search query matches our valid post but NOT our invalid post.
		$query_with_match = new SWP_Query( 
			array(
				's'      => '"ta ipsum meta quoted sea"',
				'fields' => 'ids',
			)
		);

		remove_all_filters( 'searchwp_allow_quoted_phrase_search' );

		// Assert that the results contain only the valid post.
		$this->assertEqualSets( 
			array( self::$post_valid ), 
			$query_with_match->posts
		);
	}

	public function test_quoted_search_in_taxonomy() {
		// Enable quoted search support.
		add_filter( 'searchwp_allow_quoted_phrase_search', '__return_true' );

		// Search query matches our valid post but NOT our invalid post.
		$query_with_match = new SWP_Query( 
			array(
				's'      => '"term ipsum"',
				'fields' => 'ids',
			)
		);

		remove_all_filters( 'searchwp_allow_quoted_phrase_search' );

		// Assert that the results contain only the valid post.
		$this->assertEqualSets( 
			array( self::$post_valid, self::$post_valid_taxonomy_suffix ), 
			$query_with_match->posts
		);
	}
}
