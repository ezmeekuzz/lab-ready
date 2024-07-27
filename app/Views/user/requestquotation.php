<?=$this->include('user/header');?>
<div class="app-container">
    <?=$this->include('user/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h4><i class="fa fa-file-pdf-o"></i> Request Quotation</h4>
                        </div>
                        <div class="ml-auto d-flex align-items-center">
                            <nav>
                                <ol class="breadcrumb p-0 m-b-0">
                                    <li class="breadcrumb-item">
                                        <a href="/"><i class="ti ti-home"></i></a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        Request Quotation
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-statistics">
                        <div class="card-header">
                            <div class="card-heading">
                                <h4 class="card-title"><i class="fa fa-file-pdf-o"></i> Request Quotation</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <form class="mb-5" id="submitQuotation">
                                <div class="row" id="assembly" style="display: none;">
                                    <div class="col-lg-12 mb-5">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <span class="text-danger">If fit, finish and assembly is required. Please assembly print here.</span>
                                                    <label for="assemblyFile">(Assembly Print File) Upload Multiple Files</label>
                                                    <div class="custom-file">
                                                        <label class="custom-file-label" for="assemblyFile">Choose file</label>
                                                        <input type="file" class="custom-file-input" id="assemblyFile" name="assemblyFile[]" accept="application/pdf" multiple>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="formsContainer"></div>
                                <div class="row" id="submitBTN" style="display: none;">
                                    <div class="col-lg-12">
                                        <button type="button" id="submitAll" class="btn bg-dark text-white">Submit</button>
                                    </div>
                                </div>
                            </form>
                            <form id="requestquotation">
                                <div class="form-group">
                                    <label for="invoicefile">Drop Files</label>
                                    <div class="upload-area" id="uploadArea">
                                        <h2>Drag & Drop CAD Files (STEP, IGES AND STL Only)</h2>
                                        <p>or</p>
                                        <button type="button" id="fileSelectBtn">Select Files</button>
                                        <input type="file" id="fileInput" name="files" multiple hidden accept=".step,.iges,.igs,.pdf,.STEP,.IGES,.IGS,.PDF">
                                        <div id="fileList"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
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
<script src="<?=base_url();?>assets/js/requestquotation.js"></script>
