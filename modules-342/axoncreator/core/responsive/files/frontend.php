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

namespace AxonCreator\Core\Responsive\Files;

use AxonCreator\Core\Files\Base;
use AxonCreator\Core\Responsive\Responsive;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

class Frontend extends Base {

	const META_KEY = 'elementor-custom-breakpoints-files';

	private $template_file;

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function __construct( $file_name, $template_file = null ) {
		$this->template_file = $template_file;

		parent::__construct( $file_name );
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function parse_content() {
		$breakpoints = Responsive::get_breakpoints();

		$breakpoints_keys = array_keys( $breakpoints );

		$file_content = \Tools::file_get_contents( $this->template_file );

		$file_content = preg_replace_callback( '/AXON_CREATOR_SCREEN_([A-Z]+)_([A-Z]+)/', function ( $placeholder_data ) use ( $breakpoints_keys, $breakpoints ) {
			$breakpoint_index = array_search( strtolower( $placeholder_data[1] ), $breakpoints_keys );

			$is_max_point = 'MAX' === $placeholder_data[2];

			if ( $is_max_point ) {
				$breakpoint_index++;
			}

			$value = $breakpoints[ $breakpoints_keys[ $breakpoint_index ] ];

			if ( $is_max_point ) {
				$value--;
			}

			return $value . 'px';
		}, $file_content );

		return $file_content;
	}

	/**
	 * Load meta.
	 *
	 * Retrieve the file meta data.
	 *
	 * @since 2.1.0
	 * @access protected
	 */
	protected function load_meta() {
		$option = $this->load_meta_option();

		$file_meta_key = $this->get_file_meta_key();

		if ( empty( $option[ $file_meta_key ] ) ) {
			return [];
		}

		return $option[ $file_meta_key ];
	}

	/**
	 * Update meta.
	 *
	 * Update the file meta data.
	 *
	 * @since 2.1.0
	 * @access protected
	 *
	 * @param array $meta New meta data.
	 */
	protected function update_meta( $meta ) {
		$option = $this->load_meta_option();

		$option[ $this->get_file_meta_key() ] = $meta;

		Wp_Helper::update_option( static::META_KEY, $option );
	}

	/**
	 * Delete meta.
	 *
	 * Delete the file meta data.
	 *
	 * @since 2.1.0
	 * @access protected
	 */
	protected function delete_meta() {
		$option = $this->load_meta_option();

		$file_meta_key = $this->get_file_meta_key();

		if ( isset( $option[ $file_meta_key ] ) ) {
			unset( $option[ $file_meta_key ] );
		}

		if ( $option ) {
			Wp_Helper::update_option( static::META_KEY, $option );
		} else {
			delete_option( static::META_KEY );
		}
	}

	/**
	 * @since 2.1.0
	 * @access private
	 */
	private function get_file_meta_key() {
		return pathinfo( $this->get_file_name(), PATHINFO_FILENAME );
	}

	/**
	 * @since 2.1.0
	 * @access private
	 */
	private function load_meta_option() {
		$option = Wp_Helper::get_option( static::META_KEY );

		if ( ! $option ) {
			$option = [];
		}

		return $option;
	}
}
