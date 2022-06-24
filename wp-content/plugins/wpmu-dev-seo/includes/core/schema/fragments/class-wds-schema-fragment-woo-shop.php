<?php

class Smartcrawl_Schema_Fragment_Woo_Shop extends Smartcrawl_Schema_Fragment {
	private $url;
	private $posts;
	private $title;
	private $description;
	/**
	 * @var Smartcrawl_Woocommerce_Data
	 */
	private $data;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;

	public function __construct( $url, $posts, $title, $description ) {
		$this->url = $url;
		$this->posts = $posts;
		$this->title = $title;
		$this->description = $description;
		$this->data = new Smartcrawl_Woocommerce_Data();
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	private function get_options() {
		return $this->data->get_options();
	}

	protected function get_raw() {
		$woo_enabled = (bool) smartcrawl_get_array_value( $this->get_options(), 'woocommerce_enabled' );
		$enable_shop_page_schema = (bool) smartcrawl_get_array_value( $this->get_options(), 'enable_shop_page_schema' );

		if ( $woo_enabled && $enable_shop_page_schema ) {
			return new Smartcrawl_Schema_Fragment_Archive(
				"CollectionPage",
				$this->url,
				$this->posts,
				$this->title,
				$this->description
			);
		} else {
			$custom_schema_types = $this->utils->get_custom_schema_types();
			if ( $custom_schema_types ) {
				return $this->utils->add_custom_schema_types(
					array(),
					$custom_schema_types,
					$this->utils->get_webpage_id( $this->url )
				);
			} else {
				return array();
			}
		}
	}
}