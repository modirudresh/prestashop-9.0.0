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
class Widget_Axps_Revslider extends Widget_Base {

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
		return 'axps-revslider';
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
		return Wp_Helper::__( 'Revo slides', 'elementor' );
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
		return 'eicon-slides';
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
		return [ 'slides', 'axps' ];
	}

	/**
	 * Register Site Logo controls.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function _register_controls() {
        $sliders = [];
        $sliders[0] = Wp_Helper::__('-- None --', 'elementor');
		
		$datas = $this->get_sliders();
		if ($datas) {
			foreach ($datas as $slider) {
				$sliders[$slider['id']] = $slider['title'] . '(' . $slider['alias'] . ')';
			}
		}
		
		$this->start_controls_section(
			'section_general_fields',
			[
				'label' => Wp_Helper::__( 'Widget', 'elementor' ),
			]
		);

		$this->add_control(
			'slider',
			[
				'label'        => Wp_Helper::__( 'Slider', 'elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 0,
				'options'      => $sliders,
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
    public function get_sliders()
    {
        $sql = 'SELECT *
				FROM  `' . _DB_PREFIX_ . 'revslider_sliders`  
				';
        if (!$result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql)) {
            return false;
        }

        return $result;
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
				
		$settings = $this->get_settings_for_display();
		
		if(\Module::isEnabled('revsliderprestashop'))
		{
			$slider = '';
			$sliderId = (int)$settings['slider'];

			if ($sliderId != 0) {
				$module = \Module::getInstanceByName('revsliderprestashop');

				if (\Validate::isLoadedObject($module)) {					
					if((int)$module->version < 6){
						$module->_prehook();
						$slider = $module->generateSliderById($sliderId);
					}else{
						$slider = $module->generateSliderFromShortcode($sliderId);
					}
				}
			}

			echo $slider;
		}
	}
}