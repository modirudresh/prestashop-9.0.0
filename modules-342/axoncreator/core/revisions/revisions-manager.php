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

namespace AxonCreator\Core\Revisions;

use AxonCreator\Core\Base\Document;
use AxonCreator\Core\Common\Modules\Ajax\Module as Ajax;
use AxonCreator\Core\Files\CSS\Post as Post_CSS;
use AxonCreator\Core\Settings\Manager as SettingsManager;
use AxonCreator\Plugin;
use AxonCreator\Utils;
use AxonCreator\Wp_Helper;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor history revisions manager.
 *
 * Elementor history revisions manager handler class is responsible for
 * registering and managing Elementor revisions manager.
 *
 * @since 1.7.0
 */
class Revisions_Manager {

	/**
	 * Maximum number of revisions to display.
	 */
	const MAX_REVISIONS_TO_DISPLAY = 100;

	/**
	 * Authors list.
	 *
	 * Holds all the authors.
	 *
	 * @access private
	 *
	 * @var array
	 */
	private static $authors = [];

	/**
	 * History revisions manager constructor.
	 *
	 * Initializing Elementor history revisions manager.
	 *
	 * @since 1.7.0
	 * @access public
	 */
	public function __construct() {
		self::register_actions();
	}

	/**
	 * @since 1.7.0
	 * @access public
	 * @static
	 */
	public static function handle_revision() {
		add_filter( 'wp_save_post_revision_check_for_changes', '__return_false' );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @param $post_content
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function avoid_delete_auto_save( $post_content, $post_id ) {
		// Add a temporary string in order the $post will not be equal to the $autosave
		// in edit-form-advanced.php:210
		if ( $post_id && Plugin::$instance->db->is_built_with_elementor( $post_id ) ) {
			$post_content .= '<!-- Created with Elementor -->';
		}

		return $post_content;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 */
	public static function remove_temp_post_content() {
		global $post;

		if ( Plugin::$instance->db->is_built_with_elementor( $post->ID ) ) {
			$post->post_content = str_replace( '<!-- Created with Elementor -->', '', $post->post_content );
		}
	}
	
	
    public static function get_list_revisions() {	
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'axon_creator_revisions` 
				WHERE `id_post` = ' . Wp_Helper::$id_post . '
				AND `id_lang` = ' . Wp_Helper::$id_lang . '
				ORDER BY `date_add`
				DESC LIMIT ' . self::MAX_REVISIONS_TO_DISPLAY;
		
		$list = \Db::getInstance()->executeS( $sql );
				
		$revisions = [];
		
        $id_post = Wp_Helper::$id_post;
        $id_lang = Wp_Helper::$id_lang;
		
		$post = new \AxonCreatorPost( $id_post, $id_lang );
		
		$revisions[] = [
			'id' => 'current',
			'id_employee' => $post->id_employee,
			'date' => $post->date_upd,
		];
		
		foreach ( $list as $key => $post ) {
			$revision = new \AxonCreatorRevisions( $post['id_axon_creator_revisions'] );
			
			$revisions[] = [
				'id' => $revision->id,
				'id_employee' => $revision->id_employee,
				'date' => $revision->date_add,
			];
		}
		
		return $revisions;
    }

	/**
	 * @since 1.7.0
	 * @access public
	 * @static
	 *
	 * @param int   $post_id
	 * @param array $query_args
	 * @param bool  $parse_result
	 *
	 * @return array
	 */
	public static function get_revisions( $post_id = 0, $query_args = [], $parse_result = true ) {
		
        $id_post = Wp_Helper::$id_post;
        $id_lang = Wp_Helper::$id_lang;

		$revisions = [];
		
		$current_time = Wp_Helper::current_time();
										
		$posts = self::get_list_revisions(); // WPCS: unprepared SQL ok.
										
		/** @var \WP_Post $revision */
		foreach ( $posts as $post ) {			
			$date = date( Wp_Helper::_x( 'M j @ H:i', 'revision date format', 'elementor' ), strtotime( $post['date'] ) );
			$human_time = Wp_Helper::human_time_diff( strtotime( $post['date'] ), $current_time );

			if ( $post['id'] == 'current' ) {
				$type = 'current';
			} else {
				$type = 'revision';
			}

			$employee = new \Employee( $post['id_employee'] );
			$display_name = $employee->firstname;

			$revisions[] = [
				'id' => $post['id'],
				'author' => $display_name,
				'timestamp' => strtotime( $post['date'] ),
				'date' => sprintf(
					/* translators: 1: Human readable time difference, 2: Date */
					Wp_Helper::__( '%1$s ago (%2$s)', 'elementor' ),
					$human_time,
					$date
				),
				'type' => $type,
				'gravatar' => '<img alt="" src="' . AXON_CREATOR_ASSETS_URL . 'images/avata.jpg' . '" width="22" height="22">',
			];
		}
		
		return $revisions;
	}

	/**
	 * @since 1.9.2
	 * @access public
	 * @static
	 */
	public static function update_autosave( $autosave_data ) {
		self::save_revision( $autosave_data['ID'] );
	}

	/**
	 * @since 1.7.0
	 * @access public
	 * @static
	 */
	public static function save_revision( $revision_id ) {
		$parent_id = wp_is_post_revision( $revision_id );

		if ( $parent_id ) {
			Plugin::$instance->db->safe_copy_elementor_meta( $parent_id, $revision_id );
		}
	}

	/**
	 * @since 1.7.0
	 * @access public
	 * @static
	 */
	public static function restore_revision( $parent_id, $revision_id ) {
		$is_built_with_elementor = Plugin::$instance->db->is_built_with_elementor( $revision_id );

		Plugin::$instance->db->set_is_elementor_page( $parent_id, $is_built_with_elementor );

		if ( ! $is_built_with_elementor ) {
			return;
		}

		Plugin::$instance->db->copy_elementor_meta( $revision_id, $parent_id );

		$post_css = Post_CSS::create( $parent_id );

		$post_css->update();
	}

	/**
	 * @since 2.3.0
	 * @access public
	 * @static
	 *
	 * @param $data
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function ajax_get_revision_data( array $data ) {
		if ( ! isset( $data['id'] ) ) {
			throw new \Exception( 'You must set the revision ID.' );
		}
		
        $id_lang = Wp_Helper::$id_lang;
		
		if( $data['id'] == 'current' ){
			$revision = new \AxonCreatorPost( Wp_Helper::$id_post, $id_lang );

			$settings = SettingsManager::get_settings_managers( 'page' )->get_model( Wp_Helper::$id_post )->get_data( 'settings' );	
		}else{
			$revision = new \AxonCreatorRevisions( $data['id'] );

			$settings = json_decode( $revision->page_settings, true );	
		}

		if ( ! $revision ) {
			throw new \Exception( 'Invalid revision.' );
		}
				
		$revision_data = [
			'settings' => $settings,
			'elements' => json_decode( $revision->content, true ),
		];

		return $revision_data;
	}

	/**
	 * @since 2.3.0
	 * @access public
	 * @static
	 *
	 * @param array $data
	 *
	 * @throws \Exception
	 */
	public static function ajax_delete_revision( array $data ) {
		if ( empty( $data['id'] ) ) {
			throw new \Exception( 'You must set the revision ID.' );
		}
		
		$return = true;
		
		if( $data['id'] != 'current' ){
			$revision = new \AxonCreatorRevisions( $data['id'] );
			$return &= $revision->delete();
		}
		
		return $return;
	}

	/**
	 * @since 1.7.0
	 * @access public
	 * @static
	 */
	public static function add_revision_support_for_all_post_types() {
		$post_types = get_post_types_by_support( 'elementor' );
		foreach ( $post_types as $post_type ) {
			add_post_type_support( $post_type, 'revisions' );
		}
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 * @param array $return_data
	 * @param Document $document
	 *
	 * @return array
	 */
	public static function on_ajax_save_builder_data( $return_data, $document ) {		
					
		$latest_revisions = self::get_revisions();
		
		$all_revision_ids = [];
		
		foreach ( $latest_revisions as $key => $revision_id ) {
			$all_revision_ids[] = $revision_id['id'];
		}

		// Send revisions data only if has revisions.
		if ( ! empty( $latest_revisions ) ) {
			$current_revision_id = self::current_revision_id( Wp_Helper::$id_post );

			$return_data = array_replace_recursive( $return_data, [
				'config' => [
					'current_revision_id' => $current_revision_id,
				],
				'latest_revisions' => $latest_revisions,
				'revisions_ids' => $all_revision_ids,
			] );
		}

		return $return_data;
	}

	/**
	 * @since 1.7.0
	 * @access public
	 * @static
	 */
	public static function db_before_save( $status, $has_changes ) {
		if ( $has_changes ) {
			self::handle_revision();
		}
	}

	/**
	 * Localize settings.
	 *
	 * Add new localized settings for the revisions manager.
	 *
	 * Fired by `elementor/editor/localize_settings` filter.
	 *
	 * @since 1.7.0
	 * @access public
	 * @static
	 */
	public static function editor_settings( $settings, $post_id ) {
				
		$settings = array_replace_recursive( $settings, [
			'revisions_enabled' => ( Wp_Helper::$id_post && true ),
			'current_revision_id' => self::current_revision_id( Wp_Helper::$id_post ),
			'i18n' => [
				'edit_draft' => Wp_Helper::__( 'Edit Draft', 'elementor' ),
				'edit_published' => Wp_Helper::__( 'Edit Published', 'elementor' ),
				'no_revisions_1' => Wp_Helper::__( 'Revision history lets you save your previous versions of your work, and restore them any time.', 'elementor' ),
				'no_revisions_2' => Wp_Helper::__( 'Start designing your page and you\'ll be able to see the entire revision history here.', 'elementor' ),
				'current' => Wp_Helper::__( 'Current Version', 'elementor' ),
				'restore' => Wp_Helper::__( 'Restore', 'elementor' ),
				'restore_auto_saved_data' => Wp_Helper::__( 'Restore Auto Saved Data', 'elementor' ),
				'restore_auto_saved_data_message' => Wp_Helper::__( 'There is an autosave of this post that is more recent than the version below. You can restore the saved data fron the Revisions panel', 'elementor' ),
				'revision' => Wp_Helper::__( 'Revision', 'elementor' ),
				'revision_history' => Wp_Helper::__( 'Revision History', 'elementor' ),
				'revisions_disabled_1' => Wp_Helper::__( 'It looks like the post revision feature is unavailable in your website.', 'elementor' ),
				'revisions_disabled_2' => sprintf(
					/* translators: %s: Codex URL */
					Wp_Helper::esc_html( 'Learn more about <a target="_blank" href="%s">WordPress revisions</a>', 'elementor' ),
					'https://codex.wordpress.org/Revisions#Revision_Options'
				),
			],
		] );

		return $settings;
	}

	public static function ajax_get_revisions() {
		return self::get_revisions();
	}

	/**
	 * @since 2.3.0
	 * @access public
	 * @static
	 */
	public static function register_ajax_actions( Ajax $ajax ) {
		$ajax->register_ajax_action( 'get_revisions', [ __CLASS__, 'ajax_get_revisions' ] );
		$ajax->register_ajax_action( 'get_revision_data', [ __CLASS__, 'ajax_get_revision_data' ] );
		$ajax->register_ajax_action( 'delete_revision', [ __CLASS__, 'ajax_delete_revision' ] );
	}

	/**
	 * @since 1.7.0
	 * @access private
	 * @static
	 */
	private static function register_actions() {
		Wp_Helper::add_action( 'wp_restore_post_revision', [ __CLASS__, 'restore_revision' ], 10, 2 );
		Wp_Helper::add_action( 'init', [ __CLASS__, 'add_revision_support_for_all_post_types' ], 9999 );
		Wp_Helper::add_filter( 'elementor/editor/localize_settings', [ __CLASS__, 'editor_settings' ], 10, 2 );
		Wp_Helper::add_action( 'elementor/db/before_save', [ __CLASS__, 'db_before_save' ], 10, 2 );
		Wp_Helper::add_action( '_wp_put_post_revision', [ __CLASS__, 'save_revision' ] );
		Wp_Helper::add_action( 'wp_creating_autosave', [ __CLASS__, 'update_autosave' ] );
		Wp_Helper::add_action( 'elementor/ajax/register_actions', [ __CLASS__, 'register_ajax_actions' ] );

		// Hack to avoid delete the auto-save revision in WP editor.
		Wp_Helper::add_filter( 'edit_post_content', [ __CLASS__, 'avoid_delete_auto_save' ], 10, 2 );
		Wp_Helper::add_action( 'edit_form_after_title', [ __CLASS__, 'remove_temp_post_content' ] );
		
		Wp_Helper::add_filter( 'elementor/documents/ajax_save/return_data', [ __CLASS__, 'on_ajax_save_builder_data' ], 10, 2 );
	}

	/**
	 * @since 1.9.0
	 * @access private
	 * @static
	 */
	private static function current_revision_id( $post_id ) {		
		$current_revision_id = $post_id;

		return 'current';
	}
}
