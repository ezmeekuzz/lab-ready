$(document).ready(function() {
    $('#sigup-form').submit(function(event) {
        // Prevent default form submission
        event.preventDefault();

        // Get form data
        let email = $('#email').val();
        let password = $('#password').val();
        // Perform client-side validation
        if (email.trim() === '' || password.trim() === '') {
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
            url: '/login/authenticate',
            data: $('#sigup-form').serialize(), // Serialize form data
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
                    // Redirect upon successful login
                    $('#sigup-form')[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Logged In',
                        text: response.message,
                        timer: 3000, // Display message for 5 seconds
                        timerProgressBar: true,
                        showConfirmButton: false // Hide the "OK" button
                    }).then((result) => {
                        // Redirect after Swal alert is closed
                        if (result.dismiss === Swal.DismissReason.timer) {
                            window.location.href = "/dashboard";
                        }
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