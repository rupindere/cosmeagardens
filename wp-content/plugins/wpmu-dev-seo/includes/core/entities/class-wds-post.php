<?php

class Smartcrawl_Post extends Smartcrawl_Entity {
	/**
	 * @var int
	 */
	private $post_id;
	/**
	 * @var WP_Post
	 */
	private $wp_post;
	/**
	 * @var string
	 */
	private $trimmed_excerpt;
	/**
	 * @var string
	 */
	private $permalink;
	/**
	 * @var int
	 */
	private $thumbnail_id;
	/**
	 * @var array
	 */
	private $opengraph_post_meta;
	/**
	 * @var array
	 */
	private $twitter_post_meta;
	/**
	 * @var string
	 */
	private $post_date_formatted;
	/**
	 * @var string
	 */
	private $category_list_string;
	/**
	 * @var string
	 */
	private $post_type;
	/**
	 * @var Smartcrawl_Model_User
	 */
	private $post_author;
	/**
	 * @var int
	 */
	private $page_number;
	/**
	 * @var int
	 */
	private $comments_page;
	/**
	 * @var array
	 */
	private $focus_keywords;

	public function __construct( $post, $page_number = 0, $comments_page = 0 ) {
		if ( is_a( $post, 'WP_Post' ) ) {
			$this->post_id = $post->ID;
			$this->wp_post = $post;
		} else {
			$this->post_id = $post;
		}
		$this->page_number = $page_number;
		$this->comments_page = $comments_page;
	}

	public function get_post_id() {
		return $this->post_id;
	}

	/**
	 * @return WP_Post|null
	 */
	public function get_wp_post() {
		if ( is_null( $this->wp_post ) ) {
			$this->wp_post = $this->load_wp_post();
		}
		return $this->wp_post;
	}

	private function load_wp_post() {
		$post_id = $this->get_post_id();
		if ( ! $post_id ) {
			return false;
		}

		$wp_post = get_post( $post_id );
		return $wp_post
			? $wp_post
			: false;
	}

	public function get_title() {
		$wp_post = $this->get_wp_post();
		return $wp_post
			? $wp_post->post_title
			: '';
	}

	public function get_excerpt() {
		$wp_post = $this->get_wp_post();
		return $wp_post
			? $wp_post->post_excerpt
			: '';
	}

	public function get_content() {
		$wp_post = $this->get_wp_post();
		return $wp_post
			? $wp_post->post_content
			: '';
	}

	public function get_thumbnail_id() {
		if ( is_null( $this->thumbnail_id ) ) {
			$wp_post = $this->get_wp_post();
			$this->thumbnail_id = $wp_post
				? get_post_thumbnail_id( $wp_post )
				: 0;
		}
		return $this->thumbnail_id;
	}

	public function get_post_author() {
		if ( is_null( $this->post_author ) ) {
			$wp_post = $this->get_wp_post();
			$this->post_author = $wp_post
				? Smartcrawl_Model_User::get( $wp_post->post_author )
				: false;
		}

		return $this->post_author;
	}

	public function get_post_author_id() {
		$author = $this->get_post_author();
		return $author
			? $author->get_id()
			: 0;
	}

	public function get_post_author_display_name() {
		$author = $this->get_post_author();
		return $author
			? $author->get_display_name()
			: '';
	}

	public function get_post_author_description() {
		$author = $this->get_post_author();
		return $author
			? $author->get_description()
			: '';
	}

	public function get_post_modified() {
		$wp_post = $this->get_wp_post();
		return $wp_post
			? $wp_post->post_modified
			: '';
	}

	public function get_permalink() {
		if ( is_null( $this->permalink ) ) {
			$this->permalink = $this->load_permalink();
		}
		return $this->permalink;
	}

	private function load_permalink() {
		$wp_post = $this->get_wp_post();
		return $wp_post
			? get_permalink( $wp_post->ID )
			: '';
	}

	public function get_trimmed_excerpt() {
		if ( is_null( $this->trimmed_excerpt ) ) {
			$this->trimmed_excerpt = smartcrawl_get_trimmed_excerpt(
				$this->get_excerpt(),
				$this->get_content()
			);
		}
		return $this->trimmed_excerpt;
	}

	public function get_post_date() {
		$wp_post = $this->get_wp_post();
		return $wp_post
			? $wp_post->post_date
			: '';
	}

	public function get_post_date_formatted() {
		if ( is_null( $this->post_date_formatted ) ) {
			$this->post_date_formatted = $this->load_post_date_formatted();
		}
		return $this->post_date_formatted;
	}

	private function load_post_date_formatted() {
		$post_date = $this->get_post_date();
		if ( ! $post_date ) {
			return '';
		}

		return mysql2date( get_option( 'date_format' ), $post_date );
	}

	public function get_category_list_string() {
		if ( is_null( $this->category_list_string ) ) {
			$this->category_list_string = $this->load_category_list_string();
		}
		return $this->category_list_string;
	}

	private function load_category_list_string() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return '';
		}

		return get_the_category_list( ', ', '', $wp_post->ID );
	}

	protected function load_meta_title() {
		return $this->load_string_value(
			$this->get_post_type(),
			array( $this, 'load_meta_title_from_post_meta' ),
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return "%%title%% %%sep%% %%sitename%%";
			}
		);
	}

	protected function load_meta_title_from_post_meta() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return '';
		}

		return smartcrawl_get_value( 'title', $wp_post->ID );
	}

	protected function load_meta_description() {
		return $this->load_string_value(
			$this->get_post_type(),
			array( $this, 'load_meta_desc_from_post_meta' ),
			array( $this, 'load_meta_desc_from_options' ),
			array( $this, 'get_trimmed_excerpt' )
		);
	}

	protected function load_meta_desc_from_post_meta() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return '';
		}
		return smartcrawl_get_value( 'metadesc', $wp_post->ID );
	}

	protected function load_robots() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return '';
		}

		$post_id = $wp_post->ID;
		$robots[] = $this->is_post_noindex( $post_id ) ? 'noindex' : 'index';
		$robots[] = $this->is_post_nofollow( $post_id ) ? 'nofollow' : 'follow';

		$advanced_value = smartcrawl_get_value( 'meta-robots-adv', $post_id );
		if ( $advanced_value && 'none' !== $advanced_value ) {
			$robots[] = $advanced_value;
		}

		return implode( ',', $robots );
	}

	private function is_post_noindex( $post_id ) {
		// Check if a comment page
		if ( $this->comments_page ) {
			return true;
		}

		// Check at post type level
		$post_type_noindexed = $this->get_noindex_setting( $this->get_post_type() );

		// Check at post level
		$index = (boolean) smartcrawl_get_value( 'meta-robots-index', $post_id );
		$noindex = (boolean) smartcrawl_get_value( 'meta-robots-noindex', $post_id );

		if ( $post_type_noindexed ) {
			return ! $index;
		} else {
			return $noindex;
		}
	}

	private function is_post_nofollow( $post_id ) {
		// Check at post type level
		$post_type_nofollowed = $this->get_nofollow_setting( $this->get_post_type() );

		// Check at post level
		$follow = (boolean) smartcrawl_get_value( 'meta-robots-follow', $post_id );
		$nofollow = (boolean) smartcrawl_get_value( 'meta-robots-nofollow', $post_id );

		if ( $post_type_nofollowed ) {
			return ! $follow;
		} else {
			return $nofollow;
		}
	}

	protected function load_canonical_url() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return '';
		}

		if ( $this->is_noindex() ) {
			return '';
		}

		$canonical = smartcrawl_get_value( 'canonical', $wp_post->ID );
		if ( empty( $canonical ) ) {
			$canonical = $this->get_default_canonical();
		}

		return $canonical;
	}

	private function get_default_canonical() {
		// Start with the permalink
		$canonical_url = $this->get_permalink();

		// Append the page number
		if ( $this->page_number > 1 ) {
			if ( ! get_option( 'permalink_structure' ) ) {
				$canonical_url = add_query_arg( 'page', $this->page_number, $canonical_url );
			} else {
				$canonical_url = trailingslashit( $canonical_url ) . user_trailingslashit( $this->page_number, 'single_paged' );
			}
		}

		// As opposed to wp_get_canonical_url we are not going to include the comment part because we noindex comment pages

		return $canonical_url;
	}

	protected function load_schema() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return array();
		}

		$schema = new Smartcrawl_Schema_Fragment_Singular( $this );
		return $schema->get_schema();
	}

	protected function load_opengraph_enabled() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return false;
		}

		$enabled_in_options = $this->is_opengraph_enabled_for_location( $this->get_post_type() );
		if ( ! $enabled_in_options ) {
			return false;
		}

		$post_meta = $this->get_opengraph_post_meta();
		$disabled_in_post_meta = smartcrawl_get_array_value( $post_meta, 'disabled' );

		return ! $disabled_in_post_meta;
	}

	private function get_opengraph_post_meta() {
		if ( is_null( $this->opengraph_post_meta ) ) {
			$this->opengraph_post_meta = $this->load_opengraph_post_meta();
		}
		return $this->opengraph_post_meta;
	}

	private function load_opengraph_post_meta() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return array();
		}

		return smartcrawl_get_value( 'opengraph', $wp_post->ID );
	}

	protected function load_opengraph_title() {
		return $this->load_string_value(
			$this->get_post_type(),
			array( $this, 'load_opengraph_title_from_post_meta' ),
			array( $this, 'load_opengraph_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_opengraph_title_from_post_meta() {
		return smartcrawl_get_array_value( $this->get_opengraph_post_meta(), 'title' );
	}

	protected function load_opengraph_description() {
		return $this->load_string_value(
			$this->get_post_type(),
			array( $this, 'load_opengraph_description_from_post_meta' ),
			array( $this, 'load_opengraph_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_opengraph_description_from_post_meta() {
		return smartcrawl_get_array_value( $this->get_opengraph_post_meta(), 'description' );
	}

	protected function load_opengraph_images() {
		return $this->load_social_images(
			array( $this, 'get_opengraph_post_meta' ),
			array( $this, 'load_opengraph_images_from_options' ),
			array( $this, 'use_first_content_image_for_opengraph' )
		);
	}

	protected function load_social_images( $load_post_meta, $load_from_options, $use_content_image ) {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return array();
		}

		// Check meta
		$images = smartcrawl_get_array_value( call_user_func( $load_post_meta ), 'images' );
		if ( ! $images ) {
			// Meta not available, check options
			$images = call_user_func( $load_from_options, $this->get_post_type() );
		}

		// Include post thumbnail, if available
		if ( $this->get_thumbnail_id() ) {
			$images[] = $this->get_thumbnail_id();
		}

		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		// Still nothing? Try the first image from the content
		if ( call_user_func( $use_content_image, $this->get_post_type() ) ) {
			$from_content = $this->get_first_image_from_content();
			if ( $from_content ) {
				return array( $from_content => array( $from_content ) );
			}
		}

		return array();
	}

	private function get_first_image_from_content() {
		if ( ! $this->get_content() ) {
			return '';
		}

		$attributes = Smartcrawl_Html::find_attributes( 'img', 'src', $this->get_content() );
		if ( empty( $attributes ) ) {
			return '';
		}

		return array_shift( $attributes );
	}

	private function use_first_content_image_for_opengraph( $post_type ) {
		return ! $this->get_onpage_option( 'og-disable-first-image-' . $post_type );
	}

	private function use_first_content_image_for_twitter( $post_type ) {
		return ! $this->get_onpage_option( 'twitter-disable-first-image-' . $post_type );
	}

	protected function load_twitter_enabled() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return false;
		}

		$enabled_in_options = $this->is_twitter_enabled_for_location( $this->get_post_type() );
		if ( ! $enabled_in_options ) {
			return false;
		}

		$post_meta = $this->get_twitter_post_meta();
		$disabled_in_post_meta = smartcrawl_get_array_value( $post_meta, 'disabled' );

		return ! $disabled_in_post_meta;
	}

	private function get_twitter_post_meta() {
		if ( is_null( $this->twitter_post_meta ) ) {
			$this->twitter_post_meta = $this->load_twitter_post_meta();
		}
		return $this->twitter_post_meta;
	}

	private function load_twitter_post_meta() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return array();
		}

		return smartcrawl_get_value( 'twitter', $wp_post->ID );
	}

	protected function load_twitter_title() {
		return $this->load_string_value(
			$this->get_post_type(),
			array( $this, 'load_twitter_title_from_post_meta' ),
			array( $this, 'load_twitter_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_twitter_title_from_post_meta() {
		return smartcrawl_get_array_value( $this->get_twitter_post_meta(), 'title' );
	}

	protected function load_twitter_description() {
		return $this->load_string_value(
			$this->get_post_type(),
			array( $this, 'load_twitter_description_from_post_meta' ),
			array( $this, 'load_twitter_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_twitter_description_from_post_meta() {
		return smartcrawl_get_array_value( $this->get_twitter_post_meta(), 'description' );
	}

	protected function load_twitter_images() {
		return $this->load_social_images(
			array( $this, 'get_twitter_post_meta' ),
			array( $this, 'load_twitter_images_from_options' ),
			array( $this, 'use_first_content_image_for_twitter' )
		);
	}

	protected function get_post_meta( $meta_key ) {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return '';
		}

		return get_post_meta( $wp_post->ID, $meta_key, true );
	}

	protected function get_linked_terms( $taxonomy_name ) {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return array();
		}

		$terms = get_the_terms( $wp_post->ID, $taxonomy_name );
		return $terms && ! is_wp_error( $terms )
			? $terms
			: array();
	}

	public function get_macros( $subject = '' ) {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return array();
		}

		$macros = array(
			'%%date%%'             => array( $this, 'get_post_date_formatted' ),
			'%%excerpt%%'          => array( $this, 'get_trimmed_excerpt' ),
			'%%excerpt_only%%'     => array( $this, 'get_excerpt' ),
			'%%id%%'               => array( $this, 'get_post_id' ),
			'%%modified%%'         => array( $this, 'get_post_modified' ),
			'%%name%%'             => array( $this, 'get_post_author_display_name' ),
			'%%title%%'            => array( $this, 'get_title' ),
			'%%userid%%'           => array( $this, 'get_post_author_id' ),
			'%%user_description%%' => array( $this, 'get_post_author_description' ),
			'%%caption%%'          => array( $this, 'get_excerpt' ),
			'%%category%%'         => array( $this, 'get_category_list_string' ),
		);

		$dynamic = $this->find_dynamic_replacements(
			$subject,
			array( $this, 'get_linked_terms' ),
			array( $this, 'get_post_meta' )
		);

		return array_merge(
			$macros,
			$dynamic
		);
	}

	public function get_post_type() {
		if ( is_null( $this->post_type ) ) {
			$this->post_type = $this->load_post_type();
		}
		return $this->post_type;
	}

	private function load_post_type() {
		$wp_post = $this->get_wp_post();
		if ( ! $wp_post ) {
			return '';
		}

		if (
			$wp_post->post_type === 'revision' &&
			$wp_post->post_parent
		) {
			return get_post_type( $wp_post->post_parent );
		}

		return $wp_post->post_type;
	}

	protected function load_opengraph_tags() {
		if ( ! $this->get_wp_post() ) {
			return array();
		}

		$tags = parent::load_opengraph_tags();

		if ( $this->is_front_page() ) {
			$tags['og:type'] = 'website';
		} else {
			$tags['og:type'] = 'article';
			$tags['article:published_time'] = mysql2date( 'Y-m-d\TH:i:s', $this->get_post_date() );
			$tags['article:author'] = $this->get_post_author_display_name();
		}

		return $tags;
	}

	/**
	 * @return array
	 */
	public function get_focus_keywords() {
		if ( is_null( $this->focus_keywords ) ) {
			$this->focus_keywords = $this->load_focus_keywords();
		}
		return $this->focus_keywords;
	}

	private function load_focus_keywords() {
		$string = smartcrawl_get_value( 'focus-keywords', $this->get_post_id() );
		if ( empty( $string ) || ! is_scalar( $string ) ) {
			return array();
		}

		$string = trim( strval( $string ) );
		$array = $string ? explode( ',', $string ) : array();
		$array = array_map( 'trim', $array );

		return array_values( array_filter( array_unique( $array ) ) );
	}

	public function is_front_page() {
		return 'page' === get_option( 'show_on_front' )
		       && $this->get_post_id() === (int) get_option( 'page_on_front' );
	}
}