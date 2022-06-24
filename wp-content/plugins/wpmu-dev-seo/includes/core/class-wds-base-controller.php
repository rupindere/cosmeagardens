<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

abstract class Smartcrawl_Base_Controller {
	protected function __construct() {
	}

	/**
	 * Currently running state flag
	 *
	 * @var bool
	 */
	private $is_running = false;

	/**
	 * Do the thing!
	 */
	public function run() {
		if ( $this->is_running() ) {
			return false;
		}
		$this->is_running = true;

		// Some parts need to be run every time
		$this->always();

		if ( $this->should_run() ) {
			// while the rest are run when should_run returns true
			$this->init();
			return true;
		}

		return false;
	}

	protected function always() {

	}

	/**
	 * Child controllers can use this method to initialize.
	 *
	 * @return mixed
	 */
	abstract protected function init();

	public function stop() {
		if ( ! $this->is_running() ) {
			return false;
		}

		$this->is_running = false;

		return $this->terminate();
	}

	/**
	 * Opposite of init
	 *
	 * @return bool
	 */
	protected function terminate() {
		return true;
	}

	/**
	 * Check if we already have the actions bound
	 *
	 * @return bool Status
	 */
	public function is_running() {
		return $this->is_running;
	}

	/**
	 * Whether or not this controller should run in the current context.
	 * Default is true which means it will always run.
	 *
	 * @return bool
	 */
	public function should_run() {
		return true;
	}
}