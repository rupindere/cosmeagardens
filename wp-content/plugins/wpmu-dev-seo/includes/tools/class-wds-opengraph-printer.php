<?php

/**
 * Outputs OG tags to the page
 */
class Smartcrawl_OpenGraph_Printer {

	/**
	 * Singleton instance holder
	 */
	private static $_instance;

	private $_is_running = false;
	private $_is_done = false;

	public function __construct() {
	}

	/**
	 * Boot the hooking part
	 */
	public static function run() {
		self::get()->_add_hooks();
	}

	private function _add_hooks() {
		// Do not double-bind
		if ( apply_filters( 'wds-opengraph-is_running', $this->_is_running ) ) {
			return true;
		}

		add_action( 'wp_head', array( $this, 'dispatch_og_tags_injection' ), 50 );
		add_action( 'wds_head-after_output', array( $this, 'dispatch_og_tags_injection' ) );

		$this->_is_running = true;
	}

	/**
	 * Singleton instance getter
	 *
	 * @return Smartcrawl_OpenGraph_Printer instance
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * First-line dispatching of OG tags injection
	 */
	public function dispatch_og_tags_injection() {
		if ( ! ! $this->_is_done ) {
			return false;
		}

		$settings = Smartcrawl_Settings::get_component_options( Smartcrawl_Settings::COMP_SOCIAL );
		if ( empty( $settings['og-enable'] ) ) {
			return false;
		}
		$this->inject_global_tags();

		$this->_is_done = true;

		return $this->inject_og_tags();
	}

	/**
	 * Injects globally valid tags - regardless of context
	 */
	public function inject_global_tags() {
		$settings = Smartcrawl_Settings::get_component_options( Smartcrawl_Settings::COMP_SOCIAL );
		if ( ! empty( $settings['fb-app-id'] ) ) {
			$this->print_og_tag( 'fb:app_id', $settings['fb-app-id'] );
		}
	}

	/**
	 * Actually prints the OG tag
	 *
	 * @param string $tag Tagname or tagname-like string to print
	 * @param mixed $value Tag value as string, or list of string tag values
	 *
	 * @return bool
	 */
	public function print_og_tag( $tag, $value ) {
		if ( empty( $tag ) || empty( $value ) ) {
			return false;
		}

		$og_tag = $this->get_og_tag( $tag, $value );
		if ( empty( $og_tag ) ) {
			return false;
		}

		echo wp_kses( $og_tag, $this->get_allowed_tags() );

		return true;
	}

	/**
	 * Gets the markup for an OG tag
	 *
	 * @param string $tag Tagname or tagname-like string to print
	 * @param mixed $value Tag value as string, or list of string tag values
	 *
	 * @return string
	 */
	public function get_og_tag( $tag, $value ) {
		if ( empty( $tag ) || empty( $value ) ) {
			return false;
		}

		return '<meta property="' . esc_attr( $tag ) . '" content="' . esc_attr( $value ) . '" />' . "\n";
	}

	/**
	 * Attempt to use post-specific meta setup to resolve tag values
	 *
	 * Fallback to generic, global values
	 *
	 * @return bool
	 */
	public function inject_og_tags() {
		$queried = Smartcrawl_Endpoint_Resolver::resolve()->get_queried_entity();
		if ( ! $queried || ! $queried->is_opengraph_enabled() ) {
			return false;
		}

		$this->print_og_tags( $queried->get_opengraph_tags() );

		return true;
	}

	public function print_og_tags( $opengraph_tags ) {
		if ( empty( $opengraph_tags ) ) {
			return;
		}

		foreach ( $opengraph_tags as $opengraph_tag => $opengraph_value ) {
			if ( $opengraph_value && is_scalar( $opengraph_value ) ) {
				$this->print_og_tag( $opengraph_tag, $opengraph_value );
			} elseif ( is_array( $opengraph_value ) ) {
				$this->print_og_tags( $opengraph_value );
			}
		}
	}

	/**
	 * @return array
	 */
	private function get_allowed_tags() {
		$allowed_tags = array(
			'meta' => array(
				'property' => array(),
				'content'  => array(),
			),
		);

		return $allowed_tags;
	}
}