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

namespace AxonCreator\Core\Settings\Page;

use AxonCreator\Core\Settings\Base\Model as BaseModel;
use AxonCreator\Plugin;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor page settings model.
 *
 * Elementor page settings model handler class is responsible for registering
 * and managing Elementor page settings models.
 *
 * @since 1.6.0
 */
class Model extends BaseModel {

	/**
	 * WordPress post object.
	 *
	 * Holds an instance of `WP_Post` containing the post object.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * @var \WP_Post
	 */
	private $post_parent;

	/**
	 * Model constructor.
	 *
	 * Initializing Elementor page settings model.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @param array $data Optional. Model data. Default is an empty array.
	 */
	public function __construct( array $data = [] ) {

		parent::__construct( $data );
	}

	/**
	 * Get model name.
	 *
	 * Retrieve page settings model name.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return string Model name.
	 */
	public function get_name() {
		return 'page-settings';
	}

	/**
	 * Get model unique name.
	 *
	 * Retrieve page settings model unique name.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return string Model unique name.
	 */
	public function get_unique_name() {
		return $this->get_name() . '-' . Wp_Helper::$id_post;
	}

	/**
	 * Get CSS wrapper selector.
	 *
	 * Retrieve the wrapper selector for the page settings model.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return string CSS wrapper selector.
	 */
	public function get_css_wrapper_selector() {
		$document = Plugin::$instance->documents->get( Wp_Helper::$id_post );
		return $document->get_css_wrapper_selector();
	}

	/**
	 * Get panel page settings.
	 *
	 * Retrieve the panel setting for the page settings model.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return array {
	 *    Panel settings.
	 *
	 *    @type string $title The panel title.
	 * }
	 */
	public function get_panel_page_settings() {
		return [
			/* translators: %s: Document title */
			'title' => sprintf( Wp_Helper::__( '%s Settings', 'elementor' ), Wp_Helper::$post_title ),
		];
	}

	/**
	 * On export post meta.
	 *
	 * When exporting data, check if the post is not using page template and
	 * exclude it from the exported Elementor data.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @param array $element_data Element data.
	 *
	 * @return array Element data to be exported.
	 */
	public function on_export( $element_data ) {
		if ( ! empty( $element_data['settings']['template'] ) ) {
			/**
			 * @var \Elementor\Modules\PageTemplates\Module $page_templates_module
			 */
			$page_templates_module = Plugin::$instance->modules_manager->get_modules( 'page-templates' );
			$is_elementor_template = ! ! $page_templates_module->get_template_path( $element_data['settings']['template'] );

			if ( ! $is_elementor_template ) {
				unset( $element_data['settings']['template'] );
			}
		}

		return $element_data;
	}

	/**
	 * Register model controls.
	 *
	 * Used to add new controls to the page settings model.
	 *
	 * @since 1.6.0
	 * @access protected
	 */
	protected function _register_controls() {
		// Check if it's a real model, or abstract (for example - on import )
		if ( Wp_Helper::$id_post ) {
			$document = Plugin::$instance->documents->get_doc_or_auto_save( Wp_Helper::$id_post );

			if ( $document ) {
				$controls = $document->get_controls();

				foreach ( $controls as $control_id => $args ) {
					$this->add_control( $control_id, $args );
				}
			}
		}
	}
}
