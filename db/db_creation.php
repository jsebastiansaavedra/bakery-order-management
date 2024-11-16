<?php
/**
 * Function that creates the tables if not exists
 */


function create_related_tables() {
    global $wpdb;

    $clients_table = $wpdb->prefix . 'boms_clients';
    $products_table = $wpdb->prefix . 'boms_products';
    $orders_table = $wpdb->prefix . 'boms_orders';
    $payments_table = $wpdb->prefix . 'boms_payments';
    $order_details_table = $wpdb->prefix . 'boms_order_details';
    $expenses_table = $wpdb->prefix . 'boms_expenses';

    $charset_collate = $wpdb->get_charset_collate();

    $sql1 = "CREATE TABLE IF NOT EXISTS $clients_table (
        phone VARCHAR(10) NOT NULL,
        name tinytext NOT NULL,
        address VARCHAR(150) DEFAULT NULL,
        genre CHAR(1) DEFAULT 'F',
        age tinyint DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        last_purchase datetime DEFAULT NULL,
        PRIMARY KEY  (phone)
    ) $charset_collate;";

    $sql2 = "CREATE TABLE IF NOT EXISTS $products_table (
        product_id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_name tinytext NOT NULL,
        product_price mediumint(9) NOT NULL,
        PRIMARY KEY  (product_id)
    ) $charset_collate;";

    $sql3 = "CREATE TABLE IF NOT EXISTS $orders_table (
        order_id mediumint(9) NOT NULL AUTO_INCREMENT,
        client_phone VARCHAR(10) NOT NULL,
        subtotal mediumint NOT NULL,
        delivery_price mediumint NOT NULL,
        final_price mediumint NOT NULL,
        fully_paid tinyint DEFAULT 0,
        delivery_day date DEFAULT NULL,
        delivered_by VARCHAR(40) DEFAULT NULL,
        delivered tinyint DEFAULT 0,
        delivery_paid tinyint DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        delivered_at datetime DEFAULT NULL,
        PRIMARY KEY  (order_id),
        FOREIGN KEY  (client_phone) REFERENCES $clients_table (phone)
    ) $charset_collate;";

    $sql4 = "CREATE TABLE IF NOT EXISTS $payments_table (
        payment_id bigint NOT NULL AUTO_INCREMENT,
        order_id mediumint(9) NOT NULL,
        payment_method tinyint NOT NULL,
        amount int NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (payment_id),
        FOREIGN KEY  (order_id) REFERENCES $orders_table (order_id)
    ) $charset_collate;";

    $sql5 = "CREATE TABLE IF NOT EXISTS $order_details_table (
        order_id mediumint(9) NOT NULL,
        product_id mediumint(9) NOT NULL,
        units mediumint NOT NULL,
        comments text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (order_id, product_id),
        FOREIGN KEY (order_id) REFERENCES $orders_table(order_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES $products_table(product_id) ON DELETE CASCADE
    ) $charset_collate;";

    $sql6 = "CREATE TABLE IF NOT EXISTS $expenses_table (
        expense_id bigint NOT NULL AUTO_INCREMENT,
        expense_description tinytext NOT NULL,
        amount mediumint(9) NOT NULL,
        payment_method mediumint NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (expense_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql1 );
    dbDelta( $sql2 );
    dbDelta( $sql3 );
    dbDelta( $sql4 );
    dbDelta( $sql5 );
    dbDelta( $sql6 );
}