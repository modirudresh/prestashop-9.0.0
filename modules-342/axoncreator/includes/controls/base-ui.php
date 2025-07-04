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
 * Elementor base UI control.
 *
 * An abstract class for creating new UI controls in the panel.
 *
 * @abstract
 */
abstract class Base_UI_Control extends Base_Control {

	/**
	 * Get features.
	 *
	 * Retrieve the list of all the available features.
	 *
	 * @since 1.5.0
	 * @access public
	 * @static
	 *
	 * @return array Features array.
	 */
	public static function get_features() {
		return [ 'ui' ];
	}
}
