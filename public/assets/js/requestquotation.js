$(document).ready(function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const fileSelectBtn = document.getElementById('fileSelectBtn');
    const fileList = document.getElementById('fileList');
    const assemblyFileInput = document.getElementById('assemblyFile');
    const assemblyFileNames = document.getElementById('assemblyFileNames');
    let selectedFiles = []; // Store selected files in this array

    const acceptedFileTypes = ['', 'step', 'iges', 'stl', 'igs', 'pdf', 'STEP', 'IGES', 'STL', 'IGS', 'PDF'];

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
            Swal.fire('Error', `Invalid file type(s): ${invalidFiles.join(', ')}. Only STEP, IGES AND STL files are allowed.`, 'error');
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
                Swal.close();
                console.log('Success response:', response);
                Swal.fire('Success', response.success, 'success').then(() => {
                    if(response.files.length > 0) {
                        $('#assembly').css('display', 'block');
                        $('#submitBTN').css('display', 'block');
                    }
                    else {
                        $('#assembly').css('display', 'none');
                        $('#submitBTN').css('display', 'none');
                    }
                    response.files.forEach(item => {
                        let stlContId = `stlCont_${item.quotation_item_id}`;
                        let partNumberId = `partNumber_${item.quotation_item_id}`;
                        let quoteTypeId = `quotetype_${item.quotation_item_id}`;
                        let materialId = `material_${item.quotation_item_id}`;
                        let quantityId = `quantity_${item.quotation_item_id}`;
                        let printFileId = `printFile_${item.quotation_item_id}`;
                        let increaseId = `increase_${item.quotation_item_id}`;
                        let decreaseId = `decrease_${item.quotation_item_id}`;
        
                        const formHtml = `
                            <div class="col-lg-6 mb-4 items">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-6 mb-5">
                                                <div id="${stlContId}" class="file-container" style="height: 250px;"></div>
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
                                                        <option hidden>Select Manufacturing Service</option>
                                                        <option disabled></option>
                                                        <option value="3D Printing">3D Printing</option>
                                                        <option value="CNC Machine">CNC Machine</option>
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
        
                        $('#formsContainer').append(formHtml);
        
                        const stlContainer = document.getElementById(stlContId);
                        if (stlContainer) {
                            if (item.filetype == 'SLDPRT') {
                                stlContainer.innerHTML = `<img src="${baseURL}assets/img/SLDPRT-icon.png" alt="SLDPRT Icon" class="file-icon">`;
                            } else if (item.filetype === 'X_T') {
                                stlContainer.innerHTML = `<img src="${baseURL}assets/img/X_T-icon.png" alt="X_T Icon" class="file-icon">`;
                            } else if (item.filetype === 'PDF') {
                                stlContainer.innerHTML = `<img src="${baseURL}assets/img/PDF-icon.png" alt="PDF Icon" class="file-icon">`;
                            } else if (item.filetype === 'STEP' && item.stl_location == null) {
                                stlContainer.innerHTML = `<img src="${baseURL}assets/img/STEP-icon.png" alt="STEP Icon" class="file-icon">`;
                            } else if (item.filetype === 'IGS' && item.stl_location == null) {
                                stlContainer.innerHTML = `<img src="${baseURL}assets/img/IGS-icon.webp" alt="IGS Icon" class="file-icon">`;
                            } else {
                                if (item.stl_location !== null) {
                                    initializeStlViewer(stlContainer, `${baseURL}${item.stl_location}`);
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
                        document.getElementById(quoteTypeId).addEventListener('change', function() {
                            updateMaterialOptions(this.value, materialId);
                        });
    
                        // Trigger material update based on initial quoteType value
                        updateMaterialOptions(document.getElementById(quoteTypeId).value, materialId);
                    });
                    $('#requestquotation')[0].reset();
                    fileList.innerHTML = '';
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
                if(response.length > 0) {
                    $('#assembly').css('display', 'block');
                    $('#submitBTN').css('display', 'block');
                }
                else {
                    $('#assembly').css('display', 'none');
                    $('#submitBTN').css('display', 'none');
                }
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
                        <div class="col-lg-6 items">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6 mb-5">
                                            <div id="${stlContId}" class="file-container" style="height: 250px;"></div>
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
                                                    <option hidden>Select Manufacturing Service</option>
                                                    <option disabled></option>
                                                    <option value="3D Printing">3D Printing</option>
                                                    <option value="CNC Machine">CNC Machine</option>
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

                $(document).on('change', '.custom-file-input', function() {
                    var file = this.files[0].name;
                    $(this).siblings('.custom-file-label').text(file.substring(0, 20));
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

                document.getElementById('formsContainer').innerHTML = allFormsHtml;

                $('#submitAll').click(function(event) {
                    event.preventDefault();
                
                    let formData = new FormData();
                    let proceed = true;
                
                    const assemblyFileInput = $('[name="assemblyFile[]"]')[0];
                
                    if (!assemblyFileInput) {
                        console.error('Assembly file input not found');
                        return;
                    }
                
                    const assemblyFiles = assemblyFileInput.files;
                    console.log('Assembly Files:', assemblyFiles);
                
                    for (let i = 0; i < assemblyFiles.length; i++) {
                        formData.append('assemblyFile[]', assemblyFiles[i]);
                    }
                
                    $('#formsContainer').find('.card').each(function(index) {
                        const partNumber = $(this).find('[name="partnumber"]').val();
                        const quoteType = $(this).find('[name="quotetype"]').val();
                        const material = $(this).find('[name="material"]').val();
                        const quantity = $(this).find('[name="quantity"]').val();
                        const quotationItemId = $(this).find('[name="quotation_item_id"]').val();
                        const printFileInput = $(this).find('[name="printFile"]')[0];
                        const printFile = printFileInput ? printFileInput.files[0] : null;
                
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
                                $('#requestquotation')[0].reset();
                                $('#assemblyFile').val('');
                                $('#assemblyFile').siblings('.custom-file-label').text('Choose file');
                                getQuotationLists();
                            });
                        },
                        error: function(response) {
                            Swal.close();
                
                            // Check if responseJSON is available
                            if (response.responseJSON && response.responseJSON.errors) {
                                let errors = response.responseJSON.errors;
                                let errorMessages = Object.values(errors).join("\n");
                                Swal.fire('Error', errorMessages, 'error');
                            } else {
                                Swal.fire('Error', 'An unexpected error occurred.', 'error');
                            }
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
                            stlContainer.innerHTML = '<img src="' + baseURL + 'assets/img/SLDPRT-icon.png" alt="SLDPRT Icon" class="file-icon">';
                        } else if (item.filetype === 'X_T' && item.stl_location == null) {
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
        materialSelect.innerHTML = ''; // Clear current options
    
        // Add a blank or empty option
        const emptyOption = document.createElement('option');
        emptyOption.value = ''; // Set the value to an empty string
        emptyOption.textContent = ''; // Set the display text to be empty
        emptyOption.disabled = true; // Disable the blank option
        emptyOption.selected = true; // Make sure it's selected initially
        materialSelect.appendChild(emptyOption); // Append the empty option first
    
        // Make an AJAX request to the server to fetch materials based on quoteType
        fetch(`/requestquotation/getMaterials?quoteType=${encodeURIComponent(quoteType)}`)
            .then(response => response.json())
            .then(data => {
                // Check if data is received and is an array
                if (Array.isArray(data)) {
                    data.forEach(material => {
                        const option = document.createElement('option');
                        option.value = material.material_id; // Assuming the data has a 'material_id' field for the value
                        option.textContent = material.materialname; // Assuming the data has a 'materialname' field for the option text
                        materialSelect.appendChild(option);
                    });
                } else {
                    console.error('Invalid data received from the server');
                }
            })
            .catch(error => {
                console.error('Error fetching materials:', error);
            });
    }    

    // Call the function when the page is ready or when needed
    getQuotationLists();

    $(document).on('click', '.delete-quotation-item', function (e) {
        e.preventDefault();

        let id = $(this).data('id');
        let requestQuotationId = $(this).data('request-quotation-id');
        let row = $(this).closest('.items');

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
                            row.remove();
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
    
    // Handle file selection
    assemblyFileInput.addEventListener('change', function(event) {
        const files = event.target.files;
    
        // Loop through selected files and display them
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileId = Date.now() + i; // Generate a unique ID for the file
    
            // Add file to the selectedFiles array
            selectedFiles.push({
                id: fileId,
                file: file
            });
    
            // Create HTML for the selected file
            const assemblyFileHtml = `
                <div class="label label-info assembly-file-item position-relative d-inline-block" style="padding-right: 25px;">
                    ${file.name}
                    <button type="button" data-id="${fileId}" class="delete-file-btn-unsave btn btn-danger btn-sm position-absolute rounded-circle" style="top: -5px; right: -5px;">
                        <i class="fa fa-times"></i>
                    </button>
                </div><br/>
            `;
    
            // Append the file HTML to the assemblyFileNames div
            assemblyFileNames.insertAdjacentHTML('beforeend', assemblyFileHtml);
        }
    
        // Clear the input value to allow selecting the same file again if needed
        assemblyFileInput.value = '';
    });
    
    // Handle file deletion
    $(document).on('click', '.delete-file-btn-unsave', function() {
        var button = $(this); // Reference to the clicked delete button
        var fileId = button.data('id'); // Get the file ID from the data-id attribute
    
        // Remove the file item from the display
        var fileItem = button.closest('.assembly-file-item');
        fileItem.next('br').remove(); // Remove the <br> that comes after the file item
        fileItem.remove(); // Remove the file item itself
    
        // Remove the file from the selectedFiles array
        selectedFiles = selectedFiles.filter(fileObj => fileObj.id !== fileId);
    
        // Create a new DataTransfer object to update the files input
        const dataTransfer = new DataTransfer();
    
        // Add the remaining files to the DataTransfer object
        selectedFiles.forEach(fileObj => {
            dataTransfer.items.add(fileObj.file);
        });
    
        // Update the input files property with the updated DataTransfer files
        assemblyFileInput.files = dataTransfer.files;
    });
});
