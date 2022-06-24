<?php

class Smartcrawl_Post_Type extends Smartcrawl_Entity_With_Archive {
	/**
	 * @var WP_Post_Type
	 */
	private $post_type;
	/**
	 * @var array
	 */
	private $posts;
	/**
	 * @var string
	 */
	private $location;
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $singular_name;
	/**
	 * @var int
	 */
	private $page_number;

	/**
	 * @param $post_type WP_Post_Type
	 * @param array $posts
	 */
	public function __construct( $post_type, $posts = array(), $page_number = 0 ) {
		$this->post_type = $post_type;
		$this->posts = $posts;
		$this->location = $this->post_type
			? Smartcrawl_Onpage_Settings::PT_ARCHIVE_PREFIX . $this->post_type->name
			: '';
		$this->page_number = $page_number;
	}

	public function get_name() {
		if ( is_null( $this->name ) ) {
			$this->name = $this->load_name();
		}

		return $this->name;
	}

	private function load_name() {
		if ( ! $this->post_type ) {
			return '';
		}

		return $this->post_type->labels->name;
	}

	public function get_singular_name() {
		if ( is_null( $this->singular_name ) ) {
			$this->singular_name = $this->load_singular_name();
		}

		return $this->singular_name;
	}

	private function load_singular_name() {
		if ( ! $this->post_type ) {
			return '';
		}

		return $this->post_type->labels->singular_name;
	}

	protected function load_meta_title() {
		return $this->load_option_string_value(
			$this->location,
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return '%%pt_plural%% %%sep%% %%sitename%%';
			}
		);
	}

	protected function load_meta_description() {
		return $this->load_option_string_value(
			$this->location,
			array( $this, 'load_meta_desc_from_options' ),
			'__return_empty_string'
		);
	}

	protected function load_robots() {
		return $this->get_robots_for_page_number( $this->page_number );
	}

	protected function load_canonical_url() {
		if ( ! $this->post_type ) {
			return '';
		}

		$first_page_indexed = $this->is_first_page_indexed();
		$current_page_indexed = ! $this->is_noindex();
		$post_type_link = get_post_type_archive_link( $this->post_type->name );

		if ( $current_page_indexed ) {
			return $this->append_page_number( $post_type_link, $this->page_number );
		} else {
			if ( $first_page_indexed ) {
				return $post_type_link;
			} else {
				return '';
			}
		}
	}

	protected function load_schema() {
		if ( ! $this->post_type ) {
			return array();
		}

		$fragment = new Smartcrawl_Schema_Fragment_PT_Archive(
			$this->post_type,
			$this->posts,
			$this->get_meta_title(),
			$this->get_meta_description()
		);

		return $fragment->get_schema();
	}

	protected function load_opengraph_enabled() {
		return $this->is_opengraph_enabled_for_location( $this->location );
	}

	protected function load_opengraph_title() {
		return $this->load_option_string_value(
			$this->location,
			array( $this, 'load_opengraph_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_opengraph_description() {
		return $this->load_option_string_value(
			$this->location,
			array( $this, 'load_opengraph_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_opengraph_images() {
		$images = $this->load_opengraph_images_from_options( $this->location );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	protected function load_twitter_enabled() {
		return $this->is_twitter_enabled_for_location( $this->location );
	}

	protected function load_twitter_title() {
		return $this->load_option_string_value(
			$this->location,
			array( $this, 'load_twitter_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_twitter_description() {
		return $this->load_option_string_value(
			$this->location,
			array( $this, 'load_twitter_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_twitter_images() {
		$images = $this->load_twitter_images_from_options( $this->location );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	public function get_macros( $subject = '' ) {
		return array(
			'%%pt_plural%%' => array( $this, 'get_name' ),
			'%%pt_single%%' => array( $this, 'get_singular_name' ),
		);
	}

	/**
	 * @param $page_number
	 *
	 * @return string
	 */
	protected function get_robots_for_page_number( $page_number ) {
		if (
			$this->show_robots_on_subsequent_pages_only( $this->location )
			&& $page_number < 2
		) {
			return '';
		}

		$noindex = $this->get_noindex_setting( $this->location ) ? 'noindex' : 'index';
		$nofollow = $this->get_nofollow_setting( $this->location ) ? 'nofollow' : 'follow';

		return "{$noindex},{$nofollow}";
	}
}