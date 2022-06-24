<?php

class Smartcrawl_OpenGraph_Value_Helper extends Smartcrawl_Social_Value_Helper {
	/**
	 * Singleton instance
	 *
	 * @var self
	 */
	private static $_instance;

	/**
	 * @return self instance
	 */
	public static function get() {
		_deprecated_function( __FUNCTION__, '2.18.0' );

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		self::$_instance->traverse();

		return self::$_instance;
	}

	public function __construct() {
		_deprecated_constructor( __CLASS__, '2.18.0', 'Smartcrawl_Type_Traverser' );

		parent::__construct( 'og', 'opengraph', 'opengraph' );
	}
}