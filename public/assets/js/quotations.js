$(document).ready(function () {
    $(document).on('click', '.quotationDetails', function () {
        let userQuotationId = $(this).data("id");
        let quotationId = $(this).data("quotation-id");
        let productAmount = $(this).data('amount');

        $.ajax({
            type: "GET",
            url: "/quotations/quotationDetails",
            data: { userQuotationId: userQuotationId },
            success: function (response) {
                // Access specific properties from the response object
                let quotationDate = moment(response.quotationdate).format('MMMM DD, YYYY');
                let invoiceFile = response.invoicefile; // Assuming you have a PDF URL property
                let productName = response.productname;
                let productPrice = response.productprice;

                // Format the content as HTML
                let htmlContent = '<div class="book-layout">';
                htmlContent += '<object data="' + invoiceFile + '" type="application/pdf" class="book-pdf" style="width:100%;height:500px;"></object>';
                htmlContent += '<div class="book-details mt-3">';
                htmlContent += '<div class="date mt-3"><strong>DATE:</strong> ' + quotationDate + '</div>';
                htmlContent += '<div class="date mt-3"><strong>Amount:</strong> ' + productPrice + '</div>';
                if (response.status === 'Unpaid') {
                    htmlContent += '<div class="row">';
                    htmlContent += '<div class="mb-3 mt-3 col-lg-12"><div id="paypalButton" class="form-group"></div><button type="button" class="btn btn-info p-3 w-100" id="chargeCreditCard"><img src="https://static.vecteezy.com/system/resources/previews/019/879/184/original/credit-cards-payment-icon-on-transparent-background-free-png.png" class="w-25" /> Credit Card Payment</button></div>';
                    htmlContent += '</div>';
                }
                htmlContent += '</div>'; // Close book-details div
                htmlContent += '</div>'; // Close book-layout div

                // Display the formatted content in the #displayDetails div
                $("#displayDetails").html(htmlContent);
                $("#productName").html('<h3><i class="fa fa-flag"></i> ' + productName + '</h3>');

                // Show the modal
                $("#quotationDetails").modal("show");

                // Render PayPal button after content is loaded
                paypal.Buttons({
                    createOrder: (data, actions) => {
                        if (userQuotationId !== '' && productAmount !== '') {
                            return actions.order.create({
                                purchase_units: [{
                                    amount: {
                                        value: productAmount,
                                        currency_code: 'USD'
                                    }
                                }]
                            });
                        } else {
                            Swal.fire({
                                title: 'Warning!',
                                text: 'Please fill up all of the required form!',
                                icon: 'warning',
                            });
                        }
                    },
                    onApprove: (data, actions) => {
                        return actions.order.capture().then(function (orderData) {
                            console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));
                            const transaction = orderData.purchase_units[0].payments.captures[0];
                            var transaction_number = transaction.id;

                            // Create FormData
                            var formData = new FormData();
                            formData.append('quotationId', quotationId);

                            // Send AJAX request
                            $.ajax({
                                type: "POST",
                                url: '/quotations/pay',
                                processData: false,
                                contentType: false,
                                data: formData,
                                success: function (data) {
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: data.message,
                                            icon: 'success',
                                            willClose: () => {
                                                window.location.href = "/quotations";
                                            }
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Failed!',
                                            text: data.message,
                                            icon: 'error',
                                            willClose: () => {
                                                window.location.href = "/quotations";
                                            }
                                        });
                                    }
                                }
                            });
                        });
                    },

                    onCancel: function (data) {
                        Swal.fire({
                            title: 'Warning!',
                            text: 'You cancelled your payment!',
                            icon: 'warning',
                        });
                    }
                }).render('#paypalButton');

                // Add event listener for the "chargeCreditCard" button
                $(document).on('click', '#chargeCreditCard', function () {
                    Swal.fire({
                        title: 'Credit Card Payment',
                        html:
                            '<label>Amount:</label><input type="text" id="amount" name="amount" class="form-control" value="' + productAmount + '" readonly><br>' +
                            '<label>Card Number:</label><input type="text" id="card_number" name="card_number" class="form-control" required><br>' +
                            '<label>Expiration Date (YYYY-MM):</label><input type="text" id="expiration_date" name="expiration_date" class="form-control" required><br>' +
                            '<label>CVV:</label><input type="text" id="cvv" name="cvv" class="form-control" required><br>',
                        focusConfirm: false,
                        preConfirm: () => {
                            const cardNumber = Swal.getPopup().querySelector('#card_number').value;
                            const expirationDate = Swal.getPopup().querySelector('#expiration_date').value;
                            const cvv = Swal.getPopup().querySelector('#cvv').value;
                
                            if (!cardNumber || !expirationDate || !cvv) {
                                Swal.showValidationMessage(`Please fill out all fields`);
                            }
                
                            return {
                                cardNumber: cardNumber,
                                expirationDate: expirationDate,
                                cvv: cvv
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                type: "POST",
                                url: '/quotations/chargeCreditCard',
                                data: {
                                    amount: productAmount,
                                    card_number: result.value.cardNumber,
                                    expiration_date: result.value.expirationDate,
                                    cvv: result.value.cvv
                                },
                                success: function (data) {
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: 'Payment was successful!',
                                            icon: 'success',
                                            willClose: () => {
                                                window.location.reload();
                                            }
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Payment Failed!',
                                            text: data.message,
                                            icon: 'error'
                                        });
                                    }
                                },
                                error: function (xhr, status, error) {
                                    Swal.fire({
                                        title: 'Payment Failed!',
                                        text: xhr.responseJSON.message || 'There was an error processing your payment.',
                                        icon: 'error'
                                    });
                                }
                            });
                        }
                    });
                });                
            },
            error: function () {
                console.error("Error fetching data");
            }
        });
    });

    function fetchData() {
        $.ajax({
            type: "GET",
            url: "/quotations/getData",
            success: function (response) {
                $("#card-columns").empty();
                if(response.length === 0) {
                    $("#noQuotationsMessage").show(); // Show the message if no quotations
                } else {
                    $("#noQuotationsMessage").hide(); // Hide the message if there are quotations
                    response.forEach(function (item) {
                        var cardHTML = `
                            <div class="col-xl-3 col-sm-6">
                                <div class="card card-statistics position-relative">
                                    <div class="delete-btn">
                                        <button class="btn btn-danger delete-quotation" data-id="${item.user_quotation_id}">Delete</button>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="mr-3">
                                                <img src="../assets/img/file-icon/pdf.png" alt="png-img">
                                            </div>
                                            <div>
                                                <h4 class="mb-2">${item.productname}</h4>
                                                <p class="mb-2"><span style="font-weight: bold; color: red;">Price : ${item.productprice}</span></p>
                                                <p class="mb-2"><span style="font-weight: bold; color: blue;">Date : ${item.quotationdate}</span></p>
                                                <a href="javascript:void(0)" class="btn btn-light quotationDetails" data-quotation-id="${item.quotation_id}" data-id="${item.user_quotation_id}" data-amount="${item.productprice}">Open</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $("#card-columns").append(cardHTML);
                    });
                }
            },
            error: function () {
                console.error("Error fetching data");
            }
        });
    }

    fetchData();

    $(document).on('click', '.delete-quotation', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/quotations/delete/' + id,
                    method: 'DELETE',
                    success: function (response) {
                        if (response.status === 'success') {
                            fetchData();
                        } else {
                            // Handle unsuccessful deletion
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            });
                        }
                    },
                    error: function () {
                        // Handle AJAX request error
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong with the request!',
                        });
                    }
                });
            }
        });
    });
    $.fn.modal.Constructor.prototype._enforceFocus = function() {};
});