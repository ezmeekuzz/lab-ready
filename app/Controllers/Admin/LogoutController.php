<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class LogoutController extends BaseController
{
    public function index()
    {
        // Destroy admin session data
        session()->remove(['admin_user_id', 'admin_fullname', 'admin_email', 'admin_usertype', 'AdminLoggedIn']);
        
        // Redirect to the admin login page
        return redirect()->to('/admin/login');
    }
}
