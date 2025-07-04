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
class Widget_Axps_Subscription extends Widget_Base {

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
		return 'axps-subscription';
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
		return Wp_Helper::__( 'Email Subscription', 'elementor' );
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
		return 'eicon-email-field';
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
		return [ 'email', 'subscription' ];
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
			'section_email_subscription',
			[
				'label' => Wp_Helper::__('Email Subscription', 'elementor'),
			]
		);

		$this->add_control(
			'placeholder',
			[
				'label' => Wp_Helper::__('Input Placeholder', 'elementor'),
				'type' => Controls_Manager::TEXT,
				'placeholder' => Wp_Helper::__('Your email address', 'elementor'),
			]
		);

		$this->add_control(
			'button',
			[
				'label' => Wp_Helper::__('Button', 'elementor'),
				'type' => Controls_Manager::TEXT,
				'placeholder' => Wp_Helper::__('Subscribe', 'elementor'),
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
					'justify' => [
						'title' => Wp_Helper::__('Justified', 'elementor'),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'prefix_class' => 'elementor%s-align-',
				'default' => '',
			]
		);
		
		$this->add_control(
			'disable_psgdpr',
			[
				'label'        => Wp_Helper::__( 'Disable Psgdpr', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => Wp_Helper::__( 'Yes', 'elementor' ),
				'label_off'    => Wp_Helper::__( 'No', 'elementor' ),
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => Wp_Helper::__('Button Icon', 'elementor'),
				'type' => Controls_Manager::ICONS,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'icon_align',
			[
				'label' => Wp_Helper::__('Icon Position', 'elementor'),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'left' => Wp_Helper::__('Before', 'elementor'),
					'right' => Wp_Helper::__('After', 'elementor'),
				],
				'separator' => '',
				'default' => 'left',
			]
		);

		$this->add_control(
			'icon_indent',
			[
				'label' => Wp_Helper::__('Icon Spacing', 'elementor'),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				]
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
			'section_input_style',
			[
				'label' => Wp_Helper::__('Input', 'elementor'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'input_text_color',
			[
				'label' => Wp_Helper::__('Text Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} input[name=email]' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'input_width',
			[
				'label' => Wp_Helper::__('Width', 'elementor'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1600,
					],
				],
				'responsive' => true,
				'selectors' => [
					'{{WRAPPER}} input[name=email]' => 'max-width: 100%; width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'input_height',
			[
				'label' => Wp_Helper::__('Height', 'elementor'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 200,
					],
				],
				'separator' => '',
				'selectors' => [
					'{{WRAPPER}} input[name=email]' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'input_align',
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
				'selectors' => [
					'{{WRAPPER}} input[name=email]' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'input_typography',
				'label' => Wp_Helper::__('Typography', 'elementor'),
				'selector' => '{{WRAPPER}} input[name=email]',
			]
		);

		$this->add_control(
			'input_background_color',
			[
				'label' => Wp_Helper::__('Background Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} input[name=email]' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'label' => Wp_Helper::__('Border', 'elementor'),
				'selector' => '{{WRAPPER}} input[name=email]',
			]
		);

		$this->add_control(
			'input_border_radius',
			[
				'label' => Wp_Helper::__('Border Radius', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} input[name=email]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'input_padding',
			[
				'label' => Wp_Helper::__('Text Padding', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} input[name=email]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'input_margin',
			[
				'label' => Wp_Helper::__('Margin', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} input[name=email]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button_style',
			[
				'label' => Wp_Helper::__('Button', 'elementor'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => Wp_Helper::__('Text Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_width',
			[
				'label' => Wp_Helper::__('Width', 'elementor'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', '%'],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 1600,
					],
				],
				'responsive' => true,
				'selectors' => [
					'{{WRAPPER}} button' => 'max-width: 100%; width: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->add_control(
			'button_height',
			[
				'label' => Wp_Helper::__('Height', 'elementor'),
				'type' => Controls_Manager::SLIDER,
				'size_units' => ['px', 'em', 'rem'],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 200,
					],
				],
				'separator' => '',
				'selectors' => [
					'{{WRAPPER}} button' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'label' => Wp_Helper::__('Typography', 'elementor'),
				'selector' => '{{WRAPPER}} button',
			]
		);

		$this->add_control(
			'button_background_color',
			[
				'label' => Wp_Helper::__('Background Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'label' => Wp_Helper::__('Border', 'elementor'),
				'selector' => '{{WRAPPER}} button',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => Wp_Helper::__('Border Radius', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_padding',
			[
				'label' => Wp_Helper::__('Text Padding', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'button_margin',
			[
				'label' => Wp_Helper::__('Margin', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_button_hover',
			[
				'label' => Wp_Helper::__('Button Hover', 'elementor'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'button_hover_color',
			[
				'label' => Wp_Helper::__('Text Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_background_hover_color',
			[
				'label' => Wp_Helper::__('Background Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => Wp_Helper::__('Border Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => Wp_Helper::__('Animation', 'elementor'),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_psgdpr_style',
			[
				'label' => Wp_Helper::__('Psgdpr', 'elementor'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		
		$this->add_responsive_control(
			'psgdpr_text_align',
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
				'selectors' => [
					'{{WRAPPER}} .elementor_psgdpr_consent_message' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'psgdpr_text_color',
			[
				'label' => Wp_Helper::__('Text Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .psgdpr_consent_message' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'psgdpr_typography',
				'label' => Wp_Helper::__('Typography', 'elementor'),
				'selector' => '{{WRAPPER}} .psgdpr_consent_message',
			]
		);
		
        $this->add_control(
            'checkbox_spacing',
            [
                'label' => Wp_Helper::__('Checkbox Spacing', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .custom-checkbox input + span' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

		$this->add_responsive_control(
			'psgdpr_margin',
			[
				'label' => Wp_Helper::__('Margin', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .psgdpr_consent_message' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
		
        $this->start_controls_section(
            'section_alert_style',
            [
                'label' => Wp_Helper::__('Alert', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
		
		$this->add_responsive_control(
			'alert_text_align',
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
				'selectors' => [
					'{{WRAPPER}} .alert' => 'text-align: {{VALUE}};',
				],
			]
		);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'alert_typography',
                'selector' => '{{WRAPPER}} .alert',
            ]
        );

        $this->add_control(
            'heading_style_success',
            [
                'type' => Controls_Manager::HEADING,
                'label' => Wp_Helper::__('Success', 'elementor'),
            ]
        );

        $this->add_control(
            'success_alert_color',
            [
                'label' => Wp_Helper::__('Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'separator' => '',
                'selectors' => [
                    '{{WRAPPER}} .alert.alert-success' => 'color: {{COLOR}};',
                ],
            ]
        );

        $this->add_control(
            'heading_style_error',
            [
                'type' => Controls_Manager::HEADING,
                'label' => Wp_Helper::__('Error', 'elementor'),
            ]
        );

        $this->add_control(
            'error_alert_color',
            [
                'label' => Wp_Helper::__('Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'separator' => '',
                'selectors' => [
                    '{{WRAPPER}} .alert.alert-danger' => 'color: {{COLOR}};',
                ],
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
				
		if( \Module::isEnabled( 'ps_emailsubscription' ) ) {
			$module = \Module::getInstanceByName( 'ps_emailsubscription' );
			
			$vars = $module->getWidgetVariables();
			
			$vars['settings'] = $this->get_settings_for_display();
			$vars['id_module'] = $module->id;
			
			if ( ! empty( $vars['settings']['icon']['value'] ) ) {
				ob_start();
					Icons_Manager::render_icon( $vars['settings']['icon'], [ 'aria-hidden' => 'true' ] );
				$vars['settings']['icon'] = ob_get_clean();
			}else{
				$vars['settings']['icon'] = '';
			}
						
			echo $this->fetch('module:axoncreator/views/templates/widgets/subscription.tpl', $vars);
		}
	}
}