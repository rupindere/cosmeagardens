<?php

class Smartcrawl_Schema_Fragment_Date_Archive extends Smartcrawl_Schema_Fragment {
	private $year;
	private $month;
	private $posts;
	/**
	 * @var Smartcrawl_Schema_Utils
	 */
	private $utils;
	private $title;
	private $description;

	public function __construct( $year, $month, $posts, $title, $description ) {
		$this->year = $year;
		$this->month = $month;
		$this->posts = $posts;
		$this->title = $title;
		$this->description = $description;
		$this->utils = Smartcrawl_Schema_Utils::get();
	}

	protected function get_raw() {
		$enabled = (bool) $this->utils->get_schema_option( 'schema_enable_date_archives' );
		$requested_year = $this->year;
		$requested_month = $this->month;
		$date_callback = ! empty( $requested_year ) && empty( $requested_month )
			? 'get_year_link'
			: 'get_month_link';
		$date_archive_url = $date_callback( $requested_year, $requested_month );

		if ( $enabled ) {
			return new Smartcrawl_Schema_Fragment_Archive(
				"CollectionPage",
				$date_archive_url,
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
					$this->utils->get_webpage_id( $date_archive_url )
				);
			} else {
				return array();
			}
		}
	}
}