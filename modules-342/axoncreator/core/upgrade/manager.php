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

namespace AxonCreator\Core\Upgrade;

use AxonCreator\Core\Base\DB_Upgrades_Manager;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

class Manager extends DB_Upgrades_Manager {

	// todo: remove in future releases
	public function should_upgrade() {
		if ( ( 'elementor' === $this->get_plugin_name() ) && version_compare( Wp_Helper::get_option( $this->get_version_option_name() ), '2.4.2', '<' ) ) {
			delete_option( 'elementor_log' );
		}

		return parent::should_upgrade();
	}

	public function get_name() {
		return 'upgrade';
	}

	public function get_action() {
		return 'elementor_updater';
	}

	public function get_plugin_name() {
		return 'elementor';
	}

	public function get_plugin_label() {
		return Wp_Helper::__( 'Elementor', 'elementor' );
	}

	public function get_updater_label() {
		return sprintf( '<strong>%s </strong> &#8211;', Wp_Helper::__( 'Elementor Data Updater', 'elementor' ) );
	}

	public function get_new_version() {
		return AXON_CREATOR_VERSION;
	}

	public function get_version_option_name() {
		return 'elementor_version';
	}

	public function get_upgrades_class() {
		return 'Elementor\Core\Upgrade\Upgrades';
	}
}
