
            <footer class="footer">
                <div class="row">
                    <div class="col-12 col-sm-6 text-center text-sm-left">
                        <p>&copy; Copyright <?=date('Y');?>. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="<?=base_url();?>assets/js/vendors.js"></script>
    <script src="<?=base_url();?>assets/js/app.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input@1.3.4/dist/bs-custom-file-input.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://www.paypal.com/sdk/js?client-id=AUGQnX6C6HQFTWNwl7T886HC_EoOHpltUlAphlFNy-I6AaUObwheXJ4qO7qNYdKJRGtRDDYZlHyl0ufT&disable-funding=credit,card"></script>
    <script src="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone-min.js"></script>
    <script>
        $(".chosen-select").chosen({ 
            maxHeight: "400px" 
        });
    </script>
</body>
</html>