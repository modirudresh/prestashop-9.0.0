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
class Widget_Axps_Contact extends Widget_Base {

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
		return 'axps-contact';
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
		return Wp_Helper::__( 'Contact Form', 'elementor' );
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
		return 'eicon-form-horizontal';
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
		return [ 'email', 'contact' ];
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
		
		$col_width = [
			'100' => '100%',
			'80' => '80%',
			'75' => '75%',
			'66' => '66%',
			'60' => '60%',
			'50' => '50%',
			'40' => '40%',
			'33' => '33%',
			'25' => '25%',
			'20' => '20%',
		];
		
		$context = \Context::getContext();
		
        $contacts = \Contact::getContacts($context->language->id);
		
        $opts = [
            '0' => Wp_Helper::__('Select', 'elementor'),
        ];
		
        foreach ( $contacts as $contact ) {
            $opts[$contact['id_contact']] = $contact['name'];
        }
		
		$this->start_controls_section(
            'section_form_content',
            [
                'label' => Wp_Helper::__('Form Fields', 'elementor'),
            ]
        );

        $this->add_control(
            'subject_id',
            [
                'label' => Wp_Helper::__('Subject Heading', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => $opts,
                'default' => '0',
            ]
        );

        $this->add_control(
            'show_upload',
            [
                'label' => Wp_Helper::__('Attach File', 'elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => Wp_Helper::__('Hide', 'elementor'),
                'label_on' => Wp_Helper::__('Show', 'elementor'),
            ]
        );

        $this->add_control(
            'show_labels',
            [
                'label' => Wp_Helper::__('Label', 'elementor'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_off' => Wp_Helper::__('Hide', 'elementor'),
                'label_on' => Wp_Helper::__('Show', 'elementor'),
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
            'heading_subject_content',
            [
                'label' => Wp_Helper::__('Subject Heading', 'elementor'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'subject_id' => '0',
                ],
            ]
        );

        $this->add_responsive_control(
            'subject_width',
            [
                'label' => Wp_Helper::__('Column Width', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => $col_width,
                'default' => '100',
                'condition' => [
					'subject_id' => '0',
                ],
            ]
        );
		
        $this->add_control(
            'heading_email_content',
            [
                'label' => Wp_Helper::__('Email address', 'elementor'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'email_width',
            [
                'label' => Wp_Helper::__('Column Width', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => $col_width,
                'default' => '100',
            ]
        );

        $this->add_control(
            'heading_upload_content',
            [
                'label' => Wp_Helper::__('Attach File', 'elementor'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_upload' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'upload_width',
            [
                'label' => Wp_Helper::__('Column Width', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => $col_width,
                'default' => '100',
                'condition' => [
					'show_upload' => 'yes',
                    'show_labels' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'heading_message_content',
            [
                'label' => Wp_Helper::__('Message', 'elementor'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'message_width',
            [
                'label' => Wp_Helper::__('Column Width', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => $col_width,
                'default' => '100',
            ]
        );

        $this->add_control(
            'message_rows',
            [
                'label' => Wp_Helper::__('Rows', 'elementor'),
                'type' => Controls_Manager::NUMBER,
                'default' => '4',
            ]
        );

        $this->add_control(
            'heading_button_content',
            [
                'label' => Wp_Helper::__('Button', 'elementor'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'button_width',
            [
                'label' => Wp_Helper::__('Column Width', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'options' => $col_width,
                'default' => '100',
            ]
        );

        $this->add_responsive_control(
            'button_align',
            [
                'label' => Wp_Helper::__('Alignment', 'elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => Wp_Helper::__('Left', 'elementor'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => Wp_Helper::__('Center', 'elementor'),
                        'icon' => 'fa fa-align-center',
                    ],
                    'end' => [
                        'title' => Wp_Helper::__('Right', 'elementor'),
                        'icon' => 'fa fa-align-right',
                    ],
                    'stretch' => [
                        'title' => Wp_Helper::__('Justified', 'elementor'),
                        'icon' => 'fa fa-align-justify',
                    ],
                ],
                'default' => 'stretch',
                'prefix_class' => 'elementor%s-button-align-',
            ]
        );

        $this->add_control(
            'icon',
            [
                'label' => Wp_Helper::__('Icon', 'elementor'),
				'type' => Controls_Manager::ICONS,
				'separator' => 'before',
            ]
        );

        $this->add_control(
            'icon_align',
            [
                'label' => Wp_Helper::__('Icon Position', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => Wp_Helper::__('Before', 'elementor'),
                    'right' => Wp_Helper::__('After', 'elementor'),
                ],
                'separator' => '',
                'condition' => [
                    'icon!' => '',
                ],
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
                'separator' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'icon!' => '',
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
            'section_style_form',
            [
                'label' => Wp_Helper::__('Form', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'column_gap',
            [
                'label' => Wp_Helper::__('Columns Gap', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-group' => 'padding-right: calc({{SIZE}}{{UNIT}} / 2); padding-left: calc({{SIZE}}{{UNIT}} / 2);',
                    '{{WRAPPER}} .elementor-form-fields-wrapper' => 'margin-left: calc(-{{SIZE}}{{UNIT}} / 2); margin-right: calc(-{{SIZE}}{{UNIT}} / 2);',
                ],
            ]
        );

        $this->add_control(
            'row_gap',
            [
                'label' => Wp_Helper::__('Rows Gap', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'separator' => '',
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-group, {{WRAPPER}} .elementor-field-group .alert' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-form-fields-wrapper' => 'margin-bottom: -{{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_style_label',
            [
                'type' => Controls_Manager::HEADING,
                'label' => Wp_Helper::__('Label', 'elementor'),
                'separator' => 'before',
                'condition' => [
                    'show_labels!' => '',
                ],
            ]
        );

        $this->add_control(
            'label_spacing',
            [
                'label' => Wp_Helper::__('Spacing', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 60,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-group > label' => 'padding-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_labels!' => '',
                ],
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => Wp_Helper::__('Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-group label' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_labels!' => '',
                ],
           ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .elementor-field-group label',
                'condition' => [
                    'show_labels!' => '',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_field_style',
            [
                'label' => Wp_Helper::__('Field', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
		
		$this->add_control(
			'field_height',
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
					'{{WRAPPER}} .elementor-field-group .elementor-field:not([type=file]):not(textarea)' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'field_typography',
                'selector' => '{{WRAPPER}} .elementor-field-group .elementor-field',
            ]
        );

        $this->add_control(
            'field_text_color',
            [
                'label' => Wp_Helper::__('Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-group .elementor-field' => 'color: {{VALUE}};',
                ]
            ]
        );

        $this->add_control(
            'field_background_color',
            [
                'label' => Wp_Helper::__('Background Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'separator' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-group .elementor-field:not([type=file])' => 'background-color: {{VALUE}};',
                ],
            ]
        );
		
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'input_border',
				'label' => Wp_Helper::__('Border', 'elementor'),
				'selector' => '{{WRAPPER}} .elementor-field-group .elementor-field:not([type=file])',
			]
		);

        $this->add_control(
            'field_border_radius',
            [
                'label' => Wp_Helper::__('Border Radius', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'separator' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-field-group .elementor-field:not([type=file])' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
		
		$this->add_control(
			'field_padding',
			[
				'label' => Wp_Helper::__('Text Padding', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .elementor-field-group .elementor-field:not([type=file]):not(textarea)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .elementor-field-group textarea.elementor-field' => 'padding: {{LEFT}}{{UNIT}} {{RIGHT}}{{UNIT}} {{LEFT}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .elementor-button',
            ]
        );

        $this->start_controls_tabs('tabs_button_style');

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => Wp_Helper::__('Normal', 'elementor'),
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
					'{{WRAPPER}} .elementor-button' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
            'button_text_color',
            [
                'label' => Wp_Helper::__('Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => Wp_Helper::__('Background Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'separator' => '',
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );
		
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'label' => Wp_Helper::__('Border', 'elementor'),
				'selector' => '{{WRAPPER}} .elementor-field-group .elementor-field:not([type=file])',
			]
		);

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => Wp_Helper::__('Hover', 'elementor'),
            ]
        );

        $this->add_control(
            'button_hover_color',
            [
                'label' => Wp_Helper::__('Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_background_hover_color',
            [
                'label' => Wp_Helper::__('Background Color', 'elementor'),
                'separator' => '',
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label' => Wp_Helper::__('Border Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'separator' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_radius',
            [
                'label' => Wp_Helper::__('Border Radius', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'separator' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button' => 'border-radius: {{SIZE}}{{UNIT}};',
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
					'{{WRAPPER}} .elementor-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
            'button_hover_animation',
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
		
		if( \Module::isEnabled( 'contactform' ) ) {
			$module = \Module::getInstanceByName( 'contactform' );
			
			$vars = $module->getWidgetVariables();
			
			$vars['settings'] = $this->get_settings_for_display();
            $vars['id_widget'] = $this->get_id();
            
			if ( ! empty( $vars['settings']['icon']['value'] ) ) {
				ob_start();
					Icons_Manager::render_icon( $vars['settings']['icon'], [ 'aria-hidden' => 'true' ] );
				$vars['settings']['icon'] = ob_get_clean();
			}else{
				$vars['settings']['icon'] = '';
			}

            echo $this->fetch('module:axoncreator/views/templates/widgets/contact.tpl', $vars);
		}
	}
}