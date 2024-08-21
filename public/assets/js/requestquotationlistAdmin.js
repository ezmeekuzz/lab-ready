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
            { "data": "reference" },
            {
                "data": "status",
                "render": function (data) {
                    let statusClass = '';
                    if (data === 'Pending') {
                        statusClass = 'badge badge-warning';
                    } else if (data === 'Done') {
                        statusClass = 'badge badge-success';
                    }
                    else {
                        statusClass = 'badge-info p-1 rounded';
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
                        <a href="#" title="Update Status" class="update-status" data-id="${row.request_quotation_id}" style="color: blue;">
                            <i class="ti ti-pencil" style="font-size: 18px;"></i>
                        </a>
                        <a href="/download-files/${row.request_quotation_id}" download title="Download Excel File" style="color: green;">
                            <i class="ti ti-download" style="font-size: 18px;"></i>
                        </a>`;
                }
            }
        ],
        "createdRow": function (row, data) {
            $(row).attr('data-id', data.request_quotation_id);
            $(row).attr('data-reference', data.reference);

            $('td', row).each(function (index) {
                if (index !== 7) { // Assuming the actions column is at index 5
                    $(this).attr('data-user-id', data.uid);
                    $(this).attr('data-reference', data.reference);
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

        if (cellIndex === 6) { // If the cell index is the actions column, do nothing
            return;
        }

        let userId = $(this).data('user-id');
        let requestQuotationId = $(this).closest('tr').data('id');
        let reference = $(this).closest('tr').data('reference');

        $('#user_id').val(userId);
        $('#request_quotation_id').val(requestQuotationId);
        $('#productname').val(reference);

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
    $('#downloadAssembly').click(function(e) {
        e.preventDefault();

        var requestId = $(this).data('id');

        var downloadUrl = '/requestquotationlist/downloadAssemblyFiles/' + requestId;

        window.location.href = downloadUrl;
    });
});
