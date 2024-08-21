$(document).ready(function () {
    let table = $('#materialmasterlist').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/materialmasterlist/getData",
            "type": "POST"
        },
        "columns": [
            { "data": "quotetype" },
            { "data": "materialname" },
            { "data": "arrange_order" },
            {
                "data": null,
                "render": function (data, type, row) {
                    return `<a href="/edit-material/${row.material_id}" title="Edit" class="edit-btn" data-id="${row.material_id}" style="color: blue;"><i class="ti ti-pencil" style="font-size: 18px;"></i></a>
                            <a href="#" title="Arrange Order" class="arrange-btn" data-quotetype="${row.quotetype}" data-id="${row.material_id}" style="color: orange;"><i class="fa fa-refresh" style="font-size: 18px;"></i></a>
                            <a href="#" title="Delete" class="delete-btn" data-id="${row.material_id}" style="color: red;"><i class="ti ti-trash" style="font-size: 18px;"></i></a>`;
                }
            }
        ],
        "order": [[0, "asc"]], // Order by 'arrange_order'
        "createdRow": function (row, data, dataIndex) {
            $(row).attr('data-id', data.material_id);
        },
        "initComplete": function (settings, json) {
            $(this).trigger('dt-init-complete');
        }
    });

    $(document).on('click', '.arrange-btn', function () {
        let quotetype = $(this).data('quotetype');

        // Use AJAX to fetch the list based on the quotetype
        $.ajax({
            url: '/materialmasterlist/getListByQuoteType',
            type: 'POST',
            data: { quotetype: quotetype },
            success: function(response) {
                // Insert the HTML content into the modal
                $('#orderContainer').html(response.html);

                // Make the list sortable
                $('#sortableList').sortable({
                    update: function(event, ui) {
                        // On order change, send the new order to the server
                        let order = $(this).sortable('toArray', { attribute: 'data-id' });
                        
                        $.ajax({
                            url: '/materialmasterlist/updateOrder',
                            type: 'POST',
                            data: { order: order },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Order updated',
                                        text: 'The material order has been successfully updated.',
                                    });

                                    // Refresh the table row
                                    table.ajax.reload(); // false to keep pagination
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Failed to update the order on the server.',
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Failed to update the order.',
                                });
                            }
                        });
                    }
                });
            },
            error: function() {
                // Handle the error
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Failed to load the list for the selected quote type.',
                });
            }
        });

        // Trigger the modal for arranging the order
        $('#arrangeOrderModal').modal('show');
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
                    url: '/materialmasterlist/delete/' + id,
                    method: 'DELETE',
                    success: function (response) {
                        if (response.status === 'success') {
                            table.row(row).remove().draw(false);
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
});
