<?php


/**
 * Function that shows the orders via shortcode
*/
function boms_shortcode_orders() {

    $output = '<div class="boms-page"> ' . get_side_menu() . '
        <div class="main-content">';

    $data = get_orders();
    if ( ! empty( $data ) ) {
        foreach ( $data as $item ) {
            $client_info = get_client_information( esc_html( $item->client_id ) );

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
                <button id="update-order" class="btn-update" data-order-id="' . esc_html( $item->order_id ) .'">
                    Actualizar
                </button>';
            }
            $output .= '</div>';
        }
    } else {
        $output .= 'No hay ordenes';
    }
    $output .= '</div></div>';

    return $output;
}

/**
 * This function runs the select to get the non-delivered orders from the database
 */
function get_orders() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'boms_orders';

    $sql = "SELECT * FROM $table_name ORDER BY delivered ASC, delivery_day ASC";

    return $wpdb->get_results( $sql );
}