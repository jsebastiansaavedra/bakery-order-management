// Wait until the DOM is fully loaded
jQuery(document).ready(function($) {

    // Delegated event for update client
    $(document).on('click', '#update-client', function() {
        var clientId = $(this).data('client-id');
        var nameElement = document.querySelector(`input[name="name_${clientId}"]`).value;
        var phoneElement = clientId;
        var addressElement = document.querySelector(`textarea[name="address_${clientId}"]`).value;
        var ageElement = document.querySelector(`input[name="age_${clientId}"]`).value;
        var genreElement = document.querySelector(`select[name="genre_${clientId}"]`).value;
        
        if (nameElement && phoneElement && addressElement && ageElement && genreElement) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'call_update_clients',
                    client_id: clientId,
                    phone: phoneElement,
                    name: nameElement,
                    address: addressElement,
                    age: ageElement,
                    genre: genreElement
                },
                success: function(response) {
                    if (response.success) {
                        alert('Cliente Actualizado!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error al actualizar la información del cliente.');
                }
            });
        } else {
            alert('Por favor llena todos los datos');
        }
    });

    // Delegated event for create client
    $(document).on('click', '#create-client', function() {
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
                    action: 'call_create_clients',
                    phone: phoneElement,
                    name: nameElement,
                    address: addressElement,
                    age: ageElement,
                    genre: genreElement
                },
                success: function(response) {
                    if (response.success) {
                        alert('Cliente Creado!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error al crear el cliente.');
                }
            });
        } else {
            alert('Por favor llena todos los datos');
        }
    });
});

// SEARCH BAR
// Get the search input and form elements
const searchInput = document.getElementById('search_clients');
const searchForm = document.getElementById('searchForm');

// Add an event listener to prevent form submission on Enter key
searchInput.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        event.preventDefault(); // Prevent form submission on Enter
    }
});
document.getElementById('search_clients').addEventListener('input', function() {
    const searchValue = this.value;

    fetch(`../src/clients.php?search_clients=${encodeURIComponent(searchValue)}`)
            .then(response => response.text())
            .then(data => {
                // Display the results in the #results div
                document.getElementById('results').innerHTML = data;
                activateAccordionFunctionality( true );
            })
            .catch(error => console.error('Error:', error));
});
// END OF SEARCH BAR