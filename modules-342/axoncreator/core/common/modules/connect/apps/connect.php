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

class Connect extends Common_App {

	/**
	 * @since 2.3.0
	 * @access protected
	 */
	protected function get_slug() {
		return 'connect';
	}

	/**
	 * @since 2.3.0
	 * @access public
	 */
	public function render_admin_widget() {
		if ( $this->is_connected() ) {
			$remote_user = $this->get( 'user' );
			$title = sprintf( Wp_Helper::__( 'Connected to Elementor as %s', 'elementor' ), '<strong>' . $remote_user->email . '</strong>' ) . get_avatar( $remote_user->email, 20, '' );
			$label = Wp_Helper::__( 'Disconnect', 'elementor' );
			$url = $this->get_admin_url( 'disconnect' );
			$attr = '';
		} else {
			$title = Wp_Helper::__( 'Connect to Elementor', 'elementor' );
			$label = Wp_Helper::__( 'Connect', 'elementor' );
			$url = $this->get_admin_url( 'authorize' );
			$attr = 'class="elementor-connect-popup"';
		}

		echo '<h1>' . Wp_Helper::__( 'Connect', 'elementor' ) . '</h1>';

		echo sprintf( '%s <a %s href="%s">%s</a>', $title, $attr, Wp_Helper::esc_attr( $url ), Wp_Helper::esc_html( $label ) );
	}
}