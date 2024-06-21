$(document).ready(function () {
    let table = $('#requestquotationmasterlist').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/requestquotationmasterlist/getData",
            "type": "POST"
        },
        "columns": [
            { 
                "data": "uid",
                "visible": false // Hide the User ID column
            },
            { "data": "fullname" },
            { "data": "email" },
            {
                "data": "status",
                "render": function (data) {
                    let statusClass = '';
                    if (data === 'Pending') {
                        statusClass = 'badge badge-warning';
                    } else if (data === 'Done') {
                        statusClass = 'badge badge-success';
                    }
                    return `<span class="${statusClass}">${data}</span>`;
                }
            },
            { "data": "datesubmitted" },
            {
                "data": null,
                "orderable": false,
                "render": function (data, type, row) {
                    return `
                        <a href="#" title="Quotation List" class="quotation-list" data-id="${row.request_quotation_id}" style="color: orange;">
                            <i class="fa fa-file-text" style="font-size: 18px;"></i>
                        </a>
                        <a href="#" title="Update Status" class="update-status" data-id="${row.request_quotation_id}" style="color: green;">
                            <i class="ti ti-pencil" style="font-size: 18px;"></i>
                        </a>
                        <a href="./${row.file_location}" download title="Download File" style="color: blue;">
                            <i class="ti ti-download" style="font-size: 18px;"></i>
                        </a>`;
                }
            }
        ],
        "createdRow": function (row, data) {
            $(row).attr('data-id', data.request_quotation_id);

            $('td', row).each(function (index) {
                if (index !== 5) { // Assuming the actions column is at index 5
                    $(this).attr('data-user-id', data.uid);
                }
            });
        },
        "initComplete": function () {
            $(this).trigger('dt-init-complete');
        }
    });

    $('#requestquotationmasterlist tbody').on('click', 'td', function () {
        let cell = table.cell(this);
        let cellIndex = cell.index().column;

        if (cellIndex === 5) { // If the cell index is the actions column, do nothing
            return;
        }

        let userId = $(this).data('user-id');
        let requestQuotationId = $(this).closest('tr').data('id');

        $('#user_id').val(userId);
        $('#request_quotation_id').val(requestQuotationId);

        $('#quotationModal').modal('show');
    });    
    function initializeStlViewer(stlContainer, stlLocation) {
        // Initialize StlViewer with the provided container and STL file location
        new StlViewer(stlContainer, {
            // Provide the STL file location
            models: [{
                filename: stlLocation
            }],
            // Configure canvas settings
            canvasConfig: {
                antialias: true, // Enable antialiasing for smoother edges
                quality: 'high' // Set rendering quality to high
            },
            // Render the model as solid
            solid: true,
            // Enable rotation of the model
            rotate: true,
            // Automatically resize the viewer based on container size
            autoResize: true,
            // Add light sources for better visibility
            lights: [
                { dir: [1, 1, 1], color: [1, 1, 1] }, // White light from one direction
                { dir: [-1, -1, -1], color: [0.5, 0.5, 0.5] } // Dim light from the opposite direction
            ],
            // Set initial pan position
            pan: [0, 0] // Center the model initially
        });
    }
    $(document).on('click', '.quotation-list', function (e) {
        e.preventDefault();
    
        let requestQuotationId = $(this).data('id');
    
        // Fetch quotation list data via AJAX
        $.ajax({
            url: '/requestquotationmasterlist/getQuotationList/' + requestQuotationId,
            method: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    // Clear previous content
                    $('#quotationContainer').empty();
    
                    // Iterate over each item in the response data
                    response.data.forEach(item => {
                        // Create unique IDs for elements
                        let stlContId = 'stlCont_' + item.quotation_item_id;
                        let partNumberId = 'partNumber_' + item.quotation_item_id;
                        let quoteTypeId = 'quotetype_' + item.quotation_item_id;
                        let materialId = 'material_' + item.quotation_item_id;
                        let quantityId = 'quantity_' + item.quotation_item_id;
                        let downloadBTN = "";
                        if (item.print_location !== null) {
                            downloadBTN = `<a href="${item.print_location}" download class="btn bg-dark text-white"><i class="fa fa-download"></i> Download Print File</a>`;
                        }
                        // Create HTML for each item
                        let itemHtml = `
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-6 mb-5">
                                                <div id="${stlContId}" style="height: 250px;"></div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group" hidden>
                                                    <input type="text" class="form-control" name="quotation_item_id" value="${item.quotation_item_id}" id="${partNumberId}" placeholder="Part Number">
                                                </div>
                                                <div class="form-group">
                                                    <label for="partnumber">Part Number</label>
                                                    <input type="text" class="form-control" name="partnumber" id="${partNumberId}" value="${item.partnumber}" placeholder="Part Number" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label for="quotetype">Quote Type</label>
                                                    <input type="text" class="form-control" name="quotetype" id="${quoteTypeId}" value="${item.quotetype}" placeholder="Part Number" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label for="material">Material</label>
                                                    <textarea name="material" id="${materialId}" class="form-control" placeholder="Materials" style="min-height: 150px;" readonly>${item.material}</textarea>
                                                </div>
                                                <div class="form-group">
                                                    <label for="quantity">Quantity</label>
                                                    <input type="text" class="form-control" name="quantity" id="${quantityId}" value="${item.quantity}" placeholder="Quantity" readonly>
                                                </div>
                                                ${downloadBTN}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                        
                        // Append the item HTML to the container
                        $('#quotationContainer').append(itemHtml);
                        const stlContainer = document.getElementById(stlContId);
                        if (stlContainer) {
                            console.log("STL Container found:", stlContainer);
                            if (item.filetype == 'SLDPRT') {
                                console.log("Appending SLDPRT");
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/SLDPRT-icon.png" alt="SLDPRT Icon" class="file-icon">';
                            } else if (item.filetype === 'X_T') {
                                console.log("Appending X_T");
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/X_T-icon.png" alt="X_T Icon" class="file-icon">';
                            } else if (item.filetype === 'PDF') {
                                console.log("Appending PDF");
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/PDF-icon.png" alt="PDF Icon" class="file-icon">';
                            } else if (item.filetype === 'STEP' && item.stl_location == null) {
                                console.log("Appending STEP");
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/STEP-icon.png" alt="PDF Icon" class="file-icon">';
                            }  else if (item.filetype === 'IGS' && item.stl_location == null) {
                                console.log("Appending IGS");
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/IGS-icon.webp" alt="PDF Icon" class="file-icon">';
                            } else {
                                if (item.stl_location !== null) {
                                    console.log("Initializing STL Viewer");
                                    console.log("STL Location:", baseURL + item.stl_location);
                                    initializeStlViewer(stlContainer, baseURL + item.stl_location);
                                }
                            }
                        } else {
                            console.log("STL Container not found or not recognized as a jQuery object:", stlContainer);
                        }
                    });
    
                    // Show the modal
                    $('#quotationListModal').modal('show');
    
                    // Clear STL viewer when modal is hidden
                    $('#quotationListModal').on('hidden.bs.modal', function (e) {
                        $('.stlViewer').empty(); // Assuming the STL viewer container has a class of 'stlViewer'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to fetch quotation list!',
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred while fetching the quotation list!',
                });
            }
        });
    });
    
    $(document).on('click', '.delete-request', function (e) {
        e.preventDefault();

        let id = $(this).data('id');
        let row = $(this).closest('tr');

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
                    url: '/requestquotationlist/delete/' + id,
                    method: 'DELETE',
                    success: function (response) {
                        if (response.status === 'success') {
                            table.row(row).remove().draw(false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            });
                        }
                    },
                    error: function () {
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

    $('#sendquotation').submit(function (event) {
        event.preventDefault();

        let row = $(this).closest('tr');

        let productName = $('#productname').val();
        let productPrice = $('#productprice').val();
        let invoiceFile = $('#invoicefile')[0].files[0];
        let userId = $('#user_id').val();
        let requestQuotationId = $('#request_quotation_id').val();

        if (productName.trim() === '' || productPrice.trim() === '' || !invoiceFile || !userId || !requestQuotationId) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Please fill in all the required fields!',
            });
            return;
        }

        let formData = new FormData();
        formData.append('productname', productName);
        formData.append('productprice', productPrice);
        formData.append('invoicefile', invoiceFile);
        formData.append('userId', userId);
        formData.append('requestQuotationId', requestQuotationId);

        $.ajax({
            type: 'POST',
            url: '/requestquotationmasterlist/insert',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                Swal.fire({
                    title: 'Sending...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function (response) {
                if (response.success) {
                    $('#sendquotation')[0].reset();
                    $('#user_id').trigger('chosen:updated');
                    table.row(row).draw(false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message,
                    });
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred while sending quotation. Please try again later.',
                });
                console.error(xhr.responseText);
            }
        });
    });

    $(document).on('click', '.update-status', function () {
        let id = $(this).data('id');
        let row = $(this).closest('tr');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Quotation already submitted!',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/requestquotationmasterlist/updateStatus/' + id,
                    method: 'POST',
                    success: function (response) {
                        if (response.status === 'success') {
                            table.row(row).draw(false);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            });
                        }
                    },
                    error: function () {
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
});
