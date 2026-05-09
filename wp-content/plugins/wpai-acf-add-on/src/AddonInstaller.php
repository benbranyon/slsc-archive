<?php

namespace PMXI\AddonInstaller;

use Exception;
use Plugin_Installer_Skin;
use Plugin_Upgrader;
use WP_Error;

/**
 * PMXI Addon Installer SDK
 * 
 * A reusable SDK for installing and managing free plugin dependencies for pro plugins.
 */
class AddonInstaller {

	/**
	 * Configuration for the addon being managed.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * The detected addon version.
	 *
	 * @var string
	 */
	protected $addon_version = '0';

	/**
	 * The detected addon plugin file.
	 *
	 * @var string
	 */
	protected $addon_file;

	/**
	 * The detected addon directory.
	 *
	 * @var string
	 */
	protected $addon_dir;

	/**
	 * Constructor.
	 *
	 * @param array $config Configuration array with the following keys:
	 *   - 'addon_name' (string): Display name of the free addon (e.g., 'ACF Add-On Free')
	 *   - 'addon_slug' (string): WordPress.org plugin slug (e.g., 'csv-xml-import-for-acf')
	 *   - 'addon_author' (string): Plugin author name (e.g., 'Soflyy')
	 *   - 'minimum_version' (string): Minimum required version (e.g., '1.0.4')
	 *   - 'pro_plugin_name' (string): Name of the pro plugin (e.g., 'WP All Import - ACF Add-On Pro')
	 *   - 'pro_plugin_file' (string): Pro plugin main file path
	 *   - 'textdomain' (string): Text domain for translations
	 *   - 'version_constant' (string, optional): Constant name that holds the addon version
	 *   - 'edition_constant' (string, optional): Constant name that holds the edition type
	 *   - 'expected_edition' (string, optional): Expected edition value (default: 'free')
	 *   - 'free_plugin_file' (string, optional): Free plugin filename if different from plugin.php
	 *   - 'disable_deactivation' (bool, optional): Whether to prevent deactivation of the free plugin
	 *   - 'send_email_alert' (bool, optional): Whether to send email alerts for failures (default: true)
	 */
	public function __construct( array $config ) {
		$this->config = $this->validate_config( $config );
		
		// Use custom filename or default to plugin.php
		$plugin_filename = $this->config['free_plugin_file'] ?? 'plugin.php';
		$this->addon_file = $this->config['addon_slug'] . '/' . $plugin_filename;
		$this->addon_dir = WP_PLUGIN_DIR . '/' . $this->config['addon_slug'];
		
		// Initialize hooks for import blocking
		$this->init_import_blocking_hooks();
		
		// Initialize CLI hooks for preventing deactivation
		$this->init_cli_hooks();
	}

	/**
	 * Validates and sets defaults for the configuration.
	 *
	 * @param array $config Configuration array.
	 * @return array Validated configuration.
	 * @throws Exception If required configuration is missing.
	 */
	protected function validate_config( array $config ) {
		$required = [
			'addon_name',
			'addon_slug', 
			'addon_author',
			'minimum_version',
			'pro_plugin_name',
			'pro_plugin_file',
			'textdomain'
		];

		foreach ( $required as $key ) {
			if ( empty( $config[ $key ] ) ) {
				throw new Exception( "Required configuration key '{$key}' is missing." );
			}
		}

		// Set defaults for optional parameters
		$defaults = [
			'expected_edition' => 'free',
			'disable_deactivation' => false,
			'send_email_alert' => true
		];
		
		foreach ( $defaults as $key => $default_value ) {
			if ( ! isset( $config[ $key ] ) ) {
				$config[ $key ] = $default_value;
			}
		}
		
		// Ensure boolean values are properly cast
		$config['disable_deactivation'] = (bool) $config['disable_deactivation'];
		$config['send_email_alert'] = (bool) $config['send_email_alert'];

		return $config;
	}

	/**
	 * Initialize CLI-specific hooks.
	 *
	 * @return void
	 */
	protected function init_cli_hooks() {
		if ( $this->is_cli_context() ) {
			// Hook into CLI plugin deactivation to prevent or immediately reactivate
			add_action( 'deactivated_plugin', array( $this, 'handle_cli_deactivation' ), 10, 2 );
			
			// Also check on every CLI operation that might run imports
			add_action( 'wp_loaded', array( $this, 'ensure_cli_addon_activation' ), 5 );
		}
	}

	/**
	 * Handles plugin deactivation in CLI context.
	 *
	 * @param string $plugin Plugin file that was deactivated.
	 * @param bool   $network_deactivating Whether the plugin is being network deactivated.
	 * @return void
	 */
	public function handle_cli_deactivation( $plugin, $network_deactivating ) {
		if ( $plugin === $this->addon_file && $this->config['disable_deactivation'] ) {
			// Immediately reactivate the plugin
			$this->reactivate_addon_silently();
		}
	}

	/**
	 * Ensures addon is activated in CLI context before imports.
	 *
	 * @return void
	 */
	public function ensure_cli_addon_activation() {
		if ( ! $this->is_cli_context() ) {
			return;
		}
		
		// If we're about to run an import operation, ensure addon is active
		if ( $this->is_import_operation() && ! is_plugin_active( $this->addon_file ) ) {
			$this->reactivate_addon_silently();
		}
	}

	/**
	 * Reactivates the addon silently without output.
	 *
	 * @return void
	 */
	protected function reactivate_addon_silently() {
		if ( ! function_exists( 'activate_plugin' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		// Simple activation - WordPress handles translation timing internally
		$result = activate_plugin( $this->addon_file, '', false, true );
		
		// If activation failed, try network activation
		if ( is_wp_error( $result ) ) {
			activate_plugin( $this->addon_file, '', true, true );
		}
	}

	/**
	 * Checks if this is an import operation.
	 *
	 * @return bool
	 */
	protected function is_import_operation() {
		global $argv;
		
		if ( ! is_array( $argv ) ) {
			return false;
		}
		
		$command_line = implode( ' ', $argv );
		
		// Check for common import-related operations
		$import_indicators = [
			'wp-all-import',
			'pmxi',
			'import',
			'cron',
			'scheduled'
		];
		
		foreach ( $import_indicators as $indicator ) {
			if ( strpos( $command_line, $indicator ) !== false ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Initialize hooks to block imports when dependencies are not met.
	 *
	 * @return void
	 */
	protected function init_import_blocking_hooks() {
		// Hook to block manual imports
		add_action( 'pmxi_before_xml_import', array( $this, 'block_manual_import_if_dependency_missing' ), 1, 1 );
		
		// Hook to block scheduled imports (early in the process)
		add_action( 'init', array( $this, 'block_scheduled_import_if_dependency_missing' ), 1 );
		
		// Additional safety hook for any PMXI operations
		add_action( 'pmxi_before_post_import', array( $this, 'block_import_operation_if_dependency_missing' ), 1, 2 );
	}

	/**
	 * Blocks manual imports if dependency is missing.
	 *
	 * @param int $import_id Import ID.
	 * @return void
	 */
	public function block_manual_import_if_dependency_missing( $import_id ) {
		$this->block_if_dependency_missing( 'manual_import' );
	}

	/**
	 * Blocks scheduled imports if dependency is missing.
	 *
	 * @return void
	 */
	public function block_scheduled_import_if_dependency_missing() {
		// Check if this is a scheduling request
		if ( ! $this->is_scheduling_request() ) {
			return;
		}
		
		$this->block_if_dependency_missing( 'scheduled_import' );
	}

	/**
	 * Additional safety block for any import operations.
	 *
	 * @param int   $import_id Import ID.
	 * @param array $import Import data.
	 * @return void
	 */
	public function block_import_operation_if_dependency_missing( $import_id, $import = null ) {
		$this->block_if_dependency_missing( 'import_operation' );
	}

	/**
	 * Unified method to block operations if dependency is missing.
	 *
	 * @param string $operation_type Type of operation being blocked.
	 * @return void
	 */
	protected function block_if_dependency_missing( $operation_type ) {
		if ( ! $this->is_addon_up_to_date() ) {
			// Only attempt installation if user has proper permissions (or we're in CLI)
			if ( $this->can_install_automatically() ) {
				// Attempt to install dependency - if successful, allow import to continue
				if ( $this->attempt_dependency_installation() ) {
					// Dependencies are now satisfied, allow import to continue
					return;
				}
			}
			
			// Installation failed or not permitted, block the import
			$this->send_failure_notification_once();
			
			$this->handle_operation_blocking( $operation_type );
		}
	}

	/**
	 * Handles blocking of different operation types.
	 *
	 * @param string $operation_type Type of operation being blocked.
	 * @return void
	 */
	protected function handle_operation_blocking( $operation_type ) {
		switch ( $operation_type ) {
			case 'manual_import':
				$this->block_manual_import();
				break;
			case 'scheduled_import':
				$this->block_scheduled_import();
				break;
			case 'import_operation':
				throw new Exception( $this->get_dependency_error_message() );
				break;
		}
	}

	/**
	 * Blocks manual import operations.
	 *
	 * @return void
	 */
	protected function block_manual_import() {
		// For admin UI, show HTML error
		if ( is_admin() && ! wp_doing_ajax() ) {
			wp_die( 
				$this->get_dependency_error_html(),
				$this->safe_translate( 'Dependency Missing', 'Dependency Missing' ),
				array( 'response' => 400 )
			);
		}
		
		// For AJAX requests, send JSON response
		if ( wp_doing_ajax() ) {
			wp_send_json_error( array(
				'message' => $this->get_dependency_error_message()
			), 400 );
		}
		
		// Fallback - just die with message
		die( $this->get_dependency_error_message() );
	}

	/**
	 * Blocks scheduled import operations.
	 *
	 * @return void
	 */
	protected function block_scheduled_import() {
		// Send proper JSON response for scheduling
		if ( ! headers_sent() ) {
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			http_response_code( 424 ); // Failed Dependency
		}
		
		echo wp_json_encode( array(
			'status' => 424,
			'message' => $this->get_dependency_error_message(),
			'error' => 'dependency_missing'
		) );
		
		exit;
	}

	/**
	 * Safely translates a string or returns fallback if translations cause issues.
	 *
	 * @param string $text Text to translate.
	 * @param string $fallback Fallback text if translation isn't safe.
	 * @return string
	 */
	protected function safe_translate( $text, $fallback ) {
		// Only translate if we're past the early loading phase
		if ( did_action( 'init' ) && function_exists( '__' ) ) {
			return __( $text, $this->config['textdomain'] );
		}
		
		return $fallback;
	}

	/**
	 * Attempts to install missing dependency with retry logic for multiple dependencies.
	 *
	 * @return bool True if all dependencies are now satisfied, false otherwise.
	 */
	protected function attempt_dependency_installation() {
		$max_attempts = 3;
		$attempts = 0;
		
		while ( $attempts < $max_attempts && ! $this->is_addon_up_to_date() ) {
			try {
				// Clear any existing status to force fresh installation
				delete_option( $this->get_option_key() );
				
				// Attempt installation with immediate activation and loading
				$this->install_and_activate_immediately();
				
				// Give time for the installation to register
				if ( ! $this->is_cli_context() ) {
					sleep( 1 );
				}
				
				$attempts++;
				
				// Break early if we've successfully installed
				if ( $this->is_addon_up_to_date() ) {
					break;
				}
				
			} catch ( Exception $e ) {
				$attempts++;
				if ( $attempts >= $max_attempts ) {
					return false;
				}
			}
		}
		
		return $this->is_addon_up_to_date();
	}

	/**
	 * Installs and immediately activates the addon, then forces loading of constants.
	 *
	 * @return void
	 * @throws Exception If installation or activation fails.
	 */
	protected function install_and_activate_immediately() {
		$this->perform_installation_steps();
		
		// Mark installation as completed
		update_option( $this->get_option_key(), 'completed', true );
	}

	/**
	 * Performs the core installation steps.
	 *
	 * @return void
	 * @throws Exception If installation or activation fails.
	 */
	protected function perform_installation_steps() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$this->detect_addon();
		
		// Check if we need to install or update
		if ( version_compare( $this->addon_version, $this->config['minimum_version'], '<' ) ) {
			$this->install_or_upgrade_plugin();
			
			// Re-detect the addon after installation
			$this->detect_addon();
		}

		// Ensure addon is activated immediately
		$this->ensure_addon_is_activated();
		
		// Force load the addon plugin file to make constants available immediately
		$this->force_load_addon();
		
		// Transfer auto-update settings
		$this->transfer_auto_update_settings();
	}

	/**
	 * Installs or upgrades the plugin.
	 *
	 * @return void
	 * @throws Exception If installation fails.
	 */
	protected function install_or_upgrade_plugin() {
		// Ensure we have required WordPress functions available
		$this->include_required_files();

		// Silent installer skin
		$skin = $this->get_silent_installer_skin();

		// Get plugin download URL
		$url = $this->get_plugin_download_url();

		$upgrader = new Plugin_Upgrader( $skin );
		$installed = $upgrader->install( $url );
		
		if ( is_wp_error( $installed ) || ! $installed ) {
			throw new Exception( $this->get_dependency_error_message() );
		}

		// Force plugin cache refresh after installation
		$this->refresh_plugin_cache();
	}

	/**
	 * Includes required WordPress files for installation.
	 *
	 * @return void
	 */
	protected function include_required_files() {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include_once ABSPATH . 'wp-includes/pluggable.php';
		}
		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	}

	/**
	 * Gets a silent installer skin.
	 *
	 * @return object Anonymous class extending Plugin_Installer_Skin.
	 */
	protected function get_silent_installer_skin() {
		return new class() extends Plugin_Installer_Skin {
			public function header() {}
			public function footer() {}
			public function error( $errors ) {}
			public function feedback( $feedback, ...$args ) {}
		};
	}

	/**
	 * Gets the plugin download URL.
	 *
	 * @return string Plugin download URL.
	 */
	protected function get_plugin_download_url() {
		// Try to get the specific version first, fallback to latest
		$url = sprintf(
			'https://downloads.wordpress.org/plugin/%s.%s.zip',
			$this->config['addon_slug'],
			$this->config['minimum_version']
		);
		
		$check_result = wp_remote_retrieve_response_code( wp_remote_head( $url ) );
		if ( $check_result !== 200 ) {
			$url = sprintf(
				'https://downloads.wordpress.org/plugin/%s.zip',
				$this->config['addon_slug']
			);
		}
		
		return $url;
	}

	/**
	 * Refreshes the plugin cache.
	 *
	 * @return void
	 */
	protected function refresh_plugin_cache() {
		if ( function_exists( 'wp_clean_plugins_cache' ) ) {
			wp_clean_plugins_cache();
		}
		
		// Also clear general cache in CLI context
		if ( $this->is_cli_context() ) {
			wp_cache_flush();
		}
	}

	/**
	 * Checks if the current request is a scheduling request.
	 *
	 * @return bool
	 */
	protected function is_scheduling_request() {
		//todo: validate import_key to prevent automatic dependency installation for invalid requests
		return (
			( isset( $_GET['action'] ) && $_GET['action'] == 'wpai_public_api' ) ||
			( isset( $_GET['import_key'] ) && isset( $_GET['action'] ) && 
			  in_array( $_GET['action'], array( 'processing', 'trigger', 'pipe', 'cancel', 'cleanup' ) ) )
		);
	}

	/**
	 * Gets the dependency error message.
	 *
	 * @param bool $check_admin Whether to include admin check message.
	 * @return string
	 */
	protected function get_dependency_error_message( $check_admin = true ) {
		$message = 'Dependency installation failed and import cannot continue.';
		if ( $check_admin ) {
			$message .= ' Please check the WordPress admin area for more details about missing dependencies.';
		}
		return $this->safe_translate( $message, $message );
	}

	/**
	 * Gets the dependency error as HTML for admin display.
	 *
	 * @return string
	 */
	protected function get_dependency_error_html() {
		$message = $this->get_dependency_error_message( false );
		
		$install_link = '';
		if ( current_user_can( 'install_plugins' ) ) {
			$install_link = sprintf(
				'<p><a href="%s" class="button button-primary">%s</a></p>',
				esc_url( self_admin_url( 'plugins.php' ) ),
				esc_html( $this->safe_translate( 'Check Installed Plugins', 'Check Installed Plugins' ) )
			);
		}
		
		return sprintf(
			'<div style="padding: 20px;"><h2>%s</h2><p>%s</p>%s</div>',
			esc_html( $this->safe_translate( 'Missing Dependency', 'Missing Dependency' ) ),
			esc_html( $message ),
			$install_link
		);
	}

	/**
	 * Sends failure notification email to admin (only once).
	 *
	 * @return void
	 */
	protected function send_failure_notification_once() {
		// Check if email alerts are disabled
		if ( ! $this->config['send_email_alert'] ) {
			return;
		}
		
		$notification_key = 'pmxi_addon_failure_notified_' . sanitize_key( $this->config['addon_slug'] );
		
		// Check if we've already sent notification for this addon
		if ( get_option( $notification_key ) ) {
			return;
		}
		
		// Mark as notified
		update_option( $notification_key, time(), false );
		
		// Ensure wp_mail is available
		if ( ! function_exists( 'wp_mail' ) ) {
			require_once ABSPATH . 'wp-includes/pluggable.php';
		}
		
		// Get admin email
		$admin_email = get_option( 'admin_email' );
		if ( ! $admin_email ) {
			return;
		}
		
		$subject = sprintf(
			'[%s] Dependency Installation Failed',
			get_bloginfo( 'name' )
		);
		
		$message = sprintf(
			"Hello,\n\nA dependency installation failed on your website and this might prevent imports from running properly.\n\nPlease login to the WordPress admin area for details about which dependencies need to be installed.\n\nSite: %s",
			home_url()
		);
		
		// Send email
		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Gets the option key for tracking installer status.
	 *
	 * @return string
	 */
	protected function get_option_key() {
		return 'pmxi_addon_installer_' . sanitize_key( $this->config['addon_slug'] );
	}

	/**
	 * Performs the installer if it hasn't been done yet.
	 *
	 * @return void
	 */
	public function install_addon_from_repository() {
		// Defer activation if we're too early in the WordPress loading process
		if ( ! did_action( 'plugins_loaded' ) && ! $this->is_cli_context() ) {
			add_action( 'plugins_loaded', array( $this, 'install_addon_from_repository' ) );
			return;
		}
		
		// If we're in CLI context, handle dependencies immediately and synchronously
		if ( $this->is_cli_context() ) {
			$this->handle_cli_installation();
			return;
		}

		// Normal web interface installation
		$this->setup_web_interface_hooks();
		
		// Check current installation status
		$status = $this->get_status();
		
		if ( ! $status ) {
			// Send notification if installation will fail before attempting
			if ( ! $this->can_install_automatically() ) {
				$this->send_failure_notification_once();
			}
			
			try {
				$this->install();
			} catch ( Exception $e ) {
				// Auto installation failed, the notice will be displayed.
				return;
			}
		} elseif ( $status === 'started' ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$this->detect_addon();
			if ( is_plugin_active( $this->addon_file ) ) {
				// Addon is active so mark installation as successful.
				update_option( $this->get_option_key(), 'completed', true );
			}
		}
	}

	/**
	 * Sets up hooks for web interface.
	 *
	 * @return void
	 */
	protected function setup_web_interface_hooks() {
		// Only add notification hooks if translations are ready
		if ( did_action( 'init' ) ) {
			add_action( 'admin_notices', array( $this, 'show_install_addon_notification' ) );
			add_action( 'network_admin_notices', array( $this, 'show_install_addon_notification' ) );
		} else {
			// Defer notification hooks until translations are ready
			add_action( 'init', function() {
				add_action( 'admin_notices', array( $this, 'show_install_addon_notification' ) );
				add_action( 'network_admin_notices', array( $this, 'show_install_addon_notification' ) );
			} );
		}
		
		add_action( 'plugins_loaded', array( $this, 'validate_installation_status' ) );
		
		// Auto-install dependencies when WordPress is ready
		add_action( 'wp_loaded', array( $this, 'auto_install_dependencies' ) );
		
		if ( $this->config['disable_deactivation'] ) {
			add_filter( 'plugin_action_links', array( $this, 'disable_deactivation_link' ), 10, 2 );
			add_filter( 'network_admin_plugin_action_links', array( $this, 'disable_deactivation_link' ), 10, 2 );
			add_action( 'admin_init', array( $this, 'prevent_deactivation' ) );
		}
	}

	/**
	 * Auto-installs dependencies when WordPress is loaded.
	 *
	 * @return void
	 */
	public function auto_install_dependencies() {
		// Skip if we're in admin and doing AJAX (to avoid conflicts)
		if ( is_admin() && wp_doing_ajax() ) {
			return;
		}
		
		// Skip if already up to date
		if ( $this->is_addon_up_to_date() ) {
			return;
		}
		
		// Only auto-install if we have the necessary permissions (or we're in CLI)
		if ( ! $this->can_install_automatically() ) {
			return;
		}
		
		// Prevent multiple simultaneous installations
		$lock_key = 'pmxi_auto_install_lock_' . sanitize_key( $this->config['addon_slug'] );
		if ( get_transient( $lock_key ) ) {
			return;
		}
		
		// Set a lock for 5 minutes
		set_transient( $lock_key, true, 300 );
		
		try {
			$this->attempt_dependency_installation();
		} catch ( Exception $e ) {
			// Silent failure for auto-installation
		}
		
		// Release lock
		delete_transient( $lock_key );
	}

	/**
	 * Prevents deactivation of the addon plugin.
	 *
	 * @return void
	 */
	public function prevent_deactivation() {
		if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'deactivate' ) {
			return;
		}
		
		if ( ! isset( $_GET['plugin'] ) ) {
			return;
		}
		
		$plugin = sanitize_text_field( wp_unslash( $_GET['plugin'] ) );
		
		if ( $plugin === $this->addon_file ) {
			wp_die(
				sprintf(
					'%1$s cannot be deactivated because it is required by %2$s.',
					esc_html( $this->config['addon_name'] ),
					esc_html( $this->config['pro_plugin_name'] )
				),
				'Plugin Deactivation Prevented',
				array( 'back_link' => true )
			);
		}
	}

	/**
	 * Checks if automatic installation is possible.
	 *
	 * @return bool
	 */
	protected function can_install_automatically() {
		// In CLI and scheduling context, allow installation regardless of user permissions
		// since CLI/scheduling typically runs with elevated system permissions
		if ( $this->is_cli_context() || $this->is_scheduling_request() ) {
			return true;
		}
		
		// For web/manual context, strictly require both permissions
		$can_install = current_user_can( 'install_plugins' );
		$can_activate = current_user_can( 'activate_plugins' );
		
		return $can_install && $can_activate;
	}

	/**
	 * Checks if we're in CLI context.
	 *
	 * @return bool
	 */
	protected function is_cli_context() {
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * Handles installation in CLI context with immediate dependency resolution.
	 *
	 * @return void
	 * @throws Exception If dependencies cannot be satisfied.
	 */
	protected function handle_cli_installation() {
		try {
			if ( ! $this->is_addon_up_to_date() ) {
				// Clear any existing status to force fresh installation
				delete_option( $this->get_option_key() );
				
				// For CLI, defer activation until after WordPress is fully loaded
				if ( did_action( 'init' ) ) {
					// We're past init, safe to install synchronously
					$this->install_synchronous();
				} else {
					// Defer until after init to avoid translation loading issues
					add_action( 'init', function() {
						$this->install_synchronous();
					}, 999 );
					return; // Exit early, installation will happen on init
				}
				
				// Force WordPress to refresh plugin cache
				$this->refresh_plugin_cache();
				
				// Wait a moment for filesystem operations to complete
				sleep( 1 );
				
				// Verify installation succeeded
				if ( ! $this->is_addon_up_to_date() ) {
					$error_message = $this->get_dependency_error_message();
					
					if ( class_exists( 'WP_CLI' ) ) {
						\WP_CLI::error( $error_message );
					}
					
					throw new Exception( $error_message );
				}
			}
		} catch ( Exception $e ) {
			$error_message = $e->getMessage();
			
			if ( class_exists( 'WP_CLI' ) ) {
				\WP_CLI::error( $error_message );
			}
			
			throw new Exception( $error_message );
		}
	}

	/**
	 * Performs synchronous installation for CLI context.
	 *
	 * @return void
	 * @throws Exception If installation fails.
	 */
	protected function install_synchronous() {
		$this->perform_installation_steps();
		
		// Mark installation as completed
		update_option( $this->get_option_key(), 'completed', true );
	}

	/**
	 * Disables the deactivation link for the free addon and shows a required message.
	 *
	 * @param array  $actions Plugin action links.
	 * @param string $plugin_file Plugin file path.
	 * @return array Modified action links.
	 */
	public function disable_deactivation_link( array $actions, $plugin_file ) {
		if ( $plugin_file === $this->addon_file ) {
			if ( isset( $actions['deactivate'] ) ) {
				$actions['deactivate'] = sprintf(
					'<span style="color: #666;">%s</span>',
					sprintf(
						'Required by %s',
						$this->config['pro_plugin_name']
					)
				);
			}
		}
		return $actions;
	}

	/**
	 * Displays a notification to install the addon.
	 *
	 * @return void
	 */
	public function show_install_addon_notification() {
		if ( ! $this->should_show_notification() ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$this->detect_addon();

		$action = $this->get_notification_action();

		if ( ! $action ) {
			return;
		}

		printf(
			'<div class="error pmxi-addon-notice">
				<h4 class="pmxi-notice-header">%s</h4>
				<div class="notice-pmxi-content">
					<p>%s</p>
					<p>%s</p>
				</div>
			</div>',
			sprintf(
				$this->safe_translate( 'Install latest %s', 'Install latest %s' ),
				esc_html( $this->config['addon_name'] )
			),
			sprintf(
				$this->safe_translate( '%1$s %2$s must be installed and activated in order to use %3$s.', '%1$s %2$s must be installed and activated in order to use %3$s.' ),
				esc_html( $this->config['addon_name'] ),
				esc_html( $this->config['minimum_version'] ),
				esc_html( $this->config['pro_plugin_name'] )
			),
			$action // Already escaped in get_notification_action()
		);
	}

	/**
	 * Returns the notification action to display.
	 *
	 * @return string|false The notification action or false if no action should be taken.
	 */
	protected function get_notification_action() {
		$minimum_version_met = version_compare( $this->addon_version, $this->config['minimum_version'], '>=' );
		$network_active = is_plugin_active_for_network( plugin_basename( $this->config['pro_plugin_file'] ) );
		$addon_active = ( $network_active ) ? is_plugin_active_for_network( $this->addon_file ) : is_plugin_active( $this->addon_file );

		if ( $minimum_version_met && $addon_active ) {
			return false;
		}

		if ( $minimum_version_met ) {
			$permission = 'activate_plugins';
		} elseif ( $this->addon_version !== '0' ) {
			$permission = 'update_plugins';
		} else {
			$permission = 'install_plugins';
		}

		if ( current_user_can( $permission ) ) {
			switch ( $permission ) {
				case 'activate_plugins':
					if ( $network_active ) {
						$base_url = network_admin_url( 'plugins.php?action=activate&plugin=' . $this->addon_file );
						$button_content = $this->safe_translate( '%2$sNetwork Activate %1$s now%3$s', '%2$sNetwork Activate %1$s now%3$s' );
					} else {
						$base_url = self_admin_url( 'plugins.php?action=activate&plugin=' . $this->addon_file );
						$button_content = $this->safe_translate( '%2$sActivate %1$s now%3$s', '%2$sActivate %1$s now%3$s' );
					}
					$url = wp_nonce_url( $base_url, 'activate-plugin_' . $this->addon_file );
					break;
				case 'update_plugins':
					$url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $this->addon_file ), 'upgrade-plugin_' . $this->addon_file );
					$button_content = $this->safe_translate( '%2$sUpgrade %1$s now%3$s', '%2$sUpgrade %1$s now%3$s' );
					break;
				case 'install_plugins':
					$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $this->config['addon_slug'] ), 'install-plugin_' . $this->config['addon_slug'] );
					$button_content = $this->safe_translate( '%2$sInstall %1$s now%3$s', '%2$sInstall %1$s now%3$s' );
					break;
			}
			return sprintf(
				esc_html( $button_content ),
				esc_html( $this->config['addon_name'] ),
				'<a class="button" href="' . esc_url( $url ) . '">',
				'</a>'
			);
		}

		if ( is_multisite() ) {
			$message = $this->safe_translate( 'Please contact a network administrator to install %1$s %2$s.', 'Please contact a network administrator to install %1$s %2$s.' );
		} else {
			$message = $this->safe_translate( 'Please contact an administrator to install %1$s %2$s.', 'Please contact an administrator to install %1$s %2$s.' );
		}
		return sprintf(
			esc_html( $message ),
			esc_html( $this->config['addon_name'] ),
			esc_html( $this->config['minimum_version'] )
		);
	}

	/**
	 * Checks if the addon is at the minimum required version.
	 *
	 * @return bool True if addon is at the minimal required version
	 */
	public function is_addon_up_to_date() {
		// If version and edition constants are provided, check them
		if ( ! empty( $this->config['version_constant'] ) ) {
			if ( ! defined( $this->config['version_constant'] ) ) {
				// In CLI context, try to force load the addon to make constants available
				if ( $this->is_cli_context() ) {
					$this->detect_addon();
					$this->force_load_addon();
				}
				
				// Check again after force loading
				if ( ! defined( $this->config['version_constant'] ) ) {
					return false;
				}
			}

			$version_check = version_compare( 
				constant( $this->config['version_constant'] ), 
				$this->config['minimum_version'], 
				'>=' 
			);

			// Also check edition if specified
			if ( ! empty( $this->config['edition_constant'] ) ) {
				$edition_check = defined( $this->config['edition_constant'] ) && 
					constant( $this->config['edition_constant'] ) === $this->config['expected_edition'];
				return $version_check && $edition_check;
			}

			return $version_check;
		}

		// Fallback to plugin detection
		$this->detect_addon();
		
		// Check if plugin is active
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( ! is_plugin_active( $this->addon_file ) ) {
			return false;
		}
		
		return version_compare( $this->addon_version, $this->config['minimum_version'], '>=' );
	}

	/**
	 * Resets the installation status if addon is not installed or outdated.
	 *
	 * @return void
	 */
	public function validate_installation_status() {
		if ( ! $this->is_addon_up_to_date() ) {
			delete_option( $this->get_option_key() );
		}
	}

	/**
	 * Returns the status of the installer.
	 *
	 * @return string|false false if the installer hasn't been started.
	 *                      "started" if it has but hasn't completed.
	 *                      "completed" if it has been completed.
	 */
	protected function get_status() {
		return get_option( $this->get_option_key() );
	}

	/**
	 * Installs the addon.
	 *
	 * @return void
	 * @throws Exception If the installer failed.
	 */
	protected function install() {
		if ( $this->get_status() ) {
			return;
		}

		// Mark the installer as having been started but not completed.
		update_option( $this->get_option_key(), 'started', true );

		$this->perform_installation_steps();
		
		// Mark the installer as having been completed.
		update_option( $this->get_option_key(), 'completed', true );
	}

	/**
	 * Force loads the addon plugin file to make constants available immediately.
	 *
	 * @return void
	 */
	protected function force_load_addon() {
		if ( ! empty( $this->addon_file ) ) {
			$addon_path = WP_PLUGIN_DIR . '/' . $this->addon_file;
			if ( file_exists( $addon_path ) && is_readable( $addon_path ) ) {
				// Include the plugin file to make constants available
				include_once $addon_path;
				
				// Also try to include the main plugin file if it has a different name
				$addon_dir = dirname( $addon_path );
				$main_files = array(
					$addon_dir . '/' . $this->config['addon_slug'] . '.php',
					$addon_dir . '/plugin.php',
					$addon_dir . '/index.php',
					$addon_dir . '/main.php'
				);
				
				foreach ( $main_files as $main_file ) {
					if ( file_exists( $main_file ) && is_readable( $main_file ) && $main_file !== $addon_path ) {
						include_once $main_file;
						break;
					}
				}
			}
		}
	}

	/**
	 * Detects the addon plugin file and version.
	 *
	 * @return void
	 */
	protected function detect_addon() {
		// Make sure addon isn't already installed in another directory.
		foreach ( get_plugins() as $file => $plugin ) {
			// Look for the exact plugin name and author to identify it correctly.
			if (
				isset( $plugin['Name'] ) && $plugin['Name'] === $this->config['addon_name']
				&& isset( $plugin['Author'] ) && $plugin['Author'] === $this->config['addon_author']
			) {
				$this->addon_file = $file;
				$this->addon_version = isset( $plugin['Version'] ) ? $plugin['Version'] : '0';
				$this->addon_dir = WP_PLUGIN_DIR . '/' . dirname( $file );
				break;
			}
		}
	}

	/**
	 * Activates the addon.
	 *
	 * @return void
	 * @throws Exception If addon could not be activated.
	 */
	protected function ensure_addon_is_activated() {
		if ( ! is_plugin_active( $this->addon_file ) ) {
			$network_active = is_plugin_active_for_network( plugin_basename( $this->config['pro_plugin_file'] ) );
			// If we're not active at all it means we're being activated.
			if ( ! $network_active && ! is_plugin_active( plugin_basename( $this->config['pro_plugin_file'] ) ) ) {
				// So set network active to whether or not we're in the network admin.
				$network_active = is_network_admin();
			}
			
			// Activate addon. If pro plugin is network active then make sure addon is as well.
			$activation = activate_plugin( $this->addon_file, '', $network_active, true );
			
			if ( is_wp_error( $activation ) ) {
				throw new Exception( sprintf(
					'Could not activate %s: %s',
					$this->config['addon_name'],
					$activation->get_error_message()
				) );
			}
		}
	}

	/**
	 * Transfers the auto update settings from pro plugin to addon.
	 *
	 * @return void
	 */
	protected function transfer_auto_update_settings() {
		$auto_updates = (array) get_site_option( 'auto_update_plugins', array() );

		if ( in_array( plugin_basename( $this->config['pro_plugin_file'] ), $auto_updates, true ) ) {
			$auto_updates[] = $this->addon_file;
			$auto_updates = array_unique( $auto_updates );
			update_site_option( 'auto_update_plugins', $auto_updates );
		}
	}

	/**
	 * Whether or not the notification to install addon should be shown.
	 *
	 * @return bool
	 */
	protected function should_show_notification() {
		global $pagenow;

		// Do not output on plugin / theme upgrade pages or when WordPress is upgrading.
		if ( ( defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) || wp_installing() ) {
			return false;
		}

		// IFRAME_REQUEST is not defined on these pages, though these action pages do show when upgrading themes or plugins.
		$actions = array( 'do-theme-upgrade', 'do-plugin-upgrade', 'do-core-upgrade', 'do-core-reinstall' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['action'] ) && in_array( $_GET['action'], $actions, true ) ) {
			return false;
		}

		if ( (isset( $_GET['action'] ) && $_GET['action'] == 'install-plugin') && (isset( $_GET['plugin'] ) && $_GET['plugin'] == $this->config['addon_slug'])) {
			return false;
		}

		// Show on ALL admin pages when dependency is missing
		if ( is_admin() && ! $this->is_addon_up_to_date() ) {
			return true;
		}

		return false;
	}
}