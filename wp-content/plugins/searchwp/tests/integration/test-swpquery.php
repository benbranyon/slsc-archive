<?php

class SWPQueryTest extends WP_UnitTestCase {
	protected static $post;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post = $factory->post->create( 
			array( 
				'post_title' => 'This is a SWPQUERYTESTPOST post'
			)
		);

		$indexer = new SearchWPIndexer();
		$indexer->unindexedPosts = array( get_post( self::$post ) );
		$indexer->index();
	}
    
	public static function wpTearDownAfterClass() {
		SWP()->purge_index();
	}

	public function test_s_arg() {
		$expected_result = new SWP_Query( 
			array(
				's'      => 'SWPQUERYTESTPOST',
				'fields' => 'ids',
			)
		);

		$expected_empty = new SWP_Query( 
			array(
				's'      => 'invalid',
				'fields' => 'ids',
			)
		);

		$this->assertEqualSets( 
			array( self::$post ), 
			$expected_result->posts
		);

		$this->assertEmpty( $expected_empty->posts );
	}
}
