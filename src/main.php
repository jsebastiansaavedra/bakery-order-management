<?php


/**
 * Function that shows the dashboard via shortcode
*/
function boms_shortcode_dashboard() {

    $output = '';
    $today_sales = get_today_sales();
    $month_sales = get_month_sales();
    $balances = get_money_balance();
    
    $data = get_non_delivered_orders();
    $output .= '
    <div class="boms-page"> ' . get_side_menu() . '
        <div class="main-content">
            <div class="sales-panel">
                <div class="card today-sales">
                    <h3>Ventas hoy</h3>
                    <span>$ ' . esc_html( number_format( $today_sales[0]->total_subtotal, 0, '.', ',' ) ) . '</span>
                </div>
                <div class="card month-sales">
                    <h3>Ventas este mes</h3>
                    <span>$ ' . esc_html( number_format( $month_sales[0]->total_subtotal, 0, '.', ',' ) ) . '</span>
                </div>
            </div>
            <div class="money-balance-panel">
                <div class="card nequi-balance">
                    <h3>Nequi</h3>
                    <span>$ ' . esc_html( number_format( $balances[0]->total_balance, 0, '.', ',' ) ) . '</span>
                </div>
                <div class="card bancolombia-balance">
                    <h3>Bancolombia</h3>
                    <span>$ ' . esc_html( number_format( $balances[1]->total_balance, 0, '.', ',' ) ) . '</span>
                </div>
                <div class="card cash-balance">
                    <h3>Efectivo</h3>
                    <span>$ ' . esc_html( number_format( $balances[2]->total_balance, 0, '.', ',' ) ) . '</span>
                </div>
            </div>
            <h2 style="text-align:center; margin-top: 80px;">Pendientes de Entrega</h2>'; 

    if ( ! empty( $data ) ) {
        foreach ( $data as $item ) {
            $client_info = get_client_information( esc_html( $item->client_phone ) );

            $payments = get_payments_information( esc_html( $item->order_id ) );
            $balance = 0 - $item->final_price;
            foreach ( $payments as $payment ) {
                $balance += $payment->amount;
            }

            $products_ordered = get_products_ordered( $item->order_id );
            $products = '
            <div class="products_ordered">
                <table>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Comentarios</th>
                    </tr>';

            foreach ( $products_ordered as $product_ordered ) {
                $product = get_individual_product($product_ordered->product_id);
                $products .= '
                    <tr>
                        <td><strong>' . esc_html( $product_ordered->units ) . '</strong></td>
                        <td><strong>' . esc_html( $product[0]->product_name ) . '</strong></td>
                        <td>' . esc_html( $product_ordered->comments ) . '</td>
                    </tr>';
            }

            $products .= '</table></div>';

            $output .= '
            <button class="items_accordion">'. check_if_paid( $item->fully_paid ) . check_if_delivered( $item->delivered ) . esc_html( date_i18n( 'j F\, Y', strtotime( $item->delivery_day ) ) ) . ' - ' . esc_html( $client_info[0]->name )  . '</button>
            <div class="main_panel">
                <table>
                    <tr>
                        <td><strong> Fecha de entrega </strong></td>
                        <td>' . esc_html( date_i18n( 'j F\, Y', strtotime( $item->delivery_day ) ) ) . '</td>
                    </tr>
                    <tr>
                        <td><strong> Cliente </strong></td>
                        <td>' . esc_html( $client_info[0]->name ) . '</td>
                    </tr>
                    <tr>
                        <td><strong> Teléfono </strong></td>
                        <td>' . esc_html( $client_info[0]->phone ) . '</td>
                    </tr>
                    <tr>
                        <td><strong> Dirección </strong></td>
                        <td>' . esc_html( $client_info[0]->address ) . '</td>
                    </tr>
                    <tr>
                        <td><strong> Productos </strong></td>
                        <td>' . $products . '</td>
                    </tr>
                    <tr>
                        <td><strong> Subtotal </strong></td>
                        <td>$ ' . esc_html( number_format( $item->subtotal, 0, '.', ',' ) ) . '</td>
                    </tr>
                    <tr>
                        <td><strong> Domicilio </strong></td>
                        <td>$ ' . esc_html( number_format( $item->delivery_price, 0, '.', ',' ) ) . '</td>
                    </tr>
                    <tr>
                        <td><strong> Total </strong></td>
                        <td>$ ' . esc_html( number_format( $item->final_price, 0, '.', ',' ) ) . '</td>
                    </tr>
                    <tr>
                        <td><strong> Balance </strong></td>
                        <td class="balance">' . esc_html( number_format( $balance, 0, '.', ',' ) ) . '</td>
                    </tr>
                    <tr>
                        <td><strong> Domicilio </strong></td>
                        <td><select id="deliveryDropdown">
                            <option value="" disabled selected>Seleccionar</option>
                            <option value="Oscar">Oscar</option>
                            <option value="paid_nequi">Domiciliario externo pagado desde Nequi</option>
                            <option value="paid_bancolombia">Domiciliario externo pagado desde Bancolombia</option>
                            <option value="paid_cash">Domiciliario externo pagado en Efectivo</option>
                            <option value="pickup">Recoge</option>
                        </select></td>
                    </tr>
                </table>
            ';
            if ( $item->delivered != 1 ) {
                $output .= '
                <button id="mark-as-delivered" class="btn-update" data-order-id="' . esc_html( $item->order_id ) .'">
                    Marcar como Entregado
                </button>';
            }
            $output .= '</div>';
        }
    } else {
        $output .= 'No hay pedidos pendientes';
    }

    $output .= '</div></div>';
    return $output;
}

/**
 * This function runs the select to get the non-delivered orders from the database
 */
function get_non_delivered_orders() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_orders';

    $sql = "SELECT * FROM $table_name WHERE delivered = 0 OR fully_paid = 0 ORDER BY delivery_day ASC";

    return $wpdb->get_results( $sql );
}


/**
 * This function runs the select to get the client data from the database
 */
function get_client_information( $phone = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_clients';

    $sql = "SELECT * FROM $table_name WHERE phone = $phone";

    return $wpdb->get_results( $sql );
}


/**
 * This function runs the select to get the payments data from the database
 */
function get_payments_information( $order_id = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_payments';

    $sql = "SELECT * FROM $table_name WHERE order_id = $order_id";

    return $wpdb->get_results( $sql );
}


/**
 * This function runs the select to get the products ordered data from the database
 */
function get_products_ordered( $order_id = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_order_details';

    $sql = "SELECT * FROM $table_name WHERE order_id = $order_id";

    return $wpdb->get_results( $sql );
}

/**
 * This function runs the select to get the individual product data from the database
 */
function get_individual_product( $product_id = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_products';

    $sql = "SELECT * FROM $table_name WHERE product_id = $product_id";

    return $wpdb->get_results( $sql );
}

/**
 * This function runs the select to get the delivery price
 */
function get_delivery_price( $order_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_orders';

    $sql = "SELECT * FROM $table_name WHERE order_id = $order_id";

    return $wpdb->get_results( $sql );
}


function get_side_menu () {
    return '
        <div class="icons-side-menu">
            <a id="menu-dashboard" class="icon-side-menu" href="'. home_url() .'/dashboard">
                <img src="https://icongr.am/entypo/blackboard.svg?size=32&color=B24949" alt="Inicio" loading="lazy">
                <span>Inicio</span>
            </a>
            <a id="menu-orders" class="icon-side-menu" href="'. home_url() .'/orders">
                <img src="https://icongr.am/entypo/folder.svg?size=32&color=B24949" alt="Orders" loading="lazy">
                <span>Pedidos</span>
            </a>
            <a id="menu-clients" class="icon-side-menu" href="'. home_url() .'/clients">
                <img src="https://icongr.am/entypo/users.svg?size=32&color=B24949" alt="Clients" loading="lazy">
                <span>Clientes</span>
            </a>
            <a id="menu-payments" class="icon-side-menu" href="'. home_url() .'/payments">
                <img src="https://icongr.am/entypo/credit-card.svg?size=32&color=B24949" alt="Payments" loading="lazy">
                <span>Pagos</span>
            </a>
            <a id="menu-products" class="icon-side-menu" href="'. home_url() .'/products">
                <img src="https://icongr.am/entypo/cake.svg?size=32&color=B24949" alt="Products" loading="lazy">
                <span>Productos</span>
            </a>
            <a id="menu-expenses" class="icon-side-menu" href="'. home_url() .'/expenses">
                <img src="https://icongr.am/entypo/calculator.svg?size=32&color=B24949" alt="Expenses" loading="lazy">
                <span>Gastos</span>
            </a>
            <a id="menu-accounting" class="icon-side-menu" href="'. home_url() .'/accounting">
                <img src="https://icongr.am/entypo/bar-graph.svg?size=32&color=B24949" alt="Accounting" loading="lazy">
                <span>Contabilidad</span>
            </a>
        </div>';
}


function check_if_paid($paid = 0) {
    if ($paid ==  1){
        return '<span class="fully_paid">*Pagado*</span> - ';
    }else {
        return '';
    }
}

function check_if_delivered($delivered = 0) {
    if ($delivered ==  1){
        return '<span class="delivered">*Entregado*</span> - ';
    }else {
        return '';
    }
}

function mark_as_delivered($order_id = 0, $delivery) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_orders';

    $sql = "UPDATE $table_name SET delivered = 1, delivery_day = CURRENT_DATE(), delivered_by = '$delivery' WHERE order_id = $order_id";

    return $wpdb->get_results( $sql );
}

add_action('wp_ajax_call_mark_as_delivered', 'handle_mark_as_delivered');
add_action('wp_ajax_nopriv_call_mark_as_delivered', 'handle_mark_as_delivered');

function handle_mark_as_delivered() {
    if (isset($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']);
        $delivery = $_POST['delivery'];

        // Call the update delivery function
        $result = mark_as_delivered($order_id, $delivery);

        if ($result != "") {
            wp_send_json_success('Order updated successfully.');
        } else {
            wp_send_json_error('Failed to update order.' . $result);
        }
    } else {
        wp_send_json_error('Invalid order ID.');
    }

    wp_die();
}

function get_today_sales() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_orders';

    $sql = "SELECT SUM(subtotal) AS total_subtotal FROM $table_name WHERE DATE(created_at) = CURDATE()";

    return $wpdb->get_results( $sql );
}

function get_month_sales() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_orders';

    $sql = "SELECT SUM(subtotal) AS total_subtotal FROM $table_name WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";

    return $wpdb->get_results( $sql );
}

function get_money_balance() {
    global $wpdb;
    $table_name_1 = $wpdb->prefix . 'boms_payments';
    $table_name_2 = $wpdb->prefix . 'boms_expenses';

    $sql = "SELECT pm_defaults.payment_method, 
        IFNULL(pm.total_payments, 0) - IFNULL(exp.total_expenses, 0) AS total_balance
        FROM 
            (SELECT 1 AS payment_method
            UNION ALL
            SELECT 2
            UNION ALL
            SELECT 3) AS pm_defaults
        LEFT JOIN 
            (SELECT payment_method, SUM(amount) AS total_payments 
            FROM $table_name_1
            GROUP BY payment_method) AS pm
        ON pm_defaults.payment_method = pm.payment_method
        LEFT JOIN 
            (SELECT payment_method, SUM(amount) AS total_expenses 
            FROM $table_name_2
            GROUP BY payment_method) AS exp 
        ON pm_defaults.payment_method = exp.payment_method
        ORDER BY pm_defaults.payment_method";

    return $wpdb->get_results( $sql );
}