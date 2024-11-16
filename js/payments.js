// Wait until the DOM is fully loaded
jQuery(document).ready(function($) {

    // Delegated event for update payment
    $(document).on('click', '#update-payment', function() {
        var paymentId = $(this).data('payment-id');
        var nameElement = document.querySelector(`input[name="name_${paymentId}"]`).value;
        var phoneElement = paymentId;
        var addressElement = document.querySelector(`textarea[name="address_${paymentId}"]`).value;
        var ageElement = document.querySelector(`input[name="age_${paymentId}"]`).value;
        var genreElement = document.querySelector(`select[name="genre_${paymentId}"]`).value;
        
        if (nameElement && phoneElement && addressElement && ageElement && genreElement) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'call_update_payments',
                    payment_id: paymentId,
                    phone: phoneElement,
                    name: nameElement,
                    address: addressElement,
                    age: ageElement,
                    genre: genreElement
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
        var nameElement = document.querySelector(`input[name="name_new"]`).value;
        var phoneElement = document.querySelector(`input[name="phone_new"]`).value;
        var addressElement = document.querySelector(`textarea[name="address_new"]`).value;
        var ageElement = document.querySelector(`input[name="age_new"]`).value;
        var genreElement = document.querySelector(`select[name="genre_new"]`).value;
        
        if (nameElement && phoneElement && addressElement && ageElement && genreElement) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'call_create_payments',
                    phone: phoneElement,
                    name: nameElement,
                    address: addressElement,
                    age: ageElement,
                    genre: genreElement
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
            alert('Por favor llena todos los datos');
        }
    });
});

// SEARCH BAR
// Get the search input and form elements
const searchInput = document.getElementById('search_payments');
const searchForm = document.getElementById('searchForm');

// Add an event listener to prevent form submission on Enter key
searchInput.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Prevent form submission on Enter
    }
});
document.getElementById('search_payments').addEventListener('input', function() {
    const searchValue = this.value;

    fetch(`../src/payments.php?search_payments=${encodeURIComponent(searchValue)}`)
            .then(response => response.text())
            .then(data => {
                // Display the results in the #results div
                document.getElementById('results').innerHTML = data;
                activateAccordionFunctionality();
            })
            .catch(error => console.error('Error:', error));
});
// END OF SEARCH BAR