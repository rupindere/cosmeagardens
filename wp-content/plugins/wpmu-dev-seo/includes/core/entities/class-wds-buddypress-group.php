<?php

class Smartcrawl_Buddypress_Group extends Smartcrawl_Entity {
	/**
	 * @var Smartcrawl_Buddypress_Api
	 */
	private $buddypress_api;
	/**
	 * @var BP_Groups_Group
	 */
	private $buddypress_group;
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $description;

	/**
	 * @param $buddypress_group BP_Groups_Group|object
	 */
	public function __construct( $buddypress_group ) {
		$this->buddypress_api = new Smartcrawl_Buddypress_Api();
		$this->buddypress_group = $buddypress_group;
	}

	protected function load_meta_title() {
		return $this->load_option_string_value(
			'bp_groups',
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return '%%bp_group_name%% %%sep%% %%sitename%%';
			}
		);
	}

	protected function load_meta_description() {
		return $this->load_option_string_value(
			'bp_groups',
			array( $this, 'load_meta_desc_from_options' ),
			function () {
				return '%%bp_group_description%%';
			}
		);
	}

	protected function load_robots() {
		$noindex = $this->get_noindex_setting( 'bp_groups' ) ? 'noindex' : 'index';
		$nofollow = $this->get_nofollow_setting( 'bp_groups' ) ? 'nofollow' : 'follow';

		return "{$noindex},{$nofollow}";
	}

	protected function load_canonical_url() {
		if ( ! $this->buddypress_group ) {
			return '';
		}

		return $this->buddypress_api->bp_get_group_permalink( $this->buddypress_group );
	}

	protected function load_schema() {
		return array();
	}

	protected function load_opengraph_enabled() {
		return $this->is_opengraph_enabled_for_location( 'bp_groups' );
	}

	protected function load_opengraph_title() {
		return $this->load_option_string_value(
			'bp_groups',
			array( $this, 'load_opengraph_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_opengraph_description() {
		return $this->load_option_string_value(
			'bp_groups',
			array( $this, 'load_opengraph_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_opengraph_images() {
		$images = $this->load_opengraph_images_from_options( 'bp_groups' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	protected function load_twitter_enabled() {
		return $this->is_twitter_enabled_for_location( 'bp_groups' );
	}

	protected function load_twitter_title() {
		return $this->load_option_string_value(
			'bp_groups',
			array( $this, 'load_twitter_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_twitter_description() {
		return $this->load_option_string_value(
			'bp_groups',
			array( $this, 'load_twitter_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_twitter_images() {
		$images = $this->load_twitter_images_from_options( 'bp_groups' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	public function get_name() {
		if ( is_null( $this->name ) ) {
			$this->name = $this->load_name();
		}

		return $this->name;
	}

	private function load_name() {
		if ( ! $this->buddypress_group ) {
			return '';
		}

		return $this->buddypress_api->bp_get_group_name( $this->buddypress_group );
	}

	public function get_description() {
		if ( is_null( $this->description ) ) {
			$this->description = $this->load_description();
		}

		return $this->description;
	}

	private function load_description() {
		if ( ! $this->buddypress_group ) {
			return '';
		}

		return $this->buddypress_api->bp_get_group_description( $this->buddypress_group );
	}

	public function get_macros( $subject = '' ) {
		return array(
			'%%bp_group_name%%'        => array( $this, 'get_name' ),
			'%%bp_group_description%%' => array( $this, 'get_description' ),
		);
	}

	public function set_buddypress_api( $api ) {
		$this->buddypress_api = $api;
	}
}