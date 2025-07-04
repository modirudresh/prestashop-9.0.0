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

use AxonCreator\TemplateLibrary\Source_Local;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor compatibility.
 *
 * Elementor compatibility handler class is responsible for compatibility with
 * external plugins. The class resolves different issues with non-compatible
 * plugins.
 *
 * @since 1.0.0
 */
class Compatibility {

	/**
	 * Register actions.
	 *
	 * Run Elementor compatibility with external plugins using custom filters and
	 * actions.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function register_actions() {
		Wp_Helper::add_action( 'init', [ __CLASS__, 'init' ] );

		self::polylang_compatibility();

		Wp_Helper::add_action( 'elementor/maintenance_mode/mode_changed', [ __CLASS__, 'clear_3rd_party_cache' ] );
	}

	public static function clear_3rd_party_cache() {
		// W3 Total Cache.
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}

		// WP Fastest Cache.
		if ( ! empty( $GLOBALS['wp_fastest_cache'] ) && method_exists( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ) {
			$GLOBALS['wp_fastest_cache']->deleteCache();
		}

		// WP Super Cache
		if ( function_exists( 'wp_cache_clean_cache' ) ) {
			global $file_prefix;
			wp_cache_clean_cache( $file_prefix, true );
		}
	}

	/**
	 * Add new button to gutenberg.
	 *
	 * Insert new "Elementor" button to the gutenberg editor to create new post
	 * using Elementor page builder.
	 *
	 * @since 1.9.0
	 * @access public
	 * @static
	 */
	public static function add_new_button_to_gutenberg() {
		global $typenow;
		if ( ! User::is_current_user_can_edit_post_type( $typenow ) ) {
			return;
		}

		// Introduced in WP 5.0
		if ( function_exists( 'use_block_editor_for_post' ) && ! use_block_editor_for_post( $typenow ) ) {
			return;
		}

		?>
		<script type="text/javascript">
			document.addEventListener( 'DOMContentLoaded', function() {
				var dropdown = document.querySelector( '#split-page-title-action .dropdown' );

				if ( ! dropdown ) {
					return;
				}

				var url = '<?php echo esc_url( Utils::get_create_new_post_url( $typenow ) ); ?>';

				dropdown.insertAdjacentHTML( 'afterbegin', '<a href="' + url + '">Elementor</a>' );
			} );
		</script>
		<?php
	}

	/**
	 * Init.
	 *
	 * Initialize Elementor compatibility with external plugins.
	 *
	 * Fired by `init` action.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function init() {
		// Hotfix for NextGEN Gallery plugin.
		if ( defined( 'NGG_PLUGIN_VERSION' ) ) {
			Wp_Helper::add_filter( 'elementor/document/urls/edit', function( $edit_link ) {
				return add_query_arg( 'display_gallery_iframe', '', $edit_link );
			} );
		}

		// Hack for Ninja Forms.
		if ( class_exists( '\Ninja_Forms' ) && class_exists( '\NF_Display_Render' ) ) {
			Wp_Helper::add_action( 'elementor/preview/enqueue_styles', function() {
				ob_start();
				\NF_Display_Render::localize( 0 );

				ob_clean();

				wp_add_inline_script( 'nf-front-end', 'var nfForms = nfForms || [];' );
			} );
		}

		// Exclude our Library from Yoast SEO plugin.
		Wp_Helper::add_filter( 'wpseo_sitemaps_supported_post_types', [ __CLASS__, 'filter_library_post_type' ] );
		Wp_Helper::add_filter( 'wpseo_accessible_post_types', [ __CLASS__, 'filter_library_post_type' ] );
		Wp_Helper::add_filter( 'wpseo_sitemap_exclude_post_type', function( $retval, $post_type ) {
			if ( Source_Local::CPT === $post_type ) {
				$retval = true;
			}

			return $retval;
		}, 10, 2 );

		// Disable optimize files in Editor from Autoptimize plugin.
		Wp_Helper::add_filter( 'autoptimize_filter_noptimize', function( $retval ) {
			if ( Plugin::$instance->editor->is_edit_mode() ) {
				$retval = true;
			}

			return $retval;
		} );

		// Add the description (content) tab for a new product, so it can be edited with Elementor.
		Wp_Helper::add_filter( 'woocommerce_product_tabs', function( $tabs ) {
			if ( ! isset( $tabs['description'] ) && Plugin::$instance->preview->is_preview_mode() ) {
				$post = get_post();
				if ( empty( $post->post_content ) ) {
					$tabs['description'] = [
						'title' => Wp_Helper::__( 'Description', 'elementor' ),
						'priority' => 10,
						'callback' => 'woocommerce_product_description_tab',
					];
				}
			}

			return $tabs;
		} );

		// Fix WC session not defined in editor.
		if ( class_exists( 'woocommerce' ) ) {
			Wp_Helper::add_action( 'elementor/editor/before_enqueue_scripts', function() {
				remove_action( 'woocommerce_shortcode_before_product_cat_loop', 'wc_print_notices' );
				remove_action( 'woocommerce_before_shop_loop', 'wc_print_notices' );
				remove_action( 'woocommerce_before_single_product', 'wc_print_notices' );
			} );

			Wp_Helper::add_filter( 'elementor/maintenance_mode/is_login_page', function( $value ) {

				// Support Woocommerce Account Page.
				if ( is_account_page() && ! is_user_logged_in() ) {
					$value = true;
				}
				return $value;
			} );
		}

		// Fix Jetpack Contact Form in Editor Mode.
		if ( class_exists( 'Grunion_Editor_View' ) ) {
			Wp_Helper::add_action( 'elementor/editor/before_enqueue_scripts', function() {
				remove_action( 'media_buttons', 'grunion_media_button', 999 );
				remove_action( 'admin_enqueue_scripts', 'grunion_enable_spam_recheck' );

				remove_action( 'admin_notices', [ 'Grunion_Editor_View', 'handle_editor_view_js' ] );
				remove_action( 'admin_head', [ 'Grunion_Editor_View', 'admin_head' ] );
			} );
		}

		// Fix Popup Maker in Editor Mode.
		if ( class_exists( 'PUM_Admin_Shortcode_UI' ) ) {
			Wp_Helper::add_action( 'elementor/editor/before_enqueue_scripts', function() {
				$pum_admin_instance = \PUM_Admin_Shortcode_UI::instance();

				remove_action( 'print_media_templates', [ $pum_admin_instance, 'print_media_templates' ] );
				remove_action( 'admin_print_footer_scripts', [ $pum_admin_instance, 'admin_print_footer_scripts' ], 100 );
				remove_action( 'wp_ajax_pum_do_shortcode', [ $pum_admin_instance, 'wp_ajax_pum_do_shortcode' ] );

				remove_action( 'admin_enqueue_scripts', [ $pum_admin_instance, 'admin_enqueue_scripts' ] );

				remove_filter( 'pum_admin_var', [ $pum_admin_instance, 'pum_admin_var' ] );
			} );
		}

		// Fix Preview URL for https://premium.wpmudev.org/project/domain-mapping/ plugin
		if ( class_exists( 'domain_map' ) ) {
			Wp_Helper::add_filter( 'elementor/document/urls/preview', function( $preview_url ) {
				if ( wp_parse_url( $preview_url, PHP_URL_HOST ) !== $_SERVER['HTTP_HOST'] ) {
					$preview_url = \domain_map::utils()->unswap_url( $preview_url );
					$preview_url = add_query_arg( [
						'dm' => \Domainmap_Module_Mapping::BYPASS,
					], $preview_url );
				}

				return $preview_url;
			} );
		}
	}

	public static function filter_library_post_type( $post_types ) {
		unset( $post_types[ Source_Local::CPT ] );

		return $post_types;
	}

	/**
	 * Polylang compatibility.
	 *
	 * Fix Polylang compatibility with Elementor.
	 *
	 * @since 2.0.0
	 * @access private
	 * @static
	 */
	private static function polylang_compatibility() {
		// Fix language if the `get_user_locale` is difference from the `get_locale
		if ( isset( $_REQUEST['action'] ) && 0 === strpos( $_REQUEST['action'], 'elementor' ) ) {
			Wp_Helper::add_action( 'set_current_user', function() {
				global $current_user;
				$current_user->locale = get_locale();
			} );

			// Fix for Polylang
			define( 'PLL_AJAX_ON_FRONT', true );

			Wp_Helper::add_action( 'pll_pre_init', function( $polylang ) {
				if ( isset( $_REQUEST['post'] ) ) {
					$post_language = $polylang->model->post->get_language( $_REQUEST['post'], 'locale' );
					if ( ! empty( $post_language ) ) {
						$_REQUEST['lang'] = $post_language->locale;
					}
				}
			} );
		}

		// Copy elementor data while polylang creates a translation copy
		Wp_Helper::add_filter( 'pll_copy_post_metas', [ __CLASS__, 'save_polylang_meta' ], 10, 4 );
	}

	/**
	 * Save polylang meta.
	 *
	 * Copy elementor data while polylang creates a translation copy.
	 *
	 * Fired by `pll_copy_post_metas` filter.
	 *
	 * @since 1.6.0
	 * @access public
	 * @static
	 *
	 * @param array $keys List of custom fields names.
	 * @param bool  $sync True if it is synchronization, false if it is a copy.
	 * @param int   $from ID of the post from which we copy information.
	 * @param int   $to   ID of the post to which we paste information.
	 *
	 * @return array List of custom fields names.
	 */
	public static function save_polylang_meta( $keys, $sync, $from, $to ) {
		// Copy only for a new post.
		if ( ! $sync ) {
			Plugin::$instance->db->copy_elementor_meta( $from, $to );
		}

		return $keys;
	}

	/**
	 * Process post meta before WP importer.
	 *
	 * Normalize Elementor post meta on import, We need the `wp_slash` in order
	 * to avoid the unslashing during the `add_post_meta`.
	 *
	 * Fired by `wp_import_post_meta` filter.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param array $post_meta Post meta.
	 *
	 * @return array Updated post meta.
	 */
	public static function on_wp_import_post_meta( $post_meta ) {
		foreach ( $post_meta as &$meta ) {
			if ( '_elementor_data' === $meta['key'] ) {
				$meta['value'] = wp_slash( $meta['value'] );
				break;
			}
		}

		return $post_meta;
	}

	/**
	 * Process post meta before WXR importer.
	 *
	 * Normalize Elementor post meta on import with the new WP_importer, We need
	 * the `wp_slash` in order to avoid the unslashing during the `add_post_meta`.
	 *
	 * Fired by `wxr_importer.pre_process.post_meta` filter.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param array $post_meta Post meta.
	 *
	 * @return array Updated post meta.
	 */
	public static function on_wxr_importer_pre_process_post_meta( $post_meta ) {
		if ( '_elementor_data' === $post_meta['key'] ) {
			$post_meta['value'] = wp_slash( $post_meta['value'] );
		}

		return $post_meta;
	}
}