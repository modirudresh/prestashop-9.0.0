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

class Theme_Missing extends Inspection_Base {

	public function run() {
		$theme = wp_get_theme();
		return $theme->exists();
	}

	public function get_name() {
		return 'theme-missing';
	}

	public function get_message() {
		return Wp_Helper::__( 'Some of your theme files are missing.', 'elementor' );
	}

	public function get_help_doc_url() {
		return 'https://api.axonviz.com/preview-not-loaded/#theme-files';
	}
}
