<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;

class LogoutController extends BaseController
{
    public function index()
    {
        // Destroy admin session data
        session()->remove(['user_user_id', 'user_fullname', 'user_email', 'user_usertype', 'UserLoggedIn']);
        
        // Redirect to the admin login page
        return redirect()->to('/user/login');
    }
}
