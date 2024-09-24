<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class HomeController extends BaseController
{
    public function index()
    {
        $redirectToQuotation = ($this->request->getGet('redirect') == 'quote') ? 'request-quotation' : 'quotations';

        if (session()->has('user_user_id') && session()->get('user_usertype') == 'Regular User') {
            return redirect()->to('/' . $redirectToQuotation);
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
        $redirect = $this->request->getPost('redirect');
    
        $result = $userModel
            ->where('email', $email)
            ->where('usertype', 'Regular User')
            ->first();
    
        if ($result && password_verify($password, $result['encryptedpass'])) {
            // Set session data
            session()->set([
                'user_user_id' => $result['user_id'],
                'user_fullname' => $result['fullname'],
                'user_email' => $result['email'],
                'user_phonenumber' => $result['phonenumber'],
                'user_companyname' => $result['companyname'],
                'user_address' => $result['address'],
                'user_state' => $result['state'],
                'user_city' => $result['city'],
                'user_zipcode' => $result['zipcode'],
                'user_usertype' => $result['usertype'],
                'UserLoggedIn' => true,
            ]);
    
            $redirectUrl = ($redirect === 'quote') ? '/request-quotation' : (($redirect === 'quotation') ? '/quotations' : '/');
    
            // Prepare response
            $response = [
                'success' => true,
                'redirect' => $redirectUrl,
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
