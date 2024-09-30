<?=$this->include('user/header');?>
<div class="app-container">
    <?=$this->include('user/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h4><i class="ti ti-user"></i> Account</h4>
                        </div>
                        <div class="ml-auto d-flex align-items-center">
                            <nav>
                                <ol class="breadcrumb p-0 m-b-0">
                                    <li class="breadcrumb-item">
                                        <a href="<?=base_url();?>"><i class="ti ti-home"></i></a>
                                    </li>
                                    <li class="breadcrumb-item">Dashboard</li>
                                    <li class="breadcrumb-item active text-primary" aria-current="page">Edit Account</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row select-wrapper mt-4">
                <div class="col-lg-12">
                    <div class="card card-statistics">
                        <div class="card-header">
                            <div class="card-heading">
                                <h4 class="card-title"><i class="fa fa-user"></i> Edit Account</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="userinfo" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-lg-6" hidden>
                                        <div class="form-group">
                                            <label for="user_id">User ID</label>
                                            <input type="text" value="<?=$userDetails['user_id'];?>" name="user_id" id="user_id" class="form-control" placeholder="Enter User ID">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="fullname">Full Name</label>
                                            <input type="text" value="<?=$userDetails['fullname'];?>" name="fullname" id="fullname" class="form-control" placeholder="Enter Full Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="companyname">Company Name</label>
                                            <input type="text" value="<?=$userDetails['companyname'];?>" name="companyname" id="companyname" class="form-control" placeholder="Enter Company Name">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" value="<?=$userDetails['email'];?>" name="email" id="email" class="form-control" placeholder="Enter Email Address">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="phonenumber">Phone Number</label>
                                            <input type="tel" value="<?=$userDetails['phonenumber'];?>" name="phonenumber" id="phonenumber" class="form-control" placeholder="Enter Phone Number">
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="address">Address</label>
                                            <input type="text" value="<?=$userDetails['address'];?>" name="address" id="address" class="form-control" placeholder="Enter Address">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="state">State</label>
                                            <input type="text" value="<?=$userDetails['state'];?>" name="state" id="state" class="form-control" placeholder="Enter State">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" value="<?=$userDetails['city'];?>" name="city" id="city" class="form-control" placeholder="Enter City">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="zipcode">Zip Code</label>
                                            <input type="text" value="<?=$userDetails['zipcode'];?>" name="zipcode" id="zipcode" class="form-control" placeholder="Enter Zip Code">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" value="<?=$userDetails['password'];?>" name="password" id="password" class="form-control" placeholder="Enter Password">
                                        </div>
                                    </div>
                                </div>
                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?=$this->include('user/footer');?>
<script type="text/javascript" src="<?=base_url();?>assets/js/userinfo.js"></script>
