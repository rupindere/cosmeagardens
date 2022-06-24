<?php

abstract class Smartcrawl_Schema_Fragment {
	private $schema = null;

	public function get_schema() {
		if ( is_null( $this->schema ) ) {
			$this->schema = $this->make_schema();
		}

		return $this->schema;
	}

	abstract protected function get_raw();

	private function make_schema() {
		return $this->process_schema_item( $this->get_raw() );
	}

	private function process_schema_item( $schema_item ) {
		if ( is_a( $schema_item, self::class ) ) {
			return $schema_item->get_schema();
		} elseif ( is_array( $schema_item ) ) {
			return $this->traverse_schema_array( $schema_item );
		} else {
			return $schema_item;
		}
	}

	private function traverse_schema_array( $schema ) {
		$new_schema = array();
		$keys_numeric = true;

		foreach ( $schema as $schema_item_id => $schema_item ) {
			$keys_numeric = $keys_numeric && is_numeric( $schema_item_id );

			$processed_schema_item = $this->process_schema_item( $schema_item );
			if ( $processed_schema_item !== false ) {
				$new_schema[ $schema_item_id ] = $processed_schema_item;
			}
		}

		return $keys_numeric
			? array_values( $new_schema )
			: $new_schema;
	}
}