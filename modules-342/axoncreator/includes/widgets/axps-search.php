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
 * Elementor image carousel widget.
 *
 * Elementor widget that displays a set of images in a rotating carousel or
 * slider.
 *
 * @since 1.0.0
 */
class Widget_Axps_Search extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve image carousel widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'axps-search';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve image carousel widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return Wp_Helper::__( 'Search', 'elementor' );
	}
	
	public function get_categories() {
		return [ 'axon-elements' ];
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve image carousel widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-search';
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'seach', 'axps' ];
	}

	/**
	 * Register Site Logo controls.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->register_content_controls();
		$this->register_styling_controls();
	}

	/**
	 * Register Site Logo General Controls.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function register_content_controls() {
		
		$this->start_controls_section(
			'section_general_fields',
			[
				'label' => Wp_Helper::__('Search', 'elementor'),
			]
		);
		
		$this->add_control(
			'layout',
			[
				'label'        => Wp_Helper::__( 'Skin', 'elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'button',
				'options'      => [
					'button'    => Wp_Helper::__( 'Popup', 'elementor' ),
					'form'      => Wp_Helper::__( 'Form', 'elementor' ),
				],
			]
		);
				
		$this->add_control(
			'button_layout',
			[
				'label'        => Wp_Helper::__( 'Button Layout', 'elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'icon',
				'options'      => [
					'icon'    => Wp_Helper::__( 'Icon', 'elementor' ),
					'text'      => Wp_Helper::__( 'Text', 'elementor' ),
					'icon_text'      => Wp_Helper::__( 'Icon & Text', 'elementor' ),
				],
				'condition'    => [
					'layout' => [ 'button' ],
				],
				'prefix_class' => 'button-layout-',
			]
		);
		
		$this->add_responsive_control(
			'align',
			[
				'label' => Wp_Helper::__('Alignment', 'elementor'),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => Wp_Helper::__('Left', 'elementor'),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => Wp_Helper::__('Center', 'elementor'),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => Wp_Helper::__('Right', 'elementor'),
						'icon' => 'eicon-text-align-right',
					],
				],
				'condition'    => [
					'layout' => [ 'button' ],
				],
				'prefix_class' => 'elementor%s-align-',
				'default' => '',
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => Wp_Helper::__('Button Icon', 'elementor'),
				'type' => Controls_Manager::ICONS,
				'separator' => 'before',
				'condition' => [
					'layout' => 'button',
					'button_layout!' => 'text',
				],
			]
		);

		$this->add_control(
			'view',
			[
				'label' => Wp_Helper::__('View', 'elementor'),
				'type' => Controls_Manager::HIDDEN,
			]
		);
		
		$this->end_controls_section();
	}
	/**
	 * Register Site Image Style Controls.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function register_styling_controls() {
		
		$this->start_controls_section(
			'section_button',
			[
				'label' => Wp_Helper::__( 'Button', 'elementor' ),
				'type' => Controls_Manager::SECTION,
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'button',
				],
			]
		);
		
			$this->add_control(
				'button_icon_size',
				[
					'label' => Wp_Helper::__( 'Icon Size', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .btn-canvas i' => 'font-size: {{SIZE}}{{UNIT}}',
						'{{WRAPPER}} .btn-canvas svg' => 'width: {{SIZE}}px; height: {{SIZE}}px;',
					],
					'separator' => 'before',
				]
			);		
		
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'button_typography',
					'selector' => '{{WRAPPER}} .btn-canvas .btn-canvas-text',
				]
			);

			$this->start_controls_tabs( 'title_tabs_style' );

				$this->start_controls_tab(
					'button_tab_normal',
					[
						'label' => Wp_Helper::__( 'Normal', 'elementor' ),
					]
				);

					$this->add_control(
						'button_text_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .btn-canvas' => 'fill: {{VALUE}}; color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'button_background_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .btn-canvas' => 'background-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'button_tab_hover',
					[
						'label' => Wp_Helper::__( 'Hover', 'elementor' ),
					]
				);

					$this->add_control(
						'button_hover_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .btn-canvas:hover' => 'fill: {{VALUE}}; color: {{VALUE}};'
							],
						]
					);

					$this->add_control(
						'button_background_hover_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .btn-canvas:hover' => 'background-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'button_hover_border_color',
						[
							'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .btn-canvas:hover' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'button_border',
					'selector' => '{{WRAPPER}} .btn-canvas',
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
						'{{WRAPPER}} .btn-canvas' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'button_box_shadow',
					'selector' => '{{WRAPPER}} .btn-canvas',
				]
			);

			$this->add_control(
				'button_text_padding',
				[
					'label' => Wp_Helper::__( 'Padding', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .btn-canvas' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);

		$this->end_controls_section();
		
		$this->start_controls_section(
			'section_form',
			[
				'label' => Wp_Helper::__( 'Form', 'elementor' ),
				'type' => Controls_Manager::SECTION,
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'form',
				],
			]
		);
		
			$this->add_control(
				'form_width',
				[
					'label' => Wp_Helper::__( 'Width', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => 200,
							'max' => 1920,
						],
						'%' => [
							'min' => 1,
							'max' => 100,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .search-widget.search-wrapper' => 'width: {{SIZE}}{{UNIT}}',
					],
					'separator' => 'before'
				]
			);
		
			$this->add_control(
				'form_height',
				[
					'label' => Wp_Helper::__( 'Height', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 30,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .search-widget .query' => 'height: {{SIZE}}{{UNIT}}',
					],
					'separator' => 'before'
				]
			);
		
			$this->add_control(
				'form_icon_size',
				[
					'label' => Wp_Helper::__( 'Button Icon Size', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .search-widget .search-submit::before' => 'font-size: {{SIZE}}{{UNIT}}',
					],
					'separator' => 'before'
				]
			);	
		
			$this->add_control(
				'form_button_width',
				[
					'label' => Wp_Helper::__( 'Button width', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .search-widget .search-submit' => 'width: {{SIZE}}px;',
					],
					'separator' => 'before'
				]
			);
		
			$this->start_controls_tabs( 'form_button_tabs_style' );

				$this->start_controls_tab(
					'form_button_tab_normal',
					[
						'label' => Wp_Helper::__( 'Button Normal', 'elementor' ),
					]
				);

					$this->add_control(
						'form_button_text_color',
						[
							'label' => Wp_Helper::__( 'Button Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .search-widget .search-submit' => 'fill: {{VALUE}}; color: {{VALUE}};',
								'{{WRAPPER}} .search-widget .search-submit::after' => 'border-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'form_button_background_color',
						[
							'label' => Wp_Helper::__( 'Button Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .search-widget .search-submit' => 'background-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'form_button_tab_hover',
					[
						'label' => Wp_Helper::__( 'Button Hover', 'elementor' ),
					]
				);

					$this->add_control(
						'form_button_hover_color',
						[
							'label' => Wp_Helper::__( 'Button Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .search-widget .search-submit:hover' => 'fill: {{VALUE}}; color: {{VALUE}};'
							],
						]
					);

					$this->add_control(
						'form_button_background_hover_color',
						[
							'label' => Wp_Helper::__( 'Button Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .search-widget .search-submit:hover' => 'background-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();
		
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'form_typography',
					'selector' => '{{WRAPPER}} .search-widget .query',
				]
			);

			$this->add_control(
				'form_text_color',
				[
					'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
					'type' => Controls_Manager::COLOR,
					'default' => '',
					'selectors' => [
						'{{WRAPPER}} .search-widget .query, {{WRAPPER}} .search-widget .search-submit' => 'fill: {{VALUE}}; color: {{VALUE}};',
					],
				]
			);

			$this->add_control(
				'form_background_color',
				[
					'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .search-widget .query' => 'background-color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'form_border',
					'selector' => '{{WRAPPER}} .search-widget .query',
					'separator' => 'before',
				]
			);

			$this->add_control(
				'form_border_radius',
				[
					'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .search-widget .query' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        '{{WRAPPER}} .search-widget .search-submit' => 'border-radius: 0 {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} 0;',
						'body:not(.rtl) {{WRAPPER}} .search-widget .search-submit' => 'border-radius: 0 {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} 0;',
						'body.rtl {{WRAPPER}} .search-widget .search-submit' => 'border-radius: {{RIGHT}}{{UNIT}} 0 0 {{BOTTOM}}{{UNIT}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'form_box_shadow',
					'selector' => '{{WRAPPER}} .search-widget .query',
				]
			);

			$this->add_control(
				'form_text_padding',
				[
					'label' => Wp_Helper::__( 'Padding', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .search-widget .query' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);

		$this->end_controls_section();
	}

	/**
	 * Render Site Image output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function render() {
		
		if ( Wp_Helper::is_admin() ) {
			return;
		}
						
		if(\Module::isEnabled('nrtsearchbar'))
		{
			$settings = $this->get_settings_for_display();

			if ( ! empty( $settings['icon']['value'] ) ) {
				ob_start();
					Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
				$icon = ob_get_clean();
			}else{
				$icon = '';
			}

			if( $settings['layout'] == 'form' ) {
				$context = \Context::getContext();

				echo $this->fetch('module:nrtsearchbar/views/templates/hook/searchbar.tpl', [ 'search_controller_url' => $context->link->getPageLink('search', null, null, null, false, null, true) ]);
			}else{
				echo $this->fetch('module:nrtsearchbar/views/templates/hook/btn_search.tpl', [ 'icon' => $icon ]);
			}
		}
	}
}