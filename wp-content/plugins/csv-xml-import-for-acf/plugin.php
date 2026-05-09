<?php
/*
Plugin Name: WP All Import - ACF Add-On Free
Plugin URI: http://www.wpallimport.com/
Description: Import to Advanced Custom Fields. Requires WP All Import & Advanced Custom Fields.
Version: 1.0.4
Author: Soflyy
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Deactivate if an incompatible version is already active.
if ( class_exists('PMAI_Plugin') ){

	deactivate_plugins(__DIR__ . '/plugin.php');

}
else {

	/**
	 * Plugin root dir with forward slashes as directory separator regardless of actual DIRECTORY_SEPARATOR value
	 * @var string
	 */
	define( 'PMAI_ROOT_DIR', str_replace( '\\', '/', dirname( __FILE__ ) ) );
	/**
	 * Plugin root url for referencing static content
	 * @var string
	 */
	define( 'PMAI_ROOT_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );
	/**
	 * Plugin prefix for making names unique (be aware that this variable is used in conjuction with naming convention,
	 * i.e. in order to change it one must not only modify this constant but also rename all constants, classes and functions which
	 * names composed using this prefix)
	 * @var string
	 */
	define( 'PMAI_PREFIX', 'pmai_' );

	define( 'PMAI_VERSION', '1.0.4' );


	define( 'PMAI_EDITION', 'free' );

	require PMAI_ROOT_DIR . '/vendor/autoload.php';

	/**
	 * Main plugin file, Introduces MVC pattern
	 *
	 * @singletone
	 * @author Maksym Tsypliakov <maksym.tsypliakov@gmail.com>
	 */
	final class PMAI_Plugin {
		/**
		 * Singletone instance
		 * @var PMAI_Plugin
		 */
		protected static $instance;

		/**
		 * Plugin options
		 * @var array
		 */
		public static $all_acf_fields = array();

		/**
		 * Plugin root dir
		 * @var string
		 */
		const ROOT_DIR = PMAI_ROOT_DIR;
		/**
		 * Plugin root URL
		 * @var string
		 */
		const ROOT_URL = PMAI_ROOT_URL;
		/**
		 * Prefix used for names of shortcodes, action handlers, filter functions etc.
		 * @var string
		 */
		const PREFIX = PMAI_PREFIX;
		/**
		 * Plugin file path
		 * @var string
		 */
		const FILE = __FILE__;

		/**
		 * Return singletone instance
		 * @return PMAI_Plugin
		 */
		static public function getInstance() {
			if ( self::$instance == null ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Common logic for requestin plugin info fields
		 */
		public function __call( $method, $args ) {
			if ( preg_match( '%^get(.+)%i', $method, $mtch ) ) {
				$info = get_plugin_data( self::FILE );
				if ( isset( $info[ $mtch[1] ] ) ) {
					return $info[ $mtch[1] ];
				}
			}
			// Translators: %1$s is the class name, %2$s is the method name.
			throw new Exception( sprintf( esc_html__( 'Requested method %1$s::%2$s doesn\'t exist.', 'csv-xml-import-for-acf' ),
				esc_html( get_class( $this ) ),
				esc_html( $method )
			) );
		}

		/**
		 * Get path to plagin dir relative to wordpress root
		 *
		 * @param bool[optional] $noForwardSlash Whether path should be returned withot forwarding slash
		 *
		 * @return string
		 */
		public function getRelativePath( $noForwardSlash = false ) {
			$wp_root = str_replace( '\\', '/', ABSPATH );

			return ( $noForwardSlash ? '' : '/' ) . str_replace( $wp_root, '', self::ROOT_DIR );
		}

		/**
		 * Check whether plugin is activated as network one
		 * @return bool
		 */
		public function isNetwork() {
			if ( ! is_multisite() ) {
				return false;
			}

			$plugins = get_site_option( 'active_sitewide_plugins' );
			if ( isset( $plugins[ plugin_basename( self::FILE ) ] ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check whether permalinks is enabled
		 * @return bool
		 */
		public function isPermalinks() {
			global $wp_rewrite;

			return $wp_rewrite->using_permalinks();
		}

		/**
		 * Return prefix for plugin database tables
		 * @return string
		 */
		public function getTablePrefix() {
			global $wpdb;

			return ( $this->isNetwork() ? $wpdb->base_prefix : $wpdb->prefix ) . self::PREFIX;
		}

		/**
		 * Return prefix for wordpress database tables
		 * @return string
		 */
		public function getWPPrefix() {
			global $wpdb;

			return ( $this->isNetwork() ? $wpdb->base_prefix : $wpdb->prefix );
		}

		/**
		 * Class constructor containing dispatching logic
		 *
		 * @param string $rootDir Plugin root dir
		 * @param string $pluginFilePath Plugin main file
		 */
		protected function __construct() {

			// create/update required database tables

			// register autoloading method
			spl_autoload_register( array( $this, 'autoload' ) );

			// register helpers
			if ( is_dir( self::ROOT_DIR . '/helpers' ) ) {
				foreach ( PMAI_Helper::safe_glob( self::ROOT_DIR . '/helpers/*.php', PMAI_Helper::GLOB_RECURSE | PMAI_Helper::GLOB_PATH ) as $filePath ) {
					require_once $filePath;
				}
			}

			register_activation_hook( self::FILE, array( $this, 'activation' ) );

			// register action handlers
			if ( is_dir( self::ROOT_DIR . '/actions' ) ) {
				if ( is_dir( self::ROOT_DIR . '/actions' ) ) {
					foreach ( PMAI_Helper::safe_glob( self::ROOT_DIR . '/actions/*.php', PMAI_Helper::GLOB_RECURSE | PMAI_Helper::GLOB_PATH ) as $filePath ) {
						require_once $filePath;
						$function = $actionName = basename( $filePath, '.php' );
						if ( preg_match( '%^(.+?)[_-](\d+)$%', $actionName, $m ) ) {
							$actionName = $m[1];
							$priority   = intval( $m[2] );
						} else {
							$priority = 10;
						}
						add_action( $actionName, self::PREFIX . str_replace( '-', '_', $function ), $priority, 99 ); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
					}
				}
			}

			// register filter handlers
			if ( is_dir( self::ROOT_DIR . '/filters' ) ) {
				foreach ( PMAI_Helper::safe_glob( self::ROOT_DIR . '/filters/*.php', PMAI_Helper::GLOB_RECURSE | PMAI_Helper::GLOB_PATH ) as $filePath ) {
					require_once $filePath;
					$function = $actionName = basename( $filePath, '.php' );
					if ( preg_match( '%^(.+?)[_-](\d+)$%', $actionName, $m ) ) {
						$actionName = $m[1];
						$priority   = intval( $m[2] );
					} else {
						$priority = 10;
					}
					add_filter( $actionName, self::PREFIX . str_replace( '-', '_', $function ), $priority, 99 ); // since we don't know at this point how many parameters each plugin expects, we make sure they will be provided with all of them (it's unlikely any developer will specify more than 99 parameters in a function)
				}
			}

			// register shortcodes handlers
			if ( is_dir( self::ROOT_DIR . '/shortcodes' ) ) {
				foreach ( PMAI_Helper::safe_glob( self::ROOT_DIR . '/shortcodes/*.php', PMAI_Helper::GLOB_RECURSE | PMAI_Helper::GLOB_PATH ) as $filePath ) {
					$tag = strtolower( str_replace( '/', '_', preg_replace( '%^' . preg_quote( self::ROOT_DIR . '/shortcodes/', '%' ) . '|\.php$%', '', $filePath ) ) );
					add_shortcode( $tag, array( $this, 'shortcodeDispatcher' ) );
				}
			}

			// register admin page pre-dispatcher
			add_action( 'admin_init', array( $this, 'adminInit' ), 1 );
			add_action( 'init', array( $this, 'init' ), 10 );
		}

		public function init() {
			$this->load_plugin_textdomain();
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'csv-xml-import-for-acf' );
			load_plugin_textdomain( 'csv-xml-import-for-acf', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );
		}

		/**
		 * pre-dispatching logic for admin page controllers
		 */
		public function adminInit() {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$page    = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : '';
			$page    = strtolower( $page );
			$pattern = '%^' . preg_quote( str_replace( '_', '-', self::PREFIX ), '%' ) . '([\w-]+)$%';

			if ( preg_match( $pattern, $page ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : 'index';
				$action = strtolower( $action );
				$this->adminDispatcher( $page, $action );
			}
		}

		/**
		 * Dispatch shorttag: create corresponding controller instance and call its index method
		 *
		 * @param array $args Shortcode tag attributes
		 * @param string $content Shortcode tag content
		 * @param string $tag Shortcode tag name which is being dispatched
		 *
		 * @return string
		 */
		public function shortcodeDispatcher( $args, $content, $tag ) {

			$controllerName = self::PREFIX . preg_replace_callback( '%(^|_).%', array(
					$this,
					"replace_callback"
				), $tag );// capitalize first letters of class name parts and add prefix
			$controller     = new $controllerName();
			if ( ! $controller instanceof PMAI_Controller ) {
				// Translators: %1$s is the shortcode tag.
				throw new Exception( sprintf( esc_html__( 'Shortcode `%1$s` matches to a wrong controller type.', 'csv-xml-import-for-acf' ),
					esc_html( $tag )
				) );
			}
			ob_start();
			$controller->index( $args, $content );

			return ob_get_clean();
		}

		/**
		 * Dispatch admin page: call corresponding controller based on get parameter `page`
		 * The method is called twice: 1st time as handler `parse_header` action and then as admin menu item handler
		 *
		 * @param string[optional] $page When $page set to empty string ealier buffered content is outputted, otherwise controller is called based on $page value
		 */
		public function adminDispatcher( $page = '', $action = 'index' ) {
			static $buffer = null;
			static $buffer_callback = null;
			if ( '' === $page ) {
				if ( ! is_null( $buffer ) ) {
					echo '<div class="wrap">';
					echo esc_html( $buffer );
					do_action( 'pmai_action_after' );
					echo '</div>';
				} elseif ( ! is_null( $buffer_callback ) ) {
					echo '<div class="wrap">';
					call_user_func( $buffer_callback );
					do_action( 'pmai_action_after' );
					echo '</div>';
				} else {
					throw new Exception( 'There is no previousely buffered content to display.' );
				}
			} else {
				$actionName = str_replace( '-', '_', $action );
				// capitalize prefix and first letters of class name parts
				$controllerName = preg_replace_callback( '%(^' . preg_quote( self::PREFIX, '%' ) . '|_).%', array(
					$this,
					"replace_callback"
				), str_replace( '-', '_', $page ) );
				if ( method_exists( $controllerName, $actionName ) ) {

					if ( ! get_current_user_id() or ! current_user_can( PMXI_Plugin::$capabilities ) ) {
						// This nonce is not valid.
						die( 'Security check' );

					} else {

						$this->_admin_current_screen = (object) array(
							'id'         => $controllerName,
							'base'       => $controllerName,
							'action'     => $actionName,
							'is_ajax'    => isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) ) === 'xmlhttprequest',
							'is_network' => is_network_admin(),
							'is_user'    => is_user_admin(),
						);
						add_filter( 'current_screen', array( $this, 'getAdminCurrentScreen' ) );

						$controller = new $controllerName();
						if ( ! $controller instanceof PMAI_Controller_Admin ) {
							// Translators: %1$s is the administration page.
							throw new Exception( sprintf( esc_html__( 'Administration page `%1$s` matches to a wrong controller type.', 'csv-xml-import-for-acf' ),
								esc_html( $page )
							) );
						}

						if ( $this->_admin_current_screen->is_ajax ) { // ajax request
							$controller->$action();
							do_action( 'pmai_action_after' );
							die(); // stop processing since we want to output only what controller is randered, nothing in addition
						} elseif ( ! $controller->isInline ) {
							ob_start();
							$controller->$action();
							$buffer = ob_get_clean();
						} else {
							$buffer_callback = array( $controller, $action );
						}
					}

				} else { // redirect to dashboard if requested page and/or action don't exist
					wp_redirect( admin_url() );
					die();
				}
			}
		}

		protected $_admin_current_screen = null;

		public function getAdminCurrentScreen() {
			return $this->_admin_current_screen;
		}

		public function replace_callback( $matches ) {
			return strtoupper( $matches[0] );
		}

		/**
		 * Autoloader
		 * It's assumed class name consists of prefix folloed by its name which in turn corresponds to location of source file
		 * if `_` symbols replaced by directory path separator. File name consists of prefix folloed by last part in class name (i.e.
		 * symbols after last `_` in class name)
		 * When class has prefix it's source is looked in `models`, `controllers`, `shortcodes` folders, otherwise it looked in `core` or `library` folder
		 *
		 * @param string $className
		 *
		 * @return bool
		 */
		public function autoload( $className ) {

			if ( ! preg_match( '/PMAI/m', $className ) ) {
				return false;
			}

			$is_prefix = false;
			$filePath  = str_replace( '_', '/', preg_replace( '%^' . preg_quote( self::PREFIX, '%' ) . '%', '', strtolower( $className ), 1, $is_prefix ) ) . '.php';
			if ( ! $is_prefix ) { // also check file with original letter case
				$filePathAlt = $className . '.php';
			}
			foreach ( $is_prefix ? array( 'models', 'controllers', 'shortcodes', 'classes' ) : array() as $subdir ) {
				$path = self::ROOT_DIR . '/' . $subdir . '/' . $filePath;
				if ( is_file( $path ) ) {
					require $path;

					return true;
				}
				if ( ! $is_prefix ) {
					$pathAlt = self::ROOT_DIR . '/' . $subdir . '/' . $filePathAlt;
					if ( is_file( $pathAlt ) ) {
						require $pathAlt;

						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Plugin activation logic
		 */
		public function activation() {
			// Uncaught exception doesn't prevent plugin from being activated, therefore replace it with fatal error so it does.
			set_exception_handler( function ( $e ) {
				trigger_error( esc_html( $e->getMessage() ), E_USER_ERROR );
			} );
		}

		/**
		 *  Init all available ACF fields.
		 */
		public static function get_available_acf_fields() {
			if ( empty( self::$all_acf_fields ) ) {
				global $acf;
				if ( $acf and version_compare( $acf->settings['version'], '5.0.0' ) >= 0 ) {
					self::$all_acf_fields = array();
					$groups               = acf_get_field_groups();
					if ( ! empty( $groups ) ) {
						foreach ( $groups as $group ) {
							$fields = acf_get_fields( $group );
							if ( ! empty( $fields ) ) {
								foreach ( $fields as $key => $field ) {
									if ( ! empty( $field['name'] ) ) {
										self::$all_acf_fields[] = $field['name'];
										self::$all_acf_fields[] = '_' . $field['name'];
									}
								}
							}
						}
					}
				} else {
					$acfs                 = get_posts( array( 'posts_per_page' => - 1, 'post_type' => 'acf' ) );
					self::$all_acf_fields = array();
					if ( ! empty( $acfs ) ) {
						foreach ( $acfs as $key => $acf_entry ) {
							foreach ( get_post_meta( $acf_entry->ID, '' ) as $cur_meta_key => $cur_meta_val ) {
								if ( strpos( $cur_meta_key, 'field_' ) !== 0 ) {
									continue;
								}
								$field      = ( ! empty( $cur_meta_val[0] ) ) ? unserialize( $cur_meta_val[0] ) : array();
								$field_name = $field['name'];
								if ( ! in_array( $field_name, self::$all_acf_fields ) ) {
									self::$all_acf_fields[] = $field_name;
								}
								if ( ! empty( $field['sub_fields'] ) ) {
									foreach ( $field['sub_fields'] as $key => $sub_field ) {
										$sub_field_name = $sub_field['name'];
										if ( ! in_array( $sub_field_name, self::$all_acf_fields ) ) {
											self::$all_acf_fields[] = $sub_field_name;
										}
									}
								}
							}
						}
					}
				}
			}

			return self::$all_acf_fields;
		}

		/**
		 * Method returns default import options, main utility of the method is to avoid warnings when new
		 * option is introduced but already registered imports don't have it
		 */
		public static function get_default_import_options() {
			return array(
				'acf'                     => array(),
				'fields'                  => array(),
				'is_multiple_field_value' => array(),
				'multiple_value'          => array(),
				'fields_delimiter'        => array(),

				'is_update_acf'    => 1,
				'update_acf_logic' => 'full_update',
				'acf_list'         => array(),
				'acf_only_list'    => array(),
				'acf_except_list'  => array()
			);
		}
	}

	PMAI_Plugin::getInstance();
}