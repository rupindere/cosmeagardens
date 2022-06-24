<?php

class Smartcrawl_Schema_Fragment_Author_Archive extends Smartcrawl_Schema_Fragment {
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	/**
	 * @var WP_User
	 */
	private $author;
	private $posts;
	private $title;
	private $description;

	public function __construct( $author, $posts, $title, $description ) {
		$this->author = $author;
		$this->posts = $posts;
		$this->title = $title;
		$this->description = $description;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$author_url = get_author_posts_url( $this->author->ID );

		$enabled = (bool) $this->utils->get_schema_option( 'schema_enable_author_archives' );
		if ( $enabled ) {
			return new Smartcrawl_Schema_Fragment_Archive(
				"ProfilePage",
				$author_url,
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
					$this->utils->get_webpage_id( $author_url )
				);
			} else {
				return array();
			}
		}
	}
}