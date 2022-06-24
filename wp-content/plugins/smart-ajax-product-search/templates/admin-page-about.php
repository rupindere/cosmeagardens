<?php
if ( !defined('ABSPATH') ) exit;

$p_code = filter_input( INPUT_POST, 'p_code', FILTER_SANITIZE_STRING );
if ( $p_code ) {
	update_option( 'saps_p_code', sanitize_text_field( $p_code ), 'no' );
	if ( $p_code ) {
		ysm_add_message( __( 'Your plugin have been activated.', 'smart-woocommerce-search' ) );
	} else {
		ysm_add_message( __( 'Please activate the plugin.', 'smart-woocommerce-search' ) );
	}
}

$is_patch = filter_input( INPUT_POST, 'apply_patch', FILTER_SANITIZE_STRING );
if ( $is_patch ) {
	$patch_key = filter_input( INPUT_POST, 'patch_key', FILTER_SANITIZE_STRING );
	if ( $patch_key ) {

		if ( '1' === $patch_key ) {
			global $wpdb;
			$res = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' AND meta_value LIKE '%smart_search%'" );

			foreach ( $res as $res_item ) {
				$meta_changed = false;
				$meta = json_decode( $res_item->meta_value, true );
				foreach ( $meta as &$section ) {
					if ( ! empty( $section['elType'] ) && 'section' === $section['elType'] ) {
						foreach ( $section['elements'] as &$column ) {
							if ( ! empty( $column['elType'] ) && 'column' === $column['elType'] ) {
								foreach ( $column['elements'] as &$element ) {
									if ( ! empty( $element['widgetType'] ) && 'smart_search' === $element['widgetType'] ) {
										if ( ! empty( $element['settings']['id'] ) ) {
											$element['settings'] = array(
												'ysm_widget_id' => $element['settings']['id'],
											);
											$meta_changed = true;
										}
									}
								}
							}
						}
					}
				}

				if ( $meta_changed ) {
					// We need the `wp_slash` in order to avoid the unslashing during the `update_metadata`
					$json_value = wp_slash( wp_json_encode( $meta ) );
					update_metadata( 'post', $res_item->post_id, '_elementor_data', $json_value );
				}
			}
		}

		update_option( 'ysm_patch_' . $patch_key, true, 'no' );
		ysm_add_message( sprintf( __( 'Patch %s applied', 'smart-woocommerce-search' ), $patch_key ) );
	}
}
?>

<div class="wrap">

	<h1>
		<span><?php echo esc_html( get_admin_page_title() ); ?></span>
	</h1>

	<?php ysm_message(); ?>

	<h2>Activate Plugin</h2>
	<p>Please enter your purchase code</p>

	<form method="post" action="" enctype="multipart/form-data">
		<input type="text" value="<?php echo esc_attr( get_option( 'saps_p_code' ) ); ?>" name="p_code" class="ymapp-button activate-plugin-input" style="margin-left: 0;">
		<input type="submit" value="Activate" name="activate" class="ymapp-button activate-plugin">
	</form>

	<br>
	<br>
	<br>
	<br>
	<br>
	<h2>Patches</h2>

	<form method="post" action="" enctype="multipart/form-data">
		<span>1. October 11, 2020 - Fix for Elementor widget removing issue</span>
		<?php if ( ! get_option( 'ysm_patch_1' ) ) : ?>
			<input type="hidden" value="1" name="patch_key">
			<input type="submit" value="Apply" name="apply_patch" class="ymapp-button-small">
		<?php else : ?>
			<span style="background: #ccc;padding: 5px;">Applied</span>
		<?php endif; ?>
	</form>
</div>
