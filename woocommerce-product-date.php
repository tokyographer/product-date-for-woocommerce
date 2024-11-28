<?php
/*
Plugin Name: WooCommerce Product Date
Plugin URI: https://github.com/tokyographer
Description: Adds a custom "Retreat Start Date" field to WooCommerce products, Quick Edit, cart, checkout, orders, emails, and the WooCommerce REST API.
Version: 1.0
Author: https://github.com/tokyographer
Text Domain: woocommerce-product-date
Domain Path: /languages
License: GPL2
*/

/**
 * WooCommerce Product Date Plugin
 *
 * Adds a custom "Retreat Start Date" field to WooCommerce products.
 *
 * @package WooCommerce_Product_Date
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin textdomain for translations.
 */
function wcpd_load_textdomain() {
	load_plugin_textdomain( 'woocommerce-product-date', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'wcpd_load_textdomain' );

/**
 * Add the Retreat Start Date field to the product edit page.
 */
function wcpd_add_retreat_start_date_field() {
	echo '<div class="options_group">';
	woocommerce_wp_text_input( array(
		'id'          => '_retreat_start_date',
		'label'       => __( 'Retreat Start Date', 'woocommerce-product-date' ),
		'placeholder' => 'DD-MM-YYYY',
		'type'        => 'date',
		'description' => __( 'Enter the retreat start date for this product.', 'woocommerce-product-date' ),
		'desc_tip'    => true,
	) );
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'wcpd_add_retreat_start_date_field' );

/**
 * Save the Retreat Start Date field value.
 *
 * @param int $post_id Product ID.
 */
function wcpd_save_retreat_start_date_field( $post_id ) {
	$retreat_start_date = isset( $_POST['_retreat_start_date'] ) ? sanitize_text_field( $_POST['_retreat_start_date'] ) : '';
	update_post_meta( $post_id, '_retreat_start_date', $retreat_start_date );
}
add_action( 'woocommerce_process_product_meta', 'wcpd_save_retreat_start_date_field' );

/**
 * Add Retreat Start Date column to the admin product list table.
 *
 * @param array $columns Existing columns.
 * @return array Modified columns.
 */
function wcpd_add_retreat_start_date_column( $columns ) {
	$new_columns = array();

	foreach ( $columns as $key => $value ) {
		$new_columns[ $key ] = $value;
		if ( 'sku' === $key ) { // Add after SKU column.
			$new_columns['retreat_start_date'] = __( 'Retreat Start Date', 'woocommerce-product-date' );
		}
	}

	return $new_columns;
}
add_filter( 'manage_edit-product_columns', 'wcpd_add_retreat_start_date_column' );

/**
 * Populate the Retreat Start Date column.
 *
 * @param string $column Column name.
 * @param int    $post_id Product ID.
 */
function wcpd_show_retreat_start_date_column_content( $column, $post_id ) {
	if ( 'retreat_start_date' === $column ) {
		$retreat_start_date = get_post_meta( $post_id, '_retreat_start_date', true );
		echo $retreat_start_date ? esc_html( $retreat_start_date ) : __( 'N/A', 'woocommerce-product-date' );
	}
}
add_action( 'manage_product_posts_custom_column', 'wcpd_show_retreat_start_date_column_content', 10, 2 );

/**
 * Extend Quick Edit for Retreat Start Date.
 *
 * @param string $column_name Column name.
 * @param string $post_type Post type.
 */
function wcpd_quick_edit_custom_box( $column_name, $post_type ) {
	if ( 'retreat_start_date' !== $column_name || 'product' !== $post_type ) {
		return;
	}
	?>
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-col">
			<label>
				<span class="title"><?php esc_html_e( 'Retreat Start Date', 'woocommerce-product-date' ); ?></span>
				<span class="input-text-wrap">
					<input type="date" name="_retreat_start_date" class="retreat_start_date" value="">
				</span>
			</label>
		</div>
	</fieldset>
	<?php
}
add_action( 'quick_edit_custom_box', 'wcpd_quick_edit_custom_box', 10, 2 );

/**
 * Save Quick Edit data.
 *
 * @param int $post_id Post ID.
 */
function wcpd_save_quick_edit_data( $post_id ) {
	if ( ! isset( $_POST['_retreat_start_date'] ) ) {
		return;
	}

	$retreat_start_date = sanitize_text_field( $_POST['_retreat_start_date'] );
	update_post_meta( $post_id, '_retreat_start_date', $retreat_start_date );
}
add_action( 'save_post', 'wcpd_save_quick_edit_data' );

/**
 * Enqueue admin scripts for Quick Edit.
 *
 * @param string $hook Current admin page.
 */
function wcpd_admin_scripts( $hook ) {
	if ( 'edit.php' !== $hook || 'product' !== get_post_type() ) {
		return;
	}

	wp_enqueue_script(
		'wcpd_quick_edit',
		plugins_url( 'js/wcpd_quick_edit.js', __FILE__ ),
		array( 'jquery', 'inline-edit-post' ),
		'1.0',
		true
	);
}
add_action( 'admin_enqueue_scripts', 'wcpd_admin_scripts' );

/**
 * Add data to Quick Edit via JavaScript.
 */
function wcpd_print_quick_edit_scripts() {
	if ( 'product' !== get_post_type() ) {
		return;
	}
	?>
	<script type="text/javascript">
	jQuery(function($) {
		var wp_inline_edit = inlineEditPost.edit;
		inlineEditPost.edit = function(post_id) {
			wp_inline_edit.apply(this, arguments);

			var id = 0;
			if (typeof(post_id) === 'object') {
				id = parseInt(this.getId(post_id));
			}

			if (id > 0) {
				var $post_row = $('#post-' + id);
				var $quick_edit_row = $('#edit-' + id);
				var retreat_start_date = $post_row.find('.column-retreat_start_date').text().trim();

				// Handle 'N/A' value.
				if ('N/A' === retreat_start_date) {
					retreat_start_date = '';
				}

				$quick_edit_row.find('input[name="_retreat_start_date"]').val(retreat_start_date);
			}
		};
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'wcpd_print_quick_edit_scripts' );

/**
 * Add Retreat Start Date to cart item data.
 *
 * @param array $cart_item_data Cart item data.
 * @param int   $product_id Product ID.
 * @return array Modified cart item data.
 */
function wcpd_add_retreat_start_date_to_cart_item( $cart_item_data, $product_id ) {
	$retreat_start_date = get_post_meta( $product_id, '_retreat_start_date', true );
	if ( ! empty( $retreat_start_date ) ) {
		$cart_item_data['retreat_start_date'] = $retreat_start_date;
	}
	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'wcpd_add_retreat_start_date_to_cart_item', 10, 2 );

/**
 * Display Retreat Start Date in cart and checkout.
 *
 * @param array $item_data Item data.
 * @param array $cart_item Cart item.
 * @return array Modified item data.
 */
function wcpd_display_retreat_start_date_cart_checkout( $item_data, $cart_item ) {
	if ( isset( $cart_item['retreat_start_date'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Retreat Start Date', 'woocommerce-product-date' ),
			'value' => wc_clean( $cart_item['retreat_start_date'] ),
		);
	}
	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'wcpd_display_retreat_start_date_cart_checkout', 10, 2 );

/**
 * Save Retreat Start Date to order item meta.
 *
 * @param WC_Order_Item_Product $item Order item.
 * @param string                $cart_item_key Cart item key.
 * @param array                 $values Cart item values.
 * @param WC_Order              $order Order object.
 */
function wcpd_add_retreat_start_date_to_order_items( $item, $cart_item_key, $values, $order ) {
	if ( isset( $values['retreat_start_date'] ) ) {
		$item->add_meta_data( __( 'Retreat Start Date', 'woocommerce-product-date' ), $values['retreat_start_date'], true );
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wcpd_add_retreat_start_date_to_order_items', 10, 4 );

/**
 * Add Retreat Start Date to order emails.
 *
 * @param array    $fields Fields to display in emails.
 * @param bool     $sent_to_admin Whether the email is sent to admin.
 * @param WC_Order $order Order object.
 * @return array Modified fields.
 */
function wcpd_display_retreat_start_date_in_emails( $fields, $sent_to_admin, $order ) {
	foreach ( $order->get_items() as $item_id => $item ) {
		if ( $item->get_meta( 'Retreat Start Date' ) ) {
			$fields['retreat_start_date'] = array(
				'label' => __( 'Retreat Start Date', 'woocommerce-product-date' ),
				'value' => $item->get_meta( 'Retreat Start Date' ),
			);
		}
	}
	return $fields;
}
add_filter( 'woocommerce_email_order_meta_fields', 'wcpd_display_retreat_start_date_in_emails', 10, 3 );

/**
 * Add Retreat Start Date to WooCommerce REST API.
 */
function wcpd_register_retreat_start_date_rest_field() {
	register_rest_field( 'product', 'retreat_start_date', array(
		'get_callback'    => 'wcpd_get_retreat_start_date',
		'update_callback' => 'wcpd_update_retreat_start_date',
		'schema'          => array(
			'description' => __( 'Retreat Start Date', 'woocommerce-product-date' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
		),
	) );
}
add_action( 'rest_api_init', 'wcpd_register_retreat_start_date_rest_field' );

/**
 * Get Retreat Start Date for REST API.
 *
 * @param array  $object Object data.
 * @param string $field_name Field name.
 * @param WP_REST_Request $request Request object.
 * @return string Retreat Start Date.
 */
function wcpd_get_retreat_start_date( $object, $field_name, $request ) {
	return get_post_meta( $object['id'], '_retreat_start_date', true );
}

/**
 * Update Retreat Start Date via REST API.
 *
 * @param string          $value Value to update.
 * @param WP_Post         $object Post object.
 * @param string          $field_name Field name.
 */
function wcpd_update_retreat_start_date( $value, $object, $field_name ) {
	if ( ! is_string( $value ) ) {
		return;
	}
	update_post_meta( $object->ID, '_retreat_start_date', sanitize_text_field( $value ) );
}

/**
 * Basic error handling and logging.
 *
 * @param string $message Error message.
 */
function wcpd_log_error( $message ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'WooCommerce Product Date: ' . $message );
	}
}