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

namespace AxonCreator\Core\Utils;

use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Elementor exceptions.
 *
 * Elementor exceptions handler class is responsible for handling exceptions.
 *
 * @since 2.0.0
 */
class Exceptions {

	/**
	 * HTTP status code for bad request error.
	 */
	const BAD_REQUEST = 400;

	/**
	 * HTTP status code for unauthorized access error.
	 */
	const UNAUTHORIZED = 401;

	/**
	 * HTTP status code for forbidden access error.
	 */
	const FORBIDDEN = 403;

	/**
	 * HTTP status code for resource that could not be found.
	 */
	const NOT_FOUND = 404;

	/**
	 * HTTP status code for internal server error.
	 */
	const INTERNAL_SERVER_ERROR = 500;
}
