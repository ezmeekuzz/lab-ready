$(document).ready(function() {
    $('#sendquotation').submit(function(event) {
        // Prevent default form submission
        event.preventDefault();

        // Get form data
        let productName = $('#productname').val();
        let productPrice = $('#productprice').val();
        let invoiceFile = $('#invoicefile')[0].files[0];
        let userId = $('#user_id').val();

        // Perform client-side validation
        if (productName.trim() === '' || productPrice.trim() === '' || !invoiceFile || !userId) {
            // Show error using SweetAlert2
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fill in all the required fields!',
            });
            return;
        }

        // Prepare form data for AJAX submission
        let formData = new FormData();
        formData.append('productname', productName);
        formData.append('productprice', productPrice);
        formData.append('invoicefile', invoiceFile);
        formData.append('userId', userId);

        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: '/sendquotation/insert',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                // Show loading effect
                Swal.fire({
                    title: 'Sending...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                if (response.success) {
                    // Reset form upon successful submission
                    $('#sendquotation')[0].reset();
                    $('#user_id').trigger('chosen:updated');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message,
                    });
                }
            },
            error: function(xhr, status, error) {
                // Handle AJAX errors
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred while sending quotation. Please try again later.',
                });
                console.error(xhr.responseText);
            }
        });
    });
});
