// Wait until the DOM is fully loaded
jQuery(document).ready(function($) {

    // Delegated event for update product
    $(document).on('click', '#update-product', function() {
        var productId = $(this).data('product-id');
        var productName = document.querySelector(`textarea[name="product_name_${productId}"]`).value;
        var productPrice = document.querySelector(`input[name="product_price_${productId}"]`).value;
        
        if (productId && productName && productPrice) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'call_update_products',
                    product_id: productId,
                    product_name: productName,
                    product_price: productPrice
                },
                success: function(response) {
                    if (response.success) {
                        alert('Producto Actualizado!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error al actualizar la información del producto.');
                }
            });
        } else {
            alert('Por favor llena todos los datos');
        }
    });

    // Delegated event for create product
    $(document).on('click', '#create-product', function() {
        var productName = document.querySelector(`textarea[name="product_name_new"]`).value;
        var productPrice = document.querySelector(`input[name="product_price_new"]`).value;

        
        if ( productName && productPrice && parseInt(productPrice) > 0 ) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'call_create_products',
                    product_name: productName,
                    product_price: productPrice
                },
                success: function(response) {
                    if (response.success) {
                        alert('Producto Creado!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error al crear el producto.');
                }
            });
        } else {
            alert('Por favor llena todos los datos');
        }
    });

});

// SEARCH BAR
var search_products = document.getElementById('search_products');

// Add an event listener to prevent form submission on Enter key
if ( search_products ) {
    search_products.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission on Enter
        }
    });
}

if ( search_products ){
    search_products.addEventListener('input', function() {
        const searchValue = this.value;
    
        fetch(`../src/products.php?search_products=${encodeURIComponent(searchValue)}`)
                .then(response => response.text())
                .then(data => {
                    // Display the results in the #results div
                    document.getElementById('results').innerHTML = data;
                    activateAccordionFunctionality( true );
                })
                .catch(error => console.error('Error:', error));
    });
}
// END OF SEARCH BAR