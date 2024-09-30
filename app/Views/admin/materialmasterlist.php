<?=$this->include('admin/header');?>
<div class="app-container">
    <?=$this->include('admin/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h1><i class="ti ti-file"></i> Material Masterlist</h1>
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
                                    <li class="breadcrumb-item active text-primary" aria-current="page">Material Masterlist</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Row for buttons -->
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="btn-group" role="group" aria-label="Quote Type Filter">
                        <button type="button" class="btn btn-primary" id="filter-cnc" data-quotetype="CNC Machine">CNC Machine</button>
                        <button type="button" class="btn btn-secondary" id="filter-3d" data-quotetype="3D Printing">3D Printing</button>
                    </div>
                </div>
            </div>

            <!-- Material Masterlist Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-statistics">
                        <div class="card-header">
                            <div class="card-heading">
                                <h4 class="card-title"><i class="ti ti-file"></i> Materials</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="datatable-wrapper table-responsive">
                                <table id="materialmasterlist" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Quote Type</th>
                                            <th>Material Name</th>
                                            <th>Order</th>
                                            <th>Actions</th>
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

<!-- Order Sequence Modal -->
<div class="modal fade" id="arrangeOrderModal" tabindex="-1" role="dialog" aria-labelledby="arrangeOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="arrangeOrderModalLabel">Order Sequence</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="orderContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?=$this->include('admin/footer');?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src="<?=base_url();?>assets/js/materialmasterlist.js"></script>