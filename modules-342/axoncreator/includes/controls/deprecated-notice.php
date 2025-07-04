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
 * Elementor Deprecated Notice control.
 *
 * A base control specific for creating Deprecation Notices control.
 * Displays a warning notice in the panel.
 *
 * @since 2.6.0
 */
class Control_Deprecated_Notice extends Base_UI_Control {

	/**
	 * Get deprecated-notice control type.
	 *
	 * Retrieve the control type, in this case `deprecated_notice`.
	 *
	 * @since 2.6.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'deprecated_notice';
	}

	/**
	 * Render deprecated notice control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since 2.6.0
	 * @access public
	 */
	public function content_template() {
		?>
		<# if ( data.label ) { #>
		<span class="elementor-control-title">{{{ data.label }}}</span>
		<#
		}
		let notice = elementor.translate( 'deprecated_notice', [ data.widget, data.plugin, data.since ] );
		if ( data.replacement ) {
			notice += '<br>' + elementor.translate( 'deprecated_notice_replacement', [ data.replacement ] );
		}
		if ( data.last ) {
			notice += '<br>' + elementor.translate( 'deprecated_notice_last', [ data.widget, data.plugin, data.last ] );
		}
		#>
		<div class="elementor-control-deprecated-notice elementor-panel-alert elementor-panel-alert-warning">{{{ notice }}}</div>
		<?php
	}

	/**
	 * Get deprecated-notice control default settings.
	 *
	 * Retrieve the default settings of the deprecated notice control. Used to return the
	 * default settings while initializing the deprecated notice control.
	 *
	 * @since 2.6.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'widget' => '', // Widgets name
			'since' => '', // Plugin version widget was deprecated
			'last' => '', // Plugin version in which the widget will be removed
			'plugin' => '', // Plugin's title
			'replacement' => '', // Widget replacement
		];
	}
}
