<?php

class Smartcrawl_Controller_Woo_Global_Id extends Smartcrawl_Base_Controller {
	const GLOBAL_ID_META_KEY = '_wds_global_id';
	const GLOBAL_ID_VARIATION_NAME = "_wds_global_id_variable";

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

	public function should_run() {
		return smartcrawl_woocommerce_active() &&
		       (bool) smartcrawl_get_array_value( $this->data->get_options(), 'woocommerce_enabled' ) &&
		       ! empty( $this->get_global_identifier() );
	}

	protected function init() {
		add_action( 'woocommerce_product_options_sku', array( $this, 'add_global_id_field' ) );
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_global_id' ) );
		add_filter( 'woocommerce_structured_data_product', array(
			$this,
			'add_global_id_to_woocommerce_schema',
		), 15, 2 );

		/*
		// We don't support variable global IDs yet because we don't have anywhere to put them ATM
		add_action( 'woocommerce_product_after_variable_attributes', array(
			$this,
			'add_variation_global_id_field',
		), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_global_id' ), 10, 2 );
		*/
	}

	public function add_global_id_field() {
		$global_identifier = $this->get_global_identifier();
		$label = $this->get_global_identifier_label( $global_identifier );

		?>
		<div class="options_group">
			<?php
			woocommerce_wp_text_input( array(
				'id'          => self::GLOBAL_ID_META_KEY,
				'label'       => $label,
				'desc_tip'    => true,
				'description' => sprintf( esc_html__( '%s value to use in the SmartCrawl Product schema.', 'wds' ), $label ),
			) );
			?>
		</div>
		<?php
	}

	/**
	 * Save GTIN code.
	 *
	 * @param WC_PRODUCT $product Product Object.
	 */
	public function save_global_id( $product ) {
		if ( ! isset( $_POST[ self::GLOBAL_ID_META_KEY ] ) ) {
			return;
		}

		$product->update_meta_data(
			self::GLOBAL_ID_META_KEY,
			smartcrawl_clean( wp_unslash( $_POST[ self::GLOBAL_ID_META_KEY ] ) )
		);
	}

	public function add_variation_global_id_field( $variation_id, $variation_data, $variation ) {
		$global_identifier = $this->get_global_identifier();
		$label = $this->get_global_identifier_label( $global_identifier );
		$variation_object = wc_get_product( $variation->ID );
		$value = $variation_object->get_meta( self::GLOBAL_ID_META_KEY );

		woocommerce_wp_text_input( array(
			'id'            => self::GLOBAL_ID_VARIATION_NAME . "[{$variation_id}]",
			'name'          => self::GLOBAL_ID_VARIATION_NAME . "[{$variation_id}]",
			'value'         => $value,
			'label'         => $label,
			'desc_tip'      => true,
			'description'   => sprintf( esc_html__( '%s value to use in SmartCrawl Product schema.', 'wds' ), $label ),
			'wrapper_class' => 'form-row',
		) );
	}

	public function save_variation_global_id( $variation_id, $id ) {
		if ( ! isset( $_POST[ self::GLOBAL_ID_VARIATION_NAME ] ) ) {
			return;
		}

		$global_id = $_POST[ self::GLOBAL_ID_VARIATION_NAME ][ $id ];
		$variation = wc_get_product( $variation_id );
		$variation->update_meta_data( self::GLOBAL_ID_META_KEY, smartcrawl_clean( wp_unslash( $global_id ) ) );
		$variation->save_meta_data();
	}

	private function get_global_identifier_label( $global_identifier ) {
		if ( 'isbn' === $global_identifier ) {
			return esc_html__( 'ISBN', 'wds' );
		}

		if ( 'mpn' === $global_identifier ) {
			return esc_html__( 'MPN', 'wds' );
		}

		return esc_html__( 'GTIN', 'wds' );
	}

	private function get_global_identifier() {
		return smartcrawl_get_array_value( $this->data->get_options(), 'global_identifier' );
	}

	/**
	 * @param $schema
	 * @param $product WC_Product
	 *
	 * @return mixed
	 */
	public function add_global_id_to_woocommerce_schema( $schema, $product ) {
		if ( empty( $schema ) ) {
			// We may have removed the schema
			return $schema;
		}

		$global_identifier_key = $this->get_global_identifier();
		if ( ! empty( $schema[ $global_identifier_key ] ) ) {
			// Global identifier already set
			return $schema;
		}

		$global_identifier_value = $product->get_meta( self::GLOBAL_ID_META_KEY );
		if ( $global_identifier_value ) {
			$schema[ $global_identifier_key ] = smartcrawl_clean( $global_identifier_value );
		}

		return $schema;
	}
}