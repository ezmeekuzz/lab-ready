<?=$this->include('header');?>

<section class="jumbotron-container">
    <div class="jumbotron">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-12 col-md-12">
                    <h1 class="display-1 fw-bold">Register Here</h1>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cnc-machining mt-5 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="display-4 fw-bold text-black">Registration Form</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="register" class="border border-dark rounded p-4 shadow">
                        <div class="mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Phone Number</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 bg-black text-white p-3">Register</button>
                        <div class="col-12  mt-3">
                            <p>Already have an account ?<a href="./user/login"> Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?=$this->include('footer');?>
<script>
    $(document).ready(function() {
        $('#register').submit(function(event) {
            // Prevent default form submission
            event.preventDefault();

            // Get form data
            let fullname = $('#fullname').val();
            let email = $('#email').val();
            let password = $('#password').val();

            // Perform client-side validation
            if (fullname.trim() === '' || email.trim() === '' || password.trim() === '') {
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
                url: '/register/insert',
                data: $('#register').serialize(), // Serialize form data
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
                        $('#register')[0].reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Congrats!',
                            text: response.message,
                        }).then((result) => {
                            // Check if modal was closed
                            window.location.href = '/user/login';
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
</script>
