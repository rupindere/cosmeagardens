<?php

class Smartcrawl_Schema_Fragment_Singular extends Smartcrawl_Schema_Fragment {
	/**
	 * @var Smartcrawl_Post
	 */
	private $post;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	/**
	 * @var Smartcrawl_Model_User
	 */
	private $owner;
	/**
	 * @var bool
	 */
	private $include_article_schema;

	/**
	 * Smartcrawl_Schema_Fragment_Singular constructor.
	 *
	 * @param $post
	 * @param $include_article_schema
	 */
	public function __construct( $post, $include_article_schema = true ) {
		$this->post = $post;
		$this->include_article_schema = $include_article_schema;
		$this->utils = Smartcrawl_Schema_Utils::get();
		$this->owner = Smartcrawl_Model_User::owner();
	}

	protected function get_raw() {
		$url = $this->post->get_permalink();
		$title = $this->post->get_meta_title();
		$description = $this->post->get_meta_description();

		$is_publisher_page = $this->is_publisher_output_page();
		$publisher = new Smartcrawl_Schema_Fragment_Publisher( $is_publisher_page );

		$schema = array(
			new Smartcrawl_Schema_Fragment_Header( $url, $title, $description ),
			new Smartcrawl_Schema_Fragment_Footer( $url, $title, $description ),
			$publisher,
			new Smartcrawl_Schema_Fragment_Website(),
		);

		$author_id = false;
		$author = Smartcrawl_Model_User::get( $this->post->get_wp_post()->post_author );
		$add_publishing_person = $is_publisher_page && $this->utils->is_schema_type_person();
		if ( $add_publishing_person ) {
			$publishing_person = new Smartcrawl_Schema_Fragment_Publishing_Person( $publisher->get_publisher_url() );
			$schema[] = $publishing_person;

			if ( $this->owner->get_id() === $author->get_id() ) {
				$author_id = $publishing_person->get_publishing_person_id();
			}
		}

		if ( ! $author_id ) {
			$post_author = new Smartcrawl_Schema_Fragment_Post_Author( $this->post->get_post_author() );
			$schema[] = $post_author;
			$author_id = $post_author->get_post_author_id();
		}

		$custom_schema_types = $this->utils->get_custom_schema_types( $this->post->get_wp_post(), $this->post->is_front_page() );
		if ( $custom_schema_types ) {
			$schema[] = new Smartcrawl_Schema_Fragment_Minimal_Webpage( $url, $publisher->get_publisher_id() );

			$schema = $this->utils->add_custom_schema_types(
				$schema,
				$custom_schema_types,
				$this->utils->get_webpage_id( $url )
			);
		} elseif ( $this->is_contact_page() || $this->is_about_page() ) {
			$webpage_type = $this->is_contact_page()
				? "ContactPage"
				: "AboutPage";

			$schema[] = new Smartcrawl_Schema_Fragment_Webpage(
				$this->post,
				$webpage_type,
				$author_id,
				$publisher->get_publisher_id()
			);
		} else if ( $this->include_article_schema ) {
			$schema[] = new Smartcrawl_Schema_Fragment_Minimal_Webpage( $url, $publisher->get_publisher_id() );
			$schema[] = new Smartcrawl_Schema_Fragment_Article(
				$this->post,
				$this->get_article_type(),
				$author_id,
				$publisher->get_publisher_id()
			);
		}

		$media = new Smartcrawl_Schema_Fragment_Media( $this->post );
		foreach ( $media->get_schema() as $media_schema ) {
			$schema[] = $media_schema;
		}

		return $schema;
	}

	private function get_article_type() {
		return $this->show_news_article_schema()
			? "NewsArticle"
			: "Article";
	}

	private function is_about_page() {
		return $this->is_special_page( 'schema_about_page' );
	}

	private function is_contact_page() {
		return $this->is_special_page( 'schema_contact_page' );
	}

	private function is_special_page( $key, $default = false ) {
		$output_page = $this->utils->get_special_page( $key );
		if ( ! $output_page ) {
			return $default;
		}

		return $this->post->get_post_id() === $output_page->ID;
	}

	private function is_publisher_output_page() {
		if ( $this->is_special_page( 'schema_output_page', $this->post->is_front_page() ) ) {
			return true;
		}

		return false;
	}

	private function show_news_article_schema() {
		if ( ! Smartcrawl_Sitemap_Utils::get_sitemap_option( 'enable-news-sitemap' ) ) {
			return false;
		}

		$news_query = new Smartcrawl_Sitemap_News_Query();
		$supported_types = $news_query->get_supported_types();
		if ( ! in_array( $this->post->get_post_type(), $supported_types, true ) ) {
			return false;
		}

		$ignore_ids = $news_query->get_ignore_ids( array( $this->post->get_post_type() ) );
		if ( in_array( $this->post->get_post_id(), $ignore_ids ) ) {
			return false;
		}

		return true;
	}
}