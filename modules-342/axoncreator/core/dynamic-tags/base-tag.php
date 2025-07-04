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

use AxonCreator\Controls_Stack;
use AxonCreator\Plugin;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor base tag.
 *
 * An abstract class to register new Elementor tags.
 *
 * @since 2.0.0
 * @abstract
 */
abstract class Base_Tag extends Controls_Stack {

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 */
	final public static function get_type() {
		return 'tag';
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @abstract
	 */
	abstract public function get_categories();

	/**
	 * @since 2.0.0
	 * @access public
	 * @abstract
	 */
	abstract public function get_group();

	/**
	 * @since 2.0.0
	 * @access public
	 * @abstract
	 */
	abstract public function get_title();

	/**
	 * @since 2.0.0
	 * @access public
	 * @abstract
	 *
	 * @param array $options
	 */
	abstract public function get_content( array $options = [] );

	/**
	 * @since 2.0.0
	 * @access public
	 * @abstract
	 */
	abstract public function get_content_type();

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_panel_template_setting_key() {
		return '';
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function is_settings_required() {
		return false;
	}

	/**
	 * @since 2.0.9
	 * @access public
	 */
	public function get_editor_config() {
		 ob_start();

		$this->print_panel_template();

		$panel_template = ob_get_clean();

		return [
			'name' => $this->get_name(),
			'title' => $this->get_title(),
			'panel_template' => $panel_template,
			'categories' => $this->get_categories(),
			'group' => $this->get_group(),
			'controls' => $this->get_controls(),
			'content_type' => $this->get_content_type(),
			'settings_required' => $this->is_settings_required(),
		];
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function print_panel_template() {
		$panel_template_setting_key = $this->get_panel_template_setting_key();

		if ( ! $panel_template_setting_key ) {
			return;
		}
		?><#
		var key = <?php echo Wp_Helper::esc_html( $panel_template_setting_key ); ?>;

		if ( key ) {
			var settingsKey = "<?php echo Wp_Helper::esc_html( $panel_template_setting_key ); ?>";

			/*
			 * If the tag has controls,
			 * and key is an existing control (and not an old one),
			 * and the control has options (select/select2),
			 * and the key is an existing option (and not in a group or an old one).
			 */
			if ( controls && controls[settingsKey] ) {
				var controlSettings = controls[settingsKey];

				if ( controlSettings.options && controlSettings.options[ key ] ) {
					key = controlSettings.options[ key ];
				} else if ( controlSettings.groups ) {
					var label = _.filter( _.pluck( _.pluck( controls.key.groups, 'options' ), key ) );

					if ( label[0] ) {
						key = label[0];
					}
				}
			}

			print( '(' + key + ')' );
		}
		#>
		<?php
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	final public function get_unique_name() {
		return 'tag-' . $this->get_name();
	}

	/**
	 * @since 2.0.0
	 * @access protected
	 */
	protected function register_advanced_section() {}

	/**
	 * @since 2.0.0
	 * @access protected
	 */
	final protected function init_controls() {
		Plugin::$instance->controls_manager->open_stack( $this );

		$this->start_controls_section( 'settings', [
			'label' => Wp_Helper::__( 'Settings', 'elementor' ),
		] );

		$this->_register_controls();

		$this->end_controls_section();

		// If in fact no controls were registered, empty the stack
		if ( 1 === count( Plugin::$instance->controls_manager->get_stacks( $this->get_unique_name() )['controls'] ) ) {
			Plugin::$instance->controls_manager->open_stack( $this );
		}

		$this->register_advanced_section();
	}
}
