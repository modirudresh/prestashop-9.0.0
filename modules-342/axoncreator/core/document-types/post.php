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

namespace AxonCreator\Core\DocumentTypes;

use AxonCreator\Controls_Manager;
use AxonCreator\Core\Base\Document;
use AxonCreator\Group_Control_Background;
use AxonCreator\Plugin;
use AxonCreator\Settings;
use AxonCreator\Core\Settings\Manager as SettingsManager;
use AxonCreator\Utils;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

class Post extends Document {

	/**
	 * @since 2.0.8
	 * @access public
	 * @static
	 */
	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['admin_tab_group'] = '';
		$properties['support_wp_page_templates'] = true;

		return $properties;
	}

	/**
	 * @since 2.1.2
	 * @access protected
	 * @static
	 */
	protected static function get_editor_panel_categories() {
		return Utils::array_inject(
			parent::get_editor_panel_categories(),
			'theme-elements',
			[
				'theme-elements-single' => [
					'title' => Wp_Helper::__( 'Single', 'elementor' ),
					'active' => false,
				],
			]
		);
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_name() {
		return 'post';
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 */
	public static function get_title() {
		return Wp_Helper::__( 'Page', 'elementor' );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_css_wrapper_selector() {
		return '.elementor.elementor-' . Wp_Helper::$id_post;
	}

	/**
	 * @since 2.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		parent::_register_controls();

		self::register_hide_title_control( $this );

		self::register_post_fields_control( $this );

		self::register_style_controls( $this );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 * @param Document $document
	 */
	public static function register_hide_title_control( $document ) {
		///xxxxx
		$page_title_selector = '.elementor-page-title';

		$document->start_injection( [
			'of' => 'post_status',
			'fallback' => [
				'of' => 'post_title',
			],
		] );

		$document->add_control(
			'hide_title',
			[
				'label' => Wp_Helper::__( 'Hide Title', 'elementor' ),
				'type' => Controls_Manager::HIDDEN,
				'selectors' => [
					'{{WRAPPER}} ' . $page_title_selector => 'display: none',
				],
			]
		);

		$document->end_injection();
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 * @param Document $document
	 */
	public static function register_style_controls( $document ) {
		$document->start_controls_section(
			'section_page_style',
			[
				'label' => Wp_Helper::__( 'Body Style', 'elementor' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$document->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'  => 'background',
				'fields_options' => [
					'image' => [
						// Currently isn't supported.
						'dynamic' => [
							'active' => false,
						],
					],
				],
			]
		);

		$document->add_responsive_control(
			'padding',
			[
				'label' => Wp_Helper::__( 'Padding', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
			]
		);
		
		$document->add_responsive_control(
			'margin',
			[
				'label' => Wp_Helper::__( 'Margin', 'elementor' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'allowed_dimensions' => 'vertical',
				'placeholder' => [
					'top' => '',
					'right' => 'auto',
					'bottom' => '',
					'left' => 'auto',
				],
				'selectors' => [
					'{{WRAPPER}}' => 'margin-top: {{TOP}}{{UNIT}}; margin-bottom: {{BOTTOM}}{{UNIT}};',
				],
			]
		);

		$document->end_controls_section();

		Plugin::$instance->controls_manager->add_custom_css_controls( $document );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 * @param Document $document
	 */
	public static function register_post_fields_control( $document ) {
		$document->start_injection( [
			'of' => 'post_status',
			'fallback' => [
				'of' => 'post_title',
			],
		] );
		$document->end_injection();
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $data
	 *
	 * @throws \Exception
	 */
	public function __construct( array $data = [] ) {
		if ( $data ) {
			$template = Wp_Helper::get_post_meta( $data['post_id'], '_wp_page_template' . '_id_lang_' . Wp_Helper::$id_lang, true );

			if ( empty( $template ) ) {
				$template = 'default';
			}

			$data['settings']['template'] = $template;
		}

		parent::__construct( $data );
	}

	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$config['type'] = 'page';

		return $config;
	}
}
