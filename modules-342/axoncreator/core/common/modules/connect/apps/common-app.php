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

namespace AxonCreator\Core\Common\Modules\Connect\Apps;

use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

abstract class Common_App extends Base_User_App {

	protected static $common_data = null;

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function get_option_name() {
		return static::OPTION_NAME_PREFIX . 'common_data';
	}

	/**
	 * @since 2.3.0
	 * @access protected
	 */
	protected function init_data() {
		if ( is_null( self::$common_data ) ) {
			self::$common_data = get_user_meta( get_current_user_id(), static::get_option_name(), true );

			if ( ! self::$common_data ) {
				self::$common_data = [];
			};
		}

		$this->data = & self::$common_data;
	}
}