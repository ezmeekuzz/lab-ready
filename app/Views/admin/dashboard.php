<?=$this->include('admin/header');?>
<div class="app-container">
    <?=$this->include('admin/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h4><i class="fa fa-dashboard"></i> Dashboard</h4>
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
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xxl-12 m-b-30">
                    <div class="card card-statistics h-100 mb-0">
                        <div class="card-header">
                            <h4 class="card-title">Yearly Sales Report</h4>
                        </div>
                        <div class="card-body pb-0">
                            <div class="row m-b-20">
                                <div class="col-xxs-6 col-xl-4 col-xxl-4 mb-2 mb-xxl-0">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-container img-icon m-r-20 bg-light-gray rounded">
                                            <i class="fa fa-cart-plus text-primary"></i>
                                        </div>
                                        <div class="report-details">
                                            <p>Annual Sales</p>
                                            <h3>15,236</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxs-6 col-md-4 col-xxl-4 mb-2 mb-xxl-0">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-container img-icon m-r-20 bg-light-gray rounded">
                                            <i class="fa fa-dollar text-primary"></i>
                                        </div>
                                        <div class="report-details">
                                            <p>Annual Revenue</p>
                                            <h3>$40,516</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="apexchart-wrapper">
                                <div id="ecommerce5" class="chart-fit"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?=$this->include('admin/footer');?>