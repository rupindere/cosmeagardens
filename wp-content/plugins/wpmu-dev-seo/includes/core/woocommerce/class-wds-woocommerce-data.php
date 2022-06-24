<?php

class Smartcrawl_Woocommerce_Data {
	const OPTION_ID = 'wds_woocommerce_options';

	public function __construct() {
	}

	public function get_data() {
		$options = $this->get_options();

		$js_data = array();
		$js_setting_keys = $this->get_js_setting_keys();
		foreach ( $js_setting_keys as $js_setting_key => $sanitizer ) {
			$setting_value = smartcrawl_get_array_value(
				$options,
				smartcrawl_camel_to_snake( $js_setting_key )
			);
			$js_data[ $js_setting_key ] = call_user_func( $sanitizer, $setting_value );
		}

		return array_merge(
			array(
				'brandOptions'     => $this->get_brand_options(),
				'opengraphEnabled' => $this->is_opengraph_enabled(),
				'socialAllowed'    => $this->is_social_allowed(),
				'schemaEnabled'    => $this->is_schema_enabled(),
				'schemaAllowed'    => $this->is_schema_allowed(),
				'socialUrl'        => $this->get_social_url(),
				'schemaUrl'        => Smartcrawl_Settings_Admin::admin_url( Smartcrawl_Settings::TAB_SCHEMA ),
			),
			$js_data
		);
	}

	public function get_options() {
		$options = get_option( self::OPTION_ID );
		return empty( $options ) || ! is_array( $options )
			? array()
			: $options;
	}

	public function save_data( $data ) {
		// Save the rest in the options table
		$setting_keys = $this->get_js_setting_keys();
		$settings = array();
		foreach ( $setting_keys as $setting_key => $sanitizer ) {
			$setting_value = smartcrawl_get_array_value( $data, $setting_key );

			$settings[ smartcrawl_camel_to_snake( $setting_key ) ] = call_user_func( $sanitizer, $setting_value );
		}

		return update_option( self::OPTION_ID, $settings );
	}

	private function get_js_setting_keys() {
		return array(
			'woocommerceEnabled'    => 'boolval',
			'removeGeneratorTag'    => 'boolval',
			'enableOpenGraph'       => 'boolval',
			'addRobots'             => 'boolval',
			'enableShopPageSchema'  => 'boolval',
			'noindexHiddenProducts' => 'boolval',
			'brand'                 => 'sanitize_text_field',
			'globalIdentifier'      => 'sanitize_text_field',
		);
	}

	private function get_brand_options() {
		$options = array(
			'' => esc_html__( 'None', 'wds' ),
		);
		$product_taxonomies = get_object_taxonomies( 'product', 'objects' );
		$excluded = array(
			'product_shipping_class',
			'product_type',
			'product_visibility',
		);
		foreach ( $product_taxonomies as $product_taxonomy ) {
			if ( in_array( $product_taxonomy->name, $excluded ) ) {
				continue;
			}

			$options[ $product_taxonomy->name ] = $product_taxonomy->label;
		}

		return $options;
	}

	private function is_opengraph_enabled() {
		$options = Smartcrawl_Settings::get_options();
		$social_enabled = (bool) smartcrawl_get_array_value( $options, 'social' );
		$og_active = (bool) smartcrawl_get_array_value( $options, 'og-enable' );
		$og_active_for_products = (bool) smartcrawl_get_array_value( $options, 'og-active-product' );

		return $social_enabled && $og_active && $og_active_for_products;
	}

	private function get_social_url() {
		$options = Smartcrawl_Settings::get_options();
		$social_enabled = (bool) smartcrawl_get_array_value( $options, 'social' );
		$og_active = (bool) smartcrawl_get_array_value( $options, 'og-enable' );

		if ( ! $social_enabled || ! $og_active ) {
			return Smartcrawl_Settings_Admin::admin_url( Smartcrawl_Settings::TAB_SOCIAL );
		} else {
			return Smartcrawl_Settings_Admin::admin_url( Smartcrawl_Settings::TAB_ONPAGE ) . '&tab=tab_post_types';
		}
	}

	private function is_schema_enabled() {
		$social = Smartcrawl_Settings::get_component_options( Smartcrawl_Settings::COMP_SOCIAL );
		return empty( $social['disable-schema'] );
	}

	private function is_social_allowed() {
		$options = Smartcrawl_Settings::get_options();
		$social_enabled = (bool) smartcrawl_get_array_value( $options, 'social' );
		$og_active = (bool) smartcrawl_get_array_value( $options, 'og-enable' );

		if ( ! $social_enabled || ! $og_active ) {
			return Smartcrawl_Settings_Admin::is_tab_allowed( Smartcrawl_Settings::TAB_SOCIAL );
		} else {
			return Smartcrawl_Settings_Admin::is_tab_allowed( Smartcrawl_Settings::TAB_ONPAGE );
		}
	}

	private function is_schema_allowed() {
		return Smartcrawl_Settings_Admin::is_tab_allowed( Smartcrawl_Settings::TAB_SCHEMA );
	}
}