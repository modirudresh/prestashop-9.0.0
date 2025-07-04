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
class Widget_Axps_Module extends Widget_Base {

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
		return 'axps-module';
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
		return Wp_Helper::__( 'Module', 'elementor' );
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
		return 'eicon-parallax';
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
	 * Register Site Logo controls.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->register_content_controls();
	}

	/**
	 * Register Site Logo General Controls.
	 *
	 * @since 1.3.0
	 * @access protected
	 */
	protected function register_content_controls() {
			$this->add_control(
				'section_pswidget_options',
				[
					'label' => Wp_Helper::__('Widget Settings', 'elementor'),
					'type' => 'section',
				]
			);

			$this->add_control(
				'module',
				[
					'label' => Wp_Helper::__('Module', 'elementor'),
					'type' => 'select',
					'label_block' => true,
					'default' => '0',
					'description' => Wp_Helper::__('This function is only for advanced users, and issues related to this will be not supported. It maybe needed to clear Prestashop Cache if you do some changes in included module if they will be not visible.', 'elementor'),
					'section' => 'section_pswidget_options',
					'options' => $this->_getModules(),
				]
			);
	}
	
    public function _getModules()
    {
		$excludeModules = ['axoncreator', 'gadwords'];
		
        $table = _DB_PREFIX_ . 'module';
        $excludeModules = implode("','", $excludeModules);
        $rows = \Db::getInstance()->executeS(
            "SELECT m.name FROM $table AS m " . \Shop::addSqlAssociation('module', 'm') .
            " WHERE m.active = 1 AND m.name NOT IN ('$excludeModules')"
        );

		$modules = [Wp_Helper::__('- Select Module -', 'elementor')];
        foreach ($rows as $row) {
            try {
                $mod = \Module::getInstanceByName($row['name']);

                if (\Validate::isLoadedObject($mod) && method_exists($mod, 'renderWidget')) {
                    $modules[$mod->name] = $mod->displayName;
                }
            } catch (\Exception $ex) {
                // TODO
            }
        }
        return $modules;
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
		$content = empty($settings['module']) ? '' : $this->_renderModule('displayIncludePageBuilder', [], $settings['module']);
		
		echo $content;
	}
	
    public function _renderModule($hook_name, $hook_args = array(), $module = null)
    {
        $res = '';
        try {
            $mod = \Module::getInstanceByName($module);

            if (\Validate::isLoadedObject($mod) && method_exists($mod, 'renderWidget')) {
                $res = $mod->renderWidget($hook_name, $hook_args);
            }
        } catch (\Exception $ex) {
            // TODO
        }
        return $res;
    }
}