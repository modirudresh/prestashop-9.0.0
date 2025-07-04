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

namespace AxonCreator\Core\Debug\Classes;

use AxonCreator\Wp_Helper; 

class Htaccess extends Inspection_Base {

	private $message = '';

	public function __construct() {
		$this->message = Wp_Helper::__( 'Your site\'s .htaccess file appears to be missing.', 'elementor' );
	}

	public function run() {
		$permalink_structure = Wp_Helper::get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) ) {
			return true;
		}

		if ( empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$this->message = Wp_Helper::__( 'We failed to recognize your Server software. Please contact your hosting provider.', 'elementor' );
			return false;
		}
		$server = strtoupper( $_SERVER['SERVER_SOFTWARE'] );

		if ( strstr( $server, 'APACHE' ) ) {
			$htaccess_file = get_home_path() . '.htaccess';
			return file_exists( $htaccess_file );
		}
		return true;
	}

	public function get_name() {
		return 'apache-htaccess';
	}

	public function get_message() {
		return $this->message;
	}

	public function get_help_doc_url() {
		return 'https://api.axonviz.com/preview-not-loaded/#htaccess';
	}
}
