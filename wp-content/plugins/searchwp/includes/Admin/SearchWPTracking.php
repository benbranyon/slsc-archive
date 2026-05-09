<?php
/**
 * Tracking functions for reporting plugin usage to the SearchWP site.
 *
 * @package SearchWP
 *
 * @since 4.5.2
 */

namespace SearchWP\Admin;

use SearchWP\License;
use SearchWP\Utils;
use SearchWP\Settings;
use SearchWP\Forms\Storage as FormsStorage;
use SearchWP\Templates\Storage as TemplatesStorage;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Usage tracking.
 *
 * @since 4.5.2
 *
 * @return void
 */
class SearchWPTracking {

	/**
	 * Usage Tracking endpoint.
	 *
	 * @since 4.5.2
	 *
	 * @var string
	 */
	private $endpoint = 'https://swpusage.com/v1/checkin/';

	/**
	 * Constructor.
	 *
	 * @since 4.5.2
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'init' ] );
		add_filter( 'cron_schedules', [ $this, 'add_schedules' ] );
		add_action( 'searchwp_usage_tracking', [ $this, 'process' ] );
	}

	/**
	 * Initialize the usage tracking.
	 *
	 * @since 4.5.2
	 *
	 * @return void
	 */
	public function init() {

		/**
		 * Whether to disable usage tracking.
		 *
		 * @since 4.5.2
		 *
		 * @param bool $opt_out Whether to disable usage tracking.
		 */
		$opt_out = apply_filters( 'searchwp\disable_usage_tracking', false );

		if ( $opt_out ) {
			// If an event was scheduled, remove it.
			if ( wp_next_scheduled( 'searchwp_usage_tracking' ) ) {
				wp_clear_scheduled_hook( 'searchwp_usage_tracking' );
			}

			// Remove the tracking config and last send option.
			delete_option( 'searchwp_usage_tracking_config' );
			delete_option( 'searchwp_usage_tracking_last_send' );

			return;
		}

		if ( ! wp_next_scheduled( 'searchwp_usage_tracking' ) ) {
			$tracking             = [];
			$tracking['day']      = wp_rand( 0, 6 );
			$tracking['hour']     = wp_rand( 0, 23 );
			$tracking['minute']   = wp_rand( 0, 59 );
			$tracking['second']   = wp_rand( 0, 59 );
			$tracking['offset']   = ( $tracking['day'] * DAY_IN_SECONDS ) +
									( $tracking['hour'] * HOUR_IN_SECONDS ) +
									( $tracking['minute'] * MINUTE_IN_SECONDS ) +
									$tracking['second'];
			$tracking['initsend'] = strtotime( 'next sunday' ) + $tracking['offset'];

			wp_schedule_event( $tracking['initsend'], 'weekly', 'searchwp_usage_tracking' );
			update_option( 'searchwp_usage_tracking_config', $tracking );
		}
	}

	/**
	 * Add weekly schedule.
	 *
	 * @since 4.5.2
	 *
	 * @param array $schedules Existing schedules.
	 *
	 * @return array
	 */
	public function add_schedules( $schedules = [] ) {

		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = [
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'searchwp' ),
		];

		return $schedules;
	}

	/**
	 * Process the usage tracking.
	 *
	 * @since 4.5.2
	 *
	 * @return bool
	 */
	public function process() {

		// Send a maximum of once per week.
		$last_send = get_option( 'searchwp_usage_tracking_last_send' );
		if ( is_numeric( $last_send ) && $last_send > strtotime( '-1 week' ) ) {
			return false;
		}

		wp_remote_post(
			$this->endpoint,
			[
				'method'     => 'POST',
				'blocking'   => false,
				'user-agent' => 'SWP/' . SEARCHWP_VERSION . '; ' . get_bloginfo( 'url' ),
				'headers'    => [
					'Content-Type' => 'application/json; charset=utf-8',
				],
				'body'       => wp_json_encode( $this->get_data() ),
			]
		);

		// If we have completed successfully, recheck in 1 week.
		update_option( 'searchwp_usage_tracking_last_send', time() );

		return true;
	}

	/**
	 * Get the tracking data.
	 *
	 * @since 4.5.2
	 *
	 * @return array
	 */
	private function get_data() {

		// Retrieve current theme info.
		$theme_data = wp_get_theme();

		// Retrieve current plugin information.
		$plugins = $this->get_plugins();

		// Retrieve database details.
		$db_info = Utils::get_db_details();

		$data = [
			// Generic data (environment).
			'url'                 => home_url(),
			'swp_version'         => SEARCHWP_VERSION,
			'license_key'         => License::get_key(),
			'license_type'        => License::get_type(),
			'server'              => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			'database'            => $db_info['engine'] . ' ' . $db_info['version'],
			'php_version'         => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			'php_memory_limit'    => ini_get( 'memory_limit' ),
			'php_time_limit'      => ini_get( 'max_execution_time' ),
			'wp_version'          => get_bloginfo( 'version' ),
			'wp_memory_limit'     => WP_MEMORY_LIMIT,
			'multisite'           => is_multisite(),
			'sites_count'         => function_exists( 'get_blog_count' ) ? get_blog_count() : 1,
			'user_count'          => function_exists( 'get_user_count' ) ? get_user_count() : null,
			'locale'              => get_locale(),
			'theme_name'          => $theme_data->name,
			'theme_version'       => $theme_data->version,
			'is_wp_cron_disabled' => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON,
			// Plugins.
			'active_plugins'      => $plugins['active'],
			'inactive_plugins'    => $plugins['inactive'],
			// SearchWP specific data.
			'index_total'         => $this->get_index_total(),
			'engines_count'       => $this->get_engines_count(),
			'settings'            => $this->get_settings(),
		];

		return $data;
	}

	/**
	 * Get the active and inactive plugins.
	 *
	 * @since 4.5.2
	 *
	 * @return array
	 */
	private function get_plugins() {

		// Retrieve current plugin information.
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', [] );

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				// Remove active plugins from the list so we can show active and inactive separately.
				unset( $plugins[ $key ] );
			}
		}

		return [
			'active'   => $active_plugins,
			'inactive' => $plugins,
		];
	}

	/**
	 * Get the SearchWP engines.
	 *
	 * @since 4.5.2
	 *
	 * @return array|array[]
	 */
	private function get_engines_count() {

		$engines = Settings::get_engines();

		return count( $engines );
	}

	/**
	 * Get the total number of indexed items.
	 *
	 * @since 4.5.2
	 *
	 * @return int|string
	 */
	private function get_index_total() {

		$index_stats = \SearchWP::$index->get_stats();

		return ! empty( $index_stats['total'] ) ? $index_stats['total'] : 0;
	}

	/**
	 * Get the SearchWP settings.
	 *
	 * @since 4.5.2
	 *
	 * @return array|array[]
	 */
	private function get_settings() {

		$keys = [
			'disable_email_summaries',
			'dismissed_notices',
			'document_content_reset',
			'do_suggestions',
			'hide_announcements',
			'highlighting',
			'ignored_queries',
			'indexer_paused',
			'index_outdated',
			'nuke_on_delete',
			'parse_shortcodes',
			'partial_matches',
			'quoted_search_support',
			'reduced_indexer_aggressiveness',
			'remove_min_word_length',
			'tokenize_pattern_matches',
			'trim_stats_logs_after',
		];

		$settings = [];

		foreach ( $keys as $key ) {
			$settings[ $key ] = Settings::get_single( $key, 'boolean' );
		}

		$form_settings = $this->get_forms_settings();

		$template_settings = $this->get_templates_settings();

		return array_merge( $settings, $form_settings, $template_settings );
	}

	/**
	 * Get the SearchWP forms settings.
	 *
	 * @since 4.5.2
	 *
	 * @return array|array[]
	 */
	private function get_forms_settings() {

		$settings = [
			'category-search'              => false,
			'quick-search'                 => false,
			'advanced-search'              => false,
			'search-button'                => false,
			'template-include-search-form' => false,
		];

		$forms = FormsStorage::get_all();

		foreach ( $forms as $form ) {
			foreach ( $settings as $key => $value ) {
				if ( ! empty( $form[ $key ] ) ) {
					$settings[ $key ] = true;
				}
			}
		}

		$settings['total-forms'] = count( $forms );

		return $this->prefix_settings( $settings, 'forms-' );
	}

	/**
	 * Get the SearchWP templates settings.
	 *
	 * @since 4.5.2
	 *
	 * @return array|array[]
	 */
	private function get_templates_settings() {

		$settings = [
			'swp-description-enabled'          => false,
			'swp-button-enabled'               => false,
			'swp-links-open-new-tab-enabled'   => false,
			'swp-media-direct-link-enabled'    => false,
			'swp-total-results-notice-enabled' => false,
			'swp-promoted-ads-enabled'         => false,
		];

		$templates = TemplatesStorage::get_templates();

		foreach ( $templates as $template ) {
			foreach ( $settings as $key => $value ) {
				if ( ! empty( $template[ $key ] ) ) {
					$settings[ $key ] = true;
				}
			}
		}

		$settings['total-templates'] = count( $templates );

		return $this->prefix_settings( $settings, 'templates-' );
	}

	/**
	 * Prefix the settings.
	 *
	 * @since 4.5.2
	 *
	 * @param array  $settings The settings to prefix.
	 * @param string $prefix   The prefix to use.
	 *
	 * @return array|array[]
	 */
	private function prefix_settings( $settings, $prefix ) {

		return array_combine(
			array_map(
				function ( $key ) use ( $prefix ) {
					return $prefix . $key;
				},
				array_keys( $settings )
			),
			array_values( $settings )
		);
	}
}
