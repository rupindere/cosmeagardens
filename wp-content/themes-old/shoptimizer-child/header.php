<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package shoptimizer
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="height=device-height, width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php
if(is_page('checkout'))
{
	?>
  
<?php } ?>
<?php wp_head(); ?>

<script type="text/javascript">
//var modal = document.getElementById("myModal");

// Get the button that opens the modal
//var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
//var span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal



// When the user clicks anywhere outside of the modal, close it
(function($){
	$(document).ready(function(e) {   
	var galpos = $('.woocommerce-product-gallery').offset().top;
	var woopos = $('.wc-tabs-wrapper').offset().top;
	
$(window).scroll(function() { 

    var scroll = $(window).scrollTop();

     //>=, not <=
    if (scroll >=(galpos-100) && scroll < 920) {
        //clearHeader, not clearheader - caps H
        $(".woocommerce-product-gallery").addClass("fixed");
		$(".woocommerce-product-gallery").removeClass("absolute");
    }
	else{
		if(scroll > 920){
			$(".woocommerce-product-gallery").removeClass("fixed");
			$(".woocommerce-product-gallery").addClass("absolute");
			}
		else{
			$(".woocommerce-product-gallery").removeClass("absolute");
		$(".woocommerce-product-gallery").removeClass("fixed");
		}
		}
});
$('.wcpa_use_sumo').change(function(){
	$('.radio-group .wcpa_radio:nth-child(2)').find('input').trigger('click');
	
});
});
})(jQuery);
</script>
</head>

<body <?php body_class(); ?>>
<div id="myModal" class="modal1">
  <!-- Modal content -->
  <div class="modal-content zip_code_content">
		<div class="modal-header">
			<h2>Please choose the recipient's state to get started...</h2>
	    <span class="close">&times;</span>
		</div>
		<div class="modal-body">
	    <p></p>
	  </div>
  </div>

</div>
<?php if ( function_exists( 'wp_body_open' ) ) {
	wp_body_open();
} ?>

<?php do_action( 'shoptimizer_before' ); ?>

<div id="page" class="hfeed site">

	<?php
	do_action( 'shoptimizer_before_site' );
	do_action( 'shoptimizer_before_header' );
	?>

	<?php do_action( 'shoptimizer_topbar' ); ?>

	<header id="masthead" class="site-header">

		<div class="menu-overlay"></div>

		<div class="main-header col-full">

			<?php
			/**
			 * Functions hooked into shoptimizer_header action
			 *
			 * @hooked shoptimizer_site_branding                    - 20
			 * @hooked shoptimizer_secondary_navigation             - 30
			 * @hooked shoptimizer_product_search                   - 40
			 */
			do_action( 'shoptimizer_header' );
			?>

		</div>


	</header><!-- #masthead -->

	
	<div class="col-full-nav">

	<?php
	/**
	 * Functions hooked into shoptimizer_header action
	 *
	 * @hooked shoptimizer_primary_navigation_wrapper       - 42
	 * @hooked shoptimizer_primary_navigation               - 50
	 * @hooked shoptimizer_header_cart                      - 60
	 * @hooked shoptimizer_primary_navigation_wrapper_close - 68
	 */
	do_action( 'shoptimizer_navigation' );
	?>

	</div>

	<?php
	/**
	 * Functions hooked in to shoptimizer_before_content
	 *
	 * @hooked shoptimizer_header_widget_region - 10
	 */
	do_action( 'shoptimizer_before_content' );
	?>

	<div id="content" class="site-content" tabindex="-1">

		<div class="shoptimizer-archive">

		<div class="archive-header">
			<div class="col-full">
				<?php
				/**
				 * Functions hooked in to shoptimizer_content_top
				 *
				 * @hooked woocommerce_breadcrumb - 10
				 */
				do_action( 'shoptimizer_content_top' );
				?>
			</div>
		</div>

		<div class="col-full">