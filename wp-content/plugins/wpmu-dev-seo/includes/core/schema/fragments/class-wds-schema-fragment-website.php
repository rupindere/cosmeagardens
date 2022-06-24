<?php

class Smartcrawl_Schema_Fragment_Website extends Smartcrawl_Schema_Fragment {
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;

	public function __construct() {
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$website_name = $this->utils->get_social_option( 'sitename' );
		$website_name = ! empty( $website_name )
			? $website_name
			: get_bloginfo( 'name' );
		$website_url = get_site_url();

		$schema = array(
			"@type"    => "WebSite",
			"@id"      => $this->utils->get_website_id(),
			"url"      => $website_url,
			"name"     => $this->utils->apply_filters( 'site-data-name', $website_name ),
			"encoding" => get_bloginfo( 'charset' ),
		);

		if ( (bool) $this->utils->get_schema_option( 'sitelinks_search_box' ) ) {
			$search_url = str_replace(
				'search_term_string',
				'{search_term_string}',
				get_search_link( 'search_term_string' )
			);
			$schema["potentialAction"] = array(
				"@type"       => "SearchAction",
				"target"      => $search_url,
				"query-input" => "required name=search_term_string",
			);
		}

		$image = $this->utils->get_media_item_image_schema(
			(int) $this->utils->get_schema_option( 'schema_website_logo' ),
			$this->utils->url_to_id( $website_url, '#schema-site-logo' )
		);
		if ( $image ) {
			$schema["image"] = $image;
		}

		return $this->utils->apply_filters( 'site-data', $schema );
	}
}