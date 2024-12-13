<?php


/**
 * Function that shows the expenses via shortcode
 */
function boms_shortcode_expenses()
{

    $output = '<div class="boms-page"> ' . get_side_menu();

    $output .= '<div class="main-content">
        <div class="search_bar">
            <form method="GET">
                <input type="text" name="search_expenses" id="search_expenses" placeholder="Buscar">
            </form>
        </div>
        <button class="items_accordion new-button" id="new_button">Crear Gasto</button>
        <div class="main_panel" id="panel_new_button">
            <table>
                <tr>
                    <td><strong> Descripción </strong></td>
                    <td><textarea name="expense_description_new"></textarea></td>
                </tr>
                <tr>
                    <td><strong> Cantidad </strong></td>
                    <td><input type="number" name="expense_amount_new" value=""></td>
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
            </table>
            <button id="create-expense" class="btn-create">
                    Crear
            </button>
        </div>
        ';

    $search = isset( $_GET['search_expenses'] ) ? sanitize_text_field( $_GET['search_expenses'] ) : '';
    $data = get_expenses_html( $search );
    
    $output .= '<div id="results">' . $data . '</div>
    </div></div>';

    return $output;
}


/**
 * This function runs the select to get the expenses from the database
 */
function get_expenses_html( $search = '' )
{
    global $wpdb;

    // Table names
    $expenses_table = $wpdb->prefix . 'boms_expenses';

    $search_query = '%' . $wpdb->esc_like( $search ) . '%';

    // Query with INNER JOIN and subquery for balance
    $sql = "
        SELECT 
            *
        FROM 
            {$expenses_table}
        WHERE 
            {$expenses_table}.expense_description LIKE %s OR {$expenses_table}.amount LIKE %s
        ORDER BY 
            {$expenses_table}.created_at ASC
    ";

    $data = $wpdb->get_results($wpdb->prepare($sql, $search_query, $search_query));
    $output = '';

    if (! empty($data)) {
        foreach ($data as $expense_info) {
            $payment_method = esc_html($expense_info->payment_method);
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
            <button class="items_accordion" id="' . esc_html($expense_info->expense_id) . '">#' . esc_html($expense_info->expense_description)  . ' - Cantidad: $' . esc_html( number_format( $expense_info->amount, 0, '.', ',' ) ) . ' --- '.  esc_html($payment_method) . ' --- '.  esc_html( date_i18n( 'j F\, Y', strtotime( $expense_info->created_at ) ) ) . '</button>
            <div class="main_panel" id="panel_' . esc_html($expense_info->expense_id) . '">
                <table>
                    <tr>
                        <td><strong> Descripción </strong></td>
                        <td><textarea name="expense_description_' . esc_html($expense_info->expense_id) . '">' . esc_html($expense_info->expense_description) . '</textarea></td>
                    </tr>
                    <tr>
                        <td><strong> Cantidad </strong></td>
                        <td><input type="number" name="amount_' . esc_html($expense_info->expense_id) . '" value="' . esc_html($expense_info->amount) . '"></td>
                    </tr>
                    <tr>
                        <td><strong> Método de Pago </strong></td>
                        <td>
                            <select name="payment_method_' . esc_html($expense_info->expense_id) . '" id="paymentMethodDropdown">
                                <option value="" disabled ' . (empty($expense_info->payment_method) ? 'selected' : '') . '>Seleccionar</option>
                                <option value="1" ' . ($expense_info->payment_method === '1' ? 'selected' : '') . '>Nequi</option>
                                <option value="2" ' . ($expense_info->payment_method === '2' ? 'selected' : '') . '>Bancolombia</option>
                                <option value="3" ' . ($expense_info->payment_method === '3' ? 'selected' : '') . '>Efectivo</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><strong> Pagado en </strong></td>
                        <td>' . esc_html( date_i18n( 'j F\, Y', strtotime( $expense_info->created_at ) ) ) . '</td>
                    </tr>
                </table>
                <button id="update-expense" class="btn-update" data-expense-id="' . esc_html($expense_info->expense_id) . '">
                    Actualizar
                </button>
            </div>
            ';
        }
    } else {
        $output .= '<div>Ningún gasto encontrado</div>';
    }

    return $output;
}

if (isset($_GET['search_expenses'])) {
    echo get_expenses_html(sanitize_text_field($_GET['search_expenses']));
    exit;
}

function update_expenses($expense_id, $expense_description, $payment_method, $amount)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_expenses';

    $sql = "UPDATE $table_name 
            SET payment_method = %d, amount = %d, expense_description = %s
            WHERE expense_id = %d";

    return $wpdb->query($wpdb->prepare($sql, $payment_method, $amount, $expense_description, $expense_id));
}

add_action('wp_ajax_call_update_expenses', 'handle_update_expenses');
add_action('wp_ajax_nopriv_call_update_expenses', 'handle_update_expenses');

function handle_update_expenses()
{
    if (isset($_POST['expense_id'])) {

        $expense_id = sanitize_text_field($_POST['expense_id']);
        $expense_description = sanitize_textarea_field($_POST['expense_description']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $amount = sanitize_text_field($_POST['amount']);

        // Call the update delivery function
        $result = update_expenses($expense_id, $expense_description, $payment_method, $amount);

        if ($result !== false) {
            wp_send_json_success('Expense updated successfully.');
        } else {
            wp_send_json_error('Failed to update expense.');
        }
    } else {
        wp_send_json_error('Invalid expense ID.');
    }

    wp_die();
}


function create_expenses($expense_description, $payment_method, $amount)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_expenses';

    // Use $wpdb->insert() for cleaner and safer insertion
    $data = [
        'expense_description' => $expense_description,
        'payment_method' => $payment_method,
        'amount' => $amount,
    ];
    
    $format = ['%s', '%d', '%d'];
    
    return $wpdb->insert($table_name, $data, $format);
}

add_action('wp_ajax_call_create_expenses', 'handle_create_expenses');
add_action('wp_ajax_nopriv_call_create_expenses', 'handle_create_expenses');

function handle_create_expenses() {
    if (isset($_POST['expense_description']) && isset($_POST['payment_method']) && isset($_POST['amount'])) {

        $expense_description = sanitize_textarea_field($_POST['expense_description']);
        $payment_method = intval($_POST['payment_method']);
        $amount = intval($_POST['amount']);

        // Call the create delivery function
        $result = create_expenses($expense_description, $payment_method, $amount);

        if ($result !== false) {
            wp_send_json_success('Expense created successfully.');
        } else {
            wp_send_json_error('Failed to create expense.');
        }
    } else {
        wp_send_json_error('Missing values');
    }

    wp_die();
}

