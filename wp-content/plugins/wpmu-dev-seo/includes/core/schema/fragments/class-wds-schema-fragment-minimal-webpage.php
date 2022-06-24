<?php

class Smartcrawl_Schema_Fragment_Minimal_Webpage extends Smartcrawl_Schema_Fragment {
	private $url;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	private $publisher_id;

	public function __construct( $url, $publisher_id ) {
		$this->url = $url;
		$this->publisher_id = $publisher_id;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		return array(
			"@type"     => "WebPage",
			"@id"       => $this->utils->get_webpage_id( $this->url ),
			"isPartOf"  => array(
				"@id" => $this->utils->get_website_id(),
			),
			"publisher" => array(
				"@id" => $this->publisher_id,
			),
			"url"       => $this->url,
			"hasPart"   => new Smartcrawl_Schema_Fragment_Menu( $this->url ),
		);
	}
}