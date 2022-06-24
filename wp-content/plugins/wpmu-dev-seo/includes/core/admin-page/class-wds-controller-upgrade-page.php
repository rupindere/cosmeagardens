<?php

class Smartcrawl_Controller_Upgrade_Page extends Smartcrawl_Admin_Page {
	private static $_instance;

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function init() {
	}

	public function get_menu_slug() {
		return '';
	}
}