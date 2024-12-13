<?php
/**
 * Plugin Name:          Bakery Order Management Software
 * Description:          This plugin helps you to manage the orders and is mainly focused on bakeries
 * Version:              0.1.0
 * Requires at least:    6.1
 * Requires PHP:         7.4
 * Author:               Juan Sebastian Saavedra Alvarez
 * Author URI:           https://github.com/jsebastiansaavedra
 * License:              GPLv2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          bakery-order-management
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Enqueue Plugin Styles and Scripts
 */
function boms_enqueue_styles() {
    // Enqueue all CSS files in the css folder
    foreach ( glob( plugin_dir_path( __FILE__ ) . 'css/*.css' ) as $css_file ) {
        wp_enqueue_style( 
            'boms-style-' . basename( $css_file, '.css' ), 
            plugin_dir_url( __FILE__ ) . 'css/' . basename( $css_file ), 
            array(), 
            '0.1.0', 
            'all' 
        );
    }

    // Enqueue all JS files in the js folder
    foreach ( glob( plugin_dir_path( __FILE__ ) . 'js/*.js' ) as $js_file ) {
        wp_enqueue_script( 
            'boms-script-' . basename( $js_file, '.js' ), 
            plugin_dir_url( __FILE__ ) . 'js/' . basename( $js_file ), 
            array('jquery'), 
            '0.1.0', 
            true 
        );
    }
    wp_enqueue_script('jquery'); // Ensure jQuery is loaded
    wp_localize_script('jquery', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action( 'wp_enqueue_scripts', 'boms_enqueue_styles' );

/**
 * Plugin Initialization
 */
add_action( 'init', 'order_management_plugin_init' );

function order_management_plugin_init() {
    require_once __DIR__ . '/db/db_creation.php';
    require_once __DIR__ . '/src/main.php';
    require_once __DIR__ . '/src/orders.php';
    require_once __DIR__ . '/src/clients.php';
    require_once __DIR__ . '/src/payments.php';
    require_once __DIR__ . '/src/expenses.php';
    require_once __DIR__ . '/src/products.php';
    create_related_tables();
    add_shortcode( 'boms_dashboard', 'boms_shortcode_dashboard' );
    add_shortcode( 'boms_orders', 'boms_shortcode_orders' );
    add_shortcode( 'boms_clients', 'boms_shortcode_clients' );
    add_shortcode( 'boms_payments', 'boms_shortcode_payments' );
    add_shortcode( 'boms_expenses', 'boms_shortcode_expenses' );
    add_shortcode( 'boms_products', 'boms_shortcode_products' );
}