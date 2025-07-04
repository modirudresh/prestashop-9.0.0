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

namespace AxonCreator\Core\Debug\Classes;

use AxonCreator\Wp_Helper; 

abstract class Inspection_Base {

	/**
	 * @return bool
	 */
	abstract public function run();

	/**
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * @return string
	 */
	abstract public function get_message();

	/**
	 * @return string
	 */
	public function get_header_message() {
		return Wp_Helper::__( 'The preview could not be loaded', 'elementor' );
	}

	/**
	 * @return string
	 */
	abstract public function get_help_doc_url();
}
