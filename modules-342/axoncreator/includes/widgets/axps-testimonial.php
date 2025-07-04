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
class Widget_Axps_Testimonial extends Widget_Base {

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
		return 'axps-testimonial';
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
		return Wp_Helper::__( 'Testimonial ( Carousel / Grid )', 'elementor' );
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
		return 'eicon-testimonial-carousel';
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
		return [ 'testimonial', 'blockquote' ];
	}

	/**
	 * Register image carousel widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->_register_content_controls();
		
		$this->_register_styling_controls();
	}
	
	protected function _register_content_controls() {
		$this->start_controls_section(
			'section_testimonial',
			[
				'label' => Wp_Helper::__( 'Testimonial', 'elementor' ),
			]
		);
		
		$repeater = new Repeater();

		$repeater->add_control(
			'testimonial_content',
			[
				'label' => Wp_Helper::__( 'Content', 'elementor' ),
				'type' => Controls_Manager::TEXTAREA,
				'rows' => '10',
				'default' => 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
			]
		);

		$repeater->add_control(
			'testimonial_image',
			[
				'label' => Wp_Helper::__( 'Choose Image', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
			]
		);

		$repeater->add_control(
			'testimonial_name',
			[
				'label' => Wp_Helper::__( 'Name', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'John Doe',
			]
		);

		$repeater->add_control(
			'testimonial_job',
			[
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => 'Designer',
			]
		);

		$repeater->add_control(
			'link',
			[
				'label' => Wp_Helper::__( 'Link', 'elementor' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'placeholder' => Wp_Helper::__( 'https://your-link.com', 'elementor' ),
			]
		);

		$this->add_control(
			'items',
			[
				'label' => Wp_Helper::__( 'Items', 'elementor' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'testimonial_content' => 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
						'testimonial_image' => [ 'url' => Utils::get_placeholder_image_src() ],
						'testimonial_name' => 'John Doe',
						'testimonial_job' => 'Designer',
						'link' => '#',
					],
					[
						'testimonial_content' => 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
						'testimonial_image' => [ 'url' => Utils::get_placeholder_image_src() ],
						'testimonial_name' => 'John Doe',
						'testimonial_job' => 'Designer',
						'link' => '#',
					],
					[
						'testimonial_content' => 'Click edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
						'testimonial_image' => [ 'url' => Utils::get_placeholder_image_src() ],
						'testimonial_name' => 'John Doe',
						'testimonial_job' => 'Designer',
						'link' => '#',
					],
				]
			]
		);
		
		$this->add_control(
			'testimonial_image_position',
			[
				'label' => Wp_Helper::__( 'Image Position', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'aside',
				'options' => [
					'aside' => Wp_Helper::__( 'Aside', 'elementor' ),
					'top' => Wp_Helper::__( 'Top', 'elementor' ),
				],
				'separator' => 'before',
				'style_transfer' => true,
			]
		);

		$this->add_control(
			'testimonial_alignment',
			[
				'label' => Wp_Helper::__( 'Alignment', 'elementor' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'center',
				'options' => [
					'left'    => [
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
				'label_block' => false,
				'style_transfer' => true,
			]
		);

		$this->add_control(
			'view',
			[
				'label' => Wp_Helper::__( 'View', 'elementor' ),
				'type' => Controls_Manager::HIDDEN,
				'default' => 'traditional',
			]
		);

		$this->end_controls_section();
		
		$this->_register_view_settings_controls();
		
	}
	
	protected function _register_styling_controls() {
		// Content.
		$this->start_controls_section(
			'section_style_testimonial_content',
			[
				'label' => Wp_Helper::__( 'Content', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'content_content_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-testimonial-content' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} .elementor-testimonial-content',
			]
		);

		$this->end_controls_section();

		// Image.
		$this->start_controls_section(
			'section_style_testimonial_image',
			[
				'label' => Wp_Helper::__( 'Image', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'testimonial_image[url]!' => '',
				],
			]
		);

		$this->add_control(
			'image_size',
			[
				'label' => Wp_Helper::__( 'Image Size', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 200,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-testimonial-wrapper .elementor-testimonial-image img' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'image_border',
				'selector' => '{{WRAPPER}} .elementor-testimonial-wrapper .elementor-testimonial-image img',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'image_border_radius',
			[
				'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-testimonial-wrapper .elementor-testimonial-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Name.
		$this->start_controls_section(
			'section_style_testimonial_name',
			[
				'label' => Wp_Helper::__( 'Name', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'name_text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_1,
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-testimonial-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'name_typography',
				'scheme' => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .elementor-testimonial-name',
			]
		);

		$this->end_controls_section();

		// Job.
		$this->start_controls_section(
			'section_style_testimonial_job',
			[
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'job_text_color',
			[
				'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_2,
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-testimonial-job' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'job_typography',
				'scheme' => Scheme_Typography::TYPOGRAPHY_2,
				'selector' => '{{WRAPPER}} .elementor-testimonial-job',
			]
		);

		$this->end_controls_section();

		$this->_register_view_styling_controls();
		
	}

	/**
	 * Render image carousel widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['items'] ) ) {
			return;
		}
		
		$wrapper = ['elementor-testimonial-wrapper'];
		
		if ( $settings['testimonial_alignment'] ) {
			$wrapper[] = 'elementor-testimonial-text-align-' . $settings['testimonial_alignment'];
		}
		
		$attrs = $this->_render_view_setting_attributes( $settings, [] , $wrapper );
		
		$i = 0;
		$y = isset( $settings['per_col'] ) ? $settings['per_col'] :  1;

		?>
        <div<?php echo $attrs['attr_class_section']; ?>>
            <div<?php echo $attrs['attr_class_wrapper']; ?><?php echo $attrs['attr_slider_options']; ?>>
				<div class="swiper-wrapper">
					<?php foreach ( $settings['items'] as $index => $item ) {
						$this->add_render_attribute( 'meta' . $index, 'class', 'elementor-testimonial-meta' );
						$this->add_render_attribute( 'meta' . $index, 'class', 'item-inner' );

						if ( $item['testimonial_image']['url'] ) {
							$this->add_render_attribute( 'meta' . $index, 'class', 'elementor-has-image' );
						}

						if ( $settings['testimonial_image_position'] ) {
							$this->add_render_attribute( 'meta' . $index, 'class', 'elementor-testimonial-image-position-' . $settings['testimonial_image_position'] );
						}

						$has_content = ! ! $item['testimonial_content'];
						$has_image = ! ! $item['testimonial_image']['url'];
						$has_name = ! ! $item['testimonial_name'];
						$has_job = ! ! $item['testimonial_job'];

						if ( ! $has_content && ! $has_image && ! $has_name && ! $has_job ) {
							return;
						}

						if ( ! empty( $item['link']['url'] ) ) {
							$this->add_render_attribute( 'link' . $index, 'href', $item['link']['url'] );

							if ( $item['link']['is_external'] ) {
								$this->add_render_attribute( 'link' . $index, 'target', '_blank' );
							}

							if ( ! empty( $item['link']['nofollow'] ) ) {
								$this->add_render_attribute( 'link' . $index, 'rel', 'nofollow' );
							}
						}
			
						if( $i%$y == 0 ) { ?>
							<div class="swiper-slide item">
						<?php }
						
						if ( $has_content ) :
							$this->add_render_attribute( 'testimonial_content' . $index, 'class', 'elementor-testimonial-content' );

							//$this->add_inline_editing_attributes( 'testimonial_content' . $index );
							?>
							<div <?php echo $this->get_render_attribute_string( 'testimonial_content' . $index ); ?>><?php echo $item['testimonial_content']; ?></div>
						<?php endif; ?>

						<?php if ( $has_image || $has_name || $has_job ) : ?>
						<div <?php echo $this->get_render_attribute_string( 'meta' . $index ); ?>>
							<div class="elementor-testimonial-meta-inner">
								<?php if ( $has_image ) : ?>
									<div class="elementor-testimonial-image">
										<?php
										$image_html = Group_Control_Image_Size::get_attachment_image_html( $item, 'testimonial_image' );
										if ( ! empty( $item['link']['url'] ) ) :
											$image_html = '<a ' . $this->get_render_attribute_string( 'link' . $index ) . '>' . $image_html . '</a>';
										endif;
										echo $image_html;
										?>
									</div>
								<?php endif; ?>

								<?php if ( $has_name || $has_job ) : ?>
								<div class="elementor-testimonial-details">
									<?php
									if ( $has_name ) :
										$this->add_render_attribute( 'testimonial_name' . $index, 'class', 'elementor-testimonial-name' );

										//$this->add_inline_editing_attributes( 'testimonial_name' . $index, 'none' );

										$testimonial_name_html = $item['testimonial_name'];
										if ( ! empty( $item['link']['url'] ) ) :
											$testimonial_name_html = '<a ' . $this->get_render_attribute_string( 'link' . $index ) . '>' . $testimonial_name_html . '</a>';
										endif;
										?>
										<div <?php echo $this->get_render_attribute_string( 'testimonial_name' . $index ); ?>><?php echo $testimonial_name_html; ?></div>
									<?php endif; ?>
									<?php
									if ( $has_job ) :
										$this->add_render_attribute( 'testimonial_job' . $index, 'class', 'elementor-testimonial-job' );

										//$this->add_inline_editing_attributes( 'testimonial_job' . $index, 'none' );

										$testimonial_job_html = $item['testimonial_job'];
										if ( ! empty( $item['link']['url'] ) ) :
											$testimonial_job_html = '<a ' . $this->get_render_attribute_string( 'link' . $index ) . '>' . $testimonial_job_html . '</a>';
										endif;
										?>
										<div <?php echo $this->get_render_attribute_string( 'testimonial_job' . $index ); ?>><?php echo $testimonial_job_html; ?></div>
									<?php endif; ?>
								</div>
								<?php endif; ?>
							</div>
						</div>
						<?php endif; 
						
						$i++;

						if( ( $i%$y == 0 || $i == count( $settings['items'] ) ) ) { ?>
						</div>
						<?php } ?>
								
					<?php } ?>
				</div>
            </div>
			<?php if( $settings['view_type'] == 'carousel' ){ ?>
				<div class="swiper-arrows">
					<button class="axps-swiper-arrow axps-swiper-arrow-prev">
						<?php
							if ( ! empty( $settings['arrow_prev_icon']['value'] ) ) { 
								Icons_Manager::render_icon( $settings['arrow_prev_icon'], [ 'aria-hidden' => 'true' ] );
							}
						?>
					</button>
					<button class="axps-swiper-arrow axps-swiper-arrow-next">
						<?php
							if ( ! empty( $settings['arrow_next_icon']['value'] ) ) { 
								Icons_Manager::render_icon( $settings['arrow_next_icon'], [ 'aria-hidden' => 'true' ] );
							}
						?>
					</button>
				</div>
				<div class="swiper-dots">
					<div class="axps-swiper-pagination"></div>
				</div>
			<?php } ?>
        </div>
		<?php
	}

	/**
	 * Retrieve image carousel link URL.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $attachment
	 * @param object $instance
	 *
	 * @return array|string|false An array/string containing the attachment URL, or false if no link.
	 */
	private function get_link_url( $image, $item ) {
		if ( 'none' === $item['link_to'] ) {
			return false;
		}

		if ( 'custom' === $item['link_to'] ) {
			if ( empty( $item['link']['url'] ) ) {
				return false;
			}

			return $item['link'];
		}

		return [
			'url' =>  $image['url'] ,
		];
	}
	
	protected function _render_view_setting_attributes( $settings, $attr_class_section = [], $attr_class_wrapper = [] ) {
				
		$attr_class_wrapper[] = 'wrapper-items';
				
		if( $settings['view_type'] == 'carousel' ){
			
			$attr_class_section[] = 'axps-swiper-slider';	
		
			$attr_class_wrapper[] = 'wrapper-swiper-slider swiper-container';

			$slidesToShow = abs( (int)$settings['per_line'] );
			$slidesToShowTablet = abs( (int)$settings['per_line_tablet'] );
			$slidesToShowMobile	= abs( (int)$settings['per_line_mobile'] );

			$slidesToScroll = abs( (int)$settings['scroll'] );
			$slidesToScrollTablet = abs( (int)$settings['scroll_tablet'] );
			$slidesToScrollMobile = abs( (int)$settings['scroll_mobile'] );

			$attr_class_wrapper[] = 'items-xs-' . $slidesToShowMobile;
			$attr_class_wrapper[] = 'items-md-' . $slidesToShowTablet;
			$attr_class_wrapper[] = 'items-lg-' . $slidesToShow;

			if( in_array( $settings['navigation'], ['arrows', 'both'] ) ){
				$attr_class_section[] = 'swiper-arrows-on';
				$attr_class_section[] = 'swiper-arrows-' . $settings['arrows_position'];
				$attr_class_section[] = 'swiper-arrows-show-' . $settings['arrows_show'];
			}

			if( in_array( $settings['navigation_tablet'], ['arrows', 'both'] ) ){
				$attr_class_section[] = 'swiper-arrows-md-on';
				$attr_class_section[] = 'swiper-arrows-md-' . $settings['arrows_position_tablet'];
				$attr_class_section[] = 'swiper-arrows-show-md-' . $settings['arrows_show_tablet'];
			}

			if( in_array( $settings['navigation_mobile'], ['arrows', 'both'] ) ){
				$attr_class_section[] = 'swiper-arrows-xs-on';
				$attr_class_section[] = 'swiper-arrows-xs-' . $settings['arrows_position_mobile'];
				$attr_class_section[] = 'swiper-arrows-show-xs-' . $settings['arrows_show_mobile'];
			}

			if( in_array( $settings['navigation'], ['dots', 'both'] ) ){
				$attr_class_section[] = 'swiper-dots-on';
				$attr_class_section[] = 'swiper-dots-' . $settings['dots_position'];
			}

			if( in_array( $settings['navigation_tablet'], ['dots', 'both'] ) ){
				$attr_class_section[] = 'swiper-dots-md-on';
				$attr_class_section[] = 'swiper-dots-md-' . $settings['dots_position_tablet'];
			}

			if( in_array( $settings['navigation_mobile'], ['dots', 'both'] ) ){
				$attr_class_section[] = 'swiper-dots-xs-on';
				$attr_class_section[] = 'swiper-dots-xs-' . $settings['dots_position_mobile'];
			}

			$attr_class_wrapper[] = 'is-carousel';

			$slider_options = array(
				'slidesToShow' => $slidesToShow,
				'slidesToShowTablet' => $slidesToShowTablet,
				'slidesToShowMobile' => $slidesToShowMobile,
				'slidesToScroll' => $slidesToScroll,
				'slidesToScrollTablet' => $slidesToScrollTablet,
				'slidesToScrollMobile' => $slidesToScrollMobile,
				'autoplaySpeed' => abs( (int)$settings['auto_speed'] ),
				'autoplay' => $settings['auto'] ? true : false,
				'infinite' => $settings['infinite'] ? true : false,
				'pauseOnHover' => $settings['pause'] ? true : false,
				'speed' => abs( (int)$settings['speed'] ),
				'arrowPrevIcon' => $settings['arrow_prev_icon'],
				'arrowNextIcon' => $settings['arrow_next_icon'],
			);
			
			$attr_slider_options = sprintf( ' %1$s="%2$s"', 'data-slider-options', Wp_Helper::esc_attr( json_encode( $slider_options ) ) );

		}else{
			$attr_class_section[] = 'axps-grid-items';
		}
				
		return [
			'attr_class_section' => sprintf( ' %1$s="%2$s"', 'class', implode(' ', $attr_class_section) ),
			'attr_class_wrapper' => sprintf( ' %1$s="%2$s"', 'class', implode(' ', $attr_class_wrapper) ),
			'attr_slider_options' => isset( $attr_slider_options ) ? $attr_slider_options : ''
		];
		
	}
	
	protected function _register_view_settings_controls() {
		
		$this->start_controls_section(
			'section_view_settings',
			[
				'label' => Wp_Helper::__( 'View Settings', 'elementor' ),
			]
		);
		
		$this->add_control(
			'view_type',
			[
				'label'   => Wp_Helper::__( 'View type', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'carousel',
				'options' => [
					'carousel' => Wp_Helper::__( 'Carousel', 'elementor' ),
					'grid'     => Wp_Helper::__( 'Grid', 'elementor' ),
				],
			]
		);
		
		$this->add_responsive_control(
			'per_line',
			[
				'label'       => Wp_Helper::__( 'Number of items per line', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 1,
				'tablet_default' => 1,
				'mobile_default' => 1,
				'options'     => [ 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10 ],
				'selectors' => [
					'{{WRAPPER}} .wrapper-items:not(.swiper-container-initialized) .item' => '-ms-flex: 0 0 calc(100%/{{VALUE}}); flex: 0 0 calc(100%/{{VALUE}}); max-width: calc(100%/{{VALUE}});'
				],
				'render_type' => 'template',
			]
		);
		
		$this->add_control(
			'per_col',
			[
				'label'       => Wp_Helper::__( 'Number of items per column', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 1,
				'options'     => [ 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10 ],
				'condition'   => [
					'view_type' => 'carousel',
				],
			]
		);
		
		$this->add_responsive_control(
			'spacing',
			[
				'label'       => Wp_Helper::__( 'Items Spacing', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 30,
				'tablet_default' => 20,
				'mobile_default' => 10,
				'options'     => [ 0 => 0, 5 => 5, 10 => 10, 15 => 15, 20 => 20, 25 => 25, 30 => 30, 35 => 35, 40 => 40, 45 => 45, 50 => 50 ],
				'selectors' => [
					'{{WRAPPER}} .wrapper-items .swiper-slide' => 'padding-left: calc({{VALUE}}px/2);padding-right: calc({{VALUE}}px/2);',
					'{{WRAPPER}} .wrapper-items .swiper-slide .item-inner' => 'margin-bottom: {{VALUE}}px;',
					'{{WRAPPER}} .wrapper-items' => 'margin-left: calc(-{{VALUE}}px/2);margin-right: calc(-{{VALUE}}px/2);'
				],
			]
		);
				
		$this->add_responsive_control(
			'scroll',
			[
				'label'       => Wp_Helper::__( 'Slides to Scroll', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 1,
				'tablet_default' => 1,
				'mobile_default' => 1,
				'options'     => [ 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10 ],
				'condition'   => [
					'view_type' => 'carousel',
				],
			]
		);
		
		$this->add_responsive_control(
			'navigation',
			[
				'label'       => Wp_Helper::__( 'Navigation', 'elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'both',
				'tablet_default' => 'dots',
				'mobile_default' => 'dots',
				'options'     => [
					'both' => Wp_Helper::__('Arrows and Dots', 'elementor'),
					'arrows' => Wp_Helper::__('Arrows', 'elementor'),
					'dots' => Wp_Helper::__('Dots', 'elementor'),
					'none' => Wp_Helper::__('None', 'elementor'),
				],
				'condition'   => [
					'view_type' => 'carousel',
				],
			]
		);
				
		$this->add_control(
			'arrow_prev_icon',
			[
				'label' => Wp_Helper::__( 'Arrow Left Icon', 'elementor' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default' => [
					'value' => 'la la-angle-left',
					'library' => 'line-awesome',
				],
				'condition'   => [
					'navigation' => ['both', 'arrows' ],
					'view_type' => 'carousel',
				],
			]
		);
		
		$this->add_control(
			'arrow_next_icon',
			[
				'label' => Wp_Helper::__( 'Arrow Right Icon', 'elementor' ),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'default' => [
					'value' => 'la la-angle-right',
					'library' => 'line-awesome',
				],
				'condition'   => [
					'navigation' => ['both', 'arrows' ],
					'view_type' => 'carousel',
				],
			]
		);
								
		$this->add_control(
			'auto',
			[
				'label'        => Wp_Helper::__( 'Autoplay', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => Wp_Helper::__( 'Yes', 'elementor' ),
				'label_off'    => Wp_Helper::__( 'No', 'elementor' ),
				'condition'   => [
					'view_type' => 'carousel',
				],
			]
		);
		
		$this->add_control(
			'auto_speed',
			[
				'label'       => Wp_Helper::__( 'Autoplay Speed', 'elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'	  => '5000',	
				'condition'   => [
					'auto'    => 'yes',
					'view_type' => 'carousel',
				],
			]
		);
		
		$this->add_control(
			'pause',
			[
				'label'        => Wp_Helper::__( 'Pause on Hover', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'label_on'     => Wp_Helper::__( 'Yes', 'elementor' ),
				'label_off'    => Wp_Helper::__( 'No', 'elementor' ),
				'condition'   => [
					'auto' => 'yes',
					'view_type' => 'carousel',
				],
			]
		);
		
		$this->add_control(
			'speed',
			[
				'label'       => Wp_Helper::__( 'Animation Speed', 'elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'default'	  => '300',	
				'condition'   => [
					'view_type' => 'carousel',
				],
			]
		);
		
		$this->add_control(
			'infinite',
			[
				'label'        => Wp_Helper::__( 'Loop', 'elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => Wp_Helper::__( 'Yes', 'elementor' ),
				'label_off'    => Wp_Helper::__( 'No', 'elementor' ),
				'condition'   => [
					'view_type' => 'carousel',
				],
			]
		);
		
		$this->end_controls_section();
	}
	
	protected function _register_view_styling_controls() {
		$this->start_controls_section(
			'section_arrows_style',
			[
				'label' => Wp_Helper::__( 'Carousel Arrows', 'elementor' ),
				'type' => Controls_Manager::SECTION,
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'view_type' => 'carousel',
				],
			]
		);
		
			$this->add_responsive_control(
				'arrows_show',
				[
					'label'       => Wp_Helper::__( 'Arrows Show', 'elementor' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'always',
					'tablet_default' => 'always',
					'mobile_default' => 'always',
					'options'     => [
						'always' => Wp_Helper::__('Always', 'elementor'),
						'hover' => Wp_Helper::__('Section hover on show', 'elementor'),
					],
				]
			);
		
			$this->add_responsive_control(
				'arrows_position',
				[
					'label'       => Wp_Helper::__( 'Arrows Position', 'elementor' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'middle',
					'tablet_default' => 'middle',
					'mobile_default' => 'middle',
					'options'     => [
						'middle' => Wp_Helper::__('In Middle', 'elementor'),
						'top-right' => Wp_Helper::__('Top Right', 'elementor'),
						'top-left' => Wp_Helper::__('Top Left', 'elementor'),
						'top-center' => Wp_Helper::__('Top Center', 'elementor'),
						'bottom-right' => Wp_Helper::__('Bottom Right', 'elementor'),
						'bottom-left' => Wp_Helper::__('Bottom Left', 'elementor'),
						'bottom-center' => Wp_Helper::__('Bottom Center', 'elementor')
					],
				]
			);
		
			$this->add_responsive_control(
				'arrows_width',
				[
					'label' => Wp_Helper::__( 'Arrows width', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow' => 'width: {{SIZE}}{{UNIT}}'
					],
				]
			);	
		
			$this->add_responsive_control(
				'arrows_height',
				[
					'label' => Wp_Helper::__( 'Arrows height', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow' => 'height: {{SIZE}}{{UNIT}}'
					],
				]
			);	
		
			$this->add_responsive_control(
				'arrows_spacing',
				[
					'label' => Wp_Helper::__( 'Arrows Spacing', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => -600,
							'max' => 600,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider:not(.swiper-arrows-middle) .axps-swiper-arrow-prev' => 'margin-right: {{SIZE}}{{UNIT}}',
						'{{WRAPPER}} .axps-swiper-slider.swiper-arrows-middle .swiper-arrows' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}}'
					],
				]
			);	
		
			$this->add_responsive_control(
				'arrows_margin_top',
				[
					'label' => Wp_Helper::__( 'Arrows magin top', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => -300,
							'max' => 300,
						],
						'%' => [
							'min' => -100,
							'max' => 100,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .swiper-arrows' => 'margin-top: {{SIZE}}{{UNIT}}'
					],
				]
			);

			$this->add_responsive_control(
				'arrows_margin_full',
				[
					'label' => Wp_Helper::__( 'Arrows magin', 'axiosy' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px' ],
                    'allowed_dimensions' => 'horizontal',
                    'placeholder' => [
                        'top' => 'auto',
                        'right' => '',
                        'bottom' => 'auto',
                        'left' => '',
                    ],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .swiper-arrows' => 'margin-left: {{LEFT}}{{UNIT}};margin-right: {{RIGHT}}{{UNIT}};',
					],
				]
			);
		
			$this->add_responsive_control(
				'icon_size',
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
						'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow i' => 'font-size: {{SIZE}}{{UNIT}}',
						'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					],
					'separator' => 'before',
				]
			);
		
			$this->start_controls_tabs( 'arrows_tabs_style' );

				$this->start_controls_tab(
					'arrows_tab_normal',
					[
						'label' => Wp_Helper::__( 'Normal', 'elementor' ),
					]
				);

					$this->add_control(
						'arrows_color',

						[
							'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'default' => '',
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow' => 'fill: {{VALUE}}; color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'arrows_background_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow' => 'background-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'arrows_tab_hover',
					[
						'label' => Wp_Helper::__( 'Hover', 'elementor' ),
					]
				);

					$this->add_control(
						'arrows_hover_color',
						[
							'label' => Wp_Helper::__( 'Text Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow:hover' => 'fill: {{VALUE}}; color: {{VALUE}};'
							],
						]
					);

					$this->add_control(
						'arrows_background_hover_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow:hover' => 'background-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'arrows_hover_border_color',
						[
							'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow:hover' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'arrows_border',
					'selector' => '{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow',
					'separator' => 'before',
				]
			);

			$this->add_responsive_control(
				'arrows_border_radius',
				[
					'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					]
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'arrows_box_shadow',
					'selector' => '{{WRAPPER}} .axps-swiper-slider .axps-swiper-arrow',
				]
			);
		
		$this->end_controls_section();
		
		////////////////////////////DOTS/////////////////////////////////
		
		$this->start_controls_section(
			'section_dots_style',
			[
				'label' => Wp_Helper::__( 'Carousel Dots', 'elementor' ),
				'type' => Controls_Manager::SECTION,
				'tab' => Controls_Manager::TAB_STYLE,
				'condition'   => [
					'view_type' => 'carousel',
				],
			]
		);
				
			$this->add_responsive_control(
				'dots_position',
				[
					'label'       => Wp_Helper::__( 'Dots Position', 'elementor' ),
					'type'        => Controls_Manager::SELECT,
					'default'     => 'bottom-center',
					'tablet_default' => 'bottom-center',
					'mobile_default' => 'bottom-center',
					'options'     => [
						'middle' => Wp_Helper::__('In Middle', 'elementor'),
						'top-right' => Wp_Helper::__('Top Right', 'elementor'),
						'top-left' => Wp_Helper::__('Top Left', 'elementor'),
						'top-center' => Wp_Helper::__('Top Center', 'elementor'),
						'bottom-right' => Wp_Helper::__('Bottom Right', 'elementor'),
						'bottom-left' => Wp_Helper::__('Bottom Left', 'elementor'),
						'bottom-center' => Wp_Helper::__('Bottom Center', 'elementor')
					],
				]
			);
		
			$this->add_responsive_control(
				'dots_width',
				[
					'label' => Wp_Helper::__( 'Dots width', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};',
					],
				]
			);	
		
			$this->add_responsive_control(
				'dots_height',
				[
					'label' => Wp_Helper::__( 'Dots height', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 200,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}};',
					],
				]
			);	
		
			$this->add_responsive_control(
				'dots_circle',
				[
					'label' => Wp_Helper::__( 'Circle', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => 0,
							'max' => 5,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet::before' => 'left: {{SIZE}}{{UNIT}}; bottom: {{SIZE}}{{UNIT}}; top: {{SIZE}}{{UNIT}}; right: {{SIZE}}{{UNIT}};'
					],
				]
			);	
		
			$this->add_responsive_control(
				'dots_spacing',
				[
					'label' => Wp_Helper::__( 'Dots Spacing', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px' ],
					'range' => [
						'px' => [
							'min' => -300,
							'max' => 300,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet' => 'margin: {{SIZE}}{{UNIT}}'
					],
				]
			);	
		
			$this->add_responsive_control(
				'dots_margin_top',
				[
					'label' => Wp_Helper::__( 'Dots magin top', 'elementor' ),
					'type' => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%' ],
					'range' => [
						'px' => [
							'min' => -300,
							'max' => 300,
						],
						'%' => [
							'min' => -100,
							'max' => 100,
						]
					],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .swiper-dots' => 'margin-top: {{SIZE}}{{UNIT}}'
					],
				]
			);
				
			$this->start_controls_tabs( 'dots_tabs_style' );

				$this->start_controls_tab(
					'dots_tab_normal',
					[
						'label' => Wp_Helper::__( 'Normal', 'elementor' ),
					]
				);

					$this->add_control(
						'dots_background_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet' => 'background-color: {{VALUE}};',
							],
						]
					);
		
					$this->add_control(
						'dots_circle_background_color',
						[
							'label' => Wp_Helper::__( 'Circle Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet::before' => 'background-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'dots_tab_hover',
					[
						'label' => Wp_Helper::__( 'Hover & Active', 'elementor' ),
					]
				);

					$this->add_control(
						'dots_background_hover_color',
						[
							'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet:hover, {{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}};',
							],
						]
					);
		
					$this->add_control(
						'dots_circle_background_hover_color',
						[
							'label' => Wp_Helper::__( 'Circle Background Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet:hover::before, {{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet.swiper-pagination-bullet-active::before' => 'background-color: {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'dots_hover_border_color',
						[
							'label' => Wp_Helper::__( 'Border Color', 'elementor' ),
							'type' => Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet:hover, {{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'border-color: {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();


			$this->end_controls_tabs();

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name' => 'dots_border',
					'selector' => '{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet',
					'separator' => 'before',
				]
			);
				
			$this->add_responsive_control(
				'dots_border_radius',
				[
					'label' => Wp_Helper::__( 'Border Radius', 'elementor' ),
					'type' => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%' ],
					'selectors' => [
						'{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet, {{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					]
				]
			);

			$this->add_group_control(
				Group_Control_Box_Shadow::get_type(),
				[
					'name' => 'dots_box_shadow',
					'selector' => '{{WRAPPER}} .axps-swiper-slider .swiper-dots .swiper-pagination-bullet',
				]
			);
						
		$this->end_controls_section();
	}
}