<?php


/**
 * Function that shows the clients via shortcode
 */
function boms_shortcode_clients()
{

    $output = '<div class="boms-page"> ' . get_side_menu();


    $output .= '<div class="main-content">
        <div class="search_bar">
            <form method="GET">
                <input type="text" name="search_clients" id="search_clients" placeholder="Buscar">
            </form>
        </div>
        <button class="items_accordion new-button" id="new_button">Crear Cliente</button>
        <div class="main_panel" id="panel_new_button">
            <table>
                <tr>
                    <td><strong> Nombre </strong></td>
                    <td><input type="text" name="name_new" value=""></td>
                </tr>
                <tr>
                    <td><strong> Teléfono </strong></td>
                    <td><input type="text" name="phone_new" value=""></td>
                </tr>
                <tr>
                    <td><strong> Dirección </strong></td>
                    <td><textarea name="address_new"></textarea></td>
                </tr>
                <tr>
                    <td><strong> Edad </strong></td>
                    <td><input type="number" name="age_new" value=""></td>
                </tr>
                <tr>
                    <td><strong> Género </strong></td>
                    <td>
                        <select name="genre_new" id="genreDropdown">
                            <option value="" disabled selected>Seleccionar</option>
                            <option value="M">Hombre</option>
                            <option value="F">Mujer</option>
                        </select>
                    </td>
                </tr>
            </table>
            <button id="create-client" class="btn-create">
                    Crear
            </button>
        </div>
        ';

    $search = isset( $_GET['search_clients'] ) ? sanitize_text_field( $_GET['search_clients'] ) : '';
    $data = get_clients_html( $search );
    
    $output .= '<div id="results">' . $data . '</div>
    </div></div>';

    return $output;
}


/**
 * This function runs the select to get the clients from the database
 */
function get_clients_html( $search = '' )
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_clients';

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
        foreach ($data as $client_info) {
            $output .= '
            <button class="items_accordion" id="' . esc_html($client_info->phone) . '">' . esc_html($client_info->name)  . '</button>
            <div class="main_panel" id="panel_' . esc_html($client_info->phone) . '">
                <table>
                    <tr>
                        <td><strong> Teléfono </strong></td>
                        <td><span>' . esc_html($client_info->phone) . '</span></td>
                    </tr>
                    <tr>
                        <td><strong> Nombre </strong></td>
                        <td><input type="text" name="name_' . esc_html($client_info->phone) . '" value="' . esc_html($client_info->name) . '"></td>
                    </tr>
                    <tr>
                        <td><strong> Dirección </strong></td>
                        <td><textarea name="address_' . esc_html($client_info->phone) . '">' . esc_html($client_info->address) . '</textarea></td>
                    </tr>
                    <tr>
                        <td><strong> Edad </strong></td>
                        <td><input type="number" name="age_' . esc_html($client_info->phone) . '" value="' . esc_html($client_info->age) . '"></td>
                    </tr>
                    <tr>
                        <td><strong> Género </strong></td>
                        <td>
                            <select name="genre_' . esc_html($client_info->phone) . '" id="genreDropdown">
                                <option value="" disabled ' . (empty($client_info->genre) ? 'selected' : '') . '>Seleccionar</option>
                                <option value="M" ' . ($client_info->genre === 'M' ? 'selected' : '') . '>Hombre</option>
                                <option value="F" ' . ($client_info->genre === 'F' ? 'selected' : '') . '>Mujer</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <button id="update-client" class="btn-update" data-client-id="' . esc_html($client_info->phone) . '">
                    Actualizar
                </button>
            </div>
            ';
        }
    } else {
        $output .= '<div>Ningún cliente encontrado</div>';
    }

    return $output;
}

if (isset($_GET['search_clients'])) {
    echo get_clients_html(sanitize_text_field($_GET['search_clients']));
    exit;
}

function update_clients($client_id, $phone, $name, $address, $genre, $age)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_clients';

    $sql = "UPDATE $table_name 
            SET phone = %s, name = %s, address = %s, genre = %s, age = %d 
            WHERE phone = %d";

    return $wpdb->query($wpdb->prepare($sql, $phone, $name, $address, $genre, $age, $client_id));
}

add_action('wp_ajax_call_update_clients', 'handle_update_clients');
add_action('wp_ajax_nopriv_call_update_clients', 'handle_update_clients');

function handle_update_clients()
{
    if (isset($_POST['client_id'])) {

        $client_id = sanitize_text_field($_POST['client_id']);
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
        $result = update_clients($client_id, $phone, $name, $address, $genre, $age);

        if ($result !== false) {
            wp_send_json_success('Client updated successfully.');
        } else {
            wp_send_json_error('Failed to update client.');
        }
    } else {
        wp_send_json_error('Invalid client ID.');
    }

    wp_die();
}


function create_clients($phone, $name, $address, $genre, $age)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_clients';

    $sql = "INSERT INTO $table_name (phone, name, address, genre, age) 
            VALUES (%s, %s, %s, %s, %d)";

    return $wpdb->query($wpdb->prepare($sql, $phone, $name, $address, $genre, $age));
}

add_action('wp_ajax_call_create_clients', 'handle_create_clients');
add_action('wp_ajax_nopriv_call_create_clients', 'handle_create_clients');

function handle_create_clients()
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
        $result = create_clients($phone, $name, $address, $genre, $age);

        if ($result !== false) {
            wp_send_json_success('Client created successfully.');
        } else {
            wp_send_json_error('Failed to create client.');
        }
    } else {
        wp_send_json_error('Missing values');
    }

    wp_die();
}
