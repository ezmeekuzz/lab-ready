$(document).ready(function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const fileSelectBtn = document.getElementById('fileSelectBtn');
    const fileList = document.getElementById('fileList');

    const acceptedFileTypes = ['step', 'x_t', 'iges', 'igs', 'x_t', 'pdf', 'STEP', 'X_T', 'IGES', 'IGS', 'X_T', 'PDF'];

    const materials3DPrinting = ['Nylon', 'ABS', 'PETG', 'Aluminum', 'Stainless Steel', 'Titanium'];
    const materialsCNCMachine = ['ABS', 'PA (Nylon)', 'Polycarbonate', 'PEEK', 'PEI (Ultem)', 'PMMA (Acrylic)', 'POM (Acetal/Delrin)', 'Aluminum', 'Stainless Steel', 'Titanium'];
    const materialsMetalSurfaceFinishes = ['Aluminum anodizing', 'Titanium anodizing', 'DLC (diamond like coating)'];

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

        Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while we upload your files.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '/requestquotation/uploadFiles',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire('Success', response.success, 'success').then(() => {
                    getQuotationLists();
                    $('#requestquotation')[0].reset();
                    fileList.innerHTML = '';
                    fileInput.value = '';
                });
            },
            error: function(response) {
                Swal.close();
                let errors = response.responseJSON.errors;
                let errorMessages = Object.values(errors).join("\n");
                Swal.fire('Error', errorMessages, 'error');
            }
        });
    }
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
    function getQuotationLists() {
        $.ajax({
            url: "/requestquotation/quotationLists",
            type: "GET",
            dataType: "json",
            success: function(response) {
                let allFormsHtml = '';

                response.forEach((item, index) => {
                    const stlContId = `stl_cont${index + 1}`;
                    const partNumberId = `partnumber${index + 1}`;
                    const quoteTypeId = `quoteType${index + 1}`;
                    const materialId = `material${index + 1}`;
                    const printFileId = `printFile${index + 1}`;
                    const quantityId = `quantity${index + 1}`;
                    const increaseId = `increase${index + 1}`;
                    const decreaseId = `decrease${index + 1}`;

                    const formHtml = `
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6 mb-5">
                                            <div id="${stlContId}" style="height: 150px;"></div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group" hidden>
                                                <input type="text" class="form-control" name="quotation_item_id" value="${item.quotation_item_id}" id="${partNumberId}" placeholder="Part Number">
                                            </div>
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="partnumber" id="${partNumberId}" value="${item.filename}" placeholder="Part Number" readonly>
                                            </div>
                                            <div class="form-group">
                                                <select class="form-control" name="quotetype" id="${quoteTypeId}">
                                                    <option hidden>Select a quote type</option>
                                                    <option disabled></option>
                                                    <option value="3D Printing">3D Printing</option>
                                                    <option value="CNC Machine">CNC Machine</option>
                                                    <option value="Metal Surface Finishes">Metal Surface Finishes</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <select class="form-control" name="material" id="${materialId}">
                                                    <option hidden>Select a Material</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="${printFileId}">Print File</label>
                                                <div class="custom-file">
                                                    <label class="custom-file-label" for="${printFileId}">Choose file</label>
                                                    <input type="file" class="custom-file-input" id="${printFileId}" name="printFile" accept="application/pdf">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="input-group quantity-control">
                                                    <div class="input-group-prepend">
                                                        <button type="button" id="${decreaseId}" class="btn btn-secondary">-</button>
                                                    </div>
                                                    <input type="text" id="${quantityId}" name="quantity" value="1" min="1" class="form-control text-center">
                                                    <div class="input-group-append">
                                                        <button type="button" id="${increaseId}" class="btn btn-secondary">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-danger w-100 delete-quotation-item" data-id="${item.quotation_item_id}"><i class="fa fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    allFormsHtml += formHtml;
                });

                if (response.length > 0) {
                    allFormsHtml += `
                        <div class="col-lg-12">
                            <button type="button" id="submitAll" class="btn bg-dark text-white">Submit</button>
                        </div>
                    `;
                }

                document.getElementById('formsContainer').innerHTML = allFormsHtml;

                $('#submitAll').click(function(event) {
                    event.preventDefault();
                
                    let formData = new FormData();
                    let proceed = true;
                
                    $('#formsContainer').find('.card').each(function(index) {
                        const partNumber = $(this).find('[name="partnumber"]').val();
                        const quoteType = $(this).find('[name="quotetype"]').val();
                        const material = $(this).find('[name="material"]').val();
                        const quantity = $(this).find('[name="quantity"]').val();
                        const quotationItemId = $(this).find('[name="quotation_item_id"]').val();
                        const printFile = $(this).find('[name="printFile"]')[0].files[0];
                
                        if (!printFile && !material) {
                            Swal.fire('Error', 'Material field is required if Print File is not provided', 'error');
                            proceed = false;
                            return false; // Break out of the loop
                        }
                
                        if (!quantity) {
                            Swal.fire('Error', 'Quantity field is required', 'error');
                            proceed = false;
                            return false; // Break out of the loop
                        }
                
                        // Append form data as JSON string
                        formData.append(`forms[${index}][partNumber]`, partNumber);
                        formData.append(`forms[${index}][material]`, material);
                        formData.append(`forms[${index}][quotetype]`, quoteType);
                        formData.append(`forms[${index}][quantity]`, quantity);
                        formData.append(`forms[${index}][quotation_item_id]`, quotationItemId);
                
                        // Append the file, if provided
                        if (printFile) {
                            formData.append(`forms[${index}][printFile]`, printFile);
                        }
                    });
                
                    if (!proceed) {
                        return; // Stop form submission if conditions are not met
                    }
                
                    Swal.fire({
                        title: 'Submitting...',
                        text: 'Please wait while we submit your quotations.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                
                    $.ajax({
                        url: '/requestquotation/submitQuotations',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            Swal.fire('Success', 'Quotations submitted successfully!', 'success').then(() => {
                                getQuotationLists();
                            });
                        },
                        error: function(response) {
                            Swal.close();
                            let errors = response.responseJSON.errors;
                            let errorMessages = Object.values(errors).join("\n");
                            Swal.fire('Error', errorMessages, 'error');
                        }
                    });
                });

                response.forEach((item, index) => {
                    const stlContId = `stl_cont${index + 1}`;
                    const quantityId = `quantity${index + 1}`;
                    const increaseId = `increase${index + 1}`;
                    const decreaseId = `decrease${index + 1}`;
                    const quoteTypeId = `quoteType${index + 1}`;
                    const materialId = `material${index + 1}`;

                    const stlContainer = document.getElementById(stlContId);
                    if (stlContainer) {
                        console.log("STL Container found:", stlContainer);
                        if (item.filetype == 'SLDPRT') {
                            console.log("Appending SLDPRT");
                            stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/SLDPRT-icon.png" alt="SLDPRT Icon" style="width: 100%;">';
                        } else if (item.filetype === 'X_T') {
                            console.log("Appending X_T");
                            stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/X_T-icon.png" alt="X_T Icon" style="width: 100%;">';
                        } else if (item.filetype === 'PDF') {
                            console.log("Appending PDF");
                            stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/PDF-icon.png" alt="PDF Icon" style="width: 100%;">';
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

                    const quantityInput = document.getElementById(quantityId);
                    document.getElementById(increaseId).addEventListener('click', function() {
                        quantityInput.value = parseInt(quantityInput.value) + 1;
                    });

                    document.getElementById(decreaseId).addEventListener('click', function() {
                        if (parseInt(quantityInput.value) > 1) {
                            quantityInput.value = parseInt(quantityInput.value) - 1;
                        }
                    });
                    document.getElementById(quoteTypeId).addEventListener('change', function() {
                        updateMaterialOptions(this.value, materialId);
                    });

                    // Trigger material update based on initial quoteType value
                    updateMaterialOptions(document.getElementById(quoteTypeId).value, materialId);
                });
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function updateMaterialOptions(quoteType, materialId) {
        const materialSelect = document.getElementById(materialId);
        materialSelect.innerHTML = '';

        let options = [];
        if (quoteType === '3D Printing') {
            options = materials3DPrinting;
        } else if (quoteType === 'CNC Machine') {
            options = materialsCNCMachine;
        } else if (quoteType === 'Metal Surface Finishes') {
            options = materialsMetalSurfaceFinishes;
        }

        options.forEach((material) => {
            const option = document.createElement('option');
            option.value = material;
            option.textContent = material;
            materialSelect.appendChild(option);
        });
    }
    // Call the function when the page is ready or when needed
    getQuotationLists();

    $(document).on('click', '.delete-quotation-item', function (e) {
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
                    url: '/requestquotation/delete/' + id,
                    method: 'DELETE',
                    success: function (response) {
                        if (response.status === 'success') {
                            getQuotationLists();
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
