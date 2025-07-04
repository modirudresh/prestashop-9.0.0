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

namespace AxonCreator\Core\Logger\Loggers;

use AxonCreator\Core\Logger\Items\Log_Item_Interface as Log_Item;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

class Db extends Base {

	public function save_log( Log_Item $item ) {
		$log = $this->maybe_truncate_log();

		$id = $item->get_fingerprint();

		if ( empty( $log[ $id ] ) ) {
			$log[ $id ] = $item;
		}

		$log[ $id ]->increase_times( $item );

		Wp_Helper::update_option( self::LOG_NAME, $log, 'no' );
	}

	private function maybe_truncate_log() {
		/** @var Log_Item[] $log */
		$log = $this->get_log();

		if ( Log_Item::MAX_LOG_ENTRIES < count( $log ) ) {
			$log = array_slice( $log, -Log_Item::MAX_LOG_ENTRIES );
		}

		return $log;
	}

	protected function get_log() {
		// Clear cache.
		wp_cache_delete( self::LOG_NAME, 'options' );

		return Wp_Helper::get_option( self::LOG_NAME, [] );
	}
}
