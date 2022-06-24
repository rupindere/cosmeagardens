<?php

class Smartcrawl_Schema_Fragment_Tax_Archive extends Smartcrawl_Schema_Fragment {
	private $term;
	private $posts;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	private $title;
	private $description;

	public function __construct( $term, $posts, $title, $description ) {
		$this->term = $term;
		$this->posts = $posts;
		$this->title = $title;
		$this->description = $description;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$enabled = (bool) $this->utils->get_schema_option( 'schema_enable_taxonomy_archives' );
		$disabled = (bool) $this->utils->get_schema_option( array(
			'schema_disabled_taxonomy_archives',
			$this->term->taxonomy,
		) );
		$term_url = get_term_link( $this->term, $this->term->taxonomy );

		if ( $enabled && ! $disabled ) {
			return new Smartcrawl_Schema_Fragment_Archive(
				"CollectionPage",
				$term_url,
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
					$this->utils->get_webpage_id( $term_url )
				);
			} else {
				return array();
			}
		}
	}
}