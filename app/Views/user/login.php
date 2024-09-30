<!DOCTYPE html>
<html lang="en">
<head>
    <title><?=$title;?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="We have on-site capabilities to tweak as needed or to aid in assembly. These services can also be quoted a la' carte for your existing parts. Contact us for details." />
    <meta content="Rustom Codilan" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="icon" href="<?=base_url();?>images/favicon.jpeg" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?=base_url();?>assets/css/vendors.css" />
    <link rel="stylesheet" type="text/css" href="<?=base_url();?>assets/css/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        .login {
            background-color: rgba(255, 255, 255, 0.8); /* Add transparency to the background */
            border-radius: 10px; /* Add rounded corners */
            padding: 20px; /* Add some padding to the login form */
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1); /* Add a subtle shadow */
        }
        label {
            color: #000 !important;
        }
        @media (max-width: 768px) {
            .my-custom-flex-container {
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }
        }
        body {
            background: url('<?=base_url();?>images/AAL2.png') center center no-repeat;
            background-size: cover;
        }
    </style>
</head>
<body class="bg-dark">
    <div class="app">
        <div class="app-wrap">
            <div class="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="<?=base_url();?>assets/img/loader/loader.gif" alt="loader">
                    </div>
                </div>
            </div>
            <div class="app-contant">
                <div>
                    <div class="container-fluid p-0">
                        <div class="row no-gutters">
                            <div class="col-lg-12 d-flex align-items-center justify-content-center">
                                <div class="d-flex flex-column align-items-center justify-content-center h-100-vh my-custom-flex-container">
                                    <div class="login p-50">
                                        <p>Welcome back, please login to your account.</p>
                                        <form id="signIn" class="mt-3 mt-sm-5">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Email Address*</label>
                                                        <input type="text" class="form-control" name="email" id="email" placeholder="email" />
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="control-label">Password*</label>
                                                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" />
                                                    </div>
                                                </div>
                                                <div class="col-12 mt-3">
                                                    <div class="g-recaptcha mb-3" data-sitekey="6LeJO_ApAAAAAKjH-ats7ZeBaHnW7s3U2HFePpS1"></div>
                                                    <div class="d-block d-sm-flex  align-items-center">
                                                        <a href="../forgot-password" class="ml-auto">Forgot Password ?</a>
                                                    </div>
                                                    <button type="submit" class="btn btn-dark text-uppercase">Sign In</button>
                                                </div>
                                            </div>
                                            <input type="hidden" name="redirect" id="redirect" value="">
                                        </form>
                                        <div class="row">
                                            <div class="col-12 mt-3">
                                                <p>Don't have an account ?<a href="../register">Register</a></p>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <p><a href="../forgot-password">Forgot Password?</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?=base_url();?>assets/js/vendors.js"></script>
    <script src="<?=base_url();?>assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            // Check if the current URL has the redirect parameter set to "quote"
            const urlParams = new URLSearchParams(window.location.search);
            $('#redirect').val(urlParams.get('redirect'));

            $('#signIn').submit(function(event) {
                // Prevent default form submission
                event.preventDefault();
                // Check if reCAPTCHA is filled
                let captchaResponse = grecaptcha.getResponse();
                if (captchaResponse.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please complete the reCAPTCHA!',
                    });
                    return;
                }
                // Get form data
                var email = $('#email').val();
                var password = $('#password').val();

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
                    url: '<?= base_url('user/authenticate'); ?>',
                    data: $('#signIn').serialize(), // Serialize form data
                    dataType: 'json',
                    beforeSend: function() {
                        // Show loading effect
                        Swal.fire({
                            title: 'Logging In...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        if (response.success) {
                            // Redirect upon successful login
                            Swal.fire({
                                icon: 'success',
                                title: 'Logged In',
                                text: response.message,
                                timer: 1000, // Display message for 3 seconds
                                timerProgressBar: true,
                                showConfirmButton: false // Hide the "OK" button
                            }).then((result) => {
                                // Redirect after Swal alert is closed
                                if (result.dismiss === Swal.DismissReason.timer) {
                                    window.location.href = response.redirect;
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
    </script>
</body>
</html>
