<?php
namespace FileBird\Support;

use FileBird\Controller\Folder;

defined( 'ABSPATH' ) || exit;

class PageBuilders {
	protected $folderController;

	public function __construct() {
		$this->folderController = Folder::getInstance();
		add_action( 'init', array( $this, 'prepareRegister' ) );
	}

	public function prepareRegister() {
		// Compatible for Elementor
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$this->registerForElementor();
		}
		// Compatible for WPBakery - Work normally

		// Compatible for Beaver Builder
		if ( class_exists( 'FLBuilderLoader' ) ) {
			$this->registerForBeaver();
		}

		// Brizy Builder
		if ( class_exists( 'Brizy_Editor' ) ) {
			$this->registerForBrizy();
		}

		// Cornerstone
		if ( class_exists( 'Cornerstone_Plugin' ) ) {
			$this->registerCornerstone();
		}

		// Compatible for Divi
		if ( class_exists( 'ET_Builder_Element' ) ) {
			$this->registerForDivi();
		}

		// Compatible for Thrive
		if ( defined( 'TVE_IN_ARCHITECT' ) || class_exists( 'Thrive_Quiz_Builder' ) ) {
			$this->registerForThrive();
		}

		// Fusion Builder
		if ( class_exists( 'Fusion_Builder_Front' ) ) {
			$this->registerForFusion();
		}

		// Oxygen Builder
		if ( defined( 'CT_VERSION' ) ) {
			$this->registerOxygenBuilder();
		}

		// Tatsu Builder
		if ( defined( 'TATSU_VERSION' ) ) {
			$this->registerTatsuBuilder();
		}

		// Themify
		if ( defined( 'THEMIFY_VERSION' ) && class_exists( 'Themify_Builder_Model' ) ) {
			$this->registerThemify();
		}
	}

	public function enqueueScripts() {
		$this->folderController->enqueueAdminScripts( 'pagebuilders' );
	}

	public function registerForElementor() {
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function registerForBeaver() {
		add_action( 'fl_before_sortable_enqueue', array( $this, 'enqueueScripts' ) );
	}

	public function registerForBrizy() {
		add_action( 'brizy_editor_enqueue_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function registerCornerstone() {
		add_action( 'cornerstone_before_wp_editor', array( $this, 'enqueueScripts' ) );
	}

	public function registerForDivi() {
		add_action(
			'et_fb_enqueue_assets',
			function() {
				$this->enqueueScripts();
			}
		);
	}

	public function registerForThrive() {
		add_action( 'tcb_main_frame_enqueue', array( $this, 'enqueueScripts' ) );
	}

	public function registerForFusion() {
		add_action( 'fusion_builder_enqueue_live_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function registerOxygenBuilder() {
		add_action( 'oxygen_enqueue_ui_scripts', array( $this, 'enqueueScripts' ) );
	}

	public function registerTatsuBuilder() {
		add_action( 'tatsu_builder_footer', array( $this, 'enqueueScripts' ) );
	}

	public function registerThemify() {
		if ( ( is_admin() === true && \Themify_Builder_Model::hasAccess() ) || \Themify_Builder_Model::is_frontend_editor_page() ) {
			wp_enqueue_media();
			add_action( 'themify_body_end', array( $this, 'enqueueScripts' ) );
		}
	}
}
