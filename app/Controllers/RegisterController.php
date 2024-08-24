<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class RegisterController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Register - Lab Ready'
        ];
        return view('register', $data);
    }
    public function insert()
    {
        $usersModel = new UsersModel();
        $fullName = $this->request->getPost('fullname');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $phonenumber = $this->request->getPost('phonenumber');
        $companyname = $this->request->getPost('companyname');
        $address = $this->request->getPost('address');
        $city = $this->request->getPost('city');
        $state = $this->request->getPost('state');
        $data = [
            'fullname' => $fullName,
            'email' => $email,
            'phonenumber' => $phonenumber,
            'companyname' => $companyname,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'password' => $password,
            'encryptedpass' => password_hash($password, PASSWORD_BCRYPT),
            'usertype' => 'Regular User'
        ];
        $userList = $usersModel->where('email', $email)->first();
        if($userList) {
            $response = [
                'success' => false,
                'message' => 'Email is not available',
            ];
        }
        else {
            $userId = $usersModel->insert($data);
    
            if ($userId) {
                $response = [
                    'success' => 'success',
                    'message' => 'Successfully Registered!',
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to register.',
                ];
            }
        }

        return $this->response->setJSON($response);
    }
}
