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
 * Elementor background control.
 *
 * A base control for creating background control. Displays input fields to define
 * the background color, background image, background gradient or background video.
 *
 * @since 1.2.2
 */
class Group_Control_Background extends Group_Control_Base {

	/**
	 * Fields.
	 *
	 * Holds all the background control fields.
	 *
	 * @since 1.2.2
	 * @access protected
	 * @static
	 *
	 * @var array Background control fields.
	 */
	protected static $fields;

	/**
	 * Background Types.
	 *
	 * Holds all the available background types.
	 *
	 * @since 1.2.2
	 * @access private
	 * @static
	 *
	 * @var array
	 */
	private static $background_types;

	/**
	 * Get background control type.
	 *
	 * Retrieve the control type, in this case `background`.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return string Control type.
	 */
	public static function get_type() {
		return 'background';
	}

	/**
	 * Get background control types.
	 *
	 * Retrieve available background types.
	 *
	 * @since 1.2.2
	 * @access public
	 * @static
	 *
	 * @return array Available background types.
	 */
	public static function get_background_types() {
		if ( null === self::$background_types ) {
			self::$background_types = self::get_default_background_types();
		}

		return self::$background_types;
	}

	/**
	 * Get Default background types.
	 *
	 * Retrieve background control initial types.
	 *
	 * @since 2.0.0
	 * @access private
	 * @static
	 *
	 * @return array Default background types.
	 */
	private static function get_default_background_types() {
		return [
			'classic' => [
				'title' => Wp_Helper::_x( 'Classic', 'Background Control', 'elementor' ),
				'icon' => 'eicon-paint-brush',
			],
			'gradient' => [
				'title' => Wp_Helper::_x( 'Gradient', 'Background Control', 'elementor' ),
				'icon' => 'eicon-barcode',
			],
			'video' => [
				'title' => Wp_Helper::_x( 'Background Video', 'Background Control', 'elementor' ),
				'icon' => 'eicon-video-camera',
			],
		];
	}

	/**
	 * Init fields.
	 *
	 * Initialize background control fields.
	 *
	 * @since 1.2.2
	 * @access public
	 *
	 * @return array Control fields.
	 */
	public function init_fields() {
		$fields = [];

		$fields['background'] = [
			'label' => Wp_Helper::_x( 'Background Type', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::CHOOSE,
			'label_block' => false,
			'render_type' => 'ui',
		];

		$fields['color'] = [
			'label' => Wp_Helper::_x( 'Color', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::COLOR,
			'default' => '',
			'title' => Wp_Helper::_x( 'Background Color', 'Background Control', 'elementor' ),
			'selectors' => [
				'{{SELECTOR}}' => 'background-color: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'classic', 'gradient' ],
			],
		];

		$fields['color_stop'] = [
			'label' => Wp_Helper::_x( 'Location', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'size_units' => [ '%' ],
			'default' => [
				'unit' => '%',
				'size' => 0,
			],
			'render_type' => 'ui',
			'condition' => [
				'background' => [ 'gradient' ],
			],
			'of_type' => 'gradient',
		];

		$fields['color_b'] = [
			'label' => Wp_Helper::_x( 'Second Color', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::COLOR,
			'default' => '',
			'render_type' => 'ui',
			'condition' => [
				'background' => [ 'gradient' ],
			],
			'of_type' => 'gradient',
		];

		$fields['color_b_stop'] = [
			'label' => Wp_Helper::_x( 'Location', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'size_units' => [ '%' ],
			'default' => [
				'unit' => '%',
				'size' => 100,
			],
			'render_type' => 'ui',
			'condition' => [
				'background' => [ 'gradient' ],
			],
			'of_type' => 'gradient',
		];

		$fields['gradient_type'] = [
			'label' => Wp_Helper::_x( 'Type', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'options' => [
				'linear' => Wp_Helper::_x( 'Linear', 'Background Control', 'elementor' ),
				'radial' => Wp_Helper::_x( 'Radial', 'Background Control', 'elementor' ),
			],
			'default' => 'linear',
			'render_type' => 'ui',
			'condition' => [
				'background' => [ 'gradient' ],
			],
			'of_type' => 'gradient',
		];

		$fields['gradient_angle'] = [
			'label' => Wp_Helper::_x( 'Angle', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'size_units' => [ 'deg' ],
			'default' => [
				'unit' => 'deg',
				'size' => 180,
			],
			'range' => [
				'deg' => [
					'step' => 10,
				],
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-color: transparent; background-image: linear-gradient({{SIZE}}{{UNIT}}, {{color.VALUE}} {{color_stop.SIZE}}{{color_stop.UNIT}}, {{color_b.VALUE}} {{color_b_stop.SIZE}}{{color_b_stop.UNIT}})',
			],
			'condition' => [
				'background' => [ 'gradient' ],
				'gradient_type' => 'linear',
			],
			'of_type' => 'gradient',
		];

		$fields['gradient_position'] = [
			'label' => Wp_Helper::_x( 'Position', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'options' => [
				'center center' => Wp_Helper::_x( 'Center Center', 'Background Control', 'elementor' ),
				'center left' => Wp_Helper::_x( 'Center Left', 'Background Control', 'elementor' ),
				'center right' => Wp_Helper::_x( 'Center Right', 'Background Control', 'elementor' ),
				'top center' => Wp_Helper::_x( 'Top Center', 'Background Control', 'elementor' ),
				'top left' => Wp_Helper::_x( 'Top Left', 'Background Control', 'elementor' ),
				'top right' => Wp_Helper::_x( 'Top Right', 'Background Control', 'elementor' ),
				'bottom center' => Wp_Helper::_x( 'Bottom Center', 'Background Control', 'elementor' ),
				'bottom left' => Wp_Helper::_x( 'Bottom Left', 'Background Control', 'elementor' ),
				'bottom right' => Wp_Helper::_x( 'Bottom Right', 'Background Control', 'elementor' ),
			],
			'default' => 'center center',
			'selectors' => [
				'{{SELECTOR}}' => 'background-color: transparent; background-image: radial-gradient(at {{VALUE}}, {{color.VALUE}} {{color_stop.SIZE}}{{color_stop.UNIT}}, {{color_b.VALUE}} {{color_b_stop.SIZE}}{{color_b_stop.UNIT}})',
			],
			'condition' => [
				'background' => [ 'gradient' ],
				'gradient_type' => 'radial',
			],
			'of_type' => 'gradient',
		];

		$fields['image'] = [
			'label' => Wp_Helper::_x( 'Image', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::MEDIA,
			'responsive' => true,
			'title' => Wp_Helper::_x( 'Background Image', 'Background Control', 'elementor' ),
			'selectors' => [
				'{{SELECTOR}}' => 'background-image: url("{{URL}}");',
			],
			'render_type' => 'template',
			'condition' => [
				'background' => [ 'classic' ],
			],
		];

		$fields['position'] = [
			'label' => Wp_Helper::_x( 'Position', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'default' => '',
			'responsive' => true,
			'options' => [
				'' => Wp_Helper::_x( 'Default', 'Background Control', 'elementor' ),
				'top left' => Wp_Helper::_x( 'Top Left', 'Background Control', 'elementor' ),
				'top center' => Wp_Helper::_x( 'Top Center', 'Background Control', 'elementor' ),
				'top right' => Wp_Helper::_x( 'Top Right', 'Background Control', 'elementor' ),
				'center left' => Wp_Helper::_x( 'Center Left', 'Background Control', 'elementor' ),
				'center center' => Wp_Helper::_x( 'Center Center', 'Background Control', 'elementor' ),
				'center right' => Wp_Helper::_x( 'Center Right', 'Background Control', 'elementor' ),
				'bottom left' => Wp_Helper::_x( 'Bottom Left', 'Background Control', 'elementor' ),
				'bottom center' => Wp_Helper::_x( 'Bottom Center', 'Background Control', 'elementor' ),
				'bottom right' => Wp_Helper::_x( 'Bottom Right', 'Background Control', 'elementor' ),
				'initial' => Wp_Helper::_x( 'Custom', 'Background Control', 'elementor' ),

			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-position: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'image[url]!' => '',
			],
		];

		$fields['xpos'] = [
			'label' => Wp_Helper::_x( 'X Position', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'responsive' => true,
			'size_units' => [ 'px', 'em', '%', 'vw' ],
			'default' => [
				'unit' => 'px',
				'size' => 0,
			],
			'tablet_default' => [
				'unit' => 'px',
				'size' => 0,
			],
			'mobile_default' => [
				'unit' => 'px',
				'size' => 0,
			],
			'range' => [
				'px' => [
					'min' => -800,
					'max' => 800,
				],
				'em' => [
					'min' => -100,
					'max' => 100,
				],
				'%' => [
					'min' => -100,
					'max' => 100,
				],
				'vw' => [
					'min' => -100,
					'max' => 100,
				],
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-position: {{SIZE}}{{UNIT}} {{ypos.SIZE}}{{ypos.UNIT}}',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'position' => [ 'initial' ],
				'image[url]!' => '',
			],
			'required' => true,
			'device_args' => [
				Controls_Stack::RESPONSIVE_TABLET => [
					'selectors' => [
						'{{SELECTOR}}' => 'background-position: {{SIZE}}{{UNIT}} {{ypos_tablet.SIZE}}{{ypos_tablet.UNIT}}',
					],
					'condition' => [
						'background' => [ 'classic' ],
						'position_tablet' => [ 'initial' ],
					],
				],
				Controls_Stack::RESPONSIVE_MOBILE => [
					'selectors' => [
						'{{SELECTOR}}' => 'background-position: {{SIZE}}{{UNIT}} {{ypos_mobile.SIZE}}{{ypos_mobile.UNIT}}',
					],
					'condition' => [
						'background' => [ 'classic' ],
						'position_mobile' => [ 'initial' ],
					],
				],
			],
		];

		$fields['ypos'] = [
			'label' => Wp_Helper::_x( 'Y Position', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'responsive' => true,
			'size_units' => [ 'px', 'em', '%', 'vh' ],
			'default' => [
				'unit' => 'px',
				'size' => 0,
			],
			'tablet_default' => [
				'unit' => 'px',
				'size' => 0,
			],
			'mobile_default' => [
				'unit' => 'px',
				'size' => 0,
			],
			'range' => [
				'px' => [
					'min' => -800,
					'max' => 800,
				],
				'em' => [
					'min' => -100,
					'max' => 100,
				],
				'%' => [
					'min' => -100,
					'max' => 100,
				],
				'vh' => [
					'min' => -100,
					'max' => 100,
				],
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-position: {{xpos.SIZE}}{{xpos.UNIT}} {{SIZE}}{{UNIT}}',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'position' => [ 'initial' ],
				'image[url]!' => '',
			],
			'required' => true,
			'device_args' => [
				Controls_Stack::RESPONSIVE_TABLET => [
					'selectors' => [
						'{{SELECTOR}}' => 'background-position: {{xpos_tablet.SIZE}}{{xpos_tablet.UNIT}} {{SIZE}}{{UNIT}}',
					],
					'condition' => [
						'background' => [ 'classic' ],
						'position_tablet' => [ 'initial' ],
					],
				],
				Controls_Stack::RESPONSIVE_MOBILE => [
					'selectors' => [
						'{{SELECTOR}}' => 'background-position: {{xpos_mobile.SIZE}}{{xpos_mobile.UNIT}} {{SIZE}}{{UNIT}}',
					],
					'condition' => [
						'background' => [ 'classic' ],
						'position_mobile' => [ 'initial' ],
					],
				],
			],
		];

		$fields['attachment'] = [
			'label' => Wp_Helper::_x( 'Attachment', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'default' => '',
			'options' => [
				'' => Wp_Helper::_x( 'Default', 'Background Control', 'elementor' ),
				'scroll' => Wp_Helper::_x( 'Scroll', 'Background Control', 'elementor' ),
				'fixed' => Wp_Helper::_x( 'Fixed', 'Background Control', 'elementor' ),
			],
			'selectors' => [
				'(desktop+){{SELECTOR}}' => 'background-attachment: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'image[url]!' => '',
			],
		];

		$fields['attachment_alert'] = [
			'type' => Controls_Manager::RAW_HTML,
			'content_classes' => 'elementor-control-field-description',
			'raw' => Wp_Helper::__( 'Note: Attachment Fixed works only on desktop.', 'elementor' ),
			'separator' => 'none',
			'condition' => [
				'background' => [ 'classic' ],
				'image[url]!' => '',
				'attachment' => 'fixed',
			],
		];

		$fields['repeat'] = [
			'label' => Wp_Helper::_x( 'Repeat', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'default' => '',
			'responsive' => true,
			'options' => [
				'' => Wp_Helper::_x( 'Default', 'Background Control', 'elementor' ),
				'no-repeat' => Wp_Helper::_x( 'No-repeat', 'Background Control', 'elementor' ),
				'repeat' => Wp_Helper::_x( 'Repeat', 'Background Control', 'elementor' ),
				'repeat-x' => Wp_Helper::_x( 'Repeat-x', 'Background Control', 'elementor' ),
				'repeat-y' => Wp_Helper::_x( 'Repeat-y', 'Background Control', 'elementor' ),
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-repeat: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'image[url]!' => '',
			],
		];

		$fields['size'] = [
			'label' => Wp_Helper::_x( 'Size', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SELECT,
			'responsive' => true,
			'default' => '',
			'options' => [
				'' => Wp_Helper::_x( 'Default', 'Background Control', 'elementor' ),
				'auto' => Wp_Helper::_x( 'Auto', 'Background Control', 'elementor' ),
				'cover' => Wp_Helper::_x( 'Cover', 'Background Control', 'elementor' ),
				'contain' => Wp_Helper::_x( 'Contain', 'Background Control', 'elementor' ),
				'initial' => Wp_Helper::_x( 'Custom', 'Background Control', 'elementor' ),
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background-size: {{VALUE}};',
			],
			'condition' => [
				'background' => [ 'classic' ],
				'image[url]!' => '',
			],
		];

		$fields['bg_width'] = [
			'label' => Wp_Helper::_x( 'Width', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::SLIDER,
			'responsive' => true,
			'size_units' => [ 'px', 'em', '%', 'vw' ],
			'range' => [
				'px' => [
					'min' => 0,
					'max' => 1000,
				],
				'%' => [
					'min' => 0,
					'max' => 100,
				],
				'vw' => [
					'min' => 0,
					'max' => 100,
				],
			],
			'default' => [
				'size' => 100,
				'unit' => '%',
			],
			'required' => true,
			'selectors' => [
				'{{SELECTOR}}' => 'background-size: {{SIZE}}{{UNIT}} auto',

			],
			'condition' => [
				'background' => [ 'classic' ],
				'size' => [ 'initial' ],
				'image[url]!' => '',
			],
			'device_args' => [
				Controls_Stack::RESPONSIVE_TABLET => [
					'selectors' => [
						'{{SELECTOR}}' => 'background-size: {{SIZE}}{{UNIT}} auto',
					],
					'condition' => [
						'background' => [ 'classic' ],
						'size_tablet' => [ 'initial' ],
					],
				],
				Controls_Stack::RESPONSIVE_MOBILE => [
					'selectors' => [
						'{{SELECTOR}}' => 'background-size: {{SIZE}}{{UNIT}} auto',
					],
					'condition' => [
						'background' => [ 'classic' ],
						'size_mobile' => [ 'initial' ],
					],
				],
			],
		];

		$fields['video_link'] = [
			'label' => Wp_Helper::_x( 'Video Link', 'Background Control', 'elementor' ),
			'type' => Controls_Manager::TEXT,
			'placeholder' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
			'description' => Wp_Helper::__( 'YouTube link or video file (mp4 is recommended).', 'elementor' ),
			'label_block' => true,
			'default' => '',
			'condition' => [
				'background' => [ 'video' ],
			],
			'of_type' => 'video',
		];

		$fields['video_start'] = [
			'label' => Wp_Helper::__( 'Start Time', 'elementor' ),
			'type' => Controls_Manager::NUMBER,
			'description' => Wp_Helper::__( 'Specify a start time (in seconds)', 'elementor' ),
			'placeholder' => 10,
			'condition' => [
				'background' => [ 'video' ],
			],
			'of_type' => 'video',
		];

		$fields['video_end'] = [
			'label' => Wp_Helper::__( 'End Time', 'elementor' ),
			'type' => Controls_Manager::NUMBER,
			'description' => Wp_Helper::__( 'Specify an end time (in seconds)', 'elementor' ),
			'placeholder' => 70,
			'condition' => [
				'background' => [ 'video' ],
			],
			'of_type' => 'video',
		];

		$fields['play_once'] = [
			'label' => Wp_Helper::__( 'Play Once', 'elementor' ),
			'type' => Controls_Manager::SWITCHER,
			'condition' => [
				'background' => [ 'video' ],
			],
			'of_type' => 'video',
		];

		$fields['video_fallback'] = [
			'label' => Wp_Helper::_x( 'Background Fallback', 'Background Control', 'elementor' ),
			'description' => Wp_Helper::__( 'This cover image will replace the background video on mobile and tablet devices.', 'elementor' ),
			'type' => Controls_Manager::MEDIA,
			'label_block' => true,
			'condition' => [
				'background' => [ 'video' ],
			],
			'selectors' => [
				'{{SELECTOR}}' => 'background: url("{{URL}}") 50% 50%; background-size: cover;',
			],
			'of_type' => 'video',
		];

		return $fields;
	}

	/**
	 * Get child default args.
	 *
	 * Retrieve the default arguments for all the child controls for a specific group
	 * control.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @return array Default arguments for all the child controls.
	 */
	protected function get_child_default_args() {
		return [
			'types' => [ 'classic', 'gradient' ],
			'selector' => '{{WRAPPER}}:not(.elementor-motion-effects-element-type-background), {{WRAPPER}} > .elementor-motion-effects-container > .elementor-motion-effects-layer',
		];
	}

	/**
	 * Filter fields.
	 *
	 * Filter which controls to display, using `include`, `exclude`, `condition`
	 * and `of_type` arguments.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @return array Control fields.
	 */
	protected function filter_fields() {
		$fields = parent::filter_fields();

		$args = $this->get_args();

		foreach ( $fields as &$field ) {
			if ( isset( $field['of_type'] ) && ! in_array( $field['of_type'], $args['types'] ) ) {
				unset( $field );
			}
		}

		return $fields;
	}

	/**
	 * Prepare fields.
	 *
	 * Process background control fields before adding them to `add_control()`.
	 *
	 * @since 1.2.2
	 * @access protected
	 *
	 * @param array $fields Background control fields.
	 *
	 * @return array Processed fields.
	 */
	protected function prepare_fields( $fields ) {
		$args = $this->get_args();

		$background_types = self::get_background_types();

		$choose_types = [];

		foreach ( $args['types'] as $type ) {
			if ( isset( $background_types[ $type ] ) ) {
				$choose_types[ $type ] = $background_types[ $type ];
			}
		}

		$fields['background']['options'] = $choose_types;

		return parent::prepare_fields( $fields );
	}

	/**
	 * Get default options.
	 *
	 * Retrieve the default options of the background control. Used to return the
	 * default options while initializing the background control.
	 *
	 * @since 1.9.0
	 * @access protected
	 *
	 * @return array Default background control options.
	 */
	protected function get_default_options() {
		return [
			'popover' => false,
		];
	}
}
