<?php

class Smartcrawl_Term_Cache {
	private $cache = array();

	private static $_instance;

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function get_term( $term_id ) {
		if ( empty( $this->cache[ $term_id ] ) ) {
			$term = new Smartcrawl_Taxonomy_Term( $term_id );
			if ( ! $term->get_wp_term() ) {
				return null;
			}
			$this->cache[ $term_id ] = $term;
		}

		return $this->cache[ $term_id ];
	}

	public function purge( $term_id ) {
		unset( $this->cache[ $term_id ] );
	}

	public function purge_all() {
		$this->cache = array();
	}
}