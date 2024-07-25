$(document).ready(function () {
    let table = $('#quotationmasterlist').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/quotationmasterlist/getData",
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
            { "data": "productname" },
            { "data": "productprice" },
            { "data": "quotationdate" },
            {
                "data": "stat",
                "render": function (data, type, row) {
                    let statusClass = '';
                    if (data === 'Unpaid') {
                        statusClass = 'badge badge-warning';
                    } else if (data === 'Paid') {
                        statusClass = 'badge badge-success';
                    }
                    return `<span class="${statusClass}">${data}</span>`;
                }
            },
            {
                "data": null,
                "render": function (data, type, row) {
                    let buttons = `<a href="#" title="Paid" class="paid-btn" data-id="${row.quotation_id}" style="color: blue;"><i class="ti ti-money" style="font-size: 18px;"></i></a>`;
                    if (row.stat === 'Paid') {
                        buttons += `<a href="#" title="Update Shipment Status" class="shipment-btn" data-email="${row.email}" data-id="${row.quotation_id}" style="color: green;"><i class="ti ti-truck" style="font-size: 18px;"></i></a>`;
                    }
                    buttons += `<a href="#" title="Delete" class="delete-btn" data-id="${row.quotation_id}" style="color: red;"><i class="ti ti-trash" style="font-size: 18px;"></i></a>`;
                    return buttons;
                }
            }
        ],
        "createdRow": function (row, data, dataIndex) {
            $(row).attr('data-id', data.quotation_id);
        },
        "initComplete": function (settings, json) {
            $(this).trigger('dt-init-complete');
        }
    });

    $(document).on('click', '.delete-btn', function () {
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
                    url: '/quotationmasterlist/delete/' + id,
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

    $(document).on('click', '.paid-btn', function () {
        let id = $(this).data('id');
        let row = $(this).closest('tr');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, It is already paid!',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/quotationmasterlist/updateStatus/' + id,
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

    $(document).on('click', '.shipment-btn', function () {
        let id = $(this).data('id');
        let email = $(this).data('email');
    
        $.ajax({
            url: '/quotationmasterlist/getShipment/' + id,
            method: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    let data = response.data || {};
                    Swal.fire({
                        title: 'Update Shipment Status',
                        html: `
                            <input type="hidden" id="quotation_id" value="${id}">
                            <input type="hidden" id="email" value="${email}">
                            <div class="form-group">
                                <label for="shipment_address">Shipment Address</label>
                                <input type="text" id="shipment_address" class="form-control" value="${data.shipment_address || ''}" placeholder="Shipment Address">
                            </div>
                            <div class="form-group">
                                <label for="shipment_note">Shipment Note</label>
                                <textarea id="shipment_note" class="form-control" style="min-height: 150px;" placeholder="Shipment Note">${data.shipment_note || ''}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="shipment_link">Shipment Link</label>
                                <input type="text" id="shipment_link" class="form-control" value="${data.shipment_link || ''}" placeholder="Shipment Link">
                            </div>
                            <div class="form-group">
                                <label for="shipment_date">Shipment Date</label>
                                <input type="date" id="shipment_date" class="form-control" value="${data.shipment_date || ''}" placeholder="Shipment Date">
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            Swal.showLoading();
                            return new Promise((resolve) => {
                                $.ajax({
                                    url: '/quotationmasterlist/updateShipment/' + id,
                                    method: 'POST',
                                    data: {
                                        quotation_id: document.getElementById('quotation_id').value,
                                        shipment_address: document.getElementById('shipment_address').value,
                                        shipment_note: document.getElementById('shipment_note').value,
                                        shipment_link: document.getElementById('shipment_link').value,
                                        shipment_date: document.getElementById('shipment_date').value,
                                        email: document.getElementById('email').value
                                    },
                                    success: function (response) {
                                        if (response.status === 'success') {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Updated!',
                                                text: 'Shipment status has been updated.',
                                            });
                                            table.row($(`tr[data-id="${id}"]`)).draw(false);
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Oops...',
                                                text: 'Something went wrong!',
                                            });
                                        }
                                        resolve();
                                    },
                                    error: function () {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Oops...',
                                            text: 'Something went wrong with the request!',
                                        });
                                        resolve();
                                    }
                                });
                            });
                        }
                    });
                } else {
                    console.error('Server response:', response);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Failed to fetch shipment data!',
                    });
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong with the request!',
                });
            }
        });
    });    
});
