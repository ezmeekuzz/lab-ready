<?=$this->include('admin/header');?>
<div class="app-container">
    <?=$this->include('admin/sidebar');?>
    <div class="app-main" id="main">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 m-b-30">
                    <div class="d-block d-sm-flex flex-nowrap align-items-center">
                        <div class="page-title mb-2 mb-sm-0">
                            <h1><i class="ti ti-user"></i> Edit User</h1>
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
                                    <li class="breadcrumb-item active text-primary" aria-current="page">Edit User</li>
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
                                <h4 class="card-title"><i class="ti ti-user"></i> Users</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="edituser">
                                <div class="form-group" hidden="">
                                    <label for="user_id">User Id</label>
                                    <input type="text" name="user_id" id="user_id" class="form-control" value="<?=$userDetails['user_id'];?>" placeholder="Enter User ID">
                                </div>
                                <div class="form-group">
                                    <label for="fullname">Full Name</label>
                                    <input type="text" name="fullname" id="fullname" class="form-control" placeholder="Enter Full Name" value="<?=$userDetails['fullname'];?>">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="text" name="email" id="email" class="form-control" value="<?=$userDetails['email'];?>" placeholder="Enter Email Address">
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" value="<?=$userDetails['password'];?>" placeholder="Enter Password">
                                </div>
                                <div class="form-group">
                                    <label for="usertype">Usertype</label>
                                    <select class="form-control chosen-select" name="usertype" id="usertype">
                                        <option <?php if($userDetails['usertype'] == 'Administrator') { echo 'selected'; } ?> value="Administrator">Administrator</option>
                                        <option <?php if($userDetails['usertype'] == 'Regular User') { echo 'selected'; } ?> value="Regular User">Regular User</option>
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
<script src="<?=base_url();?>assets/js/edituser.js"></script>