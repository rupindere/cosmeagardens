<?php

class Smartcrawl_Schema_Fragment_Header extends Smartcrawl_Schema_Fragment {
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	private $url;
	private $title;
	private $description;

	public function __construct( $url, $title, $description ) {
		$this->url = $url;
		$this->title = $title;
		$this->description = $description;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$enable_header_footer = (bool) $this->utils->get_schema_option( 'schema_wp_header_footer' );
		if ( ! $enable_header_footer ) {
			return false;
		}

		return array(
			"@type"       => "WPHeader",
			"url"         => $this->url,
			"headline"    => $this->title,
			"description" => $this->description,
		);
	}
}