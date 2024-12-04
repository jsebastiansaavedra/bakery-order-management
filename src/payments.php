<?php


/**
 * Function that shows the payments via shortcode
 */
function boms_shortcode_payments()
{

    $output = '<div class="boms-page"> ' . get_side_menu();

    // Fetch the non-paid orders
    $non_paid_orders = get_non_paid_orders();

    // Loop through the results and create options
    $options = '';
    $hidden = '';
    foreach ($non_paid_orders as $order) {
        // Assuming $order->client_phone exists
        $options .= '<option value="' . esc_attr($order->order_id) . '">Orden #'. esc_html($order->order_id) .' - Cliente: '. esc_html($order->name) .' - ' . esc_html($order->client_phone) . '</option>';
        $hidden .= '<input type="hidden" order-id="' . esc_attr($order->order_id) . '" name="balance" value="' . esc_attr($order->balance) . '">';
    }


    $output .= '<div class="main-content">
        <div class="search_bar">
            <form method="GET">
                <input type="text" name="search_payments" id="search_payments" placeholder="Buscar">
            </form>
        </div>
        <button class="items_accordion new-button" id="new_button">Crear Pago</button>
        <div class="main_panel" id="panel_new_button">
            <table>
                <tr>
                    <td><strong> Orden </strong></td>
                    <td>
                        <select name="payment_order_new" id="paymentOrderDropdown">
                            <option value="" disabled selected>Seleccionar</option>
                            '. $options .'
                            <option value="0">Otro concepto</option>
                        </select>
                        '. $hidden .'
                        <div class="debt-container" style="display: none;">
                            Deuda: <span id="debt-value"></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><strong> Medio de pago </strong></td>
                    <td>
                        <select name="payment_method_new" id="paymentMethodDropdown">
                            <option value="" disabled selected>Seleccionar</option>
                            <option value="1">Nequi</option>
                            <option value="2">Bancolombia</option>
                            <option value="3">Efectivo</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><strong> Valor </strong></td>
                    <td><input type="number" name="payment_amount_new" value=""></td>
                </tr>
            </table>
            <button id="create-payment" class="btn-create">
                    Crear
            </button>
        </div>
        ';

    $search = isset( $_GET['search_payments'] ) ? sanitize_text_field( $_GET['search_payments'] ) : '';
    $data = get_payments_html( $search );
    
    $output .= '<div id="results">' . $data . '</div>
    </div></div>';

    return $output;
}


/**
 * This function runs the select to get the payments from the database
 */
function get_payments_html( $search = '' )
{
    global $wpdb;

    // Table names
    $orders_table = $wpdb->prefix . 'boms_orders';
    $clients_table = $wpdb->prefix . 'boms_clients';
    $payments_table = $wpdb->prefix . 'boms_payments';

    $search_query = '%' . $wpdb->esc_like( $search ) . '%';

    // Query with INNER JOIN and subquery for balance
    $sql = "
        SELECT 
            {$clients_table}.name, 
            {$orders_table}.client_phone, 
            {$orders_table}.order_id, 
            {$payments_table}.payment_method, 
            {$payments_table}.amount, 
            {$payments_table}.payment_id,
            {$payments_table}.created_at
        FROM 
            {$payments_table}
        INNER JOIN 
            {$orders_table} 
        ON 
            {$orders_table}.order_id = {$payments_table}.order_id
        INNER JOIN 
            {$clients_table} 
        ON 
            {$orders_table}.client_phone = {$clients_table}.phone
        WHERE 
            {$orders_table}.order_id LIKE %s OR {$clients_table}.name LIKE %s  OR {$orders_table}.client_phone LIKE %s 
        ORDER BY 
            {$orders_table}.delivery_day ASC
    ";

    $data = $wpdb->get_results($wpdb->prepare($sql, $search_query, $search_query, $search_query));
    $output = '';

    if (! empty($data)) {
        foreach ($data as $payment_info) {
            $payment_method = esc_html($payment_info->payment_method);
            if ($payment_method == 1) {
                $payment_method = "Nequi";
            } elseif ($payment_method == 2) {
                $payment_method = "Bancolombia";
            } elseif ($payment_method == 3) {
                $payment_method = "Efectivo";
            } else {
                echo "Unknown Payment Method"; 
            }
            $output .= '
            <button class="items_accordion" id="' . esc_html($payment_info->payment_id) . '">#' . esc_html($payment_info->order_id)  . ' - Cantidad: $ ' . esc_html( number_format( $payment_info->amount, 0, '.', ',' ) ) . ' '.  esc_html($payment_method) . ' - Cliente: '. esc_html($payment_info->name) .' - '. esc_html($payment_info->client_phone) .'</button>
            <div class="main_panel" id="panel_' . esc_html($payment_info->payment_id) . '">
                <table>
                    <tr>
                        <td><strong> Orden </strong></td>
                        <td><span>#' . esc_html($payment_info->order_id) . '</span></td>
                    </tr>
                    <tr>
                        <td><strong> Cantidad </strong></td>
                        <td><span>$ ' . esc_html( number_format( $payment_info->amount, 0, '.', ',' ) ) . '</span></td>
                    </tr>
                    <tr>
                        <td><strong> Método de Pago </strong></td>
                        <td>
                            <select name="payment_method_' . esc_html($payment_info->payment_id) . '" id="paymentMethodDropdown">
                                <option value="" disabled ' . (empty($payment_info->payment_method) ? 'selected' : '') . '>Seleccionar</option>
                                <option value="1" ' . ($payment_info->payment_method === '1' ? 'selected' : '') . '>Nequi</option>
                                <option value="2" ' . ($payment_info->payment_method === '2' ? 'selected' : '') . '>Bancolombia</option>
                                <option value="3" ' . ($payment_info->payment_method === '3' ? 'selected' : '') . '>Efectivo</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><strong> Pagado en </strong></td>
                        <td>' . esc_html( date_i18n( 'j F\, Y', strtotime( $payment_info->created_at ) ) ) . '</td>
                    </tr>
                    <tr>
                        <td><strong> Cliente </strong></td>
                        <td><span>' . esc_html($payment_info->name) . '</span></td>
                    </tr>
                    <tr>
                        <td><strong> Teléfono </strong></td>
                        <td><span>' . esc_html($payment_info->client_phone) . '</span></td>
                    </tr>
                </table>
                <button id="update-payment" class="btn-update" data-payment-id="' . esc_html($payment_info->payment_id) . '">
                    Actualizar
                </button>
            </div>
            ';
        }
    } else {
        $output .= '<div>Ningún pago encontrado</div>';
    }

    return $output;
}

if (isset($_GET['search_payments'])) {
    echo get_payments_html(sanitize_text_field($_GET['search_payments']));
    exit;
}

function update_payments($payment_id, $payment_method)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_payments';

    $sql = "UPDATE $table_name 
            SET payment_method = %d
            WHERE payment_id = %d";

    return $wpdb->query($wpdb->prepare($sql, $payment_method, $payment_id));
}

add_action('wp_ajax_call_update_payments', 'handle_update_payments');
add_action('wp_ajax_nopriv_call_update_payments', 'handle_update_payments');

function handle_update_payments()
{
    if (isset($_POST['payment_id'])) {

        $payment_id = sanitize_text_field($_POST['payment_id']);
        $payment_method = sanitize_text_field($_POST['payment_method']);

        // Call the update delivery function
        $result = update_payments($payment_id, $payment_method);

        if ($result !== false) {
            wp_send_json_success('Payment updated successfully.');
        } else {
            wp_send_json_error('Failed to update payment.');
        }
    } else {
        wp_send_json_error('Invalid payment ID.');
    }

    wp_die();
}


function create_payments($order_id, $payment_method, $amount)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_payments';

    // Use $wpdb->insert() for cleaner and safer insertion
    $data = [
        'order_id' => $order_id,
        'payment_method' => $payment_method,
        'amount' => $amount,
    ];
    
    $format = ['%d', '%d', '%d'];
    
    return $wpdb->insert($table_name, $data, $format);
}

add_action('wp_ajax_call_create_payments', 'handle_create_payments');
add_action('wp_ajax_nopriv_call_create_payments', 'handle_create_payments');

function handle_create_payments() {
    if (isset($_POST['order_id']) && isset($_POST['payment_method']) && isset($_POST['amount'])) {

        $order_id = intval($_POST['order_id']);
        $payment_method = intval($_POST['payment_method']);
        $amount = intval($_POST['amount']);

        // Call the create delivery function
        $result = create_payments($order_id, $payment_method, $amount);

        if ($result !== false) {
            $result_mark_as_fully_paid = mark_as_fully_paid($order_id);
            wp_send_json_success('Payment created successfully.');
        } else {
            wp_send_json_error('Failed to create payment.');
        }
    } else {
        wp_send_json_error('Missing values');
    }

    wp_die();
}

function mark_as_fully_paid($order_id) {
    global $wpdb;
    $orders_table = $wpdb->prefix . 'boms_orders';
    $payments_table = $wpdb->prefix . 'boms_payments';

    $sql = "
        UPDATE $orders_table AS o
        SET fully_paid = 1
        WHERE o.order_id = %d
        AND (
            SELECT SUM(p.amount)
            FROM $payments_table AS p
            WHERE p.order_id = %d
        ) = o.final_price
    ";

    // Prepare the query with the order ID
    $prepared_sql = $wpdb->prepare($sql, $order_id, $order_id);

    // Execute the query
    return $wpdb->query($prepared_sql);
}

function get_non_paid_orders() {
    global $wpdb;

    // Table names
    $orders_table = $wpdb->prefix . 'boms_orders';
    $clients_table = $wpdb->prefix . 'boms_clients';
    $payments_table = $wpdb->prefix . 'boms_payments';

    // Query with INNER JOIN and subquery for balance
    $sql = "
        SELECT 
            {$clients_table}.name, 
            {$orders_table}.client_phone, 
            {$orders_table}.order_id, 
            {$orders_table}.final_price - (
                SELECT 
                    COALESCE(SUM(amount), 0) 
                FROM 
                    {$payments_table} 
                WHERE 
                    {$payments_table}.order_id = {$orders_table}.order_id
            ) AS balance
        FROM 
            {$orders_table}
        INNER JOIN 
            {$clients_table} 
        ON 
            {$clients_table}.phone = {$orders_table}.client_phone
        WHERE 
            {$orders_table}.fully_paid = 0 
        ORDER BY 
            {$orders_table}.delivery_day ASC
    ";

    return $wpdb->get_results($sql);
}
