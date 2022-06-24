<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div id="fields_tab" class="ymapp-settings__content">
	<table class="form-table">
		<tbody>

		<?php if ( $w_id !== 'product' ) { ?>

			<th class="ymapp-settings__title"><?php esc_html_e( 'Post Types', 'smart-woocommerce-search' ); ?></th>

			<?php
			$post_types = get_post_types( array( 'public' => true ), 'object' );

			$search_in = array(
				'post' => 0,
				'page' => 0,
				'product' => 1,
			);

			foreach ( $post_types as $id => $post_type ) {

				if ( isset( $search_in[ $id ] ) ) {
					ysm_setting( $w_id, 'post_type_' . $id, array(
						'type'        => 'checkbox',
						/* translators: %s: Name of a field to search in */
						'title'       => sprintf( __( 'Search in %s', 'smart-woocommerce-search' ), $post_type->label ),
						/* translators: %s: Name of a Post Type */
						'description' => sprintf( __( 'Enable search through "%s" post type', 'smart-woocommerce-search' ), $post_type->labels->singular_name ),
						'value'       => $search_in[ $id ],
					));
				}
			}
			?>

		<?php } ?>

		<?php
		$post_types_exclude = array(
			// public
			'post' => 1,
			'page' => 1,
			'attachment' => 1,
			'product' => 1,
			// not public
			'revision' => 1,
			'nav_menu_item' => 1,
			'custom_css' => 1,
			'customize_changeset' => 1,
			'oembed_cache' => 1,
			'user_request' => 1,
			'acf' => 1,
			'product_variation' => 1,
			'shop_order' => 1,
			'shop_order_refund' => 1,
			'shop_coupon' => 1,
		);
		$cpt = array_diff_key( get_post_types( array( 'public' => true ) ), $post_types_exclude );

		ysm_setting( $w_id, 'custom_post_types', array(
			'type'        => 'select',
			'title'       => __( 'Search in Custom Post Types', 'smart-woocommerce-search' ),
			'description' => __( 'Searching will be provided in selected custom post types', 'smart-woocommerce-search' ),
			'multiple'    => true,
			'choices'     => $cpt,
		));
		?>

		<th class="ymapp-settings__title"><?php esc_html_e( 'Product Variations', 'smart-woocommerce-search' ); ?></th>

		<?php
		ysm_setting( $w_id, 'post_type_product_variation', array(
			'type'        => 'checkbox',
			'title'       => __( 'Search in Variations', 'smart-woocommerce-search' ),
			'description' => __( 'Enable search through Product Variations', 'smart-woocommerce-search' ),
			'value'       => 0,
		));
		?>

		<th class="ymapp-settings__title"><?php esc_html_e( 'Fields', 'smart-woocommerce-search' ); ?></th>

		<?php
		$fields = array(
			'title'       => __( 'Title', 'smart-woocommerce-search' ),
			'content'     => __( 'Content', 'smart-woocommerce-search' ),
			'excerpt'     => __( 'Excerpt', 'smart-woocommerce-search' ),
			'tag'         => __( 'Post Tag', 'smart-woocommerce-search' ),
			'category'    => __( 'Post Category', 'smart-woocommerce-search' ),
			'product_tag' => __( 'Product Tag', 'smart-woocommerce-search' ),
			'product_cat' => __( 'Product Category', 'smart-woocommerce-search' ),
			'product_sku' => __( 'Product SKU', 'smart-woocommerce-search' ),
		);

		if ($w_id === 'product') {
			unset($fields['tag']);
			unset($fields['category']);
		}

		$search_in = array(
			'title' => 1,
			'content' => 1,
			'excerpt' => 1,
		);

		foreach ($fields as $id => $field){

			ysm_setting( $w_id, 'field_' . $id, array(
				'type'        => 'checkbox',
				/* translators: %s: Name of a field to search in */
				'title'       => sprintf( __( 'Search in %s', 'smart-woocommerce-search' ), $field ),
				/* translators: %s: Name of a field (title, description, tags, etc.) */
				'description' => sprintf( __( 'Enable search through "%s"', 'smart-woocommerce-search' ), $field ),
				'value'       => isset( $search_in[ $id ] ) ? $search_in[ $id ] : '',
			));

		}

		/* Custom Tax */
		$tax_list = array();
		$exclude_taxes = array(
			'product_type' => 1,
			'product_visibility' => 1,
			'product_cat' => 1,
			'product_tag' => 1,
			'product_shipping_class' => 1,
		);
		$taxonomies = get_taxonomies( array(
			'_builtin' => false,
		) );

		if ( ! is_wp_error( $taxonomies ) && $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( ! isset( $exclude_taxes[ $taxonomy ] ) ) {
					$tax_list[ $taxonomy ] = $taxonomy;
				}
			}
		}

		ysm_setting( $w_id, 'custom_tax', array(
			'type'        => 'select',
			'title'       => __( 'Search in Custom Taxonomies', 'smart-woocommerce-search' ),
			'description' => __( 'Searching will be provided in selected custom taxonomies', 'smart-woocommerce-search' ),
			'multiple'    => true,
			'choices'     => $tax_list,
		));

		ysm_setting( $w_id, 'custom_fields', array(
			'type'        => 'text',
			'title'       => __( 'Search in Custom Fields', 'smart-woocommerce-search' ),
			'description' => __( 'Custom Fields slug separated by comma. Searching will be provided in custom fields values', 'smart-woocommerce-search' ),
			'value'       => '',
		));

		/* Product categories */
		$product_cats = get_terms( array(
			'taxonomy'   => 'product_cat',
			'orderby'    => 'id',
			'order'      => 'asc',
			'hide_empty' => false,
		) );
		$product_cats_list = array();

		if ( ! is_wp_error( $product_cats ) && is_array( $product_cats ) ) {
			foreach ( $product_cats as $product_cat ) {
				$product_cats_list[ $product_cat->term_id ] = $product_cat->name;
			}
		}

		ysm_setting( $w_id, 'allowed_product_cat', array(
			'type'        => 'select',
			'title'       => __( 'Allowed Product Categories', 'smart-woocommerce-search' ),
			'description' => __( 'Search in chosen product categories', 'smart-woocommerce-search' ),
			'multiple'    => true,
			'choices'     => $product_cats_list,
		));

		ysm_setting( $w_id, 'disallowed_product_cat', array(
			'type'        => 'select',
			'title'       => __( 'Disallowed Product Categories', 'smart-woocommerce-search' ),
			'description' => __( 'Do not search in chosen product categories', 'smart-woocommerce-search' ),
			'multiple'    => true,
			'choices'     => $product_cats_list,
		));

		?>

		</tbody>
	</table>
</div>