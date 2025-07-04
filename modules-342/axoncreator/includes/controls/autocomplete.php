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
 * Elementor font control.
 *
 * A base control for creating font control. Displays font select box. The
 * control allows you to set a list of fonts.
 *
 * @since 1.0.0
 */
class Control_Autocomplete extends Base_Data_Control {
	/**
	 * Get axps_autocomplete control type.
	 *
	 * Retrieve the control type, in this case `axps_autocomplete`.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'autocomplete';
	}

	/**
	 * Get axps_autocomplete control default settings.
	 *
	 * Retrieve the default settings of the axps_autocomplete control. Used to return the
	 * default settings while initializing the axps_autocomplete control.
	 *
	 * @since  1.8.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'label_block' => true,
			'multiple'    => false,
			'options'     => [],
			'callback'    => '',
		];
	}

	/**
	 * Enqueue control scripts and styles.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function enqueue() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		Wp_Helper::wp_enqueue_script(
			'autocomplete-control',
			AXON_CREATOR_ASSETS_URL . 'js/control/autocomplete' . $suffix . '.js',
			[
				'jquery',
			],
			'',
			false
		);
	}

	/**
	 * Render axps_autocomplete control output in the editor.
	 *
	 * Used to generate the control HTML in the editor using Underscore JS
	 * template. The variables for the class are available using `data` JS
	 * object.
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<label for="<?php echo Wp_Helper::esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper">
				<# var multiple = ( data.multiple ) ? 'multiple' : ''; #>
				<select id="<?php echo Wp_Helper::esc_attr( $control_uid ); ?>" class="elementor-select2" type="select2" {{ multiple }} data-setting="{{ data.name }}" data-placeholder="<?php echo Wp_Helper::esc_attr__( 'Search', 'axiosy' ); ?>">
					<# _.each( data.options, function( option_title, option_value ) {
					var value = data.controlValue;
					if ( typeof value == 'string' ) {
						var selected = ( option_value === value ) ? 'selected' : '';
					} else if ( null !== value ) {
						var value = _.values( value );
						var selected = ( -1 !== value.indexOf( option_value ) ) ? 'selected' : '';
					}
					#>
					<option {{ selected }} value="{{ option_value }}">{{{ option_title }}}</option>
					<# } ); #>
				</select>
			</div>
		</div>
		<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}
