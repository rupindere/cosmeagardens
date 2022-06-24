<?php

class Smartcrawl_Schema_Fragment_Menu extends Smartcrawl_Schema_Fragment {
	private $url;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;

	public function __construct( $url ) {
		$this->url = $url;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$main_menu_slug = $this->utils->get_schema_option( 'schema_main_navigation_menu' );
		if ( empty( $main_menu_slug ) ) {
			return false;
		}

		$menu_items = wp_get_nav_menu_items( $main_menu_slug );
		if ( empty( $menu_items ) || ! is_array( $menu_items ) ) {
			return false;
		}

		$schema = array();
		foreach ( $menu_items as $menu_item ) {
			/**
			 * @var $menu_item WP_Post
			 */
			$schema[] = array(
				"@type" => "SiteNavigationElement",
				"@id"   => $this->utils->url_to_id( $this->url, '#schema-nav-element-' . $menu_item->ID ),
				"name"  => $menu_item->post_title,
				"url"   => $menu_item->url,
			);
		}

		return $schema;
	}
}