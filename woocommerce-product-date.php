<?php
/*
Plugin Name: WooCommerce Product Date
Plugin URI: https://github.com/tokyographer
Description: Adds a custom "Retreat Start Date" field to WooCommerce products, including admin panel, Quick Edit, cart, checkout, and emails.
Version: 1.6
Author: tokyographer
Author URI: https://github.com/tokyographer
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
        'placeholder' => 'YYYY-MM-DD', // Matches the expected format
        'type'        => 'date', // Uses the browser-native date picker
        'description' => __( 'Enter the retreat start date in YYYY-MM-DD format.', 'woocommerce-product-date' ),
        'desc_tip'    => true,
    ) );
    echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'wcpd_add_retreat_start_date_field' );
/**
 * Save the Retreat Start Date field value.
 */
function wcpd_save_retreat_start_date_field( $post_id ) {
    $retreat_start_date = isset( $_POST['_retreat_start_date'] ) ? sanitize_text_field( $_POST['_retreat_start_date'] ) : '';
    if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $retreat_start_date ) ) {
        update_post_meta( $post_id, '_retreat_start_date', $retreat_start_date );
    } else {
        delete_post_meta( $post_id, '_retreat_start_date' ); // Clear invalid data
    }
}
add_action( 'woocommerce_process_product_meta', 'wcpd_save_retreat_start_date_field' );

/**
 * Add Retreat Start Date column to the admin product list table.
 */
function wcpd_add_retreat_start_date_column( $columns ) {
    $new_columns = array();

    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        if ( 'sku' === $key ) { // Add after SKU column
            $new_columns['retreat_start_date'] = __( 'Retreat Start Date', 'woocommerce-product-date' );
        }
    }

    return $new_columns;
}
add_filter( 'manage_edit-product_columns', 'wcpd_add_retreat_start_date_column' );

/**
 * Populate the Retreat Start Date column in the product list.
 */
function wcpd_show_retreat_start_date_column_content( $column, $post_id ) {
    if ( 'retreat_start_date' === $column ) {
        $retreat_start_date = get_post_meta( $post_id, '_retreat_start_date', true );
        echo $retreat_start_date ? esc_html( $retreat_start_date ) : __( 'N/A', 'woocommerce-product-date' );
    }
}
add_action( 'manage_product_posts_custom_column', 'wcpd_show_retreat_start_date_column_content', 10, 2 );

/**
 * Enqueue admin styles and scripts for Quick Edit.
 */
function wcpd_enqueue_admin_styles_and_scripts( $hook ) {
    if ( 'edit.php' === $hook && isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] ) {
        wp_enqueue_style(
            'wcpd-admin-styles',
            plugins_url( 'css/admin-styles.css', __FILE__ ),
            [],
            '1.6'
        );
        wp_enqueue_script(
            'wcpd-quick-edit',
            plugins_url( 'js/quick-edit.js', __FILE__ ),
            ['jquery', 'inline-edit-post'],
            '1.6',
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'wcpd_enqueue_admin_styles_and_scripts' );

/**
 * Add Retreat Start Date field to Quick Edit.
 */
function wcpd_add_quick_edit_field( $column_name, $post_type ) {
    if ( $column_name === 'retreat_start_date' && $post_type === 'product' ) {
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label class="inline-edit-group">
                    <span class="title"><?php _e( 'Retreat Start Date', 'woocommerce-product-date' ); ?></span>
                    <input type="date" name="_retreat_start_date" class="retreat-start-date" placeholder="YYYY-MM-DD" value="">
                </label>
            </div>
        </fieldset>
        <?php
    }
}
add_action( 'quick_edit_custom_box', 'wcpd_add_quick_edit_field', 10, 2 );

/**
 * Add hidden data for populating Quick Edit field.
 */
function wcpd_add_quick_edit_hidden_data( $actions, $post ) {
    if ( $post->post_type === 'product' ) {
        $retreat_start_date = get_post_meta( $post->ID, '_retreat_start_date', true );
        $actions['inline hide-if-no-js'] .= sprintf(
            '<span class="hidden" id="retreat_start_date_%d">%s</span>',
            $post->ID,
            esc_attr( $retreat_start_date )
        );
    }
    return $actions;
}
add_filter( 'post_row_actions', 'wcpd_add_quick_edit_hidden_data', 10, 2 );

/**
 * Save Retreat Start Date from Quick Edit.
 */
function wcpd_save_quick_edit_field( $post_id ) {
    if ( isset( $_POST['_retreat_start_date'] ) ) {
        update_post_meta( $post_id, '_retreat_start_date', sanitize_text_field( $_POST['_retreat_start_date'] ) );
    }
}
add_action( 'save_post', 'wcpd_save_quick_edit_field' );
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
 * Display Retreat Start Date in order emails.
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
            if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
                update_post_meta( $object->ID, '_retreat_start_date', sanitize_text_field( $value ) );
            }
        },
        'schema'          => array(
            'description' => __( 'Retreat Start Date in YYYY-MM-DD format', 'woocommerce-product-date' ),
            'type'        => 'string',
            'context'     => array( 'view', 'edit' ),
        ),
    ) );
}
add_action( 'rest_api_init', 'wcpd_register_retreat_start_date_rest_field' );
