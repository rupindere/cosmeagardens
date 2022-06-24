<table class="shop_table">
    <?php
    do_action('woocommerce_review_order_before_cart_contents');

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $delivery_date = $cart_item['custom_option']['delivery_date'];
        $abc = $delivery_date;
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {

            if (isset($cart_item['custom_option']) && $cart_item['custom_option']['main_product_id'] != $product_id) {
                $no_border = 'border-top: 0px !important; border-bottom: 0px !important;';
                $prodtype = 'gift-pro';
                $prodtype1 = '<p>ADD ON GIFT</p>';
            } else {
                $prodtype = "";
                $prodtypeclass="mainItem";
                $prodtype1 = "<span style='text-align:center;display:block;font-weight:600; color:#A11193'></span>";
                $no_border = 'border-bottom: 0px !important;';
            }

            ?>
            <tr id="<?php echo $cart_item['custom_option']['main_product_id'] ?>" class="mainItem <?php echo esc_attr
            (apply_filters('woocommerce_cart_item_class', 'cart_item',
                $cart_item,
                $cart_item_key)); ?>">


                <td class="<?php echo $prodtype; ?> product-thumbnail product-name" style="<?php echo $no_border; ?>">
                    <!--<div class="inline-setting" style="width:16%!important;">-->
                        <?php
                        // $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                        // if (!$_product->is_visible())
                        //     echo $thumbnail;
                        // else
                        //     printf('<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail);
                        ?>
                    <!--</div>-->
                    <div class="inline-setting" style="position: relative;width:100%!important;">
                        <div class="multi-product-wrap">
                            <?php echo apply_filters('woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key) . ' ' . apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                            <?php echo $prodtype1; ?>
                            <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times; %s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); ?>
                        </div>
                    </div>
                    
                    <?php echo WC()->cart->get_item_data($cart_item); ?>
                </td>
                <td class="product-total" style="<?php echo $no_border; ?>">
                   
                </td>
            </tr>
            <?php
        }
    }
    do_action('woocommerce_review_order_after_cart_contents');
    ?>
</table>