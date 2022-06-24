<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
    echo get_the_password_form(); // WPCS: XSS ok.
    return;
}
?>
<!-- <div class="col-full single-product-custom-above">
    <div class="single-product-custom-title">
       <?php echo woocommerce_template_single_title(); ?> 
    </div>
</div> -->
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

    <?php
    /**
     * Hook: woocommerce_before_single_product_summary.
     *woocommerce_output_product_data_tabs
     * @hooked woocommerce_show_product_sale_flash - 10
     * @hooked woocommerce_show_product_images - 20
     */
    do_action( 'woocommerce_before_single_product_summary' );
    ?>
    <?php
        if($product->is_type('variable')){
            foreach($product->get_available_variations() as $variation ){
            //echo "<pre>"; print_r($variation); echo "</pre>";
            $price_arr[$variation['attributes']['attribute_bouquet-size']]=$variation['display_price']; 
            $img_arr[$variation['attributes']['attribute_bouquet-size']]=$variation['image']['thumb_src'];
        }
    ?>
    <script>
        jQuery( document ).ready( function( $ ) {
            $( ".variations_form" ).on( "wc_variation_form woocommerce_update_variation_values", function() {
                $( "label.generatedRadios" ).remove();
                $( ".variationsB" ).remove();
                $( "table.variations select" ).each( function() {
                    var selName = $( this ).attr( "name" );
                    $( "select[name=" + selName + "] option" ).each( function() {
                        var option = $( this );
                        var value = option.attr( "value" );
                        if( value == "" ) { return; }
                        var label = option.html();
                        var select = option.parent();
                        var selected = select.val();
                        var isSelected = ( selected == value ) ? " checked=\"checked\"" : "";
                        var selClass = ( selected == value ) ? " selected" : "";
                        var radHtml = `<input name="${selName}" type="radio" value="${value}" />`;
                        // var img = '<img src ="<?php echo $img1; ?>.">';
                        // var price = '<?php echo $price; ?>';
                        //var pricB = '${label}';
                        if (value=='Classic'){
                            var optionHtml = `<label class="generatedRadios${selClass}"><img src="<?php echo $img_arr['Classic'];?>" width="120">${label}<span class="seperator">|</span><?php echo get_woocommerce_currency_symbol().$price_arr['Classic']; ?></label>`;
                        }
                        if (value=='Deluxe'){
                            var optionHtml = `<label class="generatedRadios${selClass}"><img src="<?php echo $img_arr['Deluxe'];?>" width="120">${label}<span class="seperator">|</span><?php echo get_woocommerce_currency_symbol().$price_arr['Deluxe']; ?></label>`;
                        }
                        if (value=='Premium'){
                            var optionHtml = `<label class="generatedRadios${selClass}"><img src="<?php echo $img_arr['Premium'];?>" width="120">${label}<span class="seperator">|</span><?php echo get_woocommerce_currency_symbol().$price_arr['Premium']; ?></label>`;
                        }
                        select.after( $( optionHtml ).click( function() { 
                            select.val( value ).trigger( "change" );
                        }))}).parent().hide();
                });
                
                $('.generatedRadios').wrapAll( "<div class='variationsB' />");
            });

            $( ".variations_form" ).on( "wc_variation_form", function() {
                $('.generatedRadios:last-child').trigger('click');
            })
        });
		jQuery(window).load( function() {
			jQuery('.variations').fadeIn();
			jQuery('.wcpa_image .wcpa_has_price').removeAttr('disabled');
			});
		
    </script>

    <div class="summary entry-summary">
        <?php
        // do_action( 'woocommerce_single_product_summary' );
        /*Product title */
        //echo woocommerce_template_single_title();
        /* Rating for product */
        // echo woocommerce_template_single_rating();
        /* Single product price */
        // echo woocommerce_template_single_price();
        /* any extra information desc of single product */
        // echo woocommerce_template_single_excerpt();
        /*add to cart and varation data or products addons come for this script*/
        
        echo woocommerce_template_single_add_to_cart();
        /* Single meta info*/
        //echo woocommerce_template_single_meta();
        /*extra information */
        echo woocommerce_template_single_sharing();
        //echo WC_Structured_Data::generate_product_data()();

        ?>

    </div>
    <?php
        /**
        * Hook: woocommerce_after_single_product_summary.
        *
        * @hooked woocommerce_output_product_data_tabs - 10
        * @hooked woocommerce_upsell_display - 15
        * @hooked woocommerce_output_related_products - 20
        */
        do_action( 'woocommerce_after_single_product_summary' );
    ?>
</div>

<?php do_action( 'woocommerce_after_single_product' ); } ?>