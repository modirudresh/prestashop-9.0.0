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

namespace AxonCreator\Core;

use AxonCreator\Core\Base\Document;
use AxonCreator\Core\Common\Modules\Ajax\Module as Ajax;
use AxonCreator\Core\DocumentTypes\Post;
use AxonCreator\DB;
use AxonCreator\Plugin;
use AxonCreator\TemplateLibrary\Source_Local;
use AxonCreator\Utils;
use AxonCreator\Core\Settings\Manager as SettingsManager;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Elementor documents manager.
 *
 * Elementor documents manager handler class is responsible for registering and
 * managing Elementor documents.
 *
 * @since 2.0.0
 */
class Documents_Manager {

	/**
	 * Registered types.
	 *
	 * Holds the list of all the registered types.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @var Document[]
	 */
	protected $types = [];

	/**
	 * Registered documents.
	 *
	 * Holds the list of all the registered documents.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @var Document[]
	 */
	protected $documents = [];

	/**
	 * Current document.
	 *
	 * Holds the current document.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @var Document
	 */
	protected $current_doc;

	/**
	 * Switched data.
	 *
	 * Holds the current document when changing to the requested post.
	 *
	 * @since 2.0.0
	 * @access protected
	 *
	 * @var array
	 */
	protected $switched_data = [];

	protected $cpt = [];

	/**
	 * Documents manager constructor.
	 *
	 * Initializing the Elementor documents manager.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function __construct() {
		Wp_Helper::add_action( 'elementor/documents/register', [ $this, 'register_default_types' ], 0 );
		Wp_Helper::add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
		Wp_Helper::add_filter( 'post_row_actions', [ $this, 'filter_post_row_actions' ], 11, 2 );
		Wp_Helper::add_filter( 'page_row_actions', [ $this, 'filter_post_row_actions' ], 11, 2 );
		Wp_Helper::add_filter( 'user_has_cap', [ $this, 'remove_user_edit_cap' ], 10, 3 );
		Wp_Helper::add_filter( 'elementor/editor/localize_settings', [ $this, 'localize_settings' ] );
	}

	/**
	 * Register ajax actions.
	 *
	 * Process ajax action handles when saving data and discarding changes.
	 *
	 * Fired by `elementor/ajax/register_actions` action.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param Ajax $ajax_manager An instance of the ajax manager.
	 */
	public function register_ajax_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'save_builder', [ $this, 'ajax_save' ] );
		$ajax_manager->register_ajax_action( 'discard_changes', [ $this, 'ajax_discard_changes' ] );
		$ajax_manager->register_ajax_action( 'import_from_language', [ $this, 'ajax_import_from_language' ] );
	}

	/**
	 * Register default types.
	 *
	 * Registers the default document types.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function register_default_types() {
		$default_types = [
			'post' => Post::get_class_full_name(),
		];

		foreach ( $default_types as $type => $class ) {
			$this->register_document_type( $type, $class );
		}
	}

	/**
	 * Register document type.
	 *
	 * Registers a single document.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $type  Document type name.
	 * @param Document $class The name of the class that registers the document type.
	 *                      Full name with the namespace.
	 *
	 * @return Documents_Manager The updated document manager instance.
	 */
	public function register_document_type( $type, $class ) {
		$this->types[ $type ] = $class;

		$cpt = $class::get_property( 'cpt' );

		if ( $cpt ) {
			foreach ( $cpt as $post_type ) {
				$this->cpt[ $post_type ] = $type;
			}
		}

		if ( $class::get_property( 'register_type' ) ) {
			Source_Local::add_template_type( $type );
		}

		return $this;
	}

	/**
	 * Get document.
	 *
	 * Retrieve the document data based on a post ID.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int  $post_id    Post ID.
	 * @param bool $from_cache Optional. Whether to retrieve cached data. Default is true.
	 *
	 * @return false|Document Document data or false if post ID was not entered.
	 */
	public function get( $post_id, $from_cache = true ) {
		$this->register_types();
		
		$post_id = abs( (int) $post_id );
		
		if ( ! $post_id ) {
			return false;
		}
		
		$doc_type = 'post';
		
		$doc_type_class = $this->get_document_type( $doc_type );
		$this->documents[ $post_id ] = new $doc_type_class( [
			'post_id' => $post_id,
		] );
		
		return $this->documents[ $post_id ];
	}

	/**
	 * Get document or autosave.
	 *
	 * Retrieve either the document or the autosave.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $id      Optional. Post ID. Default is `0`.
	 * @param int $user_id Optional. User ID. Default is `0`.
	 *
	 * @return false|Document The document if it exist, False otherwise.
	 */
	public function get_doc_or_auto_save( $id, $user_id = 0 ) {
		$document = $this->get( $id );

		return $document;
	}

	/**
	 * Get document for frontend.
	 *
	 * Retrieve the document for frontend use.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param int $post_id Optional. Post ID. Default is `0`.
	 *
	 * @return false|Document The document if it exist, False otherwise.
	 */
	public function get_doc_for_frontend( $post_id ) {

		$document = $this->get( $post_id );

		return $document;
	}

	/**
	 * Get document type.
	 *
	 * Retrieve the type of any given document.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @param string $type
	 *
	 * @param string $fallback
	 *
	 * @return Document|bool The type of the document.
	 */
	public function get_document_type( $type, $fallback = 'post' ) {
		$types = $this->get_document_types();

		if ( isset( $types[ $type ] ) ) {
			return $types[ $type ];
		}

		if ( isset( $types[ $fallback ] ) ) {
			return $types[ $fallback ];
		}

		return false;
	}

	/**
	 * Get document types.
	 *
	 * Retrieve the all the registered document types.
	 *
	 * @since  2.0.0
	 * @access public
	 *
	 * @param array $args      Optional. An array of key => value arguments to match against
	 *                               the properties. Default is empty array.
	 * @param string $operator Optional. The logical operation to perform. 'or' means only one
	 *                               element from the array needs to match; 'and' means all elements
	 *                               must match; 'not' means no elements may match. Default 'and'.
	 *
	 * @return Document[] All the registered document types.
	 */
	public function get_document_types( $args = [], $operator = 'and' ) {
		$this->register_types();

		return $this->types;
	}

	/**
	 * Get document types with their properties.
	 *
	 * @return array A list of properties arrays indexed by the type.
	 */
	public function get_types_properties() {
		$types_properties = [];

		foreach ( $this->get_document_types() as $type => $class ) {
			$types_properties[ $type ] = $class::get_properties();
		}
		return $types_properties;
	}

	/**
	 * Create a document.
	 *
	 * Create a new document using any given parameters.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param string $type      Document type.
	 * @param array  $post_data An array containing the post data.
	 * @param array  $meta_data An array containing the post meta data.
	 *
	 * @return Document The type of the document.
	 */
	public function create( $type, $post_data = [], $meta_data = [] ) {
		$class = $this->get_document_type( $type, false );

		if ( ! $class ) {
			return new \WP_Error( 500, sprintf( 'Type %s does not exist.', $type ) );
		}

		if ( empty( $post_data['post_title'] ) ) {
			$post_data['post_title'] = Wp_Helper::__( 'Elementor', 'elementor' );
			if ( 'post' !== $type ) {
				$post_data['post_title'] = sprintf(
					/* translators: %s: Document title */
					Wp_Helper::__( 'Elementor %s', 'elementor' ),
					call_user_func( [ $class, 'get_title' ] )
				);
			}
			$update_title = true;
		}

		$meta_data['_elementor_edit_mode'] = 'builder';

		// Save the type as-is for plugins that hooked at `wp_insert_post`.
		$meta_data[ Document::TYPE_META_KEY ] = $type;

		$post_data['meta_input'] = $meta_data;

		$post_id = wp_insert_post( $post_data );

		if ( ! empty( $update_title ) ) {
			$post_data['ID'] = $post_id;
			$post_data['post_title'] .= ' #' . $post_id;

			// The meta doesn't need update.
			unset( $post_data['meta_input'] );

			wp_update_post( $post_data );
		}

		/** @var Document $document */
		$document = new $class( [
			'post_id' => $post_id,
		] );

		// Let the $document to re-save the template type by his way + version.
		$document->save( [] );

		return $document;
	}

	/**
	 * Remove user edit capabilities if document is not editable.
	 *
	 * Filters the user capabilities to disable editing in admin.
	 *
	 * @param array $allcaps An array of all the user's capabilities.
	 * @param array $caps    Actual capabilities for meta capability.
	 * @param array $args    Optional parameters passed to has_cap(), typically object ID.
	 *
	 * @return array
	 */
	public function remove_user_edit_cap( $allcaps, $caps, $args ) {
		global $pagenow;

		if ( ! in_array( $pagenow, [ 'post.php', 'edit.php' ], true ) ) {
			return $allcaps;
		}

		$capability = $args[0];

		if ( 'edit_post' !== $capability ) {
			return $allcaps;
		}

		if ( empty( $args[2] ) ) {
			return $allcaps;
		}

		$post_id = $args[2];

		$document = Plugin::$instance->documents->get( $post_id );

		if ( ! $document ) {
			return $allcaps;
		}

		$allcaps[ $caps[0] ] = $document::get_property( 'is_editable' );

		return $allcaps;
	}

	/**
	 * Filter Post Row Actions.
	 *
	 * Let the Document to filter the array of row action links on the Posts list table.
	 *
	 * @param array $actions
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	public function filter_post_row_actions( $actions, $post ) {
		$document = $this->get( $post->ID );

		if ( $document ) {
			$actions = $document->filter_admin_row_actions( $actions );
		}

		return $actions;
	}

	/**
	 * Save document data using ajax.
	 *
	 * Save the document on the builder using ajax, when saving the changes, and refresh the editor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param $request Post ID.
	 *
	 * @throws \Exception If current user don't have permissions to edit the post or the post is not using Elementor.
	 *
	 * @return array The document data after saving.
	 */
	public function ajax_save( $request ) {
		$document = $this->get( $request['editor_post_id'] );

        $id_post = Wp_Helper::$id_post;
        $id_lang = Wp_Helper::$id_lang;
        $id_shop = Wp_Helper::$id_shop;
        $post_type = Wp_Helper::$post_type;
        $key_related = Wp_Helper::$key_related;

		$post = new \AxonCreatorPost( $id_post, $id_lang );
		
		if( $key_related && $post_type != 'hook' ){
			$related = Wp_Helper::getRelatedByKey();
			$obj = new \AxonCreatorRelated( (int)$related['id_axon_creator_related'], $id_lang, $id_shop );
			$obj->id_post = $id_post;
			$obj->key_related = $key_related;
			$obj->save();
		}
		
		if( $request['status'] != DB::STATUS_AUTOSAVE && (int)Wp_Helper::get_option( 'elementor_max_saved_revision' ) ){
			$revision = new \AxonCreatorRevisions();			
			$revision->id_post = $post->id;
			$revision->id_lang = $id_lang;
			$revision->id_employee = $post->id_employee;
			$revision->content = $post->content;
			$revision->page_settings = json_encode( SettingsManager::get_settings_managers( 'page' )->get_model( $post->id )->get_data( 'settings' ) );	
			$revision->save();

			$this->delete_revisions_old($post->id, $id_lang, (int)Wp_Helper::get_option( 'elementor_max_saved_revision' ));
		}
				
		if( $request['settings']['post_title'] ){
			$post->title = $request['settings']['post_title'];
		}
				
		if( $request['settings']['post_status'] == 'private' ){
			$post->active = 0;
		}else{
			$post->active = 1;
		}
		
		$post->id_employee = Wp_Helper::get_current_user_id();
		$post->content = json_encode( $request['elements'] );
		
		if( $request['status'] == DB::STATUS_AUTOSAVE ){
			$autosave = new \AxonCreatorPost( $id_post, $id_lang );
			$autosave->content_autosave = json_encode( $request['elements'] );
			$autosave->save();
		}else{
			$post->content_autosave = json_encode( $request['elements'] );
			$post->save();
		}

		$data = [
			'elements' => $request['elements'],
			'settings' => $request['settings'],
		];
		
		$document->save( $data );
				
		$return_data = [
			'config' => [
				'document' => [
					'last_edited' => $post->date_upd,
					'urls' => [
						'wp_preview' => Wp_Helper::get_permalink( [ 'wp_preview' => $id_post ] ),
					],
				],
			],
		];

		/**
		 * Returned documents ajax saved data.
		 *
		 * Filters the ajax data returned when saving the post on the builder.
		 *
		 * @since 2.0.0
		 *
		 * @param array    $return_data The returned data.
		 * @param Document $document    The document instance.
		 */
		$return_data = Wp_Helper::apply_filters( 'elementor/documents/ajax_save/return_data', $return_data, $document );

		return $return_data;
	}

    public function delete_revisions_old($id_post, $id_lang, $limit) {			
        $sql = 'SELECT `id_axon_creator_revisions` FROM `'._DB_PREFIX_.'axon_creator_revisions` 
				WHERE `id_post` = ' . $id_post . '
				AND `id_lang` = ' . $id_lang . '
				ORDER BY `date_add` ASC';
		
		$revisions = \Db::getInstance()->executeS( $sql );

		$count = count($revisions);

		if( $count <= $limit ){
			return true;
		}
		
		foreach ( $revisions as $key => $revision ) {
			$revi = new \AxonCreatorRevisions( $revision['id_axon_creator_revisions'] );
			$revi->delete();
			$count--;
			if( $count <= $limit ){
				break;
			}
		}
	}
	
	/**
	 * Save document data using ajax.
	 *
	 * Save the document on the builder using ajax, when saving the changes, and refresh the editor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param $request Post ID.
	 *
	 * @throws \Exception If current user don't have permissions to edit the post or the post is not using Elementor.
	 *
	 * @return array The document data after saving.
	 */
	public function ajax_import_from_language( $request ) {
		$id_post = $request['editor_post_id'];
		$id_lang = $request['id_lang'];
		
		$post = new \AxonCreatorPost( $id_post, $id_lang );
		
		$data = [
			'content' => json_decode( $post->content, true ),
		];

		return $data;
	}
	
	/**
	 * Ajax discard changes.
	 *
	 * Load the document data from an autosave, deleting unsaved changes.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param $request
	 *
	 * @return bool True if changes discarded, False otherwise.
	 */
	public function ajax_discard_changes( $request ) {
		$document = $this->get( $request['editor_post_id'] );

		$autosave = $document->get_autosave();

		if ( $autosave ) {
			$success = $autosave->delete();
		} else {
			$success = true;
		}

		return $success;
	}

	/**
	 * Switch to document.
	 *
	 * Change the document to any new given document type.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param Document $document The document to switch to.
	 */
	public function switch_to_document( $document ) {
		// If is already switched, or is the same post, return.
		if ( $this->current_doc === $document ) {
			$this->switched_data[] = false;
			return;
		}

		$this->switched_data[] = [
			'switched_doc' => $document,
			'original_doc' => $this->current_doc, // Note, it can be null if the global isn't set
		];

		$this->current_doc = $document;
	}

	/**
	 * Restore document.
	 *
	 * Rollback to the original document.
	 *
	 * @since 2.0.0
	 * @access public
	 */
	public function restore_document() {
		$data = array_pop( $this->switched_data );

		// If not switched, return.
		if ( ! $data ) {
			return;
		}

		$this->current_doc = $data['original_doc'];
	}

	/**
	 * Get current document.
	 *
	 * Retrieve the current document.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return Document The current document.
	 */
	public function get_current() {
		return $this->current_doc;
	}

	/**
	 * Get groups.
	 *
	 * @since 2.0.0
	 * @deprecated 2.4.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_groups() {
		// _deprecated_function( __METHOD__, '2.4.0' );

		return [];
	}

	public function localize_settings( $settings ) {
		$translations = [];

		foreach ( $this->get_document_types() as $type => $class ) {
			$translations[ $type ] = $class::get_title();
		}

		return array_replace_recursive( $settings, [
			'i18n' => $translations,
		] );
	}

	private function register_types() {
		Wp_Helper::do_action( 'elementor/documents/register', $this );
	}
}