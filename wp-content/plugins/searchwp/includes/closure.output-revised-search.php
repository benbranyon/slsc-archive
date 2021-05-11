<?php

// This file is kept separate to prevent Fatal errors in legacy PHP versions.
add_action( 'loop_start', function() use ( $args ) {
	SWP()->output_revised_search( $args );
} );