<?php

/**
 * Class Smartcrawl_Schema_Fragment_Blog_Home
 *
 * Schema for traditional blog style home page
 */
class Smartcrawl_Schema_Fragment_Blog_Home extends Smartcrawl_Schema_Fragment {
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	private $url;
	private $title;
	private $description;

	public function __construct( $title, $description ) {
		$this->title = $title;
		$this->description = $description;
		$this->url = get_site_url();
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$is_publisher_page = $this->is_publisher_output_page();

		$publisher = new Smartcrawl_Schema_Fragment_Publisher( $is_publisher_page );
		$schema = array(
			new Smartcrawl_Schema_Fragment_Header( $this->url, $this->title, $this->description ),
			new Smartcrawl_Schema_Fragment_Footer( $this->url, $this->title, $this->description ),
			$publisher,
			new Smartcrawl_Schema_Fragment_Website(),
		);

		if ( $is_publisher_page && $this->utils->is_schema_type_person() ) {
			$schema[] = new Smartcrawl_Schema_Fragment_Publishing_Person( $publisher->get_publisher_url() );
		}

		$custom_schema_types = $this->utils->get_custom_schema_types( null, true );
		if ( $custom_schema_types ) {
			$webpage_id = $this->utils->get_webpage_id( $this->url );

			$schema[] = new Smartcrawl_Schema_Fragment_Minimal_Webpage(
				$this->url,
				$publisher->get_publisher_id()
			);

			$schema = $this->utils->add_custom_schema_types(
				$schema,
				$custom_schema_types,
				$webpage_id
			);
		} else {
			$schema[] = new Smartcrawl_Schema_Fragment_Blog_Home_Webpage(
				$this->title,
				$this->description,
				$publisher->get_publisher_id()
			);
		}

		return $schema;
	}

	private function is_publisher_output_page() {
		$publisher_output_page = $this->utils->get_special_page( 'schema_output_page' );

		// We are on the home page which is the default schema_output_page if another page has not been specified
		return ! $publisher_output_page;
	}
}