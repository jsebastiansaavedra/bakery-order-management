<?php


/**
 * Function that shows the products via shortcode
 */
function boms_shortcode_products()
{

    $output = '<div class="boms-page"> ' . get_side_menu();

    $output .= '<div class="main-content">
        <div class="search_bar">
            <form method="GET">
                <input type="text" name="search_products" id="search_products" placeholder="Buscar">
            </form>
        </div>
        <button class="items_accordion new-button" id="new_button">Crear Producto</button>
        <div class="main_panel" id="panel_new_button">
            <table>
                <tr>
                    <td><strong> Nombre del producto </strong></td>
                    <td><textarea name="product_name_new"></textarea></td>
                </tr>
                <tr>
                    <td><strong> Precio </strong></td>
                    <td><input type="number" name="product_price_new" value=""></td>
                </tr>
            </table>
            <button id="create-product" class="btn-create">
                    Crear
            </button>
        </div>
        ';

    $search = isset( $_GET['search_products'] ) ? sanitize_text_field( $_GET['search_products'] ) : '';
    $data = get_products_html( $search );
    
    $output .= '<div id="results">' . $data . '</div>
    </div></div>';

    return $output;
}


/**
 * This function runs the select to get the products from the database
 */
function get_products_html( $search = '' )
{
    global $wpdb;

    // Table names
    $products_table = $wpdb->prefix . 'boms_products';

    $search_query = '%' . $wpdb->esc_like( $search ) . '%';

    // Query with INNER JOIN and subquery for balance
    $sql = "
        SELECT 
            *
        FROM 
            {$products_table}
        WHERE 
            {$products_table}.product_name LIKE %s OR {$products_table}.product_price LIKE %s
        ORDER BY 
            {$products_table}.product_id ASC
    ";

    $data = $wpdb->get_results($wpdb->prepare($sql, $search_query, $search_query));
    $output = '';

    if (! empty($data)) {
        foreach ($data as $product_info) {
            $output .= '
            <button class="items_accordion" id="' . esc_html($product_info->product_id) . '">#' . esc_html($product_info->product_id) . ' --- ' . esc_html($product_info->product_name)  . ' - Precio: $' . esc_html( number_format( $product_info->product_price, 0, '.', ',' ) ) . '</button>
            <div class="main_panel" id="panel_' . esc_html($product_info->product_id) . '">
                <table>
                    <tr>
                        <td><strong> Nombre del producto </strong></td>
                        <td><textarea name="product_name_' . esc_html($product_info->product_id) . '">' . esc_html($product_info->product_name) . '</textarea></td>
                    </tr>
                    <tr>
                        <td><strong> Precio </strong></td>
                        <td><input type="number" name="product_price_' . esc_html($product_info->product_id) . '" value="' . esc_html($product_info->product_price) . '"></td>
                    </tr>
                </table>
                <button id="update-product" class="btn-update" data-product-id="' . esc_html($product_info->product_id) . '">
                    Actualizar
                </button>
            </div>
            ';
        }
    } else {
        $output .= '<div>Ningún producto encontrado</div>';
    }

    return $output;
}

if (isset($_GET['search_products'])) {
    echo get_products_html(sanitize_text_field($_GET['search_products']));
    exit;
}

function update_products($product_id, $product_name, $product_price)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_products';

    $sql = "UPDATE $table_name 
            SET product_name = %s, product_price = %d
            WHERE product_id = %d";

    return $wpdb->query($wpdb->prepare($sql, $product_name, $product_price, $product_id));
}

add_action('wp_ajax_call_update_products', 'handle_update_products');
add_action('wp_ajax_nopriv_call_update_products', 'handle_update_products');

function handle_update_products()
{
    if (isset($_POST['product_id'])) {

        $product_id = sanitize_text_field($_POST['product_id']);
        $product_name = sanitize_textarea_field($_POST['product_name']);
        $product_price = sanitize_text_field($_POST['product_price']);

        // Call the update delivery function
        $result = update_products($product_id, $product_name, $product_price);

        if ($result !== false) {
            wp_send_json_success('Product updated successfully.');
        } else {
            wp_send_json_error('Failed to update product.');
        }
    } else {
        wp_send_json_error('Invalid product ID.');
    }

    wp_die();
}


function create_products( $product_name, $product_price )
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_products';

    // Use $wpdb->insert() for cleaner and safer insertion
    $data = [
        'product_name' => $product_name,
        'product_price' => $product_price,
    ];
    
    $format = ['%s', '%d'];
    
    return $wpdb->insert($table_name, $data, $format);
}

add_action('wp_ajax_call_create_products', 'handle_create_products');
add_action('wp_ajax_nopriv_call_create_products', 'handle_create_products');

function handle_create_products() {
    if (isset($_POST['product_name']) && isset($_POST['product_price'])) {

        $product_name = sanitize_textarea_field($_POST['product_name']);
        $product_price = intval($_POST['product_price']);

        // Call the create delivery function
        $result = create_products( $product_name, $product_price );

        if ($result !== false) {
            wp_send_json_success('Product created successfully.');
        } else {
            wp_send_json_error('Failed to create product.');
        }
    } else {
        wp_send_json_error('Missing values');
    }

    wp_die();
}

