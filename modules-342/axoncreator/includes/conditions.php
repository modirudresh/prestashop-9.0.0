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

use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor conditions.
 *
 * Elementor conditions handler class introduce the compare conditions and the
 * check conditions methods.
 *
 * @since 1.0.0
 */
class Conditions {

	/**
	 * Compare conditions.
	 *
	 * Whether the two values comply the comparison operator.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param mixed  $left_value  First value to compare.
	 * @param mixed  $right_value Second value to compare.
	 * @param string $operator    Comparison operator.
	 *
	 * @return bool Whether the two values complies the comparison operator.
	 */
	public static function compare( $left_value, $right_value, $operator ) {
		switch ( $operator ) {
			case '==':
				return $left_value == $right_value;
			case '!=':
				return $left_value != $right_value;
			case '!==':
				return $left_value !== $right_value;
			case 'in':
				return false !== array_search( $left_value, $right_value );
			case '!in':
				return false === array_search( $left_value, $right_value );
			case '<':
				return $left_value < $right_value;
			case '<=':
				return $left_value <= $right_value;
			case '>':
				return $left_value > $right_value;
			case '>=':
				return $left_value >= $right_value;
			default:
				return $left_value === $right_value;
		}
	}

	/**
	 * Check conditions.
	 *
	 * Whether the comparison conditions comply.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param array $conditions The conditions to check.
	 * @param array $comparison The comparison parameter.
	 *
	 * @return bool Whether the comparison conditions comply.
	 */
	public static function check( array $conditions, array $comparison ) {
		$is_or_condition = isset( $conditions['relation'] ) && 'or' === $conditions['relation'];

		$condition_succeed = ! $is_or_condition;

		foreach ( $conditions['terms'] as $term ) {
			if ( ! empty( $term['terms'] ) ) {
				$comparison_result = self::check( $term, $comparison );
			} else {
				preg_match( '/(\w+)(?:\[(\w+)])?/', $term['name'], $parsed_name );

				$value = $comparison[ $parsed_name[1] ];

				if ( ! empty( $parsed_name[2] ) ) {
					$value = $value[ $parsed_name[2] ];
				}

				$operator = null;

				if ( ! empty( $term['operator'] ) ) {
					$operator = $term['operator'];
				}

				$comparison_result = self::compare( $value, $term['value'], $operator );
			}

			if ( $is_or_condition ) {
				if ( $comparison_result ) {
					return true;
				}
			} elseif ( ! $comparison_result ) {
				return false;
			}
		}

		return $condition_succeed;
	}
}