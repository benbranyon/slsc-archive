<?php

/**
 * SearchWP Posts Source.
 *
 * @package SearchWP
 * @author  SearchWP
 */

namespace SearchWP\Sources;

use SearchWP\Option;
use SearchWP\Source;
use SearchWP\Entry;
use SearchWP\Utils;
use SearchWP\Query;

/**
 * Class Taxonomy is a Source for WP_Terms.
 *
 * @since 4.3.3
 */
class Taxonomy extends Source {

	/**
	 * The taxonomy name.
	 *
	 * @since 4.3.3
	 * @package SearchWP\Sources
	 * @var string
	 */
	private $taxonomy;

	/**
	 * Column name used to track index status.
	 *
	 * @since 4.3.3
	 * @var   string
	 */
	protected $db_id_column = 'term_id';

	/**
	 * Whether empty taxonomy terms should be excluded.
	 *
	 * @since 4.3.3
	 * @var boolean
	 */
	public $exclude_empty_terms = true;

	/**
	 * Constructor.
	 *
	 * @param string $taxonomy_name
	 * @since 4.3.3
	 */
	function __construct( string $taxonomy_name = 'category' ) {
		global $wpdb;

		$labels = get_taxonomy_labels( get_taxonomy( $taxonomy_name ) );

		$this->labels = [
			'plural'   => $labels->name,
			'singular' => $labels->singular_name,
		];

		$this->exclude_empty_terms = (bool) apply_filters( 'searchwp\source\taxonomy\exclude_empty_terms', true );

		$this->name       = 'taxonomy' . SEARCHWP_SEPARATOR . $taxonomy_name;
		$this->taxonomy   = $taxonomy_name;
		$this->db_table   = $wpdb->term_taxonomy;
		$this->attributes = $this->attributes();
		$this->rules      = $this->rules();
	}

	/**
	 * Restrict available WP_Terms to this taxonomy.
	 *
	 * @since 4.3.3
	 * @return array
	 */
	protected function db_where() {

		$db_where = [
			'relation' => 'AND',
			[   // Only include applicable taxonomy terms.
				'column'  => 'taxonomy',
				'value'   => $this->taxonomy,
			]
		];

		if ( $this->exclude_empty_terms ) {

			$db_where[] = [
				'column'  => 'count',
				'compare' => '!=',
				'value'   => '0'
			];
		}

		return apply_filters( 'searchwp\source\taxonomy\db_where', $db_where, [ 'source' => $this ] );
	}

	/**
	 * Defines the Attributes for this Source.
	 *
	 * @since 4.3.3
	 * @return array
	 */
	protected function attributes() {
		global $wpdb;

		return [
			[ // Term name
				'name'    => 'name',
				'label'   => __( 'Name', 'searchwp' ),
				'default' => Utils::get_max_engine_weight(),
				'data'    => function( $entry_id ) {
					return get_term_field( 'name', $entry_id );
				},
				'phrases' => 'name',
			],
			[ // Term description
				'name'    => 'description',
				'label'   => __( 'Description', 'searchwp' ),
				'default' => Utils::get_min_engine_weight(),
				'data'    => function( $entry_id ) {
					return get_term_field( 'description', $entry_id );
				},
				'phrases' => 'description',
			],
			[ // Term slug
				'name'    => 'slug',
				'label'   => __( 'Slug', 'searchwp' ),
				'default' => Utils::get_min_engine_weight(),
				'data'    => function( $entry_id ) {
					return get_term_field( 'slug', $entry_id );
				},
			],
			[ // Custom Fields
				'name'    => 'meta',
				'label'   => __( 'Custom Fields', 'searchwp' ),
				'notes'   => [
					__( 'Tip: Match multiple keys using * as wildcard and hitting Enter', 'searchwp' ),
				],
				'default' => Utils::get_min_engine_weight(),
				'options' => function( $search = false, array $include = [] ) {
					// If we're retrieving a specific set of options, get them and return.
					if ( ! empty( $include ) ) {
						return array_map( function( $meta_key ) {
							return new Option( (string) $meta_key );
						}, $include );
					}

					return array_map( function( $meta_key ) {
						return new Option( $meta_key );
					}, Utils::get_meta_keys_for_tax_terms( $search ) );
				},
				'allow_custom' => true,
				'data'    => function( $entry_id, $meta_key ) {
					return get_term_meta( $entry_id, $meta_key, false );
				},
				'phrases' => [ [
					'table'  => $wpdb->termmeta,
					'column' => 'meta_value',
					'id'     => 'term_id'
				] ],
			]
		];
	}

	/**
	 * Defines the Rules for this Source.
	 *
	 * @since 4.3.3
	 * @return array
	 */
	protected function rules() {
		return [
			[	// ID.
				'name'        => 'term_id',
				'label'       => __( 'ID', 'searchwp' ),
				'options'     => false,
				'conditions'  => [ 'IN', 'NOT IN' ],
				'application' => function( $properties ) {
					global $wpdb;

					$condition = 'NOT IN' === $properties['condition'] ? 'NOT IN' : 'IN';
					$ids = explode( ',', Utils::get_integer_csv_string_from( $properties['value'] ) );

					return $wpdb->prepare( "SELECT term_id FROM {$wpdb->terms} WHERE term_id {$condition}  ("
						. implode( ',', array_fill( 0, count( $ids ), '%s' ) )
						. ')', $ids );
				},
			]
		];
	}

	/**
	 * Weight Transfer Option options.
	 *
	 * @since 4.3.3
	 * @return array
	 */
	protected function weight_transfer_options() {

		return [
			[
				'option'     => new Option(
					'id',
					sprintf(
						// Translators: placeholder is singular taxonomy label.
						__( 'To %s ID', 'searchwp' ),
						$this->labels['singular']
					)
				),
				'source_map' => function ( $args ) {
					global $wpdb;

					$taxonomy = $this->taxonomy;

					do_action( 'searchwp\debug\log', "Transferring {$this->get_name()} weight to {$taxonomy}:{$args['id']}", 'source' ); // phpcs:ignore WPForms.Comments

					return $wpdb->prepare( '%s', $this->name );
				},
			],
		];
	}

	/**
	 * Maps an Entry for this Source to its native model.
	 *
	 * @since  4.3.3
	 *
	 * @param Entry   $entry       The Entry.
	 * @param Boolean $doing_query Whether a query is being run.
	 *
	 * @return \WP_Term
	 */
	public function entry( Entry $entry, $doing_query = false ) {

		$term = get_term( $entry->get_id() );

		if ( ! $term instanceof \WP_Term ) {
			return $term;
		}

		$highlighter = Utils::is_highlighter_enabled( $entry, $doing_query, 'taxonomy' );

		/**
		 * Determine whether we're going to find a global excerpt based on whether highlighting is enabled.
		 *
		 * @since 4.5.2
		 *
		 * @param boolean $global_excerpt Whether to use a global excerpt.
		 * @param Entry   $entry          The entry to consider.
		 *
		 * @return boolean
		 */
		$global_excerpt = apply_filters( 'searchwp\source\taxonomy\global_excerpt', ! empty( $highlighter ), [ 'entry' => $entry ] );

		/**
		 * Determine whether we're going to find a global excerpt for this source based on whether highlighting is enabled.
		 *
		 * @since 4.5.2
		 *
		 * @param boolean $global_excerpt Whether to use a global excerpt.
		 * @param Entry   $entry          The entry to consider.
		 *
		 * @return boolean
		 */
		$global_excerpt = apply_filters( 'searchwp\source\taxonomy\global_excerpt\\' . $this->taxonomy, $global_excerpt, [ 'entry' => $entry ] );

		if (
			! $term instanceof \WP_Term ||
			! $doing_query instanceof Query ||
			( ! $global_excerpt && ! $highlighter )
		) {
			return $term;
		}

		$search_terms = Utils::get_highlighting_terms( $doing_query, 'taxonomy' );

		// Set the excerpt early if the global excerpt is applicable.
		if ( $global_excerpt ) {
			$term->description = self::get_global_excerpt( $entry, $doing_query, 55, $search_terms );
		}

		// Apply highlights if applicable.
		if ( $highlighter ) {
			$term->name        = $highlighter::apply( $term->name, $search_terms );
			$term->description = $highlighter::apply( $term->description, $search_terms );
		}

		return $term;
	}

	/**
	 * Returns a global excerpt based on the submitted WP_Term. Will check all enabled Attributes.
	 *
	 * @since 4.5.2
	 *
	 * @param Entry        $entry        The entry to consider.
	 * @param string|Query $query        Either the search string or a Query proper.
	 * @param int          $length       The length of the excerpt.
	 * @param array        $search_terms The search terms to look for.
	 *
	 * @return string An excerpt containing (at least) the first search term.
	 */
	public static function get_global_excerpt( Entry $entry, $query, $length = 55, $search_terms = '' ) {

		/**
		 * Fires before the global excerpt is retrieved.
		 */
		do_action( 'searchwp\get_global_excerpt' );

		$term_id = $entry->get_id();
		$term    = get_term( $term_id );

		if ( ! $term instanceof \WP_Term ) {
			return '';
		}

		if ( empty( $search_terms ) ) {
			$search_terms = Utils::get_highlighting_terms( $query, 'post' );
		}

		$context = [
			'search' => $search_terms,
			'term'   => $term,
			'query'  => $query,
		];

		// Try description first.
		$excerpt = self::get_description_excerpt( $term, $search_terms, $length, $context );
		if ( $excerpt ) {
			return $excerpt;
		}

		/**
		 * Filters whether to kill the global excerpt generation.
		 *
		 * @since 4.5.2
		 *
		 * @param boolean $kill_switch Whether to kill the global excerpt.
		 * @param array   $context     The context array.
		 */
		if ( apply_filters( 'searchwp\source\taxonomy\global_excerpt_break', false, $context ) ) {
			return $term->name;
		}

		// Try meta values.
		$meta_excerpt = self::get_meta_excerpt( $entry, $search_terms, $length, $context );
		if ( $meta_excerpt ) {
			return $meta_excerpt;
		}

		// Fallback to description or name.
		return self::get_fallback_excerpt( $term, $search_terms, $length, $context );
	}

	/**
	 * Get excerpt from term description.
	 *
	 * @since 4.5.2
	 *
	 * @param \WP_Term $term         The term object.
	 * @param array    $search_terms The search terms.
	 * @param int      $length       The excerpt length.
	 * @param array    $params       The filter parameters.
	 *
	 * @return string|false The excerpt or false if not found.
	 */
	private static function get_description_excerpt( \WP_Term $term, $search_terms, $length, $params ) {

		$description = isset( $term->description ) ? $term->description : '';

		/**
		 * Filter the description excerpt.
		 *
		 * @since 4.5.2
		 *
		 * @param string $description The description.
		 * @param array  $params      The parameters.
		 */
		$description = apply_filters(
			'searchwp\source\taxonomy\excerpt_haystack',
			$description,
			$params
		);

		$description = Utils::stringify_html( $description );

		if ( ! empty( $description ) && Utils::string_has_substring_from_string( $description, $search_terms ) ) {
			return Utils::trim_string_around_substring(
				$description,
				$search_terms,
				$length
			);
		}

		return false;
	}

	/**
	 * Get excerpt from term meta values.
	 *
	 * @since 4.5.2
	 *
	 * @param Entry $entry        The entry object.
	 * @param array $search_terms The search terms.
	 * @param int   $length       The excerpt length.
	 * @param array $params       The filter parameters.
	 *
	 * @return string|false The excerpt or false if not found.
	 */
	private static function get_meta_excerpt( Entry $entry, $search_terms, $length, $params ) {

		$entry_data = $entry->get_data( true, true );

		if ( empty( $entry_data['meta'] ) || ! is_array( $entry_data['meta'] ) ) {
			return false;
		}

		foreach ( $entry_data['meta'] as $meta_key => $meta_data ) {
			$meta_excerpt = self::process_meta_value( $meta_data, $meta_key, $search_terms, $length, $params );
			if ( $meta_excerpt ) {
				return $meta_excerpt;
			}
		}

		return false;
	}

	/**
	 * Process a single meta value for excerpt generation.
	 *
	 * @since 4.5.2
	 *
	 * @param mixed  $meta_data    The meta data.
	 * @param string $meta_key     The meta key.
	 * @param array  $search_terms The search terms.
	 * @param int    $length       The excerpt length.
	 * @param array  $params       The filter parameters.
	 *
	 * @return string|false The excerpt or false if not found.
	 */
	private static function process_meta_value( $meta_data, $meta_key, $search_terms, $length, $params ) {

		$meta_params = array_merge( $params, [ 'meta_key' => $meta_key ] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key

		/**
		 * Filter the meta value excerpt.
		 *
		 * @since 4.5.2
		 *
		 * @param string $meta_value The meta value.
		 * @param array  $params     The parameters.
		 */
		$meta_value = apply_filters(
			'searchwp\source\taxonomy\excerpt_haystack',
			$meta_data,
			$meta_params
		);

		$meta_value = Utils::get_string_from( $meta_value );

		if ( ! empty( $meta_value ) && Utils::string_has_substring_from_string( $meta_value, $search_terms ) ) {
			$meta_value = Utils::stringify_html( $meta_value );

			return Utils::trim_string_around_substring(
				$meta_value,
				$search_terms,
				$length
			);
		}

		return false;
	}

	/**
	 * Get fallback excerpt from description or term name.
	 *
	 * @since 4.5.2
	 *
	 * @param \WP_Term $term         The term object.
	 * @param array    $search_terms The search terms.
	 * @param int      $length       The excerpt length.
	 * @param array    $params       The filter parameters.
	 *
	 * @return string The fallback excerpt.
	 */
	private static function get_fallback_excerpt( \WP_Term $term, $search_terms, $length, $params ) {

		$description = isset( $term->description ) ? $term->description : '';

		/**
		 * Filter the fallback excerpt.
		 *
		 * @since 4.5.2
		 *
		 * @param string $excerpt The fallback excerpt.
		 * @param array  $params  The parameters.
		 */
		return apply_filters(
			'searchwp\source\taxonomy\excerpt_fallback',
			Utils::trim_string_around_substring(
				$description,
				$search_terms,
				$length
			),
			$params
		);
	}

	/**
	 * Add class hooks.
	 *
	 * @since 4.3.3
	 * @param array $params Parameters.
	 * @return array
	 */
	public function add_hooks( array $params = [] ) {
		if ( ! has_action( 'edited_term', [ $this, 'drop' ] ) ) {
			add_action( "edited_{$this->taxonomy}", [ $this, 'drop' ], 10, 1 );
		}

		if ( ! has_action( 'delete_term', [ $this, 'drop' ] ) ) {
			add_action( "delete_{$this->taxonomy}", [ $this, 'drop' ], 10, 1 );
		}
	}

	/**
	 * Callback to drop a Taxonomy Term from the index.
	 *
	 * @since 4.3.3
	 * @param $entry_id
	 */
	public function drop( $entry_id ) {

		// Drop this entry from the index.
		\SearchWP::$index->drop( $this, $entry_id );
	}
}
