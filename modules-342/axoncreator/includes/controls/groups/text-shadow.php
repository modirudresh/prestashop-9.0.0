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
 * Elementor text shadow control.
 *
 * A base control for creating text shadow control. Displays input fields to define
 * the text shadow including the horizontal shadow, vertical shadow, shadow blur and
 * shadow color.
 *
 * @since 1.6.0
 */
class Group_Control_Text_Shadow extends Group_Control_Base {

	/**
	 * Fields.
	 *
	 * Holds all the text shadow control fields.
	 *
	 * @since 1.6.0
	 * @access protected
	 * @static
	 *
	 * @var array Text shadow control fields.
	 */
	protected static $fields;

	/**
	 * Get text shadow control type.
	 *
	 * Retrieve the control type, in this case `text-shadow`.
	 *
	 * @since 1.6.0
	 * @access public
	 * @static
	 *
	 * @return string Control type.
	 */
	public static function get_type() {
		return 'text-shadow';
	}

	/**
	 * Init fields.
	 *
	 * Initialize text shadow control fields.
	 *
	 * @since 1.6.0
	 * @access protected
	 *
	 * @return array Control fields.
	 */
	protected function init_fields() {
		$controls = [];

		$controls['text_shadow'] = [
			'label' => Wp_Helper::_x( 'Text Shadow', 'Text Shadow Control', 'elementor' ),
			'type' => Controls_Manager::TEXT_SHADOW,
			'selectors' => [
				'{{SELECTOR}}' => 'text-shadow: {{HORIZONTAL}}px {{VERTICAL}}px {{BLUR}}px {{COLOR}};',
			],
		];

		return $controls;
	}

	/**
	 * Get default options.
	 *
	 * Retrieve the default options of the text shadow control. Used to return the
	 * default options while initializing the text shadow control.
	 *
	 * @since 1.9.0
	 * @access protected
	 *
	 * @return array Default text shadow control options.
	 */
	protected function get_default_options() {
		return [
			'popover' => [
				'starter_title' => Wp_Helper::_x( 'Text Shadow', 'Text Shadow Control', 'elementor' ),
				'starter_name' => 'text_shadow_type',
				'starter_value' => 'yes',
				'settings' => [
					'render_type' => 'ui',
				],
			],
		];
	}
}
