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
 * Elementor API.
 *
 * Elementor API handler class is responsible for communicating with Elementor
 * remote servers retrieving templates data and to send uninstall feedback.
 *
 * @since 1.0.0
 */
class Api {

	/**
	 * Elementor library option key.
	 */
	const LIBRARY_OPTION_KEY = 'elementor_remote_info_library';

	/**
	 * Elementor feed option key.
	 */
	const FEED_OPTION_KEY = 'elementor_remote_info_feed_data';

	/**
	 * API info URL.
	 *
	 * Holds the URL of the info API.
	 *
	 * @access public
	 * @static
	 *
	 * @var string API info URL.
	 */
	public static $api_info_url = 'https://api.axonviz.com/api_info';

	/**
	 * API feedback URL.
	 *
	 * Holds the URL of the feedback API.
	 *
	 * @access private
	 * @static
	 *
	 * @var string API feedback URL.
	 */
	private static $api_feedback_url = 'https://api.axonviz.com/api_feedback';

	/**
	 * API get template content URL.
	 *
	 * Holds the URL of the template content API.
	 *
	 * @access private
	 * @static
	 *
	 * @var string API get template content URL.
	 */
	private static $api_get_template_content_url = 'https://api.axonviz.com/api_templates?template_id=%d';

	/**
	 * Get info data.
	 *
	 * This function notifies the user of upgrade notices, new templates and contributors.
	 *
	 * @since 2.0.0
	 * @access private
	 * @static
	 *
	 * @param bool $force_update Optional. Whether to force the data retrieval or
	 *                                     not. Default is false.
	 *
	 * @return array|false Info data, or false.
	 */
	private static function get_info_data( $force_update = false ) {
		$cache_key = 'elementor_remote_info_api_data_' . AXON_CREATOR_VERSION;

		$info_data = Wp_Helper::get_transient( $cache_key );

		if ( $force_update || false === $info_data ) {
			$timeout = ( $force_update ) ? 25 : 8;

			$response = Wp_Helper::wp_remote_post( self::$api_info_url, [
				'timeout' => $timeout,
				'body' => [
					// Which API version is used.
					'api_version' => AXON_CREATOR_VERSION,
					// Which language to return.
					'site_lang' => \Context::getContext()->language->iso_code,
					'license' => Wp_Helper::api_get_license_key(),
					'item_name' => Wp_Helper::PRODUCT_NAME,
					'url' => Wp_Helper::getShopDomainSsl(true),
				],
			] );
			
			if ( Wp_Helper::is_wp_error( $response ) || 200 !== (int) Wp_Helper::wp_remote_retrieve_response_code( $response ) ) {
				Wp_Helper::set_transient( $cache_key, [], 2 * AXON_HOUR_IN_SECONDS );

				return false;
			}
			
			$info_data = json_decode( Wp_Helper::wp_remote_retrieve_body( $response ), true );
			
			if ( empty( $info_data ) || ! is_array( $info_data ) ) {
				Wp_Helper::set_transient( $cache_key, [], 2 * AXON_HOUR_IN_SECONDS );

				return false;
			}
			
			if( isset( $info_data['license_status'] ) && !$info_data['license_status'] ){
				Wp_Helper::api_get_license_data( true );
			}

			if ( isset( $info_data['library'] ) ) {
				Wp_Helper::update_option( self::LIBRARY_OPTION_KEY, $info_data['library'], 'no' );

				unset( $info_data['library'] );
			}

			if ( isset( $info_data['feed'] ) ) {
				Wp_Helper::update_option( self::FEED_OPTION_KEY, $info_data['feed'], 'no' );

				unset( $info_data['feed'] );
			}

			Wp_Helper::set_transient( $cache_key, $info_data, 12 * AXON_HOUR_IN_SECONDS );
		}

		return $info_data;
	}

	/**
	 * Get upgrade notice.
	 *
	 * Retrieve the upgrade notice if one exists, or false otherwise.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return array|false Upgrade notice, or false none exist.
	 */
	public static function get_upgrade_notice() {
		$data = self::get_info_data();

		if ( empty( $data['upgrade_notice'] ) ) {
			return false;
		}

		return $data['upgrade_notice'];
	}

	public static function get_canary_deployment_info( $force = false ) {
		$data = self::get_info_data( $force );

		if ( empty( $data['canary_deployment'] ) ) {
			return false;
		}

		return $data['canary_deployment'];
	}

	/**
	 * Get templates data.
	 *
	 * Retrieve the templates data from a remote server.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @param bool $force_update Optional. Whether to force the data update or
	 *                                     not. Default is false.
	 *
	 * @return array The templates data.
	 */
	public static function get_library_data( $force_update = false ) {
		self::get_info_data( $force_update );

		$library_data = Wp_Helper::get_option( self::LIBRARY_OPTION_KEY );

		if ( empty( $library_data ) ) {
			return [];
		}

		return $library_data;
	}

	/**
	 * Get feed data.
	 *
	 * Retrieve the feed info data from remote elementor server.
	 *
	 * @since 1.9.0
	 * @access public
	 * @static
	 *
	 * @param bool $force_update Optional. Whether to force the data update or
	 *                                     not. Default is false.
	 *
	 * @return array Feed data.
	 */
	public static function get_feed_data( $force_update = false ) {
		self::get_info_data( $force_update );

		$feed = Wp_Helper::get_option( self::FEED_OPTION_KEY );

		if ( empty( $feed ) ) {
			return [];
		}

		return $feed;
	}

	/**
	 * Get template content.
	 *
	 * Retrieve the templates content received from a remote server.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param int $template_id The template ID.
	 *
	 * @return array The template content.
	 */
	public static function get_template_content( $template_id ) {
		$url = sprintf( self::$api_get_template_content_url, $template_id );

		$body_args = [
			// Which API version is used.
			'api_version' => AXON_CREATOR_VERSION,
			// Which language to return.
			'site_lang' => \Context::getContext()->language->iso_code,
			'license' => Wp_Helper::api_get_license_key(),
			'item_name' => Wp_Helper::PRODUCT_NAME,
			'url' => Wp_Helper::getShopDomainSsl(true),
		];

		/**
		 * API: Template body args.
		 *
		 * Filters the body arguments send with the GET request when fetching the content.
		 *
		 * @since 1.0.0
		 *
		 * @param array $body_args Body arguments.
		 */
		$body_args = Wp_Helper::apply_filters( 'elementor/api/get_templates/body_args', $body_args );

		$response = Wp_Helper::wp_remote_post( $url, [
			'timeout' => 40,
			'body' => $body_args,
		] );

		if ( Wp_Helper::is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = (int) Wp_Helper::wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			return new \WP_Error( 'response_code_error', sprintf( 'The request returned with a status code of %s.', $response_code ) );
		}

		$template_content = json_decode( Wp_Helper::wp_remote_retrieve_body( $response ), true );

		if ( isset( $template_content['error'] ) ) {
			return new \WP_Error( 'response_error', $template_content['error'] );
		}

		if ( empty( $template_content['data'] ) && empty( $template_content['content'] ) ) {
			return new \WP_Error( 'template_data_error', 'An invalid data was returned.' );
		}

		return $template_content;
	}

	/**
	 * Send Feedback.
	 *
	 * Fires a request to Elementor server with the feedback data.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $feedback_key  Feedback key.
	 * @param string $feedback_text Feedback text.
	 *
	 * @return array The response of the request.
	 */
	public static function send_feedback( $feedback_key, $feedback_text ) {
		return Wp_Helper::wp_remote_post( self::$api_feedback_url, [
			'timeout' => 30,
			'body' => [
				'api_version' => AXON_CREATOR_VERSION,
				'site_lang' => \Context::getContext()->language->iso_code,
				'feedback_key' => $feedback_key,
				'feedback' => $feedback_text,
			],
		] );
	}

	/**
	 * Ajax reset API data.
	 *
	 * Reset Elementor library API data using an ajax call.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function ajax_reset_api_data() {
		Wp_Helper::check_ajax_referer( 'elementor_reset_library', '_nonce' );

		self::get_info_data( true );

		Wp_Helper::wp_send_json_success();
	}

	/**
	 * Init.
	 *
	 * Initialize Elementor API.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function init() {
		Wp_Helper::add_action( 'wp_ajax_elementor_reset_library', [ __CLASS__, 'ajax_reset_api_data' ] );
	}
}