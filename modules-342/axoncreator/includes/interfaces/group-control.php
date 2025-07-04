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
 * Group control interface.
 *
 * An interface for Elementor group control.
 *
 * @since 1.0.0
 */
interface Group_Control_Interface {

	/**
	 * Get group control type.
	 *
	 * Retrieve the group control type.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function get_type();
}
