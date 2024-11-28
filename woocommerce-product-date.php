<?php
/*
Plugin Name: WooCommerce Product Date
Plugin URI: https://github.com/tokyographer
Description: Adds a custom "Retreat Start Date" field to WooCommerce products, Quick Edit, cart, checkout, orders, emails, and the WooCommerce REST API.
Version: 1.3
Author: https://github.com/tokyographer
Text Domain: woocommerce-product-date
Domain Path: /languages
License: GPL2
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
		'type'        => 'text',
		'description' => __( 'Enter the retreat start date in DD-MM-YYYY format.', 'woocommerce-product-date' ),
		'desc_tip'    => true,
		'custom_attributes' => array(
			'pattern' => '\d{2}-\d{2}-\d{4}',
		),
	) );
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'wcpd_add_retreat_start_date_field' );

/**
 * Save the Retreat Start Date field value.
 */
function wcpd_save_retreat_start_date_field( $post_id ) {
	$retreat_start_date = isset( $_POST['_retreat_start_date'] ) ? sanitize_text_field( $_POST['_retreat_start_date'] ) : '';
	if ( preg_match( '/\d{2}-\d{2}-\d{4}/', $retreat_start_date ) ) {
		update_post_meta( $post_id, '_retreat_start_date', $retreat_start_date );
	} else {
		delete_post_meta( $post_id, '_retreat_start_date' );
	}
}
add_action( 'woocommerce_process_product_meta', 'wcpd_save_retreat_start_date_field' );

/**
 * Add Retreat Start Date to cart item data.
 */
function wcpd_add_retreat_start_date_to_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
	$retreat_start_date = get_post_meta( $product_id, '_retreat_start_date', true );

	if ( ! empty( $retreat_start_date ) ) {
		$cart_item_data['retreat_start_date'] = $retreat_start_date;
		$cart_item_data['unique_key'] = md5( microtime() . rand() ); // Prevent merging items
	}

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'wcpd_add_retreat_start_date_to_cart_item_data', 10, 3 );

/**
 * Display Retreat Start Date in cart and checkout.
 */
function wcpd_display_retreat_start_date_in_cart( $item_data, $cart_item ) {
	if ( isset( $cart_item['retreat_start_date'] ) ) {
		$item_data[] = array(
			'key'   => __( 'Retreat Start Date', 'woocommerce-product-date' ),
			'value' => wc_clean( $cart_item['retreat_start_date'] ),
		);
	}

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'wcpd_display_retreat_start_date_in_cart', 10, 2 );

/**
 * Save Retreat Start Date to order item meta.
 */
function wcpd_add_retreat_start_date_to_order_meta( $item, $cart_item_key, $values, $order ) {
	if ( isset( $values['retreat_start_date'] ) ) {
		$item->add_meta_data( __( 'Retreat Start Date', 'woocommerce-product-date' ), $values['retreat_start_date'], true );
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wcpd_add_retreat_start_date_to_order_meta', 10, 4 );

/**
 * Display Retreat Start Date in order details (frontend and admin).
 */
function wcpd_display_retreat_start_date_in_order( $item_id, $item, $order, $plain_text ) {
	$retreat_start_date = $item->get_meta( __( 'Retreat Start Date', 'woocommerce-product-date' ) );
	if ( $retreat_start_date ) {
		echo '<p><strong>' . __( 'Retreat Start Date:', 'woocommerce-product-date' ) . '</strong> ' . esc_html( $retreat_start_date ) . '</p>';
	}
}
add_action( 'woocommerce_order_item_meta_start', 'wcpd_display_retreat_start_date_in_order', 10, 4 );

/**
 * Add Retreat Start Date to order emails.
 */
function wcpd_add_retreat_start_date_to_order_email( $fields, $sent_to_admin, $order ) {
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
add_filter( 'woocommerce_email_order_meta_fields', 'wcpd_add_retreat_start_date_to_order_email', 10, 3 );

/**
 * Add Retreat Start Date to WooCommerce REST API.
 */
function wcpd_register_retreat_start_date_rest_field() {
	register_rest_field( 'product', 'retreat_start_date', array(
		'get_callback'    => function ( $object ) {
			return get_post_meta( $object['id'], '_retreat_start_date', true );
		},
		'update_callback' => function ( $value, $object ) {
			if ( preg_match( '/\d{2}-\d{2}-\d{4}/', $value ) ) {
				update_post_meta( $object->ID, '_retreat_start_date', sanitize_text_field( $value ) );
			}
		},
		'schema'          => array(
			'description' => __( 'Retreat Start Date in DD-MM-YYYY format', 'woocommerce-product-date' ),
			'type'        => 'string',
			'context'     => array( 'view', 'edit' ),
		),
	) );
}
add_action( 'rest_api_init', 'wcpd_register_retreat_start_date_rest_field' );

/**
 * Enqueue admin styles for column display.
 */
add_action('admin_enqueue_scripts', 'wcpd_enqueue_admin_styles');
function wcpd_enqueue_admin_styles($hook) {
	if ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'product') {
		wp_enqueue_style(
			'wcpd-admin-styles',
			plugins_url('css/admin-styles.css', __FILE__),
			[],
			'1.3'
		);
	}
}