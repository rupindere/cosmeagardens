<?php

/**
 * @method array|WP_Error wpml_get_language_information( $empty_value = null, int $post_id = null )
 * @method string get_current_language()
 * @method array get_active_languages( bool $refresh = false, bool $major_first = false, string $order_by = 'english_name' )
 * @method string get_default_language()
 * @method bool|string convert_url( string $url, string $code = null )
 * @method mixed get_setting( string $key, mixed $default = false )
 */
class Smartcrawl_Wpml_Api {
	public function __call( $name, $arguments ) {
		if ( function_exists( $name ) ) {
			return call_user_func_array( $name, $arguments );
		}

		global $sitepress;
		if ( empty( $sitepress ) ) {
			return null;
		}

		if ( method_exists( $sitepress, $name ) ) {
			return call_user_func_array(
				array( $sitepress, $name ),
				$arguments
			);
		}

		return null;
	}
}