<?=$this->include('admin/header');?>
<div class="app-container">
    <?=$this->include('admin/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h4><i class="fa fa-paper-plane"></i> Send Quotation</h4>
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
                                    <li class="breadcrumb-item active text-primary" aria-current="page">Send Quotation</li>
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
                                <h4 class="card-title"><i class="fa fa-paper-plane"></i> Send Quotation</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="sendquotation">
                                <div class="form-group">
                                    <label for="productname">Product Name</label>
                                    <input type="text" name="productname" id="productname" class="form-control" placeholder="Enter Product Name">
                                </div>
                                <div class="form-group">
                                    <label for="productprice">Product Price</label>
                                    <input type="text" name="productprice" id="productprice" class="form-control" placeholder="Enter Product Price">
                                </div>
                                <div class="form-group">
                                    <label for="productdetails">Product Details</label>
                                    <textarea class="form-control" name="productdetails" id="productdetails" rows="5" placeholder="Enter Product Details"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="invoicefile">Invoice File</label>
                                    <div class="custom-file">
                                        <label class="custom-file-label" for="invoicefile">Choose file</label>
                                        <input type="file" class="custom-file-input" id="invoicefile" name="invoicefile" accept="application/pdf">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="usertype">User Account</label>
                                    <select class="form-control chosen-select" data-placeholder = "Select a user" name="user_id" id="user_id">
                                        <option hidden></option>
                                        <option disabled></option>
                                        <?php if($userList) : ?>
                                        <?php foreach($userList as $list) : ?>
                                        <option value="<?=$list['user_id'];?>"><?=$list['fullname'];?></option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
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
<script src="<?=base_url();?>assets/js/sendquotation.js"></script>