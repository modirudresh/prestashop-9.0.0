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

namespace AxonCreator\Core\Files;

use AxonCreator\Plugin;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

abstract class Base {

	const UPLOADS_DIR = 'elementor/';

	const DEFAULT_FILES_DIR = 'css/';

	const META_KEY = '';

	private static $wp_uploads_dir = [];

	private $files_dir;

	private $file_name;

	/**
	 * File path.
	 *
	 * Holds the file path.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Content.
	 *
	 * Holds the file content.
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $content;

	/**
	 * @since 2.1.0
	 * @access public
	 * @static
	 */
	public static function get_base_uploads_dir() {
		$wp_upload_dir = self::get_wp_uploads_dir();

		return $wp_upload_dir['basedir'];
	}

	/**
	 * @since 2.1.0
	 * @access public
	 * @static
	 */
	public static function get_base_uploads_url() {
		$wp_upload_dir = self::get_wp_uploads_dir();

		return $wp_upload_dir['baseurl'];
	}

	/**
	 * Use a create function for PhpDoc (@return static).
	 *
	 * @return static
	 */
	public static function create() {
		return Plugin::$instance->files_manager->get( get_called_class(), func_get_args() );
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function __construct( $file_name ) {
		/**
		 * Elementor File Name
		 *
		 * Filters the File name
		 *
		 * @since 2.3.0
		 *
		 * @param string   $file_name
		 * @param object $this The file instance, which inherits Elementor\Core\Files
		 */
		$file_name = Wp_Helper::apply_filters( 'elementor/files/file_name', $file_name, $this );

		$this->set_file_name( $file_name );

		$this->set_files_dir( static::DEFAULT_FILES_DIR );

		$this->set_path();
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function set_files_dir( $files_dir ) {
		$this->files_dir = $files_dir;
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function set_file_name( $file_name ) {
		$this->file_name = $file_name;
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function get_file_name() {
		return $this->file_name;
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function get_url() {
		return Wp_Helper::add_query_arg( [ 'ver' => $this->get_meta( 'time' ) ], self::get_base_uploads_url() . $this->files_dir . $this->file_name );
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function get_content() {
		if ( ! $this->content ) {
			$this->content = $this->parse_content();
		}

		return $this->content;
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function update() {
		$this->update_file();

		$meta = $this->get_meta();

		$meta['time'] = time();

		$this->update_meta( $meta );
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function update_file() {
		$this->content = $this->parse_content();

		if ( $this->content ) {
			$this->write();
		} else {
			$this->delete();
		}
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function write() {
		return file_put_contents( $this->path, $this->content );
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function delete() {
		if ( file_exists( $this->path ) ) {
			unlink( $this->path );
		}

		$this->delete_meta();
	}

	/**
	 * Get meta data.
	 *
	 * Retrieve the CSS file meta data. Returns an array of all the data, or if
	 * custom property is given it will return the property value, or `null` if
	 * the property does not exist.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @param string $property Optional. Custom meta data property. Default is
	 *                         null.
	 *
	 * @return array|null An array of all the data, or if custom property is
	 *                    given it will return the property value, or `null` if
	 *                    the property does not exist.
	 */
	public function get_meta( $property = null ) {
		$meta = array_merge( $this->get_default_meta(), (array) $this->load_meta() );

		if ( $property ) {
			return isset( $meta[ $property ] ) ? $meta[ $property ] : null;
		}

		return $meta;
	}

	/**
	 * @since 2.1.0
	 * @access protected
	 * @abstract
	 */
	abstract protected function parse_content();

	/**
	 * Load meta.
	 *
	 * Retrieve the file meta data.
	 *
	 * @since 2.1.0
	 * @access protected
	 */
	protected function load_meta() {
		return Wp_Helper::get_option( static::META_KEY );
	}

	/**
	 * Update meta.
	 *
	 * Update the file meta data.
	 *
	 * @since 2.1.0
	 * @access protected
	 *
	 * @param array $meta New meta data.
	 */
	protected function update_meta( $meta ) {
		Wp_Helper::update_option( static::META_KEY, $meta );
	}

	/**
	 * Delete meta.
	 *
	 * Delete the file meta data.
	 *
	 * @since 2.1.0
	 * @access protected
	 */
	protected function delete_meta() {
		Wp_Helper::delete_option( static::META_KEY );
	}

	/**
	 * @since 2.1.0
	 * @access protected
	 */
	protected function get_default_meta() {
		return [
			'time' => 0,
		];
	}

	/**
	 * @since 2.1.0
	 * @access private
	 * @static
	 */
	private static function get_wp_uploads_dir() {
		return self::$wp_uploads_dir = [
			'basedir' => AXON_CREATOR_ASSETS_PATH . 'upload/',
			'baseurl' => AXON_CREATOR_ASSETS_URL . 'upload/'
		];
	}

	/**
	 * @since 2.1.0
	 * @access private
	 */
	private function set_path() {
		$dir_path = self::get_base_uploads_dir() . $this->files_dir;

		if ( ! is_dir( $dir_path ) ) {
			mkdir( $dir_path, 0777, true );
		}

		$this->path = $dir_path . $this->file_name;
	}
}
