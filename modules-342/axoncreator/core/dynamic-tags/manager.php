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

namespace AxonCreator\Core\DynamicTags;

use AxonCreator\Core\Common\Modules\Ajax\Module as Ajax;
use AxonCreator\Core\Files\CSS\Post;
use AxonCreator\Core\Files\CSS\Post_Preview;
use AxonCreator\Plugin;
use AxonCreator\User;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

class Manager {

	const TAG_LABEL = 'elementor-tag';

	const MODE_RENDER = 'render';

	const MODE_REMOVE = 'remove';

	const DYNAMIC_SETTING_KEY = '__dynamic__';

	private $tags_groups = [];

	private $tags_info = [];

	private $parsing_mode = self::MODE_RENDER;

	/**
	 * Dynamic tags manager constructor.
	 *
	 * Initializing Elementor dynamic tags manager.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function __construct() {
		$this->add_actions();
	}

	/**
	 * Parse dynamic tags text.
	 *
	 * Receives the dynamic tag text, and returns a single value or multiple values
	 * from the tag callback function.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string   $text           Dynamic tag text.
	 * @param array    $settings       The dynamic tag settings.
	 * @param callable $parse_callback The functions that renders the dynamic tag.
	 *
	 * @return string|string[]|mixed A single string or an array of strings with
	 *                               the return values from each tag callback
	 *                               function.
	 */
	public function parse_tags_text( $text, array $settings, callable $parse_callback ) {
		if ( ! empty( $settings['returnType'] ) && 'object' === $settings['returnType'] ) {
			$value = $this->parse_tag_text( $text, $settings, $parse_callback );
		} else {

			$value = preg_replace_callback( '/\[' . self::TAG_LABEL . '.+?(?=\])\]/', function( $tag_text_match ) use ( $settings, $parse_callback ) {
				return $this->parse_tag_text( $tag_text_match[0], $settings, $parse_callback );
			}, $text );
		}

		return $value;
	}

	/**
	 * Parse dynamic tag text.
	 *
	 * Receives the dynamic tag text, and returns the value from the callback
	 * function.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string   $tag_text       Dynamic tag text.
	 * @param array    $settings       The dynamic tag settings.
	 * @param callable $parse_callback The functions that renders the dynamic tag.
	 *
	 * @return string|array|mixed If the tag was not found an empty string or an
	 *                            empty array will be returned, otherwise the
	 *                            return value from the tag callback function.
	 */
	public function parse_tag_text( $tag_text, array $settings, callable $parse_callback ) {
		$tag_data = $this->tag_text_to_tag_data( $tag_text );

		if ( ! $tag_data ) {
			if ( ! empty( $settings['returnType'] ) && 'object' === $settings['returnType'] ) {
				return [];
			}

			return '';
		}

		return call_user_func_array( $parse_callback, $tag_data );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $tag_text
	 *
	 * @return array|null
	 */
	public function tag_text_to_tag_data( $tag_text ) {
		preg_match( '/id="(.*?(?="))"/', $tag_text, $tag_id_match );
		preg_match( '/name="(.*?(?="))"/', $tag_text, $tag_name_match );
		preg_match( '/settings="(.*?(?="]))/', $tag_text, $tag_settings_match );

		if ( ! $tag_id_match || ! $tag_name_match || ! $tag_settings_match ) {
			return null;
		}

		return [
			'id' => $tag_id_match[1],
			'name' => $tag_name_match[1],
			'settings' => json_decode( urldecode( $tag_settings_match[1] ), true ),
		];
	}

	/**
	 * Dynamic tag to text.
	 *
	 * Retrieve the shortcode that represents the dynamic tag.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param Base_Tag $tag An instance of the dynamic tag.
	 *
	 * @return string The shortcode that represents the dynamic tag.
	 */
	public function tag_to_text( Base_Tag $tag ) {
		return sprintf( '[%1$s id="%2$s" name="%3$s" settings="%4$s"]', self::TAG_LABEL, $tag->get_id(), $tag->get_name(), urlencode( json_encode( $tag->get_settings(), JSON_FORCE_OBJECT ) ) );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @param string $tag_id
	 * @param string $tag_name
	 * @param array  $settings
	 *
	 * @return string
	 */
	public function tag_data_to_tag_text( $tag_id, $tag_name, array $settings = [] ) {
		$tag = $this->create_tag( $tag_id, $tag_name, $settings );

		if ( ! $tag ) {
			return '';
		}

		return $this->tag_to_text( $tag );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @param string $tag_id
	 * @param string $tag_name
	 * @param array  $settings
	 *
	 * @return Tag|null
	 */
	public function create_tag( $tag_id, $tag_name, array $settings = [] ) {
		$tag_info = $this->get_tag_info( $tag_name );

		if ( ! $tag_info ) {
			return null;
		}

		$tag_class = $tag_info['class'];

		return new $tag_class( [
			'settings' => $settings,
			'id' => $tag_id,
		] );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param       $tag_id
	 * @param       $tag_name
	 * @param array $settings
	 *
	 * @return null|string
	 */
	public function get_tag_data_content( $tag_id, $tag_name, array $settings = [] ) {
		if ( self::MODE_REMOVE === $this->parsing_mode ) {
			return null;
		}

		$tag = $this->create_tag( $tag_id, $tag_name, $settings );

		if ( ! $tag ) {
			return null;
		}

		return $tag->get_content();
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param $tag_name
	 *
	 * @return mixed|null
	 */
	public function get_tag_info( $tag_name ) {
		$tags = $this->get_tags();

		if ( empty( $tags[ $tag_name ] ) ) {
			return null;
		}

		return $tags[ $tag_name ];
	}

	/**
	 * @since 2.0.9
	 * @access public
	 */
	public function get_tags() {
		/**
		 * Register dynamic tags.
		 *
		 * Fires when Elementor registers dynamic tags.
		 *
		 * @since 2.0.9
		 *
		 * @param Manager $this Dynamic tags manager.
		 */
		Wp_Helper::do_action( 'elementor/dynamic_tags/register_tags', $this );

		return $this->tags_info;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $class
	 */
	public function register_tag( $class ) {
		/** @var Tag $tag */
		$tag = new $class();

		$this->tags_info[ $tag->get_name() ] = [
			'class' => $class,
			'instance' => $tag,
		];
	}

	/**
	 * @since 2.0.9
	 * @access public
	 *
	 * @param string $tag_name
	 */
	public function unregister_tag( $tag_name ) {
		unset( $this->tags_info[ $tag_name ] );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param       $group_name
	 * @param array $group_settings
	 */
	public function register_group( $group_name, array $group_settings ) {
		$default_group_settings = [
			'title' => '',
		];

		$group_settings = array_merge( $default_group_settings, $group_settings );

		$this->tags_groups[ $group_name ] = $group_settings;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function print_templates() {
		foreach ( $this->get_tags() as $tag_name => $tag_info ) {
			$tag = $tag_info['instance'];

			if ( ! $tag instanceof Tag ) {
				continue;
			}

			$tag->print_template();
		}
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_tags_config() {
		$config = [];

		foreach ( $this->get_tags() as $tag_name => $tag_info ) {
			/** @var Tag $tag */
			$tag = $tag_info['instance'];

			$config[ $tag_name ] = $tag->get_editor_config();
		}

		return $config;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_config() {
		return [
			'tags' => $this->get_tags_config(),
			'groups' => $this->tags_groups,
		];
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @throws \Exception If post ID is missing.
	 * @throws \Exception If current user don't have permissions to edit the post.
	 */
	public function ajax_render_tags( $data ) {
		if ( empty( $data['post_id'] ) ) {
			throw new \Exception( 'Missing post id.' );
		}

		if ( ! User::is_current_user_can_edit( $data['post_id'] ) ) {
			throw new \Exception( 'Access denied.' );
		}

		Plugin::$instance->db->switch_to_post( $data['post_id'] );

		/**
		 * Before dynamic tags rendered.
		 *
		 * Fires before Elementor renders the dynamic tags.
		 *
		 * @since 2.0.0
		 */
		Wp_Helper::do_action( 'elementor/dynamic_tags/before_render' );

		$tags_data = [];

		foreach ( $data['tags'] as $tag_key ) {
			$tag_key_parts = explode( '-', $tag_key );

			$tag_name = base64_decode( $tag_key_parts[0] );

			$tag_settings = json_decode( urldecode( base64_decode( $tag_key_parts[1] ) ), true );

			$tag = $this->create_tag( null, $tag_name, $tag_settings );

			$tags_data[ $tag_key ] = $tag->get_content();
		}

		/**
		 * After dynamic tags rendered.
		 *
		 * Fires after Elementor renders the dynamic tags.
		 *
		 * @since 2.0.0
		 */
		Wp_Helper::do_action( 'elementor/dynamic_tags/after_render' );

		return $tags_data;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param $mode
	 */
	public function set_parsing_mode( $mode ) {
		$this->parsing_mode = $mode;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_parsing_mode() {
		return $this->parsing_mode;
	}

	/**
	 * @since 2.1.0
	 * @access public
	 * @param Post $css_file
	 */
	public function after_enqueue_post_css( $css_file ) {
		$post_id = $css_file->get_post_id();

		if ( $css_file instanceof Post_Preview ) {
			$post_id_for_data = $css_file->get_preview_id();
		} else {
			$post_id_for_data = $post_id;
		}

		$css_file = new Dynamic_CSS( $post_id, $post_id_for_data );

		$css_file->enqueue();
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function register_ajax_actions( Ajax $ajax ) {
		$ajax->register_ajax_action( 'render_tags', [ $this, 'ajax_render_tags' ] );
	}

	/**
	 * @since 2.0.0
	 * @access private
	 */
	private function add_actions() {
		Wp_Helper::add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
		Wp_Helper::add_action( 'elementor/css-file/post/enqueue', [ $this, 'after_enqueue_post_css' ] );
	}
}
