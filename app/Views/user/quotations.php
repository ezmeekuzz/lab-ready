<?=$this->include('user/header');?>
<div class="app-container">
    <?=$this->include('user/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h4><i class="fa fa-file-text-o"></i> Quotes</h4>
                        </div>
                        <div class="ml-auto d-flex align-items-center">
                            <nav>
                                <ol class="breadcrumb p-0 m-b-0">
                                    <li class="breadcrumb-item">
                                        <a href="/"><i class="ti ti-home"></i></a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        Quotes
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter section -->
            <div class="row mb-5">
                <div class="col-lg-12 d-flex justify-content-end">
                    <div class="col-lg-4 p-0">
                        <div class="search-box">
                            <input type="text" id="searchBox" class="form-control" placeholder="Search Quotes">
                        </div>
                    </div>
                    <!-- Year Dropdown -->
                    <div class="col-lg-2">
                        <select id="yearFilter" class="form-control">
                            <option value="">Select Year</option>
                            <?php
                            $currentYear = date("Y");
                            for ($year = 2020; $year <= $currentYear; $year++) {
                                echo "<option value=\"$year\">$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <!-- Month Dropdown -->
                    <div class="col-lg-2">
                        <select id="monthFilter" class="form-control">
                            <option value="">Select Month</option>
                            <?php
                            $months = array(
                                '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                                '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                                '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
                            );
                            foreach ($months as $num => $name) {
                                echo "<option value=\"$num\">$name</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- No quotes message -->
            <div class="row">
                <div class="col-lg-12">
                    <div id="noQuotationsMessage" class="alert alert-info" style="display: none;">
                        No quotations available.
                    </div>
                </div>
            </div>

            <!-- Quotations display area -->
            <div class="row" id="card-columns">
                
            </div>
        </div>
    </div>
</div>

<!-- Modal for quotation details -->
<div class="modal fade" id="quotationDetails" tabindex="-1" role="dialog" aria-labelledby="quotationDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productName"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="text-align: justify;">
                <div id="displayDetails"></div>
            </div>
        </div>
    </div>
</div>
<?=$this->include('user/footer');?>
<script>
    let address = "<?=session()->get('user_address');?>";
    let state = "<?=session()->get('user_state');?>";
    let city = "<?=session()->get('user_city');?>";
    let zipcode = "<?=session()->get('user_zipcode');?>";
    let phonenumber = "<?=session()->get('user_phonenumber');?>";
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<!-- Filter functionality script -->
<script src="<?=base_url();?>assets/js/quotations.js"></script>
