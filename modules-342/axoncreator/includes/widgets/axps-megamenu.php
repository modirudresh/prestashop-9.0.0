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
class Widget_Axps_Megamenu extends Widget_Base {

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
		return 'axps-megamenu';
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
		return Wp_Helper::__( 'Megamenu', 'elementor' );
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
		return 'eicon-nav-menu';
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
		return [ 'menu', 'axps' ];
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
			'section_menu',
			[
				'label' => Wp_Helper::__( 'Menu', 'elementor' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => Wp_Helper::__( 'Layout', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal' => Wp_Helper::__( 'Horizontal', 'elementor' ),
					'vertical'   => Wp_Helper::__( 'Vertical', 'elementor' ),
					'mobile'   => Wp_Helper::__( 'Mobile', 'elementor' ),
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
				'prefix_class' => 'button-layout-',
				'condition'    => [
					'layout' => [ 'mobile' ],
				],
			]
		);
		
		$this->add_control(
			'icon',
			[
				'label' => Wp_Helper::__('Button Icon', 'elementor'),
				'type' => Controls_Manager::ICONS,
				'separator' => 'before',
				'condition' => [
					'layout' => [ 'mobile' ],
					'button_layout!' => 'text',
				],
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
					'layout' => [ 'mobile' ],
				],
				'prefix_class' => 'elementor%s-align-',
				'default' => '',
			]
		);
		
		$this->add_control(
			'vertical_text',
			[
				'label'        => Wp_Helper::__( 'Display title text', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'has-text',
				'options' => [
					'has-text' => Wp_Helper::__( 'Yes', 'elementor' ),
					'no-text'   => Wp_Helper::__( 'No', 'elementor' ),
				],
				'prefix_class' => 'axps-menu-',
				'condition'    => [
					'layout' => [ 'vertical' ],
				],
			]
		);
		
		$this->add_control(
			'vertical_margin_top',
			[
				'label' => Wp_Helper::__( 'Dropdown margin top', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
				],
				'range' => [
					'px' => [
						'min' => -10,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .menu-vertical' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
				'condition'    => [
					'layout' => [ 'vertical' ],
				],
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
			'section_style_main_menu',
			[
				'label'     => Wp_Helper::__( 'Main Menu', 'elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition'    => [
					'layout!' => [ 'mobile' ],
				],
			]
		);
		
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'typography',
					'selector' => '{{WRAPPER}} .menu-horizontal .item-level-0 > a, {{WRAPPER}} .menu-vertical .item-level-0 > a',
				]
			);

			$this->start_controls_tabs( 'tabs_style' );

				$this->start_controls_tab(
					'tab_normal',
					[
						'label' => Wp_Helper::__( 'Normal', 'elementor' ),
					]
				);

					$this->add_control(
						'text_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .menu-horizontal .item-level-0 > a, {{WRAPPER}} .menu-vertical .item-level-0 > a' => 'fill: {{VALUE}}; color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'background_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .menu-horizontal .item-level-0 > a, {{WRAPPER}} .menu-vertical .item-level-0 > a' => 'background-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_hover',
					[
						'label' => Wp_Helper::__( 'Hover & Active', 'elementor' ),
					]
				);

					$this->add_control(
						'hover_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .menu-horizontal .item-level-0:hover > a, {{WRAPPER}} .menu-vertical .item-level-0:hover > a, {{WRAPPER}} .menu-horizontal .item-level-0.current-menu-item > a, {{WRAPPER}} .menu-vertical .item-level-0.current-menu-item > a' => 'color: {{VALUE}};'
							],
						]
					);

					$this->add_control(
						'background_hover_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .menu-horizontal .item-level-0:hover > a, {{WRAPPER}} .menu-vertical .item-level-0:hover > a, {{WRAPPER}} .menu-horizontal .item-level-0.current-menu-item > a, {{WRAPPER}} .menu-vertical .item-level-0.current-menu-item > a' => 'background-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'hover_border_color',
						[
							'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .menu-horizontal .item-level-0:hover, {{WRAPPER}} .menu-vertical .item-level-0:hover, {{WRAPPER}} .menu-horizontal .item-level-0.current-menu-item, {{WRAPPER}} .menu-vertical .item-level-0.current-menu-item' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'border',
					'selector' => '{{WRAPPER}} .menu-horizontal .item-level-0, {{WRAPPER}} .menu-vertical .item-level-0',
					'separator' => 'before',
				]
			);

			$this->add_control(
				'border_radius',
				[
					'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .menu-horizontal .item-level-0, {{WRAPPER}} .menu-vertical .item-level-0' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],					
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'box_shadow',
					'selector' => '{{WRAPPER}} .menu-horizontal .item-level-0, {{WRAPPER}} .menu-vertical',
				]
			);

			$this->add_control(
				'text_padding',
				[
					'label' => Wp_Helper::__( 'Padding', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .menu-horizontal .item-level-0 > a, {{WRAPPER}} .menu-vertical .item-level-0 > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);

			$this->add_control(
				'margin',
				[
					'label' => Wp_Helper::__( 'Margin', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px' ],
					'selectors' => [
						'{{WRAPPER}} .menu-horizontal .item-level-0' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
		
		$this->end_controls_section();
		
		$this->start_controls_section(
			'section_vertical_title',
			[
				'label' => Wp_Helper::__( 'Vertical Title', 'elementor' ),
				'type' => Controls_Manager::SECTION,
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'    => [
					'layout' => [ 'vertical' ],
				],
			]
		);
		
			$this->add_control(
				'title_icon_size',
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
						'{{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title i' => 'font-size: {{SIZE}}{{UNIT}}',
						'{{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);		
		
			$this->add_control(
				'title_icon_size_margin',
				[
					'label' => Wp_Helper::__( 'Icon Margin Right', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'body:not(.rtl) {{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title span' => 'margin-left: {{SIZE}}{{UNIT}}',
						'body.rtl {{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title span' => 'margin-right: {{SIZE}}{{UNIT}}',
					]
				]
			);
		
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'title_typography',
					'selector' => '{{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title',
				]
			);

			$this->start_controls_tabs( 'title_tabs_style' );

				$this->start_controls_tab(
					'title_tab_normal',
					[
						'label' => Wp_Helper::__( 'Normal', 'elementor' ),
					]
				);

					$this->add_control(
						'title_text_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title' => 'fill: {{VALUE}}; color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'title_background_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title' => 'background-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'title_tab_hover',
					[
						'label' => Wp_Helper::__( 'Hover & Active', 'elementor' ),
					]
				);

					$this->add_control(
						'title_hover_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .wrapper-menu-vertical:hover .menu-vertical-title, {{WRAPPER}} .wrapper-menu-vertical.active .menu-vertical-title' => 'fill: {{VALUE}}; color: {{VALUE}};'
							],
						]
					);

					$this->add_control(
						'title_background_hover_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .wrapper-menu-vertical:hover .menu-vertical-title, {{WRAPPER}} .wrapper-menu-vertical.active .menu-vertical-title' => 'background-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'title_hover_border_color',
						[
							'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .wrapper-menu-vertical:hover .menu-vertical-title, {{WRAPPER}} .wrapper-menu-vertical.active .menu-vertical-title' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'title_border',
					'selector' => '{{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title',
					'separator' => 'before',
				]
			);

			$this->add_control(
				'title_border_radius',
				[
					'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					]
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'title_box_shadow',
					'selector' => '{{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title',
				]
			);

			$this->add_control(
				'title_text_padding',
				[
					'label' => Wp_Helper::__( 'Padding', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .wrapper-menu-vertical .menu-vertical-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);

		$this->end_controls_section();
		
		$this->start_controls_section(
			'section_button',
			[
				'label' => Wp_Helper::__( 'Button', 'elementor' ),
				'type' => Controls_Manager::SECTION,
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'    => [
					'layout' => [ 'mobile' ],
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
					],
					'separator' => 'before',
					'condition' => [
						'button_layout!' => 'text',
					],
				]
			);	
		
			$this->add_control(
				'button_icon_size_margin',
				[
					'label' => Wp_Helper::__( 'Icon Margin Right', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'body:not(.rtl) {{WRAPPER}} .btn-canvas .btn-canvas-text' => 'margin-left: {{SIZE}}{{UNIT}}',
						'body.rtl {{WRAPPER}} .btn-canvas .btn-canvas-text' => 'margin-right: {{SIZE}}{{UNIT}}',
					]
				]
			);
		
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'button_typography',
					'selector' => '{{WRAPPER}} .btn-canvas',
					'condition' => [
						'button_layout!' => 'icon',
					],
				]
			);
				
			$this->start_controls_tabs( 'button_tabs_style' );

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

		if( \Module::isEnabled('nrtmegamenu') ) {
			$settings = $this->get_settings_for_display();
			
			if ( ! empty( $settings['icon']['value'] ) ) {
				ob_start();
					Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] );
				$icon = ob_get_clean();
			}else{
				$icon = '';
			}

			$module = \Module::getInstanceByName('nrtmegamenu');

			if ( $settings['layout'] === 'horizontal' ) {
				echo $module->hookDisplayMenuHorizontal( [] );
			}else if ( $settings['layout'] === 'vertical' ) {	
				echo $module->hookDisplayMenuVertical( [] );
			}else{
				echo $this->fetch('module:nrtmegamenu/views/templates/hook/button-canvas.tpl', [ 'icon' => $icon ]);
			}
		}
		
	}
}