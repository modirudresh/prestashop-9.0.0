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

namespace AxonCreator\TemplateLibrary\Classes;

use AxonCreator\Wp_Helper; 
use AxonCreator\Utils;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor template library import images.
 *
 * Elementor template library import images handler class is responsible for
 * importing remote images used by the template library.
 *
 * @since 1.0.0
 */
class Import_Images {

	/**
	 * Replaced images IDs.
	 *
	 * The IDs of all the new imported images. An array containing the old
	 * attachment ID and the new attachment ID generated after the import.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @var array
	 */
	private $_replace_image_ids = [];
	
    public static $allowed_ext = [ 'jpg', 'png', 'jpe', 'jpeg', 'gif', 'tiff', 'tif', 'bmp', 'svg' ];
	
    private static $dir = 'cms/';
	
    private static $placeholder = 'placeholder.png';
	
    private static $imported = [];
	
	/**
	 * Get image hash.
	 *
	 * Retrieve the sha1 hash of the image URL.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param string $attachment_url The attachment URL.
	 *
	 * @return string Image hash.
	 */
	private function get_hash_image( $attachment_url ) {
		return sha1( $attachment_url );
	}

	/**
	 * Get saved image.
	 *
	 * Retrieve new image ID, if the image has a new ID after the import.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param array $attachment The attachment.
	 *
	 * @return false|array New image ID  or false.
	 */
	private function get_saved_image( $attachment ) {
		global $wpdb;

		if ( isset( $this->_replace_image_ids[ $attachment['id'] ] ) ) {
			return $this->_replace_image_ids[ $attachment['id'] ];
		}

		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT `post_id` FROM `' . $wpdb->postmeta . '`
					WHERE `meta_key` = \'_elementor_source_image_hash\'
						AND `meta_value` = %s
				;',
				$this->get_hash_image( $attachment['url'] )
			)
		);

		if ( $post_id ) {
			$new_attachment = [
				'id' => $post_id,
				'url' => wp_get_attachment_url( $post_id ),
			];
			$this->_replace_image_ids[ $attachment['id'] ] = $new_attachment;

			return $new_attachment;
		}

		return false;
	}

	/**
	 * Import image.
	 *
	 * Import a single image from a remote server, upload the image WordPress
	 * uploads folder, create a new attachment in the database and updates the
	 * attachment metadata.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $attachment The attachment.
	 *
	 * @return false|array Imported image data, or false.
	 */
	public function import( $attachment ) {

		$url = $attachment['url'];

		if ( isset( self::$imported[$url] ) ) {
			return self::$imported[$url];
		}

		// Extract the file name and extension from the url.
		$filename = basename( $url );
		
		if ( self::$placeholder == $filename ) {
		    return self::$imported[$url] = false;
		}

		$file_content = Wp_Helper::wp_remote_retrieve_body( Wp_Helper::wp_remote_get( $attachment['url'] ) );

		if ( empty( $file_content ) ) {
			return self::$imported[$url] = false;
		}
		
		$file_info = pathinfo( $filename );
		
		if ( in_array( $file_info['extension'], self::$allowed_ext ) ) {
		    $file_path = _PS_IMG_DIR_ . self::$dir . $filename;

		    if ( file_exists( $file_path ) ) {
		        $existing_content = \Tools::file_get_contents( $file_path );

		        if ( $file_content === $existing_content ) {
		            return self::$imported[$url] = [
		                'id' => 0,
		                'url' => _PS_IMG_ . self::$dir . $filename,
		            ];
		        }

		        $filename = $file_info['filename'] . '_' . Utils::generate_random_string() . '.' . $file_info['extension'];
		        $file_path = _PS_IMG_DIR_ . self::$dir . $filename;
		    }

		    if ( file_put_contents( $file_path, $file_content ) ) {
		        return self::$imported[$url] = [
		            'id' => 0,
		            'url' => _PS_IMG_ . self::$dir . $filename,
		        ];
		    }
		}

		return $attachment;
	}

	/**
	 * Template library import images constructor.
	 *
	 * Initializing the images import class used by the template library through
	 * the WordPress Filesystem API.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() { }
}
