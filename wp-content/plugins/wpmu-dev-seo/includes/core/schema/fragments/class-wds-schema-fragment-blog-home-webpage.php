<?php

class Smartcrawl_Schema_Fragment_Blog_Home_Webpage extends Smartcrawl_Schema_Fragment {
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	private $publisher_id;
	private $title;
	private $description;

	public function __construct( $title, $description, $publisher_id ) {
		$this->title = $title;
		$this->description = $description;
		$this->publisher_id = $publisher_id;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$site_url = get_site_url();
		$schema = array(
			"@type"      => "WebPage",
			"@id"        => $this->utils->get_webpage_id( $site_url ),
			"url"        => $site_url,
			"name"       => $this->title,
			"inLanguage" => get_bloginfo( 'language' ),
			"isPartOf"   => array(
				"@id" => $this->utils->get_website_id(),
			),
			"publisher"  => array(
				"@id" => $this->publisher_id,
			),
		);

		if ( $this->description ) {
			$schema["description"] = $this->utils->apply_filters( 'site-data-description', $this->description );
		}

		$last_post_date = get_lastpostdate( 'blog' );
		if ( $last_post_date ) {
			$schema["dateModified"] = $last_post_date;
		}

		$schema["hasPart"] = new Smartcrawl_Schema_Fragment_Menu( $site_url );

		return $schema;
	}
}