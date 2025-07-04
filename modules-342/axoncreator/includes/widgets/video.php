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

use AxonCreator\Modules\DynamicTags\Module as TagsModule;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor video widget.
 *
 * Elementor widget that displays a video player.
 *
 * @since 1.0.0
 */
class Widget_Video extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve video widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'video';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve video widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return Wp_Helper::__( 'Video', 'elementor' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve video widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-youtube';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the video widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'basic' ];
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
		return [ 'video', 'player', 'embed', 'youtube', 'vimeo', 'dailymotion' ];
	}

	/**
	 * Register video widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'section_video',
			[
				'label' => Wp_Helper::__( 'Video', 'elementor' ),
			]
		);

		$this->add_control(
			'video_type',
			[
				'label' => Wp_Helper::__( 'Source', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'youtube',
				'options' => [
					'youtube' => Wp_Helper::__( 'YouTube', 'elementor' ),
					'vimeo' => Wp_Helper::__( 'Vimeo', 'elementor' ),
					'dailymotion' => Wp_Helper::__( 'Dailymotion', 'elementor' ),
					'hosted' => Wp_Helper::__( 'Self Hosted', 'elementor' ),
				],
			]
		);

		$this->add_control(
			'youtube_url',
			[
				'label' => Wp_Helper::__( 'Link', 'elementor' ),
				'type' => Controls_Manager::TEXT,

				'placeholder' => Wp_Helper::__( 'Enter your URL', 'elementor' ) . ' (YouTube)',
				'default' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
				'label_block' => true,
				'condition' => [
					'video_type' => 'youtube',
				],
			]
		);

		$this->add_control(
			'vimeo_url',
			[
				'label' => Wp_Helper::__( 'Link', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => Wp_Helper::__( 'Enter your URL', 'elementor' ) . ' (Vimeo)',
				'default' => 'https://vimeo.com/235215203',
				'label_block' => true,
				'condition' => [
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'dailymotion_url',
			[
				'label' => Wp_Helper::__( 'Link', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => Wp_Helper::__( 'Enter your URL', 'elementor' ) . ' (Dailymotion)',
				'default' => 'https://www.dailymotion.com/video/x6tqhqb',
				'label_block' => true,
				'condition' => [
					'video_type' => 'dailymotion',
				],
			]
		);

		$this->add_control(
			'insert_url',
			[
				'label' => Wp_Helper::__( 'External URL', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video_type' => 'hosted',
				],
			]
		);

		$this->add_control(
			'hosted_url',
			[
				'label' => Wp_Helper::__( 'Choose File', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'media_type' => 'video',
				'condition' => [
					'video_type' => 'hosted',
					'insert_url' => '',
				],
			]
		);

		$this->add_control(
			'external_url',
			[
				'label' => Wp_Helper::__( 'URL', 'elementor' ),
				'type' => Controls_Manager::URL,
				'autocomplete' => false,
				'show_external' => false,
				'label_block' => true,
				'show_label' => false,
				'media_type' => 'video',
				'placeholder' => Wp_Helper::__( 'Enter your URL', 'elementor' ),
				'condition' => [
					'video_type' => 'hosted',
					'insert_url' => 'yes',
				],
			]
		);

		$this->add_control(
			'start',
			[
				'label' => Wp_Helper::__( 'Start Time', 'elementor' ),
				'type' => Controls_Manager::NUMBER,
				'description' => Wp_Helper::__( 'Specify a start time (in seconds)', 'elementor' ),
				'condition' => [
					'loop' => '',
				],
			]
		);

		$this->add_control(
			'end',
			[
				'label' => Wp_Helper::__( 'End Time', 'elementor' ),
				'type' => Controls_Manager::NUMBER,
				'description' => Wp_Helper::__( 'Specify an end time (in seconds)', 'elementor' ),
				'condition' => [
					'loop' => '',
					'video_type' => [ 'youtube', 'hosted' ],
				],
			]
		);

		$this->add_control(
			'video_options',
			[
				'label' => Wp_Helper::__( 'Video Options', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => Wp_Helper::__( 'Autoplay', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'play_on_mobile',
			[
				'label' => Wp_Helper::__( 'Play On Mobile', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'autoplay' => 'yes',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'mute',
			[
				'label' => Wp_Helper::__( 'Mute', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
			]
		);

		$this->add_control(
			'loop',
			[
				'label' => Wp_Helper::__( 'Loop', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video_type!' => 'dailymotion',
				],
			]
		);

		$this->add_control(
			'controls',
			[
				'label' => Wp_Helper::__( 'Player Controls', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'default' => 'yes',
				'condition' => [
					'video_type!' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'showinfo',
			[
				'label' => Wp_Helper::__( 'Video Info', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'default' => 'yes',
				'condition' => [
					'video_type' => [ 'dailymotion' ],
				],
			]
		);

		$this->add_control(
			'modestbranding',
			[
				'label' => Wp_Helper::__( 'Modest Branding', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'video_type' => [ 'youtube' ],
					'controls' => 'yes',
				],
			]
		);

		$this->add_control(
			'logo',
			[
				'label' => Wp_Helper::__( 'Logo', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'default' => 'yes',
				'condition' => [
					'video_type' => [ 'dailymotion' ],
				],
			]
		);

		$this->add_control(
			'color',
			[
				'label' => Wp_Helper::__( 'Controls Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'condition' => [
					'video_type' => [ 'vimeo', 'dailymotion' ],
				],
			]
		);

		// YouTube.
		$this->add_control(
			'yt_privacy',
			[
				'label' => Wp_Helper::__( 'Privacy Mode', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'description' => Wp_Helper::__( 'When you turn on privacy mode, YouTube won\'t store information about visitors on your website unless they play the video.', 'elementor' ),
				'condition' => [
					'video_type' => 'youtube',
				],
			]
		);

		$this->add_control(
			'rel',
			[
				'label' => Wp_Helper::__( 'Suggested Videos', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => Wp_Helper::__( 'Current Video Channel', 'elementor' ),
					'yes' => Wp_Helper::__( 'Any Video', 'elementor' ),
				],
				'condition' => [
					'video_type' => 'youtube',
				],
			]
		);

		// Vimeo.
		$this->add_control(
			'vimeo_title',
			[
				'label' => Wp_Helper::__( 'Intro Title', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'default' => 'yes',
				'condition' => [
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'vimeo_portrait',
			[
				'label' => Wp_Helper::__( 'Intro Portrait', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'default' => 'yes',
				'condition' => [
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'vimeo_byline',
			[
				'label' => Wp_Helper::__( 'Intro Byline', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'default' => 'yes',
				'condition' => [
					'video_type' => 'vimeo',
				],
			]
		);

		$this->add_control(
			'download_button',
			[
				'label' => Wp_Helper::__( 'Download Button', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
				'condition' => [
					'video_type' => 'hosted',
				],
			]
		);

		$this->add_control(
			'poster',
			[
				'label' => Wp_Helper::__( 'Poster', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'condition' => [
					'video_type' => 'hosted',
				],
			]
		);

		$this->add_control(
			'view',
			[
				'label' => Wp_Helper::__( 'View', 'elementor' ),
				'type' => Controls_Manager::HIDDEN,
				'default' => 'youtube',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_image_overlay',
			[
				'label' => Wp_Helper::__( 'Image Overlay', 'elementor' ),
			]
		);

		$this->add_control(
			'show_image_overlay',
			[
				'label' => Wp_Helper::__( 'Image Overlay', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => Wp_Helper::__( 'Hide', 'elementor' ),
				'label_on' => Wp_Helper::__( 'Show', 'elementor' ),
			]
		);

		$this->add_control(
			'image_overlay',
			[
				'label' => Wp_Helper::__( 'Choose Image', 'elementor' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'show_image_overlay' => 'yes',
				],
			]
		);

		$this->add_control(
			'lazy_load',
			[
				'label' => Wp_Helper::__( 'Lazy Load', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'condition' => [
					'show_image_overlay' => 'yes',
					'video_type!' => 'hosted',
				],
			]
		);

		$this->add_control(
			'show_play_icon',
			[
				'label' => Wp_Helper::__( 'Play Icon', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'condition' => [
					'show_image_overlay' => 'yes',
					'image_overlay[url]!' => '',
				],
			]
		);

		$this->add_control(
			'lightbox',
			[
				'label' => Wp_Helper::__( 'Lightbox', 'elementor' ),
				'type' => Controls_Manager::SWITCHER,
				'frontend_available' => true,
				'label_off' => Wp_Helper::__( 'Off', 'elementor' ),
				'label_on' => Wp_Helper::__( 'On', 'elementor' ),
				'condition' => [
					'show_image_overlay' => 'yes',
					'image_overlay[url]!' => '',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_video_style',
			[
				'label' => Wp_Helper::__( 'Video', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'aspect_ratio',
			[
				'label' => Wp_Helper::__( 'Aspect Ratio', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'169' => '16:9',
					'219' => '21:9',
					'43' => '4:3',
					'32' => '3:2',
					'11' => '1:1',
					'916' => '9:16',
				],
				'default' => '169',
				'prefix_class' => 'elementor-aspect-ratio-',
				'frontend_available' => true,
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name' => 'css_filters',
				'selector' => '{{WRAPPER}} .elementor-wrapper',
			]
		);

		$this->add_control(
			'play_icon_title',
			[
				'label' => Wp_Helper::__( 'Play Icon', 'elementor' ),
				'type' => Controls_Manager::HEADING,
				'condition' => [
					'show_image_overlay' => 'yes',
					'show_play_icon' => 'yes',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'play_icon_color',
			[
				'label' => Wp_Helper::__( 'Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-custom-embed-play i' => 'color: {{VALUE}}',
				],
				'condition' => [
					'show_image_overlay' => 'yes',
					'show_play_icon' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'play_icon_size',
			[
				'label' => Wp_Helper::__( 'Size', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 300,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-custom-embed-play i' => 'font-size: {{SIZE}}{{UNIT}}',
				],
				'condition' => [
					'show_image_overlay' => 'yes',
					'show_play_icon' => 'yes',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'play_icon_text_shadow',
				'selector' => '{{WRAPPER}} .elementor-custom-embed-play i',
				'fields_options' => [
					'text_shadow_type' => [
						'label' => Wp_Helper::_x( 'Shadow', 'Text Shadow Control', 'elementor' ),
					],
				],
				'condition' => [
					'show_image_overlay' => 'yes',
					'show_play_icon' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_lightbox_style',
			[
				'label' => Wp_Helper::__( 'Lightbox', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_image_overlay' => 'yes',
					'image_overlay[url]!' => '',
					'lightbox' => 'yes',
				],
			]
		);

		$this->add_control(
			'lightbox_color',
			[
				'label' => Wp_Helper::__( 'Background Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'#elementor-lightbox-{{ID}}' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'lightbox_ui_color',
			[
				'label' => Wp_Helper::__( 'UI Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'#elementor-lightbox-{{ID}} .dialog-lightbox-close-button' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'lightbox_ui_color_hover',
			[
				'label' => Wp_Helper::__( 'UI Hover Color', 'elementor' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'#elementor-lightbox-{{ID}} .dialog-lightbox-close-button:hover' => 'color: {{VALUE}}',
				],
				'separator' => 'after',
			]
		);

		$this->add_control(
			'lightbox_video_width',
			[
				'label' => Wp_Helper::__( 'Content Width', 'elementor' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'unit' => '%',
				],
				'range' => [
					'%' => [
						'min' => 30,
					],
				],
				'selectors' => [
					'(desktop+)#elementor-lightbox-{{ID}} .elementor-video-container' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'lightbox_content_position',
			[
				'label' => Wp_Helper::__( 'Content Position', 'elementor' ),
				'type' => Controls_Manager::SELECT,
				'frontend_available' => true,
				'options' => [
					'' => Wp_Helper::__( 'Center', 'elementor' ),
					'top' => Wp_Helper::__( 'Top', 'elementor' ),
				],
				'selectors' => [
					'#elementor-lightbox-{{ID}} .elementor-video-container' => '{{VALUE}}; transform: translateX(-50%);',
				],
				'selectors_dictionary' => [
					'top' => 'top: 60px',
				],
			]
		);

		$this->add_responsive_control(
			'lightbox_content_animation',
			[
				'label' => Wp_Helper::__( 'Entrance Animation', 'elementor' ),
				'type' => Controls_Manager::ANIMATION,
				'frontend_available' => true,
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render video widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$video_url = $settings[ $settings['video_type'] . '_url' ];

		if ( 'hosted' === $settings['video_type'] ) {
			$video_url = $this->get_hosted_video_url();
		}

		if ( empty( $video_url ) ) {
			return;
		}

		if ( 'hosted' === $settings['video_type'] ) {
			ob_start();

			$this->render_hosted_video();

			$video_html = ob_get_clean();
		} else {
			$embed_params = $this->get_embed_params();

			$embed_options = $this->get_embed_options();

			$video_html = Embed::get_embed_html( $video_url, $embed_params, $embed_options );
		}

		if ( empty( $video_html ) ) {
			echo Wp_Helper::esc_url( $video_url );

			return;
		}

		$this->add_render_attribute( 'video-wrapper', 'class', 'elementor-wrapper' );

		if ( ! $settings['lightbox'] ) {
			$this->add_render_attribute( 'video-wrapper', 'class', 'elementor-fit-aspect-ratio' );
		}

		$this->add_render_attribute( 'video-wrapper', 'class', 'elementor-open-' . ( $settings['lightbox'] ? 'lightbox' : 'inline' ) );
		?>
		<div <?php echo $this->get_render_attribute_string( 'video-wrapper' ); ?>>
			<?php
			if ( ! $settings['lightbox'] ) {
				echo $video_html; // XSS ok.
			}

			if ( $this->has_image_overlay() ) {
				$this->add_render_attribute( 'image-overlay', 'class', 'elementor-custom-embed-image-overlay' );

				if ( $settings['lightbox'] ) {
					if ( 'hosted' === $settings['video_type'] ) {
						$lightbox_url = $video_url;
					} else {
						$lightbox_url = Embed::get_embed_url( $video_url, $embed_params, $embed_options );
					}

					$lightbox_options = [
						'type' => 'video',
						'videoType' => $settings['video_type'],
						'url' => $lightbox_url,
						'modalOptions' => [
							'id' => 'elementor-lightbox-' . $this->get_id(),
							'entranceAnimation' => $settings['lightbox_content_animation'],
							'entranceAnimation_tablet' => $settings['lightbox_content_animation_tablet'],
							'entranceAnimation_mobile' => $settings['lightbox_content_animation_mobile'],
							'videoAspectRatio' => $settings['aspect_ratio'],
						],
					];

					if ( 'hosted' === $settings['video_type'] ) {
						$lightbox_options['videoParams'] = $this->get_hosted_params();
					}

					$this->add_render_attribute( 'image-overlay', [
						'data-elementor-open-lightbox' => 'yes',
						'data-elementor-lightbox' => json_encode( $lightbox_options ),
					] );

					if ( Plugin::$instance->editor->is_edit_mode() ) {
						$this->add_render_attribute( 'image-overlay', [
							'class' => 'elementor-clickable',
						] );
					}
				} else {
					$this->add_render_attribute( 'image-overlay', 'style', 'background-image: url(' . $settings['image_overlay']['url'] . ');' );
				}
				?>
				<div <?php echo $this->get_render_attribute_string( 'image-overlay' ); ?>>
					<?php if ( $settings['lightbox'] ) : ?>
						<?php echo Group_Control_Image_Size::get_attachment_image_html( $settings, 'image_overlay' ); ?>
					<?php endif; ?>
					<?php if ( 'yes' === $settings['show_play_icon'] ) : ?>
						<div class="elementor-custom-embed-play" role="button">
							<i class="eicon-play" aria-hidden="true"></i>
							<span class="elementor-screen-only"><?php echo Wp_Helper::__( 'Play Video', 'elementor' ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Render video widget as plain content.
	 *
	 * Override the default behavior, by printing the video URL insted of rendering it.
	 *
	 * @since 1.4.5
	 * @access public
	 */
	public function render_plain_content() {
		$settings = $this->get_settings_for_display();

		if ( 'hosted' !== $settings['video_type'] ) {
			$url = $settings[ $settings['video_type'] . '_url' ];
		} else {
			$url = $this->get_hosted_video_url();
		}

		echo Wp_Helper::esc_url( $url );
	}

	/**
	 * Get embed params.
	 *
	 * Retrieve video widget embed parameters.
	 *
	 * @since 1.5.0
	 * @access public
	 *
	 * @return array Video embed parameters.
	 */
	public function get_embed_params() {
		$settings = $this->get_settings_for_display();

		$params = [];

		if ( $settings['autoplay'] && ! $this->has_image_overlay() ) {
			$params['autoplay'] = '1';

			if ( $settings['play_on_mobile'] ) {
				$params['playsinline'] = '1';
			}
		}

		$params_dictionary = [];

		if ( 'youtube' === $settings['video_type'] ) {
			$params_dictionary = [
				'loop',
				'controls',
				'mute',
				'rel',
				'modestbranding',
			];

			if ( $settings['loop'] ) {
				$video_properties = Embed::get_video_properties( $settings['youtube_url'] );

				$params['playlist'] = $video_properties['video_id'];
			}

			$params['start'] = $settings['start'];

			$params['end'] = $settings['end'];

			$params['wmode'] = 'opaque';
		} elseif ( 'vimeo' === $settings['video_type'] ) {
			$params_dictionary = [
				'loop',
				'mute' => 'muted',
				'vimeo_title' => 'title',
				'vimeo_portrait' => 'portrait',
				'vimeo_byline' => 'byline',
			];

			$params['color'] = str_replace( '#', '', $settings['color'] );

			$params['autopause'] = '0';
		} elseif ( 'dailymotion' === $settings['video_type'] ) {
			$params_dictionary = [
				'controls',
				'mute',
				'showinfo' => 'ui-start-screen-info',
				'logo' => 'ui-logo',
			];

			$params['ui-highlight'] = str_replace( '#', '', $settings['color'] );

			$params['start'] = $settings['start'];

			$params['endscreen-enable'] = '0';
		}

		foreach ( $params_dictionary as $key => $param_name ) {
			$setting_name = $param_name;

			if ( is_string( $key ) ) {
				$setting_name = $key;
			}

			$setting_value = $settings[ $setting_name ] ? '1' : '0';

			$params[ $param_name ] = $setting_value;
		}

		return $params;
	}

	/**
	 * Whether the video widget has an overlay image or not.
	 *
	 * Used to determine whether an overlay image was set for the video.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return bool Whether an image overlay was set for the video.
	 */
	protected function has_image_overlay() {
		$settings = $this->get_settings_for_display();

		return ! empty( $settings['image_overlay']['url'] ) && 'yes' === $settings['show_image_overlay'];
	}

	/**
	 * @since 2.1.0
	 * @access private
	 */
	private function get_embed_options() {
		$settings = $this->get_settings_for_display();

		$embed_options = [];

		if ( 'youtube' === $settings['video_type'] ) {
			$embed_options['privacy'] = $settings['yt_privacy'];
		} elseif ( 'vimeo' === $settings['video_type'] ) {
			$embed_options['start'] = $settings['start'];
		}

		$embed_options['lazy_load'] = ! empty( $settings['lazy_load'] );

		return $embed_options;
	}

	/**
	 * @since 2.1.0
	 * @access private
	 */
	private function get_hosted_params() {
		$settings = $this->get_settings_for_display();

		$video_params = [];

		foreach ( [ 'autoplay', 'loop', 'controls' ] as $option_name ) {
			if ( $settings[ $option_name ] ) {
				$video_params[ $option_name ] = '';
			}
		}

		if ( $settings['mute'] ) {
			$video_params['muted'] = 'muted';
		}

		if ( $settings['play_on_mobile'] ) {
			$video_params['playsinline'] = '';
		}

		if ( ! $settings['download_button'] ) {
			$video_params['controlsList'] = 'nodownload';
		}

		if ( $settings['poster']['url'] ) {
			$video_params['poster'] = $settings['poster']['url'];
		}

		return $video_params;
	}

	/**
	 * @param bool $from_media
	 *
	 * @return string
	 * @since 2.1.0
	 * @access private
	 */
	private function get_hosted_video_url() {
		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings['insert_url'] ) ) {
			$video_url = $settings['external_url']['url'];
		} else {
			$video_url = $settings['hosted_url']['url'];
		}

		if ( empty( $video_url ) ) {
			return '';
		}

		if ( $settings['start'] || $settings['end'] ) {
			$video_url .= '#t=';
		}

		if ( $settings['start'] ) {
			$video_url .= $settings['start'];
		}

		if ( $settings['end'] ) {
			$video_url .= ',' . $settings['end'];
		}

		return $video_url;
	}

	/**
	 *
	 * @since 2.1.0
	 * @access private
	 */
	private function render_hosted_video() {
		$video_url = $this->get_hosted_video_url();
		if ( empty( $video_url ) ) {
			return;
		}

		$video_params = $this->get_hosted_params();
		?>
		<video class="elementor-video" src="<?php echo Wp_Helper::esc_url( $video_url ); ?>" <?php echo Utils::render_html_attributes( $video_params ); ?>></video>
		<?php
	}
}
