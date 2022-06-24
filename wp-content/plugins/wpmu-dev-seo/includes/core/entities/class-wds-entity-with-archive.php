<?php

abstract class Smartcrawl_Entity_With_Archive extends Smartcrawl_Entity {
	protected function append_page_number( $url, $page_number ) {
		return smartcrawl_append_archive_page_number( $url, $page_number );
	}

	protected abstract function get_robots_for_page_number( $page_number );

	protected function is_first_page_indexed() {
		$first_page_robots = $this->get_robots_for_page_number( 1 );
		return strpos( $first_page_robots, 'noindex' ) === false;
	}
}