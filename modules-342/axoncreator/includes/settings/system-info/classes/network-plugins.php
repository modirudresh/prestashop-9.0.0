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
 * Elementor network plugins report.
 *
 * Elementor system report handler class responsible for generating a report for
 * network plugins.
 *
 * @since 1.0.0
 */
class Network_Plugins_Reporter extends Base_Reporter {

	/**
	 * Network plugins.
	 *
	 * Holds the sites network plugins list.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array
	 */
	private $plugins;

	/**
	 * Get network plugins reporter title.
	 *
	 * Retrieve network plugins reporter title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Reporter title.
	 */
	public function get_title() {
		return 'Network Plugins';
	}

	/**
	 * Get active network plugins.
	 *
	 * Retrieve the active network plugins from the list of active site-wide plugins.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @return array Active network plugins.
	 */
	private function get_network_plugins() {
		if ( ! $this->plugins ) {
			$active_plugins = get_site_option( 'active_sitewide_plugins' );
			$this->plugins = array_intersect_key( get_plugins(), $active_plugins );
		}

		return $this->plugins;
	}

	/**
	 * Is enabled.
	 *
	 * Whether there are active network plugins or not.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if the site has active network plugins, False otherwise.
	 */
	public function is_enabled() {
		if ( ! is_multisite() ) {
			return false;
		};

		return ! ! $this->get_network_plugins();
	}

	/**
	 * Get network plugins report fields.
	 *
	 * Retrieve the required fields for the network plugins report.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Required report fields with field ID and field label.
	 */
	public function get_fields() {
		return [
			'network_active_plugins' => 'Network Plugins',
		];
	}

	/**
	 * Get active network plugins.
	 *
	 * Retrieve the sites active network plugins.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array {
	 *    Report data.
	 *
	 *    @type string $value The active network plugins list.
	 * }
	 */
	public function get_network_active_plugins() {
		return [
			'value' => $this->get_network_plugins(),
		];
	}
}
