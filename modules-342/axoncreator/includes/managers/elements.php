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
 * Elementor elements manager.
 *
 * Elementor elements manager handler class is responsible for registering and
 * initializing all the supported elements.
 *
 * @since 1.0.0
 */
class Elements_Manager {


	/**
	 * Element types.
	 *
	 * Holds the list of all the element types.
	 *
	 * @access private
	 *
	 * @var Element_Base[]
	 */
	private $_element_types;

	/**
	 * Element categories.
	 *
	 * Holds the list of all the element categories.
	 *
	 * @access private
	 *
	 * @var
	 */
	private $categories;

	/**
	 * Elements constructor.
	 *
	 * Initializing Elementor elements manager.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$this->require_files();
	}

	/**
	 * Create element instance.
	 *
	 * This method creates a new element instance for any given element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array        $element_data Element data.
	 * @param array        $element_args Optional. Element arguments. Default is
	 *                                   an empty array.
	 * @param Element_Base $element_type Optional. Element type. Default is null.
	 *
	 * @return Element_Base|null Element instance if element created, or null
	 *                           otherwise.
	 */
	public function create_element_instance( array $element_data, array $element_args = [], Element_Base $element_type = null ) {
		if ( null === $element_type ) {
			if ( 'widget' === $element_data['elType'] ) {
				$element_type = Plugin::$instance->widgets_manager->get_widget_types( $element_data['widgetType'] );
			} else {
				$element_type = $this->get_element_types( $element_data['elType'] );
			}
		}

		if ( ! $element_type ) {
			return null;
		}

		$args = array_merge( $element_type->get_default_args(), $element_args );

		$element_class = $element_type->get_class_name();

		try {
			$element = new $element_class( $element_data, $args );
		} catch ( \Exception $e ) {
			return null;
		}

		return $element;
	}

	/**
	 * Get element categories.
	 *
	 * Retrieve the list of categories the element belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Element categories.
	 */
	public function get_categories() {
		if ( null === $this->categories ) {
			$this->init_categories();
		}

		return $this->categories;
	}

	/**
	 * Add element category.
	 *
	 * Register new category for the element.
	 *
	 * @since 1.7.12
	 * @access public
	 *
	 * @param string $category_name       Category name.
	 * @param array  $category_properties Category properties.
	 */
	public function add_category( $category_name, $category_properties ) {
		if ( null === $this->categories ) {
			$this->get_categories();
		}

		if ( ! isset( $this->categories[ $category_name ] ) ) {
			$this->categories[ $category_name ] = $category_properties;
		}
	}

	/**
	 * Register element type.
	 *
	 * Add new type to the list of registered types.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Element_Base $element Element instance.
	 *
	 * @return bool Whether the element type was registered.
	 */
	public function register_element_type( Element_Base $element ) {
		$this->_element_types[ $element->get_name() ] = $element;

		return true;
	}

	/**
	 * Unregister element type.
	 *
	 * Remove element type from the list of registered types.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $name Element name.
	 *
	 * @return bool Whether the element type was unregister, or not.
	 */
	public function unregister_element_type( $name ) {
		if ( ! isset( $this->_element_types[ $name ] ) ) {
			return false;
		}

		unset( $this->_element_types[ $name ] );

		return true;
	}

	/**
	 * Get element types.
	 *
	 * Retrieve the list of all the element types, or if a specific element name
	 * was provided retrieve his element types.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $element_name Optional. Element name. Default is null.
	 *
	 * @return null|Element_Base|Element_Base[] Element types, or a list of all the element
	 *                             types, or null if element does not exist.
	 */
	public function get_element_types( $element_name = null ) {
		if ( is_null( $this->_element_types ) ) {
			$this->init_elements();
		}

		if ( null !== $element_name ) {
			return isset( $this->_element_types[ $element_name ] ) ? $this->_element_types[ $element_name ] : null;
		}

		return $this->_element_types;
	}

	/**
	 * Get element types config.
	 *
	 * Retrieve the config of all the element types.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Element types config.
	 */
	public function get_element_types_config() {
		$config = [];

		foreach ( $this->get_element_types() as $element ) {
			$config[ $element->get_name() ] = $element->get_config();
		}

		return $config;
	}

	/**
	 * Render elements content.
	 *
	 * Used to generate the elements templates on the editor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_elements_content() {
		foreach ( $this->get_element_types() as $element_type ) {
			$element_type->print_template();
		}
	}

	/**
	 * Init elements.
	 *
	 * Initialize Elementor elements by registering the supported elements.
	 * Elementor supports by default `section` element and `column` element.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function init_elements() {
		$this->_element_types = [];

		foreach ( [ 'section', 'column' ] as $element_name ) {
			$class_name = __NAMESPACE__ . '\Element_' . $element_name;

			$this->register_element_type( new $class_name() );
		}

		/**
		 * After elements registered.
		 *
		 * Fires after Elementor elements are registered.
		 *
		 * @since 1.0.0
		 */
		Wp_Helper::do_action( 'elementor/elements/elements_registered' );
	}

	/**
	 * Init categories.
	 *
	 * Initialize the element categories.
	 *
	 * @since 1.7.12
	 * @access private
	 */
	private function init_categories() {
		$this->categories = [
			'basic' => [
				'title' => Wp_Helper::__( 'Basic', 'elementor' ),
				'icon' => 'eicon-font',
			],
			'general' => [
				'title' => Wp_Helper::__( 'General', 'elementor' ),
				'icon' => 'eicon-font',
			],
			'axon-elements' => [
				'title' => Wp_Helper::__( 'Axon - Elements', 'elementor' ),
			],
		];

		/**
		 * When categories are registered.
		 *
		 * Fires after basic categories are registered, before WordPress
		 * category have been registered.
		 *
		 * This is where categories registered by external developers are
		 * added.
		 *
		 * @since 2.0.0
		 *
		 * @param Elements_Manager $this Elements manager instance.
		 */
		Wp_Helper::do_action( 'elementor/elements/categories_registered', $this );
	}

	/**
	 * Require files.
	 *
	 * Require Elementor element base class and column, section and repeater
	 * elements.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function require_files() {
		require_once AXON_CREATOR_PATH . 'includes/base/element-base.php';

		require AXON_CREATOR_PATH . 'includes/elements/column.php';
		require AXON_CREATOR_PATH . 'includes/elements/section.php';
		require AXON_CREATOR_PATH . 'includes/elements/repeater.php';
	}
}
