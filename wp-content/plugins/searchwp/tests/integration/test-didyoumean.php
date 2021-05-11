<?php

class DidYouMeanTest extends WP_UnitTestCase {
	protected static $post_did_you_mean;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_did_you_mean = $factory->post->create( 
			array( 
				'post_title' => 'How to brew coffee'
			)
		);

		$indexer = new SearchWPIndexer();
		$indexer->unindexedPosts = array( get_post( self::$post_did_you_mean ) );
		$indexer->index();
	}

	public static function wpTearDownAfterClass() {
		SWP()->purge_index();
	}

	public function test_mispelled_single_word() {
		// Enable "Did you mean?" support.
		add_filter( 'searchwp_do_suggestions', '__return_true' );

		$expected_result = new SWP_Query( 
			array(
				's'      => 'cofee',
				'fields' => 'ids',
			)
		);

		remove_all_filters( 'searchwp_do_suggestions' );

		$this->assertEqualSets( 
			array( self::$post_did_you_mean ), 
			$expected_result->posts
		);
	}
}
