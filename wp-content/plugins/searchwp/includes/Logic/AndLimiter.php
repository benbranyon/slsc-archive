<?php

/**
 * SearchWP AND Limiter.
 *
 * @package SearchWP
 * @author  Jon Christopher
 */

namespace SearchWP\Logic;

use SearchWP\Query;

/**
 * Class AndLimiter is responsible for generating an AND logic clause.
 *
 * @since 4.0
 */
class AndLimiter {

	/**
	 * Query for the limiter.
	 *
	 * @since 4.0
	 * @var Query
	 */
	private $query;

	/**
	 * Whether this limiter is strict.
	 *
	 * @since 4.1
	 * @var bool
	 */
	private $strict;

	/**
	 * AndLimiter constructor.
	 *
	 * @since 4.0
	 */
	function __construct( Query $query, bool $strict = false ) {
		$this->query  = $query;
		$this->strict = $strict;
	}

	/**
	 * Generates and returns the SQL clause for AND logic.
	 *
	 * NOTES: This implementation is as complex as it is primarily due to partial matches, stems, and synonyms.
	 * Ideally we'd be able to simply restrict results to those that have ALL tokens, but when concerning the above
	 * we want to be more advanced than that and allow AND logic to not be cut short by token transformations
	 * that have taken place. This logic will handle these cases by grouping tokens where applicable.
	 *
	 * @since 4.0
	 * @return string
	 */
	public function get_sql() {

		$args    = $this->query->get_args();
		$tokens  = $this->query->get_tokens();
		$site_in = '1=1';

		if ( 'all' !== $this->query->get_args()['site'] ) {
			$site_in = $this->query->get_site_limit_sql();
		}

		$validation_result = $this->validate_tokens( $tokens );
		if ( $validation_result !== null ) {
			return $validation_result;
		}

		// AND logic is based on token groups. In order for AND logic to be satisfied there
		// must be a match for all token groups. A token group consists of a token and its
		// keyword stem and any partial matches when applicable.

		// Create initial token groups.
		$token_groups = $this->create_initial_token_groups( $tokens );

		// Group tokens based on stemming if applicable.
		$token_groups = $this->group_tokens_by_stems( $token_groups, $tokens );

		// Group tokens based on partial matches if applicable.
		$token_groups = $this->group_tokens_by_partial_matches( $token_groups, $tokens );

		// Group tokens based on synonyms if applicable.
		$token_groups = $this->group_tokens_by_synonyms( $token_groups, $tokens );

		// Validate multi-word synonyms.
		$validation_result = $this->validate_multi_word_synonyms( $token_groups );
		if ( $validation_result !== null ) {
			return $validation_result;
		}

		// Validate single-word to multi-word synonyms.
		$validation_result = $this->validate_single_word_to_multi_word_synonyms( $token_groups );
		if ( $validation_result !== null ) {
			return $validation_result;
		}

		// Validate token group count.
		$validation_result = $this->validate_token_group_count( $token_groups );
		if ( $validation_result !== null ) {
			return $validation_result;
		}

		$this->query->set_debug_data( 'tokengroups.and', $token_groups );

		$token_groups = array_values( $token_groups );

		// Check token threshold.
		$validation_result = $this->check_token_threshold( $token_groups, $tokens );
		if ( $validation_result !== null ) {
			return $validation_result;
		}

		// Build and return the SQL query.
		return $this->build_and_sql_query( $token_groups, $tokens, $args, $site_in );
	}

	/**
	 * Validates tokens and returns early if invalid token found.
	 *
	 * @since 4.5.7
	 *
	 * @param array $tokens The tokens to validate.
	 *
	 * @return string|null Returns '1=0' if invalid token found, null to continue.
	 */
	private function validate_tokens( array $tokens ) {
		// If there's an invalid token, AND logic fails. Bail out and short circuit.
		if ( array_key_exists( 0, $tokens ) ) {
			return '1=0';
		}

		return null;
	}

	/**
	 * Creates initial token groups from tokens array.
	 *
	 * @since 4.5.7
	 *
	 * @param array $tokens The tokens to create groups from.
	 *
	 * @return array Array of token groups.
	 */
	private function create_initial_token_groups( array $tokens ) {

		return array_map(
			function ( $token ) {
				return [ $token ];
			},
			array_flip( $tokens )
		);
	}

	/**
	 * Groups tokens by stemming if applicable.
	 *
	 * @since 4.5.7
	 *
	 * @param array $token_groups The token groups to modify.
	 * @param array $tokens       The tokens array.
	 *
	 * @return array Modified token groups array.
	 */
	private function group_tokens_by_stems( array $token_groups, array $tokens ) {

		if ( ! $this->query->use_stems ) {
			return $token_groups;
		}

		$index             = \SearchWP::$index;
		$stem_token_groups = $index->group_tokens_by_stem_from_tokens( array_keys( $tokens ) );

		foreach ( $stem_token_groups as $stem_token_group ) {
			foreach ( $stem_token_group as $stem_token ) {
				if ( isset( $token_groups[ $tokens[ $stem_token ] ] ) ) {
					unset( $token_groups[ $tokens[ $stem_token ] ] );
				}
			}
			$token_groups[ $tokens[ $stem_token_group[0] ] ] = $stem_token_group;
		}

		return $token_groups;
	}

	/**
	 * Groups tokens based on partial matches.
	 *
	 * @since 4.5.7
	 *
	 * @param array $token_groups The token groups to modify.
	 * @param array $tokens       The tokens array.
	 *
	 * @return array Modified token groups array.
	 */
	private function group_tokens_by_partial_matches( array $token_groups, array $tokens ) {
		// If we're dealing with partial matches we can further group the groups.
		/**
		 * Filters whether partial matches should be used.
		 *
		 * @since 4.0
		 *
		 * @param bool  $partial_matches Whether to use partial matches.
		 * @param array $args            Additional arguments including 'tokens' and 'query'.
		 */
		if ( ! apply_filters(
			'searchwp\query\partial_matches',
			\SearchWP\Settings::get_single( 'partial_matches', 'boolean' ),
			[
				'tokens' => $tokens,
				'query'  => $this->query,
			]
		) ) {
			return $token_groups;
		}

		// Rebuild the token groups based on partial matches from the original search string.
		$raw_token_groups = $token_groups;
		$token_groups     = [];

		$original_search_tokens = \SearchWP\Utils::tokenize( $this->query->get_keywords() )->get();

		foreach ( $original_search_tokens as $token ) {
			foreach ( $raw_token_groups as $raw_token_group_tokens ) {
				$token_groups = $this->process_partial_match_token_group(
					$token,
					$raw_token_group_tokens,
					$tokens,
					$raw_token_groups,
					$token_groups
				);
			}
		}

		return $token_groups;
	}

	/**
	 * Processes a token group for partial matches.
	 *
	 * @since 4.5.7
	 *
	 * @param string $token                  The token to process.
	 * @param array  $raw_token_group_tokens The raw token group tokens.
	 * @param array  $tokens                 The tokens array.
	 * @param array  $raw_token_groups       The raw token groups.
	 * @param array  $token_groups           The token groups to modify.
	 *
	 * @return array Modified token groups.
	 */
	private function process_partial_match_token_group( string $token, array $raw_token_group_tokens, array $tokens, array &$raw_token_groups, array $token_groups ) {

		foreach ( $raw_token_group_tokens as $raw_token_group_token ) {
			if ( false === stripos( $tokens[ $raw_token_group_token ], $token ) ) {
				continue;
			}

			if ( ! array_key_exists( $token, $raw_token_groups ) ) {
				$raw_token_groups[ $token ] = [];
			}

			if ( ! array_key_exists( $token, $token_groups ) ) {
				$token_groups[ $token ] = $raw_token_groups[ $token ];
			}

			$token_groups[ $token ] = array_unique(
				array_merge(
					(array) $token_groups[ $token ],
					$raw_token_group_tokens
				)
			);
		}

		return $token_groups;
	}

	/**
	 * Groups tokens based on synonyms.
	 *
	 * @since 4.5.7
	 *
	 * @param array $token_groups The token groups to modify.
	 * @param array $tokens       The tokens array.
	 *
	 * @return array Modified token groups array.
	 */
	private function group_tokens_by_synonyms( array $token_groups, array $tokens ) {

		$synonyms_token_groups = Synonyms::get_synonym_groups( $this->query );

		// Rebuild the token groups based on synonyms.
		if ( empty( $synonyms_token_groups ) ) {
			return $token_groups;
		}

		foreach ( $synonyms_token_groups as $synonyms_token_group => $synonym_tokens ) {

			// If there are no synonyms tokens we can skip this.
			if ( empty( $synonym_tokens ) ) {
				continue;
			}

			$token_groups = $this->process_synonym_token_group(
				$synonyms_token_group,
				$synonym_tokens,
				$tokens,
				$token_groups
			);
		}

		// Remove empty token groups.
		$token_groups = array_filter( $token_groups );

		// Sort token groups.
		$token_groups = array_map(
			function ( $token_group ) {
				$tmp_group = $token_group;
				sort( $tmp_group );

				return $tmp_group;
			},
			$token_groups
		);

		// Remove duplicated groups.
		$token_groups = array_unique( $token_groups,SORT_REGULAR );

		return $token_groups;
	}

	/**
	 * Processes a synonym token group.
	 *
	 * @since 4.5.7
	 *
	 * @param string $synonyms_token_group The synonym token group key.
	 * @param array  $synonym_tokens       The synonym tokens.
	 * @param array  $tokens               The tokens array.
	 * @param array  $token_groups         The token groups to modify.
	 *
	 * @return array Modified token groups.
	 */
	private function process_synonym_token_group( string $synonyms_token_group, array $synonym_tokens, array $tokens, array $token_groups ) {

		$token_id = array_search( (string) $synonyms_token_group, $tokens, true );

		if ( ! empty( $token_id ) ) {
			if ( ! isset( $token_groups[ $synonyms_token_group ] ) ) {
				$token_groups[ $synonyms_token_group ]   = [];
				$token_groups[ $synonyms_token_group ][] = (string) $token_id;
			}
		}

		if ( ! empty( $synonym_tokens ) ) {
			foreach ( $synonym_tokens as $synonym_token ) {
				if ( array_key_exists( $synonym_token, $token_groups ) ) {
					unset( $token_groups[ $synonym_token ] );
				}
				$token_id = array_search( $synonym_token, $tokens, true );
				if ( ! empty( $token_id ) ) {
					$token_groups[ $synonyms_token_group ][] = (string) $token_id;
				}
			}
		}

		return $token_groups;
	}

	/**
	 * Checks if any token group key matches a multi-word replacement.
	 *
	 * @since 4.5.7
	 *
	 * @param array $token_group_keys        The token group keys to check.
	 * @param array $multi_word_replacements The multi-word replacements to check against.
	 *
	 * @return bool True if any token group key matches a multi-word replacement, false otherwise.
	 */
	private function has_multi_word_replacement_in_groups( array $token_group_keys, array $multi_word_replacements ) {

		foreach ( $token_group_keys as $token_group_key ) {
			if ( in_array( $token_group_key, $multi_word_replacements, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if a keyword is accounted for (either has a token group or was part of a multi-word replacement).
	 *
	 * @since 4.5.7
	 *
	 * @param string $keyword                  The keyword to check.
	 * @param array  $token_groups             The token groups to check against.
	 * @param array  $multi_word_source_tokens The multi-word source tokens to check against.
	 *
	 * @return bool True if the keyword is accounted for, false otherwise.
	 */
	private function is_keyword_accounted_for( string $keyword, array $token_groups, array $multi_word_source_tokens ) {
		// Check if this keyword has a matching token group.
		if ( array_key_exists( $keyword, $token_groups ) ) {
			return true;
		}

		// Check if this keyword was part of a multi-word replacement.
		if ( in_array( $keyword, $multi_word_source_tokens, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Validates that all original keywords are accounted for in multi-word synonym scenarios.
	 *
	 * @since 4.5.7
	 *
	 * @param array $token_groups The token groups to validate.
	 *
	 * @return string|null Returns '1=0' if validation fails in strict mode, null to continue.
	 */
	private function validate_multi_word_synonyms( array $token_groups ) {
		// Validate that all original keywords are accounted for when we have multi-word replacements.
		// This check must happen regardless of token group count to ensure strict AND logic works correctly.
		$multi_word_replacements = Synonyms::get_multi_word_synonym_replacements( $this->query );
		if ( empty( $multi_word_replacements ) ) {
			return null;
		}

		// Get original keywords (tokenized and normalized to match token group format).
		$original_keywords_raw  = $this->query->get_keywords( true );
		$original_keywords      = \SearchWP\Utils::tokenize( $original_keywords_raw )->get();
		$original_keyword_count = count( $original_keywords );

		// If we had multiple original keywords, verify all are accounted for.
		if ( $original_keyword_count < 2 ) {
			return null;
		}

		$multi_word_source_tokens = Synonyms::get_multi_word_synonym_source_tokens( $this->query );
		$token_group_keys         = array_keys( $token_groups );

		// Check if any token group key matches a multi-word replacement.
		if ( ! $this->has_multi_word_replacement_in_groups( $token_group_keys, $multi_word_replacements ) ) {
			return null;
		}

		// Check each original keyword to ensure it's either:
		// 1. Has a matching token group, OR
		// 2. Was part of a multi-word replacement (exists in tracked source tokens).
		$all_keywords_accounted = true;
		foreach ( $original_keywords as $original_keyword ) {
			if ( ! $this->is_keyword_accounted_for( $original_keyword, $token_groups, $multi_word_source_tokens ) ) {
				$all_keywords_accounted = false;
				break;
			}
		}

		// If not all keywords are accounted for, AND logic should fail in strict mode.
		if ( ! $all_keywords_accounted && $this->strict ) {
			return '1=0';
		}

		return null;
	}

	/**
	 * Validates single-word to multi-word synonym replacements.
	 *
	 * When a single-word source is replaced with multiple words (e.g., "JTI" → "Japan Tobacco International"),
	 * ensures all resulting tokens have matching token groups. If any token is missing, AND logic fails.
	 *
	 * @since 4.5.7
	 *
	 * @param array $token_groups The token groups to validate.
	 *
	 * @return string|null Returns '1=0' if validation fails, null to continue.
	 */
	private function validate_single_word_to_multi_word_synonyms( array $token_groups ) {
		// Check for single-word to multi-word replacements.
		$single_to_multi_replacements = Synonyms::get_single_word_to_multi_word_replacements( $this->query );
		if ( empty( $single_to_multi_replacements ) ) {
			return null;
		}

		// Get original keywords (tokenized and normalized to match token group format).
		$original_keywords_raw  = $this->query->get_keywords( true );
		$original_keywords      = \SearchWP\Utils::tokenize( $original_keywords_raw )->get();
		$original_keyword_count = count( $original_keywords );

		// This validation is specifically for single-word to multi-word replacements.
		// If we have multiple original keywords, this isn't the scenario we're handling here.
		if ( $original_keyword_count !== 1 ) {
			return null;
		}

		$token_group_keys = array_keys( $token_groups );

		// Check if all replacement tokens have matching token groups.
		// When a single word expands to multiple tokens, all must have token groups for AND logic to work.
		$all_replacement_tokens_accounted = true;
		foreach ( $single_to_multi_replacements as $replacement_token ) {
			if ( ! in_array( $replacement_token, $token_group_keys, true ) ) {
				$all_replacement_tokens_accounted = false;
				break;
			}
		}

		// If not all replacement tokens are accounted for, AND logic should fail.
		if ( ! $all_replacement_tokens_accounted ) {
			return $this->strict ? '1=0' : null;
		}

		// If we have 2+ token groups from a single-word expansion, AND logic should be applied.
		// This ensures the query doesn't fall through when it should require all tokens.
		if ( count( $token_groups ) >= 2 ) {
			// AND logic will be applied in the normal flow, so return null to continue.
			return null;
		}

		return null;
	}

	/**
	 * Validates that there are enough token groups for AND logic.
	 *
	 * @since 4.5.7
	 *
	 * @param array $token_groups The token groups to validate.
	 *
	 * @return string|null Returns '1=0', '', or null based on validation.
	 */
	private function validate_token_group_count( array $token_groups ) {
		// If there are fewer than two token groups AND logic will fail. The main case here
		// is with keyword stemming: a search for a single token can yield a search for that
		// token and its stems, but once we get here that has been regrouped back into a single
		// token group which would fail AND logic because there's only one group. Returning
		// an empty string here (as opposed to an invalidator like 1=0) allows the query to
		// "pass through" and operate as expected given the additional, generated tokens.
		//
		// However, we need to distinguish between:
		// 1. Multiple keywords → synonym replacement → single token (should NOT fail AND logic)
		// 2. Multiple keywords → only one token exists (should fail AND logic).
		// 3. Single keyword → synonym replacement → multiple tokens (should apply AND logic).
		if ( count( $token_groups ) >= 2 ) {
			return null;
		}

		// Get original keywords (tokenized and normalized to match token group format).
		$original_keywords_raw  = $this->query->get_keywords( true );
		$original_keywords      = \SearchWP\Utils::tokenize( $original_keywords_raw )->get();
		$original_keyword_count = count( $original_keywords );

		// If we had multiple original keywords but only one token group, check if it's a synonym replacement.
		if ( $original_keyword_count >= 2 ) {
			$multi_word_replacements_check = Synonyms::get_multi_word_synonym_replacements( $this->query );
			$token_group_keys_check        = array_keys( $token_groups );

			// Check if any token group key matches a multi-word replacement.
			foreach ( $token_group_keys_check as $token_group_key ) {
				if ( in_array( $token_group_key, $multi_word_replacements_check, true ) ) {
					// This is a valid multi-word synonym replacement, allow pass-through.
					return '';
				}
			}
		}

		// If we had a single original keyword but only one token group, check if it expanded to multiple words.
		// This case is already handled by validate_single_word_to_multi_word_synonyms(), so we can proceed.
		if ( $original_keyword_count === 1 ) {
			$single_to_multi_replacements = Synonyms::get_single_word_to_multi_word_replacements( $this->query );
			if ( ! empty( $single_to_multi_replacements ) ) {
				// Single word expanded to multiple, but we only have one token group.
				// This means not all replacement tokens matched, which is handled by validate_single_word_to_multi_word_synonyms().
				// Allow pass-through here to let the normal flow handle it.
				return '';
			}
		}

		// One tokenized keyword with stemming collapsed to a single group (multiple index IDs, same stem).
		if (
			$this->query->use_stems
			&& $original_keyword_count === 1
			&& count( $token_groups ) === 1
		) {
			return '';
		}

		// Not a synonym replacement scenario, apply strict logic if enabled.
		return $this->strict ? '1=0' : '';
	}

	/**
	 * Checks if token groups exceed threshold.
	 *
	 * @since 4.5.7
	 *
	 * @param array $token_groups The token groups to check.
	 * @param array $tokens       The tokens array.
	 *
	 * @return string|null Returns '1=0' if threshold exceeded, null to continue.
	 */
	private function check_token_threshold( array $token_groups, array $tokens ) {
		// If there are too many token groups the query can get troublesome.
		$token_threshold = apply_filters( 'searchwp\query\logic\and\token_threshold', 5, [
			'tokens' => $tokens,
			'query'  => $this->query,
		] );

		if ( $token_threshold && count( $token_groups ) > $token_threshold ) {
			do_action( 'searchwp\debug\log', 'Skipping AND logic pass, too many token groups (' . count( $token_groups ) . ') use searchwp\query\logic\and\token_threshold filter to override current threshold (' . $token_threshold . ')', 'query' );

			// Force AND logic failure.
			return '1=0';
		}

		return null;
	}

	/**
	 * Builds the final SQL query with subqueries.
	 *
	 * @since 4.5.7
	 *
	 * @param array  $token_groups The token groups to build the query from.
	 * @param array  $tokens       The tokens array.
	 * @param array  $args         The query args.
	 * @param string $site_in      The site limit SQL.
	 *
	 * @return string The final SQL string.
	 */
	private function build_and_sql_query( array $token_groups, array $tokens, array $args, string $site_in ) {

		global $wpdb;

		$index = \SearchWP::$index;

		do_action( 'searchwp\debug\log', 'Trying AND logic', 'query' );

		// These limiters build on one another, and piggyback a parent condition for the first token
		// in the array, which is why the $token_limiters is off by one; we need that 'parent' clause
		// to establish the AND logic limiter itself, so we're structuring the children first.
		$token_limiters = implode( ' AND ', array_map( function( $token_group ) use ( $index, $site_in ) {
			return "id IN (
				SELECT id
				FROM {$index->get_tables()['index']->table_name} s
				WHERE {$site_in}
					AND {$this->get_engine_source_attribute_where_sql()}
					AND token IN ("
					. implode( ', ', array_fill( 0, count( $token_group ), '%d' ) )
					. ') GROUP BY id)';
		}, array_slice( $token_groups, 1 ) ) ); // Off by one because the 'parent' below uses that.

		// Build values array for preparation.
		$values = call_user_func_array( 'array_merge', array_map( function( $tokens ) use ( $args ) {
			if ( 'all' !== $this->query->get_args()['site'] ) {
				return array_merge( $args['site'], $tokens );
			} else {
				return $tokens;
			}
		}, $token_groups ) );

		$and_sql_subquery = "
			SELECT id
			FROM (" .
				str_replace( $this->query->get_placeholder(), '%', $wpdb->prepare("
					SELECT id
					FROM {$index->get_tables()['index']->table_name} s
					WHERE {$site_in}
						AND {$this->get_engine_source_attribute_where_sql()}
						AND token IN ("
							. implode( ', ', array_fill( 0, count( $token_groups[0] ), '%d' ) )
						. ")
						AND {$token_limiters}
					GROUP BY id",
					$values
				) )
			. ') AS a';

		// This subquery could get large, so we're going to pre-execute by default.
		if ( apply_filters( 'searchwp\query\logic\and\pre_execute', true ) ) {

			$and_time_start  = microtime( true );
			$and_ids         = $wpdb->get_col( $and_sql_subquery );
			$and_time_finish = number_format( microtime( true ) - $and_time_start, 5 );

			// Log the data only if the query was pre-executed.
			$this->query->set_debug_data( 'subqueries.and.query', $and_sql_subquery );
			$this->query->set_debug_data( 'subqueries.and.time', $and_time_finish );
			$this->query->set_debug_data( 'subqueries.and.results', $and_ids );

			// If there are many AND results we're looking at a performance hit we can avoid.
			// With that many results the query is going to take longer to run, so we're going
			// to rely on the overall relevance of OR logic here if possible e.g. not strict logic.
			$max_threshold = apply_filters( 'searchwp\query\logic\and\max_threshold', 100 );
			if ( ! $this->strict && count( $and_ids ) > absint( $max_threshold ) ) {
				$and_sql = "{$index->get_alias()}.id IN ({$and_sql_subquery})";
			} else {
				if ( empty( $and_ids ) ) {
					// Force no results.
					$and_sql = '1=0';
				}
				else {
					$and_sql = $wpdb->prepare( "{$index->get_alias()}.id IN ("
						. implode( ', ', array_fill( 0, count( $and_ids ), '%s' ) )
						. ')', $and_ids );
				}
			}
		} else {
			$and_sql = "{$index->get_alias()}.id IN ({$and_sql_subquery})";
		}

		return $and_sql;
	}

	/**
	 * Generate the WHERE clause that limits AND logic to only the Sources/Attributes for this Engine.
	 * If we don't limit to the applicable Source Attributes we can end up with results that satisfy AND
	 * logic from another Engine that is not the one used for this query.
	 *
	 * @since 4.1
	 * @return string
	 */
	protected function get_engine_source_attribute_where_sql() {
		global $wpdb;

		$where  = [];
		$values = [];
		$index_alias   = \SearchWP::$index->get_alias();
		$engines       = \SearchWP\Settings::get( 'engines' );
		$engine_config = array_filter(
			\SearchWP\Utils::normalize_engine_source_settings( $this->query->get_engine() ),
			function( $source ) {
				return ! empty( $source['attributes'] );
			}
		);

		// Potential performance gain if there is only one Engine.
		if ( 1 === count( array_keys( $engines ) ) ) {
			do_action( 'searchwp\debug\log', 'Skipping AND logic Source Attribute consideration (single Engine)', 'query' );
			return '1=1';
		}

		// Potential performance gain if Source Attributes are the same across all Engines.
		$source_attribute_consideration_necessary = false;
		foreach ( $engine_config as $source_name => $source_config ) {
			$source_attributes = $source_config['attributes'];

			foreach ( $engines as $engine => $this_engine_config ) {
				if ( $this->query->get_engine()->get_name() === $engine ) {
					continue;
				}

				$this_engine_config = \SearchWP\Utils::normalize_engine_source_settings( new \SearchWP\Engine( $engine ) );

				foreach ( $this_engine_config as $this_engine_source_name => $this_engine_source_config ) {
					$this_engine_source_attributes = $this_engine_source_config['attributes'];

					// If all of the Source Attributes of the Engine not being used for this query are also
					// present in the Source Attributes for the Engine being used, we can still can proceed.
					foreach ( array_keys( $this_engine_source_attributes ) as $this_engine_source_attribute_name ) {
						if ( ! in_array( $this_engine_source_attribute_name, array_keys( $source_attributes), true ) ) {
							$source_attribute_consideration_necessary = true;
						}
					}
				}
			}
		}

		// Potential performance gain if consideration is not necessary or developer wants less strict AND logic.
		if ( ! apply_filters( 'searchwp\query\logic\and\consider_source_attributes', $source_attribute_consideration_necessary ) ) {
			do_action( 'searchwp\debug\log', 'Skipping AND logic Source Attribute consideration', 'query' );
			return '1=1';
		}

		foreach ( $engine_config as $source => $settings ) {
			$where[] = "{$index_alias}.source = %s AND"
				. $this->query->get_source_attributes_as_where_sql( array_keys( $settings['attributes'] ) );

			$values[] = array_merge(
				[ $source ],
				$this->query->get_source_attributes_as_values( array_keys( $settings['attributes'] ) )
			);
		}

		if ( empty( $where ) ) {
			$sql = '1=1';
		} else {
			$sql = $wpdb->prepare(
				'(' . implode( ' OR ', call_user_func( 'array_merge', $where ) ) . ')',
				call_user_func_array( 'array_merge', $values )
			);
		}

		return $sql;
	}
}
