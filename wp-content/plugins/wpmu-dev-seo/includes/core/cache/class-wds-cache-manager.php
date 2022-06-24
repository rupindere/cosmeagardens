<?php

class Smartcrawl_Cache_Manager extends Smartcrawl_Base_Controller {
	private $post_cache;

	private $term_cache;

	private static $_instance;

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		parent::__construct();

		$this->post_cache = Smartcrawl_Post_Cache::get();
		$this->term_cache = Smartcrawl_Term_Cache::get();
	}

	protected function init() {
		add_action( 'save_post', array( $this, 'invalidate_post' ) );
		add_action( 'delete_post', array( $this, 'invalidate_post' ) );

		add_action( 'edit_term', array( $this, 'invalidate_term' ) );
		add_action( 'deleted_term_taxonomy', array( $this, 'invalidate_term' ) );
	}

	public function invalidate_post( $post_id ) {
		$this->post_cache->purge( $post_id );
	}

	public function invalidate_term( $term_id ) {
		$this->term_cache->purge( $term_id );
	}
}