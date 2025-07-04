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

namespace AxonCreator\Core\Logger\Items;

use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

class JS extends File {

	const FORMAT = 'JS: date [type X times][file:line:column] message [meta]';

	protected $column;

	public function __construct( $args ) {
		parent::__construct( $args );
		$this->column = $args['column'];
		$this->file = $args['url'];
		$this->date = date( 'Y-m-d H:i:s', $args['timestamp'] );
	}

	public function jsonSerialize() {
		$json_arr = parent::jsonSerialize();
		$json_arr['column'] = $this->column;
		return $json_arr;
	}

	public function deserialize( $properties ) {
		parent::deserialize( $properties );
		$this->column = ! empty( $properties['column'] ) && is_string( $properties['column'] ) ? $properties['column'] : '';
	}

	public function get_name() {
		return 'JS';
	}
}
