<?php

class Smartcrawl_Post_Cache {
	private $cache = array();

	private static $_instance;

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function get_post( $post_id ) {
		if ( ! is_numeric( $post_id ) ) {
			return null;
		}

		if ( empty( $this->cache[ $post_id ] ) ) {
			$post = new Smartcrawl_Post( $post_id );
			if ( ! $post->get_wp_post() ) {
				return null;
			}
			$this->cache[ $post_id ] = $post;
		}

		return $this->cache[ $post_id ];
	}

	public function purge( $post_id ) {
		unset( $this->cache[ $post_id ] );
	}

	public function purge_all() {
		$this->cache = array();
	}
}