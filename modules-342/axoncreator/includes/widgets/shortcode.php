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
 * Elementor shortcode widget.
 *
 * Elementor widget that insert any shortcodes into the page.
 *
 * @since 1.0.0
 */
class Widget_Shortcode extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve shortcode widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'shortcode';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve shortcode widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return Wp_Helper::__( 'Custom hook', 'elementor' );
	}
	
	public function get_categories() {
		return [ 'axon-elements' ];
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve shortcode widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-shortcode';
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
		return [ 'shortcode', 'code' ];
	}
	
	/**
	 * Register shortcode widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'section_shortcode',
			[
				'label' => Wp_Helper::__( 'Custom hook', 'elementor' ),
			]
		);

		$this->add_control(
			'shortcode',
			[
				'label' => Wp_Helper::__( 'Enter your hook', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => 'displayShortCode',
				'default' => '',
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'shortcode_key',
			[
				'label' => Wp_Helper::__( 'Key', 'elementor' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$repeater->add_control(
			'shortcode_value',
			[
				'label' => Wp_Helper::__( 'Value', 'elementor' ),
				'type' => Controls_Manager::TEXT,
			]
		);

		$this->add_control(
			'params',
			[
				'label' => Wp_Helper::__( 'Params', 'elementor' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'title_field' => '{{{ shortcode_key }}}',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render shortcode widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$shortcode = $this->get_settings_for_display( 'shortcode' );

        $params = $this->get_settings_for_display( 'params' );

        $hook_args = [];

        foreach ( $params as $param ) {
            $hook_args[$param['shortcode_key']] = $param['shortcode_value'];
        }

		if ( $shortcode && \Hook::isDisplayHookName( $shortcode ) ) { 
			echo '<div class="elementor-custom-hook">' . \Hook::exec( $shortcode, $hook_args ) . '</div>'; 
        }
	}

	/**
	 * Render shortcode widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _content_template() {}
}