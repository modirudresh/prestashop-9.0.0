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
class Widget_Price_Table extends Widget_Base {

	public function get_name() {
		return 'price-table';
	}

	public function get_title() {
		return Wp_Helper::__( 'Price Table', 'elementor' );
	}

	public function get_icon() {
		return 'eicon-price-table';
	}
	
	public function get_categories() {
		return [ 'axon-elements' ];
	}

	public function get_keywords() {
		return [ 'pricing', 'table', 'product', 'image', 'plan', 'button', 'axps' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_header',
			[
				'label' => Wp_Helper::__( 'Header', 'elementor' ),
			]
		);

		$this->add_control(
			'heading',
			[
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Enter your title', 'elementor' ),
			]
		);

		$this->add_control(
			'sub_heading',
			[
				'label' => Wp_Helper::__( 'Description', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Enter your description', 'elementor' ),
			]
		);

		$this->add_control(
			'heading_tag',
			[
				'label' => Wp_Helper::__( 'Title HTML Tag', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				],
				'default' => 'h3',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pricing',
			[
				'label' => Wp_Helper::__( 'Pricing', 'elementor' ),
			]
		);

		$this->add_control(
			'currency_symbol',
			[
				'label' => Wp_Helper::__( 'Currency Symbol', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => Wp_Helper::__( 'None', 'elementor' ),
					'dollar' => '&#36; ' . Wp_Helper::_x( 'Dollar', 'Currency Symbol', 'elementor' ),
					'euro' => '&#128; ' . Wp_Helper::_x( 'Euro', 'Currency Symbol', 'elementor' ),
					'baht' => '&#3647; ' . Wp_Helper::_x( 'Baht', 'Currency Symbol', 'elementor' ),
					'franc' => '&#8355; ' . Wp_Helper::_x( 'Franc', 'Currency Symbol', 'elementor' ),
					'guilder' => '&fnof; ' . Wp_Helper::_x( 'Guilder', 'Currency Symbol', 'elementor' ),
					'krona' => 'kr ' . Wp_Helper::_x( 'Krona', 'Currency Symbol', 'elementor' ),
					'lira' => '&#8356; ' . Wp_Helper::_x( 'Lira', 'Currency Symbol', 'elementor' ),
					'peseta' => '&#8359 ' . Wp_Helper::_x( 'Peseta', 'Currency Symbol', 'elementor' ),
					'peso' => '&#8369; ' . Wp_Helper::_x( 'Peso', 'Currency Symbol', 'elementor' ),
					'pound' => '&#163; ' . Wp_Helper::_x( 'Pound Sterling', 'Currency Symbol', 'elementor' ),
					'real' => 'R$ ' . Wp_Helper::_x( 'Real', 'Currency Symbol', 'elementor' ),
					'ruble' => '&#8381; ' . Wp_Helper::_x( 'Ruble', 'Currency Symbol', 'elementor' ),
					'rupee' => '&#8360; ' . Wp_Helper::_x( 'Rupee', 'Currency Symbol', 'elementor' ),
					'indian_rupee' => '&#8377; ' . Wp_Helper::_x( 'Rupee (Indian)', 'Currency Symbol', 'elementor' ),
					'shekel' => '&#8362; ' . Wp_Helper::_x( 'Shekel', 'Currency Symbol', 'elementor' ),
					'yen' => '&#165; ' . Wp_Helper::_x( 'Yen/Yuan', 'Currency Symbol', 'elementor' ),
					'won' => '&#8361; ' . Wp_Helper::_x( 'Won', 'Currency Symbol', 'elementor' ),
					'custom' => Wp_Helper::__( 'Custom', 'elementor' ),
				],
				'default' => 'dollar',
			]
		);

		$this->add_control(
			'currency_symbol_custom',
			[
				'label' => Wp_Helper::__( 'Custom Symbol', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'condition' => [
					'currency_symbol' => 'custom',
				],
			]
		);

		$this->add_control(
			'price',
			[
				'label' => Wp_Helper::__( 'Price', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => '39.99',
			]
		);

		$this->add_control(
			'currency_format',
			[
				'label' => Wp_Helper::__( 'Currency Format', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => '1,234.56 (Default)',
					',' => '1.234,56',
				],
			]
		);

		$this->add_control(
			'sale',
			[
				'label' => Wp_Helper::__( 'Sale', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => Wp_Helper::__( 'On', 'elementor' ),
				'label_off' => Wp_Helper::__( 'Off', 'elementor' ),
				'default' => '',
			]
		);

		$this->add_control(
			'original_price',
			[
				'label' => Wp_Helper::__( 'Original Price', 'elementor' ),
				'type' => Controls_Manager::NUMBER,
				'default' => '59',
				'condition' => [
					'sale' => 'yes',
				],
			]
		);

		$this->add_control(
			'period',
			[
				'label' => Wp_Helper::__( 'Period', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Monthly', 'elementor' ),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_features',
			[
				'label' => Wp_Helper::__( 'Features', 'elementor' ),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'item_text',
			[
				'label' => Wp_Helper::__( 'Text', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'List Item', 'elementor' ),
			]
		);

		$default_icon = [
			'value' => 'far fa-check-circle',
			'library' => 'fa-regular',
		];

		$repeater->add_control(
			'selected_item_icon',
			[
				'label' => Wp_Helper::__( 'Icon', 'elementor' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'item_icon',
				'default' => $default_icon,
			]
		);

		$repeater->add_control(
			'item_icon_color',
			[
				'label' => Wp_Helper::__( 'Icon Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} i' => 'color: {{VALUE}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'features_list',
			[
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'item_text' => Wp_Helper::__( 'List Item #1', 'elementor' ),
						'selected_item_icon' => $default_icon,
					],
					[
						'item_text' => Wp_Helper::__( 'List Item #2', 'elementor' ),
						'selected_item_icon' => $default_icon,
					],
					[
						'item_text' => Wp_Helper::__( 'List Item #3', 'elementor' ),
						'selected_item_icon' => $default_icon,
					],
				],
				'title_field' => '{{{ item_text }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_footer',
			[
				'label' => Wp_Helper::__( 'Footer', 'elementor' ),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => Wp_Helper::__( 'Button Text', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Click Here', 'elementor' ),
			]
		);

		$this->add_control(
			'link',
			[
				'label' => Wp_Helper::__( 'Link', 'elementor' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'placeholder' => Wp_Helper::__( 'https://your-link.com', 'elementor' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$this->add_control(
			'footer_additional_info',
			[
				'label' => Wp_Helper::__( 'Additional Info', 'elementor' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => Wp_Helper::__( 'This is text element', 'elementor' ),
				'rows' => 3,
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
			'show_ribbon',
			[
				'label' => Wp_Helper::__( 'Show', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'ribbon_title',
			[
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Popular', 'elementor' ),
				'condition' => [
					'show_ribbon' => 'yes',
				],
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
					'show_ribbon' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_header_style',
			[
				'label' => Wp_Helper::__( 'Header', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'header_bg_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__header' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_heading_style',
			[
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__heading' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'heading_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__heading',
			]
		);

		$this->add_control(
			'heading_sub_heading_style',
			[
				'label' => Wp_Helper::__( 'Sub Title', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sub_heading_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__subheading' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'sub_heading_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__subheading',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pricing_element_style',
			[
				'label' => Wp_Helper::__( 'Pricing', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'pricing_element_bg_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__price' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'pricing_element_padding',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__price' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__currency, {{WRAPPER}} .elementor-price-table__integer-part, {{WRAPPER}} .elementor-price-table__fractional-part' => 'color: {{VALUE}}',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__price',
			]
		);

		$this->add_control(
			'heading_currency_style',
			[
				'label' => Wp_Helper::__( 'Currency Symbol', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'currency_symbol!' => '',
				],
			]
		);

		$this->add_control(
			'currency_size',
			[
				'label' => Wp_Helper::__( 'Size', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__currency' => 'font-size: calc({{SIZE}}em/100)',
				],
				'condition' => [
					'currency_symbol!' => '',
				],
			]
		);

		$this->add_control(
			'currency_position',
			[
				'label' => Wp_Helper::__( 'Position', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'before',
				'options' => [
					'before' => [
						'title' => Wp_Helper::__( 'Before', 'elementor' ),
						'icon' => 'eicon-h-align-left',
					],
					'after' => [
						'title' => Wp_Helper::__( 'After', 'elementor' ),
						'icon' => 'eicon-h-align-right',
					],
				],
			]
		);

		$this->add_control(
			'currency_vertical_position',
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
				'default' => 'top',
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__currency' => 'align-self: {{VALUE}}',
				],
				'condition' => [
					'currency_symbol!' => '',
				],
			]
		);

		$this->add_control(
			'fractional_part_style',
			[
				'label' => Wp_Helper::__( 'Fractional Part', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'fractional-part_size',
			[
				'label' => Wp_Helper::__( 'Size', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__fractional-part' => 'font-size: calc({{SIZE}}em/100)',
				],
			]
		);

		$this->add_control(
			'fractional_part_vertical_position',
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
				'default' => 'top',
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__after-price' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'heading_original_price_style',
			[
				'label' => Wp_Helper::__( 'Original Price', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'sale' => 'yes',
					'original_price!' => '',
				],
			]
		);

		$this->add_control(
			'original_price_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__original-price' => 'color: {{VALUE}}',
				],
				'condition' => [
					'sale' => 'yes',
					'original_price!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'original_price_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__original-price',
				'condition' => [
					'sale' => 'yes',
					'original_price!' => '',
				],
			]
		);

		$this->add_control(
			'original_price_vertical_position',
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
				'default' => 'bottom',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__original-price' => 'align-self: {{VALUE}}',
				],
				'condition' => [
					'sale' => 'yes',
					'original_price!' => '',
				],
			]
		);

		$this->add_control(
			'heading_period_style',
			[
				'label' => Wp_Helper::__( 'Period', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'period!' => '',
				],
			]
		);

		$this->add_control(
			'period_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__period' => 'color: {{VALUE}}',
				],
				'condition' => [
					'period!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'period_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__period',
				'condition' => [
					'period!' => '',
				],
			]
		);

		$this->add_control(
			'period_position',
			[
				'label' => Wp_Helper::__( 'Position', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'label_block' => false,
				'options' => [
					'below' => Wp_Helper::__( 'Below', 'elementor' ),
					'beside' => Wp_Helper::__( 'Beside', 'elementor' ),
				],
				'default' => 'below',
				'condition' => [
					'period!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_features_list_style',
			[
				'label' => Wp_Helper::__( 'Features', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'features_list_bg_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'features_list_padding',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'features_list_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'features_list_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__features-list li',
			]
		);

		$this->add_control(
			'features_list_alignment',
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
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'item_width',
			[
				'label' => Wp_Helper::__( 'Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'%' => [
						'min' => 25,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__feature-inner' => 'margin-left: calc((100% - {{SIZE}}%)/2); margin-right: calc((100% - {{SIZE}}%)/2)',
				],
			]
		);

		$this->add_control(
			'list_divider',
			[
				'label' => Wp_Helper::__( 'Divider', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'divider_style',
			[
				'label' => Wp_Helper::__( 'Style', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'solid' => Wp_Helper::__( 'Solid', 'elementor' ),
					'double' => Wp_Helper::__( 'Double', 'elementor' ),
					'dotted' => Wp_Helper::__( 'Dotted', 'elementor' ),
					'dashed' => Wp_Helper::__( 'Dashed', 'elementor' ),
				],
				'default' => 'solid',
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'border-top-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ddd',
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'border-top-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'divider_weight',
			[
				'label' => Wp_Helper::__( 'Weight', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 2,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'border-top-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'divider_width',
			[
				'label' => Wp_Helper::__( 'Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'margin-left: calc((100% - {{SIZE}}%)/2); margin-right: calc((100% - {{SIZE}}%)/2)',
				],
			]
		);

		$this->add_control(
			'divider_gap',
			[
				'label' => Wp_Helper::__( 'Gap', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 15,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 50,
					],
				],
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'margin-top: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_footer_style',
			[
				'label' => Wp_Helper::__( 'Footer', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'footer_bg_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__footer' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'footer_padding',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__footer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_footer_button',
			[
				'label' => Wp_Helper::__( 'Button', 'elementor' ),
				'type' => Controls_Manager::HEADING,
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
				'default' => 'md',
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

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
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
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__button',
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(), [
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .elementor-price-table__button',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_text_padding',
			[
				'label' => Wp_Helper::__( 'Text Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => Wp_Helper::__( 'Hover', 'elementor' ),
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'button_hover_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_animation',
			[
				'label' => Wp_Helper::__( 'Animation', 'elementor' ),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'heading_additional_info',
			[
				'label' => Wp_Helper::__( 'Additional Info', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => [
					'footer_additional_info!' => '',
				],
			]
		);

		$this->add_control(
			'additional_info_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__additional_info' => 'color: {{VALUE}}',
				],
				'condition' => [
					'footer_additional_info!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'additional_info_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__additional_info',
				'condition' => [
					'footer_additional_info!' => '',
				],
			]
		);

		$this->add_control(
			'additional_info_margin',
			[
				'label' => Wp_Helper::__( 'Margin', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default' => [
					'top' => 15,
					'right' => 30,
					'bottom' => 0,
					'left' => 30,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__additional_info' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
				'condition' => [
					'footer_additional_info!' => '',
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
					'show_ribbon' => 'yes',
				],
			]
		);

		$this->add_control(
			'ribbon_bg_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__ribbon-inner' => 'background-color: {{VALUE}}',
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
					'{{WRAPPER}} .elementor-price-table__ribbon-inner' => 'margin-top: {{SIZE}}{{UNIT}}; transform: ' . $ribbon_distance_transform,
				],
			]
		);

		$this->add_control(
			'ribbon_text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#ffffff',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__ribbon-inner' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'ribbon_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__ribbon-inner',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'selector' => '{{WRAPPER}} .elementor-price-table__ribbon-inner',
			]
		);

		$this->end_controls_section();
	}

	private function render_currency_symbol( $symbol, $location ) {
		$currency_position = $this->get_settings( 'currency_position' );
		$location_setting = ! empty( $currency_position ) ? $currency_position : 'before';
		if ( ! empty( $symbol ) && $location === $location_setting ) {
			echo '<span class="elementor-price-table__currency elementor-currency--' . $location . '">' . $symbol . '</span>';
		}
	}

	private function get_currency_symbol( $symbol_name ) {
		$symbols = [
			'dollar' => '&#36;',
			'euro' => '&#128;',
			'franc' => '&#8355;',
			'pound' => '&#163;',
			'ruble' => '&#8381;',
			'shekel' => '&#8362;',
			'baht' => '&#3647;',
			'yen' => '&#165;',
			'won' => '&#8361;',
			'guilder' => '&fnof;',
			'peso' => '&#8369;',
			'peseta' => '&#8359',
			'lira' => '&#8356;',
			'rupee' => '&#8360;',
			'indian_rupee' => '&#8377;',
			'real' => 'R$',
			'krona' => 'kr',
		];

		return isset( $symbols[ $symbol_name ] ) ? $symbols[ $symbol_name ] : '';
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$symbol = '';

		if ( ! empty( $settings['currency_symbol'] ) ) {
			if ( 'custom' !== $settings['currency_symbol'] ) {
				$symbol = $this->get_currency_symbol( $settings['currency_symbol'] );
			} else {
				$symbol = $settings['currency_symbol_custom'];
			}
		}
		$currency_format = empty( $settings['currency_format'] ) ? '.' : $settings['currency_format'];
		$price = explode( $currency_format, $settings['price'] );
		$intpart = $price[0];
		$fraction = '';
		if ( 2 === count( $price ) ) {
			$fraction = $price[1];
		}

		$this->add_render_attribute( 'button_text', 'class', [
			'elementor-price-table__button',
			'elementor-button',
			'elementor-size-' . $settings['button_size'],
		] );

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'button_text', $settings['link'] );
		}

		if ( ! empty( $settings['button_hover_animation'] ) ) {
			$this->add_render_attribute( 'button_text', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
		}

		$this->add_render_attribute( 'heading', 'class', 'elementor-price-table__heading' );
		$this->add_render_attribute( 'sub_heading', 'class', 'elementor-price-table__subheading' );
		$this->add_render_attribute( 'period', 'class', [ 'elementor-price-table__period', 'elementor-typo-excluded' ] );
		$this->add_render_attribute( 'footer_additional_info', 'class', 'elementor-price-table__additional_info' );
		$this->add_render_attribute( 'ribbon_title', 'class', 'elementor-price-table__ribbon-inner' );

		$this->add_inline_editing_attributes( 'heading', 'none' );
		$this->add_inline_editing_attributes( 'sub_heading', 'none' );
		$this->add_inline_editing_attributes( 'period', 'none' );
		$this->add_inline_editing_attributes( 'footer_additional_info' );
		$this->add_inline_editing_attributes( 'button_text' );
		$this->add_inline_editing_attributes( 'ribbon_title' );

		$period_position = $settings['period_position'];
		$period_element = '<span ' . $this->get_render_attribute_string( 'period' ) . '>' . $settings['period'] . '</span>';
		$heading_tag = $settings['heading_tag'];

		$migration_allowed = Icons_Manager::is_migration_allowed();
		?>

		<div class="elementor-price-table">
			<?php if ( $settings['heading'] || $settings['sub_heading'] ) : ?>
				<div class="elementor-price-table__header">
					<?php if ( ! empty( $settings['heading'] ) ) : ?>
						<<?php echo $heading_tag . ' ' . $this->get_render_attribute_string( 'heading' ); ?>><?php echo $settings['heading'] . '</' . $heading_tag; ?>>
					<?php endif; ?>

					<?php if ( ! empty( $settings['sub_heading'] ) ) : ?>
						<span <?php echo $this->get_render_attribute_string( 'sub_heading' ); ?>><?php echo $settings['sub_heading']; ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="elementor-price-table__price">
				<?php if ( 'yes' === $settings['sale'] && ! empty( $settings['original_price'] ) ) : ?>
					<div class="elementor-price-table__original-price elementor-typo-excluded"><?php echo $symbol . $settings['original_price']; ?></div>
				<?php endif; ?>
				<?php $this->render_currency_symbol( $symbol, 'before' ); ?>
				<?php if ( ! empty( $intpart ) || 0 <= $intpart ) : ?>
					<span class="elementor-price-table__integer-part"><?php echo $intpart; ?></span>
				<?php endif; ?>

				<?php if ( '' !== $fraction || ( ! empty( $settings['period'] ) && 'beside' === $period_position ) ) : ?>
					<div class="elementor-price-table__after-price">
						<span class="elementor-price-table__fractional-part"><?php echo $fraction; ?></span>

						<?php if ( ! empty( $settings['period'] ) && 'beside' === $period_position ) : ?>
							<?php echo $period_element; ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php $this->render_currency_symbol( $symbol, 'after' ); ?>

				<?php if ( ! empty( $settings['period'] ) && 'below' === $period_position ) : ?>
					<?php echo $period_element; ?>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $settings['features_list'] ) ) : ?>
				<ul class="elementor-price-table__features-list">
					<?php
					foreach ( $settings['features_list'] as $index => $item ) :
						$repeater_setting_key = $this->get_repeater_setting_key( 'item_text', 'features_list', $index );
						$this->add_inline_editing_attributes( $repeater_setting_key );

						$migrated = isset( $item['__fa4_migrated']['selected_item_icon'] );
						// add old default
						if ( ! isset( $item['item_icon'] ) && ! $migration_allowed ) {
							$item['item_icon'] = 'fa fa-check-circle';
						}
						$is_new = ! isset( $item['item_icon'] ) && $migration_allowed;
						?>
						<li class="elementor-repeater-item-<?php echo $item['_id']; ?>">
							<div class="elementor-price-table__feature-inner">
								<?php if ( ! empty( $item['item_icon'] ) || ! empty( $item['selected_item_icon'] ) ) :
									if ( $is_new || $migrated ) :
										Icons_Manager::render_icon( $item['selected_item_icon'], [ 'aria-hidden' => 'true' ] );
									else : ?>
										<i class="<?php echo Wp_Helper::esc_attr( $item['item_icon'] ); ?>" aria-hidden="true"></i>
										<?php
									endif;
								endif; ?>
								<?php if ( ! empty( $item['item_text'] ) ) : ?>
									<span <?php echo $this->get_render_attribute_string( $repeater_setting_key ); ?>>
										<?php echo $item['item_text']; ?>
									</span>
									<?php
								else :
									echo '&nbsp;';
								endif;
								?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( ! empty( $settings['button_text'] ) || ! empty( $settings['footer_additional_info'] ) ) : ?>
				<div class="elementor-price-table__footer">
					<?php if ( ! empty( $settings['button_text'] ) ) : ?>
						<a <?php echo $this->get_render_attribute_string( 'button_text' ); ?>><?php echo $settings['button_text']; ?></a>
					<?php endif; ?>

					<?php if ( ! empty( $settings['footer_additional_info'] ) ) : ?>
						<div <?php echo $this->get_render_attribute_string( 'footer_additional_info' ); ?>><?php echo $settings['footer_additional_info']; ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		if ( 'yes' === $settings['show_ribbon'] && ! empty( $settings['ribbon_title'] ) ) :
			$this->add_render_attribute( 'ribbon-wrapper', 'class', 'elementor-price-table__ribbon' );

			if ( ! empty( $settings['ribbon_horizontal_position'] ) ) :
				$this->add_render_attribute( 'ribbon-wrapper', 'class', 'elementor-ribbon-' . $settings['ribbon_horizontal_position'] );
			endif;

			?>
			<div <?php echo $this->get_render_attribute_string( 'ribbon-wrapper' ); ?>>
				<div <?php echo $this->get_render_attribute_string( 'ribbon_title' ); ?>><?php echo $settings['ribbon_title']; ?></div>
			</div>
			<?php
		endif;
	}

	/**
	 * Render Price Table widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	protected function _content_template() {
		?>
		<#
			var symbols = {
				dollar: '&#36;',
				euro: '&#128;',
				franc: '&#8355;',
				pound: '&#163;',
				ruble: '&#8381;',
				shekel: '&#8362;',
				baht: '&#3647;',
				yen: '&#165;',
				won: '&#8361;',
				guilder: '&fnof;',
				peso: '&#8369;',
				peseta: '&#8359;',
				lira: '&#8356;',
				rupee: '&#8360;',
				indian_rupee: '&#8377;',
				real: 'R$',
				krona: 'kr'
			};

			var symbol = '',
				iconsHTML = {};

			if ( settings.currency_symbol ) {
				if ( 'custom' !== settings.currency_symbol ) {
					symbol = symbols[ settings.currency_symbol ] || '';
				} else {
					symbol = settings.currency_symbol_custom;
				}
			}

			var buttonClasses = 'elementor-price-table__button elementor-button elementor-size-' + settings.button_size;

			if ( settings.button_hover_animation ) {
				buttonClasses += ' elementor-animation-' + settings.button_hover_animation;
			}

		view.addRenderAttribute( 'heading', 'class', 'elementor-price-table__heading' );
		view.addRenderAttribute( 'sub_heading', 'class', 'elementor-price-table__subheading' );
		view.addRenderAttribute( 'period', 'class', ['elementor-price-table__period', 'elementor-typo-excluded'] );
		view.addRenderAttribute( 'footer_additional_info', 'class', 'elementor-price-table__additional_info'  );
		view.addRenderAttribute( 'button_text', 'class', buttonClasses  );
		view.addRenderAttribute( 'ribbon_title', 'class', 'elementor-price-table__ribbon-inner'  );

		view.addInlineEditingAttributes( 'heading', 'none' );
		view.addInlineEditingAttributes( 'sub_heading', 'none' );
		view.addInlineEditingAttributes( 'period', 'none' );
		view.addInlineEditingAttributes( 'footer_additional_info' );
		view.addInlineEditingAttributes( 'button_text' );
		view.addInlineEditingAttributes( 'ribbon_title' );

		var currencyFormat = settings.currency_format || '.',
			price = settings.price.split( currencyFormat ),
			intpart = price[0],
			fraction = price[1],

			periodElement = '<span ' + view.getRenderAttributeString( "period" ) + '>' + settings.period + '</span>';

		#>
		<div class="elementor-price-table">
			<# if ( settings.heading || settings.sub_heading ) { #>
				<div class="elementor-price-table__header">
					<# if ( settings.heading ) { #>
						<{{ settings.heading_tag }} {{{ view.getRenderAttributeString( 'heading' ) }}}>{{{ settings.heading }}}</{{ settings.heading_tag }}>
					<# } #>
					<# if ( settings.sub_heading ) { #>
						<span {{{ view.getRenderAttributeString( 'sub_heading' ) }}}>{{{ settings.sub_heading }}}</span>
					<# } #>
				</div>
			<# } #>

			<div class="elementor-price-table__price">
				<# if ( settings.sale && settings.original_price ) { #>
					<div class="elementor-price-table__original-price elementor-typo-excluded">{{{ symbol + settings.original_price }}}</div>
				<# } #>

				<# if ( ! _.isEmpty( symbol ) && ( 'before' == settings.currency_position || _.isEmpty( settings.currency_position ) ) ) { #>
					<span class="elementor-price-table__currency elementor-currency--before">{{{ symbol }}}</span>
				<# } #>
				<# if ( intpart ) { #>
					<span class="elementor-price-table__integer-part">{{{ intpart }}}</span>
				<# } #>
				<div class="elementor-price-table__after-price">
					<# if ( fraction ) { #>
						<span class="elementor-price-table__fractional-part">{{{ fraction }}}</span>
					<# } #>
					<# if ( settings.period && 'beside' === settings.period_position ) { #>
						{{{ periodElement }}}
					<# } #>
				</div>

				<# if ( ! _.isEmpty( symbol ) && 'after' == settings.currency_position ) { #>
				<span class="elementor-price-table__currency elementor-currency--after">{{{ symbol }}}</span>
				<# } #>

				<# if ( settings.period && 'below' === settings.period_position ) { #>
					{{{ periodElement }}}
				<# } #>
			</div>

			<# if ( settings.features_list ) { #>
				<ul class="elementor-price-table__features-list">
				<# _.each( settings.features_list, function( item, index ) {

					var featureKey = view.getRepeaterSettingKey( 'item_text', 'features_list', index ),
						migrated = elementor.helpers.isIconMigrated( item, 'selected_item_icon' );

					view.addInlineEditingAttributes( featureKey ); #>

						<li class="elementor-repeater-item-{{ item._id }}">
							<div class="elementor-price-table__feature-inner">
								<# if ( item.item_icon  || item.selected_item_icon ) {
									iconsHTML[ index ] = elementor.helpers.renderIcon( view, item.selected_item_icon, { 'aria-hidden': 'true' }, 'i', 'object' );
									if ( ( ! item.item_icon || migrated ) && iconsHTML[ index ] && iconsHTML[ index ].rendered ) { #>
										{{{ iconsHTML[ index ].value }}}
									<# } else { #>
										<i class="{{ item.item_icon }}" aria-hidden="true"></i>
									<# }
								} #>
								<# if ( ! _.isEmpty( item.item_text.trim() ) ) { #>
									<span {{{ view.getRenderAttributeString( featureKey ) }}}>{{{ item.item_text }}}</span>
								<# } else { #>
									&nbsp;
								<# } #>
							</div>
						</li>
					<# } ); #>
				</ul>
			<# } #>

			<# if ( settings.button_text || settings.footer_additional_info ) { #>
				<div class="elementor-price-table__footer">
					<# if ( settings.button_text ) { #>
						<a href="#" {{{ view.getRenderAttributeString( 'button_text' ) }}}>{{{ settings.button_text }}}</a>
					<# } #>
					<# if ( settings.footer_additional_info ) { #>
						<p {{{ view.getRenderAttributeString( 'footer_additional_info' ) }}}>{{{ settings.footer_additional_info }}}</p>
					<# } #>
				</div>
			<# } #>
		</div>

		<# if ( 'yes' === settings.show_ribbon && settings.ribbon_title ) {
			var ribbonClasses = 'elementor-price-table__ribbon';
			if ( settings.ribbon_horizontal_position ) {
				ribbonClasses += ' elementor-ribbon-' + settings.ribbon_horizontal_position;
			} #>
			<div class="{{ ribbonClasses }}">
				<div {{{ view.getRenderAttributeString( 'ribbon_title' ) }}}>{{{ settings.ribbon_title }}}</div>
			</div>
		<# } #>
		<?php
	}
}
