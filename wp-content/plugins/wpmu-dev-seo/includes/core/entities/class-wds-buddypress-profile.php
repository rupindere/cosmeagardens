<?php

class Smartcrawl_Buddypress_Profile extends Smartcrawl_Entity {
	/**
	 * @var Smartcrawl_Buddypress_Api
	 */
	private $buddypress_api;
	/**
	 * @var WP_User
	 */
	private $wp_user;
	/**
	 * @var string
	 */
	private $username;
	/**
	 * @var string
	 */
	private $display_name;

	/**
	 * @param WP_User $wp_user
	 */
	public function __construct( $wp_user ) {
		$this->wp_user = $wp_user;
		$this->buddypress_api = new Smartcrawl_Buddypress_Api();
	}

	protected function load_meta_title() {
		return $this->load_option_string_value(
			'bp_profile',
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return '%%bp_user_username%% %%sep%% %%sitename%%';
			}
		);
	}

	protected function load_meta_description() {
		return $this->load_option_string_value(
			'bp_profile',
			array( $this, 'load_meta_desc_from_options' ),
			function () {
				return '%%bp_user_full_name%%';
			}
		);
	}

	protected function load_robots() {
		$noindex = $this->get_noindex_setting( 'bp_profile' ) ? 'noindex' : 'index';
		$nofollow = $this->get_nofollow_setting( 'bp_profile' ) ? 'nofollow' : 'follow';

		return "{$noindex},{$nofollow}";
	}

	protected function load_canonical_url() {
		if ( ! $this->wp_user ) {
			return '';
		}

		return $this->buddypress_api->bp_core_get_user_domain( $this->wp_user->ID );
	}

	protected function load_schema() {
		return array();
	}

	protected function load_opengraph_enabled() {
		return $this->is_opengraph_enabled_for_location( 'bp_profile' );
	}

	protected function load_opengraph_title() {
		return $this->load_option_string_value(
			'bp_profile',
			array( $this, 'load_opengraph_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_opengraph_description() {
		return $this->load_option_string_value(
			'bp_profile',
			array( $this, 'load_opengraph_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_opengraph_images() {
		$images = $this->load_opengraph_images_from_options( 'bp_profile' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	protected function load_twitter_enabled() {
		return $this->is_twitter_enabled_for_location( 'bp_profile' );
	}

	protected function load_twitter_title() {
		return $this->load_option_string_value(
			'bp_profile',
			array( $this, 'load_twitter_title_from_options' ),
			array( $this, 'get_meta_title' )
		);
	}

	protected function load_twitter_description() {
		return $this->load_option_string_value(
			'bp_profile',
			array( $this, 'load_twitter_description_from_options' ),
			array( $this, 'get_meta_description' )
		);
	}

	protected function load_twitter_images() {
		$images = $this->load_twitter_images_from_options( 'bp_profile' );
		if ( $images ) {
			return $this->image_ids_to_urls( $images );
		}

		return array();
	}

	public function get_username() {
		if ( is_null( $this->username ) ) {
			$this->username = $this->load_username();
		}

		return $this->username;
	}

	private function load_username() {
		if ( ! $this->wp_user ) {
			return '';
		}

		return $this->buddypress_api->bp_core_get_username( $this->wp_user->ID );
	}

	public function get_display_name() {
		if ( is_null( $this->display_name ) ) {
			$this->display_name = $this->load_display_name();
		}

		return $this->display_name;
	}

	private function load_display_name() {
		if ( ! $this->wp_user ) {
			return '';
		}

		return $this->buddypress_api->bp_core_get_user_displayname( $this->wp_user->ID );
	}

	public function get_macros( $subject = '' ) {
		return array(
			'%%bp_user_username%%'  => array( $this, 'get_username' ),
			'%%bp_user_full_name%%' => array( $this, 'get_display_name' ),
		);
	}

	public function set_buddypress_api( $api ) {
		$this->buddypress_api = $api;
	}
}