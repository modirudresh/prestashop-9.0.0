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

use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

/**
 * https://github.com/A5hleyRich/wp-background-processing GPL v2.0
 *
 * WP Async Request
 *
 * @package WP-Background-Processing
 */

if ( ! class_exists( 'WP_Async_Request' ) ) {

	/**
	 * Abstract WP_Async_Request class.
	 *
	 * @abstract
	 */
	abstract class WP_Async_Request {

		/**
		 * Prefix
		 *
		 * (default value: 'wp')
		 *
		 * @var string
		 * @access protected
		 */
		protected $prefix = 'wp';

		/**
		 * Action
		 *
		 * (default value: 'async_request')
		 *
		 * @var string
		 * @access protected
		 */
		protected $action = 'async_request';

		/**
		 * Identifier
		 *
		 * @var mixed
		 * @access protected
		 */
		protected $identifier;

		/**
		 * Data
		 *
		 * (default value: array())
		 *
		 * @var array
		 * @access protected
		 */
		protected $data = array();

		/**
		 * Initiate new async request
		 */
		public function __construct() {
			$this->identifier = $this->prefix . '_' . $this->action;

			Wp_Helper::add_action( 'wp_ajax_' . $this->identifier, array( $this, 'maybe_handle' ) );
			Wp_Helper::add_action( 'wp_ajax_nopriv_' . $this->identifier, array( $this, 'maybe_handle' ) );
		}

		/**
		 * Set data used during the request
		 *
		 * @param array $data Data.
		 *
		 * @return $this
		 */
		public function data( $data ) {
			$this->data = $data;

			return $this;
		}

		/**
		 * Dispatch the async request
		 *
		 * @return array|WP_Error
		 */
		public function dispatch() {
			$url  = add_query_arg( $this->get_query_args(), $this->get_query_url() );
			$args = $this->get_post_args();

			return Wp_Helper::wp_remote_post( esc_url_raw( $url ), $args );
		}

		/**
		 * Get query args
		 *
		 * @return array
		 */
		protected function get_query_args() {
			if ( property_exists( $this, 'query_args' ) ) {
				return $this->query_args;
			}

			return array(
				'action' => $this->identifier,
				'nonce'  => wp_create_nonce( $this->identifier ),
			);
		}

		/**
		 * Get query URL
		 *
		 * @return string
		 */
		protected function get_query_url() {
			if ( property_exists( $this, 'query_url' ) ) {
				return $this->query_url;
			}

			return Wp_Helper::admin_url( 'admin-ajax.php' );
		}

		/**
		 * Get post args
		 *
		 * @return array
		 */
		protected function get_post_args() {
			if ( property_exists( $this, 'post_args' ) ) {
				return $this->post_args;
			}

			return array(
				'timeout'   => 0.01,
				'blocking'  => false,
				'body'      => $this->data,
				'cookies'   => $_COOKIE,
				'sslverify' => Wp_Helper::apply_filters( 'https_local_ssl_verify', false ),
			);
		}

		/**
		 * Maybe handle
		 *
		 * Check for correct nonce and pass to handler.
		 */
		public function maybe_handle() {
			// Don't lock up other requests while processing
			session_write_close();

			check_ajax_referer( $this->identifier, 'nonce' );

			$this->handle();

			wp_die();
		}

		/**
		 * Handle
		 *
		 * Override this method to perform any actions required
		 * during the async request.
		 */
		abstract protected function handle();

	}
}