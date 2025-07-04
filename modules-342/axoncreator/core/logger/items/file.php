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

class File extends Base {

	const FORMAT = 'date [type X times][file:line] message [meta]';

	protected $file;
	protected $line;

	public function __construct( $args ) {
		parent::__construct( $args );

		$this->file = empty( $args['file'] ) ? '' : $args['file'];
		$this->line = empty( $args['line'] ) ? '' : $args['line'];
	}

	public function jsonSerialize() {
		$json_arr = parent::jsonSerialize();
		$json_arr['file'] = $this->file;
		$json_arr['line'] = $this->line;
		return $json_arr;
	}

	public function deserialize( $properties ) {
		parent::deserialize( $properties );
		$this->file = ! empty( $properties['file'] ) && is_string( $properties['file'] ) ? $properties['file'] : '';
		$this->line = ! empty( $properties['line'] ) && is_string( $properties['line'] ) ? $properties['line'] : '';
	}

	public function get_name() {
		return 'File';
	}
}
