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

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

use Context;
use Link;
use Db;
use Tools;
use Configuration;
use Shop;
use Translate;

define( 'AXON_CREATOR_VERSION', '1.5.0' );
define( 'AXON_CREATOR_PREVIOUS_STABLE_VERSION', '1.4.9' );

define( 'AXON_CREATOR__FILE__', __FILE__ );
define( 'AXON_CREATOR_PLUGIN_BASE', 'axoncreator/axoncreator.php' );
define( 'AXON_CREATOR_PATH', _PS_MODULE_DIR_ . 'axoncreator/' );

if ( defined( 'AXON_CREATOR_TESTS' ) && AXON_CREATOR_TESTS ) {
	define( 'AXON_CREATOR_URL', 'file://' . AXON_CREATOR_PATH );
} else {
	define( 'AXON_CREATOR_URL', _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . 'modules/axoncreator/' );
}

define( 'AXON_CREATOR_ASSETS_PATH', AXON_CREATOR_PATH . 'assets/' );
define( 'AXON_CREATOR_ASSETS_URL', AXON_CREATOR_URL . 'assets/' );

define( 'AXON_MINUTE_IN_SECONDS', 60 );
define( 'AXON_HOUR_IN_SECONDS', 60 * AXON_MINUTE_IN_SECONDS );
define( 'AXON_DAY_IN_SECONDS', 24 * AXON_HOUR_IN_SECONDS );
define( 'AXON_WEEK_IN_SECONDS', 7 * AXON_DAY_IN_SECONDS );
define( 'AXON_MONTH_IN_SECONDS', 30 * AXON_DAY_IN_SECONDS );
define( 'AXON_YEAR_IN_SECONDS', 365 * AXON_DAY_IN_SECONDS );

define( 'AXON_AUTOSAVE_INTERVAL', 60 );

define( 'SCRIPT_DEBUG', false );

class Wp_Helper {
	
	public static $id_post;
	public static $id_editor;
	public static $id_lang;
	public static $id_shop;
	public static $post_type;
	public static $key_related;
	public static $post_title;
	public static $post_status;
	
	public static $is_template;
	
	public static $post_var;
	
	public static $wp_actions = [];
	public static $wp_filters = [];
	
	public static $current_action;
	public static $current_filter;
	
	public static $wp_styles = [];
    public static $wp_head_styles = [];
	
	public static $wp_scripts = [];
	public static $wp_head_scripts = [];
	public static $wp_foot_scripts = [];
	
	public static $instance;
	
	const PRODUCT_NAME = 'Axon Creator';

	const STORE_URL = 'https://api.axonviz.com/api_licenses';
	const RENEW_URL = 'https://api.axonviz.com/api_renew';

	// License Statuses
	const STATUS_VALID = 'valid';
	const STATUS_INVALID = 'invalid';
	const STATUS_EXPIRED = 'expired';
	const STATUS_SITE_INACTIVE = 'site_inactive';
	const STATUS_DISABLED = 'disabled';
	
	// Requests lock config.
	const REQUEST_LOCK_TTL = AXON_MINUTE_IN_SECONDS;
	const REQUEST_LOCK_OPTION_NAME = '_axon_creator_api_requests_lock';
	
	const LICENSE_KEY_OPTION_NAME = 'axon_creator_license_key';
	const LICENSE_DATA_OPTION_NAME = '_axon_creator_license_data';
	const LICENSE_DATA_FALLBACK_OPTION_NAME = self::LICENSE_DATA_OPTION_NAME . '_fallback';
		
	public static function _x( $text, $domain = 'elementor' ) {
		return self::module_translate( $text, $domain );
	}

	public static function __( $text, $domain = 'elementor' ) {
		return self::module_translate( $text, $domain );
	}
	
	public static function _e( $text, $domain = 'elementor' ) {
		echo self::module_translate( $text, $domain );
	}

	public static function esc_attr( $string ) {
		return Tools::safeOutput( $string );
	}
	
	public static function esc_attr__( $text, $domain = 'elementor' ) {
		return self::module_translate( $text, $domain );
	}
	
	public static function esc_attr_e( $text, $domain = 'elementor' ) {
		echo self::module_translate( $text, $domain );
	}
	
	public static function esc_html( $text ) {
		return $text;
	}
	
	public static function _n( $single, $plural, $number, $domain = 'elementor' ) {
		if( $number > 1 ){
			return self::module_translate( $plural, $domain );
		}else{
			return self::module_translate( $single, $domain );
		}
	}
	
	public static function module_translate( $text, $domain = 'elementor' )	{
		return Tools::safeOutput( $text );
	}
	
	public static function sanitize_html_class( $class, $fallback = '' ) {
		$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $class );

		$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized );

		if ( '' === $sanitized && $fallback ) {
			return self::sanitize_html_class( $fallback );
		}

		return self::apply_filters( 'sanitize_html_class', $sanitized, $class, $fallback );
	}
	
	public static function wp_print_head_scripts() {
		while ( $args = array_shift( self::$wp_head_scripts ) ) {
			self::wp_print_script( $args );
		}
	}

	public static function wp_print_footer_scripts() {
		while ( $args = array_shift( self::$wp_foot_scripts ) ) {
			self::wp_print_script( $args );
		}
	}

	public static function wp_print_script( &$args ) {
		if (!empty($args['l10n'])) {
			echo '<script type="text/javascript">' . PHP_EOL;
			foreach ($args['l10n'] as $key => &$value) {
				$json = json_encode($value);
				echo "var $key = " . str_replace('}},"', "}},\n\"", $json) . ";\n";
			}
			echo '</script>' . PHP_EOL;
		}
		if (!empty($args['ver'])) {
			$args['src'] .= (stripos($args['src'], '?') === false ? '?' : '&') . 'v=' . $args['ver'];
		}
		if (!empty($args['src'])) {
			echo '<script src="' . $args['src'] . '" type="text/javascript"></script>' . PHP_EOL;
		}
	}
	
	public static function wp_print_jquery() {	
		echo '<script src="' . AXON_CREATOR_ASSETS_URL . 'lib/jquery/jquery-1.12.4.min.js" type="text/javascript"></script>';
	}
	
	public static function wp_enqueue_scripts() {
		self::wp_enqueue_script('ui', AXON_CREATOR_ASSETS_URL . 'lib/jquery/jquery-ui.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('core', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/core.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('widget', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/widget.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('mouse', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/mouse.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('sortable', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/sortable.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('position', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/position.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('menu', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/menu.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('draggable', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/draggable.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('resizable', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/resizable.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('slider', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/slider.min.js', ['jquery'], '1.11.4', true);
		self::wp_enqueue_script('touch', AXON_CREATOR_ASSETS_URL . 'lib/jquery/ui/touch-punch.min.js', ['jquery'], '1.11.4', true);

		self::wp_enqueue_script('wp-underscore', AXON_CREATOR_ASSETS_URL . 'lib/wp-include/underscore.min.js', [], '', true);
		self::wp_enqueue_script('wp-backbone', AXON_CREATOR_ASSETS_URL . 'lib/wp-include/backbone.min.js', [], '', true);

		self::do_action( 'wp_enqueue_scripts' );
	}
		
	public static function wp_register_script( $handle, $src, $deps = [], $ver = false, $in_footer = false ) {
		if ( !isset( self::$wp_scripts[$handle] ) ) {
			self::$wp_scripts[$handle] = [
				'src' => $src,
				'deps' => $deps,
				'ver' => $ver,
				'head' => !$in_footer,
				'l10n' => [],
			];
		}
		return true;
	}

	public static function wp_enqueue_script( $handle, $src = '', $deps = [], $ver = false, $in_footer = false ) {
		empty( $src ) or self::wp_register_script( $handle, $src, $deps, $ver, $in_footer );

		if ( !empty( self::$wp_scripts[$handle] ) && empty( self::$wp_head_scripts[$handle] ) && empty( self::$wp_foot_scripts[$handle] ) ) {
			foreach ( self::$wp_scripts[$handle]['deps'] as $dep ) {
				self::wp_enqueue_script( $dep );
			}

			if ( self::$wp_scripts[$handle]['head'] ) {
				self::$wp_head_scripts[$handle] = &self::$wp_scripts[$handle];
			} else {
				self::$wp_foot_scripts[$handle] = &self::$wp_scripts[$handle];
			}
			unset( self::$wp_scripts[$handle] );
		}
	}
	
	public static function wp_localize_script( $handle, $object_name, $l10n ) {
		if ( isset( self::$wp_scripts[$handle] ) ) {
			self::$wp_scripts[$handle]['l10n'][$object_name] = $l10n;
		} elseif ( isset( self::$wp_head_scripts[$handle] ) ) {
			self::$wp_head_scripts[$handle]['l10n'][$object_name] = $l10n;
		} elseif ( isset( self::$wp_foot_scripts[$handle] ) ) {
			self::$wp_foot_scripts[$handle]['l10n'][$object_name] = $l10n;
		}
	}
		
	public static function wp_register_style( $handle, $src, $deps = [], $ver = false, $media = 'all' ) {
		if ( !isset( self::$wp_styles[$handle] ) ) {
			self::$wp_styles[$handle] = [
				'src' => $src,
				'deps' => $deps,
				'ver' => $ver,
				'media' => $media,
			];
		}
		return true;
	}
	
	public static function wp_enqueue_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all' ) {
		empty( $src ) or self::wp_register_style( $handle, $src, $deps, $ver, $media );

		if ( !empty( self::$wp_styles[$handle] ) && empty( self::$wp_head_styles[$handle] ) ) {
			foreach ( self::$wp_styles[$handle]['deps'] as $dep ) {
				self::wp_enqueue_style( $dep );
			}

			self::$wp_head_styles[$handle] = &self::$wp_styles[$handle];
			unset( self::$wp_styles[$handle] );
		}
	}
	
	public static function wp_print_styles() {
		while ( $args = array_shift( self::$wp_head_styles ) ) {
			if ( $args['ver'] ) {
				$args['src'] .= ( \Tools::strpos( $args['src'], '?' ) === false ? '?' : '&' ) . 'ver=' . $args['ver'];
			}
			echo '<link rel="stylesheet" href="' . $args['src'] . '" media="' . $args['media'] . '" />' . PHP_EOL;
		}
	}
	
	public static function wp_parse_args( $args, $defaults = [] ) {
		if ( is_object( $args ) ) {
			$parsed_args = get_object_vars( $args );
		} elseif ( is_array( $args ) ) {
			$parsed_args =& $args;
		} else {
			self::wp_parse_str( $args, $parsed_args );
		}

		if ( is_array( $defaults ) && $defaults ) {
			return array_merge( $defaults, $parsed_args );
		}
		return $parsed_args;
	}

	public static function wp_parse_str( $string, &$array ) {
		parse_str( $string, $array );
		$array = self::apply_filters( 'wp_parse_str', $array );
	}
	
	public static function add_query_arg( ...$args ) {
		if ( is_array( $args[0] ) ) {
			if ( count( $args ) < 2 || false === $args[1] ) {
				$uri = $_SERVER['REQUEST_URI'];
			} else {
				$uri = $args[1];
			}
		} else {
			if ( count( $args ) < 3 || false === $args[2] ) {
				$uri = $_SERVER['REQUEST_URI'];
			} else {
				$uri = $args[2];
			}
		}

		$frag = strstr( $uri, '#' );
		if ( $frag ) {
			$uri = substr( $uri, 0, -strlen( $frag ) );
		} else {
			$frag = '';
		}

		if ( 0 === stripos( $uri, 'http://' ) ) {
			$protocol = 'http://';
			$uri      = substr( $uri, 7 );
		} elseif ( 0 === stripos( $uri, 'https://' ) ) {
			$protocol = 'https://';
			$uri      = substr( $uri, 8 );
		} else {
			$protocol = '';
		}

		if ( strpos( $uri, '?' ) !== false ) {
			list( $base, $query ) = explode( '?', $uri, 2 );
			$base                .= '?';
		} elseif ( $protocol || strpos( $uri, '=' ) === false ) {
			$base  = $uri . '?';
			$query = '';
		} else {
			$base  = '';
			$query = $uri;
		}

		self::wp_parse_str( $query, $qs );
		$qs = self::urlencode_deep( $qs ); // This re-URL-encodes things that were already in the query string.
		if ( is_array( $args[0] ) ) {
			foreach ( $args[0] as $k => $v ) {
				$qs[ $k ] = $v;
			}
		} else {
			$qs[ $args[0] ] = $args[1];
		}

		foreach ( $qs as $k => $v ) {
			if ( false === $v ) {
				unset( $qs[ $k ] );
			}
		}

		$ret = self::build_query( $qs );
		$ret = trim( $ret, '?' );
		$ret = preg_replace( '#=(&|$)#', '$1', $ret );
		$ret = $protocol . $base . $ret . $frag;
		$ret = rtrim( $ret, '?' );
		return $ret;
	}
	
	public static function urlencode_deep( $value ) {
		return self::map_deep( $value, 'urlencode' );
	}
	
	public static function map_deep( $value, $callback ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
				$value[ $index ] = self::map_deep( $item, $callback );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );
			foreach ( $object_vars as $property_name => $property_value ) {
				$value->$property_name = self::map_deep( $property_value, $callback );
			}
		} else {
			$value = call_user_func( $callback, $value );
		}

		return $value;
	}
	
	public static function build_query( $data ) {
		return self::_http_build_query( $data, null, '&', '', false );
	}

	public static function _http_build_query( $data, $prefix = null, $sep = null, $key = '', $urlencode = true ) {
		$ret = array();

		foreach ( (array) $data as $k => $v ) {
			if ( $urlencode ) {
				$k = urlencode( $k );
			}
			if ( is_int( $k ) && null != $prefix ) {
				$k = $prefix . $k;
			}
			if ( ! empty( $key ) ) {
				$k = $key . '%5B' . $k . '%5D';
			}
			if ( null === $v ) {
				continue;
			} elseif ( false === $v ) {
				$v = '0';
			}

			if ( is_array( $v ) || is_object( $v ) ) {
				array_push( $ret, self::_http_build_query( $v, '', $sep, $k, $urlencode ) );
			} elseif ( $urlencode ) {
				array_push( $ret, $k . '=' . urlencode( $v ) );
			} else {
				array_push( $ret, $k . '=' . $v );
			}
		}

		if ( null === $sep ) {
			$sep = ini_get( 'arg_separator.output' );
		}

		return implode( $sep, $ret );
	}
		
	public static function is_wp_error( $thing ) {
		return $thing instanceof \PrestaShopException;
	}
	
	public static function wp_remote_retrieve_response_code( $response ) {
		if ( self::is_wp_error( $response ) || ! isset( $response['response'] ) || ! is_array( $response['response'] ) ) {
			return '';
		}

		return $response['response']['code'];
	}
	
	public static function wp_remote_retrieve_body( $response ) {
		if ( self::is_wp_error( $response ) || ! isset( $response['body'] ) ) {
			return '';
		}

		return $response['body'];
	}
	
	public static function wp_remote_post( $url, array $args = [] ) {
		$defaults    = array( 'method' => 'POST' );
        $parsed_args = self::wp_parse_args( $args, $defaults );
        return self::wp_request( $url, $parsed_args );
	}

	public static function wp_remote_get( $url, array $args = [] ) {
        $defaults    = array( 'method' => 'GET' );
        $parsed_args = self::wp_parse_args( $args, $defaults );
        return self::wp_request( $url, $parsed_args );
	}
	
	public static function wp_request( $url, $args = array() ) {
		$defaults = array(
			'method'      => 'GET',
			'timeout'     => 5,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array(),
			'body'        => null,
			'user-agent'  => $_SERVER['SERVER_SOFTWARE'],
		);

		$parsed_args = self::wp_parse_args( $args, $defaults );

		if ( isset( $parsed_args['headers']['User-Agent'] ) ) {
			$parsed_args['user-agent'] = $parsed_args['headers']['User-Agent'];
			unset( $parsed_args['headers']['User-Agent'] );
		} elseif ( isset( $parsed_args['headers']['user-agent'] ) ) {
			$parsed_args['user-agent'] = $parsed_args['headers']['user-agent'];
			unset( $parsed_args['headers']['user-agent'] );
		}

		$handle = curl_init();

		$ssl_verify = isset( $parsed_args['sslverify'] ) && $parsed_args['sslverify'];

		$timeout = (int) ceil( $parsed_args['timeout'] );
		
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $handle, CURLOPT_TIMEOUT, $timeout );

		curl_setopt( $handle, CURLOPT_URL, $url );
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, ( true === $ssl_verify ) ? 2 : false );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, $ssl_verify );

		if ( $ssl_verify ) {
			curl_setopt( $handle, CURLOPT_CAINFO, $parsed_args['sslcertificates'] );
		}

		curl_setopt( $handle, CURLOPT_USERAGENT, $parsed_args['user-agent'] );

		curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, false );
		curl_setopt( $handle, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS );

		switch ( $parsed_args['method'] ) {
			case 'HEAD':
				curl_setopt( $handle, CURLOPT_NOBODY, true );
				break;
			case 'POST':
				curl_setopt( $handle, CURLOPT_POST, true );
				curl_setopt( $handle, CURLOPT_POSTFIELDS, $parsed_args['body'] );
				break;
			case 'PUT':
				curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, 'PUT' );
				curl_setopt( $handle, CURLOPT_POSTFIELDS, $parsed_args['body'] );
				break;
			default:
				curl_setopt( $handle, CURLOPT_CUSTOMREQUEST, $parsed_args['method'] );
				if ( ! is_null( $parsed_args['body'] ) ) {
					curl_setopt( $handle, CURLOPT_POSTFIELDS, $parsed_args['body'] );
				}
				break;
		}

		curl_setopt( $handle, CURLOPT_HEADER, false );

		if ( ! empty( $parsed_args['headers'] ) ) {
			$headers = array();
			foreach ( $parsed_args['headers'] as $name => $value ) {
				$headers[] = "{$name}: $value";
			}
			curl_setopt( $handle, CURLOPT_HTTPHEADER, $headers );
		}

		if ( '1.0' === $parsed_args['httpversion'] ) {
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
		} else {
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		}

		$curl_exec = curl_exec( $handle );
		
		$curl_info = curl_getinfo( $handle );
		
		curl_close( $handle );

		$theHeaders = [];
		$theHeaders['response']['code'] = abs( (int) $curl_info['http_code'] );

		$response = array(
			'body'     => $curl_exec,
			'response' => $theHeaders['response'],
		);

		return $response;
	}
	
	public static function api_get_option( $option, $default = false ) {		
		$value = self::get_post_meta( Context::getContext()->shop->id, $option );
		
		if ( false === $value ) {
			return $default;
		}
		
		return $value;
	}
	
	public static function api_update_option( $option, $value = '' ) {		
		return self::update_post_meta( Context::getContext()->shop->id, $option, $value );
	}

	public static function api_delete_option( $option ) {		
		return self::delete_post_meta( Context::getContext()->shop->id, $option );
	}
		
	private static function api_remote_post( $body_args = [] ) {
		$body_args = self::wp_parse_args(
			$body_args,
			[
				'api_version' => AXON_CREATOR_VERSION,
				'item_name' => self::PRODUCT_NAME,
				'url' => self::getShopDomainSsl(true),
			]
		);

		$response = self::wp_remote_post( self::STORE_URL, [
			'timeout' => 40,
			'body' => $body_args,
		] );

		if ( self::is_wp_error( $response ) ) {
			return $response;
		}
				
		$response_code = self::wp_remote_retrieve_response_code( $response );
		
		if ( 200 !== (int) $response_code ) {
			return 'HTTP Error ' . $response_code;
		}

		$data = json_decode( self::wp_remote_retrieve_body( $response ), true );
		if ( empty( $data ) || ! is_array( $data ) ) {
			return 'An error occurred, please try again ( no_json )';
		}
		
		return $data;
	}
	
	public static function api_activate_license( $license_key ) {
		$body_args = [
			'edd_action' => 'activate_license',
			'license' => $license_key,
		];

		$license_data = self::api_remote_post( $body_args );

		return $license_data;
	}
	
	public static function api_deactivate_license( $license_key = null, $url = null ) {
		$body_args = [
			'edd_action' => 'deactivate_license',
			'license' => $license_key ? $license_key : self::api_get_license_key(),
		];

        if($url){
            $body_args['url'] = $url;
        }

		$license_data = self::api_remote_post( $body_args );

		return $license_data;
	}
		
	public static function api_set_transient( $cache_key, $value, $expiration = '+12 hours' ) {
		$data = [
			'timeout' => strtotime( $expiration, self::current_time() ),
			'value' => json_encode( $value ),
		];
		
		self::api_update_option( $cache_key, $data );
	}

	private static function api_get_transient( $cache_key ) {
		$cache = self::api_get_option( $cache_key );

		if ( empty( $cache['timeout'] ) || self::current_time() > $cache['timeout'] ) {
			return false;
		}

		return json_decode( $cache['value'], true );
	}

	public static function api_set_license_data( $license_data, $expiration = null ) {
		if ( null === $expiration ) {
			$expiration = '+12 hours';

			self::api_set_transient( self::LICENSE_DATA_FALLBACK_OPTION_NAME, $license_data, '+24 hours' );
		}

		self::api_set_transient( self::LICENSE_DATA_OPTION_NAME, $license_data, $expiration );
	}
	
	public static function api_is_request_running( $name ) {
		$requests_lock = self::api_get_option( self::REQUEST_LOCK_OPTION_NAME, [] );
		if ( isset( $requests_lock[ $name ] ) ) {
			if ( $requests_lock[ $name ] > time() - self::REQUEST_LOCK_TTL ) {
				return true;
			}
		}

		$requests_lock[ $name ] = time();
		self::api_update_option( self::REQUEST_LOCK_OPTION_NAME, $requests_lock );

		return false;
	}
	
	public static function api_get_license_data( $force_request = false ) {
		$license_data_error = [
			'license' => 'http_error',
			'payment_id' => '0',
			'license_limit' => '0',
			'site_count' => '0',
			'activations_left' => '0',
			'success' => false,
		];

		$license_key = self::api_get_license_key();
		if ( empty( $license_key ) ) {
			return $license_data_error;
		}

		$license_data = self::api_get_transient( self::LICENSE_DATA_OPTION_NAME );

		if ( false === $license_data || $force_request ) {
			$body_args = [
				'edd_action' => 'check_license',
				'license' => $license_key,
			];

			if ( self::api_is_request_running( 'get_license_data' ) ) {
				return $license_data_error;
			}

			$license_data = self::api_remote_post( $body_args );

			if ( self::is_wp_error( $license_data ) ) {
				$license_data = self::api_get_transient( self::LICENSE_DATA_FALLBACK_OPTION_NAME );
				if ( false === $license_data ) {
					$license_data = $license_data_error;
				}

				self::api_set_license_data( $license_data, '+30 minutes' );
			} else {
				self::api_set_license_data( $license_data );
			}
		}

		return $license_data;
	}
	
	public static function api_get_errors() {
		return [
			'no_activations_left' =>self::__( 'You have no more activations left. Please upgrade to a more advanced license (you\'ll only need to cover the difference).', 'elementor' ),
			'expired' => self::__( 'Your License Has Expired. Renew your license today to keep getting feature updates, premium support and unlimited access to the template library.', 'elementor' ),
			'missing' => self::__( 'Your license is missing. Please check your key again.', 'elementor' ),
			'revoked' => self::__( 'Your license key has been cancelled (most likely due to a refund request). Please consider acquiring a new license.', 'elementor' ),
			'key_mismatch' => self::__( 'Your license is invalid for this domain. Please check your key again.', 'elementor' ),
		];
	}

	public static function api_get_error_message( $error ) {
		$errors = self::api_get_errors();

		if ( isset( $errors[ $error ] ) ) {
			$error_msg = $errors[ $error ];
		} else {
			$error_msg = self::__( 'An error occurred. Please check your internet connection and try again. If the problem persists, contact our support.', 'elementor' ) . ' (' . $error . ')';
		}

		return $error_msg;
	}
	
	public static function api_is_license_active() {
		$license_data = self::api_get_license_data();
		
		if( !isset( $license_data['license'] ) ){
			$license_data = self::api_get_license_data( true );
		}

		return self::STATUS_VALID === $license_data['license'];
	}

	public static function api_is_license_about_to_expire() {
		$license_data = self::api_get_license_data();
		
		if( !isset( $license_data['license'] ) ){
			$license_data = self::api_get_license_data( true );
		}

		if ( ! empty( $license_data['subscriptions'] ) && 'enable' === $license_data['subscriptions'] ) {
			return false;
		}

		if ( 'lifetime' === $license_data['expires'] ) {
			return false;
		}

		return time() > strtotime( '-28 days', strtotime( $license_data['expires'] ) );
	}
		
	public static function api_deactivate( $license_key = null, $url = null ) {
		$license_data = self::api_deactivate_license( $license_key, $url );

		self::api_delete_option( self::LICENSE_KEY_OPTION_NAME );
		self::api_delete_option( self::LICENSE_DATA_OPTION_NAME );
		self::api_delete_option( self::LICENSE_DATA_FALLBACK_OPTION_NAME );

        return $license_data;
	}
	
	public static function api_get_hidden_license_key() {
		$input_string = self::api_get_license_key();

		$start = 5;
		$length = mb_strlen( $input_string ) - $start - 5;

		$mask_string = preg_replace( '/\S/', 'X', $input_string );
		$mask_string = mb_substr( $mask_string, $start, $length );
		$input_string = substr_replace( $input_string, $mask_string, $start, $length );

		return $input_string;
	}
		
	public static function api_get_license_key() {
		return trim( self::api_get_option( self::LICENSE_KEY_OPTION_NAME ) );
	}

	public static function api_set_license_key( $license_key ) {
		return self::api_update_option( self::LICENSE_KEY_OPTION_NAME, $license_key );
	}
	
	public static function api_get_notification() {
		$license_key = self::api_get_license_key();
		
		$html = '';
		
		if ( empty( $license_key ) ) {
			$html = '<div class="alert alert-danger">' . self::__( 'Enter your license key here, to activate AxonCreator, and get feature updates, premium support and unlimited access to the template library.', 'elementor' ) . ' <a href="' . self::get_exit_to_dashboard( 'AdminAxonCreatorLicense' ) . '">' . self::__( 'Click here.', 'elementor' ) . '</a>' . '</div>';
		}else{
			$license_data = self::api_get_license_data();
			
			if( !isset( $license_data['license'] ) ){
				$license_data = self::api_get_license_data( true );
			}
			
			if ( self::STATUS_EXPIRED === $license_data['license'] ) {
				$html = '<div class="alert alert-danger">' . self::__( 'Your License Has Expired. Renew your license today to keep getting feature updates, premium support and unlimited access to the template library.', 'elementor' ) . ' <a href="' . self::get_exit_to_dashboard( 'AdminAxonCreatorLicense' ) . '">' . self::__( 'Click here.', 'elementor' ) . '</a>' . '</div>';
			}
			
			if ( self::STATUS_SITE_INACTIVE === $license_data['license'] ) {
				$html = '<div class="alert alert-danger">' . self::__( 'Your license key doesn\'t match your current domain. This is most likely due to a change in the domain URL of your site (including HTTPS/SSL migration). Please deactivate the license and then reactivate it again.', 'elementor' ) . ' <a href="' . self::get_exit_to_dashboard( 'AdminAxonCreatorLicense' ) . '">' . self::__( 'Click here.', 'elementor' ) . '</a>' . '</div>';
			}
			
			if ( self::STATUS_INVALID === $license_data['license'] ) {
				$html = '<div class="alert alert-danger">' . self::__( 'Your license key doesn\'t match your current domain. This is most likely due to a change in the domain URL of your site (including HTTPS/SSL migration). Please deactivate the license and then reactivate it again.', 'elementor' ) . ' <a href="' . self::get_exit_to_dashboard( 'AdminAxonCreatorLicense' ) . '">' . self::__( 'Click here.', 'elementor' ) . '</a>' . '</div>';
			}

			if ( self::STATUS_DISABLED === $license_data['license'] ) {
				$html = '<div class="alert alert-danger">' . self::__( 'Your license key has been cancelled (most likely due to a refund request). Please consider acquiring a new license.', 'elementor' ) . ' <a href="' . self::get_exit_to_dashboard( 'AdminAxonCreatorLicense' ) . '">' . self::__( 'Click here.', 'elementor' ) . '</a>' . '</div>';
			}
		}
		
		return $html;
	}
		
	public static function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		if ( ! isset( self::$wp_actions[$tag] ) ) {
			self::$wp_actions[$tag] = [];
		}
		
		if ( ! isset( self::$wp_actions[$tag][$priority] ) ) {
			self::$wp_actions[$tag][$priority] = [];
		}
				
		self::$wp_actions[$tag][$priority][] = $function_to_add;
	}

	public static function do_action( $tag, $arg = '' ) {
		if (isset(self::$wp_actions[$tag])) {
			$wp_actions = &self::$wp_actions[$tag];

			$args = func_get_args(); array_shift( $args );

			$priorities = array_keys( $wp_actions );

			sort( $priorities, SORT_NUMERIC );
			
			self::$current_action = $tag;
			
			foreach ( $priorities as $priority ) {
				foreach ( $wp_actions[$priority] as $function_to_add ) {
					call_user_func_array( $function_to_add, $args );
				}
			}
			
			self::$current_action = '';
		}
	}
		
	public static function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		if ( ! isset( self::$wp_filters[$tag] ) ) {
			self::$wp_filters[$tag] = [];
		}
		
		if ( ! isset( self::$wp_filters[$tag][$priority] ) ) {
			self::$wp_filters[$tag][$priority] = [];
		}
				
		self::$wp_filters[$tag][$priority][] = $function_to_add;
	}

	public static function apply_filters( $tag, $value ) {
		if (isset(self::$wp_filters[$tag])) {
			$wp_filters = &self::$wp_filters[$tag];

			$args = func_get_args(); array_shift( $args );

			$priorities = array_keys( $wp_filters );

			sort( $priorities, SORT_NUMERIC );
			
			self::$current_filter = $tag;
			
			foreach ( $priorities as $priority ) {
				foreach ( $wp_filters[$priority] as $function_to_add ) {
					$value = call_user_func_array( $function_to_add, $args );
				}
			}
			
			self::$current_filter = '';
		}
		
		return $value;
	}
	
	public static function admin_url() {
		return '';
	}
	
	public static function wp_allowed_protocols() {
		static $protocols = [];

		if ( empty( $protocols ) ) {
			$protocols = [ 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'irc6', 'ircs', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'sms', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn' ];
		}

		return array_unique( (array) self::apply_filters( 'kses_allowed_protocols', $protocols ) );
	}
	
	public static function get_permalink_template( $elementor_library ) {
		return Context::getContext()->link->getModuleLink( 'axoncreator', 'preview', [ 'elementor_library' => $elementor_library ], true );
	}
	
	public static function get_permalink( $params = [] ) {
        $id_post = self::$id_post;
        $id_lang = self::$id_lang;
        $id_shop = self::$id_shop;
        $post_type = self::$post_type;
        $key_related = self::$key_related;
		$permalink = '';
		$context = Context::getContext();

		switch ( $post_type ) {
            case 'hook':
					if ( $key_related == 'displayProductSummary' || $key_related == 'displayFooterProduct' || $key_related == 'displayLeftColumnProduct' || $key_related == 'displayRightColumnProduct' || $key_related == 'displayProductAccessories' || $key_related == 'displayProductSameCategory' ) {
						$products = \Product::getProducts($id_lang, 0, 1, 'date_add', 'DESC', false, true);
						$product = new \Product(!empty($products[0]['id_product']) ? $products[0]['id_product'] : null, false, $id_lang);
                        
                        $permalink = $context->link->getProductLink( $product, null, null, null, $id_lang, $id_shop );

						if( $key_related == 'displayLeftColumnProduct' ){
							$params['opt'] = 'layout-3';
						}
						if( $key_related == 'displayRightColumnProduct' ){
							$params['opt'] = 'layout-1';
						}
					}elseif( $key_related == 'displayLeftColumn' || $key_related == 'displayRightColumn' || $key_related == 'displayHeaderCategory' || $key_related == 'displayFooterCategory' ){
						$permalink = $context->link->getPageLink( 'new-products', null, $id_lang, null, false, $id_shop, false );
						
						if( $key_related == 'displayLeftColumn' ){
							$params['opt'] = 'layout-1';
						}
						if( $key_related == 'displayRightColumn' ){
							$params['opt'] = 'layout-2';
						}
					}elseif( $key_related == 'displayContactPageBuilder' ){
						$permalink = $context->link->getPageLink( 'contact', null, $id_lang, null, false, $id_shop, false );
						
						$params['opt'] = 'contact-1';
					}elseif( $key_related == 'displayShoppingCartFooter' ){
						$permalink = $context->link->getPageLink( 'cart', null, $id_lang, null, false, $id_shop, false );
						
						$params['action'] = 'show';
					}elseif( $key_related == 'display404PageBuilder' ){
						$permalink = $context->link->getPageLink( 'pagenotfound', null, $id_lang, null, false, $id_shop, false );
						
						$params['opt'] = '404-1';
					}
                break;
            case 'category':
					$permalink = $context->link->getCategoryLink( $key_related, null, $id_lang, null, $id_shop );
                break;
            case 'product':
					$permalink = $context->link->getProductLink( $key_related, null, null, null, $id_lang, $id_shop );
                break;
            case 'cms':
					$permalink = $context->link->getCMSLink( $key_related, null, true, $id_lang, $id_shop );
                break;
            case 'manufacturer':
					$permalink = $context->link->getManufacturerLink( $key_related, null, $id_lang, $id_shop );
                break;
            case 'supplier':
					$permalink = $context->link->getSupplierLink( $key_related, null, $id_lang, $id_shop );
                break;
            case 'blog':
				$blog = new \SmartBlogPost( $key_related, $id_lang, $id_shop );
				$permalink = \SmartBlogLink::getSmartBlogPostLink( $blog->id, $blog->link_rewrite );
                break;
            default:
                $permalink = $context->link->getPageLink( 'index', null, $id_lang, null, false, $id_shop, false );
                if( Configuration::get('PS_REWRITING_SETTINGS') ){
					$permalink = preg_replace( '~[^/]+$~', '', $permalink );
                }
                break;
		}
		return $permalink . ( $params ? ( strpos( $permalink, '?' ) ? '&' : '?' ) . self::build_query( $params ) : '' ) ;
	}
	
	public static function get_ajax_editor( $params = [] ) {
		$context = Context::getContext();
        $id_post = self::$id_post;
        $id_lang = self::$id_lang;
        $id_shop = self::$id_shop;
        $post_type = self::$post_type;
        $key_related = self::$key_related;
		
		$id_employee = isset( $context->employee->id ) && $context->employee->id ? $context->employee->id : Tools::getValue( 'id_employee' );
		
		if( !$params ){
			$params = [ 'id_post' => $id_post, 'key_related' => $key_related, 
						'id_lang' => $id_lang, 'id_shop' => $id_shop, 
						'post_type' => $post_type, 'id_employee' => $id_employee ];
		}
		
		return $context->link->getModuleLink( 'axoncreator', 'ajax_editor', $params, null, $id_lang, $id_shop );
	}
	
	public static function get_the_ID() {
		return self::$id_post;
	}
	
	public static function get_base_admin_dir() {		
		return __PS_BASE_URI__ . basename( _PS_ADMIN_DIR_ ) . '/';
	}
	
	public static function get_elementor_editor_suffix() {		
		return '';
	}
	
	public static function render_widget() {		
		return true;
	}
	
    public static function get_exit_to_dashboard( $c, $params = [] ) {						
		return 'index.php?' . self::build_query( array_merge( [ 'controller' => $c, 'token' => Tools::getAdminTokenLite( $c ) ], $params ) );
	}
		
	public static function has_print_export_link() {			
		return true;
	}
	
	public static function is_admin() {		
		return !empty( Context::getContext()->controller->name ) && Context::getContext()->controller->name == 'AxonCreatorEditor';
	}
	
	public static function is_preview_mode() {
		return Tools::getValue('front_token') && (Tools::getValue('front_token') == self::getFrontToken()) && Tools::getIsset('id_employee') && self::checkEnvironment();
	}

    public static function checkEnvironment()
    {
        $cookie = new \Cookie('psAdmin', '', (int)Configuration::get('PS_COOKIE_LIFETIME_BO'));
        return isset($cookie->id_employee) && isset($cookie->passwd) && \Employee::checkPassword($cookie->id_employee, $cookie->passwd);
    }

    public static function getFrontToken()
    {
		return Tools::hash('AxonCreatorEditor'.(isset(Context::getContext()->employee->id) ? (int)Context::getContext()->employee->id :
                (int)Tools::getValue('id_employee')));		
    }

    public static function getRelatedByKey() {		
        $sql = 'SELECT * 
		        FROM `'._DB_PREFIX_.'axon_creator_related` bc
				LEFT JOIN `'._DB_PREFIX_.'axon_creator_related_shop` bcs
					 ON (bc.`id_axon_creator_related` = bcs.`id_axon_creator_related`)
				WHERE bcs.`id_shop` = ' . pSQL ( self::$id_shop ) . '
		        AND bc.`post_type` = \'' . pSQL ( self::$post_type ) . '\'
		        AND bc.`key_related` = \'' . pSQL ( self::$key_related ) . '\'';
		
		return Db::getInstance()->getRow( $sql );
    }
	
    public static function getRelatedByIdPost() {		
        $sql = 'SELECT * 
		        FROM `'._DB_PREFIX_.'axon_creator_related` bc
				LEFT JOIN `'._DB_PREFIX_.'axon_creator_related_shop` bcs
					 ON (bc.`id_axon_creator_related` = bcs.`id_axon_creator_related`)
				WHERE bcs.`id_shop` = ' . pSQL ( self::$id_shop ) . '
		        AND bc.`id_post` = ' . pSQL ( self::$id_post );
		
		return Db::getInstance()->getRow( $sql );
    }
	
    public static function getTemplatesLocal() {		
        $sql = 'SELECT * 
		        FROM `'._DB_PREFIX_.'axon_creator_template`';
		
		return Db::getInstance()->executeS( $sql );
    }
	
	public static function get_gmt_from_date( $string, $format = 'Y-m-d H:i:s' ) {
		$datetime = date_create( $string, new \DateTimeZone( Configuration::get( 'PS_TIMEZONE' ) ) );

		if ( false === $datetime ) {
			return gmdate( $format, 0 );
		}

		return $datetime->setTimezone( new \DateTimeZone( 'UTC' ) )->format( $format );
	}
	
	public static function is_rtl() {
		return !empty( Context::getContext()->language->is_rtl );
	}
	
	public static function esc_url( $url, $protocols = null, $_context = 'display' ) {
		if ('' == $url) {
			return $url;
		}
		
		$url = str_replace( ' ', '%20', $url );
		
		$url = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url );
		
		if ( '' === $url ) {
			return $url;
		}
		
		$url = str_replace( ';//', '://', $url );
		
		if ( strpos( $url, ':' ) === false && !in_array( $url[0], [ '/', '#', '?' ] ) && !preg_match( '/^[a-z0-9-]+?\.php/i', $url ) ) {
			$url = 'http://' . $url;
		}
		
		return $url;
	}
	
	public static function set_transient( $transient, $value, $expiration = 0 ) {
		$expiration = (int) $expiration;
		
        $transient_timeout = '_transient_timeout_' . $transient;
        $transient_option  = '_transient_' . $transient;
		
    	$id_shop = (int) self::$id_shop;
		
        if ( false === self::get_post_meta( $id_shop, $transient_option, true ) ) {
            if ( $expiration ) {
                self::update_post_meta( $id_shop, $transient_timeout, time() + $expiration );
            }
            $result = self::update_post_meta( $id_shop, $transient_option, $value );
        } else {
            $update = true;
 
            if ( $expiration ) {
                if ( false === self::get_post_meta( $id_shop, $transient_timeout, true ) ) {
                    self::update_post_meta( $id_shop, $transient_timeout, time() + $expiration );
                    $result = self::update_post_meta( $id_shop, $transient_option, $value );
                    $update = false;
                } else {
                    self::update_post_meta( $id_shop, $transient_timeout, time() + $expiration );
                }
            }
 
            if ( $update ) {
                $result = self::update_post_meta( $id_shop, $transient_option, $value );
            }
        }
		
		return $result;
	}
	
	public static function get_transient( $transient ) {
		$transient_option = '_transient_' . $transient;
		$transient_timeout = '_transient_timeout_' . $transient;
		
    	$id_shop = (int) self::$id_shop;
		
    	$timeout = self::get_post_meta( $id_shop, $transient_timeout, true );
		
		if ( false !== $timeout && $timeout < time() ) {
			self::delete_post_meta( $id_shop, $transient_option );
			self::delete_post_meta( $id_shop, $transient_timeout );
			return false;
		}
		
		return self::get_post_meta( $id_shop, $transient_option, true );
	}
		
	public static function get_post_meta( $id, $key = '', $single = false ) {	
		$db = Db::getInstance();
    	$table = _DB_PREFIX_ . 'axon_creator_meta';
		$id = preg_replace('/\D+/', '', $id);
		$key = preg_replace('/\W+/', '', $key);

		$value = $db->getValue("SELECT value FROM $table WHERE id = $id AND name = '$key'");

		return isset($value[0]) && ('{' == $value[0] || '[' == $value[0] || '"' == $value[0]) ? json_decode($value, true) : $value;
	}

	public static function update_post_meta( $id, $key, $value = '' ) {
		$db = Db::getInstance();
    	$table = _DB_PREFIX_ . 'axon_creator_meta';
		$data = [
			'id' => preg_replace( '/\D+/', '', $id ),
			'name' => preg_replace( '/\W+/', '', $key ),
			'value' => $db->escape( is_array( $value ) || is_object( $value ) ? json_encode( $value ) : $value, true ),
		];
		$id_meta = $db->getValue("SELECT id_axon_creator_meta FROM $table WHERE id = {$data['id']} AND name = '{$data['name']}'");

		if ( $id_meta ) {
			$data['id_axon_creator_meta'] = $id_meta;
			$type = Db::REPLACE;
		} else {
			$type = Db::INSERT;
		}
		
		return $db->insert($table, $data, false, true, $type, false);
	}

	public static function delete_post_meta( $id, $key ) {
		$db = Db::getInstance();
		$id = preg_replace('/\D+/', '', $id);
		$key = preg_replace('/[^\w\%]+/', '', $key);

		return $db->delete('axon_creator_meta', "id = $id AND name = '$key'");
	}
	
	public static function delete_post_meta_by_key( $key ) {
		$result = true;
		
		$db = Db::getInstance();
    	$table = _DB_PREFIX_ . 'axon_creator_meta';
		$key = preg_replace('/\W+/', '', $key);

		$metas = $db->executeS("SELECT id, name FROM $table WHERE name like '%$key%'");

		foreach ( $metas as $meta ) {
			$result &= self::delete_post_meta( $meta['id'], $meta['name'] );
		}
		
		return $result;
	}
	
	public static function get_user_meta( $user_id, $key = '', $single = false ) {
		return self::get_post_meta( $user_id, $key, true );
	}
	
	public static function update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
		return self::update_post_meta( $user_id, $meta_key, $meta_value );
	}
	
	public static function get_current_user_id() {
		$context = Context::getContext();
		return isset( $context->employee->id ) && $context->employee->id ? $context->employee->id : Tools::getValue( 'id_employee' );
	}

	public static function get_option( $option, $default = false ) {
    	$id_shop = (int) self::$id_shop;
		
		$value = self::get_post_meta( $id_shop, $option );
		
		if ( false === $value ) {
			return $default;
		}
		
		return $value;
	}
	
	public static function update_option( $option, $value = '' ) {
    	$id_shop = (int) self::$id_shop;
		
		return self::update_post_meta( $id_shop, $option, $value );
	}

	public static function delete_option( $option ) {
    	$id_shop = (int) self::$id_shop;
		
		return self::delete_post_meta( $id_shop, $option );
	}
	
	public static function current_time() {
		$now = new \DateTime( '', new \DateTimeZone( \Configuration::get( 'PS_TIMEZONE' ) ) );
		return $now->getTimestamp();
	}
	
	public static function human_time_diff( $from, $to = 0 ) {
		if ( empty( $to ) ) {
			$to = time();
		}

		$diff = (int) abs( $to - $from );

		if ( $diff < AXON_MINUTE_IN_SECONDS ) {
			$secs = $diff;
			if ( $secs <= 1 ) {
				$secs = 1;
			}
			/* translators: Time difference between two dates, in seconds. %s: Number of seconds. */
			$since = sprintf( self::_n( '%s second', '%s seconds', $secs ), $secs );
		} elseif ( $diff < AXON_HOUR_IN_SECONDS && $diff >= AXON_MINUTE_IN_SECONDS ) {
			$mins = round( $diff / AXON_MINUTE_IN_SECONDS );
			if ( $mins <= 1 ) {
				$mins = 1;
			}
			/* translators: Time difference between two dates, in minutes (min=minute). %s: Number of minutes. */
			$since = sprintf( self::_n( '%s min', '%s mins', $mins ), $mins );
		} elseif ( $diff < AXON_DAY_IN_SECONDS && $diff >= AXON_HOUR_IN_SECONDS ) {
			$hours = round( $diff / AXON_HOUR_IN_SECONDS );
			if ( $hours <= 1 ) {
				$hours = 1;
			}
			/* translators: Time difference between two dates, in hours. %s: Number of hours. */
			$since = sprintf( self::_n( '%s hour', '%s hours', $hours ), $hours );
		} elseif ( $diff < AXON_WEEK_IN_SECONDS && $diff >= AXON_DAY_IN_SECONDS ) {
			$days = round( $diff / AXON_DAY_IN_SECONDS );
			if ( $days <= 1 ) {
				$days = 1;
			}
			/* translators: Time difference between two dates, in days. %s: Number of days. */
			$since = sprintf( self::_n( '%s day', '%s days', $days ), $days );
		} elseif ( $diff < AXON_MONTH_IN_SECONDS && $diff >= AXON_WEEK_IN_SECONDS ) {
			$weeks = round( $diff / AXON_WEEK_IN_SECONDS );
			if ( $weeks <= 1 ) {
				$weeks = 1;
			}
			/* translators: Time difference between two dates, in weeks. %s: Number of weeks. */
			$since = sprintf( self::_n( '%s week', '%s weeks', $weeks ), $weeks );
		} elseif ( $diff < AXON_YEAR_IN_SECONDS && $diff >= AXON_MONTH_IN_SECONDS ) {
			$months = round( $diff / AXON_MONTH_IN_SECONDS );
			if ( $months <= 1 ) {
				$months = 1;
			}
			/* translators: Time difference between two dates, in months. %s: Number of months. */
			$since = sprintf( self::_n( '%s month', '%s months', $months ), $months );
		} elseif ( $diff >= AXON_YEAR_IN_SECONDS ) {
			$years = round( $diff / AXON_YEAR_IN_SECONDS );
			if ( $years <= 1 ) {
				$years = 1;
			}
			/* translators: Time difference between two dates, in years. %s: Number of years. */
			$since = sprintf( self::_n( '%s year', '%s years', $years ), $years );
		}

		return self::apply_filters( 'human_time_diff', $since, $diff, $from, $to );
	}
	
	public static function wp_is_mobile() {
		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$is_mobile = false;
		} elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Mobile' ) !== false // Many mobile devices (all iPhone, iPad, etc.)
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Android' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Silk/' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Kindle' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'BlackBerry' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mini' ) !== false
			|| strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mobi' ) !== false ) {
				$is_mobile = true;
		} else {
			$is_mobile = false;
		}

		return self::apply_filters( 'wp_is_mobile', $is_mobile );
	}
	
	public static function set_url_scheme( $url, $scheme = null ) {
		$orig_scheme = $scheme;

		if ( ! $scheme ) {
			$scheme = self::is_ssl() ? 'https' : 'http';
		} elseif ( 'admin' === $scheme || 'login' === $scheme || 'login_post' === $scheme || 'rpc' === $scheme ) {
			$scheme = self::is_ssl() || self::force_ssl_admin() ? 'https' : 'http';
		} elseif ( 'http' !== $scheme && 'https' !== $scheme && 'relative' !== $scheme ) {
			$scheme = self::is_ssl() ? 'https' : 'http';
		}

		$url = trim( $url );
		if ( substr( $url, 0, 2 ) === '//' ) {
			$url = 'http:' . $url;
		}

		if ( 'relative' === $scheme ) {
			$url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );
			if ( '' !== $url && '/' === $url[0] ) {
				$url = '/' . ltrim( $url, "/ \t\n\r\0\x0B" );
			}
		} else {
			$url = preg_replace( '#^\w+://#', $scheme . '://', $url );
		}

		return self::apply_filters( 'set_url_scheme', $url, $scheme, $orig_scheme );
	}
	
	public static function is_ssl() {
		if ( isset( $_SERVER['HTTPS'] ) ) {
			if ( 'on' === strtolower( $_SERVER['HTTPS'] ) ) {
				return true;
			}

			if ( '1' == $_SERVER['HTTPS'] ) {
				return true;
			}
		} elseif ( isset( $_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
			return true;
		}
		return false;
	}
	
	public static function force_ssl_admin( $force = null ) {
		static $forced = false;

		if ( ! is_null( $force ) ) {
			$old_forced = $forced;
			$forced     = $force;
			return $old_forced;
		}

		return $forced;
	}
	
	public static function wp_doing_ajax() {
		return self::apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

    public static function getShopDomainSsl($http = false, $entities = false)
    {
        if (!$domain = \ShopUrl::getMainShopDomainSSL((int) Context::getContext()->shop->id)) {
            $domain = Tools::getHttpHost();
        }
        if ($entities) {
            $domain = htmlspecialchars($domain, ENT_COMPAT, 'UTF-8');
        }
        if ($http) {
            $domain = Tools::getProtocol((bool) Configuration::get('PS_SSL_ENABLED')) . $domain;
        }

        return $domain;
    }
				
	public static function set_global_var() {
		$result = true;
		
		$context = Context::getContext();
        $id_post = self::$id_post = (int)Tools::getValue( 'id_post' );
        $id_lang = self::$id_lang = (int)Tools::getValue( 'id_lang', $context->language->id );
        $id_shop = self::$id_shop = (int)Tools::getValue( 'id_shop', $context->shop->id );
        $post_type = self::$post_type = Tools::strtolower( Tools::getValue('post_type') );
		$key_related = self::$key_related = Tools::getValue( 'key_related' );
				
        switch ( $post_type ) {
            case 'hook':
				$related = self::getRelatedByIdPost();
				$key_related = self::$key_related = $related['key_related'];
                break;
            case 'category':
            case 'product':
            case 'cms':
			case 'manufacturer':
			case 'supplier':
            case 'blog':
				$related = self::getRelatedByKey();
				if( !$related ){
					$post = new \AxonCreatorPost();
					$post->title = $post_type . '-' . $key_related;
					$post->post_type = $post_type;
					$post->active = 1;
					$post->id_employee = isset( $context->employee->id ) && $context->employee->id ? $context->employee->id : Tools::getValue( 'id_employee' );
					$post->content[$id_lang] = '[]';
					$result &= $post->save();
					if( !$result ){ return false; }
					$id_post = self::$id_post = (int)$post->id;
					
					$related = new \AxonCreatorRelated();
					$related->id_post = $id_post;
					$related->post_type = $post_type;
					$related->key_related = $key_related;
					$result &= $related->save();
					if( !$result ){ return false; }
				}else{
					$id_post = self::$id_post = (int)$related['id_post'];
				}
                break;
        }
		
		$post = new \AxonCreatorPost( $id_post, $id_lang );
				
		self::$post_title = $post->title;
		self::$post_status = $post->active ? 'publish' : 'private';
		
		return $result;
	}
	
	public static function reset_post_var() {
		if( !self::$post_var ){
			$context = Context::getContext();
			self::$id_lang = (int)Tools::getValue( 'id_lang', $context->language->id );
			self::$id_shop = (int)Tools::getValue( 'id_shop', $context->shop->id );
				
			$params = $_GET;
			
			if (!empty($context->controller->php_self)) {
				$controller = $context->controller->php_self;
			} else {
				$controller = \Dispatcher::getInstance()->getController();
			}
						
			self::$post_type = Tools::strtolower( $controller );
						
			switch ( self::$post_type ) {
				case 'category':
					if( isset($params['id_category']) ){
						$id_page = (int) $params['id_category'];
					}elseif( Tools::getValue('id_category') ){
                        $id_page = (int) Tools::getValue('id_category');
                    }
					break;
				case 'product':
					if( isset($params['id_product']) ){
						$id_page = (int) $params['id_product'];
					}elseif( Tools::getValue('id_product') ){
                        $id_page = (int) Tools::getValue('id_product');
                    }
					break;
				case 'cms':
					if( isset($params['id_cms']) ){
						$id_page = (int) $params['id_cms'];
					}elseif( Tools::getValue('id_cms') ){
                        $id_page = (int) Tools::getValue('id_cms');
                    }
					break;
				case 'manufacturer':
					if( isset($params['id_manufacturer']) ){
						$id_page = (int) $params['id_manufacturer'];
					}elseif( Tools::getValue('id_manufacturer') ){
                        $id_page = (int) Tools::getValue('id_manufacturer');
                    }
					break;
				case 'supplier':
					if( isset($params['id_supplier']) ){
						$id_page = (int) $params['id_supplier'];
					}elseif( Tools::getValue('id_supplier') ){
                        $id_page = (int) Tools::getValue('id_supplier');
                    }
					break;
				case 'details':
					self::$post_type = 'blog';
					if( isset($params['id_blog']) ){
						$id_page = (int) $params['id_blog'];
					}elseif( Tools::getValue('id_blog') ){
                        $id_page = (int) Tools::getValue('id_blog');
                    }elseif( isset($context->smarty->tpl_vars['post']->value['id_post']) ){
                        $id_page = (int) $context->smarty->tpl_vars['post']->value['id_post'];
                    }
					break;
			}

			if ( isset( $id_page ) && $id_page ) {
				self::$key_related = $id_page;

				$related = self::getRelatedByKey();

				if( $related ){
					self::$id_post = (int)$related['id_post'];

					if( self::is_preview_mode() && Tools::getValue( 'post_type' ) == self::$post_type && Tools::getValue( 'key_related' ) == self::$key_related ) {
						self::$id_editor = self::$id_post;
					}
				}
			}
			
			self::$post_var['id_post'] = self::$id_post;
			self::$post_var['id_editor'] = self::$id_editor;
			self::$post_var['id_lang'] = self::$id_lang;
			self::$post_var['id_shop'] = self::$id_shop;
			self::$post_var['post_type'] = self::$post_type;
			self::$post_var['key_related'] = self::$key_related;
		}else{
			self::$id_post = self::$post_var['id_post'];
			self::$id_editor = self::$post_var['id_editor'];
			self::$id_lang = self::$post_var['id_lang'];
			self::$id_shop = self::$post_var['id_shop'];
			self::$post_type = self::$post_var['post_type'];
			self::$key_related = self::$post_var['key_related'];
		}
    }
	
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
