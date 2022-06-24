<?php

/**
 * TODO: woo shop is not just a page, product post type archive can also be a shop
 */
class Smartcrawl_Woo_Shop_Page extends Smartcrawl_Post {
	/**
	 * @var Smartcrawl_Woocommerce_Api
	 */
	private $woo_api;
	/**
	 * @var array
	 */
	private $posts;

	/**
	 * Smartcrawl_Woo_Shop constructor.
	 *
	 * @param array $posts
	 */
	public function __construct( $posts = array(), $woo_api = null ) {
		if ( ! $woo_api ) {
			$woo_api = new Smartcrawl_Woocommerce_Api();
		}

		parent::__construct( $woo_api->wc_get_page_id( 'shop' ) );

		$this->woo_api = $woo_api;
		$this->posts = $posts;
	}

	protected function load_schema() {
		$wp_posts = $this->get_wp_post();
		if ( ! $wp_posts ) {
			return array();
		}

		$archive = new Smartcrawl_Schema_Fragment_Woo_Shop(
			$this->woo_api->wc_get_page_permalink( 'shop' ),
			$this->posts,
			$this->get_meta_title(),
			$this->get_meta_description()
		);

		return $archive->get_schema();
	}
}