$(document).ready(function() {
    $('#userinfo').submit(function(event) {
        // Prevent default form submission
        event.preventDefault();

        // Get form data
        let fullname = $('#fullname').val();
        let email = $('#email').val();
        let companyname = $('#companyname').val();
        let phonenumber = $('#phonenumber').val();
        let address = $('#address').val();
        let state = $('#state').val();
        let city = $('#city').val();
        let password = $('#password').val();
        // Perform client-side validation
        if (fullname.trim() === '' || email.trim() === '' || password.trim() === '' || companyname.trim() === '' || phonenumber.trim() === '' || address.trim() === '' || state.trim() === '' || city.trim() === '') {
            // Show error using SweetAlert2
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fill in the required fields!',
            });
            return;
        }

        // Send AJAX request
        $.ajax({
            type: 'POST',
            url: '/userinfo/update',
            data: $('#userinfo').serialize(), // Serialize form data
            dataType: 'json',
            beforeSend: function() {
                // Show loading effect
                Swal.fire({
                    title: 'Saving...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                if (response.success) {
                    // Redirect upon successful login
                    $('#userinfo')[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Data Save',
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
                    text: 'An error occurred while logging in. Please try again later.',
                });
                console.error(xhr.responseText);
            }
        });
    });
});