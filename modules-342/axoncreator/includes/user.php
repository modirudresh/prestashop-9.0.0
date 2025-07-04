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

use AxonCreator\Core\Common\Modules\Ajax\Module as Ajax;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor user.
 *
 * Elementor user handler class is responsible for checking if the user can edit
 * with Elementor and displaying different admin notices.
 *
 * @since 1.0.0
 */
class User {

	/**
	 * The admin notices key.
	 */
	const ADMIN_NOTICES_KEY = 'elementor_admin_notices';

	const INTRODUCTION_KEY = 'elementor_introduction';

	const BETA_TESTER_META_KEY = 'elementor_beta_tester';

	/**
	 * API URL.
	 *
	 * Holds the URL of the Beta Tester Opt-in API.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var string API URL.
	 */
	const BETA_TESTER_API_URL = 'https://my.elementor.com/api/v1/beta_tester/';

	/**
	 * Init.
	 *
	 * Initialize Elementor user.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function init() {
		Wp_Helper::add_action( 'wp_ajax_elementor_set_admin_notice_viewed', [ __CLASS__, 'ajax_set_admin_notice_viewed' ] );
		Wp_Helper::add_action( 'admin_post_elementor_set_admin_notice_viewed', [ __CLASS__, 'ajax_set_admin_notice_viewed' ] );

		Wp_Helper::add_action( 'elementor/ajax/register_actions', [ __CLASS__, 'register_ajax_actions' ] );
	}

	/**
	 * @since 2.1.0
	 * @access public
	 * @static
	 */
	public static function register_ajax_actions( Ajax $ajax ) {
		$ajax->register_ajax_action( 'introduction_viewed', [ __CLASS__, 'set_introduction_viewed' ] );
		$ajax->register_ajax_action( 'beta_tester_signup', [ __CLASS__, 'register_as_beta_tester' ] );
	}

	/**
	 * Is current user can edit.
	 *
	 * Whether the current user can edit the post.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param int $post_id Optional. The post ID. Default is `0`.
	 *
	 * @return bool Whether the current user can edit the post.
	 */
	public static function is_current_user_can_edit( $post_id = 0 ) {
		return true;
	}

	/**
	 * Is current user can access elementor.
	 *
	 * Whether the current user role is not excluded by Elementor Settings.
	 *
	 * @since 2.1.7
	 * @access public
	 * @static
	 *
	 * @return bool True if can access, False otherwise.
	 */
	public static function is_current_user_in_editing_black_list() {
		return true;
	}

	/**
	 * Is current user can edit post type.
	 *
	 * Whether the current user can edit the given post type.
	 *
	 * @since 1.9.0
	 * @access public
	 * @static
	 *
	 * @param string $post_type the post type slug to check.
	 *
	 * @return bool True if can edit, False otherwise.
	 */
	public static function is_current_user_can_edit_post_type( $post_type ) {
		return true;
	}

	/**
	 * Get user notices.
	 *
	 * Retrieve the list of notices for the current user.
	 *
	 * @since 2.0.0
	 * @access private
	 * @static
	 *
	 * @return array A list of user notices.
	 */
	private static function get_user_notices() {
		return get_user_meta( get_current_user_id(), self::ADMIN_NOTICES_KEY, true );
	}

	/**
	 * Is user notice viewed.
	 *
	 * Whether the notice was viewed by the user.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param int $notice_id The notice ID.
	 *
	 * @return bool Whether the notice was viewed by the user.
	 */
	public static function is_user_notice_viewed( $notice_id ) {
		$notices = self::get_user_notices();

		if ( empty( $notices ) || empty( $notices[ $notice_id ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Set admin notice as viewed.
	 *
	 * Flag the user admin notice as viewed using an authenticated ajax request.
	 *
	 * Fired by `wp_ajax_elementor_set_admin_notice_viewed` action.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function ajax_set_admin_notice_viewed() {
		if ( empty( $_REQUEST['notice_id'] ) ) {
			wp_die();
		}

		$notices = self::get_user_notices();
		if ( empty( $notices ) ) {
			$notices = [];
		}

		$notices[ $_REQUEST['notice_id'] ] = 'true';
		update_user_meta( get_current_user_id(), self::ADMIN_NOTICES_KEY, $notices );

		if ( ! wp_doing_ajax() ) {
			wp_safe_redirect( Wp_Helper::admin_url() );
			die;
		}

		wp_die();
	}

	/**
	 * @since 2.1.0
	 * @access public
	 * @static
	 */
	public static function set_introduction_viewed( array $data ) {
		$user_introduction_meta = self::get_introduction_meta();

		$user_introduction_meta[ $data['introductionKey'] ] = true;

		update_user_meta( get_current_user_id(), self::INTRODUCTION_KEY, $user_introduction_meta );
	}

	public static function register_as_beta_tester( array $data ) {
		update_user_meta( get_current_user_id(), self::BETA_TESTER_META_KEY, true );
		wp_safe_remote_post(
			self::BETA_TESTER_API_URL,
			[
				'timeout' => 25,
				'body' => [
					'api_version' => AXON_CREATOR_VERSION,
					'site_lang' => get_bloginfo( 'language' ),
					'beta_tester_email' => $data['betaTesterEmail'],
				],
			]
		);
	}

	/**
	 * @since 2.1.0
	 * @access private
	 * @static
	 */
	public static function get_introduction_meta() {
		$user_introduction_meta = get_user_meta( get_current_user_id(), self::INTRODUCTION_KEY, true );

		if ( ! $user_introduction_meta ) {
			$user_introduction_meta = [];
		}

		return $user_introduction_meta;
	}
}