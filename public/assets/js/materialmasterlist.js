$(document).ready(function () {
    let quoteType = 'CNC Machine'; // Set the default filter to CNC Machine

    // Initialize the DataTable
    let table = $('#materialmasterlist').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/materialmasterlist/getData",
            "type": "POST",
            "data": function (d) {
                d.quotetype = quoteType; // Pass the selected quote type to the server
            }
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
        "order": [[2, "asc"]] // Order by 'arrange_order'
    });

    // Function to toggle button styles based on active selection
    function setActiveButton(buttonId) {
        // Reset both buttons to 'btn-secondary'
        $('#filter-cnc').removeClass('btn-primary').addClass('btn-secondary');
        $('#filter-3d').removeClass('btn-primary').addClass('btn-secondary');
        
        // Set the clicked button to 'btn-primary' (active)
        $(buttonId).removeClass('btn-secondary').addClass('btn-primary');
    }

    // Event handler for filtering by CNC Machine
    $('#filter-cnc').on('click', function () {
        quoteType = 'CNC Machine';
        setActiveButton('#filter-cnc');  // Set the active button
        table.ajax.reload();             // Reload the DataTable
    });

    // Event handler for filtering by 3D Printing
    $('#filter-3d').on('click', function () {
        quoteType = '3D Printing';
        setActiveButton('#filter-3d');   // Set the active button
        table.ajax.reload();             // Reload the DataTable
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
