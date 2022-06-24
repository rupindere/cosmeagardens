<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Shoptimizer
 */

?>

		</div><!-- .col-full -->
	</div><!-- #content -->

</div>

	<?php do_action( 'shoptimizer_before_footer' ); ?>

	<?php
	/**
	 * Functions hooked in to shoptimizer_footer action
	 */
	do_action( 'shoptimizer_footer' );
	?>

	<?php do_action( 'shoptimizer_after_footer' ); ?>


</div><!-- #page -->
<?php wp_footer(); ?>
<script>

jQuery(window).on("load", function(){
	if(jQuery(".single-product .product-details-wrapper .wcpa_form_outer .wcpa_row:nth-child(4) input[type='checkbox']").prop("checked") == false) {
		jQuery(".single-product .product-details-wrapper .wcpa_form_outer .wcpa_row:nth-child(7)").addClass('active');
	}
	else if(jQuery(".single-product .product-details-wrapper .wcpa_form_outer .wcpa_row:nth-child(4) input[type='checkbox']").prop("checked") == true) {
		jQuery(".single-product .product-details-wrapper .wcpa_form_outer .wcpa_row:nth-child(7)").removeClass('active');
	}
	
});
</script>
</body>
</html>