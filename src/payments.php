<?php


/**
 * Function that shows the payments via shortcode
 */
function boms_shortcode_payments()
{

    $output = '<div class="boms-page"> ' . get_side_menu();


    $output .= '<div class="main-content">
        <div class="search_bar">
            <form method="GET">
                <input type="text" name="search_payments" id="search_payments" placeholder="Buscar">
            </form>
        </div>
        <button class="items_accordion new-button">Crear Pago</button>
        <div class="main_panel">
            <table>
                <tr>
                    <td><strong> Orden </strong></td>
                    <td>
                        <select name="payment_order_new" id="paymentOrderDropdown">
                            <option value="" disabled selected>Seleccionar</option>
                        </select>
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
    $table_name = $wpdb->prefix . 'boms_payments';

    $search_query = '%' . $wpdb->esc_like( $search ) . '%';

    $sql = $wpdb->prepare(
        "SELECT * FROM $table_name 
         WHERE phone LIKE %s OR name LIKE %s 
         ORDER BY created_at DESC",
        $search_query,
        $search_query
    );

    $data = $wpdb->get_results($sql);
    $output = '';

    if (! empty($data)) {
        foreach ($data as $payment_info) {
            $output .= '
            <button class="items_accordion">' . esc_html($payment_info->name)  . '</button>
            <div class="main_panel">
                <table>
                    <tr>
                        <td><strong> Teléfono </strong></td>
                        <td><span>' . esc_html($payment_info->phone) . '</span></td>
                    </tr>
                    <tr>
                        <td><strong> Nombre </strong></td>
                        <td><input type="text" name="name_' . esc_html($payment_info->phone) . '" value="' . esc_html($payment_info->name) . '"></td>
                    </tr>
                    <tr>
                        <td><strong> Dirección </strong></td>
                        <td><textarea name="address_' . esc_html($payment_info->phone) . '">' . esc_html($payment_info->address) . '</textarea></td>
                    </tr>
                    <tr>
                        <td><strong> Edad </strong></td>
                        <td><input type="number" name="age_' . esc_html($payment_info->phone) . '" value="' . esc_html($payment_info->age) . '"></td>
                    </tr>
                    <tr>
                        <td><strong> Género </strong></td>
                        <td>
                            <select name="genre_' . esc_html($payment_info->phone) . '" id="genreDropdown">
                                <option value="" disabled ' . (empty($payment_info->genre) ? 'selected' : '') . '>Seleccionar</option>
                                <option value="M" ' . ($payment_info->genre === 'M' ? 'selected' : '') . '>Hombre</option>
                                <option value="F" ' . ($payment_info->genre === 'F' ? 'selected' : '') . '>Mujer</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <button id="update-payment" class="btn-update" data-payment-id="' . esc_html($payment_info->phone) . '">
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

function update_payments($payment_id, $phone, $name, $address, $genre, $age)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_payments';

    $sql = "UPDATE $table_name 
            SET phone = %s, name = %s, address = %s, genre = %s, age = %d 
            WHERE phone = %d";

    return $wpdb->query($wpdb->prepare($sql, $phone, $name, $address, $genre, $age, $payment_id));
}

add_action('wp_ajax_call_update_payments', 'handle_update_payments');
add_action('wp_ajax_nopriv_call_update_payments', 'handle_update_payments');

function handle_update_payments()
{
    if (isset($_POST['payment_id'])) {

        $payment_id = sanitize_text_field($_POST['payment_id']);
        $name = sanitize_text_field($_POST['name']);
        $phone = sanitize_text_field($_POST['phone']);
        $address = sanitize_textarea_field($_POST['address']);
        $genre = sanitize_text_field($_POST['genre']);
        $age = intval($_POST['age']);

        // Remove all spaces to the phone
        $phone = str_replace(' ', '', $phone);

        // Remove +57 if it starts with that
        if (strpos($phone, '+57') === 0) {
            $phone = substr($phone, 3);
        }

        // Call the update delivery function
        $result = update_payments($payment_id, $phone, $name, $address, $genre, $age);

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


function create_payments($phone, $name, $address, $genre, $age)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_payments';

    $sql = "INSERT INTO $table_name (phone, name, address, genre, age) 
            VALUES (%s, %s, %s, %s, %d)";

    return $wpdb->query($wpdb->prepare($sql, $phone, $name, $address, $genre, $age));
}

add_action('wp_ajax_call_create_payments', 'handle_create_payments');
add_action('wp_ajax_nopriv_call_create_payments', 'handle_create_payments');

function handle_create_payments()
{
    if (isset($_POST['name']) && isset($_POST['phone']) && isset($_POST['address']) && isset($_POST['genre']) && isset($_POST['age'])) {

        $name = sanitize_text_field($_POST['name']);
        $phone = sanitize_text_field($_POST['phone']);
        $address = sanitize_textarea_field($_POST['address']);
        $genre = sanitize_text_field($_POST['genre']);
        $age = intval($_POST['age']);

        // Remove all spaces to the phone
        $phone = str_replace(' ', '', $phone);

        // Remove +57 if it starts with that
        if (strpos($phone, '+57') === 0) {
            $phone = substr($phone, 3);
        }

        // Call the create delivery function
        $result = create_payments($phone, $name, $address, $genre, $age);

        if ($result !== false) {
            wp_send_json_success('Payment created successfully.');
        } else {
            wp_send_json_error('Failed to create payment.');
        }
    } else {
        wp_send_json_error('Missing values');
    }

    wp_die();
}
