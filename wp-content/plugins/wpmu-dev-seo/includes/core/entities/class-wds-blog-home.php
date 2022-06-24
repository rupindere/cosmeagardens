<?php

class Smartcrawl_Blog_Home extends Smartcrawl_Entity_With_Archive {
	private $page_number;

	public function __construct( $page_number = 0 ) {
		$this->page_number = $page_number;
	}

	protected function load_meta_title() {
		return $this->load_option_string_value(
			'home',
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return '%%sitename%%';
			}
		);
	}

	protected function load_meta_description() {
		return $this->load_option_string_value(
			'home',
			array( $this, 'load_meta_desc_from_options' ),
			function () {
				return '%%sitedesc%%';
			}
		);
	}

	protected function load_robots() {
		return $this->get_robots_for_page_number( $this->page_number );
	}

	protected function load_canonical_url() {
		$blog_home_url = trailingslashit( get_bloginfo( 'url' ) );
		$first_page_indexed = $this->is_first_page_indexed();
		$current_page_indexed = ! $this->is_noindex();
		if ( $current_page_indexed ) {
			return $this->append_page_number( $blog_home_url, $this->page_number );
		} else {
			if ( $first_page_indexed ) {
				return $blog_home_url;
			} else {
				return '';
			}
		}
	}

	protected function load_schema() {
		$fragment = new Smartcrawl_Schema_Fragment_Blog_Home(
			$this->get_meta_title(),
			$this->get_meta_description()
		);

		return $fragment->get_schema();
	}

	protected function load_opengraph_tags() {
		$tags = parent::load_opengraph_tags();

		$tags['og:type'] = 'website';

		return $tags;
	}

	protected function load_opengraph_enabled() {
		return $this->is_opengraph_enabled_for_location( 'home' );
	}

	protected function load_opengraph_title() {
		return $this->load_option_string_value(
			'home',
			array( $this, 'load_opengraph_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_opengraph_description() {
		return $this->load_option_string_value(
			'home',
			array( $this, 'load_opengraph_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_opengraph_images() {
		$images = $this->load_opengraph_images_from_options( 'home' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	protected function load_twitter_enabled() {
		return $this->is_twitter_enabled_for_location( 'home' );
	}

	protected function load_twitter_title() {
		return $this->load_option_string_value(
			'home',
			array( $this, 'load_twitter_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_twitter_description() {
		return $this->load_option_string_value(
			'home',
			array( $this, 'load_twitter_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_twitter_images() {
		$images = $this->load_twitter_images_from_options( 'home' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	public function get_macros( $subject = '' ) {
		return array();
	}

	/**
	 * @param $page_number
	 *
	 * @return string
	 */
	protected function get_robots_for_page_number( $page_number ) {
		$setting_key = 'main_blog_archive';
		if (
			$this->show_robots_on_subsequent_pages_only( $setting_key )
			&& $page_number < 2
		) {
			return '';
		}

		$noindex = $this->get_noindex_setting( $setting_key ) ? 'noindex' : 'index';
		$nofollow = $this->get_nofollow_setting( $setting_key ) ? 'nofollow' : 'follow';

		return "{$noindex},{$nofollow}";
	}
}