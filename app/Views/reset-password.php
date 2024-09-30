<?=$this->include('header');?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<section class="jumbotron-container">
    <div class="jumbotron">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-12 col-md-12">
                    <h1 class="display-1 fw-bold">Reset Password</h1>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cnc-machining mt-5 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5">
                <h2 class="display-4 fw-bold text-black">Reset Password</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <form id="resetpassword" class="border border-dark rounded p-4 shadow">
                        <input type="hidden" name="token" value="<?= $token ?>">
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="cpassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="cpassword" name="cpassword" required>
                        </div>
                        <div class="g-recaptcha mb-3" data-sitekey="6LeJO_ApAAAAAKjH-ats7ZeBaHnW7s3U2HFePpS1"></div>
                        <button type="submit" class="btn btn-primary w-100 bg-black text-white p-3">Submit</button>
                        <div class="col-12 mt-3">
                            <p>Already have an account? <a href="./user/login">Login</a></p>
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
        $('#resetpassword').submit(function(event) {
            event.preventDefault();

            // Validate passwords
            let password = $('#password').val();
            let cpassword = $('#cpassword').val();
            
            if (password !== cpassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Password and Confirm Password do not match!',
                });
                return;
            }

            // Check reCAPTCHA response
            let captchaResponse = grecaptcha.getResponse();
            if (captchaResponse.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please complete the reCAPTCHA!',
                });
                return;
            }

            // Serialize form data with reCAPTCHA response
            let formData = $(this).serialize() + '&g-recaptcha-response=' + captchaResponse;

            $.ajax({
                type: 'POST',
                url: '/resetpassword/reset', // Adjust the URL to match your CI4 route
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Resetting password...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    grecaptcha.reset(); // Reset reCAPTCHA
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                        }).then(() => {
                            // Reset the form after successful submission
                            $('#resetpassword')[0].reset();
                            window.location.href = '/user/login'; // Redirect to login page
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    grecaptcha.reset(); // Reset reCAPTCHA
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again later.',
                    });
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
