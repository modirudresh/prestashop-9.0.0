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

namespace AxonCreator\Core\DynamicTags;

use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor base data tag.
 *
 * An abstract class to register new Elementor data tags.
 *
 * @since 2.0.0
 * @abstract
 */
abstract class Data_Tag extends Base_Tag {

	/**
	 * @since 2.0.0
	 * @access protected
	 * @abstract
	 *
	 * @param array $options
	 */
	abstract protected function get_value( array $options = [] );

	/**
	 * @since 2.0.0
	 * @access public
	 */
	final public function get_content_type() {
		return 'plain';
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function get_content( array $options = [] ) {
		return $this->get_value( $options );
	}
}
