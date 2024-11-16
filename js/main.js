
jQuery(document).ready(function($) {
    
    activateAccordionFunctionality();

    // Balance logic to show the number in red if there's a debt
    // Select all elements with the class 'balance'
    const balances = document.querySelectorAll('.balance');

    // Loop through each element
    balances.forEach(function(balance) {
        // Get the content of the element, convert it to a number
        let value = parseFloat(balance.textContent);
        
        // Check if the value is less than 0
        if (value < 0) {
            // Set the text color to red
            balance.style.color = 'red';
        } else {
            // Set the text color to green
            balance.style.color = 'green';
        }
    });

});

// Ajax to update delivery
jQuery('#mark-as-delivered').on('click', function() {
    var delivery = document.getElementById('deliveryDropdown').value;

    if ( delivery != "") {
        var orderId = jQuery(this).data('order-id');
    
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'call_mark_as_delivered',
                order_id: orderId,
                delivery: delivery
            },
            success: function(response) {
                if (response.success) {
                    alert('Pedido marcado como entregado!');
                    window.location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Error al actualizar el pedido.');
            }
        });
    }else {
        alert('Seleccione un tipo de domicilio');
    }
});

function activateAccordionFunctionality() {
    /**
     * Accordion functionality
     */
    var acc = document.getElementsByClassName("items_accordion");

    for (var i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.display === "block") {
                panel.style.display = "none";
            } else {
                panel.style.display = "block";
            }
        });
    }
}