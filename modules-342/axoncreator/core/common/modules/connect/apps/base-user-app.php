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

abstract class Base_User_App extends Base_App {

	/**
	 * @since 2.3.0
	 * @access protected
	 */
	protected function update_settings() {
		update_user_meta( get_current_user_id(), $this->get_option_name(), $this->data );
	}

	/**
	 * @since 2.3.0
	 * @access protected
	 */
	protected function init_data() {
		$this->data = get_user_meta( get_current_user_id(), $this->get_option_name(), true );

		if ( ! $this->data ) {
			$this->data = [];
		}
	}
}
