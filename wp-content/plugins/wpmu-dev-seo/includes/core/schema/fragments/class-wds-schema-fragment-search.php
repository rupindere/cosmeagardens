<?php

class Smartcrawl_Schema_Fragment_Search extends Smartcrawl_Schema_Fragment {
	private $search_term;
	/**
	 * @var WP_Post[]
	 */
	private $posts;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	private $title;
	private $description;

	public function __construct( $search_term, $posts, $title, $description ) {
		$this->search_term = $search_term;
		$this->posts = $posts;
		$this->title = $title;
		$this->description = $description;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$enabled = (bool) $this->utils->get_schema_option( 'schema_enable_search' );
		$search_url = get_search_link( $this->search_term );

		if ( $enabled ) {
			return new Smartcrawl_Schema_Fragment_Archive(
				"SearchResultsPage",
				$search_url,
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
					$this->utils->get_webpage_id( $search_url )
				);
			} else {
				return array();
			}
		}
	}
}