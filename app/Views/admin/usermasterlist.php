<?=$this->include('admin/header');?>
<div class="app-container">
    <?=$this->include('admin/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h1><i class="ti ti-user"></i> User Masterlist</h1>
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
                                    <li class="breadcrumb-item active text-primary" aria-current="page">User Masterlist</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-statistics">
                        <div class="card-header d-flex align-items-center">
                            <div class="card-heading">
                                <h4 class="card-title"><i class="ti ti-user"></i> Users</h4>
                            </div>
                            <div class="ml-auto">
                                <a id="downloadButton" class="btn btn-primary" href="/usermasterlist/downloadCSV">
                                    <i class="ti ti-download"></i> Download CSV
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="datatable-wrapper table-responsive">
                                <table id="usermasterlist" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Email Address</th>
                                            <th>UserType</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <!-- The table body will be dynamically filled by usermasterlist.js -->
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?=$this->include('admin/footer');?>
<script src="<?=base_url();?>assets/js/usermasterlist.js"></script>
