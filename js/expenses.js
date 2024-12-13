// Wait until the DOM is fully loaded
jQuery(document).ready(function($) {

    // Delegated event for update expense
    $(document).on('click', '#update-expense', function() {
        var expenseId = $(this).data('expense-id');
        var paymentMethod = document.querySelector(`select[name="payment_method_${expenseId}"]`).value;
        var expenseDescription = document.querySelector(`textarea[name="expense_description_${expenseId}"]`).value;
        var amount = document.querySelector(`input[name="amount_${expenseId}"]`).value;
        
        if (expenseId && paymentMethod && expenseDescription && amount) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'call_update_expenses',
                    expense_id: expenseId,
                    expense_description: expenseDescription,
                    payment_method: paymentMethod,
                    amount: amount
                },
                success: function(response) {
                    if (response.success) {
                        alert('Gasto Actualizado!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error al actualizar la información del gasto.');
                }
            });
        } else {
            alert('Por favor llena todos los datos');
        }
    });

    // Delegated event for create expense
    $(document).on('click', '#create-expense', function() {
        var paymentMethod = document.querySelector(`select[name="payment_method_new"]`).value;
        var expenseDescription = document.querySelector(`textarea[name="expense_description_new"]`).value;
        var amount = document.querySelector(`input[name="expense_amount_new"]`).value;

        
        if ( expenseDescription && paymentMethod && amount && parseInt(amount) > 0 ) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'call_create_expenses',
                    expense_description: expenseDescription,
                    payment_method: paymentMethod,
                    amount: amount
                },
                success: function(response) {
                    if (response.success) {
                        alert('Gasto Creado!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error al crear el gasto.');
                }
            });
        } else {
            alert('Por favor llena todos los datos');
        }
    });

});

// SEARCH BAR
var search_expenses = document.getElementById('search_expenses');

// Add an event listener to prevent form submission on Enter key
if ( search_expenses ) {
    search_expenses.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission on Enter
        }
    });
}

if ( search_expenses ){
    search_expenses.addEventListener('input', function() {
        const searchValue = this.value;
    
        fetch(`../src/expenses.php?search_expenses=${encodeURIComponent(searchValue)}`)
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