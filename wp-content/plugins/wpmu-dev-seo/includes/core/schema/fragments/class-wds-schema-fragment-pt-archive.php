<?php

class Smartcrawl_Schema_Fragment_PT_Archive extends Smartcrawl_Schema_Fragment {
	private $post_type;
	private $posts;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	private $title;
	private $description;

	/**
	 * Smartcrawl_Schema_Fragment_PT_Archive constructor.
	 *
	 * @param $post_type WP_Post_Type
	 * @param $posts WP_Post[]
	 * @param $title
	 * @param $description
	 */
	public function __construct( $post_type, $posts, $title, $description ) {
		$this->post_type = $post_type;
		$this->posts = $posts;
		$this->title = $title;
		$this->description = $description;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$enabled = (bool) $this->utils->get_schema_option( 'schema_enable_post_type_archives' );
		$disabled = (bool) $this->utils->get_schema_option( array(
			'schema_disabled_post_type_archives',
			$this->post_type->name,
		) );
		$post_type_archive_link = get_post_type_archive_link( $this->post_type->name );

		if ( $enabled && ! $disabled ) {
			return new Smartcrawl_Schema_Fragment_Archive(
				"CollectionPage",
				$post_type_archive_link,
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
					$this->utils->get_webpage_id( $post_type_archive_link )
				);
			} else {
				return array();
			}
		}
	}
}