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
class Widget_Axps_Linklist extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'axps-linklist';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return Wp_Helper::__( 'Link List', 'axiosy' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-link';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.3.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'axon-elements' ];
	}
	
	public function get_keywords() {
		return [ 'axps', 'link' ];
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
			'section_linklist',
			[
				'label' => Wp_Helper::__( 'Linklist', 'axiosy' ),
			]
		);
		
		$this->add_control(
			'linklist_icon_title',
			[
				'label'       => Wp_Helper::__( 'Icon', 'axiosy' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => 'true',
			]
		);
		
		$this->add_control(
			'linklist_title',
			[
				'label'   => Wp_Helper::__( 'Title', 'axiosy' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => '1',
				'default' => Wp_Helper::__( 'Linklist Title', 'axiosy' ),
			]
		);
		
		$this->add_control(
			'linklist_title_link',
			[
				'label' => Wp_Helper::__( 'Title Link', 'axiosy' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'default' => [
					'url' => '',
				],
			]
		);
								
		$this->add_control(
			'linklist_toggle',
			[
				'label'   => Wp_Helper::__( 'Toggle', 'axiosy' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'none' => Wp_Helper::__( 'None', 'axiosy' ),
					'all' => Wp_Helper::__( 'Toggle', 'axiosy' ),
					'mobile' => Wp_Helper::__( 'Toggle on mobile', 'axiosy' ),
				],
				'default' => 'none',
				'prefix_class' => 'axps-toggle-',
			]
		);
		
		$this->add_responsive_control(
			'linklist_align',
			[
				'label'        => Wp_Helper::__( 'Alignment', 'axiosy' ),
				'type'         => Controls_Manager::CHOOSE,
				'options'      => [
					'left'    => [
						'title' => Wp_Helper::__( 'Left', 'axiosy' ),
						'icon'  => 'eicon-h-align-left',
					],
					'center'  => [
						'title' => Wp_Helper::__( 'Center', 'axiosy' ),
						'icon'  => 'eicon-h-align-center',
					],
					'right'   => [
						'title' => Wp_Helper::__( 'Right', 'axiosy' ),
						'icon'  => 'eicon-h-align-right',
					],

				],
				'default'      => 'left',
				'prefix_class' => 'axps%s-align-',
				'separator' => 'after',
			]
		);

		$repeater = new Repeater();
		
		$repeater->add_control(
			'item_icon_title',
			[
				'label'       => Wp_Helper::__( 'Icon', 'axiosy' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => 'true',
			]
		);

		$repeater->add_control(
			'item_title',
			[
				'label' => Wp_Helper::__( 'Item Title', 'axiosy' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Item Title', 'axiosy' ),
				'placeholder' => Wp_Helper::__( 'Item Title', 'axiosy' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'item_link',
			[
				'label' => Wp_Helper::__( 'Item Link', 'axiosy' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'default'     => [
					'url' => '#',
				],
				'placeholder' => Wp_Helper::__( 'https://your-link.com', 'axiosy' ),
				'show_label' => false,
			]
		);
		
		
		$this->add_control(
			'items',
			[
				'label' => Wp_Helper::__( 'Items', 'axiosy' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'item_title' => Wp_Helper::__( 'Item #1', 'axiosy' ),
						'item_link' => '#',
					],
					[
						'item_title' => Wp_Helper::__( 'Item #2', 'axiosy' ),
						'item_link' => '#',
					],
				],
				'title_field' => '{{{ item_title }}}',
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
			'section_linklist_title',
			[
				'label' => Wp_Helper::__( 'Title', 'axiosy' ),
				'type' => Controls_Manager::SECTION,
				'tab' => Controls_Manager::TAB_STYLE
			]
		);
		
			$this->add_control(
				'title_icon_size',
				[
					'label' => Wp_Helper::__( 'Icon Size', 'axiosy' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .linklist-toggle i' => 'font-size: {{SIZE}}{{UNIT}}',
						'{{WRAPPER}} .linklist-toggle svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);	
		
			$this->add_control(
				'title_icon_size_margin',
				[
					'label' => Wp_Helper::__( 'Icon Margin Right', 'axiosy' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'body:not(.rtl) {{WRAPPER}} .linklist-toggle span' => 'margin-left: {{SIZE}}{{UNIT}}',
						'body.rtl {{WRAPPER}} .linklist-toggle span' => 'margin-right: {{SIZE}}{{UNIT}}',
					]
				]
			);
		
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'title_typography',
					'selector' => '{{WRAPPER}} .linklist-toggle',
				]
			);

			$this->add_group_control(
				Group_Control_Text_Shadow::get_type(),
				[
					'name' => 'title_text_shadow',
					'selector' => '{{WRAPPER}} .linklist-toggle',
				]
			);
		
			$this->add_responsive_control(
				'title_margin_bottom',
				[
					'label' => Wp_Helper::__( 'Margin Bottom', 'axiosy' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .linklist-toggle' => 'margin-bottom: {{SIZE}}{{UNIT}}'
					],
					'separator' => 'before',
				]
			);

			$this->start_controls_tabs( 'title_tabs_style' );

				$this->start_controls_tab(
					'title_tab_normal',
					[
						'label' => Wp_Helper::__( 'Normal', 'axiosy' ),
					]
				);

					$this->add_control(
						'title_text_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .linklist-toggle, {{WRAPPER}} .linklist-toggle a' => 'fill: {{VALUE}}; color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'title_background_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .linklist-toggle' => 'background-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'title_tab_hover',
					[
						'label' => Wp_Helper::__( 'Hover & Active', 'axiosy' ),
					]
				);

					$this->add_control(
						'title_hover_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .linklist-wrapper .linklist-toggle:hover, {{WRAPPER}} .linklist-wrapper .linklist-toggle:hover a' => 'fill: {{VALUE}}; color: {{VALUE}};'
							],
						]
					);

					$this->add_control(
						'title_background_hover_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .linklist-wrapper .linklist-toggle:hover, {{WRAPPER}} .linklist-wrapper .linklist-toggle:hover a' => 'background-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'title_hover_border_color',
						[
							'label' => Wp_Helper::__( 'Border Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .linklist-wrapper .linklist-toggle:hover, {{WRAPPER}} .linklist-wrapper .linklist-toggle:hover a' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'title_border',
					'selector' => '{{WRAPPER}} .linklist-toggle',
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'title_border_radius',
				[
					'label' => Wp_Helper::__( 'Border Radius', 'axiosy' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .linklist-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					]
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'title_box_shadow',
					'selector' => '{{WRAPPER}} .linklist-toggle',
				]
			);

			$this->add_responsive_control(
				'title_text_padding',
				[
					'label' => Wp_Helper::__( 'Padding', 'axiosy' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .linklist-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);

		$this->end_controls_section();
		
		$this->start_controls_section(
			'section_style_main_linklist',
			[
				'label'     => Wp_Helper::__( 'Main Items', 'axiosy' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);
		
			$this->add_control(
				'item_icon_size',
				[
					'label' => Wp_Helper::__( 'Icon Size', 'axiosy' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .linklist-menu i' => 'font-size: {{SIZE}}{{UNIT}}',
						'{{WRAPPER}} .linklist-menu svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);
		
			$this->add_control(
				'item_icon_size_margin',
				[
					'label' => Wp_Helper::__( 'Icon Margin Right', 'axiosy' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 1,
							'max' => 200,
						]
					],
					'selectors' => [
						'body:not(.rtl) {{WRAPPER}} .linklist-menu span' => 'margin-left: {{SIZE}}{{UNIT}}',
						'body.rtl {{WRAPPER}} .linklist-menu span' => 'margin-right: {{SIZE}}{{UNIT}}',
					]
				]
			);
		
			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'typography',
					'selector' => '{{WRAPPER}} .linklist-menu > a',
				]
			);

			$this->add_group_control(
				Group_Control_Text_Shadow::get_type(),
				[
					'name' => 'text_shadow',
					'selector' => '{{WRAPPER}} .linklist-menu > a',
				]
			);

			$this->start_controls_tabs( 'tabs_style' );

				$this->start_controls_tab(
					'tab_normal',
					[
						'label' => Wp_Helper::__( 'Normal', 'axiosy' ),
					]
				);

					$this->add_control(
						'text_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .linklist-menu > a' => 'fill: {{VALUE}}; color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'background_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .linklist-menu' => 'background-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'tab_hover',
					[
						'label' => Wp_Helper::__( 'Hover & Active', 'axiosy' ),
					]
				);

					$this->add_control(
						'hover_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .linklist-menu > a:hover' => 'color: {{VALUE}};'
							],
						]
					);

					$this->add_control(
						'background_hover_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .linklist-menu > a:hover' => 'background-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'hover_border_color',
						[
							'label' => Wp_Helper::__( 'Border Color', 'axiosy' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .linklist-menu > a:hover' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'border',
					'selector' => '{{WRAPPER}} .linklist-menu > a',
					'separator' => 'before',
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'box_shadow',
					'selector' => '{{WRAPPER}} .linklist-menu',
				]
			);

			$this->add_responsive_control(
				'text_padding',
				[
					'label' => Wp_Helper::__( 'Padding', 'axiosy' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', '%' ],
					'selectors' => [
						'{{WRAPPER}} .linklist-menu > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
		
		$settings = $this->get_settings_for_display();
		$items = $this->get_settings_for_display( 'items' );
							
		?>

		<div class="linklist-wrapper">
			<div class="linklist-toggle h6" data-toggle="linklist-widget">
				<?php if ( ! empty( $settings['linklist_title_link']['url'] ) ) { ?><a href="<?php echo $settings['linklist_title_link']['url']; ?>"<?php if ( $settings['linklist_title_link']['is_external'] ) { ?> target="_blank"<?php } ?><?php if ( $settings['linklist_title_link']['nofollow'] ) { ?> rel="nofollow"<?php } ?>><?php } ?><?php if ( ! empty( $settings['linklist_icon_title']['value'] ) ) {Icons_Manager::render_icon( $settings['linklist_icon_title'], [ 'aria-hidden' => 'true' ] );}?><span class="title-text"><?php echo $settings['linklist_title']; ?></span><span class="icon-toggle fa fa-angle-down"></span><?php if ( ! empty( $settings['linklist_title_link']['url'] ) ) { ?></a><?php } ?>
			</div>
			<div class="linklist-menu">
				<?php
				foreach ( $items as $index => $item ) :
				?><a href="<?php echo $item['item_link']['url']; ?>"<?php if ( $item['item_link']['is_external'] ) { ?> target="_blank"<?php } ?><?php if ( $item['item_link']['nofollow'] ) { ?> rel="nofollow"<?php } ?>><?php if ( ! empty( $item['item_icon_title']['value'] ) ) {Icons_Manager::render_icon( $item['item_icon_title'], [ 'aria-hidden' => 'true' ] );} ?><span><?php echo $item['item_title']; ?></span></a><?php endforeach; ?>
			</div>
		</div>

		<?php
		
	}
}