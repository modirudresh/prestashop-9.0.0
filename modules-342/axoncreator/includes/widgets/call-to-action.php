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
 * Elementor menu anchor widget.
 *
 * Elementor widget that allows to link and menu to a specific position on the
 * page.
 *
 * @since 1.0.0
 */
class Widget_Call_To_Action extends Widget_Base {

	public function get_name() {
		return 'call-to-action';
	}

	public function get_title() {
		return Wp_Helper::__( 'Call to Action', 'elementor' );
	}

	public function get_icon() {
		return 'eicon-image-rollover';
	}
	
	public function get_categories() {
		return [ 'axon-elements' ];
	}
	
	public function get_keywords() {
		return [ 'call to action', 'cta', 'button', 'axps' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_main_image',
			[
				'label' => Wp_Helper::__( 'Image', 'elementor' ),
			]
		);

		$this->add_control(
			'skin',
			[
				'label' => Wp_Helper::__( 'Skin', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'classic' => Wp_Helper::__( 'Classic', 'elementor' ),
					'cover' => Wp_Helper::__( 'Cover', 'elementor' ),
				],
				'render_type' => 'template',
				'prefix_class' => 'elementor-cta--skin-',
				'default' => 'classic',
			]
		);

		$this->add_responsive_control(
			'layout',
			[
				'label' => Wp_Helper::__( 'Position', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => Wp_Helper::__( 'Left', 'elementor' ),
						'icon' => 'eicon-h-align-left',
					],
					'above' => [
						'title' => Wp_Helper::__( 'Above', 'elementor' ),
						'icon' => 'eicon-v-align-top',
					],
					'right' => [
						'title' => Wp_Helper::__( 'Right', 'elementor' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'prefix_class' => 'elementor-cta-%s-layout-image-',
				'condition' => [
					'skin!' => 'cover',
				],
			]
		);

		$this->add_control(
			'bg_image',
			[
				'label' => Wp_Helper::__( 'Choose Image', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content',
			[
				'label' => Wp_Helper::__( 'Content', 'elementor' ),
			]
		);

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
				'default' => 'none',
			]
		);

		$this->add_control(
			'graphic_image',
			[
				'label' => Wp_Helper::__( 'Choose Image', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'graphic_element' => 'image',
				],
				'show_label' => false,
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
			'title',
			[
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'This is the heading', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Enter your title', 'elementor' ),
				'label_block' => true,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'description',
			[
				'label' => Wp_Helper::__( 'Description', 'elementor' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => Wp_Helper::__( 'Lorem ipsum dolor sit amet consectetur adipiscing elit dolor', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Enter your description', 'elementor' ),
				'separator' => 'none',
				'rows' => 5,
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => Wp_Helper::__( 'Title HTML Tag', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'span' => 'span',
				],
				'default' => 'h2',
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_control(
			'button',
			[
				'label' => Wp_Helper::__( 'Button Text', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Click Here', 'elementor' ),
				'separator' => 'before',
			]
		);
		
		$this->add_control(
			'selected_button_icon',
			[
				'label' => Wp_Helper::__( 'Icon', 'elementor' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
			]
		);

		$this->add_control(
			'button_icon_align',
			[
				'label' => Wp_Helper::__( 'Icon Position', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'left',
				'options' => [
					'left' => Wp_Helper::__( 'Before', 'elementor' ),
					'right' => Wp_Helper::__( 'After', 'elementor' ),
				],
				'condition' => [
					'selected_button_icon[value]!' => '',
				],
			]
		);

		$this->add_control(
			'button_icon_indent',
			[
				'label' => Wp_Helper::__( 'Icon Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .elementor-button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
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
				'separator' => 'none',
				'condition' => [
					'link[url]!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ribbon',
			[
				'label' => Wp_Helper::__( 'Ribbon', 'elementor' ),
			]
		);

		$this->add_control(
			'ribbon_title',
			[
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'ribbon_horizontal_position',
			[
				'label' => Wp_Helper::__( 'Position', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => Wp_Helper::__( 'Left', 'elementor' ),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => Wp_Helper::__( 'Right', 'elementor' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'condition' => [
					'ribbon_title!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'box_style',
			[
				'label' => Wp_Helper::__( 'Box', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'min-height',
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
					'{{WRAPPER}} .elementor-cta__content' => 'min-height: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_responsive_control(
			'alignment',
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
					'{{WRAPPER}} .elementor-cta__content' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'vertical_position',
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
				'prefix_class' => 'elementor-cta--valign-',
				'separator' => 'none',
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'heading_bg_image_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Image', 'elementor' ),
				'condition' => [
					'bg_image[url]!' => '',
					'skin' => 'classic',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'image_min_width',
			[
				'label' => Wp_Helper::__( 'Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__bg-wrapper' => 'min-width: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'skin' => 'classic',
					'layout!' => 'above',
				],
			]
		);

		$this->add_responsive_control(
			'image_min_height',
			[
				'label' => Wp_Helper::__( 'Height', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 500,
					],
					'vh' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ 'px', 'vh' ],

				'selectors' => [
					'{{WRAPPER}} .elementor-cta__bg-wrapper' => 'min-height: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'skin' => 'classic',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'graphic_element_style',
			[
				'label' => Wp_Helper::__( 'Graphic Element', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'graphic_element!' => [
						'none',
						'',
					],
				],
			]
		);

		$this->add_control(
			'graphic_image_spacing',
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
					'{{WRAPPER}} .elementor-cta__image' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'graphic_element' => 'image',
				],
			]
		);

		$this->add_control(
			'graphic_image_width',
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
					'{{WRAPPER}} .elementor-cta__image img' => 'width: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'graphic_element' => 'image',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'graphic_image_border',
				'selector' => '{{WRAPPER}} .elementor-cta__image img',
				'condition' => [
					'graphic_element' => 'image',
				],
			]
		);

		$this->add_control(
			'graphic_image_border_radius',
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
					'{{WRAPPER}} .elementor-cta__image img' => 'border-radius: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'graphic_element' => 'image',
				],
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
					'{{WRAPPER}} .elementor-view-framed .elementor-icon, {{WRAPPER}} .elementor-view-default .elementor-icon svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
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

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_style',
			[
				'label' => Wp_Helper::__( 'Content', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'conditions' => [
					'relation' => 'or',
					'terms' => [
						[
							'name' => 'title',
							'operator' => '!==',
							'value' => '',
						],
						[
							'name' => 'description',
							'operator' => '!==',
							'value' => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'heading_style_title',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'global' => [
					'default' => Scheme_Typography::TYPOGRAPHY_1,
				],
				'selector' => '{{WRAPPER}} .elementor-cta__title',
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => Wp_Helper::__( 'Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__title:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_control(
			'heading_style_description',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Description', 'elementor' ),
				'separator' => 'before',
				'condition' => [
					'description!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'global' => [
					'default' => Scheme_Typography::TYPOGRAPHY_3,
				],
				'selector' => '{{WRAPPER}} .elementor-cta__description',
				'condition' => [
					'description!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'description_spacing',
			[
				'label' => Wp_Helper::__( 'Spacing', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__description:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'description!' => '',
				],
			]
		);

		$this->add_control(
			'heading_content_colors',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Colors', 'elementor' ),
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs( 'color_tabs' );

		$this->start_controls_tab( 'colors_normal',
			[
				'label' => Wp_Helper::__( 'Normal', 'elementor' ),
			]
		);

		$this->add_control(
			'content_bg_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__content' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'skin' => 'classic',
				],
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => Wp_Helper::__( 'Title Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__title' => 'color: {{VALUE}}',
				],
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => Wp_Helper::__( 'Description Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__description' => 'color: {{VALUE}}',
				],
				'condition' => [
					'description!' => '',
				],
			]
		);

		$this->add_control(
			'button_color',
			[
				'label' => Wp_Helper::__( 'Button Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__button' => 'color: {{VALUE}}; border-color: {{VALUE}}',
				],
				'condition' => [
					'button!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'colors_hover',
			[
				'label' => Wp_Helper::__( 'Hover', 'elementor' ),
			]
		);

		$this->add_control(
			'content_bg_color_hover',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta:hover .elementor-cta__content' => 'background-color: {{VALUE}}',
				],
				'condition' => [
					'skin' => 'classic',
				],
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => Wp_Helper::__( 'Title Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta:hover .elementor-cta__title' => 'color: {{VALUE}}',
				],
				'condition' => [
					'title!' => '',
				],
			]
		);

		$this->add_control(
			'description_color_hover',
			[
				'label' => Wp_Helper::__( 'Description Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta:hover .elementor-cta__description' => 'color: {{VALUE}}',
				],
				'condition' => [
					'description!' => '',
				],
			]
		);

		$this->add_control(
			'button_color_hover',
			[
				'label' => Wp_Helper::__( 'Button Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta:hover .elementor-cta__button' => 'color: {{VALUE}}; border-color: {{VALUE}}',
				],
				'condition' => [
					'button!' => '',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'button_style',
			[
				'label' => Wp_Helper::__( 'Button', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'button!' => '',
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
			]
		);
		
		$this->add_control(
			'button_icon_size',
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
					'{{WRAPPER}} .elementor-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'label' => Wp_Helper::__( 'Typography', 'elementor' ),
				'selector' => '{{WRAPPER}} .elementor-cta__button',
				'global' => [
					'default' => Scheme_Typography::TYPOGRAPHY_4,
				],
			]
		);

		$this->start_controls_tabs( 'button_tabs' );

		$this->start_controls_tab( 'button_normal',
			[
				'label' => Wp_Helper::__( 'Normal', 'elementor' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__button' => 'color: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_border_color',
			[
				'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__button' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button-hover',
			[
				'label' => Wp_Helper::__( 'Hover', 'elementor' ),
			]
		);

		$this->add_control(
			'button_hover_text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__button:hover' => 'color: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_background_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__button:hover' => 'border-color: {{VALUE}};',
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
					'{{WRAPPER}} .elementor-cta__button' => 'border-width: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'before',
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
					'{{WRAPPER}} .elementor-cta__button' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ribbon_style',
			[
				'label' => Wp_Helper::__( 'Ribbon', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition' => [
					'ribbon_title!' => '',
				],
			]
		);

		$this->add_control(
			'ribbon_bg_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-ribbon-inner' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'ribbon_text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-ribbon-inner' => 'color: {{VALUE}}',
				],
			]
		);

		$ribbon_distance_transform = Wp_Helper::is_rtl() ? 'translateY(-50%) translateX({{SIZE}}{{UNIT}}) rotate(-45deg)' : 'translateY(-50%) translateX(-50%) translateX({{SIZE}}{{UNIT}}) rotate(-45deg)';

		$this->add_responsive_control(
			'ribbon_distance',
			[
				'label' => Wp_Helper::__( 'Distance', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-ribbon-inner' => 'margin-top: {{SIZE}}{{UNIT}}; transform: ' . $ribbon_distance_transform,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'ribbon_typography',
				'selector' => '{{WRAPPER}} .elementor-ribbon-inner',
				'global' => [
					'default' => Scheme_Typography::TYPOGRAPHY_4,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'selector' => '{{WRAPPER}} .elementor-ribbon-inner',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'hover_effects',
			[
				'label' => Wp_Helper::__( 'Hover Effects', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'content_hover_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Content', 'elementor' ),
				'condition' => [
					'skin' => 'cover',
				],
			]
		);

		$this->add_control(
			'content_animation',
			[
				'label' => Wp_Helper::__( 'Hover Animation', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'groups' => [
					[
						'label' => Wp_Helper::__( 'None', 'elementor' ),
						'options' => [
							'' => Wp_Helper::__( 'None', 'elementor' ),
						],
					],
					[
						'label' => Wp_Helper::__( 'Entrance', 'elementor' ),
						'options' => [
							'enter-from-right' => 'Slide In Right',
							'enter-from-left' => 'Slide In Left',
							'enter-from-top' => 'Slide In Up',
							'enter-from-bottom' => 'Slide In Down',
							'enter-zoom-in' => 'Zoom In',
							'enter-zoom-out' => 'Zoom Out',
							'fade-in' => 'Fade In',
						],
					],
					[
						'label' => Wp_Helper::__( 'Reaction', 'elementor' ),
						'options' => [
							'grow' => 'Grow',
							'shrink' => 'Shrink',
							'move-right' => 'Move Right',
							'move-left' => 'Move Left',
							'move-up' => 'Move Up',
							'move-down' => 'Move Down',
						],
					],
					[
						'label' => Wp_Helper::__( 'Exit', 'elementor' ),
						'options' => [
							'exit-to-right' => 'Slide Out Right',
							'exit-to-left' => 'Slide Out Left',
							'exit-to-top' => 'Slide Out Up',
							'exit-to-bottom' => 'Slide Out Down',
							'exit-zoom-in' => 'Zoom In',
							'exit-zoom-out' => 'Zoom Out',
							'fade-out' => 'Fade Out',
						],
					],
				],
				'default' => 'grow',
				'condition' => [
					'skin' => 'cover',
				],
			]
		);

		/*
		 *
		 * Add class 'elementor-animated-content' to widget when assigned content animation
		 *
		 */
		$this->add_control(
			'animation_class',
			[
				'label' => Wp_Helper::__( 'Animation', 'elementor' ),
				'type' => Controls_Manager::HIDDEN,
				'default' => 'animated-content',
				'prefix_class' => 'elementor-',
				'condition' => [
					'content_animation!' => '',
				],
			]
		);

		$this->add_control(
			'content_animation_duration',
			[
				'label' => Wp_Helper::__( 'Animation Duration', 'elementor' ) . ' (ms)',
				'type' => Controls_Manager::SLIDER,
				'render_type' => 'template',
				'default' => [
					'size' => 1000,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 3000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cta__content-item' => 'transition-duration: {{SIZE}}ms',
					'{{WRAPPER}}.elementor-cta--sequenced-animation .elementor-cta__content-item:nth-child(2)' => 'transition-delay: calc( {{SIZE}}ms / 3 )',
					'{{WRAPPER}}.elementor-cta--sequenced-animation .elementor-cta__content-item:nth-child(3)' => 'transition-delay: calc( ( {{SIZE}}ms / 3 ) * 2 )',
					'{{WRAPPER}}.elementor-cta--sequenced-animation .elementor-cta__content-item:nth-child(4)' => 'transition-delay: calc( ( {{SIZE}}ms / 3 ) * 3 )',
				],
				'condition' => [
					'content_animation!' => '',
					'skin' => 'cover',
				],
			]
		);

		$this->add_control(
			'sequenced_animation',
			[
				'label' => Wp_Helper::__( 'Sequenced Animation', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => Wp_Helper::__( 'On', 'elementor' ),
				'label_off' => Wp_Helper::__( 'Off', 'elementor' ),
				'return_value' => 'elementor-cta--sequenced-animation',
				'prefix_class' => '',
				'condition' => [
					'content_animation!' => '',

				],
			]
		);

		$this->add_control(
			'background_hover_heading',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Background', 'elementor' ),
				'separator' => 'before',
				'condition' => [
					'skin' => 'cover',
				],
			]
		);

		$this->add_control(
			'transformation',
			[
				'label' => Wp_Helper::__( 'Hover Animation', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => 'None',
					'zoom-in' => 'Zoom In',
					'zoom-out' => 'Zoom Out',
					'move-left' => 'Move Left',
					'move-right' => 'Move Right',
					'move-up' => 'Move Up',
					'move-down' => 'Move Down',
				],
				'default' => 'zoom-in',
				'prefix_class' => 'elementor-bg-transform elementor-bg-transform-',
			]
		);

		$this->start_controls_tabs( 'bg_effects_tabs' );

		$this->start_controls_tab( 'normal',
			[
				'label' => Wp_Helper::__( 'Normal', 'elementor' ),
			]
		);

		$this->add_control(
			'overlay_color',
			[
				'label' => Wp_Helper::__( 'Overlay Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta:not(:hover) .elementor-cta__bg-overlay' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'bg_filters',
				'selector' => '{{WRAPPER}} .elementor-cta__bg',
			]
		);

		$this->add_control(
			'overlay_blend_mode',
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
					'{{WRAPPER}} .elementor-cta__bg-overlay' => 'mix-blend-mode: {{VALUE}}',
				],
				'separator' => 'none',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'hover',
			[
				'label' => Wp_Helper::__( 'Hover', 'elementor' ),
			]
		);

		$this->add_control(
			'overlay_color_hover',
			[
				'label' => Wp_Helper::__( 'Overlay Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-cta:hover .elementor-cta__bg-overlay' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'bg_filters_hover',
				'selector' => '{{WRAPPER}} .elementor-cta:hover .elementor-cta__bg',
			]
		);

		$this->add_control(
			'effect_duration',
			[
				'label' => Wp_Helper::__( 'Transition Duration', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'render_type' => 'template',
				'default' => [
					'size' => 1500,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 3000,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-cta .elementor-cta__bg, {{WRAPPER}} .elementor-cta .elementor-cta__bg-overlay' => 'transition-duration: {{SIZE}}ms',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$title_tag = $settings['title_tag'];
		$wrapper_tag = 'div';
		$button_tag = 'a';
		$bg_image = '';
		$content_animation = $settings['content_animation'];
		$animation_class = '';
		$print_bg = true;
		$print_content = true;

		if ( ! empty( $settings['bg_image']['url'] ) ) {
			$bg_image = $settings['bg_image']['url'];
		}

		if ( empty( $bg_image ) && 'classic' == $settings['skin'] ) {
			$print_bg = false;
		}

		if ( empty( $settings['title'] ) && empty( $settings['description'] ) && empty( $settings['button'] ) && 'none' == $settings['graphic_element'] ) {
			$print_content = false;
		}

		$this->add_render_attribute( 'background_image', 'style', [
			'background-image: url(' . $bg_image . ');',
		] );

		$this->add_render_attribute( 'title', 'class', [
			'elementor-cta__title',
			'elementor-cta__content-item',
			'elementor-content-item',
		] );

		$this->add_render_attribute( 'description', 'class', [
			'elementor-cta__description',
			'elementor-cta__content-item',
			'elementor-content-item',
		] );
		
		$migrated = isset( $settings['__fa4_migrated']['selected_icon'] );
		$is_new = empty( $settings['icon'] ) && Icons_Manager::is_migration_allowed();

		if ( ! $is_new && empty( $settings['button_icon_align'] ) ) {
			// @todo: remove when deprecated
			// added as bc in 2.6
			//old default
			$settings['button_icon_align'] = $this->get_settings( 'button_icon_align' );
		}	

		$this->add_render_attribute( 'button', 'class', [
			'elementor-cta__button',
			'elementor-button',
			'elementor-size-' . $settings['button_size'],
		] );
		
		$this->add_render_attribute( 'icon-align', 'class', [
			'elementor-button-icon',
			'elementor-align-icon-' . $settings['button_icon_align'],
		] );
		
		$this->add_render_attribute( 'text', 'class', [
			'elementor-button-text',
		] );

		$this->add_render_attribute( 'graphic_element', 'class',
			[
				'elementor-content-item',
				'elementor-cta__content-item',
			]
		);

		if ( 'icon' === $settings['graphic_element'] ) {
			$this->add_render_attribute( 'graphic_element', 'class',
				[
					'elementor-icon-wrapper',
					'elementor-cta__icon',
				]
			);
			$this->add_render_attribute( 'graphic_element', 'class', 'elementor-view-' . $settings['icon_view'] );
			if ( 'default' != $settings['icon_view'] ) {
				$this->add_render_attribute( 'graphic_element', 'class', 'elementor-shape-' . $settings['icon_shape'] );
			}

			if ( ! isset( $settings['icon'] ) && ! Icons_Manager::is_migration_allowed() ) {
				// add old default
				$settings['icon'] = 'fa fa-star';
			}

			if ( ! empty( $settings['icon'] ) ) {
				$this->add_render_attribute( 'icon', 'class', $settings['icon'] );
			}
		} elseif ( 'image' === $settings['graphic_element'] && ! empty( $settings['graphic_image']['url'] ) ) {
			$this->add_render_attribute( 'graphic_element', 'class', 'elementor-cta__image' );
		}

		if ( ! empty( $content_animation ) && 'cover' == $settings['skin'] ) {

			$animation_class = 'elementor-animated-item--' . $content_animation;

			$this->add_render_attribute( 'title', 'class', $animation_class );

			$this->add_render_attribute( 'graphic_element', 'class', $animation_class );

			$this->add_render_attribute( 'description', 'class', $animation_class );

		}

		if ( ! empty( $settings['link']['url'] ) ) {
			$link_element = 'button';

			if ( 'box' === $settings['link_click'] ) {
				$wrapper_tag = 'a';
				$button_tag = 'span';
				$link_element = 'wrapper';
			}

			$this->add_link_attributes( $link_element, $settings['link'] );
		}

		$this->add_inline_editing_attributes( 'title' );
		$this->add_inline_editing_attributes( 'description' );

		$btn_migrated = isset( $settings['__fa4_migrated']['selected_icon'] );
		$btn_is_new = empty( $settings['icon'] ) && Icons_Manager::is_migration_allowed();

		?>
		<<?php echo $wrapper_tag . ' ' . $this->get_render_attribute_string( 'wrapper' ); ?> class="elementor-cta">
		<?php if ( $print_bg ) : ?>
			<div class="elementor-cta__bg-wrapper">
				<div class="elementor-cta__bg elementor-bg" <?php echo $this->get_render_attribute_string( 'background_image' ); ?>></div>
				<div class="elementor-cta__bg-overlay"></div>
			</div>
		<?php endif; ?>
		<?php if ( $print_content ) : ?>
			<div class="elementor-cta__content">
				<?php if ( 'image' === $settings['graphic_element'] && ! empty( $settings['graphic_image']['url'] ) ) : ?>
					<div <?php echo $this->get_render_attribute_string( 'graphic_element' ); ?>>
						<?php echo Group_Control_Image_Size::get_attachment_image_html( $settings, 'graphic_image' ); ?>
					</div>
				<?php elseif ( 'icon' === $settings['graphic_element'] && ( ! empty( $settings['icon'] ) || ! empty( $settings['selected_icon'] ) ) ) : ?>
					<div <?php echo $this->get_render_attribute_string( 'graphic_element' ); ?>>
						<div class="elementor-icon">
							<?php if ( $is_new || $migrated ) :
								Icons_Manager::render_icon( $settings['selected_icon'], [ 'aria-hidden' => 'true' ] );
							else : ?>
								<i <?php echo $this->get_render_attribute_string( 'icon' ); ?>></i>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $settings['title'] ) ) : ?>
					<<?php echo $title_tag . ' ' . $this->get_render_attribute_string( 'title' ); ?>>
						<?php echo $settings['title']; ?>
					</<?php echo $title_tag; ?>>
				<?php endif; ?>

				<?php if ( ! empty( $settings['description'] ) ) : ?>
					<div <?php echo $this->get_render_attribute_string( 'description' ); ?>>
						<?php echo $settings['description']; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $settings['button'] ) ) : ?>
					<div class="elementor-cta__button-wrapper elementor-cta__content-item elementor-content-item <?php echo $animation_class; ?>">
					<<?php echo $button_tag . ' ' . $this->get_render_attribute_string( 'button' ); ?>>
						<span class="elementor-button-content-wrapper">
							<?php if ( ! empty( $settings['button_icon'] ) || ! empty( $settings['selected_button_icon'] ) ) : ?>
							<span <?php echo $this->get_render_attribute_string( 'icon-align' ); ?>>
								<?php if ( $btn_is_new || $btn_migrated ) :
									Icons_Manager::render_icon( $settings['selected_button_icon'], [ 'aria-hidden' => 'true' ] );
								else : ?>
									<i class="<?php echo Wp_Helper::esc_attr( $settings['button_icon'] ); ?>" aria-hidden="true"></i>
								<?php endif; ?>
							</span>
							<?php endif; ?>
							<span <?php echo $this->get_render_attribute_string( 'text' ); ?>><?php echo $settings['button']; ?></span>
						</span>
					</<?php echo $button_tag; ?>>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php
		if ( ! empty( $settings['ribbon_title'] ) ) :
			$this->add_render_attribute( 'ribbon-wrapper', 'class', 'elementor-ribbon' );

			if ( ! empty( $settings['ribbon_horizontal_position'] ) ) {
				$this->add_render_attribute( 'ribbon-wrapper', 'class', 'elementor-ribbon-' . $settings['ribbon_horizontal_position'] );
			}
			?>
			<div <?php echo $this->get_render_attribute_string( 'ribbon-wrapper' ); ?>>
				<div class="elementor-ribbon-inner"><?php echo $settings['ribbon_title']; ?></div>
			</div>
		<?php endif; ?>
		</<?php echo $wrapper_tag; ?>>
		<?php
	}

	/**
	 * Render Call to Action widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	protected function _content_template() {
		?>
		<#
			var wrapperTag = 'div',
				buttonTag = 'a',
				contentAnimation = settings.content_animation,
				animationClass,
				btnSizeClass = 'elementor-size-' + settings.button_size,
				printBg = true,
				printContent = true,
				iconHTML = elementor.helpers.renderIcon( view, settings.selected_icon, { 'aria-hidden': true }, 'i' , 'object' ),
				migrated = elementor.helpers.isIconMigrated( settings, 'selected_icon' );

			if ( 'box' === settings.link_click ) {
				wrapperTag = 'a';
				buttonTag = 'span';
				view.addRenderAttribute( 'wrapper', 'href', '#' );
			}

			if ( '' !== settings.bg_image.url ) {
				var bg_image = {
					id: settings.bg_image.id,
					url: settings.bg_image.url,
					size: settings.bg_image_size,
					dimension: settings.bg_image_custom_dimension,
					model: view.getEditModel()
				};

				var bgImageUrl = elementor.imagesManager.getImageUrl( bg_image );
			}

			if ( ! bg_image && 'classic' == settings.skin ) {
				printBg = false;
			}

			if ( ! settings.title && ! settings.description && ! settings.button && 'none' == settings.graphic_element ) {
				printContent = false;
			}

			if ( 'icon' === settings.graphic_element ) {
				var iconWrapperClasses = 'elementor-icon-wrapper';
					iconWrapperClasses += ' elementor-cta__image';
					iconWrapperClasses += ' elementor-view-' + settings.icon_view;
				if ( 'default' !== settings.icon_view ) {
					iconWrapperClasses += ' elementor-shape-' + settings.icon_shape;
				}
				view.addRenderAttribute( 'graphic_element', 'class', iconWrapperClasses );

			} else if ( 'image' === settings.graphic_element && '' !== settings.graphic_image.url ) {
				var image = {
					id: settings.graphic_image.id,
					url: settings.graphic_image.url,
					size: settings.graphic_image_size,
					dimension: settings.graphic_image_custom_dimension,
					model: view.getEditModel()
				};

				var imageUrl = elementor.imagesManager.getImageUrl( image );
				view.addRenderAttribute( 'graphic_element', 'class', 'elementor-cta__image' );
			}

			if ( contentAnimation && 'cover' === settings.skin ) {

				var animationClass = 'elementor-animated-item--' + contentAnimation;

				view.addRenderAttribute( 'title', 'class', animationClass );

				view.addRenderAttribute( 'description', 'class', animationClass );

				view.addRenderAttribute( 'graphic_element', 'class', animationClass );
			}

			view.addRenderAttribute( 'background_image', 'style', 'background-image: url(' + bgImageUrl + ');' );
			view.addRenderAttribute( 'title', 'class', [ 'elementor-cta__title', 'elementor-cta__content-item', 'elementor-content-item' ] );
			view.addRenderAttribute( 'description', 'class', [ 'elementor-cta__description', 'elementor-cta__content-item', 'elementor-content-item' ] );
			view.addRenderAttribute( 'button', 'class', [ 'elementor-cta__button', 'elementor-button', btnSizeClass ] );
			view.addRenderAttribute( 'text', 'class', 'elementor-button-text' );
			view.addRenderAttribute( 'graphic_element', 'class', [ 'elementor-cta__content-item', 'elementor-content-item' ] );
			

			view.addInlineEditingAttributes( 'title' );
			view.addInlineEditingAttributes( 'description' );
						
			var btnIconHTML = elementor.helpers.renderIcon( view, settings.selected_button_icon, { 'aria-hidden': true }, 'i' , 'object' ),
				btnMigrated = elementor.helpers.isIconMigrated( settings, 'selected_button_icon' );
		#>

		<{{ wrapperTag }} class="elementor-cta" {{{ view.getRenderAttributeString( 'wrapper' ) }}}>

		<# if ( printBg ) { #>
			<div class="elementor-cta__bg-wrapper">
				<div class="elementor-cta__bg elementor-bg" {{{ view.getRenderAttributeString( 'background_image' ) }}}></div>
				<div class="elementor-cta__bg-overlay"></div>
			</div>
		<# } #>
		<# if ( printContent ) { #>
			<div class="elementor-cta__content">
				<# if ( 'image' === settings.graphic_element && '' !== settings.graphic_image.url ) { #>
					<div {{{ view.getRenderAttributeString( 'graphic_element' ) }}}>
						<img src="{{ imageUrl }}">
					</div>
				<#  } else if ( 'icon' === settings.graphic_element && ( settings.icon || ( settings.selected_icon && settings.selected_icon.value ) ) ) { #>
					<div {{{ view.getRenderAttributeString( 'graphic_element' ) }}}>
						<div class="elementor-icon">
							<# if ( iconHTML && iconHTML.rendered && ( ! settings.icon || migrated ) ) { #>
								{{{ iconHTML.value }}}
							<# } else { #>
								<i class="{{ settings.icon }}"></i>
							<# } #>
						</div>
					</div>
				<# } #>
				<# if ( settings.title ) { #>
					<{{ settings.title_tag }} {{{ view.getRenderAttributeString( 'title' ) }}}>{{{ settings.title }}}</{{ settings.title_tag }}>
				<# } #>

				<# if ( settings.description ) { #>
					<div {{{ view.getRenderAttributeString( 'description' ) }}}>{{{ settings.description }}}</div>
				<# } #>

				<# if ( settings.button ) { #>
					<div class="elementor-cta__button-wrapper elementor-cta__content-item elementor-content-item {{ animationClass }}">
						<{{ buttonTag }} href="#" {{{ view.getRenderAttributeString( 'button' ) }}}>
							<span class="elementor-button-content-wrapper">
								<# if ( settings.button_icon || ( settings.selected_button_icon && settings.selected_button_icon.value ) ) { #>
								<span class="elementor-button-icon elementor-align-icon-{{ settings.button_icon_align }}">
									<# if ( btnIconHTML && btnIconHTML.rendered && ( ! settings.button_icon || btnMigrated ) ) { #>
										{{{ btnIconHTML.value }}}
									<# } else { #>
										<i class="{{ settings.button_icon }}" aria-hidden="true"></i>
									<# } #>
								</span>
								<# } #>
								<span {{{ view.getRenderAttributeString( 'text' ) }}}>{{{ settings.button }}}</span>	
							</span>	
						</{{ buttonTag }}>
					</div>
				<# } #>
			</div>
		<# } #>
		<# if ( settings.ribbon_title ) {
			var ribbonClasses = 'elementor-ribbon';

			if ( settings.ribbon_horizontal_position ) {
				ribbonClasses += ' elementor-ribbon-' + settings.ribbon_horizontal_position;
			} #>
			<div class="{{ ribbonClasses }}">
				<div class="elementor-ribbon-inner">{{{ settings.ribbon_title }}}</div>
			</div>
		<# } #>
		</{{ wrapperTag }}>
		<?php
	}
}