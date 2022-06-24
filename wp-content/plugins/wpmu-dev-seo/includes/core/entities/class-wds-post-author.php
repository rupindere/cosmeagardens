<?php

class Smartcrawl_Post_Author extends Smartcrawl_Entity_With_Archive {
	/**
	 * @var WP_User
	 */
	private $user;
	/**
	 * @var array
	 */
	private $posts;
	/**
	 * @var string
	 */
	private $display_name;
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var int
	 */
	private $page_number;

	/**
	 * @param $user WP_User
	 */
	public function __construct( $user, $posts = array(), $page_number = 0 ) {
		$this->user = $user;
		$this->posts = $posts;
		$this->page_number = $page_number;
	}

	protected function load_meta_title() {
		return $this->load_string_value(
			'author',
			array( $this, 'load_meta_title_from_author_meta' ),
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return '%%name%% %%sep%% %%sitename%%';
			}
		);
	}

	protected function load_meta_title_from_author_meta() {
		if ( ! $this->user ) {
			return '';
		}

		return get_the_author_meta( 'wds_title', $this->user->ID );
	}

	protected function load_meta_description() {
		return $this->load_string_value(
			'author',
			array( $this, 'load_meta_desc_from_author_meta' ),
			array( $this, 'load_meta_desc_from_options' ),
			function () {
				return '%%user_description%%';
			}
		);
	}

	protected function load_meta_desc_from_author_meta() {
		if ( ! $this->user ) {
			return '';
		}

		return get_the_author_meta( 'wds_metadesc', $this->user->ID );
	}

	protected function load_robots() {
		return $this->get_robots_for_page_number( $this->page_number );
	}

	protected function load_canonical_url() {
		if ( ! $this->user ) {
			return '';
		}

		$first_page_indexed = $this->is_first_page_indexed();
		$current_page_indexed = ! $this->is_noindex();
		$author_posts_url = get_author_posts_url( $this->user->ID );

		if ( $current_page_indexed ) {
			return $this->append_page_number( $author_posts_url, $this->page_number );
		} else {
			if ( $first_page_indexed ) {
				return $author_posts_url;
			} else {
				return '';
			}
		}
	}

	protected function load_schema() {
		if ( ! $this->user ) {
			return array();
		}

		$fragment = new Smartcrawl_Schema_Fragment_Author_Archive(
			$this->user,
			$this->posts,
			$this->get_meta_title(),
			$this->get_meta_description()
		);

		return $fragment->get_schema();
	}

	protected function load_opengraph_enabled() {
		return $this->is_opengraph_enabled_for_location( 'author' );
	}

	protected function load_opengraph_title() {
		return $this->load_option_string_value(
			'author',
			array( $this, 'load_opengraph_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_opengraph_description() {
		return $this->load_option_string_value(
			'author',
			array( $this, 'load_opengraph_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_opengraph_images() {
		$images = $this->load_opengraph_images_from_options( 'author' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	protected function load_twitter_enabled() {
		return $this->is_twitter_enabled_for_location( 'author' );
	}

	protected function load_twitter_title() {
		return $this->load_option_string_value(
			'author',
			array( $this, 'load_twitter_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_twitter_description() {
		return $this->load_option_string_value(
			'author',
			array( $this, 'load_twitter_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_twitter_images() {
		$images = $this->load_twitter_images_from_options( 'author' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	public function get_id() {
		if ( ! $this->user ) {
			return 0;
		}

		return $this->user->ID;
	}

	public function get_display_name() {
		if ( is_null( $this->display_name ) ) {
			$this->display_name = $this->load_display_name();
		}
		return $this->display_name;
	}

	private function load_display_name() {
		if ( ! $this->user ) {
			return '';
		}

		return get_the_author_meta( 'display_name', $this->user->ID );
	}

	public function get_description() {
		if ( is_null( $this->description ) ) {
			$this->description = $this->load_description();
		}

		return $this->description;
	}

	private function load_description() {
		if ( ! $this->user ) {
			return '';
		}

		return get_the_author_meta( 'description', $this->user->ID );
	}

	public function get_macros( $subject = '' ) {
		if ( ! $this->user ) {
			return array();
		}

		return array(
			'%%name%%'             => array( $this, 'get_display_name' ),
			'%%userid%%'           => array( $this, 'get_id' ),
			'%%user_description%%' => array( $this, 'get_description' ),
		);
	}

	/**
	 * @param $page_number
	 *
	 * @return string
	 */
	protected function get_robots_for_page_number( $page_number ) {
		$options = Smartcrawl_Settings::get_options();
		if ( empty( $options['enable-author-archive'] ) ) {
			return 'noindex,follow';
		}

		$setting_key = 'author';
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