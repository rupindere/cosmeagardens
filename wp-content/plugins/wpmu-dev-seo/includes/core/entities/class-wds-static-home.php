<?php

/**
 * TODO: rename this and the schema fragment to be more clear
 */
class Smartcrawl_Static_Home extends Smartcrawl_Post {
	/**
	 * @var array
	 */
	private $posts;
	/**
	 * @var int
	 */
	private $page_number;

	public function __construct( $posts = array(), $page_number = 0 ) {
		parent::__construct( get_option( 'page_for_posts' ) );

		$this->posts = $posts;
		$this->page_number = $page_number;
	}

	protected function load_schema() {
		$schema = new Smartcrawl_Schema_Fragment_Static_Home(
			$this->posts,
			$this->get_meta_title(),
			$this->get_meta_description()
		);

		return $schema->get_schema();
	}

	protected function load_canonical_url() {
		return smartcrawl_append_archive_page_number(
			parent::load_canonical_url(),
			$this->page_number
		);
	}

	protected function load_opengraph_tags() {
		$tags = parent::load_opengraph_tags();

		$tags['og:type'] = 'website';

		return $tags;
	}
}