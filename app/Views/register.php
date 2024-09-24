<?=$this->include('header');?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
                            <label for="phonenumber" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phonenumber" name="phonenumber">
                        </div>
                        <div class="mb-3">
                            <label for="companyname" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="companyname" name="companyname">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address">
                        </div>
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        <div class="mb-3">
                            <label for="state" class="form-label">State</label>
                            <select name="state" id="state" class="form-control">
                                <option hidden></option>
                                <option disabled selected>Select a State</option>
                                <option value="AL">AL</option>
                                <option value="AK">AK</option>
                                <option value="AZ">AZ</option>
                                <option value="AR">AR</option>
                                <option value="CA">CA</option>
                                <option value="CO">CO</option>
                                <option value="CT">CT</option>
                                <option value="DE">DE</option>
                                <option value="FL">FL</option>
                                <option value="GA">GA</option>
                                <option value="HI">HI</option>
                                <option value="ID">ID</option>
                                <option value="IL">IL</option>
                                <option value="IN">IN</option>
                                <option value="IA">IA</option>
                                <option value="KS">KS</option>
                                <option value="KY">KY</option>
                                <option value="LA">LA</option>
                                <option value="ME">ME</option>
                                <option value="MD">MD</option>
                                <option value="MA">MA</option>
                                <option value="MI">MI</option>
                                <option value="MN">MN</option>
                                <option value="MS">MS</option>
                                <option value="MO">MO</option>
                                <option value="MT">MT</option>
                                <option value="NE">NE</option>
                                <option value="NV">NV</option>
                                <option value="NH">NH</option>
                                <option value="NJ">NJ</option>
                                <option value="NM">NM</option>
                                <option value="NY">NY</option>
                                <option value="NC">NC</option>
                                <option value="ND">ND</option>
                                <option value="OH">OH</option>
                                <option value="OK">OK</option>
                                <option value="OR">OR</option>
                                <option value="PA">PA</option>
                                <option value="RI">RI</option>
                                <option value="SC">SC</option>
                                <option value="SD">SD</option>
                                <option value="TN">TN</option>
                                <option value="TX">TX</option>
                                <option value="UT">UT</option>
                                <option value="VT">VT</option>
                                <option value="VA">VA</option>
                                <option value="WA">WA</option>
                                <option value="WV">WV</option>
                                <option value="WI">WI</option>
                                <option value="WY">WY</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="g-recaptcha mb-3" data-sitekey="6LeJO_ApAAAAAKjH-ats7ZeBaHnW7s3U2HFePpS1"></div>
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
            let formData = $(this).serialize() + '&g-recaptcha-response=' + captchaResponse;

            $.ajax({
                type: 'POST',
                url: '/register/insert',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    Swal.fire({
                        title: 'Saving...',
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
                            title: 'Congrats!',
                            text: response.message,
                        }).then((result) => {
                            window.location.href = '/user/login';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    grecaptcha.reset(); // Reset reCAPTCHA
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'An error occurred while registering. Please try again later.',
                    });
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
