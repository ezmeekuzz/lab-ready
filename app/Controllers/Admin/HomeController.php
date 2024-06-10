<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class HomeController extends BaseController
{
    public function index()
    {
        if (session()->has('admin_user_id') && session()->get('admin_usertype') == 'Administrator') {
            return redirect()->to('/send-quotation');
        }
        $data = [
            'title' => 'Login | Lab Ready'
        ];
        return view('admin/login', $data);
    }
    public function authenticate()
    {
        $userModel = new UsersModel();
    
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
    
        $result = $userModel
        ->where('email', $email)
        ->where('usertype', 'Administrator')
        ->first();
    
        if ($result && password_verify($password, $result['encryptedpass'])) {
            // Set session data
            session()->set('admin_user_id', $result['user_id']);
            session()->set('admin_fullname', $result['fullname']);
            session()->set('admin_email', $result['email']);
            session()->set('admin_usertype', $result['usertype']);
            session()->set('AdminLoggedIn', true);
            
            $redirect = '/send-quotation';

            // Prepare response
            $response = [
                'success' => true,
                'redirect' => $redirect, // Redirect URL upon successful login
                'message' => 'Login successful'
            ];
        } else {
            // Prepare response for invalid login
            $response = [
                'success' => false,
                'message' => 'Invalid login credentials'
            ];
        }
    
        // Return JSON response
        return $this->response->setJSON($response);
    }  
}
