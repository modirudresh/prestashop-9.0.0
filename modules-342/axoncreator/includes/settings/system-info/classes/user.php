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

namespace AxonCreator\System_Info\Classes;

use AxonCreator\System_Info\Classes\Abstracts\Base_Reporter;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor user report.
 *
 * Elementor system report handler class responsible for generating a report for
 * the user.
 *
 * @since 1.0.0
 */
class User_Reporter extends Base_Reporter {

	/**
	 * Get user reporter title.
	 *
	 * Retrieve user reporter title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Reporter title.
	 */
	public function get_title() {
		return 'User';
	}

	/**
	 * Get user report fields.
	 *
	 * Retrieve the required fields for the user report.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Required report fields with field ID and field label.
	 */
	public function get_fields() {
		return [
			'role' => 'Role',
			'locale' => 'WP Profile lang',
			'agent' => 'User Agent',
		];
	}

	/**
	 * Get user role.
	 *
	 * Retrieve the user role.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value The user role.
	 * }
	 */
	public function get_role() {
		$role = null;

		$current_user = wp_get_current_user();
		if ( ! empty( $current_user->roles ) ) {
			$role = $current_user->roles[0];
		}

		return [
			'value' => $role,
		];
	}

	/**
	 * Get user profile language.
	 *
	 * Retrieve the user profile language.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value User profile language.
	 * }
	 */
	public function get_locale() {
		return [
			'value' => get_locale(),
		];
	}

	/**
	 * Get user agent.
	 *
	 * Retrieve user agent.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value HTTP user agent.
	 * }
	 */
	public function get_agent() {
		return [
			'value' => Wp_Helper::esc_html( $_SERVER['HTTP_USER_AGENT'] ),
		];
	}
}
