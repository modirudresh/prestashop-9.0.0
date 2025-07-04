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
class Widget_Countdown extends Widget_Base {

	public function get_name() {
		return 'countdown';
	}

	public function get_title() {
		return Wp_Helper::__( 'Countdown timer', 'elementor' );
	}

	public function get_icon() {
		return 'eicon-countdown';
	}
	
	public function get_categories() {
		return [ 'axon-elements' ];
	}

	public function get_keywords() {
		return [ 'countdown', 'number', 'timer', 'time', 'date', 'evergreen', 'axps' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_countdown',
			[
				'label' => Wp_Helper::__( 'Countdown', 'elementor' ),
			]
		);

		$this->add_control(
			'countdown_type',
			[
				'label' => Wp_Helper::__( 'Type', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'due_date' => Wp_Helper::__( 'Due Date', 'elementor' ),
					'evergreen' => Wp_Helper::__( 'Evergreen Timer', 'elementor' ),
				],
				'default' => 'due_date',
			]
		);

		$this->add_control(
			'due_date',
			[
				'label' => Wp_Helper::__( 'Due Date', 'elementor' ),
				'type' => Controls_Manager::DATE_TIME,
				'default' => gmdate( 'Y-m-d H:i', strtotime( '+1 month' ) ),
				/* translators: %s: Time zone. */
				'description' => sprintf( Wp_Helper::__( 'Date set according to your timezone: %s.', 'axiosy' ), Utils::get_timezone_string() ),
				'condition' => [
					'countdown_type' => 'due_date',
				],
			]
		);

		$this->add_control(
			'evergreen_counter_hours',
			[
				'label' => Wp_Helper::__( 'Hours', 'elementor' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 47,
				'placeholder' => Wp_Helper::__( 'Hours', 'elementor' ),
				'condition' => [
					'countdown_type' => 'evergreen',
				],
			]
		);

		$this->add_control(
			'evergreen_counter_minutes',
			[
				'label' => Wp_Helper::__( 'Minutes', 'elementor' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 59,
				'placeholder' => Wp_Helper::__( 'Minutes', 'elementor' ),
				'condition' => [
					'countdown_type' => 'evergreen',
				],
			]
		);

		$this->add_control(
			'label_display',
			[
				'label' => Wp_Helper::__( 'View', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'block' => Wp_Helper::__( 'Block', 'elementor' ),
					'inline' => Wp_Helper::__( 'Inline', 'elementor' ),
				],
				'default' => 'block',
				'prefix_class' => 'elementor-countdown--label-',
			]
		);

		$this->add_control(
			'show_days',
			[
				'label' => Wp_Helper::__( 'Days', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_hours',
			[
				'label' => Wp_Helper::__( 'Hours', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_minutes',
			[
				'label' => Wp_Helper::__( 'Minutes', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_seconds',
			[
				'label' => Wp_Helper::__( 'Seconds', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'default' => 'yes',
			]
		);

		$this->add_control(
			'show_labels',
			[
				'label' => Wp_Helper::__( 'Show Label', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'custom_labels',
			[
				'label' => Wp_Helper::__( 'Custom Label', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'show_labels!' => '',
				],
			]
		);

		$this->add_control(
			'label_days',
			[
				'label' => Wp_Helper::__( 'Days', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Days', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Days', 'elementor' ),
				'condition' => [
					'show_labels!' => '',
					'custom_labels!' => '',
					'show_days' => 'yes',
				],
			]
		);

		$this->add_control(
			'label_hours',
			[
				'label' => Wp_Helper::__( 'Hours', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Hours', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Hours', 'elementor' ),
				'condition' => [
					'show_labels!' => '',
					'custom_labels!' => '',
					'show_hours' => 'yes',
				],
			]
		);

		$this->add_control(
			'label_minutes',
			[
				'label' => Wp_Helper::__( 'Minutes', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Minutes', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Minutes', 'elementor' ),
				'condition' => [
					'show_labels!' => '',
					'custom_labels!' => '',
					'show_minutes' => 'yes',
				],
			]
		);

		$this->add_control(
			'label_seconds',
			[
				'label' => Wp_Helper::__( 'Seconds', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Seconds', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Seconds', 'elementor' ),
				'condition' => [
					'show_labels!' => '',
					'custom_labels!' => '',
					'show_seconds' => 'yes',
				],
			]
		);

		$this->add_control(
			'expire_actions',
			[
				'label' => Wp_Helper::__( 'Actions After Expire', 'elementor' ),
				'type' => Controls_Manager::SELECT2,
				'options' => [
					'redirect' => Wp_Helper::__( 'Redirect', 'elementor' ),
					'hide' => Wp_Helper::__( 'Hide', 'elementor' ),
					'message' => Wp_Helper::__( 'Show Message', 'elementor' ),
				],
				'label_block' => true,
				'separator' => 'before',
				'render_type' => 'none',
				'multiple' => true,
			]
		);

		$this->add_control(
			'message_after_expire',
			[
				'label' => Wp_Helper::__( 'Message', 'elementor' ),
				'type' => Controls_Manager::TEXTAREA,
				'separator' => 'before',
				'condition' => [
					'expire_actions' => 'message',
				],
			]
		);

		$this->add_control(
			'expire_redirect_url',
			[
				'label' => Wp_Helper::__( 'Redirect URL', 'elementor' ),
				'type' => Controls_Manager::URL,
				'separator' => 'before',
				'options' => false,
				'autocomplete' => false,
				'condition' => [
					'expire_actions' => 'redirect',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_box_style',
			[
				'label' => Wp_Helper::__( 'Boxes', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'container_width',
			[
				'label' => Wp_Helper::__( 'Container Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'size_units' => [ '%', 'px' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-countdown-wrapper' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'box_background_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-countdown-item' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'box_border',
				'selector' => '{{WRAPPER}} .elementor-countdown-item',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'box_border_radius',
			[
				'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-countdown-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'box_spacing',
			[
				'label' => Wp_Helper::__( 'Space Between', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'body:not(.rtl) {{WRAPPER}} .elementor-countdown-item:not(:first-of-type)' => 'margin-left: calc( {{SIZE}}{{UNIT}}/2 );',
					'body:not(.rtl) {{WRAPPER}} .elementor-countdown-item:not(:last-of-type)' => 'margin-right: calc( {{SIZE}}{{UNIT}}/2 );',
					'body.rtl {{WRAPPER}} .elementor-countdown-item:not(:first-of-type)' => 'margin-right: calc( {{SIZE}}{{UNIT}}/2 );',
					'body.rtl {{WRAPPER}} .elementor-countdown-item:not(:last-of-type)' => 'margin-left: calc( {{SIZE}}{{UNIT}}/2 );',
				],
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-countdown-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_style',
			[
				'label' => Wp_Helper::__( 'Content', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_digits',
			[
				'label' => Wp_Helper::__( 'Digits', 'elementor' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'digits_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-countdown-digits' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'digits_typography',
				'selector' => '{{WRAPPER}} .elementor-countdown-digits',
			]
		);

		$this->add_control(
			'heading_label',
			[
				'label' => Wp_Helper::__( 'Label', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'label_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-countdown-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '{{WRAPPER}} .elementor-countdown-label',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_expire_message_style',
			[
				'label' => Wp_Helper::__( 'Message', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'expire_actions' => 'message',
				],
			]
		);

		$this->add_responsive_control(
			'align',
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
					'{{WRAPPER}} .elementor-countdown-expire--message' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-countdown-expire--message' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} .elementor-countdown-expire--message',
			]
		);

		$this->add_responsive_control(
			'message_padding',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-countdown-expire--message' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	private function get_strftime( $instance ) {
		$string = '';
		if ( $instance['show_days'] ) {
			$string .= $this->render_countdown_item( $instance, 'label_days', 'js-time-days' );
		}
		if ( $instance['show_hours'] ) {
			$string .= $this->render_countdown_item( $instance, 'label_hours', 'js-time-hours' );
		}
		if ( $instance['show_minutes'] ) {
			$string .= $this->render_countdown_item( $instance, 'label_minutes', 'js-time-minutes' );
		}
		if ( $instance['show_seconds'] ) {
			$string .= $this->render_countdown_item( $instance, 'label_seconds', 'js-time-seconds' );
		}

		return $string;
	}

	private $_default_countdown_labels;

	private function init_default_countdown_labels() {
		$this->_default_countdown_labels = [
			'label_months' => Wp_Helper::__( 'Months', 'elementor' ),
			'label_weeks' => Wp_Helper::__( 'Weeks', 'elementor' ),
			'label_days' => Wp_Helper::__( 'Days', 'elementor' ),
			'label_hours' => Wp_Helper::__( 'Hours', 'elementor' ),
			'label_minutes' => Wp_Helper::__( 'Minutes', 'elementor' ),
			'label_seconds' => Wp_Helper::__( 'Seconds', 'elementor' ),
		];
	}

	public function get_default_countdown_labels() {
		if ( ! $this->_default_countdown_labels ) {
			$this->init_default_countdown_labels();
		}

		return $this->_default_countdown_labels;
	}

	private function render_countdown_item( $instance, $label, $part_class ) {
		$string = '<div class="elementor-countdown-item"><span class="elementor-countdown-digits ' . $part_class . '"><i class="fa fa-circle-notch fa-spin"></i></span>';

		if ( $instance['show_labels'] ) {
			$default_labels = $this->get_default_countdown_labels();
			$label = ( $instance['custom_labels'] ) ? $instance[ $label ] : $default_labels[ $label ];
			$string .= ' <span class="elementor-countdown-label">' . $label . '</span>';
		}

		$string .= '</div>';

		return $string;
	}

	private function get_evergreen_interval( $instance ) {
		$hours = empty( $instance['evergreen_counter_hours'] ) ? 0 : ( $instance['evergreen_counter_hours'] * AXON_HOUR_IN_SECONDS );
		$minutes = empty( $instance['evergreen_counter_minutes'] ) ? 0 : ( $instance['evergreen_counter_minutes'] * AXON_MINUTE_IN_SECONDS );
		$evergreen_interval = $hours + $minutes;

		return $evergreen_interval;
	}

	private function get_actions( $settings ) {
		if ( empty( $settings['expire_actions'] ) || ! is_array( $settings['expire_actions'] ) ) {
			return false;
		}

		$actions = [];

		foreach ( $settings['expire_actions'] as $action ) {
			$action_to_run = [ 'type' => $action ];
			if ( 'redirect' === $action ) {
				if ( empty( $settings['expire_redirect_url']['url'] ) ) {
					continue;
				}
				$action_to_run['redirect_url'] = $settings['expire_redirect_url']['url'];
			}
			$actions[] = $action_to_run;
		}

		return $actions;
	}

	protected function render() {
		$instance = $this->get_settings_for_display();
		$due_date = $instance['due_date'];
		$string = $this->get_strftime( $instance );

        $timezone = \Configuration::get( 'PS_TIMEZONE' );
		
		if ( 'evergreen' === $instance['countdown_type'] ) {
			
			$date = new \DateTime("now", new \DateTimeZone( $timezone ) );			
			$times = strtotime( $date->format('Y-m-d H:i:s') ) + $this->get_evergreen_interval( $instance );
			
			$due_date = date('Y-m-d H:i:s', $times);
			
		} else {
			// Handle timezone ( we need to set GMT time )
			$gmt = Wp_Helper::get_gmt_from_date( $due_date . ':00' );
			$due_date = $gmt;
		}

		$actions = false;

		if ( ! Plugin::$instance->editor->is_edit_mode() ) {
			$actions = $this->get_actions( $instance );
		}

		if ( $actions ) {
			$this->add_render_attribute( 'div', 'data-expire-actions', json_encode( $actions ) );
		}
		
		$this->add_render_attribute( 'div', [
			'class' => 'elementor-countdown-wrapper',
			'data-countdown-timer' => $due_date,
			'data-timezone' => $timezone,
		] );

		?>
		<div <?php echo $this->get_render_attribute_string( 'div' ); ?>>
			<?php echo $string; ?>
		</div>
		<?php
		if ( $actions && is_array( $actions ) ) {
			foreach ( $actions as $action ) {
				if ( 'message' !== $action['type'] ) {
					continue;
				}
				echo '<div class="elementor-countdown-expire--message">' . $instance['message_after_expire'] . '</div>';
			}
		}
	}
}