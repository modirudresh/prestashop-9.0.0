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
class Widget_Animated_Headline extends Widget_Base {
	
	public function get_name() {
		return 'animated-headline';
	}

	public function get_title() {
		return Wp_Helper::__( 'Animated Headline', 'elementor' );
	}
	
	public function get_categories() {
		return [ 'axon-elements' ];
	}

	public function get_icon() {
		return 'eicon-animated-headline';
	}

	public function get_keywords() {
		return [ 'headline', 'heading', 'animation', 'title', 'text' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'text_elements',
			[
				'label' => Wp_Helper::__( 'Headline', 'elementor' ),
			]
		);

		$this->add_control(
			'headline_style',
			[
				'label' => Wp_Helper::__( 'Style', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'highlight',
				'options' => [
					'highlight' => Wp_Helper::__( 'Highlighted', 'elementor' ),
					'rotate' => Wp_Helper::__( 'Rotating', 'elementor' ),
				],
				'prefix_class' => 'elementor-headline--style-',
				'render_type' => 'template',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'animation_type',
			[
				'label' => Wp_Helper::__( 'Animation', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'typing' => 'Typing',
					'clip' => 'Clip',
					'flip' => 'Flip',
					'swirl' => 'Swirl',
					'blinds' => 'Blinds',
					'drop-in' => 'Drop-in',
					'wave' => 'Wave',
					'slide' => 'Slide',
					'slide-down' => 'Slide Down',
				],
				'default' => 'typing',
				'condition' => [
					'headline_style' => 'rotate',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'marker',
			[
				'label' => Wp_Helper::__( 'Shape', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'circle',
				'options' => [
					'circle' => Wp_Helper::_x( 'Circle', 'Shapes', 'elementor' ),
					'curly' => Wp_Helper::_x( 'Curly', 'Shapes', 'elementor' ),
					'underline' => Wp_Helper::_x( 'Underline', 'Shapes', 'elementor' ),
					'double' => Wp_Helper::_x( 'Double', 'Shapes', 'elementor' ),
					'double_underline' => Wp_Helper::_x( 'Double Underline', 'Shapes', 'elementor' ),
					'underline_zigzag' => Wp_Helper::_x( 'Underline Zigzag', 'Shapes', 'elementor' ),
					'diagonal' => Wp_Helper::_x( 'Diagonal', 'Shapes', 'elementor' ),
					'strikethrough' => Wp_Helper::_x( 'Strikethrough', 'Shapes', 'elementor' ),
					'x' => 'X',
				],
				'render_type' => 'template',
				'condition' => [
					'headline_style' => 'highlight',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'before_text',
			[
				'label' => Wp_Helper::__( 'Before Text', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'This page is', 'elementor' ),
				'placeholder' => Wp_Helper::__( 'Enter your headline', 'elementor' ),
				'label_block' => true,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'highlighted_text',
			[
				'label' => Wp_Helper::__( 'Highlighted Text', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::__( 'Amazing', 'elementor' ),
				'label_block' => true,
				'condition' => [
					'headline_style' => 'highlight',
				],
				'separator' => 'none',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'rotating_text',
			[
				'label' => Wp_Helper::__( 'Rotating Text', 'elementor' ),
				'type' => Controls_Manager::TEXTAREA,
				'placeholder' => Wp_Helper::__( 'Enter each word in a separate line', 'elementor' ),
				'separator' => 'none',
				'default' => "Better\nBigger\nFaster",
				'condition' => [
					'headline_style' => 'rotate',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'after_text',
			[
				'label' => Wp_Helper::__( 'After Text', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => Wp_Helper::__( 'Enter your headline', 'elementor' ),
				'label_block' => true,
				'separator' => 'none',
			]
		);

		$this->add_control(
			'loop',
			[
				'label' => Wp_Helper::__( 'Infinite Loop', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'render_type' => 'template',
				'frontend_available' => true,
				'selectors' => [
					'{{WRAPPER}}' => '--iteration-count: infinite',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'highlight_animation_duration',
			[
				'label' => Wp_Helper::__( 'Duration', 'elementor' ) . ' (ms)',
				'type' => Controls_Manager::NUMBER,
				'default' => 1200,
				'render_type' => 'template',
				'frontend_available' => true,
				'selectors' => [
					'{{WRAPPER}}' => '--animation-duration: {{VALUE}}ms',
				],
				'condition' => [
					'headline_style' => 'highlight',
				],
			]
		);

		$this->add_control(
			'highlight_iteration_delay',
			[
				'label' => Wp_Helper::__( 'Delay', 'elementor' ) . ' (ms)',
				'type' => Controls_Manager::NUMBER,
				'default' => 8000,
				'render_type' => 'template',
				'frontend_available' => true,
				'condition' => [
					'headline_style' => 'highlight',
					'loop' => 'yes',
				],
			]
		);

		$this->add_control(
			'rotate_iteration_delay',
			[
				'label' => Wp_Helper::__( 'Duration', 'elementor' ) . ' (ms)',
				'type' => Controls_Manager::NUMBER,
				'default' => 2500,
				'render_type' => 'template',
				'frontend_available' => true,
				'condition' => [
					'headline_style' => 'rotate',
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label' => Wp_Helper::__( 'Link', 'elementor' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'separator' => 'before',
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
					'{{WRAPPER}} .elementor-headline' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'tag',
			[
				'label' => Wp_Helper::__( 'HTML Tag', 'elementor' ),
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
					'p' => 'p',
				],
				'default' => 'h3',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_marker',
			[
				'label' => Wp_Helper::__( 'Shape', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'headline_style' => 'highlight',
				],
			]
		);

		$this->add_control(
			'marker_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-headline-dynamic-wrapper path' => 'stroke: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'stroke_width',
			[
				'label' => Wp_Helper::__( 'Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 20,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-headline-dynamic-wrapper path' => 'stroke-width: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->add_control(
			'above_content',
			[
				'label' => Wp_Helper::__( 'Bring to Front', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'selectors' => [
					'{{WRAPPER}} .elementor-headline-dynamic-wrapper svg' => 'z-index: 2',
					'{{WRAPPER}} .elementor-headline-dynamic-text' => 'z-index: auto',
				],
			]
		);

		$this->add_control(
			'rounded_edges',
			[
				'label' => Wp_Helper::__( 'Rounded Edges', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'selectors' => [
					'{{WRAPPER}} .elementor-headline-dynamic-wrapper path' => 'stroke-linecap: round; stroke-linejoin: round',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_text',
			[
				'label' => Wp_Helper::__( 'Headline', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-headline-plain-text' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .elementor-headline',
			]
		);

		$this->add_control(
			'heading_words_style',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Animated Text', 'elementor' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'words_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--dynamic-text-color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'words_typography',
				'selector' => '{{WRAPPER}} .elementor-headline-dynamic-text',
				'exclude' => [ 'font_size' ],
			]
		);

		$this->add_control(
			'typing_animation_highlight_colors',
			[
				'type' => Controls_Manager::HEADING,
				'label' => Wp_Helper::__( 'Selected Text', 'elementor' ),
				'separator' => 'before',
				'condition' => [
					'headline_style' => 'rotate',
					'animation_type' => 'typing',
				],
			]
		);

		$this->add_control(
			'highlighted_text_background_color',
			[
				'label' => Wp_Helper::__( 'Selection Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--typing-selected-bg-color: {{VALUE}}',
				],
				'condition' => [
					'headline_style' => 'rotate',
					'animation_type' => 'typing',
				],
			]
		);

		$this->add_control(
			'highlighted_text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}' => '--typing-selected-color: {{VALUE}}',
				],
				'condition' => [
					'headline_style' => 'rotate',
					'animation_type' => 'typing',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$tag = $settings['tag'];

		$this->add_render_attribute( 'headline', 'class', 'elementor-headline' );

		if ( 'rotate' === $settings['headline_style'] ) {
			$this->add_render_attribute( 'headline', 'class', 'elementor-headline-animation-type-' . $settings['animation_type'] );

			$is_letter_animation = in_array( $settings['animation_type'], [ 'typing', 'swirl', 'blinds', 'wave' ] );

			if ( $is_letter_animation ) {
				$this->add_render_attribute( 'headline', 'class', 'elementor-headline-letters' );
			}
		}

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'url', $settings['link'] );

			echo '<a ' . $this->get_render_attribute_string( 'url' ) . '>';
		}

		?>
		<<?php echo $tag; ?> <?php echo $this->get_render_attribute_string( 'headline' ); ?>>
		<?php if ( ! empty( $settings['before_text'] ) ) : ?>
			<span class="elementor-headline-plain-text elementor-headline-text-wrapper"><?php echo $settings['before_text']; ?></span>
		<?php endif; ?>
		<span class="elementor-headline-dynamic-wrapper elementor-headline-text-wrapper">
		<?php if ( 'rotate' === $settings['headline_style'] && $settings['rotating_text'] ) :
			$rotating_text = explode( "\n", $settings['rotating_text'] );
			foreach ( $rotating_text as $key => $text ) :
				$status_class = 1 > $key ? 'elementor-headline-text-active' : ''; ?>
			<span class="elementor-headline-dynamic-text <?php echo $status_class; ?>">
				<?php echo str_replace( ' ', '&nbsp;', $text ); ?>
			</span>
		<?php endforeach; ?>
		<?php elseif ( 'highlight' === $settings['headline_style'] && ! empty( $settings['highlighted_text'] ) ) : ?>
			<span class="elementor-headline-dynamic-text elementor-headline-text-active"><?php echo $settings['highlighted_text']; ?></span>
		<?php endif ?>
		</span>
		<?php if ( ! empty( $settings['after_text'] ) ) : ?>
			<span class="elementor-headline-plain-text elementor-headline-text-wrapper"><?php echo $settings['after_text']; ?></span>
			<?php endif; ?>
		</<?php echo $tag; ?>>
		<?php

		if ( ! empty( $settings['link']['url'] ) ) {
			echo '</a>';
		}
	}

	/**
	 * Render Animated Headline widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	protected function _content_template() {
		?>
		<#
		var headlineClasses = 'elementor-headline',
			tag = settings.tag;

		if ( 'rotate' === settings.headline_style ) {
			headlineClasses += ' elementor-headline-animation-type-' + settings.animation_type;

			var isLetterAnimation = -1 !== [ 'typing', 'swirl', 'blinds', 'wave' ].indexOf( settings.animation_type );

			if ( isLetterAnimation ) {
				headlineClasses += ' elementor-headline-letters';
			}
		}

		if ( settings.link.url ) { #>
			<a href="#">
		<# } #>
				<{{{ tag }}} class="{{{ headlineClasses }}}">
					<# if ( settings.before_text ) { #>
						<span class="elementor-headline-plain-text elementor-headline-text-wrapper">{{{ settings.before_text }}}</span>
					<# } #>

					<# if ( settings.rotating_text ) { #>
						<span class="elementor-headline-dynamic-wrapper elementor-headline-text-wrapper">
						<# if ( 'rotate' === settings.headline_style && settings.rotating_text ) {
							var rotatingText = ( settings.rotating_text || '' ).split( '\n' );
							for ( var i = 0; i < rotatingText.length; i++ ) {
								var statusClass = 0 === i ? 'elementor-headline-text-active' : ''; #>
								<span class="elementor-headline-dynamic-text {{ statusClass }}">
									{{{ rotatingText[ i ].replace( ' ', '&nbsp;' ) }}}
								</span>
							<# }
						}

						else if ( 'highlight' === settings.headline_style && settings.highlighted_text ) { #>
							<span class="elementor-headline-dynamic-text elementor-headline-text-active">{{ settings.highlighted_text }}</span>
						<# } #>
						</span>
					<# } #>

					<# if ( settings.after_text ) { #>
						<span class="elementor-headline-plain-text elementor-headline-text-wrapper">{{{ settings.after_text }}}</span>
					<# } #>
				</{{{ tag }}}>
		<# if ( settings.link.url ) { #>
			</a>
		<# } #>
		<?php
	}
}
