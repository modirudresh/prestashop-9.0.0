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

use AxonCreator\Base_Data_Control;
use AxonCreator\Controls_Manager;
use AxonCreator\Controls_Stack;
use AxonCreator\Core\Files\Base as Base_File;
use AxonCreator\Core\DynamicTags\Manager;
use AxonCreator\Core\DynamicTags\Tag;
use AxonCreator\Element_Base;
use AxonCreator\Plugin;
use AxonCreator\Core\Responsive\Responsive;
use AxonCreator\Stylesheet;
use AxonCreator\Icons_Manager;
use AxonCreator\Wp_Helper;

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor CSS file.
 *
 * Elementor CSS file handler class is responsible for generating CSS files.
 *
 * @since 1.2.0
 * @abstract
 */
abstract class Base extends Base_File {

	/**
	 * Elementor CSS file generated status.
	 *
	 * The parsing result after generating CSS file.
	 */
	const CSS_STATUS_FILE = 'file';

	/**
	 * Elementor inline CSS status.
	 *
	 * The parsing result after generating inline CSS.
	 */
	const CSS_STATUS_INLINE = 'inline';

	/**
	 * Elementor CSS empty status.
	 *
	 * The parsing result when an empty CSS returned.
	 */
	const CSS_STATUS_EMPTY = 'empty';

	/**
	 * Fonts.
	 *
	 * Holds the list of fonts.
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $fonts = [];

	private $icons_fonts = [];

	/**
	 * Stylesheet object.
	 *
	 * Holds the CSS file stylesheet instance.
	 *
	 * @access protected
	 *
	 * @var Stylesheet
	 */
	protected $stylesheet_obj;

	/**
	 * Printed.
	 *
	 * Holds the list of printed files.
	 *
	 * @access protected
	 *
	 * @var array
	 */
	private static $printed = [];

	/**
	 * Get CSS file name.
	 *
	 * Retrieve the CSS file name.
	 *
	 * @since 1.6.0
	 * @access public
	 * @abstract
	 */
	abstract public function get_name();

	/**
	 * CSS file constructor.
	 *
	 * Initializing Elementor CSS file.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function __construct( $file_name ) {
		parent::__construct( $file_name );

		$this->init_stylesheet();
	}

	/**
	 * Use external file.
	 *
	 * Whether to use external CSS file of not. When there are new schemes or settings
	 * updates.
	 *
	 * @since 1.9.0
	 * @access protected
	 *
	 * @return bool True if the CSS requires an update, False otherwise.
	 */
	protected function use_external_file() {
		return 'external' == Wp_Helper::get_option( 'elementor_css_print_method' );
	}

	/**
	 * Update the CSS file.
	 *
	 * Delete old CSS, parse the CSS, save the new file and update the database.
	 *
	 * This method also sets the CSS status to be used later on in the render posses.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function update() {
		$this->update_file();

		$meta = $this->get_meta();

		$meta['time'] = time();

		$content = $this->get_content();

		if ( empty( $content ) ) {
			$meta['status'] = self::CSS_STATUS_EMPTY;
			$meta['css'] = '';
		} else {
			$use_external_file = $this->use_external_file();

			if ( $use_external_file ) {
				$meta['status'] = self::CSS_STATUS_FILE;
			} else {
				$meta['status'] = self::CSS_STATUS_INLINE;
				$meta['css'] = $content;
			}
		}

		$this->update_meta( $meta );
	}

	/**
	 * @since 2.1.0
	 * @access public
	 */
	public function write() {
		if ( $this->use_external_file() ) {
			parent::write();
		}
	}
	
	/**
	 * @since 3.0.0
	 * @access public
	 */
	public function delete() {
		if ( $this->use_external_file() ) {
			parent::delete();
		} else {
			$this->delete_meta();
		}
	}


	/**
	 * Enqueue CSS.
	 *
	 * Either enqueue the CSS file in Elementor or add inline style.
	 *
	 * This method is also responsible for loading the fonts.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function enqueue() {
		$handle_id = $this->get_file_handle_id();

		if ( isset( self::$printed[ $handle_id ] ) ) {
			return;
		}

		self::$printed[ $handle_id ] = true;

		$meta = $this->get_meta();

		if ( self::CSS_STATUS_EMPTY === $meta['status'] ) {
			return;
		}

		// First time after clear cache and etc.
		if ( '' === $meta['status'] || $this->is_update_required() ) {
			$this->update();

			$meta = $this->get_meta();
		}

		if ( self::CSS_STATUS_INLINE === $meta['status'] ) {
			$dep = $this->get_inline_dependency();
			printf( '<style id="%1$s">%2$s</style>', $this->get_file_handle_id(), $meta['css'] ); // XSS ok.
		} elseif ( self::CSS_STATUS_FILE === $meta['status'] ) { // Re-check if it's not empty after CSS update.
			printf( '<link rel="stylesheet" href="%s" type="text/css" media="all">', $this->get_url() ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}

		// Handle fonts.
		if ( ! empty( $meta['fonts'] ) ) {
			foreach ( $meta['fonts'] as $font ) {
				Plugin::$instance->frontend->enqueue_font( $font );
			}
		}

		if ( ! empty( $meta['icons'] ) ) {
			$icons_types = Icons_Manager::get_icon_manager_tabs();
			foreach ( $meta['icons'] as $icon_font ) {
				if ( ! isset( $icons_types[ $icon_font ] ) ) {
					continue;
				}
				Plugin::$instance->frontend->enqueue_font( $icon_font );
			}
		}

		$name = $this->get_name();

		/**
		 * Enqueue CSS file.
		 *
		 * Fires when CSS file is enqueued on Elementor.
		 *
		 * The dynamic portion of the hook name, `$name`, refers to the CSS file name.
		 *
		 * @since 1.9.0
		 * @deprecated 2.0.0 Use `elementor/css-file/{$name}/enqueue` action instead.
		 *
		 * @param Base $this The current CSS file.
		 */
		//Wp_Helper::do_action_deprecated( "elementor/{$name}-css-file/enqueue", [ $this ], '2.0.0', "elementor/css-file/{$name}/enqueue" );

		/**
		 * Enqueue CSS file.
		 *
		 * Fires when CSS file is enqueued on Elementor.
		 *
		 * The dynamic portion of the hook name, `$name`, refers to the CSS file name.
		 *
		 * @since 2.0.0
		 *
		 * @param Base $this The current CSS file.
		 */
		Wp_Helper::do_action( "elementor/css-file/{$name}/enqueue", $this );
	}

	/**
	 * Print CSS.
	 *
	 * Output the final CSS inside the `<style>` tags and all the frontend fonts in
	 * use.
	 *
	 * @since 1.9.4
	 * @access public
	 */
	public function print_css() {
		printf( '<style>%s</style>', $this->get_content() ); // XSS ok.
		
		$meta = $this->get_default_meta();

		// Handle fonts.
		if ( ! empty( $meta['fonts'] ) ) {
			foreach ( $meta['fonts'] as $font ) {
				Plugin::$instance->frontend->enqueue_font( $font );
			}
		}

		if ( ! empty( $meta['icons'] ) ) {
			$icons_types = Icons_Manager::get_icon_manager_tabs();
			foreach ( $meta['icons'] as $icon_font ) {
				if ( ! isset( $icons_types[ $icon_font ] ) ) {
					continue;
				}
				Plugin::$instance->frontend->enqueue_font( $icon_font );
			}
		}
	
		Plugin::$instance->frontend->print_fonts_links();
	}

	/**
	 * Add control rules.
	 *
	 * Parse the CSS for all the elements inside any given control.
	 *
	 * This method recursively renders the CSS for all the selectors in the control.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @param array    $control        The controls.
	 * @param array    $controls_stack The controls stack.
	 * @param callable $value_callback Callback function for the value.
	 * @param array    $placeholders   Placeholders.
	 * @param array    $replacements   Replacements.
	 */
	public function add_control_rules( array $control, array $controls_stack, callable $value_callback, array $placeholders, array $replacements ) {
		$value = call_user_func( $value_callback, $control );

		if ( null === $value || empty( $control['selectors'] ) ) {
			return;
		}

		foreach ( $control['selectors'] as $selector => $css_property ) {
			try {
				$output_css_property = preg_replace_callback( '/\{\{(?:([^.}]+)\.)?([^}| ]*)(?: *\|\| *(?:([^.}]+)\.)?([^}| ]*) *)*}}/', function( $matches ) use ( $control, $value_callback, $controls_stack, $value, $css_property ) {
					$external_control_missing = $matches[1] && ! isset( $controls_stack[ $matches[1] ] );

					$parsed_value = '';

					if ( ! $external_control_missing ) {
						$parsed_value = $this->parse_property_placeholder( $control, $value, $controls_stack, $value_callback, $matches[2], $matches[1] );
					}

					if ( '' === $parsed_value ) {
						if ( isset( $matches[4] ) ) {
							$parsed_value = $matches[4];

							$is_string_value = preg_match( '/^([\'"])(.*)\1$/', $parsed_value, $string_matches );

							if ( $is_string_value ) {
								$parsed_value = $string_matches[2];
							} elseif ( ! is_numeric( $parsed_value ) ) {
								if ( $matches[3] && ! isset( $controls_stack[ $matches[3] ] ) ) {
									return '';
								}

								$parsed_value = $this->parse_property_placeholder( $control, $value, $controls_stack, $value_callback, $matches[4], $matches[3] );
							}
						}

						if ( '' === $parsed_value ) {
							if ( $external_control_missing ) {
								return '';
							}

							throw new \Exception();
						}
					}

					return $parsed_value;
				}, $css_property );
			} catch ( \Exception $e ) {
				return;
			}

			if ( ! $output_css_property ) {
				continue;
			}

			$device_pattern = '/^(?:\([^\)]+\)){1,2}/';

			preg_match( $device_pattern, $selector, $device_rules );

			$query = [];

			if ( $device_rules ) {
				$selector = preg_replace( $device_pattern, '', $selector );

				preg_match_all( '/\(([^\)]+)\)/', $device_rules[0], $pure_device_rules );

				$pure_device_rules = $pure_device_rules[1];

				foreach ( $pure_device_rules as $device_rule ) {
					if ( Element_Base::RESPONSIVE_DESKTOP === $device_rule ) {
						continue;
					}

					$device = preg_replace( '/\+$/', '', $device_rule );

					$endpoint = $device === $device_rule ? 'max' : 'min';

					$query[ $endpoint ] = $device;
				}
			}

			$parsed_selector = str_replace( $placeholders, $replacements, $selector );

			if ( ! $query && ! empty( $control['responsive'] ) ) {
				$query = array_intersect_key( $control['responsive'], array_flip( [ 'min', 'max' ] ) );

				if ( ! empty( $query['max'] ) && Element_Base::RESPONSIVE_DESKTOP === $query['max'] ) {
					unset( $query['max'] );
				}
			}

			$this->stylesheet_obj->add_rules( $parsed_selector, $output_css_property, $query );
		}
	}

	/**
	 * @param array    $control
	 * @param mixed    $value
	 * @param array    $controls_stack
	 * @param callable $value_callback
	 * @param string   $placeholder
	 * @param string   $parser_control_name
	 *
	 * @return string
	 */
	public function parse_property_placeholder( array $control, $value, array $controls_stack, $value_callback, $placeholder, $parser_control_name = null ) {
		if ( $parser_control_name ) {
			$control = $controls_stack[ $parser_control_name ];

			$value = call_user_func( $value_callback, $control );
		}

		if ( Controls_Manager::FONT === $control['type'] ) {
			$this->fonts[] = $value;
		}

		/** @var Base_Data_Control $control_obj */
		$control_obj = Plugin::$instance->controls_manager->get_control( $control['type'] );

		return (string) $control_obj->get_style_value( $placeholder, $value, $control );
	}

	/**
	 * Get the fonts.
	 *
	 * Retrieve the list of fonts.
	 *
	 * @since 1.9.0
	 * @access public
	 *
	 * @return array Fonts.
	 */
	public function get_fonts() {
		return $this->fonts;
	}

	/**
	 * Get CSS.
	 *
	 * Retrieve the CSS. If the CSS is empty, parse it again.
	 *
	 * @since 1.2.0
	 * @access public
	 * @deprecated 2.1.0 Use `CSS_File::get_content()` method instead
	 *
	 * @return string The CSS.
	 */
	public function get_css() {
		_deprecated_function( __METHOD__, '2.1.0', __CLASS__ . '::get_content()' );

		return $this->get_content();
	}

	/**
	 * Get stylesheet.
	 *
	 * Retrieve the CSS file stylesheet instance.
	 *
	 * @since 1.2.0
	 * @access public
	 *
	 * @return Stylesheet The stylesheet object.
	 */
	public function get_stylesheet() {
		return $this->stylesheet_obj;
	}

	/**
	 * Add controls stack style rules.
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
		if ( ! $all_controls ) {
			$all_controls = $controls_stack->get_controls();
		}

		$parsed_dynamic_settings = $controls_stack->parse_dynamic_settings( $values, $controls );

		foreach ( $controls as $control ) {
			if ( ! empty( $control['style_fields'] ) ) {
				$this->add_repeater_control_style_rules( $controls_stack, $control, $values[ $control['name'] ], $placeholders, $replacements );
			}

			if ( ! empty( $control[ Manager::DYNAMIC_SETTING_KEY ][ $control['name'] ] ) ) {
				$this->add_dynamic_control_style_rules( $control, $control[ Manager::DYNAMIC_SETTING_KEY ][ $control['name'] ] );
			}

			if ( Controls_Manager::ICONS === $control['type'] ) {
				$this->icons_fonts[] = $values[ $control['name'] ]['library'];
			}

			if ( ! empty( $parsed_dynamic_settings[ Manager::DYNAMIC_SETTING_KEY ][ $control['name'] ] ) ) {
				unset( $parsed_dynamic_settings[ $control['name'] ] );
				continue;
			}

			if ( empty( $control['selectors'] ) ) {
				continue;
			}

			$this->add_control_style_rules( $control, $parsed_dynamic_settings, $all_controls, $placeholders, $replacements );
		}
	}

	/**
	 * Get file handle ID.
	 *
	 * Retrieve the file handle ID.
	 *
	 * @since 1.2.0
	 * @access protected
	 * @abstract
	 *
	 * @return string CSS file handle ID.
	 */
	abstract protected function get_file_handle_id();

	/**
	 * Render CSS.
	 *
	 * Parse the CSS.
	 *
	 * @since 1.2.0
	 * @access protected
	 * @abstract
	 */
	abstract protected function render_css();

	protected function get_default_meta() {
		return array_merge( parent::get_default_meta(), [
			'fonts' => array_unique( $this->fonts ),
			'icons' => array_unique( $this->icons_fonts ),
			'status' => '',
		] );
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
		return [];
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
		return '';
	}

	/**
	 * Is update required.
	 *
	 * Whether the CSS requires an update. When there are new schemes or settings
	 * updates.
	 *
	 * @since 1.2.0
	 * @access protected
	 *
	 * @return bool True if the CSS requires an update, False otherwise.
	 */
	protected function is_update_required() {
		return false;
	}

	/**
	 * Parse CSS.
	 *
	 * Parsing the CSS file.
	 *
	 * @since 1.2.0
	 * @access protected
	 */
	protected function parse_content() {
		$this->render_css();

		$name = $this->get_name();

		/**
		 * Parse CSS file.
		 *
		 * Fires when CSS file is parsed on Elementor.
		 *
		 * The dynamic portion of the hook name, `$name`, refers to the CSS file name.
		 *
		 * @since 1.2.0
		 * @deprecated 2.0.0 Use `elementor/css-file/{$name}/parse` action instead.
		 *
		 * @param Base $this The current CSS file.
		 */
		//Wp_Helper::do_action_deprecated( "elementor/{$name}-css-file/parse", [ $this ], '2.0.0', "elementor/css-file/{$name}/parse" );

		/**
		 * Parse CSS file.
		 *
		 * Fires when CSS file is parsed on Elementor.
		 *
		 * The dynamic portion of the hook name, `$name`, refers to the CSS file name.
		 *
		 * @since 2.0.0
		 *
		 * @param Base $this The current CSS file.
		 */
		Wp_Helper::do_action( "elementor/css-file/{$name}/parse", $this );

		return $this->stylesheet_obj->__toString();
	}

	/**
	 * Add control style rules.
	 *
	 * Register new style rules for the control.
	 *
	 * @since 1.6.0
	 * @access private
	 *
	 * @param array $control      The control.
	 * @param array $values       Values array.
	 * @param array $controls     The controls stack.
	 * @param array $placeholders Placeholders.
	 * @param array $replacements Replacements.
	 */
	protected function add_control_style_rules( array $control, array $values, array $controls, array $placeholders, array $replacements ) {
		$this->add_control_rules(
			$control, $controls, function( $control ) use ( $values ) {
				return $this->get_style_control_value( $control, $values );
			}, $placeholders, $replacements
		);
	}

	/**
	 * Get style control value.
	 *
	 * Retrieve the value of the style control for any give control and values.
	 *
	 * It will retrieve the control name and return the style value.
	 *
	 * @since 1.6.0
	 * @access private
	 *
	 * @param array $control The control.
	 * @param array $values  Values array.
	 *
	 * @return mixed Style control value.
	 */
	private function get_style_control_value( array $control, array $values ) {
		$value = $values[ $control['name'] ];

		if ( isset( $control['selectors_dictionary'][ $value ] ) ) {
			$value = $control['selectors_dictionary'][ $value ];
		}

		if ( ! is_numeric( $value ) && ! is_float( $value ) && empty( $value ) ) {
			return null;
		}

		return $value;
	}

	/**
	 * Init stylesheet.
	 *
	 * Initialize CSS file stylesheet by creating a new `Stylesheet` object and register new
	 * breakpoints for the stylesheet.
	 *
	 * @since 1.2.0
	 * @access private
	 */
	private function init_stylesheet() {
		$this->stylesheet_obj = new Stylesheet();

		$breakpoints = Responsive::get_breakpoints();

		$this->stylesheet_obj
			->add_device( 'mobile', 0 )
			->add_device( 'tablet', $breakpoints['md'] )
			->add_device( 'desktop', $breakpoints['lg'] );
	}

	/**
	 * Add repeater control style rules.
	 *
	 * Register new style rules for the repeater control.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param Controls_Stack $controls_stack   The control stack.
	 * @param array          $repeater_control The repeater control.
	 * @param array          $repeater_values  Repeater values array.
	 * @param array          $placeholders     Placeholders.
	 * @param array          $replacements     Replacements.
	 */
	protected function add_repeater_control_style_rules( Controls_Stack $controls_stack, array $repeater_control, array $repeater_values, array $placeholders, array $replacements ) {
		$placeholders = array_merge( $placeholders, [ '{{CURRENT_ITEM}}' ] );

		foreach ( $repeater_control['style_fields'] as $index => $item ) {
			$this->add_controls_stack_style_rules(
				$controls_stack,
				$item,
				$repeater_values[ $index ],
				$placeholders,
				array_merge( $replacements, [ '.elementor-repeater-item-' . $repeater_values[ $index ]['_id'] ] ),
				$repeater_control['fields']
			);
		}
	}

	/**
	 * Add dynamic control style rules.
	 *
	 * Register new style rules for the dynamic control.
	 *
	 * @since 2.0.0
	 * @access private
	 *
	 * @param array  $control The control.
	 * @param string $value   The value.
	 */
	protected function add_dynamic_control_style_rules( array $control, $value ) {
		Plugin::$instance->dynamic_tags->parse_tags_text( $value, $control, function( $id, $name, $settings ) {
			$tag = Plugin::$instance->dynamic_tags->create_tag( $id, $name, $settings );

			if ( ! $tag instanceof Tag ) {
				return;
			}

			$this->add_controls_stack_style_rules( $tag, $tag->get_style_controls(), $tag->get_active_settings(), [ '{{WRAPPER}}' ], [ '#elementor-tag-' . $id ] );
		} );
	}
}
