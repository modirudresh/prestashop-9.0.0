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

use AxonCreator\Core\Settings\Manager as SettingsManager;
use AxonCreator\Wp_Helper;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor typography control.
 *
 * A base control for creating typography control. Displays input fields to define
 * the content typography including font size, font family, font weight, text
 * transform, font style, line height and letter spacing.
 *
 * @since 1.0.0
 */
class Group_Control_Typography extends Group_Control_Base {

	/**
	 * Fields.
	 *
	 * Holds all the typography control fields.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 *
	 * @var array Typography control fields.
	 */
	protected static $fields;

	/**
	 * Scheme fields keys.
	 *
	 * Holds all the typography control scheme fields keys.
	 * Default is an array containing `font_family` and `font_weight`.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var array Typography control scheme fields keys.
	 */
	private static $_scheme_fields_keys = [ 'font_family', 'font_weight' ];

	/**
	 * Get scheme fields keys.
	 *
	 * Retrieve all the available typography control scheme fields keys.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return array Scheme fields keys.
	 */
	public static function get_scheme_fields_keys() {
		return self::$_scheme_fields_keys;
	}

	/**
	 * Get typography control type.
	 *
	 * Retrieve the control type, in this case `typography`.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return string Control type.
	 */
	public static function get_type() {
		return 'typography';
	}

	/**
	 * Init fields.
	 *
	 * Initialize typography control fields.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @return array Control fields.
	 */
	protected function init_fields() {
		$fields = [];

        $default_fonts = SettingsManager::get_settings_managers( 'general' )->get_model()->get_settings( 'elementor_default_generic_fonts' );

		if ( $default_fonts ) {
			$default_fonts = ', ' . $default_fonts;
		}

		$fields['font_family'] = [
			'label' => Wp_Helper::_x( 'Family', 'Typography Control', 'elementor' ),
			'type' => Controls_Manager::FONT,
			'default' => '',
			'selector_value' => 'font-family: "{{VALUE}}"' . $default_fonts . ';',
		];

		$fields['font_size'] = [
			'label' => Wp_Helper::_x( 'Size', 'Typography Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'size_units' => [ 'px', 'em', 'rem', 'vw' ],
			'range' => [
				'px' => [
					'min' => 1,
					'max' => 200,
				],
				'vw' => [
					'min' => 0.1,
					'max' => 10,
					'step' => 0.1,
				],
			],
			'responsive' => true,
			'selector_value' => 'font-size: {{SIZE}}{{UNIT}}',
		];

		$typo_weight_options = [
			'' => Wp_Helper::__( 'Default', 'elementor' ),
		];

		foreach ( array_merge( [ 'normal', 'bold' ], range( 100, 900, 100 ) ) as $weight ) {
			$typo_weight_options[ $weight ] = ucfirst( $weight );
		}

		$fields['font_weight'] = [
			'label' => Wp_Helper::_x( 'Weight', 'Typography Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'default' => '',
			'options' => $typo_weight_options,
		];

		$fields['text_transform'] = [
			'label' => Wp_Helper::_x( 'Transform', 'Typography Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'default' => '',
			'options' => [
				'' => Wp_Helper::__( 'Default', 'elementor' ),
				'uppercase' => Wp_Helper::_x( 'Uppercase', 'Typography Control', 'elementor' ),
				'lowercase' => Wp_Helper::_x( 'Lowercase', 'Typography Control', 'elementor' ),
				'capitalize' => Wp_Helper::_x( 'Capitalize', 'Typography Control', 'elementor' ),
				'none' => Wp_Helper::_x( 'Normal', 'Typography Control', 'elementor' ),
			],
		];

		$fields['font_style'] = [
			'label' => Wp_Helper::_x( 'Style', 'Typography Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'default' => '',
			'options' => [
				'' => Wp_Helper::__( 'Default', 'elementor' ),
				'normal' => Wp_Helper::_x( 'Normal', 'Typography Control', 'elementor' ),
				'italic' => Wp_Helper::_x( 'Italic', 'Typography Control', 'elementor' ),
				'oblique' => Wp_Helper::_x( 'Oblique', 'Typography Control', 'elementor' ),
			],
		];

		$fields['text_decoration'] = [
			'label' => Wp_Helper::_x( 'Decoration', 'Typography Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'default' => '',
			'options' => [
				'' => Wp_Helper::__( 'Default', 'elementor' ),
				'underline' => Wp_Helper::_x( 'Underline', 'Typography Control', 'elementor' ),
				'overline' => Wp_Helper::_x( 'Overline', 'Typography Control', 'elementor' ),
				'line-through' => Wp_Helper::_x( 'Line Through', 'Typography Control', 'elementor' ),
				'none' => Wp_Helper::_x( 'None', 'Typography Control', 'elementor' ),
			],
		];

		$fields['line_height'] = [
			'label' => Wp_Helper::_x( 'Line-Height', 'Typography Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'desktop_default' => [
				'unit' => 'em',
			],
			'tablet_default' => [
				'unit' => 'em',
			],
			'mobile_default' => [
				'unit' => 'em',
			],
			'range' => [
				'px' => [
					'min' => 1,
				],
			],
			'responsive' => true,
			'size_units' => [ 'px', 'em' ],
			'selector_value' => 'line-height: {{SIZE}}{{UNIT}}',
		];

		$fields['letter_spacing'] = [
			'label' => Wp_Helper::_x( 'Letter Spacing', 'Typography Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'range' => [
				'px' => [
					'min' => -5,
					'max' => 10,
					'step' => 0.1,
				],
			],
			'responsive' => true,
			'selector_value' => 'letter-spacing: {{SIZE}}{{UNIT}}',
		];

		return $fields;
	}

	/**
	 * Prepare fields.
	 *
	 * Process typography control fields before adding them to `add_control()`.
	 *
	 * @since 1.2.3
	 * @access protected
	 *
	 * @param array $fields Typography control fields.
	 *
	 * @return array Processed fields.
	 */
	protected function prepare_fields( $fields ) {
		array_walk(
			$fields, function( &$field, $field_name ) {
				if ( in_array( $field_name, [ 'typography', 'popover_toggle' ] ) ) {
					return;
				}

				$selector_value = ! empty( $field['selector_value'] ) ? $field['selector_value'] : str_replace( '_', '-', $field_name ) . ': {{VALUE}};';

				$field['selectors'] = [
					'{{SELECTOR}}' => $selector_value,
				];
			}
		);

		return parent::prepare_fields( $fields );
	}

	/**
	 * Add group arguments to field.
	 *
	 * Register field arguments to typography control.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @param string $control_id Typography control id.
	 * @param array  $field_args Typography control field arguments.
	 *
	 * @return array Field arguments.
	 */
	protected function add_group_args_to_field( $control_id, $field_args ) {
		$field_args = parent::add_group_args_to_field( $control_id, $field_args );

		$args = $this->get_args();

		if ( in_array( $control_id, self::get_scheme_fields_keys() ) && ! empty( $args['scheme'] ) ) {
			$field_args['scheme'] = [
				'type' => self::get_type(),
				'value' => $args['scheme'],
				'key' => $control_id,
			];
		}

		return $field_args;
	}

	/**
	 * Get default options.
	 *
	 * Retrieve the default options of the typography control. Used to return the
	 * default options while initializing the typography control.
	 *
	 * @since 1.9.0
	 * @access protected
	 *
	 * @return array Default typography control options.
	 */
	protected function get_default_options() {
		return [
			'popover' => [
				'starter_name' => 'typography',
				'starter_title' => Wp_Helper::_x( 'Typography', 'Typography Control', 'elementor' ),
				'settings' => [
					'render_type' => 'ui',
				],
			],
		];
	}
}
