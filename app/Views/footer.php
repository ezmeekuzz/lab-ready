        <section class="footer-section bg-dark text-white py-3 d-flex flex-column justify-content-center align-items-center">
            <div class="container-fluid">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-4 mb-3 mb-lg-0">
                            <h5 class="text-uppercase display-5 fw-bold">Stay Connected!</h5>
                            <p class="fs-5">Join Our Email List</p>
                        </div>
                        <div class="col-lg-4 mb-3 mb-lg-0">
                            <h5 class="text-uppercase fw-bold">Policy Links</h5>
                            <ul class="list-unstyled">
                                <li class="mb-3"><a href="/privacy-policy" class="text-white text-decoration-none">Privacy Policy</a></li>
                                <li class="mb-3"><a href="/product-pricing" class="text-white text-decoration-none">Product Pricing</a></li>
                                <li class="mb-3"><a href="/refund-and-cancellation-policy" class="text-white text-decoration-none">Refund and Cancellation Policy</a></li>
                                <li class="mb-3"><a href="/terms-and-conditions" class="text-white text-decoration-none">Terms and Conditions</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-4">
                        <form id="subscribe" class="d-flex flex-column">
                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <input type="email" class="form-control p-3 rounded" name="emailaddress" id="emailaddress" placeholder="Enter your email">
                                </div>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary bg-black w-100 btn-outline-light p-3">Get Updates</button>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <footer class="p-3 bg-black text-white">
            <div class="container text-center">
                <p class="mb-0">&copy; <?=date('Y');?> LAB READY. All Rights Reserved.</p>
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
	    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <script>
            window.onload = function() {
                var footer = document.querySelector('.footer-section');
                var img = new Image();
                img.src = './images/footer-image.webp';
                img.onload = function() {
                    footer.style.height = img.height + 'px';
                };
            };
        </script>
        <script>
            $(document).ready(function() {
                $('#subscribe').submit(function(event) {
                    // Prevent default form submission
                    event.preventDefault();

                    // Get form data
                    let emailaddress = $('#emailaddress').val();

                    // Perform client-side validation
                    if (emailaddress.trim() === '') {
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
                        url: '/subscribers/insert',
                        data: $('#subscribe').serialize(), // Serialize form data
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
                                $('#subscribe')[0].reset();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Congrats!',
                                    text: response.message,
                                }).then((result) => {
                                    // Check if modal was closed
                                    location.reload();
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
