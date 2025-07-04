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

use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor "Tools" page in WordPress Dashboard.
 *
 * Elementor settings page handler class responsible for creating and displaying
 * Elementor "Tools" page in WordPress dashboard.
 *
 * @since 1.0.0
 */
class Tools extends Settings_Page {

	/**
	 * Settings page ID for Elementor tools.
	 */
	const PAGE_ID = 'elementor-tools';

	/**
	 * Register admin menu.
	 *
	 * Add new Elementor Tools admin menu.
	 *
	 * Fired by `admin_menu` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function register_admin_menu() {
		add_submenu_page(
			Settings::PAGE_ID,
			Wp_Helper::__( 'Tools', 'elementor' ),
			Wp_Helper::__( 'Tools', 'elementor' ),
			'manage_options',
			self::PAGE_ID,
			[ $this, 'display_settings_page' ]
		);
	}

	/**
	 * Clear cache.
	 *
	 * Delete post meta containing the post CSS file data. And delete the actual
	 * CSS files from the upload directory.
	 *
	 * Fired by `wp_ajax_elementor_clear_cache` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function ajax_elementor_clear_cache() {
		check_ajax_referer( 'elementor_clear_cache', '_nonce' );

		Plugin::$instance->files_manager->clear_cache();

		wp_send_json_success();
	}

	/**
	 * Replace URLs.
	 *
	 * Sends an ajax request to replace old URLs to new URLs. This method also
	 * updates all the Elementor data.
	 *
	 * Fired by `wp_ajax_elementor_replace_url` action.
	 *
	 * @since 1.1.0
	 * @access public
	 */
	public function ajax_elementor_replace_url() {
		check_ajax_referer( 'elementor_replace_url', '_nonce' );

		$from = ! empty( $_POST['from'] ) ? $_POST['from'] : '';
		$to = ! empty( $_POST['to'] ) ? $_POST['to'] : '';

		try {
			$results = Utils::replace_urls( $from, $to );
			wp_send_json_success( $results );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Elementor version rollback.
	 *
	 * Rollback to previous Elementor version.
	 *
	 * Fired by `admin_post_elementor_rollback` action.
	 *
	 * @since 1.5.0
	 * @access public
	 */
	public function post_elementor_rollback() {
		check_admin_referer( 'elementor_rollback' );

		$plugin_slug = basename( AXON_CREATOR__FILE__, '.php' );

		$rollback = new Rollback(
			[
				'version' => AXON_CREATOR_PREVIOUS_STABLE_VERSION,
				'plugin_name' => AXON_CREATOR_PLUGIN_BASE,
				'plugin_slug' => $plugin_slug,
				'package_url' => sprintf( 'https://downloads.wordpress.org/plugin/%s.%s.zip', $plugin_slug, AXON_CREATOR_PREVIOUS_STABLE_VERSION ),
			]
		);

		$rollback->run();

		wp_die(
			'', Wp_Helper::__( 'Rollback to Previous Version', 'elementor' ), [
				'response' => 200,
			]
		);
	}

	/**
	 * Tools page constructor.
	 *
	 * Initializing Elementor "Tools" page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		Wp_Helper::add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 205 );

		if ( ! empty( $_POST ) ) {
			Wp_Helper::add_action( 'wp_ajax_elementor_clear_cache', [ $this, 'ajax_elementor_clear_cache' ] );
			Wp_Helper::add_action( 'wp_ajax_elementor_replace_url', [ $this, 'ajax_elementor_replace_url' ] );
		}

		Wp_Helper::add_action( 'admin_post_elementor_rollback', [ $this, 'post_elementor_rollback' ] );
	}

	/**
	 * Create tabs.
	 *
	 * Return the tools page tabs, sections and fields.
	 *
	 * @since 1.5.0
	 * @access protected
	 *
	 * @return array An array with the page tabs, sections and fields.
	 */
	protected function create_tabs() {
		return [
			'general' => [
				'label' => Wp_Helper::__( 'General', 'elementor' ),
				'sections' => [
					'tools' => [
						'fields' => [
							'clear_cache' => [
								'label' => Wp_Helper::__( 'Regenerate CSS', 'elementor' ),
								'field_args' => [
									'type' => 'raw_html',
									'html' => sprintf( '<button data-nonce="%s" class="button elementor-button-spinner" id="elementor-clear-cache-button">%s</button>', wp_create_nonce( 'elementor_clear_cache' ), Wp_Helper::__( 'Regenerate Files', 'elementor' ) ),
									'desc' => Wp_Helper::__( 'Styles set in Elementor are saved in CSS files in the uploads folder. Recreate those files, according to the most recent settings.', 'elementor' ),
								],
							],
							'reset_api_data' => [
								'label' => Wp_Helper::__( 'Sync Library', 'elementor' ),
								'field_args' => [
									'type' => 'raw_html',
									'html' => sprintf( '<button data-nonce="%s" class="button elementor-button-spinner" id="elementor-library-sync-button">%s</button>', wp_create_nonce( 'elementor_reset_library' ), Wp_Helper::__( 'Sync Library', 'elementor' ) ),
									'desc' => Wp_Helper::__( 'Elementor Library automatically updates on a daily basis. You can also manually update it by clicking on the sync button.', 'elementor' ),
								],
							],
						],
					],
				],
			],
			'replace_url' => [
				'label' => Wp_Helper::__( 'Replace URL', 'elementor' ),
				'sections' => [
					'replace_url' => [
						'callback' => function() {
							$intro_text = sprintf(
								/* translators: %s: Codex URL */
								Wp_Helper::__( '<strong>Important:</strong> It is strongly recommended that you <a target="_blank" href="%s">backup your database</a> before using Replace URL.', 'elementor' ),
								'https://codex.wordpress.org/WordPress_Backups'
							);
							$intro_text = '<div>' . $intro_text . '</div>';

							echo '<h2>' . Wp_Helper::esc_html__( 'Replace URL', 'elementor' ) . '</h2>';
							echo $intro_text;
						},
						'fields' => [
							'replace_url' => [
								'label' => Wp_Helper::__( 'Update Site Address (URL)', 'elementor' ),
								'field_args' => [
									'type' => 'raw_html',
									'html' => sprintf( '<input type="text" name="from" placeholder="http://old-url.com" class="medium-text"><input type="text" name="to" placeholder="http://new-url.com" class="medium-text"><button data-nonce="%s" class="button elementor-button-spinner" id="elementor-replace-url-button">%s</button>', wp_create_nonce( 'elementor_replace_url' ), Wp_Helper::__( 'Replace URL', 'elementor' ) ),
									'desc' => Wp_Helper::__( 'Enter your old and new URLs for your WordPress installation, to update all Elementor data (Relevant for domain transfers or move to \'HTTPS\').', 'elementor' ),
								],
							],
						],
					],
				],
			],
			'versions' => [
				'label' => Wp_Helper::__( 'Version Control', 'elementor' ),
				'sections' => [
					'rollback' => [
						'label' => Wp_Helper::__( 'Rollback to Previous Version', 'elementor' ),
						'callback' => function() {
							$intro_text = sprintf(
								/* translators: %s: Elementor version */
								Wp_Helper::__( 'Experiencing an issue with Elementor version %s? Rollback to a previous version before the issue appeared.', 'elementor' ),
								AXON_CREATOR_VERSION
							);
							$intro_text = '<p>' . $intro_text . '</p>';

							echo $intro_text;
						},
						'fields' => [
							'rollback' => [
								'label' => Wp_Helper::__( 'Rollback Version', 'elementor' ),
								'field_args' => [
									'type' => 'raw_html',
									'html' => sprintf(
										'<a href="%s" class="button elementor-button-spinner elementor-rollback-button">%s</a>',
										wp_nonce_url( Wp_Helper::admin_url( 'admin-post.php?action=elementor_rollback' ), 'elementor_rollback' ),
										sprintf(
											/* translators: %s: Elementor previous stable version */
											Wp_Helper::__( 'Reinstall v%s', 'elementor' ),
											AXON_CREATOR_PREVIOUS_STABLE_VERSION
										)
									),
									'desc' => '<span style="color: red;">' . Wp_Helper::__( 'Warning: Please backup your database before making the rollback.', 'elementor' ) . '</span>',
								],
							],
						],
					],
					'beta' => [
						'label' => Wp_Helper::__( 'Become a Beta Tester', 'elementor' ),
						'callback' => function() {
							$intro_text = Wp_Helper::__( 'Turn-on Beta Tester, to get notified when a new beta version of Elementor or E-Pro is available. The Beta version will not install automatically. You always have the option to ignore it.', 'elementor' );
							$intro_text = '<p>' . $intro_text . '</p>';
							$newsletter_opt_in_text = sprintf( Wp_Helper::__( 'Click <a id="beta-tester-first-to-know" href="%s">here</a> to join our First-To-Know email updates', 'elementor' ), '#' );

							echo $intro_text;
							echo $newsletter_opt_in_text;
						},
						'fields' => [
							'beta' => [
								'label' => Wp_Helper::__( 'Beta Tester', 'elementor' ),
								'field_args' => [
									'type' => 'select',
									'default' => 'no',
									'options' => [
										'no' => Wp_Helper::__( 'Disable', 'elementor' ),
										'yes' => Wp_Helper::__( 'Enable', 'elementor' ),
									],
									'desc' => '<span style="color: red;">' . Wp_Helper::__( 'Please Note: We do not recommend updating to a beta version on production sites.', 'elementor' ) . '</span>',
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Get tools page title.
	 *
	 * Retrieve the title for the tools page.
	 *
	 * @since 1.5.0
	 * @access protected
	 *
	 * @return string Tools page title.
	 */
	protected function get_page_title() {
		return Wp_Helper::__( 'Tools', 'elementor' );
	}
}
