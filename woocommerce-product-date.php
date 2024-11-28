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
        'placeholder' => 'YYYY-MM-DD',
        'type'        => 'text',
        'description' => __( 'Enter the retreat start date in YYYY-MM-DD format.', 'woocommerce-product-date' ),
        'desc_tip'    => true,
        'custom_attributes' => array(
            'pattern' => '\d{2}-\d{2}-\d{4}', // Validation for YYYY-MM-DD
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
        delete_post_meta( $post_id, '_retreat_start_date' ); // Clear if invalid
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