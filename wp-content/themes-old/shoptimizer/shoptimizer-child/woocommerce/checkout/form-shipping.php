    <?php
    /**
     * Checkout shipping information form
     *
     * @author      cosmeagardens
     * @package     WooCommerce/Templates
     * @version     3.6.1
     */
    if (!defined('ABSPATH'))
        exit; // Exit if accessed directly
    global $woocommerce;
    @session_start();



    if(isset($_SESSION['cshiping'])){

        unset($_SESSION['cshiping']);

    }


    ?>
	
    <style type="text/css">

.gtabs {
  position: relative;
}
  .gtab {
    display: none;
    transition: all 0.4s;
}
    .gtab.active {
      display: block;
}
    </style>
    <h3 class="step-title">Who do you want to send the flowers to?</h3>
    <p class="form-row required-symbol">Required <abbr class="required" title="required">*</abbr></p>
    <?php
    if (empty($_POST)) {

                $ship_to_different_address = get_option('woocommerce_ship_to_billing') === 'no' ? 1 : 0;
                $ship_to_different_address = apply_filters('woocommerce_ship_to_different_address_checked', $ship_to_different_address);
            } else {

        $ship_to_different_address = $checkout->get_value('ship_to_different_address');
    }
    ?>
    <div id="ship-to-different-address" style="display: none">
        <input id="ship-to-different-address-checkbox"
               class="input-checkbox" <?php checked($ship_to_different_address, 1); ?> type="checkbox"
               name="ship_to_different_address" value="1" checked />
        <label for="ship-to-different-address-checkbox"
               class="checkbox"><?php _e('Ship to different address?', 'woocommerce'); ?></label>
    </div>
    <!-- Nav tabs -->
    <?php
    if (is_user_logged_in()) { ?>
<div class="recipient-btns">
  <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-1" class="btn btn-info" >Single Recipient</button>
  <button data-toggle="tab" data-tabs=".gtabs.demo" data-tab=".tab-2" class="btn btn-info" >Multiple Recipient</button>
</div>
    <?php } ?>
<div class="gtabs demo" >
  <div class="gtab active tab-1">
   
            <div class="woocommerce-shipping-fields">
                <?php if (WC()->cart->needs_shipping_address() === true) : ?>


                <div class="shipping_address">
                    <?php do_action('woocommerce_checkout_login_form', $checkout); ?>


                    <div class="wrap_shipping1">
                        <div class="clearfix"></div>

                    <?php do_action('woocommerce_before_checkout_shipping_form', $checkout); ?>

                    <?php foreach ($checkout->checkout_fields['shipping'] as $key => $field) : ?>

                        <?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>

                    <?php endforeach; ?>

                    <?php do_action('woocommerce_after_checkout_shipping_form', $checkout); ?>

                    <?php do_action('woocommerce_after_checkout_billing_form', $checkout); ?>

                <?php endif; ?>

                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
  </div>
  
  <div class="gtab tab-2">
    <div class="woocommerce-shipping-fields">
                <div class="shipping_address">

                    <div class="multiple_shipwrap">
                        <?php
                        wc_get_template('checkout/multplerevieworder.php', array('checkout' => WC()->checkout()));

                        ?>
                    </div>
                </div>
            </div>
  </div>
</div>
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $("[data-toggle='tab']").click(function () {
  var tabs = $(this).attr('data-tabs');
  var tab = $(this).attr("data-tab");
  $(tabs).find(".gtab").removeClass("active");
  $(tabs).find(tab).addClass("active");
});
</script>