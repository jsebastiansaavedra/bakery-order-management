// Wait until the DOM is fully loaded
jQuery(document).ready(function($) {

    // Delegated event for update payment
    $(document).on('click', '#update-payment', function() {
        var paymentId = $(this).data('payment-id');
        var paymentMethod = document.querySelector(`select[name="payment_method_${paymentId}"]`).value;
        
        if (paymentId && paymentMethod) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'call_update_payments',
                    payment_id: paymentId,
                    payment_method: paymentMethod
                },
                success: function(response) {
                    if (response.success) {
                        alert('Pago Actualizado!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error al actualizar la información del pago.');
                }
            });
        } else {
            alert('Por favor llena todos los datos');
        }
    });

    // Delegated event for create payment
    $(document).on('click', '#create-payment', function() {
        var orderId = document.querySelector(`select[name="payment_order_new"]`).value;
        var paymentMethod = document.querySelector(`select[name="payment_method_new"]`).value;
        var amount = document.querySelector(`input[name="payment_amount_new"]`).value;
        var balance = document.querySelector(`input[order-id="${orderId}"]`).value;
        
        if ( orderId && paymentMethod && amount && parseInt(amount) > 0 ) {
            if ( parseInt(amount) <= parseInt(balance) ) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'call_create_payments',
                        order_id: orderId,
                        payment_method: paymentMethod,
                        amount: amount
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Pago Creado!');
                            window.location.reload();
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Error al crear el pago.');
                    }
                });
            } else {
                alert('La deuda por esta orden es menor al valor ingresado');
                document.querySelector(`input[name="payment_amount_new"]`).value = "";
            }
            
        } else {
            alert('Por favor llena todos los datos');
        }
    });

    // ON CHANGE ORDER
    // Get the dropdown element
    const paymentOrderDropdown = document.getElementById("paymentOrderDropdown");

    // Add a change event listener
    paymentOrderDropdown.addEventListener("change", function() {
        // Get the order id
        const orderId = this.value;

        // Get the container element to show the debt
        var debtContainer = document.querySelector(".debt-container");

        // Clear the payment method dropdown
        document.querySelector(`select[name="payment_method_new"]`).value = "";

        if ( orderId != 0 ) {
            // Show the container
            debtContainer.style.display = "block";

            // Get the balance
            var balance = parseInt(document.querySelector(`input[order-id="${orderId}"]`).value);

            // Add the full value by default
            document.querySelector(`input[name="payment_amount_new"]`).value = balance;

            // Format the balance as a number with thousands separators
            var formattedBalance = new Intl.NumberFormat('en-US').format(balance);

            // Update the balance span
            document.getElementById("debt-value").textContent = `-${formattedBalance}`;
        } else {
            debtContainer.style.display = "none";
        }

        

        
    });
    // END OF ON CHANGE ORDER
});

// SEARCH BAR
var search_payments = document.getElementById('search_payments');

// Add an event listener to prevent form submission on Enter key
if ( search_payments ) {
    search_payments.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission on Enter
        }
    });
}

if ( search_payments ){
    search_payments.addEventListener('input', function() {
        const searchValue = this.value;
    
        fetch(`../src/payments.php?search_payments=${encodeURIComponent(searchValue)}`)
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