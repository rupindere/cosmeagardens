<?php

class Smartcrawl_Taxonomy_Term extends Smartcrawl_Entity_With_Archive {
	/**
	 * @var int
	 */
	private $term_id;

	/**
	 * @var WP_Term
	 */
	private $wp_term;
	/**
	 * @var array
	 */
	private $posts;
	/**
	 * @var array
	 */
	private $opengraph_term_meta;
	/**
	 * @var array
	 */
	private $twitter_term_meta;
	/**
	 * @var int
	 */
	private $page_number;

	/**
	 * Smartcrawl_Taxonomy_Term constructor.
	 *
	 * @param $term WP_Term|int
	 * @param $posts array
	 */
	public function __construct( $term, $posts = array(), $page_number = 0 ) {
		if ( is_a( $term, 'WP_Term' ) ) {
			$this->term_id = $term->term_id;
			$this->wp_term = $term;
		} else {
			$this->term_id = $term;
		}
		$this->posts = $posts;
		$this->page_number = $page_number;
	}

	public function get_term_id() {
		return $this->term_id;
	}

	public function get_name() {
		$wp_term = $this->get_wp_term();
		return $wp_term
			? $wp_term->name
			: '';
	}

	public function get_description() {
		$wp_term = $this->get_wp_term();
		return $wp_term
			? $wp_term->description
			: '';
	}

	public function get_slug() {
		$wp_term = $this->get_wp_term();
		return $wp_term
			? $wp_term->slug
			: '';
	}

	public function get_wp_term() {
		if ( is_null( $this->wp_term ) ) {
			$this->wp_term = $this->load_wp_term();
		}
		return $this->wp_term;
	}

	private function load_wp_term() {
		$term = get_term( $this->term_id );
		if ( ! $term || is_wp_error( $term ) ) {
			return false;
		}
		return $term;
	}

	public function get_taxonomy() {
		$wp_term = $this->get_wp_term();
		return $wp_term
			? $wp_term->taxonomy
			: '';
	}

	protected function load_meta_title() {
		return $this->load_string_value(
			$this->get_taxonomy(),
			array( $this, 'load_meta_title_from_term_meta' ),
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return '%%term_title%% %%sep%% %%sitename%%';
			}
		);
	}

	protected function load_meta_title_from_term_meta() {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return '';
		}

		return smartcrawl_get_term_meta( $wp_term, $wp_term->taxonomy, 'wds_title' );
	}

	protected function load_meta_description() {
		return $this->load_string_value(
			$this->get_taxonomy(),
			array( $this, 'load_meta_desc_from_term_meta' ),
			array( $this, 'load_meta_desc_from_options' ),
			array( $this, 'get_description' )
		);
	}

	protected function load_meta_desc_from_term_meta() {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return '';
		}

		return smartcrawl_get_term_meta( $wp_term, $wp_term->taxonomy, 'wds_desc' );
	}

	protected function load_robots() {
		return $this->get_robots_for_page_number( $this->page_number );
	}

	protected function load_canonical_url() {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return '';
		}

		$canonical_from_meta = smartcrawl_get_term_meta( $wp_term, $wp_term->taxonomy, 'wds_canonical' );
		if ( $canonical_from_meta ) {
			return $canonical_from_meta;
		}

		$first_page_indexed = $this->is_first_page_indexed();
		$current_page_indexed = ! $this->is_noindex();
		$term_link = get_term_link( $wp_term, $wp_term->taxonomy );

		if ( $current_page_indexed ) {
			return $this->append_page_number( $term_link, $this->page_number );
		} else {
			if ( $first_page_indexed ) {
				return $term_link;
			} else {
				return '';
			}
		}
	}

	protected function load_schema() {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return array();
		}

		$schema = new Smartcrawl_Schema_Fragment_Tax_Archive(
			$wp_term,
			$this->posts,
			$this->get_meta_title(),
			$this->get_meta_description()
		);
		return $schema->get_schema();
	}

	protected function load_opengraph_tags() {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return array();
		}

		return parent::load_opengraph_tags();
	}

	protected function load_opengraph_enabled() {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return false;
		}

		$enabled_in_options = $this->is_opengraph_enabled_for_location( $this->get_taxonomy() );
		if ( ! $enabled_in_options ) {
			return false;
		}

		$term_meta = $this->get_opengraph_term_meta();
		$disabled_in_term_meta = smartcrawl_get_array_value( $term_meta, 'disabled' );
		return ! $disabled_in_term_meta;
	}

	private function get_opengraph_term_meta() {
		if ( is_null( $this->opengraph_term_meta ) ) {
			$this->opengraph_term_meta = $this->load_opengraph_term_meta();
		}
		return $this->opengraph_term_meta;
	}

	private function load_opengraph_term_meta() {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return array();
		}

		return smartcrawl_get_term_meta( $wp_term, $wp_term->taxonomy, 'opengraph' );
	}

	public function get_term_meta( $meta_key ) {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return '';
		}

		return get_term_meta( $this->get_term_id(), $meta_key, true );
	}

	protected function load_opengraph_title() {
		return $this->load_string_value(
			$this->get_taxonomy(),
			array( $this, 'load_opengraph_title_from_term_meta' ),
			array( $this, 'load_opengraph_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_opengraph_title_from_term_meta() {
		return smartcrawl_get_array_value( $this->get_opengraph_term_meta(), 'title' );
	}

	protected function load_opengraph_description() {
		return $this->load_string_value(
			$this->get_taxonomy(),
			array( $this, 'load_opengraph_description_from_term_meta' ),
			array( $this, 'load_opengraph_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_opengraph_description_from_term_meta() {
		return smartcrawl_get_array_value( $this->get_opengraph_term_meta(), 'description' );
	}

	protected function load_opengraph_images() {
		return $this->load_social_images(
			array( $this, 'get_opengraph_term_meta' ),
			array( $this, 'load_opengraph_images_from_options' )
		);
	}

	private function load_social_images( $load_post_meta, $load_from_options ) {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return array();
		}

		// Check meta
		$images = smartcrawl_get_array_value( call_user_func( $load_post_meta ), 'images' );
		if ( ! $images ) {
			// Meta not available, check options
			$images = call_user_func( $load_from_options, $this->get_taxonomy() );
		}

		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	protected function load_twitter_enabled() {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return false;
		}

		$enabled_in_options = $this->is_twitter_enabled_for_location( $this->get_taxonomy() );
		if ( ! $enabled_in_options ) {
			return false;
		}

		$term_meta = $this->get_twitter_term_meta();
		$disabled_in_term_meta = smartcrawl_get_array_value( $term_meta, 'disabled' );

		return ! $disabled_in_term_meta;
	}

	public function get_twitter_term_meta() {
		if ( is_null( $this->twitter_term_meta ) ) {
			$this->twitter_term_meta = $this->load_twitter_term_meta();
		}
		return $this->twitter_term_meta;
	}

	private function load_twitter_term_meta() {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return array();
		}

		return smartcrawl_get_term_meta( $wp_term, $wp_term->taxonomy, 'twitter' );
	}

	protected function load_twitter_title() {
		return $this->load_string_value(
			$this->get_taxonomy(),
			array( $this, 'load_twitter_title_from_term_meta' ),
			array( $this, 'load_twitter_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_twitter_title_from_term_meta() {
		return smartcrawl_get_array_value( $this->get_twitter_term_meta(), 'title' );
	}

	protected function load_twitter_description() {
		return $this->load_string_value(
			$this->get_taxonomy(),
			array( $this, 'load_twitter_description_from_term_meta' ),
			array( $this, 'load_twitter_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_twitter_description_from_term_meta() {
		return smartcrawl_get_array_value( $this->get_twitter_term_meta(), 'description' );
	}

	protected function load_twitter_images() {
		return $this->load_social_images(
			array( $this, 'get_twitter_term_meta' ),
			array( $this, 'load_twitter_images_from_options' )
		);
	}

	public function get_macros( $subject = '' ) {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return array();
		}

		$macros = array(
			'%%id%%'               => array( $this, 'get_term_id' ),
			'%%term_title%%'       => array( $this, 'get_name' ),
			'%%term_description%%' => array( $this, 'get_description' ),
		);

		if ( $this->get_taxonomy() === 'category' ) {
			$macros['%%category%%'] = array( $this, 'get_name' );
			$macros['%%category_description%%'] = array( $this, 'get_description' );
		} elseif ( $this->get_taxonomy() === 'post_tag' ) {
			$macros['%%tag%%'] = array( $this, 'get_name' );
			$macros['%%tag_description%%'] = array( $this, 'get_description' );
		}

		$dynamic = $this->find_dynamic_replacements(
			$subject,
			function ( $taxonomy ) use ( $wp_term ) {
				if ( $taxonomy === $wp_term->taxonomy ) {
					return array( $wp_term );
				}

				return array();
			},
			array( $this, 'get_term_meta' )
		);

		return array_merge(
			$macros,
			$dynamic
		);
	}

	/**
	 * @param $wp_term
	 *
	 * @return bool
	 */
	protected function is_term_noindex( $wp_term ) {
		$noindex_in_settings = $this->get_noindex_setting( $wp_term->taxonomy );
		$noindex_overridden = (bool) smartcrawl_get_term_meta( $wp_term, $wp_term->taxonomy, 'wds_override_noindex' );
		$noindex_in_meta = (bool) smartcrawl_get_term_meta( $wp_term, $wp_term->taxonomy, 'wds_noindex' );
		if ( $noindex_in_settings ) {
			$noindex = ! $noindex_overridden;
		} else {
			$noindex = $noindex_in_meta;
		}
		return $noindex;
	}

	/**
	 * @param $wp_term
	 *
	 * @return bool
	 */
	protected function is_term_nofollow( $wp_term ) {
		$nofollow_in_settings = $this->get_nofollow_setting( $wp_term->taxonomy );
		$nofollow_overridden = (bool) smartcrawl_get_term_meta( $wp_term, $wp_term->taxonomy, 'wds_override_nofollow' );
		$nofollow_in_meta = (bool) smartcrawl_get_term_meta( $wp_term, $wp_term->taxonomy, 'wds_nofollow' );
		if ( $nofollow_in_settings ) {
			$nofollow = ! $nofollow_overridden;
		} else {
			$nofollow = $nofollow_in_meta;
		}
		return $nofollow;
	}

	/**
	 * @param $page_number
	 *
	 * @return string
	 */
	protected function get_robots_for_page_number( $page_number ) {
		$wp_term = $this->get_wp_term();
		if ( ! $wp_term ) {
			return '';
		}

		if (
			$this->show_robots_on_subsequent_pages_only( $this->get_taxonomy() )
			&& $page_number < 2
		) {
			return '';
		}

		$noindex = $this->is_term_noindex( $wp_term );
		$nofollow = $this->is_term_nofollow( $wp_term );

		$noindex_string = $noindex ? 'noindex' : 'index';
		$nofollow_string = $nofollow ? 'nofollow' : 'follow';
		return "{$noindex_string},{$nofollow_string}";
	}
}