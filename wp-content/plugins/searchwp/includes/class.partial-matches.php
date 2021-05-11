<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SearchWPPartialMatches enables partial term matching during searches.
 *
 * @since 3.0
 */
class SearchWPPartialMatches {

	private $exact_matches = array();
	private $consumed      = array();
	private $cache         = array();
	private $initialized   = false;
	private $engine;

	public function __construct() {}

	/**
	 * This function imposes partial matching during searches. Internally it is called only if the Advanced
	 * setting has been ticked/enabled.
	 *
	 * @since 3.0
	 */
	public function init() {
		if ( empty( $this->initialized ) ) {
			$this->initialized = true;

			add_filter( 'searchwp_terms', array( $this, 'set_exact_matches' ), 11, 2 );
			add_filter( 'searchwp_term_in', array( $this, 'find_partial_matches' ), 210, 3 );

			add_filter( 'searchwp_weight_mods', array( $this, 'exact_match_buoy' ) );
		}
	}

	/**
	 * Find any exact matches for this engine.
	 *
	 * @param string $query The full search query.
	 * @param string $engine The engine being used.
	 *
	 * @since 3.0
	 */
	public function set_exact_matches( $query, $engine ) {
		global $wpdb;

		$this->engine = $engine;

		$proceed = apply_filters( 'searchwp_partial_matching_' . $engine, true );

		if ( empty( $proceed ) ) {
			return $query;
		}

		$term_array = explode( ' ', $query );
		$term_array = array_map( 'trim', $term_array );
		$term_array = array_map( 'sanitize_text_field', $term_array );
		$term_array = array_map( 'strtolower', $term_array );

		if ( empty( $term_array ) ) {
			return $query;
		}

		$swp_db_prefix = $wpdb->prefix . SEARCHWP_DBPREFIX;

		foreach ( $term_array as $term ) {
			$found_term = $wpdb->get_col(
				$wpdb->prepare( "SELECT term FROM {$swp_db_prefix}terms WHERE term = %s LIMIT 1", $term )
			);

			if ( $found_term ) {
				if ( ! array_key_exists( $engine, $this->exact_matches ) ) {
					$this->exact_matches[ $engine ] = array();
				}

				$this->exact_matches[ $engine ][] = $term;

				$break_on_first_match = apply_filters( 'searchwp_partial_matches_aggressive', false, array(
					'engine' => $engine,
				) );

				if ( $break_on_first_match ) {
					break;
				}
			}
		}

		return $query;
	}

	public function find_partial_matches( $terms, $engine, $original_prepped_term ) {
		$proceed = apply_filters( 'searchwp_partial_matching_' . $engine, true );

		if ( empty( $proceed ) ) {
			$this->reset();

			return $terms;
		}

		$proceed_despite_exact_matches = apply_filters( 'searchwp_partial_matches_lenient', true, array(
			'engine' => $engine,
		) );

		if ( ! empty( $this->exact_matches[ $engine ] ) && empty( $proceed_despite_exact_matches ) ) {
			$this->reset();

			return $terms;
		}

		$like_terms = $this->find_like_terms( $terms, $engine );

		if ( ! empty( $like_terms ) ) {
			$like_terms = array_diff( $like_terms, $terms );
		}

		$has_like_terms = ! empty( $like_terms );

		$force_fuzzy = apply_filters( 'searchwp_partial_matches_force_fuzzy', false, array(
			'engine' => $engine,
		) );

		// SearchWP 3.1 introduced 'did you mean' functionality, which comes into play here because
		// it utilizes Fuzzy Matching and simply grabs the least fuzzy (most similar) term(s) and uses
		// those for a new search. That said, proceeding here doesn't make sense as it finds anything
		// fuzzy within the threshold and uses all of those terms. So we're going to potentially
		// short circuit at this point if that option is enabled.
		$existing_settings = searchwp_get_option( 'advanced' );
		if (
			! $has_like_terms
			&& empty( $force_fuzzy )
			&& array_key_exists( 'do_suggestions', $existing_settings )
			&& ! empty( $existing_settings['do_suggestions'] )
		) {
			$this->reset();

			// "Did you mean?" will take over from here.
			return (array) $terms;
		}

		// If we found LIKE terms and don't want to force fuzzy matches, break out
		if ( $has_like_terms && empty( $force_fuzzy ) ) {
			$this->reset();

			return array_merge( (array) $terms, (array) $like_terms );
		}

		$fuzzy_terms = $this->find_fuzzy_matches( $terms, $engine, $original_prepped_term );
		$fuzzy_terms = array_diff( $fuzzy_terms, $terms );

		$all_terms = array_unique( array_merge( (array) $terms, (array) $like_terms, (array) $fuzzy_terms ) );

		$this->reset();

		return $all_terms;
	}

	public function reset() {
		$this->consumed = array();
	}

	public function find_like_terms( $terms, $engine ) {
		global $wpdb, $searchwp;

		$terms_copy = $terms; // Make a copy of the terms.
		sort( $terms_copy ); // Sort the terms to prevent false hashes.
		$hash = md5( $engine . json_encode( $terms_copy ) );

		if ( array_key_exists( $hash, $this->cache ) ) {
			return $this->cache[ $hash ];
		}

		$original = $terms;
		$this->original_search = $original;

		$swp_db_prefix = $wpdb->prefix . SEARCHWP_DBPREFIX;

		if ( is_string( $terms ) ) {
			$terms = explode( ' ', $terms );
		}

		// check against the regex pattern whitelist
		$terms = ' ' . implode( ' ', $terms ) . ' ';
		$whitelisted_terms = array();

		if ( method_exists( $searchwp, 'extract_terms_using_pattern_whitelist' ) ) { // added in SearchWP 1.9.5
			// extract terms based on whitelist pattern, allowing for approved indexing of terms with punctuation
			$whitelisted_terms = $searchwp->extract_terms_using_pattern_whitelist( $terms );

			// add the buffer so we can whole-word replace
			$terms = '  ' . $terms . '  ';

			// remove the matches
			if ( ! empty( $whitelisted_terms ) ) {
				$terms = str_ireplace( $whitelisted_terms, '', $terms );
			}

			// clean up the double space flag we used
			$terms = str_replace( '  ', ' ', $terms );
		}

		// rebuild our terms array
		$terms = explode( ' ', $terms );

		// maybe append our whitelist
		if ( is_array( $whitelisted_terms ) && ! empty( $whitelisted_terms ) ) {
			$whitelisted_terms = array_map( 'trim', $whitelisted_terms );
			$terms = array_merge( $terms, $whitelisted_terms );
		}

		$terms = array_map( 'trim', $terms );
		$terms = array_filter( $terms, 'strlen' );
		$terms = array_map( 'sanitize_text_field', $terms );

		// dynamic minimum character length
		$minCharLength = absint( apply_filters( 'searchwp_like_min_length', 3 ) ) - 1;
		$maxCharLength = absint( apply_filters( 'searchwp_partial_result_max_length', 20 ) );

		// Filter out $terms based on min length
		foreach ( $terms as $key => $term ) {
			if ( strlen( $term ) < $minCharLength ) {
				unset( $terms[ $key ] );
			}
		}

		$terms = array_values( $terms );

		$like_term_ids = array();

		// by default we will compare to both the term and the stem, but give developers the option to prevent comparison to the stem
		$term_or_stem = 'stem';
		if ( ! apply_filters( 'searchwp_like_stem', false, $terms, $engine ) ) {
			$term_or_stem = 'term';
		}

		if ( ! empty( $terms ) ) {
			// CHAR_LENGTH is on the stem because the stem will always be shortest.
			$sql = "SELECT id FROM {$swp_db_prefix}terms WHERE CHAR_LENGTH(stem) > {$minCharLength} AND CHAR_LENGTH(term) <= {$maxCharLength} AND (";

			$wildcard_before = apply_filters( 'searchwp_like_wildcard_before', true );
			if ( ! empty( $wildcard_before ) ) {
				$wildcard_before = '%';
			} else {
				$wildcard_before = '';
			}

			$wildcard_after = apply_filters( 'searchwp_like_wildcard_after', true );
			if ( ! empty( $wildcard_after ) ) {
				$wildcard_after = '%';
			} else {
				$wildcard_after = '';
			}

			// need to query for LIKE matches in terms table and append them
			$count = 0;
			foreach ( $terms as $term ) {
				if ( $count > 0 ) {
					$sql .= ' OR ';
				}
				$sql .= $wpdb->prepare( ' ( term LIKE %s OR stem LIKE %s ) ', $wildcard_before . $wpdb->esc_like( $term ) . $wildcard_after, $wildcard_before . $wpdb->esc_like( $term ) . $wildcard_after );
				$count ++;
			}
			$sql .= ')';

			$like_term_ids = $wpdb->get_col( $sql );
		}

		// These partial matches being in the terms table does not necessarily mean they're still applicable.
		// Case in point: a post was deleted and it was the only one to have had this term. The term(s) are still
		// in the table because it's too expensive to try and also purge unique terms from the terms table when
		// content is deleted. So now we need to make sure this term is actually still in the index itself.
		if ( ! empty( $like_term_ids ) ) {
			$like_term_ids = SWP()->validate_terms_in_index_by_id( $like_term_ids, $engine );

			// Get the terms (or stems)
			if ( ! empty( $like_term_ids ) ) {
				$like_term_ids = array_map( 'absint', $like_term_ids );

				$like_terms = $wpdb->get_results(
					"SELECT term, stem FROM {$swp_db_prefix}terms WHERE id IN (" . implode( ', ', $like_term_ids ) . ')'
				);

				$like_terms = array_merge( wp_list_pluck( $like_terms, 'term' ), wp_list_pluck( $like_terms, 'stem' ) );
				$like_terms = array_unique( $like_terms );

				$terms = array_values( array_unique( array_merge( $like_terms, $terms ) ) );
			}
		}

		// Allow LIKE terms to be used more than once?
		$exclude_consumed = apply_filters( 'searchwp_like_aggressive', false );

		if ( is_array( $terms ) && ! empty( $terms ) && $exclude_consumed ) {
			$terms = array_map( 'sanitize_text_field', $terms );
			$terms = array_diff( $terms, $this->consumed );
			if ( empty( $terms ) ) {
				$terms = (array) $original;
			}
		}

		$this->consumed = array_unique( array_merge( $this->consumed, $terms ) );

		$this->cache[ $hash ] = $terms;

		return $terms;
	}

	/**
	 * Find fuzzy matches using MySQL's SOUNDEX feature
	 *
	 * @param $terms
	 * @param $engine
	 * @param $original_prepped_term
	 *
	 * @return array
	 */
	public function find_fuzzy_matches( $terms, $engine, $original_prepped_term ) {
		global $wpdb, $searchwp;

		if ( isset( $engine ) ) {
			$engine = null;
		}

		$swp_db_prefix = $wpdb->prefix . SEARCHWP_DBPREFIX;

		// there has to be at least a term
		if ( ! is_array( $terms ) || empty( $terms ) ) {
			return $terms;
		}

		// by default we're only going to apply fuzzy logic if we need to (e.g. confirmed misspelling)
		$missing_match = '';
		$found_term = $wpdb->get_col( $wpdb->prepare( "SELECT term FROM {$swp_db_prefix}terms WHERE term = %s LIMIT 1", $original_prepped_term ) );

		if ( empty( $found_term ) ) {
			$missing_match = $original_prepped_term;
		}

		// TODO: is this really necessary? It was added in 3.1 but if we're in this method (which by default)
		// only runs when there are no confirmed misspellings, is this even applicable?
		$force_fuzzy = apply_filters( 'searchwp_partial_matches_force_fuzzy', false, array(
			'engine' => $engine,
		) );

		// if everything was an exact match there's no more work to do
		if ( ! empty( $missing_match ) || $force_fuzzy ) {

			// dynamic minimum character length
			$minCharLength = absint( apply_filters( 'searchwp_fuzzy_min_length', 3 ) ) - 1;
			$maxCharLength = absint( apply_filters( 'searchwp_partial_result_max_length', 20 ) );

			$sql = "SELECT term, stem FROM {$swp_db_prefix}terms WHERE CHAR_LENGTH(term) > {$minCharLength} AND CHAR_LENGTH(term) <= {$maxCharLength} AND (";

			// need to query for fuzzy matches in terms table and append them
			$count = 0;
			$the_terms = array();
			foreach ( $terms as $term ) {

				if ( $count > 0 ) {
					$sql .= ' OR ';
				}

				// check for the number of digits (e.g. SKUs being sent through would result in disaster)
				preg_match_all( '/[0-9]/', $term, $digits );
				$percentDigits = ! empty( $digits ) && isset( $digits[0] ) ? ( count( $digits[0] ) / strlen( $term ) ) * 100 : 0;

				$percentDigitsThreshold = absint( apply_filters( 'searchwp_fuzzy_digit_threshold', 10 ) );
				if ( $percentDigits < $percentDigitsThreshold ) {
					$sql .= $wpdb->prepare( ' SOUNDEX(term) LIKE SOUNDEX( %s ) ', $term );
					$the_terms[] = $term;
				}

				$count++;
			}

			$sql .= ')';

			$wickedFuzzyTerms = array();

			if ( ! empty( $the_terms ) ) {
				$wickedFuzzyTerms = $wpdb->get_results( $sql, ARRAY_A );

				// These are both terms and stems, and at this point we do not know whether we're stemming.
				// If we are stemming this can cause issues with the main search, when a fuzzy match
				// is a non-stemmed version of a term when we want to stem.
				// As a result we are going to also return the stems along with the terms, just in case.
				if ( ! empty( $wickedFuzzyTerms ) ) {
					$all_terms = array();

					foreach ( $wickedFuzzyTerms as $thisWickedFuzzyTerm ) {
						$all_terms = array_merge( $all_terms, array_unique( array_values( $thisWickedFuzzyTerm ) ) );
					}

					$wickedFuzzyTerms = $all_terms;
				}
			}

			// depending on whether we actually used SOUNDEX, we need to trim out potential results
			// determine whether each match should be included based on how many characters match
			$threshold = absint( apply_filters( 'searchwp_fuzzy_threshold', 70 ) );

			if ( $threshold > 100 ) {
				$threshold = 100;
			}

			// loop through all of the wicked fuzzy terms and pluck out what's really relevant
			$actualTerms = array();
			if ( ! empty( $wickedFuzzyTerms ) ) {
				foreach ( $wickedFuzzyTerms as $wickedFuzzyTerm ) {
					foreach ( $terms as $term ) {

						similar_text( $wickedFuzzyTerm, $term, $percent );

						if ( $percent > $threshold ) {
							$actualTerms[] = $wickedFuzzyTerm;
						}
					}
				}
			}

			// clean up our dupes
			if ( ! empty( $actualTerms ) ) {
				$terms = array_values( array_unique( $actualTerms ) );
				$terms = array_map( 'sanitize_text_field', $terms );
			}
		}

		return $terms;
	}

	/**
	 * When considering partial matches, exact matches should always be given a bonus weight.
	 *
	 * @since 3.1.6
	 *
	 * @param string $sql The incoming search query SQL.
	 *
	 * @return string The modified SQL.
	 */
	public function exact_match_buoy( $sql ) {
		global $wpdb;

		$proceed = apply_filters( 'searchwp_exact_match_buoy', true );

		if ( empty( $proceed ) ) {
			return $sql;
		}

		$prefix         = $wpdb->prefix . SEARCHWP_DBPREFIX;
		$original_terms = SWP()->sanitize_terms( SWP()->original_query );

		// TODO: This hook is what Synonyms uses, but I don't love the overall implementation.
		// This should be easier to work with, and using this 'late' hook may be problematic
		// in other areas of functionality that depend on filtered terms in the same way.
		$terms = apply_filters( 'searchwp_pre_search_terms', $original_terms, $this->engine );

		// Retrieve exact match term IDs.
		$exact_match_term_ids = $wpdb->get_col( $wpdb->prepare( "
			SELECT id
			FROM {$prefix}terms
			WHERE term IN ( " .
				implode( ', ',
					array_fill( 0, count( $terms ), '%s' )
				) .
			" )
		",
		$terms ) );

		if ( empty( $exact_match_term_ids ) ) {
			return $sql;
		}

		$exact_match_post_ids = $wpdb->get_col(
			$wpdb->prepare( "
					SELECT      {$prefix}index.post_id
					FROM        {$prefix}index
					LEFT JOIN   {$prefix}cf
						ON      {$prefix}index.post_id = {$prefix}cf.post_id
					LEFT JOIN   {$prefix}tax
						ON      {$prefix}index.post_id = {$prefix}tax.post_id
					WHERE       {$prefix}index.term IN ( "
									. implode( ', ',
										array_fill( 0, count( $exact_match_term_ids ), '%d' )
									) .
								" )
					GROUP BY    {$prefix}index.post_id
				",
				$exact_match_term_ids
			)
		);

		if ( empty( $exact_match_post_ids ) ) {
			return $sql;
		}

		$sql .= $wpdb->prepare( "
			+ ( IF( {$wpdb->posts}.ID IN ( " .
				implode( ', ',
					array_fill( 0, count( $exact_match_post_ids ), '%d' )
				) .
			" ), 987654321, 0 ) ) ",
			$exact_match_post_ids
		);

		return $sql;
	}
}
