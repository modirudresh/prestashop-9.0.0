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

use AxonCreator\Core\Base\Background_Task;

defined( '_PS_VERSION_' ) || exit;

class Updater extends Background_Task {

	protected function format_callback_log( $item ) {
		return $this->manager->get_plugin_label() . '/Upgrades - ' . $item['callback'][1];
	}
}
