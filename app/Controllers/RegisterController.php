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
        $data = [
            'fullname' => $fullName,
            'email' => $email,
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