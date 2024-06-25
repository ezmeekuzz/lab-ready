<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class HomeController extends BaseController
{
    public function index()
    {
        if (session()->has('user_user_id') && session()->get('user_usertype') == 'Regular User') {
            return redirect()->to('/quotations');
        }
        $data = [
            'title' => 'Login | Lab Ready'
        ];
        return view('user/login', $data);
    }
    public function authenticate()
    {
        $userModel = new UsersModel();
    
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
    
        $result = $userModel
        ->where('email', $email)
        ->where('usertype', 'Regular User')
        ->first();
    
        if ($result && password_verify($password, $result['encryptedpass'])) {
            // Set session data
            session()->set('user_user_id', $result['user_id']);
            session()->set('user_fullname', $result['fullname']);
            session()->set('user_email', $result['email']);
            session()->set('user_usertype', $result['usertype']);
            session()->set('UserLoggedIn', true);
            
            $redirect = '/';

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
