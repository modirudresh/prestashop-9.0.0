<?php
/**
 * AxonCreator - Website Builder
 *
 * NOTICE OF LICENSE
 *
 * @author    axonviz.com <support@axonviz.com>
 * @copyright 2021 axonviz.com
 * @license   You can not resell or redistribute this software.
 *
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace AxonCreator;

use AxonCreator\Core\Admin\Admin;
use AxonCreator\Core\Common\Modules\Ajax\Module as Ajax;
use AxonCreator\Core\Common\App as CommonApp;
use AxonCreator\Core\Debug\Inspector;
use AxonCreator\Core\Documents_Manager;
use AxonCreator\Core\Editor\Editor;
use AxonCreator\Core\Files\Manager as Files_Manager;
use AxonCreator\Core\Files\Assets\Manager as Assets_Manager;
use AxonCreator\Core\Modules_Manager;
use AxonCreator\Core\Settings\Manager as Settings_Manager;
use AxonCreator\Core\Settings\Page\Manager as Page_Settings_Manager;
use AxonCreator\Core\Revisions\Revisions_Manager;
use AxonCreator\Core\DynamicTags\Manager as Dynamic_Tags_Manager;
use AxonCreator\Core\Logger\Manager as Log_Manager;
use AxonCreator\Wp_Helper;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

/**
 * Elementor plugin.
 *
 * The main plugin handler class is responsible for initializing Elementor. The
 * class registers and all the components required to run the plugin.
 *
 * @since 1.0.0
 */
class Plugin {
    public $icons_manager;  // Add this line to declare the property

	/**
	 * Instance.
	 *
	 * Holds the plugin instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @var Plugin
	 */
	public static $instance = null;

	/**
	 * Database.
	 *
	 * Holds the plugin database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var DB
	 */
	public $db;

	/**
	 * Ajax Manager.
	 *
	 * Holds the plugin ajax manager.
	 *
	 * @since 1.9.0
	 * @deprecated 2.3.0 Use `Plugin::$instance->common->get_component( 'ajax' )` instead
	 * @access public
	 *
	 * @var Ajax
	 */
	public $ajax;

	/**
	 * Controls manager.
	 *
	 * Holds the plugin controls manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Controls_Manager
	 */
	public $controls_manager;

	/**
	 * Documents manager.
	 *
	 * Holds the documents manager.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @var Documents_Manager
	 */
	public $documents;

	/**
	 * Schemes manager.
	 *
	 * Holds the plugin schemes manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Schemes_Manager
	 */
	public $schemes_manager;

	/**
	 * Elements manager.
	 *
	 * Holds the plugin elements manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Elements_Manager
	 */
	public $elements_manager;

	/**
	 * Widgets manager.
	 *
	 * Holds the plugin widgets manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Widgets_Manager
	 */
	public $widgets_manager;

	/**
	 * Revisions manager.
	 *
	 * Holds the plugin revisions manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Revisions_Manager
	 */
	public $revisions_manager;

	/**
	 * Maintenance mode.
	 *
	 * Holds the plugin maintenance mode.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Maintenance_Mode
	 */
	public $maintenance_mode;

	/**
	 * Page settings manager.
	 *
	 * Holds the page settings manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Page_Settings_Manager
	 */
	public $page_settings_manager;

	/**
	 * Dynamic tags manager.
	 *
	 * Holds the dynamic tags manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Dynamic_Tags_Manager
	 */
	public $dynamic_tags;

	/**
	 * Settings.
	 *
	 * Holds the plugin settings.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Settings
	 */
	public $settings;

	/**
	 * Role Manager.
	 *
	 * Holds the plugin Role Manager
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @var \Elementor\Core\RoleManager\Role_Manager
	 */
	public $role_manager;

	/**
	 * Admin.
	 *
	 * Holds the plugin admin.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Admin
	 */
	public $admin;

	/**
	 * Tools.
	 *
	 * Holds the plugin tools.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Tools
	 */
	public $tools;

	/**
	 * Preview.
	 *
	 * Holds the plugin preview.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Preview
	 */
	public $preview;

	/**
	 * Editor.
	 *
	 * Holds the plugin editor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Editor
	 */
	public $editor;

	/**
	 * Frontend.
	 *
	 * Holds the plugin frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Frontend
	 */
	public $frontend;

	/**
	 * Heartbeat.
	 *
	 * Holds the plugin heartbeat.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Heartbeat
	 */
	public $heartbeat;

	/**
	 * System info.
	 *
	 * Holds the system info data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var System_Info\Main
	 */
	public $system_info;

	/**
	 * Template library manager.
	 *
	 * Holds the template library manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var TemplateLibrary\Manager
	 */
	public $templates_manager;

	/**
	 * Skins manager.
	 *
	 * Holds the skins manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Skins_Manager
	 */
	public $skins_manager;

	/**
	 * Files Manager.
	 *
	 * Holds the files manager.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @var Files_Manager
	 */
	public $files_manager;

	/**
	 * Assets Manager.
	 *
	 * Holds the Assets manager.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @var Assets_Manager
	 */
	public $assets_manager;

	/**
	 * Files Manager.
	 *
	 * Holds the files manager.
	 *
	 * @since 1.0.0
	 * @access public
	 * @deprecated 2.1.0 Use `Plugin::$files_manager` instead
	 *
	 * @var Files_Manager
	 */
	public $posts_css_manager;

	/**
	 * WordPress widgets manager.
	 *
	 * Holds the WordPress widgets manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var WordPress_Widgets_Manager
	 */
	public $wordpress_widgets_manager;

	/**
	 * Modules manager.
	 *
	 * Holds the modules manager.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Modules_Manager
	 */
	public $modules_manager;

	/**
	 * Beta testers.
	 *
	 * Holds the plugin beta testers.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var Beta_Testers
	 */
	public $beta_testers;

	/**
	 * @var Inspector
	 * @deprecated 2.1.2 Use $inspector.
	 */
	public $debugger;

	/**
	 * @var Inspector
	 */
	public $inspector;

	/**
	 * @var CommonApp
	 */
	public $common;

	/**
	 * @var Log_Manager
	 */
	public $logger;

	/**
	 * @var Core\Upgrade\Manager
	 */
	public $upgrade;

	/**
	 * Clone.
	 *
	 * Disable class cloning and throw an error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object. Therefore, we don't want the object to be cloned.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, Wp_Helper::esc_html__( 'Something went wrong.', 'elementor' ), '1.0.0' );
	}

	/**
	 * Wakeup.
	 *
	 * Disable unserializing of the class.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, Wp_Helper::esc_html__( 'Something went wrong.', 'elementor' ), '1.0.0' );
	}

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the plugin class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			/**
			 * Elementor loaded.
			 *
			 * Fires when Elementor was fully loaded and instantiated.
			 *
			 * @since 1.0.0
			 */
			Wp_Helper::do_action( 'elementor/loaded' );
		}

		return self::$instance;
	}

	/**
	 * Init.
	 *
	 * Initialize Elementor Plugin. Register Elementor support for all the
	 * supported post types and initialize Elementor components.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {
		if ( is_null( $this->common ) ) {
			$this->common = new CommonApp();
			
			$this->common->init_components();
		}

		$this->init_components();

		/**
		 * Elementor init.
		 *
		 * Fires on Elementor init, after Elementor has finished loading but
		 * before any headers are sent.
		 *
		 * @since 1.0.0
		 */
		Wp_Helper::do_action( 'elementor/init' );
	}
	
	/**
	 * Get install time.
	 *
	 * Retrieve the time when Elementor was installed.
	 *
	 * @since 2.6.0
	 * @access public
	 * @static
	 *
	 * @return int Unix timestamp when Elementor was installed.
	 */
	public function get_install_time() {
		$installed_time = Wp_Helper::get_option( '_elementor_installed_time' );

		if ( ! $installed_time ) {
			$installed_time = time();

			Wp_Helper::update_option( '_elementor_installed_time', $installed_time );
		}

		return $installed_time;
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function on_rest_api_init() {
		// On admin/frontend sometimes the rest API is initialized after the common is initialized.
		if ( ! $this->common ) {
			$this->init_common();
		}
	}

	/**
	 * Init components.
	 *
	 * Initialize Elementor components. Register actions, run setting manager,
	 * initialize all the components that run elementor, and if in admin page
	 * initialize admin components.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function init_components() {
		Settings_Manager::run();

		$this->db = new DB();
		$this->controls_manager = new Controls_Manager();
		$this->documents = new Documents_Manager();
		$this->schemes_manager = new Schemes_Manager();
		$this->elements_manager = new Elements_Manager();
		$this->widgets_manager = new Widgets_Manager();
		$this->skins_manager = new Skins_Manager();
		$this->files_manager = new Files_Manager();
		$this->assets_manager = new Assets_Manager();
		$this->icons_manager = new Icons_Manager();
		/*
		 * @TODO: Remove deprecated alias
		 */
		$this->posts_css_manager = $this->files_manager;
		$this->settings = new Settings();
		$this->tools = new Tools();
		$this->editor = new Editor();
		$this->preview = new Preview();
		$this->frontend = new Frontend();
		$this->templates_manager = new TemplateLibrary\Manager();
		$this->dynamic_tags = new Dynamic_Tags_Manager();
		$this->revisions_manager = new Revisions_Manager();
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function init_common() {
		$this->common = new CommonApp();

		$this->common->init_components();

		$this->ajax = $this->common->get_component( 'ajax' );
	}

	/**
	 * Add custom post type support.
	 *
	 * Register Elementor support for all the supported post types defined by
	 * the user in the admin screen and saved as `elementor_cpt_support` option
	 * in WordPress `$wpdb->options` table.
	 *
	 * If no custom post type selected, usually in new installs, this method
	 * will return the two default post types: `page` and `post`.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function add_cpt_support() {
		$cpt_support = Wp_Helper::get_option( 'elementor_cpt_support', [ 'page', 'post' ] );

		foreach ( $cpt_support as $cpt_slug ) {
			add_post_type_support( $cpt_slug, 'elementor' );
		}
	}

	/**
	 * Register autoloader.
	 *
	 * Elementor autoloader loads all the classes needed to run the plugin.
	 *
	 * @since 1.6.0
	 * @access private
	 */
	private function register_autoloader() {
		require AXON_CREATOR_PATH . '/includes/autoloader.php';

		Autoloader::run();
	}

	/**
	 * Plugin constructor.
	 *
	 * Initializing Elementor plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		$this->register_autoloader();
		$this->init();
	}

	final public static function get_title() {
		return Wp_Helper::__( 'Elementor', 'elementor' );
	}
}