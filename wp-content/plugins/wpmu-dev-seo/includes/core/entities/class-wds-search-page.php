<?php

class Smartcrawl_Search_Page extends Smartcrawl_Entity {
	private $search_phrase;
	private $posts;
	private $page_number;

	public function __construct( $search_phrase, $posts = array(), $page_number = 0 ) {
		$this->search_phrase = $search_phrase;
		$this->posts = $posts;
		$this->page_number = $page_number;
	}

	/**
	 * @return string
	 */
	public function get_search_phrase() {
		return $this->search_phrase;
	}

	/**
	 * @param string $search_phrase
	 */
	public function set_search_phrase( $search_phrase ) {
		$this->search_phrase = $search_phrase;
	}

	protected function load_meta_title() {
		return $this->load_option_string_value(
			'search',
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return '%%searchphrase%% %%sep%% %%sitename%%';
			}
		);
	}

	protected function load_meta_description() {
		return $this->load_option_string_value(
			'search',
			array( $this, 'load_meta_desc_from_options' ),
			'__return_empty_string'
		);
	}

	protected function load_robots() {
		$noindex = $this->get_noindex_setting( 'search' ) ? 'noindex' : 'index';
		$nofollow = $this->get_nofollow_setting( 'search' ) ? 'nofollow' : 'follow';

		return "{$noindex},{$nofollow}";
	}

	protected function load_canonical_url() {
		return $this->is_noindex()
			? ''
			: smartcrawl_append_archive_page_number(
				get_search_link( $this->search_phrase ),
				$this->page_number
			);
	}

	protected function load_schema() {
		$search_schema = new Smartcrawl_Schema_Fragment_Search(
			$this->search_phrase,
			$this->posts,
			$this->get_meta_title(),
			$this->get_meta_description()
		);

		return $search_schema->get_schema();
	}

	protected function load_opengraph_enabled() {
		return $this->is_opengraph_enabled_for_location( 'search' );
	}

	protected function load_opengraph_title() {
		return $this->load_option_string_value(
			'search',
			array( $this, 'load_opengraph_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_opengraph_description() {
		return $this->load_option_string_value(
			'search',
			array( $this, 'load_opengraph_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_opengraph_images() {
		$images = $this->load_opengraph_images_from_options( 'search' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	protected function load_twitter_enabled() {
		return $this->is_twitter_enabled_for_location( 'search' );
	}

	protected function load_twitter_title() {
		return $this->load_option_string_value(
			'search',
			array( $this, 'load_twitter_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_twitter_description() {
		return $this->load_option_string_value(
			'search',
			array( $this, 'load_twitter_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_twitter_images() {
		$images = $this->load_twitter_images_from_options( 'search' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	public function get_macros( $subject = '' ) {
		return array(
			'%%searchphrase%%' => array( $this, 'get_search_phrase' ),
		);
	}
}