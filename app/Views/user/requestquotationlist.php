<?=$this->include('user/header');?>
<div class="app-container">
    <?=$this->include('user/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h4><i class="fa fa-archive"></i> Requested Quotes List</h4>
                        </div>
                        <div class="ml-auto d-flex align-items-center">
                            <nav>
                                <ol class="breadcrumb p-0 m-b-0">
                                    <li class="breadcrumb-item">
                                        <a href="/"><i class="ti ti-home"></i></a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        Requested Quotes List
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-lg-3">
                    <select id="yearFilter" class="form-control">
                        <option value="">Select Year</option>
                        <?php
                        $currentYear = date('Y');
                        for ($year = 2020; $year <= $currentYear; $year++) {
                            echo "<option value='$year'>$year</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-lg-3">
                    <select id="monthFilter" class="form-control">
                        <option value="">Select Month</option>
                        <?php
                        $months = array(
                            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                        );
                        foreach ($months as $num => $name) {
                            echo "<option value='$num'>$name</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-lg-6">
                    <button id="filterBtn" class="btn btn-primary">Filter</button>
                    <button id="resetBtn" class="btn btn-secondary">Reset</button>
                </div>
            </div>

            <!-- DataTable Section -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-statistics">
                        <div class="card-header">
                            <div class="card-heading">
                                <!--<h4 class="card-title"><i class="fa fa-archive"></i> Request Quotation List</h4>-->
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="datatable-wrapper table-responsive">
                                <table id="requestquotationmasterlist" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Quotes Number</th>
                                            <th>Nick Name</th>
                                            <th>Status</th>
                                            <th>Date Submitted</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quotation List Modal -->
<div class="modal fade" id="quotationListModal" tabindex="-1" role="dialog" aria-labelledby="quotationListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quotationListModalLabel">Quotation List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="quotationForm" enctype="multipart/form-data">
                    <input type="hidden" name="request_quotation_id" id="request_quotation_id">
                    <input type="hidden" name="status" id="status">
                    <div class="row mb-5" id="downloadAssemblyFiles" style="display: none;">
                        <div class="col-lg-6">
                            <a href="#" class="btn bg-warning text-white mb-2" id="downloadAssembly"><i class="fa fa-download"></i> Download Assembly Print File</a>
                        </div>
                    </div>
                    <div class="col-lg-12 mb-5" id="AssemblyPrintFile">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <span class="text-danger">If assembly is required, please upload ALL assembly prints and models here.</span>
                                    <label for="assemblyFile">(Assembly Print File) Upload Multiple Files</label>
                                    <div class="custom-file">
                                        <label class="custom-file-label" id="assemblyFilesLabel" for="assemblyFile">Click BROWSE and select ALL Assembly file to Upload</label>
                                        <input type="file" class="custom-file-input" id="assemblyFile" name="assemblyFile[]" accept=".pdf, .stl, .step, .iges" multiple>
                                    </div>
                                </div>
                                <div id="assemblyFileNames"></div>
                            </div>
                        </div>
                    </div>
                    <div id="quotationContainer" class="row"></div>
                </form>
                <div class="form-group" id="DropFiles">
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-dark" id="submit_quotation">Submit</button>
                        </div>
                    </div>
                    <label for="invoicefile">Drop Files</label>
                    <div class="upload-area" id="uploadArea">
                        <h2>Drag & Drop CAD Files (STEP, IGES AND STL Only)</h2>
                        <p>or</p>
                        <button type="button" id="fileSelectBtn">Select Files</button>
                        <input type="file" id="fileInput" name="files" multiple hidden accept=".step,.iges,.igs,.pdf,.STEP,.IGES,.IGS,.PDF">
                        <div id="fileList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?=$this->include('user/footer');?>
<script>
    let baseURL = "<?=base_url();?>";
</script>
<script src="<?=base_url();?>assets/js/requestquotationlist.js"></script>
