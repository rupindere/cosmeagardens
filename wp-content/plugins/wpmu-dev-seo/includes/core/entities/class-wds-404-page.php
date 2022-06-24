<?php

class Smartcrawl_404_Page extends Smartcrawl_Entity {
	protected function load_meta_title() {
		return $this->load_option_string_value(
			'404',
			array( $this, 'load_meta_title_from_options' ),
			function () {
				return 'Page not found %%sep%% %%sitename%%';
			}
		);
	}

	protected function load_meta_description() {
		return $this->load_option_string_value(
			'404',
			array( $this, 'load_meta_desc_from_options' ),
			'__return_empty_string'
		);
	}

	protected function load_robots() {
		return '';
	}

	protected function load_canonical_url() {
		return '';
	}

	protected function load_schema() {
		return array();
	}

	protected function load_opengraph_enabled() {
		return false;
	}

	protected function load_opengraph_title() {
		return '';
	}

	protected function load_opengraph_description() {
		return '';
	}

	protected function load_opengraph_images() {
		return array();
	}

	protected function load_twitter_enabled() {
		return false;
	}

	protected function load_twitter_title() {
		return '';
	}

	protected function load_twitter_description() {
		return '';
	}

	protected function load_twitter_images() {
		return array();
	}

	public function get_macros( $subject = '' ) {
		return array();
	}
}