$(document).ready(function () {

    const materials3DPrinting = ['', 'Nylon', 'ABS', 'PETG', 'Aluminum', 'Stainless Steel', 'Titanium'];
    const materialsCNCMachine = ['', 'ABS', 'PA (Nylon)', 'Polycarbonate', 'PEEK', 'PEI (Ultem)', 'PMMA (Acrylic)', 'POM (Acetal/Delrin)', 'Aluminum', 'Stainless Steel', 'Titanium'];

    const acceptedFileTypes = ['step', 'iges', 'stl', 'igs', 'pdf', 'STEP', 'IGES', 'STL', 'IGS', 'PDF'];
    
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const fileSelectBtn = document.getElementById('fileSelectBtn');
    const fileList = document.getElementById('fileList');

    uploadArea.addEventListener('dragover', function(event) {
        event.preventDefault();
        uploadArea.classList.add('drag-over');
    });

    uploadArea.addEventListener('dragleave', function(event) {
        event.preventDefault();
        uploadArea.classList.remove('drag-over');
    });

    uploadArea.addEventListener('drop', function(event) {
        event.preventDefault();
        uploadArea.classList.remove('drag-over');
        handleFiles(event.dataTransfer.files);
    });

    fileSelectBtn.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        handleFiles(fileInput.files);
    });

    function handleFiles(files) {
        fileList.innerHTML = '';
        let invalidFiles = [];
        let status = $('#status').val();
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();
    
            if (!acceptedFileTypes.includes(fileExtension)) {
                invalidFiles.push(file.name);
                continue;
            }
    
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.textContent = `File Name: ${file.name}, File Size: ${(file.size / 1024).toFixed(2)} KB`;
            fileList.appendChild(fileItem);
        }
    
        if (invalidFiles.length > 0) {
            Swal.fire('Error', `Invalid file type(s): ${invalidFiles.join(', ')}. Only STEP, Parasolid, IGES, and PDF files are allowed.`, 'error');
            return;
        }
    
        let formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }
    
        const requestQuotationId = $('#request_quotation_id').val(); // Fetch request_quotation_id here
        if (!requestQuotationId) {
            Swal.fire('Error', 'No request quotation ID provided.', 'error');
            return;
        }
        formData.append('request_quotation_id', requestQuotationId);
    
        Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while we upload your files.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    
        $.ajax({
            url: '/requestquotationlist/uploadFiles',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                console.log('Success response:', response);
                Swal.fire('Success', response.success, 'success').then(() => {
                    // Append the new files to the file list
                    //quotationItems(requestQuotationId, status);
                    response.files.forEach(item => {
                        let stlContId = 'stlCont_' + item.quotation_item_id;
                        let partNumberId = 'partNumber_' + item.quotation_item_id;
                        let quoteTypeId = 'quotetype_' + item.quotation_item_id;
                        let materialId = 'material_' + item.quotation_item_id;
                        let quantityId = 'quantity_' + item.quotation_item_id;
                        let printFileId = 'printFile_' + item.quotation_item_id;
                        let increaseId = 'increase_' + item.quotation_item_id;
                        let decreaseId = 'decrease_' + item.quotation_item_id;
                        let itemHtml = "";
                        let materialsOptions = getMaterialsHtml(item.quotetype, item.material);
                        itemHtml = `
                        <div class="col-lg-12 items">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6 mb-5">
                                            <div id="${stlContId}" style="height: 250px;"></div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group" hidden>
                                                <input type="text" class="form-control" name="quotation_item_id[]" value="${item.quotation_item_id}" id="${partNumberId}" placeholder="Part Number">
                                            </div>
                                            <div class="form-group">
                                                <label for="partnumber">Part Number</label>
                                                <input type="text" class="form-control" name="partnumber[]" id="${partNumberId}" value="${item.partnumber}" placeholder="Part Number" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="quotetype">Quote Type</label>
                                                <select class="form-control" name="quotetype[]" id="${quoteTypeId}">
                                                    <option hidden>Select Manufacturing Service</option>
                                                    <option disabled></option>
                                                    <option value="3D Printing" ${item.quotetype === '3D Printing' ? 'selected' : ''}>3D Printing</option>
                                                    <option value="CNC Machine" ${item.quotetype === 'CNC Machine' ? 'selected' : ''}>CNC Machine</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="material">Material</label>
                                                <select class="form-control" name="material[]" id="${materialId}">
                                                    ${materialsOptions}
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="${printFileId}">Print File</label>
                                                <div class="custom-file">
                                                    <label class="custom-file-label" for="${printFileId}">Choose file</label>
                                                    <input type="file" class="custom-file-input" id="${printFileId}" name="printFile[]" accept="application/pdf">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="input-group quantity-control">
                                                    <div class="input-group-prepend">
                                                        <button type="button" id="${decreaseId}" class="btn btn-secondary">-</button>
                                                    </div>
                                                    <input type="text" readonly id="${quantityId}" name="quantity[]" value="1" min="1" class="form-control text-center">
                                                    <div class="input-group-append">
                                                        <button type="button" id="${increaseId}" class="btn btn-secondary">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-danger delete-quotation-item" data-id="${item.quotation_item_id}"  data-request-quotation-id="${item.request_quotation_id}"><i class="fa fa-trash"></i> Delete</button><br/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
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
                        $(`#${increaseId}`).on('click', function() {
                            let quantity = parseInt($(`#${quantityId}`).val());
                            $(`#${quantityId}`).val(quantity + 1);
                        });
    
                        $(`#${decreaseId}`).on('click', function() {
                            let quantity = parseInt($(`#${quantityId}`).val());
                            if (quantity > 1) {
                                $(`#${quantityId}`).val(quantity - 1);
                            }
                        });
                    });
                    fileInput.value = '';
                });
            },
            error: function(response) {
                Swal.close();
                console.error('Error response:', response);
                let errors = response.responseJSON.errors;
                let errorMessages = Object.values(errors).join("\n");
                Swal.fire('Error', errorMessages, 'error');
            }
        });
    }
    function appendFileItem(file) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.textContent = `File Name: ${file.name}, File Size: ${(file.size / 1024).toFixed(2)} KB`;
        fileList.appendChild(fileItem);
    }
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
                        <a href="#" title="Quotation List" class="quotation-list" data-id="${row.request_quotation_id}" data-status = "${row.status}" style="color: orange;">
                            <i class="fa fa-file-text" style="font-size: 18px;"></i>
                        </a>
                        <a href="#" title="Duplicate Quotation" class="duplicate-quotation" data-id="${row.request_quotation_id}" data-status = "${row.status}" style="color: blue;">
                            <i class="fa fa-copy" style="font-size: 18px;"></i>
                        </a>
                        <a href="/requestquotationlist/download-files/${row.request_quotation_id}" download title="Download Excel File" style="color: green;">
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

    function getMaterialsHtml(quoteType, selectedMaterial) {
        let materials = quoteType === '3D Printing' ? materials3DPrinting : materialsCNCMachine;
        let options = materials.map(material => {
            let selected = material === selectedMaterial ? 'selected' : '';
            return `<option value="${material}" ${selected}>${material}</option>`;
        });
        return options.join('');
    }

    $(document).on('click', '.quotation-list', function (e) {
        e.preventDefault();

        let requestQuotationId = $(this).data('id');
        let status = $(this).data('status');
        quotationItems(requestQuotationId, status);
    });

    function quotationItems(requestQuotationId, status) {

        $.ajax({
            url: '/requestquotationlist/getQuotationList/' + requestQuotationId,
            method: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    $('#quotationContainer').empty();
                    $('#request_quotation_id').val(requestQuotationId);
                    $('#status').val(status);
                    response.data.forEach(item => {
                        let stlContId = 'stlCont_' + item.quotation_item_id;
                        let partNumberId = 'partNumber_' + item.quotation_item_id;
                        let quoteTypeId = 'quotetype_' + item.quotation_item_id;
                        let materialId = 'material_' + item.quotation_item_id;
                        let quantityId = 'quantity_' + item.quotation_item_id;
                        let printFileId = 'printFile_' + item.quotation_item_id;
                        let increaseId = 'increase_' + item.quotation_item_id;
                        let decreaseId = 'decrease_' + item.quotation_item_id;
                        let downloadBTN = "";
                        if (item.print_location !== null && item.status != 'Pending') {
                            downloadBTN = `<a href="${item.print_location}" download class="btn bg-dark text-white mb-2"><i class="fa fa-download"></i> Download Print File</a>`;
                        }
                        let itemHtml = "";
                        let materialsOptions = getMaterialsHtml(item.quotetype, item.material);
                        if (status != 'Pending') {
                            $('#DropFiles').css('display', 'none');
                            $('#AssemblyPrintFile').css('display', 'none');
                            $('#downloadAssemblyFiles').css('display', 'block');
                            $('#downloadAssembly').attr('data-id', item.request_quotation_id);
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
                                                        <input type="text" class="form-control" name="quotetype" id="${quoteTypeId}" value="${item.quotetype}" placeholder="Quote Type" readonly>
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        } else {
                            $('#downloadAssemblyFiles').css('display', 'none');
                            itemHtml = `
                                <div class="col-lg-12 items">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-6 mb-5">
                                                    <div id="${stlContId}" style="height: 250px;"></div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group" hidden>
                                                        <input type="text" class="form-control" name="quotation_item_id[]" value="${item.quotation_item_id}" id="${partNumberId}" placeholder="Part Number">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="partnumber">Part Number</label>
                                                        <input type="text" class="form-control" name="partnumber[]" id="${partNumberId}" value="${item.partnumber}" placeholder="Part Number" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="quotetype">Quote Type</label>
                                                        <select class="form-control" name="quotetype[]" id="${quoteTypeId}">
                                                            <option hidden>Select Manufacturing Service</option>
                                                            <option disabled></option>
                                                            <option value="3D Printing" ${item.quotetype === '3D Printing' ? 'selected' : ''}>3D Printing</option>
                                                            <option value="CNC Machine" ${item.quotetype === 'CNC Machine' ? 'selected' : ''}>CNC Machine</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="material">Material</label>
                                                        <select class="form-control" name="material[]" id="${materialId}">
                                                            ${materialsOptions}
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="${printFileId}">Print File</label>
                                                        <div class="custom-file">
                                                            <label class="custom-file-label" for="${printFileId}">Choose file</label>
                                                            <input type="file" class="custom-file-input" id="${printFileId}" name="printFile[]" accept="application/pdf">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="input-group quantity-control">
                                                            <div class="input-group-prepend">
                                                                <button type="button" id="${decreaseId}" class="btn btn-secondary">-</button>
                                                            </div>
                                                            <input type="text" readonly id="${quantityId}" name="quantity[]" value="${item.quantity}" min="1" class="form-control text-center">
                                                            <div class="input-group-append">
                                                                <button type="button" id="${increaseId}" class="btn btn-secondary">+</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-danger delete-quotation-item" data-id="${item.quotation_item_id}"  data-request-quotation-id="${item.request_quotation_id}"><i class="fa fa-trash"></i> Delete</button><br/>
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
                        $(`#${increaseId}`).on('click', function() {
                            let quantity = parseInt($(`#${quantityId}`).val());
                            $(`#${quantityId}`).val(quantity + 1);
                        });
    
                        $(`#${decreaseId}`).on('click', function() {
                            let quantity = parseInt($(`#${quantityId}`).val());
                            if (quantity > 1) {
                                $(`#${quantityId}`).val(quantity - 1);
                            }
                        });
                    });
                    if(status == 'Pending') {
                        $('#DropFiles').css('display', 'block');
                        $('#AssemblyPrintFile').css('display', 'block');
                    }
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
    }

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
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'The request quotation has been deleted.',
                            });
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

    $(document).on('click', '.delete-quotation-item', function (e) {
        e.preventDefault();

        let id = $(this).data('id');
        let requestQuotationId = $(this).data('request-quotation-id');
        let row = $(this).closest('.col-lg-12');

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
                    url: '/requestquotationlist/deleteItem/' + id,
                    method: 'DELETE',
                    data: { requestQuotationId: requestQuotationId },
                    success: function (response) {
                        if (response.status === 'success') {
                            row.remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'The quotation item has been deleted.',
                            });
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
    $(document).on('click', '#submit_quotation', function () {
        let formData = new FormData($('#quotationForm')[0]);

        Swal.fire({
            title: 'Submitting...',
            text: 'Please wait while we submit your quotation.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    
        $.ajax({
            url: '/requestquotationlist/submitQuotations',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                Swal.close();
                if (response.status === 'success') {
                    Swal.fire('Success', 'Quotation submitted successfully.', 'success').then(() => {
                        $('#quotationModal').modal('hide');
                        table.ajax.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function (response) {
                Swal.close();
                Swal.fire('Error', 'Failed to submit quotation.', 'error');
            }
        });
    });
    $(document).on('change', '#assemblyFile', function(event) {
        const inputFile = event.currentTarget;
        const fileCount = inputFile.files.length;
        const label = $(this).siblings('.custom-file-label');

        if (fileCount > 1) {
            label.text(`${fileCount} files selected`);
        } else if (fileCount === 1) {
            label.text(inputFile.files[0].name);
        } else {
            label.text('Choose file');
        }
    });
    $(document).on('click', '.duplicate-quotation', function (e) {
        e.preventDefault();
    
        let quotationId = $(this).data('id');

        let id = $(this).data('id');

        let row = $(this).closest('tr');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to duplicate this quotation?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, duplicate it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Duplicating...',
                    text: 'Please wait while we duplicate your quotation.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
    
                $.ajax({
                    url: '/requestquotationlist/duplicateQuotation/' + quotationId,
                    method: 'POST',
                    success: function (response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire('Success', response.message, 'success').then(() => {
                                // Reload the table or update the UI as needed
                                table.ajax.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function (response) {
                        Swal.close();
                        Swal.fire('Error', 'Failed to duplicate the quotation.', 'error');
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
