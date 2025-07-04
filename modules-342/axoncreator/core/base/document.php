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

namespace AxonCreator\Core\Base;

use AxonCreator\Core\Files\CSS\Post as Post_CSS;
use AxonCreator\Core\Utils\Exceptions;
use AxonCreator\Plugin;
use AxonCreator\DB;
use AxonCreator\Controls_Manager;
use AxonCreator\Controls_Stack;
use AxonCreator\User;
use AxonCreator\Core\Settings\Manager as SettingsManager;
use AxonCreator\Utils;
use AxonCreator\Widget_Base;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Elementor document.
 *
 * An abstract class that provides the needed properties and methods to
 * manage and handle documents in inheriting classes.
 *
 * @since 2.0.0
 * @abstract
 */
abstract class Document extends Controls_Stack {

	/**
	 * Document type meta key.
	 */
	const TYPE_META_KEY = '_elementor_template_type';
	const PAGE_META_KEY = '_elementor_page_settings';
	const ELEMENTS_USAGE_META_KEY = '_elementor_elements_usage';
	const ELEMENTS_USAGE_OPTION_NAME = 'elementor_elements_usage';

	private $main_id;

	private static $properties = [];

	/**
	 * Document post data.
	 *
	 * Holds the document post data.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @var \WP_Post WordPress post data.
	 */
	protected $post;

	/**
	 * @since 2.1.0
	 * @access protected
	 * @static
	 */
	protected static function get_editor_panel_categories() {
		return Plugin::$instance->elements_manager->get_categories();
	}

	/**
	 * Get properties.
	 *
	 * Retrieve the document properties.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return array Document properties.
	 */
	public static function get_properties() {
		return [
			'is_editable' => true,
		];
	}

	/**
	 * @since 2.1.0
	 * @access public
	 * @static
	 */
	public static function get_editor_panel_config() {
		return [
			'widgets_settings' => [],
			'elements_categories' => static::get_editor_panel_categories(),
			'messages' => [
				/* translators: %s: the document title. */
				'publish_notification' => sprintf( Wp_Helper::__( 'Hurray! Your %s is live.', 'elementor' ), static::get_title() ),
			],
		];
	}

	/**
	 * Get element title.
	 *
	 * Retrieve the element title.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return string Element title.
	 */
	public static function get_title() {
		return Wp_Helper::__( 'Document', 'elementor' );
	}

	/**
	 * Get property.
	 *
	 * Retrieve the document property.
	 *
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @param string $key The property key.
	 *
	 * @return mixed The property value.
	 */
	public static function get_property( $key ) {
		$id = static::get_class_full_name();

		if ( ! isset( self::$properties[ $id ] ) ) {
			self::$properties[ $id ] = static::get_properties();
		}

		return self::get_items( self::$properties[ $id ], $key );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 */
	public static function get_class_full_name() {
		return get_called_class();
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_unique_name() {
		return $this->get_name() . '-' . Wp_Helper::$id_post;
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function get_post_type_title() {
		$post_type_object = get_post_type_object( $this->post->post_type );

		return $post_type_object->labels->singular_name;
	}

	/**
	 * @since 2.0.12
	 * @deprecated 2.4.0 Use `Document::get_remote_library_config()` instead
	 * @access public
	 */
	public function get_remote_library_type() {
		// _deprecated_function( __METHOD__, '2.4.0', __CLASS__ . '::get_remote_library_config()' );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_main_id() {
		if ( ! $this->main_id ) {
			$post_id = $this->post->ID;

			$parent_post_id = wp_is_post_revision( $post_id );

			if ( $parent_post_id ) {
				$post_id = $parent_post_id;
			}

			$this->main_id = $post_id;
		}

		return $this->main_id;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param $data
	 *
	 * @throws \Exception If the widget was not found.
	 *
	 * @return string
	 */
	public function render_element( $data ) {
		// Start buffering
		ob_start();

		/** @var Widget_Base $widget */
		$widget = Plugin::$instance->elements_manager->create_element_instance( $data );

		if ( ! $widget ) {
			throw new \Exception( 'Widget not found.' );
		}

		$widget->render_content();

		$render_html = ob_get_clean();

		return $render_html;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_main_post() {
		return get_post( $this->get_main_id() );
	}

	/**
	 * @since 2.0.6
	 * @deprecated 2.4.0 Use `Document::get_container_attributes()` instead
	 * @access public
	 */
	public function get_container_classes() {
		// _deprecated_function( __METHOD__, '2.4.0', __CLASS__ . '::get_container_attributes()' );

		return '';
	}

	public function get_container_attributes() {
		$id = Wp_Helper::$id_post;

		$attributes = [
			'data-elementor-type' => $this->get_name(),
			'data-elementor-id' => $id,
			'class' => 'elementor elementor-' . $id,
		];
		
		if ( ! Plugin::$instance->preview->is_preview_mode( $id ) ) {
			$attributes['data-elementor-settings'] = json_encode( $this->get_frontend_settings() );
		}

		return $attributes;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_wp_preview_url() {
		$main_post_id = $this->get_main_id();

		$url = get_preview_post_link(
			$main_post_id,
			[
				'preview_id' => $main_post_id,
				'preview_nonce' => wp_create_nonce( 'post_preview_' . $main_post_id ),
			]
		);

		/**
		 * Document "WordPress preview" URL.
		 *
		 * Filters the WordPress preview URL.
		 *
		 * @since 2.0.0
		 *
		 * @param string   $url  WordPress preview URL.
		 * @param Document $this The document instance.
		 */
		$url = Wp_Helper::apply_filters( 'elementor/document/urls/wp_preview', $url, $this );

		return $url;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_exit_to_dashboard_url() {
		$url = get_edit_post_link( $this->get_main_id(), 'raw' );

		/**
		 * Document "exit to dashboard" URL.
		 *
		 * Filters the "Exit To Dashboard" URL.
		 *
		 * @since 2.0.0
		 *
		 * @param string   $url  The exit URL
		 * @param Document $this The document instance.
		 */
		$url = Wp_Helper::apply_filters( 'elementor/document/urls/exit_to_dashboard', $url, $this );

		return $url;
	}

	/**
	 * Get auto-saved post revision.
	 *
	 * Retrieve the auto-saved post revision that is newer than current post.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 *
	 * @return bool|Document
	 */

	public function get_newer_autosave() {
		$autosave = $this->get_autosave();

		// Detect if there exists an autosave newer than the post.
		if ( $autosave && mysql2date( 'U', $autosave->get_post()->post_modified_gmt, false ) > mysql2date( 'U', $this->post->post_modified_gmt, false ) ) {
			return $autosave;
		}

		return false;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function is_autosave() {
		return wp_is_post_autosave( Wp_Helper::$id_post );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $user_id
	 * @param bool $create
	 *
	 * @return bool|Document
	 */
	public function get_autosave( $user_id = 0, $create = false ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$autosave_id = $this->get_autosave_id( $user_id );

		if ( $autosave_id ) {
			$document = Plugin::$instance->documents->get( $autosave_id );
		} elseif ( $create ) {
			$autosave_id = wp_create_post_autosave( [
				'post_ID' => $this->post->ID,
				'post_type' => $this->post->post_type,
				'post_title' => $this->post->post_title,
				'post_excerpt' => $this->post->post_excerpt,
				// Hack to cause $autosave_is_different=true in `wp_create_post_autosave`.
				'post_content' => '<!-- Created With Elementor -->',
				'post_modified' => current_time( 'mysql' ),
			] );

			Plugin::$instance->db->copy_elementor_meta( $this->post->ID, $autosave_id );

			$document = Plugin::$instance->documents->get( $autosave_id );
			$document->save_template_type();
		} else {
			$document = false;
		}

		return $document;
	}

	/**
	 * Add/Remove edit link in dashboard.
	 *
	 * Add or remove an edit link to the post/page action links on the post/pages list table.
	 *
	 * Fired by `post_row_actions` and `page_row_actions` filters.
	 *
	 * @access public
	 *
	 * @param array    $actions An array of row action links.
	 *
	 * @return array An updated array of row action links.
	 */
	public function filter_admin_row_actions( $actions ) {
		if ( $this->is_built_with_elementor() && $this->is_editable_by_current_user() ) {
			$actions['edit_with_elementor'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				$this->get_edit_url(),
				Wp_Helper::__( 'Edit with Elementor', 'elementor' )
			);
		}

		return $actions;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function is_editable_by_current_user() {
		return self::get_property( 'is_editable' ) && User::is_current_user_can_edit( $this->get_main_id() );
	}

	/**
	 * @since 2.0.0
	 * @access protected
	 */
	protected function _get_initial_config() {
		return [
			'id' => $this->get_main_id(),
			'type' => $this->get_name(),
			'version' => $this->get_main_meta( '_elementor_version' ),
			'remoteLibrary' => $this->get_remote_library_config(),
			'last_edited' => $this->get_last_edited(),
			'panel' => static::get_editor_panel_config(),
			'container' => 'body',
			'urls' => [
				'exit_to_dashboard' => $this->get_exit_to_dashboard_url(),
				'preview' => $this->get_preview_url(),
				'wp_preview' => $this->get_wp_preview_url(),
				'permalink' => $this->get_permalink(),
			],
		];
	}

	/**
	 * @since 2.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'document_settings',
			[
				'label' => Wp_Helper::__( 'General Settings', 'elementor' ),
				'tab' => Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->add_control(
			'post_title',
			[
				'label' => Wp_Helper::__( 'Title', 'elementor' ),
				'type' => Controls_Manager::TEXT,
				'default' => Wp_Helper::$post_title ? Wp_Helper::$post_title : '',
				'label_block' => true,
				'separator' => 'none',
			]
		);

		
		$this->add_control(
			'post_status',
			[
				'label'   => Wp_Helper::__( 'Status', 'elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => Wp_Helper::$post_status ? Wp_Helper::$post_status : 'publish',
				'options' => [ 'publish' => Wp_Helper::__( 'Published' ), 'private' => Wp_Helper::__( 'Hidden' ) ],
			]
		);

		$this->end_controls_section();

		/**
		 * Register document controls.
		 *
		 * Fires after Elementor registers the document controls.
		 *
		 * @since 2.0.0
		 *
		 * @param Document $this The document instance.
		 */
		Wp_Helper::do_action( 'elementor/documents/register_controls', $this );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function save( $data ) {
		/**
		 * Before document save.
		 *
		 * Fires when document save starts on Elementor.
		 *
		 * @since 2.5.12
		 *
		 * @param \Elementor\Core\Base\Document $this The current document.
		 * @param $data.
		 */
		Wp_Helper::do_action( 'elementor/document/before_save', $this, $data );

		if ( ! empty( $data['settings'] ) ) {
			$this->save_settings( $data['settings'] );
		}

		//$this->save_template_type();
		
		// Remove Post CSS
		$post_css = Post_CSS::create( Wp_Helper::$id_post );

		$post_css->delete();

		/**
		 * After document save.
		 *
		 * Fires when document save is complete.
		 *
		 * @since 2.5.12
		 *
		 * @param \Elementor\Core\Base\Document $this The current document.
		 * @param $data.
		 */
		Wp_Helper::do_action( 'elementor/document/after_save', $this, $data );

		return true;
	}

	/**
	 * Is built with Elementor.
	 *
	 * Check whether the post was built with Elementor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return bool Whether the post was built with Elementor.
	 */
	public function is_built_with_elementor() {
		return ! ! Wp_Helper::get_post_meta( $this->post->ID, '_elementor_edit_mode', true );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @static
	 *
	 * @return mixed
	 */
	public function get_edit_url() {
		$url = add_query_arg(
			[
				'post' => $this->get_main_id(),
				'action' => 'elementor',
			],
			Wp_Helper::admin_url( 'post.php' )
		);

		/**
		 * Document edit url.
		 *
		 * Filters the document edit url.
		 *
		 * @since 2.0.0
		 *
		 * @param string   $url  The edit url.
		 * @param Document $this The document instance.
		 */
		$url = Wp_Helper::apply_filters( 'elementor/document/urls/edit', $url, $this );

		return $url;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_preview_url() {
		/**
		 * Use a static var - to avoid change the `ver` parameter on every call.
		 */
		static $url;

		if ( empty( $url ) ) {

			Wp_Helper::add_filter( 'pre_option_permalink_structure', '__return_empty_string' );

			$url = Wp_Helper::set_url_scheme( add_query_arg( [
				'elementor-preview' => $this->get_main_id(),
				'ver' => time(),
			], $this->get_permalink() ) );

			Wp_Helper::remove_filter( 'pre_option_permalink_structure', '__return_empty_string' );

			/**
			 * Document preview URL.
			 *
			 * Filters the document preview URL.
			 *
			 * @since 2.0.0
			 *
			 * @param string   $url  The preview URL.
			 * @param Document $this The document instance.
			 */
			$url = Wp_Helper::apply_filters( 'elementor/document/urls/preview', $url, $this );
		}

		return $url;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function get_json_meta( $key ) {
		$meta = Wp_Helper::get_post_meta( $this->post->ID, $key, true );

		if ( is_string( $meta ) && ! empty( $meta ) ) {
			$meta = json_decode( $meta, true );
		}

		if ( empty( $meta ) ) {
			$meta = [];
		}

		return $meta;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param null $data
	 * @param bool $with_html_content
	 *
	 * @return array
	 */
	public function get_elements_raw_data( $data = null, $with_html_content = false ) {
		if ( is_null( $data ) ) {
			$data = $this->get_elements_data();
		}

		// Change the current documents, so widgets can use `documents->get_current` and other post data
		Plugin::$instance->documents->switch_to_document( $this );

		$editor_data = [];

		foreach ( $data as $element_data ) {
			$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

			if ( ! $element ) {
				continue;
			}

			$editor_data[] = $element->get_raw_data( $with_html_content );
		} // End foreach().

		Plugin::$instance->documents->restore_document();

		return $editor_data;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $status
	 *
	 * @return array
	 */
	public function get_elements_data( $status = DB::STATUS_PUBLISH ) {
		$obj =  new \AxonCreatorPost( Wp_Helper::$id_post, Wp_Helper::$id_lang );
		
		$elements = (array) json_decode( $obj->content, true );

		if ( Plugin::$instance->editor->is_edit_mode() ) {
			if ( empty( $elements ) ) {
				// Convert to Elementor.
				$elements = [];
			}
		}
		
		if( Wp_Helper::$id_post != Wp_Helper::$id_editor && !$obj->active ){
			return [];
		}elseif( \Tools::getValue( 'wp_preview' ) == Wp_Helper::$id_post ){
			$elements = (array) json_decode( $obj->content_autosave, true );
		}

		return $elements;
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function convert_to_elementor() {
		$this->save( [] );

		if ( empty( $this->post->post_content ) ) {
			return [];
		}

		// Check if it's only a shortcode.
		preg_match_all( '/' . get_shortcode_regex() . '/', $this->post->post_content, $matches, PREG_SET_ORDER );
		if ( ! empty( $matches ) ) {
			foreach ( $matches as $shortcode ) {
				if ( trim( $this->post->post_content ) === $shortcode[0] ) {
					$widget_type = Plugin::$instance->widgets_manager->get_widget_types( 'shortcode' );
					$settings = [
						'shortcode' => $this->post->post_content,
					];
					break;
				}
			}
		}

		if ( empty( $widget_type ) ) {
			$widget_type = Plugin::$instance->widgets_manager->get_widget_types( 'text-editor' );
			$settings = [
				'editor' => $this->post->post_content,
			];
		}

		// TODO: Better coding to start template for editor
		return [
			[
				'id' => Utils::generate_random_string(),
				'elType' => 'section',
				'elements' => [
					[
						'id' => Utils::generate_random_string(),
						'elType' => 'column',
						'elements' => [
							[
								'id' => Utils::generate_random_string(),
								'elType' => $widget_type::get_type(),
								'widgetType' => $widget_type->get_name(),
								'settings' => $settings,
							],
						],
					],
				],
			],
		];
	}

	/**
	 * @since 2.1.3
	 * @access public
	 */
	public function print_elements_with_wrapper( $elements_data = null ) {
		if ( ! $elements_data ) {
			$elements_data = $this->get_elements_data();
		}
		
		$is_editor = Wp_Helper::$id_post == Wp_Helper::$id_editor && Wp_Helper::is_preview_mode();
		
		if ( !$elements_data && !$is_editor ) {
			return;
		}
		
		$id_post = Wp_Helper::$id_post;
				
		$attributes = [
			'data-elementor-type' => 'post',
			'data-elementor-id' => $id_post,
			'class' => 'elementor elementor-' . $id_post,
		];
						
        if ( $is_editor ){
			$attributes['id'] = 'elementor';
			$attributes['class'] .= ' elementor-edit-mode';
		}else{
			$attributes['data-elementor-settings'] = json_encode( $this->get_frontend_settings() );
		}
		
		?>
		<div <?php echo Utils::render_html_attributes( $attributes ); ?>>
			<?php if ( !$is_editor ){ ?>
				<div class="elementor-inner">
					<div class="elementor-section-wrap">
						<?php $this->print_elements( $elements_data ); ?>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_css_wrapper_selector() {
		return '';
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_panel_page_settings() {
		return [
			/* translators: %s: Document title */
			'title' => sprintf( Wp_Helper::__( '%s Settings', 'elementor' ), static::get_title() ),
		];
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_post() {
		return $this->post;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_permalink() {
		return get_permalink( $this->get_main_id() );
	}

	/**
	 * @since 2.0.8
	 * @access public
	 */
	public function get_content( $with_css = false ) {
		return Plugin::$instance->frontend->get_builder_content( Wp_Helper::$id_post, $with_css );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function delete() {
		if ( 'revision' === $this->post->post_type ) {
			$deleted = wp_delete_post_revision( $this->post );
		} else {
			$deleted = wp_delete_post( $this->post->ID );
		}

		return $deleted && ! Wp_Helper::is_wp_error( $deleted );
	}

	/**
	 * Save editor elements.
	 *
	 * Save data from the editor to the database.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @param array $elements
	 */
	protected function save_elements( $elements ) {
		$editor_data = $this->get_elements_raw_data( $elements );

		$this->save_usage( $editor_data );

		// We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
		$json_value = wp_slash( wp_json_encode( $editor_data ) );

		// Don't use `update_post_meta` that can't handle `revision` post type
		$is_meta_updated = update_metadata( 'post', $this->post->ID, '_elementor_data', $json_value );

		/**
		 * Before saving data.
		 *
		 * Fires before Elementor saves data to the database.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $status          Post status.
		 * @param int|bool $is_meta_updated Meta ID if the key didn't exist, true on successful update, false on failure.
		 */
		do_action( 'elementor/db/before_save', $this->post->post_status, $is_meta_updated );

		Plugin::$instance->db->save_plain_text( $this->post->ID );

		/**
		 * After saving data.
		 *
		 * Fires after Elementor saves data to the database.
		 *
		 * @since 1.0.0
		 *
		 * @param int   $post_id     The ID of the post.
		 * @param array $editor_data Sanitize posted data.
		 */
		do_action( 'elementor/editor/after_save', $this->post->ID, $editor_data );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $user_id Optional. User ID. Default value is `0`.
	 *
	 * @return bool|int
	 */
	public function get_autosave_id( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$autosave = Utils::get_post_autosave( $this->post->ID, $user_id );
		if ( $autosave ) {
			return $autosave->ID;
		}

		return false;
	}

	public function save_version() {
		if ( ! defined( 'IS_AXON_CREATOR_UPGRADE' ) ) {
			// Save per revision.
			$this->update_meta( '_elementor_version', AXON_CREATOR_VERSION );

			/**
			 * Document version save.
			 *
			 * Fires when document version is saved on Elementor.
			 * Will not fire during Elementor Upgrade.
			 *
			 * @since 2.5.12
			 *
			 * @param \Elementor\Core\Base\Document $this The current document.
			 *
			 */
			Wp_Helper::do_action( 'elementor/document/save_version', $this );
		}
	}

	/**
	 * @since 2.0.0
	 * @access public
	 * @deprecated 2.2.0 Use `Document::save_template_type()`.
	 */
	public function save_type() {
		_deprecated_function( __METHOD__, '2.2.0', __CLASS__ . '::save_template_type()' );

		$this->save_template_type();
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function save_template_type() {
		return $this->update_main_meta( self::TYPE_META_KEY, $this->get_name() );
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function get_template_type() {
		return $this->get_main_meta( self::TYPE_META_KEY );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key Meta data key.
	 *
	 * @return mixed
	 */
	public function get_main_meta( $key ) {
		return Wp_Helper::get_post_meta( Wp_Helper::$id_post, $key, true );
	}

	/**
	 * @since 2.0.4
	 * @access public
	 *
	 * @param string $key   Meta data key.
	 * @param string $value Meta data value.
	 *
	 * @return bool|int
	 */
	public function update_main_meta( $key, $value ) {
		return Wp_Helper::update_post_meta( Wp_Helper::$id_post, $key, $value );
	}

	/**
	 * @since 2.0.4
	 * @access public
	 *
	 * @param string $key   Meta data key.
	 * @param string $value Optional. Meta data value. Default is an empty string.
	 *
	 * @return bool
	 */
	public function delete_main_meta( $key, $value = '' ) {
		return Wp_Helper::delete_post_meta( Wp_Helper::$id_post, $key, $value );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key Meta data key.
	 *
	 * @return mixed
	 */
	public function get_meta( $key ) {
		return Wp_Helper::get_post_meta( $this->post->ID, $key, true );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $key   Meta data key.
	 * @param mixed  $value Meta data value.
	 *
	 * @return bool|int
	 */
	public function update_meta( $key, $value ) {
		// Use `update_metadata` in order to work also with revisions.
		return update_metadata( 'post', $this->post->ID, $key, $value );
	}

	/**
	 * @since 2.0.3
	 * @access public
	 *
	 * @param string $key   Meta data key.
	 * @param string $value Meta data value.
	 *
	 * @return bool
	 */
	public function delete_meta( $key, $value = '' ) {
		// Use `delete_metadata` in order to work also with revisions.
		return delete_metadata( 'post', $this->post->ID, $key, $value );
	}

	/**
	 * @since 2.0.0
	 * @access public
	 */
	public function get_last_edited() {
		$post = $this->post;
		$autosave_post = $this->get_autosave();

		if ( $autosave_post ) {
			$post = $autosave_post->get_post();
		}

		$date = date_i18n( Wp_Helper::_x( 'M j, H:i', 'revision date format', 'elementor' ), strtotime( $post->post_modified ) );
		$display_name = get_the_author_meta( 'display_name', $post->post_author );

		if ( $autosave_post || 'revision' === $post->post_type ) {
			/* translators: 1: Saving date, 2: Author display name */
			$last_edited = sprintf( Wp_Helper::__( 'Draft saved on %1$s by %2$s', 'elementor' ), '<time>' . $date . '</time>', $display_name );
		} else {
			/* translators: 1: Editing date, 2: Author display name */
			$last_edited = sprintf( Wp_Helper::__( 'Last edited on %1$s by %2$s', 'elementor' ), '<time>' . $date . '</time>', $display_name );
		}

		return $last_edited;
	}

	/**
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $data
	 *
	 * @throws \Exception If the post does not exist.
	 */
	public function __construct( array $data = [] ) {
		if ( $data ) {
			if ( empty( $data['post_id'] ) ) {
				throw new \Exception( sprintf( 'Post ID #%s does not exist.', $data['post_id'] ), Exceptions::NOT_FOUND );
			}
			
			$this->post_id = $data['post_id'];

			$data['id'] = $data['post_id'];

			if ( ! isset( $data['settings'] ) ) {
				$data['settings'] = [];
			}
		}

		parent::__construct( $data );
	}

	protected function get_remote_library_config() {
		$config = [
			'type' => 'block',
			'category' => $this->get_name(),
			'autoImportSettings' => false,
		];

		return $config;
	}

	/**
	 * @since 2.0.4
	 * @access protected
	 *
	 * @param $settings
	 */
	protected function save_settings( $settings ) {
		$page_settings_manager = SettingsManager::get_settings_managers( 'page' );
		//$page_settings_manager->ajax_before_save_settings( $settings, $this->post->ID ); ///xxxxx
		$page_settings_manager->save_settings( $settings, Wp_Helper::$id_post );
	}

	/**
	 * @since 2.1.3
	 * @access protected
	 */
	protected function print_elements( $elements_data ) {
		foreach ( $elements_data as $element_data ) {
			$element = Plugin::$instance->elements_manager->create_element_instance( $element_data );

			if ( ! $element ) {
				continue;
			}

			$element->print_element();
		}
	}

	private function save_usage( $elements ) {
		if ( DB::STATUS_PUBLISH !== $this->post->post_status ) {
			return;
		}

		if ( ! self::get_property( 'is_editable' ) ) {
			return;
		}

		$usage = [];
		Plugin::$instance->db->iterate_data( $elements, function ( $element ) use ( & $usage ) {
			if ( empty( $element['widgetType'] ) ) {
				$type = $element['elType'];
			} else {
				$type = $element['widgetType'];
			}

			if ( ! isset( $usage[ $type ] ) ) {
				$usage[ $type ] = 0;
			}

			$usage[ $type ]++;

			return $element;
		} );

		// Keep prev usage, before updating the new usage meta.
		$prev_usage = $this->get_meta( self::ELEMENTS_USAGE_META_KEY );

		$this->update_meta( self::ELEMENTS_USAGE_META_KEY, $usage );

		// Handle global usage.
		$doc_type = $this->get_name();

		$global_usage = Wp_Helper::get_option( self::ELEMENTS_USAGE_OPTION_NAME, [] );

		if ( $prev_usage ) {
			foreach ( $prev_usage as $type => $count ) {
				if ( isset( $global_usage[ $doc_type ][ $type ] ) ) {
					$global_usage[ $doc_type ][ $type ] -= $prev_usage[ $type ];
					if ( 0 === $global_usage[ $doc_type ][ $type ] ) {
						unset( $global_usage[ $doc_type ][ $type ] );
					}
				}
			}
		}

		foreach ( $usage as $type => $count ) {
			if ( ! isset( $global_usage[ $doc_type ] ) ) {
				$global_usage[ $doc_type ] = [];
			}

			if ( ! isset( $global_usage[ $doc_type ][ $type ] ) ) {
				$global_usage[ $doc_type ][ $type ] = 0;
			}

			$global_usage[ $doc_type ][ $type ] += $usage[ $type ];
		}

		Wp_Helper::update_option( self::ELEMENTS_USAGE_OPTION_NAME, $global_usage );
	}
}