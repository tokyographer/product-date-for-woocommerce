<?php
/*
Plugin Name: Woocommerce Product Date
Plugin URI: https://github.com/tokyographer
Description: Adds a custom "Retreat Start Date" field to WooCommerce products, Quick Edit, orders, emails, and the WooCommerce REST API.
Version: 1.0
Author: tokyographer
Author URI: https://github.com/tokyographer
Text Domain: woocommerce-product-date
Domain Path: /languages
License: GPL2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load plugin textdomain for translations
function wcpd_load_textdomain() {
    load_plugin_textdomain( 'woocommerce-product-date', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wcpd_load_textdomain' );

/**
 * Add the Retreat Start Date field to the product edit page
 */
function wcpd_add_retreat_start_date_field() {
    woocommerce_wp_text_input( array(
        'id'          => '_retreat_start_date',
        'label'       => __( 'Retreat Start Date', 'woocommerce-product-date' ),
        'placeholder' => 'YYYY-MM-DD',
        'type'        => 'date',
        'description' => __( 'Enter the retreat start date for this product.', 'woocommerce-product-date' ),
        'desc_tip'    => true,
    ) );
}
add_action( 'woocommerce_product_options_general_product_data', 'wcpd_add_retreat_start_date_field' );

/**
 * Save the Retreat Start Date field value
 */
function wcpd_save_retreat_start_date_field( $post_id ) {
    $retreat_start_date = isset( $_POST['_retreat_start_date'] ) ? sanitize_text_field( $_POST['_retreat_start_date'] ) : '';
    update_post_meta( $post_id, '_retreat_start_date', $retreat_start_date );
}
add_action( 'woocommerce_process_product_meta', 'wcpd_save_retreat_start_date_field' );

/**
 * Add Retreat Start Date column to the admin product list table
 */
function wcpd_add_retreat_start_date_column( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        if ( 'price' === $key ) {
            $new_columns['retreat_start_date'] = __( 'Retreat Start Date', 'woocommerce-product-date' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_edit-product_columns', 'wcpd_add_retreat_start_date_column' );

/**
 * Populate the Retreat Start Date column
 */
function wcpd_show_retreat_start_date_column_content( $column, $post_id ) {
    if ( 'retreat_start_date' === $column ) {
        $retreat_start_date = get_post_meta( $post_id, '_retreat_start_date', true );
        echo $retreat_start_date ? esc_html( $retreat_start_date ) : __( 'N/A', 'woocommerce-product-date' );
    }
}
add_action( 'manage_product_posts_custom_column', 'wcpd_show_retreat_start_date_column_content', 10, 2 );

/**
 * Add Retreat Start Date field to Quick Edit
 */
function wcpd_add_retreat_start_date_quick_edit( $column_name, $post_type ) {
    if ( 'retreat_start_date' !== $column_name || 'product' !== $post_type ) {
        return;
    }
    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <label>
                <span class="title"><?php _e( 'Retreat Start Date', 'woocommerce-product-date' ); ?></span>
                <span class="input-text-wrap">
                    <input type="date" name="_retreat_start_date" class="ptitle" value="">
                </span>
            </label>
        </div>
    </fieldset>
    <?php
}
add_action( 'quick_edit_custom_box', 'wcpd_add_retreat_start_date_quick_edit', 10, 2 );

/**
 * Enqueue Quick Edit script
 */
function wcpd_enqueue_quick_edit_scripts( $hook ) {
    global $post_type;

    if ( 'edit.php' === $hook && 'product' === $post_type ) {
        wp_enqueue_script( 'wcpd_quick_edit', plugin_dir_url( __FILE__ ) . 'quick-edit.js', array( 'jquery', 'inline-edit-post' ), '1.0', true );
    }
}
add_action( 'admin_enqueue_scripts', 'wcpd_enqueue_quick_edit_scripts' );

/**
 * Add data for Quick Edit via custom inline data
 */
function wcpd_quick_edit_custom_box( $column_name, $post_id ) {
    if ( 'retreat_start_date' !== $column_name ) {
        return;
    }
    $retreat_start_date = get_post_meta( $post_id, '_retreat_start_date', true );
    ?>
    <div class="hidden" id="wcpd_inline_<?php echo $post_id; ?>">
        <span class="retreat_start_date"><?php echo esc_attr( $retreat_start_date ); ?></span>
    </div>
    <?php
}
add_action( 'manage_product_posts_custom_column', 'wcpd_quick_edit_custom_box', 10, 2 );

/**
 * Save Quick Edit data
 */
function wcpd_save_quick_edit_data( $post_id ) {
    if ( isset( $_REQUEST['_retreat_start_date'] ) ) {
        $retreat_start_date = sanitize_text_field( $_REQUEST['_retreat_start_date'] );
        update_post_meta( $post_id, '_retreat_start_date', $retreat_start_date );
    }
}
add_action( 'save_post', 'wcpd_save_quick_edit_data' );

/**
 * Add Retreat Start Date to cart item data
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
 * Display Retreat Start Date in cart and checkout
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
 * Add Retreat Start Date to order items
 */
function wcpd_add_retreat_start_date_to_order_items( $item, $cart_item_key, $values, $order ) {
    if ( isset( $values['retreat_start_date'] ) ) {
        $item->add_meta_data( __( 'Retreat Start Date', 'woocommerce-product-date' ), $values['retreat_start_date'], true );
    }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'wcpd_add_retreat_start_date_to_order_items', 10, 4 );

/**
 * Add Retreat Start Date to order emails
 */
function wcpd_order_item_display_meta_key( $display_key, $meta, $item ) {
    if ( 'Retreat Start Date' === $display_key ) {
        $display_key = __( 'Retreat Start Date', 'woocommerce-product-date' );
    }
    return $display_key;
}
add_filter( 'woocommerce_order_item_display_meta_key', 'wcpd_order_item_display_meta_key', 10, 3 );

/**
 * Register Retreat Start Date in REST API
 */
function wcpd_register_rest_field() {
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
add_action( 'rest_api_init', 'wcpd_register_rest_field' );

function wcpd_get_retreat_start_date( $object, $field_name, $request ) {
    return get_post_meta( $object['id'], '_retreat_start_date', true );
}

function wcpd_update_retreat_start_date( $value, $object, $field_name ) {
    if ( ! $value || ! is_string( $value ) ) {
        return;
    }
    return update_post_meta( $object->ID, '_retreat_start_date', sanitize_text_field( $value ) );
}

/**
 * Basic error handling and logging
 */
function wcpd_log_error( $message ) {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( $message );
    }
}