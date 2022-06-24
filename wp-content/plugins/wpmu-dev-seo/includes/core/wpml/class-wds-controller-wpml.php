<?php

class Smartcrawl_Controller_Wpml extends Smartcrawl_Base_Controller {
	private static $_instance;
	/**
	 * @var Smartcrawl_Wpml_Api
	 */
	private $wpml_api;

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		parent::__construct();

		$this->wpml_api = new Smartcrawl_Wpml_Api();
	}

	public function should_run() {
		return class_exists( 'SitePress' );
	}

	protected function init() {
		add_action( 'plugins_loaded', array( $this, 'hook_with_wpml' ) );
	}

	public function hook_with_wpml() {
		global $sitepress;
		if ( empty( $sitepress ) ) {
			return;
		}

		add_action( 'wds_post_readability_language', array( $this, 'change_post_analysis_language' ), 10, 2 );
		add_action( 'wds_post_seo_analysis_language', array( $this, 'change_post_analysis_language' ), 10, 2 );

		$strategy = $this->wpml_api->get_setting( 'language_negotiation_type' );
		$separate_domain_per_lang = $strategy == 2;
		if ( $separate_domain_per_lang ) {
			$this->sitemap_for_each_domain();
		} else {
			$this->fix_duplicate_urls();
		}
	}

	/**
	 * If the user has a separate domain for each language we need to make sure that each domain serves a unique sitemap only containing URLs belonging to that domain
	 */
	private function sitemap_for_each_domain() {
		add_filter( 'wds_posts_sitemap_include_post_ids', array(
			$this,
			'limit_sitemap_posts_by_language',
		), 10, 2 );
		add_filter( 'wds_terms_sitemap_include_term_ids', array( $this, 'limit_sitemap_terms_by_language' ), 10, 2 );
		add_filter( 'wds_news_sitemap_include_post_ids', array( $this, 'limit_sitemap_posts_by_language' ), 10, 2 );
		add_filter( 'wds_sitemap_cache_file_name', array( $this, 'append_language_code_to_cache' ) );
	}

	public function limit_sitemap_terms_by_language( $include_ids, $taxonomies ) {
		$term_query = new WP_Term_Query( array(
			'taxonomy' => $taxonomies,
			'fields'   => 'ids',
		) );

		$term_ids = $term_query->get_terms();
		if ( empty( $term_ids ) ) {
			$term_ids = array( - 1 );
		}
		$include_ids = empty( $include_ids ) || ! is_array( $include_ids )
			? array()
			: $include_ids;

		return array_merge( $include_ids, $term_ids );
	}

	public function limit_sitemap_posts_by_language( $include_ids, $post_types ) {
		$query = new WP_Query( array(
			'post_type'        => $post_types,
			'posts_per_page'   => - 1,
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'suppress_filters' => false,
		) );

		$post_ids = $query->get_posts();
		if ( empty( $post_ids ) ) {
			$post_ids = array( - 1 );
		}
		$include_ids = empty( $include_ids ) || ! is_array( $include_ids )
			? array()
			: $include_ids;

		return array_merge( $include_ids, $post_ids );
	}

	public function append_language_code_to_cache( $file_name ) {
		$current_lang = apply_filters( 'wpml_current_language', null );
		return "$current_lang-$file_name";
	}

	/**
	 * WPML tries to 'translate' urls but in our context it leads to every URL getting converted to the default language.
	 *
	 * If the post ID of an Urdu post is passed to get_permalink, we expect to get the Urdu url in return but the conversion changes it to default language URL.
	 */
	private function fix_duplicate_urls() {
		add_filter( 'wds_before_sitemap_rebuild', array( $this, 'add_permalink_filters' ) );
		add_filter( 'wds_sitemap_created', array( $this, 'remove_permalink_filters' ) );
		add_filter( 'wds_full_sitemap_items', array( $this, 'add_homepage_versions' ) );
		add_filter( 'wds_partial_sitemap_items', array( $this, 'add_homepage_versions_to_partial' ), 10, 3 );
	}

	public function add_homepage_versions_to_partial( $items, $type, $page_number ) {
		$is_first_post_sitemap = ( $type === 'post' || $type === 'page' ) && $page_number === 1;
		if ( ! $is_first_post_sitemap ) {
			return $items;
		}

		return $this->add_homepage_versions( $items );
	}

	public function add_homepage_versions( $items ) {
		// Remove the original home url
		array_shift( $items );

		// Add all homepage versions
		$languages = $this->wpml_api->get_active_languages( false, true );
		foreach ( $languages as $language_code => $language ) {
			if ( $this->wpml_api->get_default_language() === $language_code ) {
				continue;
			}

			$item_url = $this->wpml_api->convert_url( home_url(), $language_code );
			array_unshift(
				$items,
				$this->get_sitemap_homepage_item( $item_url )
			);
		}

		array_unshift(
			$items,
			$this->get_sitemap_homepage_item( home_url( '/' ) )
		);

		return $items;
	}

	public function add_permalink_filters() {
		$callback = array( $this, 'translate_post_url' );

		add_filter( 'post_link', $callback, 10, 2 );
		add_filter( 'page_link', $callback, 10, 2 );
		add_filter( 'post_type_link', $callback, 10, 2 );
	}

	public function remove_permalink_filters() {
		$callback = array( $this, 'translate_post_url' );

		remove_filter( 'post_link', $callback );
		remove_filter( 'page_link', $callback );
		remove_filter( 'post_type_link', $callback );
	}

	/**
	 * @param $link
	 * @param $post_or_id WP_Post
	 *
	 * @return string
	 */
	public function translate_post_url( $link, $post_or_id ) {
		$post = get_post( $post_or_id );
		$language = $this->wpml_api->wpml_get_language_information( null, $post->ID );
		$language_code = smartcrawl_get_array_value( $language, 'language_code' );
		if ( $this->wpml_api->get_current_language() === $language_code ) {
			return $link;
		}

		$this->remove_permalink_filters(); // To avoid infinite recursion
		$language_url = apply_filters( 'wpml_permalink', get_permalink( $post->ID ), $language_code, true );
		$this->add_permalink_filters();

		return $language_url;
	}

	private function get_sitemap_homepage_item( $url ) {
		$item = new Smartcrawl_Sitemap_Item();
		return $item->set_location( $url )
		            ->set_priority( 1 )
		            ->set_change_frequency( Smartcrawl_Sitemap_Item::FREQ_DAILY );
	}

	public function change_post_analysis_language( $post_language, $post_id ) {
		$wpml_lang_code = $this->get_post_language_code( $post_id );

		return ! empty( $wpml_lang_code )
			? $wpml_lang_code
			: $post_language;
	}

	/**
	 * We would rather use wpml_get_language_information, but it has internal caching that doesn't get purged the first time a post is saved.
	 *
	 * @param $post_id
	 *
	 * @return string|null
	 */
	private function get_post_language_code( $post_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_id = %d", $post_id ) );
	}
}