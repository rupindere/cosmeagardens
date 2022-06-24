<?php $option_name = empty( $_view['option_name'] ) ? '' : $_view['option_name']; ?>

<input type="hidden" value="1" name="<?php echo esc_attr( $option_name ); ?>[save_woo]"/>

<div class="wds-vertical-tab-section sui-box tab_woo <?php echo $is_active ? '' : 'hidden'; ?>"
     id="tab_woo">
	<div id="wds-woo-settings-tab">
		<div class="sui-box">
			<div class="sui-box-header">
				<h2 class="sui-box-title"><?php esc_html_e( 'WooCommerce SEO', 'wds' ); ?></h2>
			</div>

			<div class="sui-box-body">
				<p>
					<?php esc_html_e( 'Use the WooCommerce SEO configurations below to add recommended Woo metadata and Product Schema to your WooCommerce site, helping you stand out in search results pages.', 'wds' ); ?>
				</p>
			</div>
		</div>
	</div>
</div>