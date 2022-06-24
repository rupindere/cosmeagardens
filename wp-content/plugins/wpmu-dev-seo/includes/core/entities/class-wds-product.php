<?php

class Smartcrawl_Product extends Smartcrawl_Entity {
	/**
	 * @var Smartcrawl_Post
	 */
	private $post;
	/**
	 * @var WC_Product
	 */
	private $woo_product;
	/**
	 * @var string
	 */
	private $brand;
	/**
	 * @var Smartcrawl_Woocommerce_Data
	 */
	private $data;
	/**
	 * @var Smartcrawl_Woocommerce_Api
	 */
	private $woo_api;

	public function __construct( $wp_post, $page_number = 0, $comments_page = 0 ) {
		$this->post = new Smartcrawl_Post( $wp_post, $page_number, $comments_page );
		$this->data = new Smartcrawl_Woocommerce_Data();
		$this->woo_api = new Smartcrawl_Woocommerce_Api();
	}

	private function get_woo_product() {
		$wp_post = $this->post->get_wp_post();
		if (
			! $wp_post
			|| ! smartcrawl_woocommerce_active()
			|| ! $this->is_woo_module_enabled()
		) {
			return false;
		}

		if ( is_null( $this->woo_product ) ) {
			$this->woo_product = $this->woo_api->wc_get_product( $wp_post );
		}
		return $this->woo_product
			? $this->woo_product
			: false;
	}

	protected function load_meta_title() {
		return $this->post->get_meta_title();
	}

	protected function load_meta_description() {
		return $this->post->get_meta_description();
	}

	protected function load_robots() {
		$woo_product = $this->get_woo_product();
		$noindex_hidden_products = smartcrawl_get_array_value( $this->get_options(), 'noindex_hidden_products' );
		if (
			$woo_product
			&& $woo_product->get_catalog_visibility() === 'hidden'
			&& $noindex_hidden_products
		) {
			return 'noindex,nofollow';
		}

		return $this->post->get_robots();
	}

	protected function load_canonical_url() {
		return $this->post->get_canonical_url();
	}

	protected function load_schema() {
		// Notice that we are not checking Woo module status here because schema is not dependent on that
		$wp_post = $this->post->get_wp_post();
		if ( ! $wp_post ) {
			return array();
		}

		$fragment = new Smartcrawl_Schema_Fragment_Singular( $this->post, false );
		return $fragment->get_schema();
	}

	protected function load_opengraph_enabled() {
		return $this->post->is_opengraph_enabled();
	}

	protected function load_opengraph_title() {
		return $this->post->get_opengraph_title();
	}

	protected function load_opengraph_description() {
		return $this->post->get_opengraph_description();
	}

	protected function load_opengraph_images() {
		return $this->post->get_opengraph_images();
	}

	protected function load_twitter_enabled() {
		return $this->post->is_twitter_enabled();
	}

	protected function load_twitter_title() {
		return $this->post->get_twitter_title();
	}

	protected function load_twitter_description() {
		return $this->post->get_twitter_description();
	}

	protected function load_twitter_images() {
		return $this->post->get_twitter_images();
	}

	private function get_options() {
		return $this->data->get_options();
	}

	/**
	 * @return bool|WP_Term
	 */
	public function get_brand() {
		if ( is_null( $this->brand ) ) {
			$this->brand = $this->load_brand();
		}
		return $this->brand;
	}

	private function load_brand() {
		$woo_product = $this->get_woo_product();
		if ( ! $woo_product ) {
			return false;
		}

		$brand = smartcrawl_get_array_value( $this->get_options(), 'brand' );
		if ( empty( $brand ) ) {
			return false;
		}

		$brands = get_the_terms( $woo_product->get_id(), $brand );
		return is_wp_error( $brands ) || empty( $brands[0] )
			? false
			: $brands[0];
	}

	public function load_opengraph_tags() {
		$tags = array();

		$woo_product = $this->get_woo_product();
		$woo_og_enabled = (bool) smartcrawl_get_array_value( $this->get_options(), 'enable_open_graph' );
		if ( $woo_product && $woo_og_enabled ) {
			$tags = parent::load_opengraph_tags();
			$tags['og:type'] = 'og:product';

			$price = $this->get_opengraph_product_price();
			if ( $price ) {
				$tags['product:price:amount'] = $price;
				$tags['product:price:currency'] = $this->woo_api->get_woocommerce_currency();
			}

			$tags = $this->add_opengraph_availability( $tags );

			$brand = $this->get_brand();
			if ( $brand ) {
				$tags['product:brand'] = $brand->name;
			}
		}

		return $tags;
	}

	private function get_opengraph_product_price() {
		$woo_product = $this->get_woo_product();
		if ( ! $woo_product ) {
			return '';
		}

		$price = $woo_product->get_price();
		if ( $price === '' ) {
			return '';
		}

		if ( $woo_product->is_type( 'variable' ) ) {
			$lowest = $woo_product->get_variation_price( 'min', false );
			$highest = $woo_product->get_variation_price( 'max', false );

			return $lowest === $highest
				? $this->woo_api->wc_format_decimal( $lowest, $this->woo_api->wc_get_price_decimals() )
				: '';
		} else {
			return $this->woo_api->wc_format_decimal( $price, $this->woo_api->wc_get_price_decimals() );
		}
	}

	/**
	 * @param array $tags
	 *
	 * @return array
	 */
	private function add_opengraph_availability( $tags ) {
		$woo_product = $this->get_woo_product();
		if ( ! $woo_product ) {
			return $tags;
		}

		$product_availability = $og_availability = false;
		$stock_status = $woo_product->get_stock_status();
		if ( $stock_status === 'onbackorder' ) {
			$product_availability = 'available for order';
			$og_availability = 'backorder';
		} elseif ( $stock_status === 'instock' ) {
			$product_availability = $og_availability = 'instock';
		} elseif ( $stock_status === 'outofstock' ) {
			$product_availability = $og_availability = 'out of stock';
		}

		if ( $og_availability ) {
			$tags['og:availability'] = $og_availability;
		}
		if ( $product_availability ) {
			$tags['product:availability'] = $product_availability;
		}
		return $tags;
	}

	public function get_macros( $subject = '' ) {
		return $this->post->get_macros( $subject );
	}

	private function is_woo_module_enabled() {
		return (bool) smartcrawl_get_array_value( $this->get_options(), 'woocommerce_enabled' );
	}

	/**
	 * @return Smartcrawl_Woocommerce_Api
	 */
	public function get_woo_api() {
		return $this->woo_api;
	}

	/**
	 * @param Smartcrawl_Woocommerce_Api $woo_api
	 */
	public function set_woo_api( $woo_api ) {
		$this->woo_api = $woo_api;
	}
}