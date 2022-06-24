<?php
/**
 * Single variation cart button
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div class="woocommerce-variation-add-to-cart variations_button">
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<?php
	do_action( 'woocommerce_before_add_to_cart_quantity' );?>
<div class="variation-add-cart-wrap">
	<?php woocommerce_quantity_input(
		array(
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
		)
	);

	do_action( 'woocommerce_after_add_to_cart_quantity' );
	?>

	<button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />

		<div class="product-widget">
			<div id="text-11" class="widget widget_text">
				<div class="textwidget">
					<div class="secure-payment">
						<div class="secure-payment-item">
							<img alt="highest quality control"  src="/wp-content/uploads/2021/11/local_florist.png" alt="">
							Your flowers are prepared in our local store to guarantee the highest quality control. 
						</div>
						<div id="" class="secure-payment-item">
							<img src="/wp-content/uploads/2021/11/secure100.png" alt="">
							CosmeaGardens.com uses the latest encyption technology to secure your purchase.
						</div>
					</div>
				</div>
			</div>
		</div>
</div>
<div>
