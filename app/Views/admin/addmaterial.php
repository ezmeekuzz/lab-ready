<?=$this->include('admin/header');?>
<div class="app-container">
    <?=$this->include('admin/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h1><i class="ti ti-file"></i> Add Material</h1>
                        </div>
                        <div class="ml-auto d-flex align-items-center">
                            <nav>
                                <ol class="breadcrumb p-0 m-b-0">
                                    <li class="breadcrumb-item">
                                        <a href="/"><i class="ti ti-home"></i></a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        Dashboard
                                    </li>
                                    <li class="breadcrumb-item active text-primary" aria-current="page">Add Material</li>
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
                                <h4 class="card-title"><i class="ti ti-file"></i> Materials</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="addmaterial">
                                <div class="form-group">
                                    <label for="materialname">Select Manufacturing Service</label>
                                    <select name="quotetype" id="quotetype" class="form-control">
                                        <option hidden></option>
                                        <option disabled>Select Manufacturing Service</option>
                                        <option value="3D Printing">3D Printing</option>
                                        <option value="CNC Machine">CNC Machine</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="materialname">Material Name</label>
                                    <input type="text" name="materialname" id="materialname" class="form-control" placeholder="Material Name">
                                </div>
                                <button type="submit" class="btn btn-dark">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?=$this->include('admin/footer');?>
<script src="<?=base_url();?>assets/js/addmaterial.js"></script>