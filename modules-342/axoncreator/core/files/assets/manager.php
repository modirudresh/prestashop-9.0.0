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

namespace AxonCreator\Core\Files\Assets;

use AxonCreator\Core\Files\Assets\Svg\Svg_Handler;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor files manager.
 *
 * Elementor files manager handler class is responsible for creating files.
 *
 * @since 2.6.0
 */
class Manager {

	/**
	 * Holds registered asset types
	 * @var array
	 */
	protected $asset_types = [];

	/**
	 * Assets manager constructor.
	 *
	 * Initializing the Elementor assets manager.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->register_asset_types();
		/**
		 * Elementor files assets registered.
		 *
		 * Fires after Elementor registers assets types
		 *
		 * @since 2.6.0
		 */
		Wp_Helper::do_action( 'elementor/core/files/assets/assets_registered', $this );
	}

	public function get_asset( $name ) {
		return isset( $this->asset_types[ $name ] ) ? $this->asset_types[ $name ] : false;
	}

	/**
	 * Add Asset
	 * @param $instance
	 */
	public function add_asset( $instance ) {
		$this->asset_types[ $instance::get_name() ] = $instance;
	}


	/**
	 * Register Asset Types
	 *
	 * Registers Elementor Asset Types
	 */
	private function register_asset_types() {
		$this->add_asset( new Svg_Handler() );
	}
}
