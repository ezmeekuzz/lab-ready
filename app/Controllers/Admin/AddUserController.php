<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class AddUserController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Add User | Lab Ready',
            'currentpage' => 'adduser'
        ];
        return view('admin/adduser', $data);
    }
    public function insert()
    {
        $usersModel = new UsersModel();
        $fullname = $this->request->getPost('fullname');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $usertype = $this->request->getPost('usertype');
        $data = [
            'fullname' => $fullname,
            'email' => $email,
            'password' => $password,
            'encryptedpass' => password_hash($password, PASSWORD_BCRYPT),
            'usertype' => $usertype
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
                    'message' => 'User added successfully!',
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to add user.',
                ];
            }
        }

        return $this->response->setJSON($response);
    }
}
