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

namespace AxonCreator\Core\Files\CSS;

use AxonCreator\Controls_Stack;
use AxonCreator\Element_Base;
use AxonCreator\Plugin;
use AxonCreator\Wp_Helper;

if (!defined('_PS_VERSION_')) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor post CSS file.
 *
 * Elementor CSS file handler class is responsible for generating the single
 * post CSS file.
 *
 * @since 1.2.0
 */
class Post extends Base {

	/**
	 * Elementor post CSS file prefix.
	 */
	const FILE_PREFIX = 'post-';

	const META_KEY = '_elementor_css';

	/**
	 * Post ID.
	 *
	 * Holds the current post ID.
	 *
	 * @var int
	 */
	private $post_id;

	/**
	 * Post CSS file constructor.
	 *
	 * Initializing the CSS file of the post. Set the post ID and initiate the stylesheet.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param int $post_id Post ID.
	 */
	public function __construct( $post_id ) {
		$this->post_id = $post_id;
		
		parent::__construct( self::FILE_PREFIX . $this->post_id . '-' . Wp_Helper::$id_lang . '.css' );
	}

	/**
	 * Get CSS file name.
	 *
	 * Retrieve the CSS file name.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @return string CSS file name.
	 */
	public function get_name() {
		return 'post';
	}

	/**
	 * Get post ID.
	 *
	 * Retrieve the ID of current post.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return int Post ID.
	 */
	public function get_post_id() {
		return $this->post_id;
	}

	/**
	 * Get unique element selector.
	 *
	 * Retrieve the unique selector for any given element.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param Element_Base $element The element.
	 *
	 * @return string Unique element selector.
	 */
	public function get_element_unique_selector( Element_Base $element ) {
		return '.elementor-' . $this->post_id . ' .elementor-element' . $element->get_unique_selector();
	}

	/**
	 * Load meta data.
	 *
	 * Retrieve the post CSS file meta data.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @return array Post CSS file meta data.
	 */
	protected function load_meta() {
		return Wp_Helper::get_post_meta( $this->post_id, static::META_KEY . '_id_lang_' . Wp_Helper::$id_lang, true );
	}

	/**
	 * Update meta data.
	 *
	 * Update the global CSS file meta data.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @param array $meta New meta data.
	 */
	protected function update_meta( $meta ) {
		Wp_Helper::update_post_meta( $this->post_id, static::META_KEY . '_id_lang_' . Wp_Helper::$id_lang, $meta );
	}

	/**
	 * Delete meta.
	 *
	 * Delete the file meta data.
	 *
	 * @since  2.1.0
	 * @access protected
	 */
	protected function delete_meta() {		
		Wp_Helper::delete_post_meta( $this->post_id, static::META_KEY . '_id_lang_' . Wp_Helper::$id_lang );
	}

	/**
	 * Get post data.
	 *
	 * Retrieve raw post data from the database.
	 *
	 * @since 1.9.0
	 * @access protected
	 *
	 * @return array Post data.
	 */
	protected function get_data() {
		$content = [];

		if( Wp_Helper::$is_template ){
			$obj =  new \AxonCreatorTemplate( $this->post_id );
			$content = (array) json_decode( $obj->content, true );
		}
		
		$document = Plugin::$instance->documents->get( $this->post_id );
		$content = $document ? $document->get_elements_data() : [];
		
		$content = Wp_Helper::apply_filters( 'elementor/post_css/get_data', $content, $this->post_id );

		return $content;
	}

	/**
	 * Render CSS.
	 *
	 * Parse the CSS for all the elements.
	 *
	 * @since 1.2.0
	 * @access protected
	 */
	protected function render_css() {
		$data = $this->get_data();

		if ( ! empty( $data ) ) {
			foreach ( $data as $element_data ) {
				$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

				if ( ! $element ) {
					continue;
				}

				$this->render_styles( $element );
			}
		}
	}

	/**
	 * Enqueue CSS.
	 *
	 * Enqueue the post CSS file in Elementor.
	 *
	 * This method ensures that the post was actually built with elementor before
	 * enqueueing the post CSS file.
	 *
	 * @since 1.2.2
	 * @access public
	 */
	public function enqueue() {
		parent::enqueue();
	}

	/**
	 * Add controls-stack style rules.
	 *
	 * Parse the CSS for all the elements inside any given controls stack.
	 *
	 * This method recursively renders the CSS for all the child elements in the stack.
	 *
	 * @since 1.6.0
	 * @access public
	 *
	 * @param Controls_Stack $controls_stack The controls stack.
	 * @param array          $controls       Controls array.
	 * @param array          $values         Values array.
	 * @param array          $placeholders   Placeholders.
	 * @param array          $replacements   Replacements.
	 * @param array          $all_controls   All controls.
	 */
	public function add_controls_stack_style_rules( Controls_Stack $controls_stack, array $controls, array $values, array $placeholders, array $replacements, array $all_controls = null ) {
		parent::add_controls_stack_style_rules( $controls_stack, $controls, $values, $placeholders, $replacements, $all_controls );

		if ( $controls_stack instanceof Element_Base ) {
			foreach ( $controls_stack->get_children() as $child_element ) {
				$this->render_styles( $child_element );
			}
		}
	}

	/**
	 * Get enqueue dependencies.
	 *
	 * Retrieve the name of the stylesheet used by `wp_enqueue_style()`.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @return array Name of the stylesheet.
	 */
	protected function get_enqueue_dependencies() {
		return [ 'elementor-frontend' ];
	}

	/**
	 * Get inline dependency.
	 *
	 * Retrieve the name of the stylesheet used by `wp_add_inline_style()`.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @return string Name of the stylesheet.
	 */
	protected function get_inline_dependency() {
		return 'elementor-frontend';
	}

	/**
	 * Get file handle ID.
	 *
	 * Retrieve the handle ID for the post CSS file.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @return string CSS file handle ID.
	 */
	protected function get_file_handle_id() {
		return 'elementor-post-' . $this->post_id;
	}

	/**
	 * Render styles.
	 *
	 * Parse the CSS for any given element.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @param Element_Base $element The element.
	 */
	protected function render_styles( Element_Base $element ) {
		/**
		 * Before element parse CSS.
		 *
		 * Fires before the CSS of the element is parsed.
		 *
		 * @since 1.2.0
		 *
		 * @param Post         $this    The post CSS file.
		 * @param Element_Base $element The element.
		 */
		Wp_Helper::do_action( 'elementor/element/before_parse_css', $this, $element );

		$element_settings = $element->get_settings();

		$this->add_controls_stack_style_rules( $element, $element->get_style_controls( null, $element->get_parsed_dynamic_settings() ), $element_settings, [ '{{ID}}', '{{WRAPPER}}' ], [ $element->get_id(), $this->get_element_unique_selector( $element ) ] );

		/**
		 * After element parse CSS.
		 *
		 * Fires after the CSS of the element is parsed.
		 *
		 * @since 1.2.0
		 *
		 * @param Post         $this    The post CSS file.
		 * @param Element_Base $element The element.
		 */
		Wp_Helper::do_action( 'elementor/element/parse_css', $this, $element );
		
		$this->add_post_css( $this, $element );
	}
	
	/**
	 * @param $post_css Post
	 * @param $element  Element_Base
	 */
	public function add_post_css( $post_css, $element ) {
		if ( $post_css instanceof Dynamic_CSS ) {
			return;
		}

		$element_settings = $element->get_settings();

		if ( empty( $element_settings['custom_css'] ) ) {
			return;
		}

		$css = trim( $element_settings['custom_css'] );

		if ( empty( $css ) ) {
			return;
		}
		$css = str_replace( 'selector', $post_css->get_element_unique_selector( $element ), $css );

		// Add a css comment
		$css = sprintf( '/* Start custom CSS for %s, class: %s */', $element->get_name(), $element->get_unique_selector() ) . $css . '/* End custom CSS */';

		$post_css->get_stylesheet()->add_raw_css( $css );
	}
}
