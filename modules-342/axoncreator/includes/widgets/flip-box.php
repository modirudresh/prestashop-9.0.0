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
 * Elementor button widget.
 *
 * Elementor widget that displays a button with the ability to control every
 * aspect of the button design.
 *
 * @since 1.0.0
 */
class Widget_Flip_Box extends Widget_Base {

	public function get_name() {
		return 'flip-box';
	}

	public function get_title() {
		return Wp_Helper::__( 'Flip Box', 'elementor' );
	}

	public function get_icon() {
		return 'eicon-flip-box';
	}
	
	public function get_categories() {
		return [ 'axon-elements' ];
	}
	
	public function get_keywords() {
		return [ 'flip', 'axps' ];
	}
	
	protected function _register_controls() {

		$this->start_controls_section(
			'section_side_a_content',
			[
				'label' => Wp_Helper::__( 'Front', 'elementor' ),
			]
		);

		$this->start_controls_tabs( 'side_a_content_tabs' );

		$this->start_controls_tab( 'side_a_content_tab', [ 'label' => Wp_Helper::__( 'Content', 'elementor' ) ] );

		$this->add_control(
			'graphic_element',
			[
				'label' => Wp_Helper::__( 'Graphic Element', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'none' => [
						'title' => Wp_Helper::__( 'None', 'elementor' ),
						'icon' => 'eicon-ban',
					],
					'image' => [
						'title' => Wp_Helper::__( 'Image', 'elementor' ),
						'icon' => 'fa fa-picture-o',
					],
					'icon' => [
						'title' => Wp_Helper::__( 'Icon', 'elementor' ),
						'icon' => 'eicon-star',
					],
				],
				'default' => 'icon',
			]
		);

		$this->add_control(
			'image',
			[
				'label' => Wp_Helper::__( 'Choose Image', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'graphic_element' => 'image',
				],
			]
		);

		$this->add_control(
			'selected_icon',
			[
				'label' => Wp_Helper::__( 'Icon', 'elementor' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default' => [
					'value' => 'fas fa-star',
					'library' => 'fa-solid',
				],
				'condition' => [
					'graphic_element' => 'icon',
				],
			]
		);

		$this->add_control(
			'icon_view',
			[
				'label' => Wp_Helper::__( 'View', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'default' => Wp_Helper::__( 'Default', 'elementor' ),
					'stacked' => Wp_Helper::__( 'Stacked', 'elementor' ),
					'framed' => Wp_Helper::__( 'Framed', 'elementor' ),
				],
				'default' => 'default',
				'condition' => [
					'graphic_element' => 'icon',
				],
			]
		);

		$this->add_control(
			'icon_shape',
			[
				'label' => Wp_Helper::__( 'Shape', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'circle' => Wp_Helper::__( 'Circle', 'elementor' ),
					'square' => Wp_Helper::__( 'Square', 'elementor' ),
				],
				'default' => 'circle',
				'condition' => [
					'icon_view!' => 'default',
					'graphic_element' => 'icon',
				],
			]
		);

		$this->add_control(
			'title_text_a',
			[
				'label' => Wp_Helper::__( 'Title & Description', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'This is the heading', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Enter your title', 'elementor' ),
				'label_block' => true,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'description_text_a',
			[
				'label' => Wp_Helper::__( 'Description', 'elementor' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => Wp_Helper::__( 'Lorem ipsum dolor sit amet consectetur adipiscing elit dolor', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Enter your description', 'elementor' ),
				'separator' => 'none',
				'rows' => 10,
				'show_label' => false,
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'side_a_background_tab', [ 'label' => Wp_Helper::__( 'Background', 'elementor' ) ] );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background_a',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .elementor-flip-box__front',
			]
		);

		$this->add_control(
			'background_overlay_a',
			[
				'label' => Wp_Helper::__( 'Background Overlay', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__overlay' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before',
				'condition' => [
					'background_a_image[id]!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'background_overlay_a_filters',
				'selector' => '{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__overlay',
				'condition' => [
					'background_overlay_a!' => '',
				],
			]
		);

		$this->add_control(
			'background_overlay_a_blend_mode',
			[
				'label' => Wp_Helper::__( 'Blend Mode', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => Wp_Helper::__( 'Normal', 'elementor' ),
					'multiply' => 'Multiply',
					'screen' => 'Screen',
					'overlay' => 'Overlay',
					'darken' => 'Darken',
					'lighten' => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'color-burn' => 'Color Burn',
					'hue' => 'Hue',
					'saturation' => 'Saturation',
					'color' => 'Color',
					'exclusion' => 'Exclusion',
					'luminosity' => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__overlay' => 'mix-blend-mode: {{VALUE}}',
				],
				'condition' => [
					'background_overlay_a!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_side_b_content',
			[
				'label' => Wp_Helper::__( 'Back', 'elementor' ),
			]
		);

		$this->start_controls_tabs( 'side_b_content_tabs' );

		$this->start_controls_tab( 'side_b_content_tab', [ 'label' => Wp_Helper::__( 'Content', 'elementor' ) ] );

		$this->add_control(
			'title_text_b',
			[
				'label' => Wp_Helper::__( 'Title & Description', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'This is the heading', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Enter your title', 'elementor' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'description_text_b',
			[
				'label' => Wp_Helper::__( 'Description', 'elementor' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => Wp_Helper::__( 'Lorem ipsum dolor sit amet consectetur adipiscing elit dolor', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Enter your description', 'elementor' ),
				'separator' => 'none',
				'rows' => 10,
				'show_label' => false,
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => Wp_Helper::__( 'Button Text', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Click Here', 'elementor' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'link',
			[
				'label' => Wp_Helper::__( 'Link', 'elementor' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'placeholder' => Wp_Helper::__( 'https://your-link.com', 'elementor' ),
			]
		);

		$this->add_control(
			'link_click',
			[
				'label' => Wp_Helper::__( 'Apply Link On', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'box' => Wp_Helper::__( 'Whole Box', 'elementor' ),
					'button' => Wp_Helper::__( 'Button Only', 'elementor' ),
				],
				'default' => 'button',
				'condition' => [
					'link[url]!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'side_b_background_tab', [ 'label' => Wp_Helper::__( 'Background', 'elementor' ) ] );

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background_b',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .elementor-flip-box__back',
			]
		);

		$this->add_control(
			'background_overlay_b',
			[
				'label' => Wp_Helper::__( 'Background Overlay', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__overlay' => 'background-color: {{VALUE}};',
				],
				'separator' => 'before',
				'condition' => [
					'background_b_image[id]!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'background_overlay_b_filters',
				'selector' => '{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__overlay',
				'condition' => [
					'background_overlay_b!' => '',
				],
			]
		);

		$this->add_control(
			'background_overlay_b_blend_mode',
			[
				'label' => Wp_Helper::__( 'Blend Mode', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => Wp_Helper::__( 'Normal', 'elementor' ),
					'multiply' => 'Multiply',
					'screen' => 'Screen',
					'overlay' => 'Overlay',
					'darken' => 'Darken',
					'lighten' => 'Lighten',
					'color-dodge' => 'Color Dodge',
					'color-burn' => 'Color Burn',
					'hue' => 'Hue',
					'saturation' => 'Saturation',
					'color' => 'Color',
					'exclusion' => 'Exclusion',
					'luminosity' => 'Luminosity',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__overlay' => 'mix-blend-mode: {{VALUE}}',
				],
				'condition' => [
					'background_overlay_b!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_box_settings',
			[
				'label' => Wp_Helper::__( 'Settings', 'elementor' ),
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label' => Wp_Helper::__( 'Height', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1000,
					],
					'vh' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', 'vh' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'separator' => 'after',
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__layer, {{WRAPPER}} .elementor-flip-box__layer__overlay' => 'border-radius: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'flip_effect',
			[
				'label' => Wp_Helper::__( 'Flip Effect', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'flip',
				'options' => [
					'flip' => 'Flip',
					'slide' => 'Slide',
					'push' => 'Push',
					'zoom-in' => 'Zoom In',
					'zoom-out' => 'Zoom Out',
					'fade' => 'Fade',
				],
				'prefix_class' => 'elementor-flip-box--effect-',
			]
		);

		$this->add_control(
			'flip_direction',
			[
				'label' => Wp_Helper::__( 'Flip Direction', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'up',
				'options' => [
					'left' => Wp_Helper::__( 'Left', 'elementor' ),
					'right' => Wp_Helper::__( 'Right', 'elementor' ),
					'up' => Wp_Helper::__( 'Up', 'elementor' ),
					'down' => Wp_Helper::__( 'Down', 'elementor' ),
				],
				'condition' => [
					'flip_effect!' => [
						'fade',
						'zoom-in',
						'zoom-out',
					],
				],
				'prefix_class' => 'elementor-flip-box--direction-',
			]
		);

		$this->add_control(
			'flip_3d',
			[
				'label' => Wp_Helper::__( '3D Depth', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => Wp_Helper::__( 'On', 'elementor' ),
				'label_off' => Wp_Helper::__( 'Off', 'elementor' ),
				'return_value' => 'elementor-flip-box--3d',
				'default' => '',
				'prefix_class' => '',
				'condition' => [
					'flip_effect' => 'flip',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_a',
			[
				'label' => Wp_Helper::__( 'Front', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'padding_a',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'alignment_a',
			[
				'label' => Wp_Helper::__( 'Alignment', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => Wp_Helper::__( 'Left', 'elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => Wp_Helper::__( 'Center', 'elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => Wp_Helper::__( 'Right', 'elementor' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__overlay' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'vertical_position_a',
			[
				'label' => Wp_Helper::__( 'Vertical Position', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => Wp_Helper::__( 'Top', 'elementor' ),
						'icon' => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => Wp_Helper::__( 'Middle', 'elementor' ),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => Wp_Helper::__( 'Bottom', 'elementor' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__overlay' => 'justify-content: {{VALUE}}',
				],
				'separator' => 'after',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border_a',
				'selector' => '{{WRAPPER}} .elementor-flip-box__front',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'heading_image_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Image', 'elementor' ),
				'condition' => [
					'graphic_element' => 'image',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'image_spacing',
			[
				'label' => Wp_Helper::__( 'Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__image' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'graphic_element' => 'image',
				],
			]
		);

		$this->add_control(
			'image_width',
			[
				'label' => Wp_Helper::__( 'Size', 'elementor' ) . ' (%)',
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'default' => [
					'unit' => '%',
				],
				'range' => [
					'%' => [
						'min' => 5,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__image img' => 'width: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'graphic_element' => 'image',
				],
			]
		);

		$this->add_control(
			'image_opacity',
			[
				'label' => Wp_Helper::__( 'Opacity', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 1,
				],
				'range' => [
					'px' => [
						'max' => 1,
						'min' => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__image' => 'opacity: {{SIZE}};',
				],
				'condition' => [
					'graphic_element' => 'image',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'image_border',
				'selector' => '{{WRAPPER}} .elementor-flip-box__image img',
				'condition' => [
					'graphic_element' => 'image',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'image_border_radius',
			[
				'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__image img' => 'border-radius: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'graphic_element' => 'image',
				],
			]
		);

		$this->add_control(
			'heading_icon_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Icon', 'elementor' ),
				'condition' => [
					'graphic_element' => 'icon',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'icon_spacing',
			[
				'label' => Wp_Helper::__( 'Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon-wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'graphic_element' => 'icon',
				],
			]
		);

		$this->add_control(
			'icon_primary_color',
			[
				'label' => Wp_Helper::__( 'Primary Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-view-stacked .elementor-icon' => 'background-color: {{VALUE}}',
					'{{WRAPPER}} .elementor-view-stacked .elementor-icon svg' => 'stroke: {{VALUE}}',
					'{{WRAPPER}} .elementor-view-framed .elementor-icon, {{WRAPPER}} .elementor-view-default .elementor-icon' => 'color: {{VALUE}}; border-color: {{VALUE}}',
					'{{WRAPPER}} .elementor-view-framed .elementor-icon svg, {{WRAPPER}} .elementor-view-default .elementor-icon svg' => 'fill: {{VALUE}}; border-color: {{VALUE}}',
				],
				'condition' => [
					'graphic_element' => 'icon',
				],
			]
		);

		$this->add_control(
			'icon_secondary_color',
			[
				'label' => Wp_Helper::__( 'Secondary Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'graphic_element' => 'icon',
					'icon_view!' => 'default',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-view-framed .elementor-icon' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .elementor-view-framed .elementor-icon svg' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .elementor-view-stacked .elementor-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .elementor-view-stacked .elementor-icon svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_size',
			[
				'label' => Wp_Helper::__( 'Icon Size', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 6,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elementor-icon svg' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'graphic_element' => 'icon',
				],
			]
		);

		$this->add_control(
			'icon_padding',
			[
				'label' => Wp_Helper::__( 'Icon Padding', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .elementor-icon' => 'padding: {{SIZE}}{{UNIT}};',
				],
				'range' => [
					'em' => [
						'min' => 0,
						'max' => 5,
					],
				],
				'condition' => [
					'graphic_element' => 'icon',
					'icon_view!' => 'default',
				],
			]
		);

		$this->add_control(
			'icon_rotate',
			[
				'label' => Wp_Helper::__( 'Icon Rotate', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
					'unit' => 'deg',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon i' => 'transform: rotate({{SIZE}}{{UNIT}});',
					'{{WRAPPER}} .elementor-icon svg' => 'transform: rotate({{SIZE}}{{UNIT}});',
				],
				'condition' => [
					'graphic_element' => 'icon',
				],
			]
		);

		$this->add_control(
			'icon_border_width',
			[
				'label' => Wp_Helper::__( 'Border Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .elementor-icon' => 'border-width: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'graphic_element' => 'icon',
					'icon_view' => 'framed',
				],
			]
		);

		$this->add_responsive_control(
			'icon_border_radius',
			[
				'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'graphic_element' => 'icon',
					'icon_view!' => 'default',
				],
			]
		);

		$this->add_control(
			'heading_title_style_a',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'separator' => 'before',
				'condition' => [
					'title_text_a!' => '',
				],
			]
		);

		$this->add_control(
			'title_spacing_a',
			[
				'label' => Wp_Helper::__( 'Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'description_text_a!' => '',
					'title_text_a!' => '',
				],
			]
		);

		$this->add_control(
			'title_color_a',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__title' => 'color: {{VALUE}}',

				],
				'condition' => [
					'title_text_a!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography_a',
				'selector' => '{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__title',
				'condition' => [
					'title_text_a!' => '',
				],
			]
		);

		$this->add_control(
			'heading_description_style_a',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Description', 'elementor' ),
				'separator' => 'before',
				'condition' => [
					'description_text_a!' => '',
				],
			]
		);

		$this->add_control(
			'description_color_a',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__description' => 'color: {{VALUE}}',

				],
				'condition' => [
					'description_text_a!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography_a',
				'selector' => '{{WRAPPER}} .elementor-flip-box__front .elementor-flip-box__layer__description',
				'condition' => [
					'description_text_a!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_b',
			[
				'label' => Wp_Helper::__( 'Back', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'padding_b',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'alignment_b',
			[
				'label' => Wp_Helper::__( 'Alignment', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => Wp_Helper::__( 'Left', 'elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => Wp_Helper::__( 'Center', 'elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => Wp_Helper::__( 'Right', 'elementor' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__overlay' => 'text-align: {{VALUE}}',
					'{{WRAPPER}} .elementor-flip-box__button' => 'margin-{{VALUE}}: 0',
				],
			]
		);

		$this->add_control(
			'vertical_position_b',
			[
				'label' => Wp_Helper::__( 'Vertical Position', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'top' => [
						'title' => Wp_Helper::__( 'Top', 'elementor' ),
						'icon' => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => Wp_Helper::__( 'Middle', 'elementor' ),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => Wp_Helper::__( 'Bottom', 'elementor' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__overlay' => 'justify-content: {{VALUE}}',
				],
				'separator' => 'after',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border_b',
				'selector' => '{{WRAPPER}} .elementor-flip-box__back',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'heading_title_style_b',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'separator' => 'before',
				'condition' => [
					'title_text_b!' => '',
				],
			]
		);

		$this->add_control(
			'title_spacing_b',
			[
				'label' => Wp_Helper::__( 'Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'title_text_b!' => '',
				],
			]
		);

		$this->add_control(
			'title_color_b',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__title' => 'color: {{VALUE}}',

				],
				'condition' => [
					'title_text_b!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography_b',
				'selector' => '{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__title',
				'condition' => [
					'title_text_b!' => '',
				],
			]
		);

		$this->add_control(
			'heading_description_style_b',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Description', 'elementor' ),
				'separator' => 'before',
				'condition' => [
					'description_text_b!' => '',
				],
			]
		);

		$this->add_control(
			'description_spacing_b',
			[
				'label' => Wp_Helper::__( 'Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__description' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'description_text_b!' => '',
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'description_color_b',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__description' => 'color: {{VALUE}}',

				],
				'condition' => [
					'description_text_b!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography_b',
				'selector' => '{{WRAPPER}} .elementor-flip-box__back .elementor-flip-box__layer__description',
				'condition' => [
					'description_text_b!' => '',
				],
			]
		);

		$this->add_control(
			'heading_button',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Button', 'elementor' ),
				'separator' => 'before',
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'button_size',
			[
				'label' => Wp_Helper::__( 'Size', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'sm',
				'options' => [
					'xs' => Wp_Helper::__( 'Extra Small', 'elementor' ),
					'sm' => Wp_Helper::__( 'Small', 'elementor' ),
					'md' => Wp_Helper::__( 'Medium', 'elementor' ),
					'lg' => Wp_Helper::__( 'Large', 'elementor' ),
					'xl' => Wp_Helper::__( 'Extra Large', 'elementor' ),
				],
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .elementor-flip-box__button',
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'normal',
			[
				'label' => Wp_Helper::__( 'Normal', 'elementor' ),
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_color',
			[
				'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__button' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'hover',
			[
				'label' => Wp_Helper::__( 'Hover', 'elementor' ),
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'button_hover_text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_background_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'button_border_width',
			[
				'label' => Wp_Helper::__( 'Border Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__button' => 'border-width: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-flip-box__button' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'after',
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$wrapper_tag = 'div';
		$button_tag = 'a';
		$migration_allowed = Icons_Manager::is_migration_allowed();
		$this->add_render_attribute( 'button', 'class', [
			'elementor-flip-box__button',
			'elementor-button',
			'elementor-size-' . $settings['button_size'],
		] );

		$this->add_render_attribute( 'wrapper', 'class', 'elementor-flip-box__layer elementor-flip-box__back' );

		if ( ! empty( $settings['link']['url'] ) ) {
			$link_element = 'button';

			if ( 'box' === $settings['link_click'] ) {
				$wrapper_tag = 'a';
				$button_tag = 'span';
				$link_element = 'wrapper';
			}

			$this->add_link_attributes( $link_element, $settings['link'] );
		}

		if ( 'icon' === $settings['graphic_element'] ) {
			$this->add_render_attribute( 'icon-wrapper', 'class', 'elementor-icon-wrapper' );
			$this->add_render_attribute( 'icon-wrapper', 'class', 'elementor-view-' . $settings['icon_view'] );
			if ( 'default' != $settings['icon_view'] ) {
				$this->add_render_attribute( 'icon-wrapper', 'class', 'elementor-shape-' . $settings['icon_shape'] );
			}

			if ( ! isset( $settings['icon'] ) && ! $migration_allowed ) {
				// add old default
				$settings['icon'] = 'fa fa-star';
			}

			if ( ! empty( $settings['icon'] ) ) {
				$this->add_render_attribute( 'icon', 'class', $settings['icon'] );
			}
		}

		$has_icon = ! empty( $settings['icon'] ) || ! empty( $settings['selected_icon'] );
		$migrated = isset( $settings['__fa4_migrated']['selected_icon'] );
		$is_new = empty( $settings['icon'] ) && $migration_allowed;

		?>
		<div class="elementor-flip-box">
			<div class="elementor-flip-box__layer elementor-flip-box__front">
				<div class="elementor-flip-box__layer__overlay">
					<div class="elementor-flip-box__layer__inner">
						<?php if ( 'image' === $settings['graphic_element'] && ! empty( $settings['image']['url'] ) ) : ?>
							<div class="elementor-flip-box__image">
								<?php echo Group_Control_Image_Size::get_attachment_image_html( $settings ); ?>
							</div>
						<?php elseif ( 'icon' === $settings['graphic_element'] && $has_icon ) : ?>
							<div <?php echo $this->get_render_attribute_string( 'icon-wrapper' ); ?>>
								<div class="elementor-icon">
									<?php if ( $is_new || $migrated ) :
										Icons_Manager::render_icon( $settings['selected_icon'] );
									else : ?>
										<i <?php echo $this->get_render_attribute_string( 'icon' ); ?>></i>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $settings['title_text_a'] ) ) : ?>
							<h3 class="elementor-flip-box__layer__title">
								<?php echo $settings['title_text_a']; ?>
							</h3>
						<?php endif; ?>

						<?php if ( ! empty( $settings['description_text_a'] ) ) : ?>
							<div class="elementor-flip-box__layer__description">
								<?php echo $settings['description_text_a']; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<<?php echo $wrapper_tag; ?> <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div class="elementor-flip-box__layer__overlay">
				<div class="elementor-flip-box__layer__inner">
					<?php if ( ! empty( $settings['title_text_b'] ) ) : ?>
						<h3 class="elementor-flip-box__layer__title">
							<?php echo $settings['title_text_b']; ?>
						</h3>
					<?php endif; ?>

					<?php if ( ! empty( $settings['description_text_b'] ) ) : ?>
						<div class="elementor-flip-box__layer__description">
							<?php echo $settings['description_text_b']; ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $settings['button_text'] ) ) : ?>
						<<?php echo $button_tag; ?> <?php echo $this->get_render_attribute_string( 'button' ); ?>>
						<?php echo $settings['button_text']; ?>
						</<?php echo $button_tag; ?>>
					<?php endif; ?>
			</div>
		</div>
		</<?php echo $wrapper_tag; ?>>
		</div>
		<?php
	}

	/**
	 * Render Flip Box widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	protected function _content_template() {
		?>
		<#
		var btnClasses = 'elementor-flip-box__button elementor-button elementor-size-' + settings.button_size;

		if ( 'image' === settings.graphic_element && '' !== settings.image.url ) {
			var image = {
				id: settings.image.id,
				url: settings.image.url,
				size: settings.image_size,
				dimension: settings.image_custom_dimension,
				model: view.getEditModel()
			};

			var imageUrl = elementor.imagesManager.getImageUrl( image );
		}

		var wrapperTag = 'div',
		buttonTag = 'a';

		if ( 'box' === settings.link_click ) {
			wrapperTag = 'a';
			buttonTag = 'span';
		}

		if ( 'icon' === settings.graphic_element ) {
			var iconWrapperClasses = 'elementor-icon-wrapper';
			iconWrapperClasses += ' elementor-view-' + settings.icon_view;
			if ( 'default' !== settings.icon_view ) {
				iconWrapperClasses += ' elementor-shape-' + settings.icon_shape;
			}
		}
		var hasIcon = settings.icon || ( settings.selected_icon && settings.selected_icon.value ),
			iconHTML = elementor.helpers.renderIcon( view, settings.selected_icon, { 'aria-hidden': true }, 'i' , 'object' ),
			migrated = elementor.helpers.isIconMigrated( settings, 'selected_icon' );
		#>

		<div class="elementor-flip-box">
			<div class="elementor-flip-box__layer elementor-flip-box__front">
				<div class="elementor-flip-box__layer__overlay">
					<div class="elementor-flip-box__layer__inner">
						<# if ( 'image' === settings.graphic_element && '' !== settings.image.url ) { #>
						<div class="elementor-flip-box__image">
							<img src="{{ imageUrl }}">
						</div>
						<#  } else if ( 'icon' === settings.graphic_element && hasIcon ) { #>
						<div class="{{ iconWrapperClasses }}" >
							<div class="elementor-icon">
								<# if ( iconHTML && iconHTML.rendered && ( ! settings.icon || migrated ) ) { #>
									{{{ iconHTML.value }}}
								<# } else { #>
									<i class="{{ settings.icon }}"></i>
								<# } #>
							</div>
						</div>
						<# } #>

						<# if ( settings.title_text_a ) { #>
						<h3 class="elementor-flip-box__layer__title">{{{ settings.title_text_a }}}</h3>
						<# } #>

						<# if ( settings.description_text_a ) { #>
						<div class="elementor-flip-box__layer__description">{{{ settings.description_text_a }}}</div>
						<# } #>
					</div>
				</div>
			</div>
			<{{ wrapperTag }} class="elementor-flip-box__layer elementor-flip-box__back">
			<div class="elementor-flip-box__layer__overlay">
				<div class="elementor-flip-box__layer__inner">
					<# if ( settings.title_text_b ) { #>
					<h3 class="elementor-flip-box__layer__title">{{{ settings.title_text_b }}}</h3>
					<# } #>

					<# if ( settings.description_text_b ) { #>
					<div class="elementor-flip-box__layer__description">{{{ settings.description_text_b }}}</div>
					<# } #>

					<# if ( settings.button_text ) { #>
					<{{ buttonTag }} href="#" class="{{ btnClasses }}">{{{ settings.button_text }}}</{{ buttonTag }}>
				<# } #>
			</div>
		</div>
		</{{ wrapperTag }}>
		</div>
		<?php
	}
}
