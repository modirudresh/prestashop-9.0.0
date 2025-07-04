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

namespace AxonCreator\Core\Base;

use AxonCreator\Core\Base\Module as BaseModule;
use AxonCreator\Plugin;
use AxonCreator\Wp_Helper; 

if ( ! defined( '_PS_VERSION_' ) ) {
	exit; // Exit if accessed directly
}

abstract class Background_Task_Manager extends BaseModule {
	/**
	 * @var Background_Task
	 */
	protected $task_runner;

	abstract public function get_action();
	abstract public function get_plugin_name();
	abstract public function get_plugin_label();
	abstract public function get_task_runner_class();
	abstract public function get_query_limit();

	abstract protected function start_run();

	public function on_runner_start() {
		$logger = Plugin::$instance->logger->get_logger();
		$logger->info( $this->get_plugin_name() . '::' . $this->get_action() . ' Started' );
	}

	public function on_runner_complete( $did_tasks = false ) {
		$logger = Plugin::$instance->logger->get_logger();
		$logger->info( $this->get_plugin_name() . '::' . $this->get_action() . ' Completed' );
	}

	public function get_task_runner() {
		if ( empty( $this->task_runner ) ) {
			$class_name = $this->get_task_runner_class();
			$this->task_runner = new $class_name( $this );
		}

		return $this->task_runner;
	}

	// TODO: Replace with a db settings system.
	protected function add_flag( $flag ) {
		return Wp_Helper::add_option( $this->get_plugin_name() . '_' . $this->get_action() . '_' . $flag, 1 );
	}

	protected function get_flag( $flag ) {
		return Wp_Helper::get_option( $this->get_plugin_name() . '_' . $this->get_action() . '_' . $flag );
	}

	protected function delete_flag( $flag ) {
		return Wp_Helper::delete_option( $this->get_plugin_name() . '_' . $this->get_action() . '_' . $flag );
	}

	protected function get_start_action_url() {
		return wp_nonce_url( add_query_arg( $this->get_action(), 'run' ), $this->get_action() . 'run' );
	}

	protected function get_continue_action_url() {
		return wp_nonce_url( add_query_arg( $this->get_action(), 'continue' ), $this->get_action() . 'continue' );
	}

	private function continue_run() {
		$runner = $this->get_task_runner();
		$runner->continue_run();
	}

	public function __construct() {
		if ( empty( $_GET[ $this->get_action() ] ) ) {
			return;
		}

		if ( 'run' === $_GET[ $this->get_action() ] && check_admin_referer( $this->get_action() . 'run' ) ) {
			$this->start_run();
		}

		if ( 'continue' === $_GET[ $this->get_action() ] && check_admin_referer( $this->get_action() . 'continue' ) ) {
			$this->continue_run();
		}

		wp_safe_redirect( remove_query_arg( [ $this->get_action(), '_wpnonce' ] ) );
		die;
	}
}