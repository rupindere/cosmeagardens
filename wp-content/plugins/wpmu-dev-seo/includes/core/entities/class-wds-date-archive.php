<?php

class Smartcrawl_Date_Archive extends Smartcrawl_Entity_With_Archive {
	private $year;
	private $month;
	private $day;
	private $posts;
	/**
	 * @var int
	 */
	private $page_number;

	public function __construct( $year, $month = '', $day = '', $posts = array(), $page_number = 0 ) {
		$this->year = $year;
		$this->month = $month;
		$this->day = $day;
		$this->posts = $posts;
		$this->page_number = $page_number;
	}

	protected function load_meta_title() {
		return $this->load_option_string_value(
			'date',
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return '%%date%% %%sep%% %%sitename%%';
			}
		);
	}

	protected function load_meta_description() {
		return $this->load_option_string_value(
			'date',
			array( $this, 'load_meta_desc_from_options' ),
			'__return_empty_string'
		);
	}

	protected function load_robots() {
		return $this->get_robots_for_page_number( $this->page_number );
	}

	protected function load_canonical_url() {
		$requested_year = $this->year;
		$requested_month = $this->month;
		$date_callback = ! empty( $requested_year ) && empty( $requested_month )
			? 'get_year_link'
			: 'get_month_link';
		$date_archive_url = $date_callback( $requested_year, $requested_month );

		$first_page_indexed = $this->is_first_page_indexed();
		$current_page_indexed = ! $this->is_noindex();
		if ( $current_page_indexed ) {
			return $this->append_page_number( $date_archive_url, $this->page_number );
		} else {
			if ( $first_page_indexed ) {
				return $date_archive_url;
			} else {
				return '';
			}
		}
	}

	protected function load_schema() {
		$fragment = new Smartcrawl_Schema_Fragment_Date_Archive(
			$this->year,
			$this->month,
			$this->posts,
			$this->get_meta_title(),
			$this->get_meta_description()
		);

		return $fragment->get_schema();
	}

	protected function load_opengraph_enabled() {
		return $this->is_opengraph_enabled_for_location( 'date' );
	}

	protected function load_opengraph_title() {
		return $this->load_option_string_value(
			'date',
			array( $this, 'load_opengraph_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_opengraph_description() {
		return $this->load_option_string_value(
			'date',
			array( $this, 'load_opengraph_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_opengraph_images() {
		$images = $this->load_opengraph_images_from_options( 'date' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	protected function load_twitter_enabled() {
		return $this->is_twitter_enabled_for_location( 'date' );
	}

	protected function load_twitter_title() {
		return $this->load_option_string_value(
			'date',
			array( $this, 'load_twitter_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_twitter_description() {
		return $this->load_option_string_value(
			'date',
			array( $this, 'load_twitter_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_twitter_images() {
		$images = $this->load_twitter_images_from_options( 'date' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	public function get_macros( $subject = '' ) {
		return array(
			'%%date%%' => array( $this, 'get_date_for_archive' ),
		);
	}

	public function get_date_for_archive() {
		$day = $this->day;
		$month = $this->month;
		$year = $this->year;
		$format = '';
		if ( empty( $year ) ) {
			// At the very least we need a year
			return '';
		}
		$timestamp = mktime( 0, 0, 0,
			empty( $month ) ? 1 : $month,
			empty( $day ) ? 1 : $day,
			$year
		);

		if ( ! empty( $day ) ) {
			$format = get_option( 'date_format' );
		} elseif ( ! empty( $month ) ) {
			$format = 'F Y';
		} elseif ( ! empty( $year ) ) {
			$format = 'Y';
		}

		// TODO: should we replace date_i18n with wp_date?
		return date_i18n( $format, $timestamp );
	}

	/**
	 * @param $page_number
	 *
	 * @return string
	 */
	protected function get_robots_for_page_number( $page_number ) {
		$options = Smartcrawl_Settings::get_options();
		if ( empty( $options['enable-date-archive'] ) ) {
			return 'noindex,follow';
		}

		$setting_key = 'date';
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