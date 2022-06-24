<?php

class Smartcrawl_Controller_Woocommerce extends Smartcrawl_Base_Controller {
	/**
	 * @var Smartcrawl_Controller_Woocommerce
	 */
	private static $_instance;

	private $data;

	public function __construct() {
		parent::__construct();

		$this->data = new Smartcrawl_Woocommerce_Data();
	}

	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function get_options() {
		return $this->data->get_options();
	}

	public function should_run() {
		return smartcrawl_woocommerce_active() &&
		       $this->woo_module_enabled();
	}

	protected function always() {
		add_action( 'wp_ajax_wds_change_woo_status', array( $this, 'change_woo_status' ) );
		add_filter( 'woocommerce_structured_data_product', array( $this, 'remove_woocommerce_product_schema' ), 10, 2 );
		add_action( 'wds_admin_notices', array( $this, 'display_notice' ) );
		add_action(
			'update_option_' . Smartcrawl_Woocommerce_Data::OPTION_ID,
			array( $this, 'maybe_invalidate_sitemap_cache' ),
			10, 2
		);
	}

	protected function init() {
		add_filter( 'get_the_generator_html', array( $this, 'remove_generator_tag' ), - 10, 2 );
		add_filter( 'get_the_generator_xhtml', array( $this, 'remove_generator_tag' ), - 10, 2 );
		add_filter( 'woocommerce_structured_data_product', array( $this, 'add_brand_to_woocommerce_schema' ), 15, 2 );
		add_action( 'wds_robots_txt_content', array( $this, 'add_rules_to_robots_txt' ) );

		Smartcrawl_Controller_Woo_Global_Id::get()->run();

		$this->remove_hidden_products_from_sitemap();
	}

	public function display_notice() {
		if (
			! smartcrawl_woocommerce_active()   // Woo not available
			|| $this->woo_module_enabled()      // Woo SEO already enabled
			|| ! current_user_can( 'manage_options' )
		) {
			return;
		}

		$key = 'try-woocommerce';
		$dismissed_messages = get_user_meta( get_current_user_id(), 'wds_dismissed_messages', true );
		$is_message_dismissed = smartcrawl_get_array_value( $dismissed_messages, $key ) === true;
		if ( $is_message_dismissed ) {
			return;
		}

		?>
		<div class="notice-info notice is-dismissible wds-native-dismissible-notice"
		     data-message-key="<?php echo esc_attr( $key ); ?>">
			<p><strong><?php esc_html_e( 'Improve your WooCommerce SEO', 'wds' ); ?></strong></p>

			<p style="margin-bottom:15px;">
				<?php printf(
					esc_html__( "Hey, %s! It looks like you’re using WooCommerce. Did you know that you can improve your site’s SEO ranking with our WooCommerce SEO settings?", 'wds' ),
					Smartcrawl_Model_User::current()->get_first_name()
				); ?>
			</p>
			<a href="<?php echo esc_attr( Smartcrawl_Settings_Admin::admin_url( Smartcrawl_Settings_Admin::TAB_AUTOLINKS ) . '&tab=tab_woo' ); ?>"
			   class="button button-primary">
				<?php esc_html_e( 'Activate WooCommerce SEO', 'wds' ); ?>
			</a>
			<a href="#"
			   class="wds-native-dismiss"
			   style="font-weight: 400;color: #2271b1;">
				<?php esc_html_e( 'Not now', 'wds' ); ?>
			</a>
			<p></p>
		</div>
		<?php
	}

	public function remove_hidden_products_from_sitemap() {
		$noindex_hidden_products = smartcrawl_get_array_value( $this->get_options(), 'noindex_hidden_products' );
		if ( $noindex_hidden_products ) {
			add_filter( 'wds_sitemap_ignored_product_ids', array( $this, 'ignore_hidden_products' ) );
			add_filter( 'wds_news_sitemap_ignored_product_ids', array( $this, 'ignore_hidden_products' ) );
		}
	}

	public function ignore_hidden_products( $ignored_ids ) {
		$product_visibility_terms = wc_get_product_visibility_term_ids();
		$product_ids = get_posts( array(
			'post_type'   => 'product',
			'fields'      => 'ids',
			'numberposts' => - 1,
			'tax_query'   => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array( $product_visibility_terms['exclude-from-catalog'] ),
				),
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => array( $product_visibility_terms['exclude-from-search'] ),
				),
			),
		) );
		$product_ids = ! empty( $product_ids ) && is_array( $product_ids )
			? $product_ids
			: array();
		$ignored_ids = ! empty( $ignored_ids ) && is_array( $ignored_ids )
			? $ignored_ids
			: array();

		return array_merge( $ignored_ids, $product_ids );
	}

	public function remove_generator_tag( $generator ) {
		$should_remove = (bool) smartcrawl_get_array_value( $this->get_options(), 'remove_generator_tag' );
		if ( $should_remove ) {
			remove_filter( 'get_the_generator_html', 'wc_generator_tag' );
			remove_filter( 'get_the_generator_xhtml', 'wc_generator_tag' );
		}

		return $generator;
	}

	public function change_woo_status() {
		$data = $this->get_request_data();
		if ( ! isset( $data['enable'] ) ) {
			wp_send_json_error();
			return;
		}

		$options = get_option( Smartcrawl_Woocommerce_Data::OPTION_ID );
		$options['woocommerce_enabled'] = ! empty( $data['enable'] );
		update_option( Smartcrawl_Woocommerce_Data::OPTION_ID, $options );

		wp_send_json_success();
	}

	private function get_request_data() {
		return isset( $_POST['_wds_nonce'] ) && wp_verify_nonce( $_POST['_wds_nonce'], 'wds-woo-nonce' )
			? stripslashes_deep( $_POST )
			: array();
	}

	/**
	 * @param $markup array Schema
	 * @param $product WC_Product
	 *
	 * @return array
	 */
	public function remove_woocommerce_product_schema( $markup, $product ) {
		if ( $this->is_schema_disabled() ) {
			return $markup;
		}

		$schema_utils = Smartcrawl_Schema_Utils::get();
		$product_post = get_post( $product->get_id() );
		$schema_types = $schema_utils->get_custom_schema_types( $product_post );
		foreach ( $schema_types as $type => $schema ) {
			if ( $type === 'Product' ) {
				return array();
			}
		}

		return $markup;
	}

	/**
	 * @param $schema
	 * @param $product WC_PRODUCT
	 *
	 * @return mixed
	 */
	public function add_brand_to_woocommerce_schema( $schema, $product ) {
		$brand = $this->get_brand( $product );
		if ( empty( $schema ) || empty( $brand ) ) {
			// We may have already removed the schema or there's no brand available
			return $schema;
		}

		$schema['brand'] = array(
			'@type' => 'Brand',
			'name'  => $brand->name,
			'url'   => get_term_link( $brand ),
		);

		return $schema;
	}

	private function is_schema_disabled() {
		$social = Smartcrawl_Settings::get_component_options( Smartcrawl_Settings::COMP_SOCIAL );
		return ! empty( $social['disable-schema'] )
		       || ! Smartcrawl_Settings_Admin::is_tab_allowed( Smartcrawl_Settings::TAB_SCHEMA );
	}

	/**
	 * The following function belongs inside a Smartcrawl_Product class
	 *
	 * @param $product WC_Product
	 *
	 * @return WP_Term|bool
	 */
	public function get_brand( $product ) {
		$brand = smartcrawl_get_array_value( $this->get_options(), 'brand' );
		if ( empty( $brand ) ) {
			return false;
		}

		$brands = get_the_terms( $product->get_id(), $brand );
		return is_wp_error( $brands ) || empty( $brands[0] )
			? false
			: $brands[0];
	}

	/**
	 * @return bool
	 */
	private function woo_module_enabled() {
		return (bool) smartcrawl_get_array_value( $this->get_options(), 'woocommerce_enabled' );
	}

	public function add_rules_to_robots_txt( $contents ) {
		$enabled = smartcrawl_get_array_value( $this->get_options(), 'add_robots' );
		if ( ! $enabled ) {
			return $contents;
		}

		$parts = array(
			"Disallow: /*add-to-cart=*",
		);

		foreach ( array( 'cart', 'checkout', 'myaccount' ) as $page ) {
			$page_id = wc_get_page_id( $page );
			if ( $page_id > 0 ) {
				$page_permalink = wc_get_page_permalink( $page );
				$page_permalink_part = str_replace( home_url( '/' ), '/', $page_permalink );
				$parts[] = "Disallow: $page_permalink_part";
			}
		}

		if ( $parts ) {
			$contents .= "\n\n" . join( "\n", $parts );
		}

		return $contents;
	}

	public function maybe_invalidate_sitemap_cache( $old_option, $new_option ) {
		$old_woo_status = smartcrawl_get_array_value( $old_option, 'woocommerce_enabled' );
		$old_noindex_value = smartcrawl_get_array_value( $old_option, 'noindex_hidden_products' );
		$old_noindex_status = $old_woo_status && $old_noindex_value;

		$new_woo_status = smartcrawl_get_array_value( $new_option, 'woocommerce_enabled' );
		$new_noindex_value = smartcrawl_get_array_value( $new_option, 'noindex_hidden_products' );
		$new_noindex_status = $new_woo_status && $new_noindex_value;

		if ( $old_noindex_status != $new_noindex_status ) {
			Smartcrawl_Sitemap_Cache::get()->invalidate();
		}
	}
}