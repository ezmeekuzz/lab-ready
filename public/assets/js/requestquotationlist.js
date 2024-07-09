$(document).ready(function () {
    const materials3DPrinting = ['', 'Nylon', 'ABS', 'PETG', 'Aluminum', 'Stainless Steel', 'Titanium'];
    const materialsCNCMachine = ['', 'ABS', 'PA (Nylon)', 'Polycarbonate', 'PEEK', 'PEI (Ultem)', 'PMMA (Acrylic)', 'POM (Acetal/Delrin)', 'Aluminum', 'Stainless Steel', 'Titanium'];

    let table = $('#requestquotationmasterlist').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "/requestquotationlist/getData",
            "type": "POST"
        },
        "columns": [
            { "data": "reference" },
            {
                "data": "status",
                "render": function (data) {
                    let statusClass = '';
                    if (data === 'Pending') {
                        statusClass = 'badge-warning p-1 rounded';
                    } else if (data === 'Done') {
                        statusClass = 'badge-success p-1 rounded';
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
                        <a href="/download-excel-file/${row.request_quotation_id}" download title="Download Excel File" style="color: green;">
                            <i class="ti ti-download" style="font-size: 18px;"></i>
                        </a>
                        <a href="#" title="Delete" class="delete-request" data-id="${row.request_quotation_id}" style="color: red;">
                            <i class="ti ti-trash" style="font-size: 18px;"></i>
                        </a>`;
                }
            }
        ],
        "createdRow": function (row, data, dataIndex) {
            $(row).attr('data-id', data.request_quotation_id);
        },
        "initComplete": function (settings, json) {
            $(this).trigger('dt-init-complete');
        }
    });

    function initializeStlViewer(stlContainer, stlLocation) {
        new StlViewer(stlContainer, {
            models: [{
                filename: stlLocation
            }],
            canvasConfig: {
                antialias: true,
                quality: 'high'
            },
            solid: true,
            rotate: true,
            autoResize: true,
            lights: [
                { dir: [1, 1, 1], color: [1, 1, 1] },
                { dir: [-1, -1, -1], color: [0.5, 0.5, 0.5] }
            ],
            pan: [0, 0]
        });
    }

    $(document).on('click', '.quotation-list', function (e) {
        e.preventDefault();

        let requestQuotationId = $(this).data('id');

        $.ajax({
            url: '/requestquotationlist/getQuotationList/' + requestQuotationId,
            method: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    $('#quotationContainer').empty();

                    response.data.forEach(item => {
                        let stlContId = 'stlCont_' + item.quotation_item_id;
                        let partNumberId = 'partNumber_' + item.quotation_item_id;
                        let quoteTypeId = 'quotetype_' + item.quotation_item_id;
                        let materialId = 'material_' + item.quotation_item_id;
                        let quantityId = 'quantity_' + item.quotation_item_id;
                        let downloadBTN = "";
                        let downloadAssemblyBTN = "";
                        if (item.print_location !== null) {
                            downloadBTN = `<a href="${item.print_location}" download class="btn bg-dark text-white mb-2"><i class="fa fa-download"></i> Download Print File</a>`;
                        }
                        if (item.assembly_file_location !== null) {
                            downloadAssemblyBTN = `<a href="${item.assembly_file_location}" download class="btn bg-warning text-white mb-2"><i class="fa fa-download"></i> Download Assembly Print File</a>`;
                        }
                        let itemHtml = "";
                        if(item.status != 'Pending') {
                            itemHtml = `
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
                                                    ${downloadBTN}<br/>
                                                    ${downloadAssemblyBTN}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        }
                        else {
                            itemHtml = `
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
                                                        <select class="form-control" name="quotetype" id="${quoteTypeId}">
                                                            <option hidden>Select Manufacturing Service</option>
                                                            <option disabled></option>
                                                            <option value="3D Printing" ${item.quotetype === '3D Printing' ? 'selected' : ''}>3D Printing</option>
                                                            <option value="CNC Machine" ${item.quotetype === 'CNC Machine' ? 'selected' : ''}>CNC Machine</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="material">Material</label>
                                                        <select class="form-control" name="material" id="${materialId}">
                                                            <option hidden>Select a Material</option>
                                                            <option value="${item.material}" selected>${item.material}</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="quantity">Quantity</label>
                                                        <input type="text" class="form-control" name="quantity" id="${quantityId}" value="${item.quantity}" placeholder="Quantity" readonly>
                                                    </div>
                                                    ${downloadBTN}<br/>
                                                    ${downloadAssemblyBTN}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        }

                        $('#quotationContainer').append(itemHtml);

                        const stlContainer = document.getElementById(stlContId);
                        if (stlContainer) {
                            if (item.filetype == 'SLDPRT') {
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/SLDPRT-icon.png" alt="SLDPRT Icon" class="file-icon">';
                            } else if (item.filetype === 'X_T') {
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/X_T-icon.png" alt="X_T Icon" class="file-icon">';
                            } else if (item.filetype === 'PDF') {
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/PDF-icon.png" alt="PDF Icon" class="file-icon">';
                            } else if (item.filetype === 'STEP' && item.stl_location == null) {
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/STEP-icon.png" alt="PDF Icon" class="file-icon">';
                            }  else if (item.filetype === 'IGS' && item.stl_location == null) {
                                stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/IGS-icon.webp" alt="PDF Icon" class="file-icon">';
                            } else {
                                if (item.stl_location !== null) {
                                    initializeStlViewer(stlContainer, baseURL + item.stl_location);
                                }
                            }
                        }
                    });

                    $('#quotationListModal').modal('show');

                    $('#quotationListModal').on('hidden.bs.modal', function (e) {
                        $('.stlViewer').empty();
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

    $(document).on('change', 'select[id^="quotetype_"]', function() {
        let selectedQuoteType = $(this).val();
        let materialSelect = $(this).closest('.row').find('select[id^="material_"]');
        let materials = selectedQuoteType === '3D Printing' ? materials3DPrinting : materialsCNCMachine;

        materialSelect.empty();
        materials.forEach(function(material) {
            materialSelect.append(`<option value="${material}">${material}</option>`);
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
});
