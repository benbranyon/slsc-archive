<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SearchWP_Synonyms is responsible for synonym definition and handling
 *
 * @since 3.0
 */
class SearchWP_Synonyms {

	private $prefix = 'swp_termsyn_'; // Synonyms was originally an extension, keeping this prefix for back compat.
	private $synonyms;

	function __construct() {
		$this->synonyms = get_option( $this->prefix . 'settings' );

		// When Term Synonyms was an extension there was a uniqid flag used for the array.
		if ( is_array( $this->synonyms ) ) {
			$this->synonyms = array_values( $this->synonyms );
		}
	}

	function init() {
		add_filter( 'searchwp_pre_search_terms', array( $this, 'find' ), 5, 2 );
	}

	function get() {
		return $this->synonyms;
	}

	function update( $synonyms ) {
		foreach ( (array) $synonyms as $key => $synonymDefinition ) {

			// prepare the term
			$synonyms[ $key ]['term'] = trim( sanitize_text_field( $synonymDefinition['term'] ) );

			if ( empty( $synonymDefinition['synonyms'] ) ) {
				// no synonyms? kill it
				unset( $synonyms[ $key ] );
			} else {
				// sanitize the synonyms
				$synonyms_synonyms = explode( ',', trim( sanitize_text_field( $synonymDefinition['synonyms'] ) ) );
				$synonyms_synonyms = array_map( 'trim', $synonyms_synonyms );
				$synonyms_synonyms = array_map( 'sanitize_text_field', $synonyms_synonyms );

				$synonyms[ $key ]['synonyms'] = $synonyms_synonyms;

				// make sure there isn't synonymception
				if ( $synonyms[ $key ]['term'] == $synonyms[ $key ]['synonyms'] ) {
					unset( $synonyms[ $key ] );
				} else {
					// finalize the replace bool
					if ( isset( $synonyms[ $key ]['replace'] ) && 'false' !== $synonyms[ $key ]['replace'] && ! empty( $synonyms[ $key ]['replace'] ) ) {
						$synonyms[ $key ]['replace'] = true;
					} else {
						$synonyms[ $key ]['replace'] = false;
					}
				}
			}
		}

		// deliver sanitized results
		$synonyms = array_values( $synonyms );

		update_option( $this->prefix . 'settings', $synonyms ); // This is a legacy key used when this was a standalone Extension.

		return $synonyms;
	}

	/**
	 * Retrieve synonyms
	 *
	 * @param $term
	 *
	 * @return array
	 */
	function find( $term, $engine = 'default' ) {
		if ( empty( $term ) || empty( $this->synonyms ) ) {
			return $term;
		}

		$engine = SWP()->is_valid_engine( $engine ) ? $engine : 'default';

		$synonyms = $this->synonyms;

		// Convert everything to lowercase.
		if ( is_array( $synonyms ) && ! empty( $synonyms ) ) {
			foreach ( $synonyms as $synonym_id => $synonym ) {
				if ( ! empty( $synonyms[ $synonym_id ]['term'] ) ) {
					if ( function_exists( 'mb_strtolower' ) ) {
						$synonyms[ $synonym_id ]['term'] = mb_strtolower( $synonyms[ $synonym_id ]['term'] );
					} else {
						$synonyms[ $synonym_id ]['term'] = strtolower( $synonyms[ $synonym_id ]['term'] );
					}
				}

				if ( is_array( $synonyms[ $synonym_id ]['synonyms'] ) && ! empty( $synonyms[ $synonym_id ]['synonyms'] ) ) {
					if ( function_exists( 'mb_strtolower' ) ) {
						array_map( 'mb_strtolower', $synonyms[ $synonym_id ]['synonyms'] );
					} else {
						array_map( 'strtolower', $synonyms[ $synonym_id ]['synonyms'] );
					}
				}
			}
		}

		// We expect $term to be an array.
		if ( is_string( $term ) ) {
			$term = array( $term );
		}

		if ( ! is_array( $term ) || ! is_array( $synonyms ) || empty( $synonyms ) ) {
			return $term;
		}

		$original_term_hash = md5( serialize( $term ) );

		$aggressive = apply_filters( 'searchwp_synonyms_aggressive', false );

		$term = $aggressive
				? $this->process_aggressive( $term, $synonyms, $engine )
				: $this->process( $term, $synonyms, $engine );

		$term = SWP()->sanitize_terms( $term, $engine );

		do_action( 'searchwp_log', 'Query after synonym application: ' . implode( ' ', $term ) );

		// If synonyms have been applied it can interfere with AND logic.
		if ( md5( serialize( $term ) ) !== $original_term_hash ) {
			add_filter( 'searchwp_and_logic', '__return_false', 9 );
		}

		return $term;
	}

	/**
	 * Process synonyms in a more lax way (default)
	 *
	 * @since 3.1
	 *
	 * @param Array $term     The query to process.
	 * @param Array $synonyms The synonyms to consider.
	 *
	 * @return Array The resulting query after synonyms have been processed.
	 */
	private function process( $term, $synonyms, $engine = 'default' ) {
		$search_query       = implode( ' ', $term );
		$generated_synonyms = array();

		$partial_matches = apply_filters( 'searchwp_synonyms_use_partial_match', false );

		// Step 1: Add applicable synonyms.
		foreach ( $synonyms as $key => $synonym ) {
			// Is there any match?
			if ( false === stripos( trim( $search_query ), trim( $synonym['term'] ) ) ) {
				continue;
			}

			// Do partial matches apply?
			$has_partial_match = false;
			foreach ( $term as $search_term ) {
				if ( strtolower( trim( $search_term ) ) === strtolower( trim( $synonym['term'] ) ) ) {
					$has_partial_match = true;
					break;
				}
			}

			if ( ! $has_partial_match || ( ! empty( $partial_matches ) && ! $has_partial_match ) ) {
				continue;
			}

			$generated_synonyms = array_merge(
				$generated_synonyms,
				$synonym['synonyms']
			);
		}

		// Step 2: Remove applicable removals.
		foreach ( $synonyms as $key => $synonym ) {
			if ( empty( $synonym['replace'] ) ) {
				continue;
			}

			if ( false === stripos( trim( $search_query ), trim( $synonym['term'] ) ) ) {
				continue;
			}

			// Do partial matches apply?
			$has_partial_match = false;
			foreach ( $term as $search_term ) {
				if ( strtolower( trim( $search_term ) ) === strtolower( trim( $synonym['term'] ) ) ) {
					$has_partial_match = true;
					break;
				}
			}

			if ( ! $has_partial_match || ( ! empty( $partial_matches ) && ! $has_partial_match ) ) {
				continue;
			}

			$search_query = str_ireplace( $synonym['term'], '', $search_query );
		}

		// Step 3: Rebuild the search query.
		$revised_search_query = array_merge(
			explode( ' ', $search_query ),
			SWP()->sanitize_terms( implode( ' ', $generated_synonyms ), $engine )
		);

		$revised_search_query = array_map( 'trim', $revised_search_query );
		$revised_search_query = array_filter( $revised_search_query );
		$revised_search_query = array_unique( $revised_search_query );
		$revised_search_query = array_values( $revised_search_query );

		return $revised_search_query;
	}

	/**
	 * Aggressive synonym replacement means that when a synonym has replacements enabled
	 * those replacements will be made as each synonym is processed, which ends up
	 * being more aggressive when there are 'recursive' synonyms set up that share terms
	 * and synonyms among one another.
	 *
	 * @since 3.1
	 *
	 * @param Array $term     The query to process.
	 * @param Array $synonyms The synonyms to consider.
	 *
	 * @return Array The resulting query after synonyms have been processed.
	 */
	private function process_aggressive( $term, $synonyms, $engine = 'default' ) {
		$replace_immediately = apply_filters( 'searchwp_synonyms_aggressive_replace_immediately', false );
		$to_replace = array();

		$source_search_query = trim( implode( ' ', $term ) );

		foreach ( $synonyms as $key => $synonym ) {
			$synonym_trigger_term = trim( $synonym['term'] );

			// If there's no match, bail out.
			if ( false === stripos( $source_search_query, $synonym_trigger_term ) ) {
				continue;
			}

			// There is a match, do we need to replace?
			if ( $replace_immediately ) {
				$replacement = ! empty( $synonym['replace'] )
					? implode( ' ', $synonym['synonyms'] ) :
					$synonym_trigger_term . implode( ' ', $synonym['synonyms'] );

				$source_search_query = str_ireplace(
					$synonym_trigger_term,
					implode( ' ', SWP()->sanitize_terms( $replacement, $engine ) ),
					$source_search_query
				);

				// Because of the replacement there is a double space somewhere (maybe).
				$source_search_query = str_replace( '  ', ' ', $source_search_query );
			} else {
				$to_replace[] = $synonym_trigger_term;
				$source_search_query .= ' ' . implode( ' ', $synonym['synonyms'] );
			}
		}

		if ( ! $replace_immediately && ! empty( $to_replace ) ) {
			// No replacements have been made yet becase we want to replace them late.
			foreach ( $to_replace as $to_remove ) {
				$source_search_query = str_ireplace( $to_remove, '', $source_search_query );

				// Because of the replacement there is a double space somewhere (maybe).
				$source_search_query = str_replace( '  ', ' ', $source_search_query );
			}

			$to_replace = array();
		}

		$term = explode( ' ', trim( $source_search_query ) );

		return $term;
	}
}
