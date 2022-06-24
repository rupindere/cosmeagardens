<?php

class Smartcrawl_Controller_Ajax_Search extends Smartcrawl_Base_Controller {
	private static $_instance;

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	protected function init() {
		add_action( 'wp_ajax_wds-search-post', array( $this, 'search_post' ) );
		add_action( 'wp_ajax_wds-search-term', array( $this, 'search_taxonomy_term' ) );
	}

	public function search_post() {
		$search_query = smartcrawl_get_array_value( $_GET, 'term' );
		$post_type = smartcrawl_get_array_value( $_GET, 'type' );
		$request_type = smartcrawl_get_array_value( $_GET, 'request_type' );
		$post_id = smartcrawl_get_array_value( $_GET, 'id' );
		$results = array();
		if ( empty( $search_query ) && empty( $post_id ) ) {
			wp_send_json( array( 'results' => $results ) );
			return;
		}

		$args = array(
			'post_status'         => $post_type === 'attachment' ? 'inherit' : 'publish',
			'posts_per_page'      => 10,
			'ignore_sticky_posts' => true,
			'post_type'           => $post_type,
			's'                   => $search_query,
		);
		if ( $request_type === 'text' && $post_id ) {
			$args['post__in'] = is_array( $post_id ) ? $post_id : array( $post_id );
		}
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			$results[] = array(
				'id'   => $post->ID,
				'text' => $post->post_title,
			);
		}
		wp_send_json( array( 'results' => $results ) );
	}

	public function search_taxonomy_term() {
		$search_query = smartcrawl_get_array_value( $_GET, 'term' );
		$taxonomy = smartcrawl_get_array_value( $_GET, 'type' );
		$request_type = smartcrawl_get_array_value( $_GET, 'request_type' );
		$term_id = smartcrawl_get_array_value( $_GET, 'id' );
		$results = array();
		if ( empty( $search_query ) && empty( $term_id ) ) {
			wp_send_json( array( 'results' => $results ) );
			return;
		}

		/**
		 * @var $terms WP_Term
		 */
		$args = array(
			'hide_empty' => false,
			'taxonomy'   => $taxonomy,
			'orderby'    => 'name',
			'order'      => 'ASC',
		);
		if ( $request_type === 'text' && $term_id ) {
			$args['include'] = is_array( $term_id ) ? $term_id : array( $term_id );
			$args['number'] = is_array( $term_id ) ? count( $term_id ) : 1;
		} else {
			$args['search'] = $search_query;
			$args['number'] = 10;
		}
		$terms = get_terms( $args );
		foreach ( $terms as $term ) {
			$results[] = array(
				'id'   => $term->term_id,
				'text' => $term->name,
			);
		}
		wp_send_json( array( 'results' => $results ) );
	}
}