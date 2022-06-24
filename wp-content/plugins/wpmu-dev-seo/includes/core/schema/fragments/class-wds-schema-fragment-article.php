<?php

class Smartcrawl_Schema_Fragment_Article extends Smartcrawl_Schema_Fragment {
	/**
	 * @var Smartcrawl_Post
	 */
	private $post;
	private $type;
	private $author_id;
	private $publisher_id;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;

	public function __construct( $post, $type, $author_id, $publisher_id ) {
		$this->post = $post;
		$this->type = $type;
		$this->author_id = $author_id;
		$this->publisher_id = $publisher_id;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$post_fragment = new Smartcrawl_Schema_Fragment_Post(
			$this->post,
			$this->author_id,
			$this->publisher_id,
			true
		);

		return array(
			       "@type"            => $this->type,
			       "mainEntityOfPage" => array(
				       "@id" => $this->utils->get_webpage_id( $this->post->get_permalink() ),
			       ),
		       ) + $post_fragment->get_schema();
	}
}