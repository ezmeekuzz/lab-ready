<?=$this->include('header');?>
<section class="jumbotron-container">
    <div class="jumbotron">
        <div class="overlay"></div>
        <div class="content">
            <h1 class="display-1 fw-bold">Contact Us</h1>
        </div>
    </div>
</section>
<section class="contact-content bg-black p-5 text-white" style="display: flex; justify-content: center;">
    <div class="container">
        <div class="row mt-3">
            <div class="col-lg-6 col-md-12 mx-auto">
                <!--<h1 class="display-4 fw-bold">The Results Are Real</h1>
                <p class="fs-5">For any inquiries, questions or commendations fill out the following form</p>-->
                <p class="fs-5"><i class="fa fa-phone"></i> 662-910-9173</p>
                <form id="sendMessage" class="mt-4">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group mb-3">
                                <input type="text" class="form-control p-2 rounded-1 bg-transparent text-white" placeholder="Full Name" id="fullName" name="fullName" value="<?=session()->get('user_fullname');?>">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group mb-3">
                                <input type="email" class="form-control p-2 rounded-1 bg-transparent text-white" placeholder="Email Address" id="email" name="email" value="<?=session()->get('user_email');?>">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group mb-3">
                                <input type="text" class="form-control p-2 rounded-1 bg-transparent text-white" placeholder="Company Name" id="companyName" name="companyName" value="<?=session()->get('user_companyname');?>">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group mb-3">
                                <input type="tel" class="form-control p-2 rounded-1 bg-transparent text-white" placeholder="Phone Number" id="phoneNumber" name="phoneNumber" value="<?=session()->get('user_phonenumber');?>">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group mb-3">
                                <textarea class="form-control p-2 rounded-1 bg-transparent text-white" placeholder="Message" id="message" name="message" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <!--<div class="g-recaptcha mb-3" data-sitekey="6LehBM0pAAAAAMk28KGdwuhB3XjbuJ0w3er0gTuj"></div>-->
                        <button type="submit" class="btn btn-lg btn-primary bg-light w-100 btn-outline-light text-black text-uppercase rounded-1">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<?=$this->include('footer');?>
<script>
    $(document).ready(function() {
        $('#sendMessage').submit(function(event) {
            // Prevent default form submission
            event.preventDefault();

            // Get form data
            let fullName = $('#fullName').val();
            let email = $('#email').val();
            let phoneNumber = $('#phoneNumber').val();
            let companyName = $('#companyName').val();
            let message = $('#message').val();
            // Perform client-side validation
            if (fullName.trim() === '' || email.trim() === '' || phoneNumber.trim() === '' || companyName.trim() === '' || message.trim() === '') {
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
                url: '/contactus/sendMessage',
                data: $('#sendMessage').serialize(), // Serialize form data
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
                        $('#sendMessage')[0].reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Message Forwarded',
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
</script>