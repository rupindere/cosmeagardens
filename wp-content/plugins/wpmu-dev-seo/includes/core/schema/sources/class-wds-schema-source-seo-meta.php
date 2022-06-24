<?php

class Smartcrawl_Schema_Source_SEO_Meta extends Smartcrawl_Schema_Property_Source {
	const ID = 'seo_meta';

	const TITLE = 'seo_title';
	const DESCRIPTION = 'seo_description';

	private $field;

	public function __construct( $field ) {
		parent::__construct();

		$this->field = $field;
	}

	public function get_value() {
		$resolver = Smartcrawl_Endpoint_Resolver::resolve();
		$entity = $resolver->get_queried_entity();
		if ( ! $entity ) {
			return '';
		}

		if ( $this->field === self::TITLE ) {
			return $entity->get_meta_title();
		} else {
			return $entity->get_meta_description();
		}
	}
}