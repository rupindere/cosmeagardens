<?php

/**
 * @method WC_Product|null|false wc_get_product( $the_product = false, $deprecated = array() )
 * @method string wc_format_decimal( $number, $dp = false, $trim_zeros = false )
 * @method int wc_get_price_decimals()
 * @method string get_woocommerce_currency( $currency = '' )
 * @method int wc_get_page_id( $page )
 * @method string wc_get_page_permalink( string $page, string|bool $fallback = null )
 * @method boolean is_shop()
 */
class Smartcrawl_Woocommerce_Api {
	public function __call( $name, $arguments ) {
		if ( function_exists( $name ) ) {
			return call_user_func_array( $name, $arguments );
		}

		return null;
	}
}