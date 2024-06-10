<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class EditUserController extends SessionController
{
    public function index($id)
    {
        $usersModel = new UsersModel();
        $userDetails = $usersModel->find($id);
        $data = [
            'title' => 'Edit User | PageDuo',
            'currentpage' => 'usermasterlist',
            'userDetails' => $userDetails
        ];
        return view('admin/edituser', $data);
    }
    public function update()
    {
        $usersModel = new UsersModel();
        $userId = $this->request->getPost('user_id');
        $fullname = $this->request->getPost('fullname');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $usertype = $this->request->getPost('usertype');
        $data = [
            'fullname' => $fullname,
            'email' => $email,
            'usertype' => $usertype // Assuming usertype should not be updated here
        ];
    
        // Check if password is provided and update password fields accordingly
        if (!empty($password)) {
            $data['password'] = $password;
            $data['encryptedpass'] = password_hash($password, PASSWORD_BCRYPT);
        }
    
        // Check if the provided username is already in use
        $userList = $usersModel->where('email', $email)->where('user_id !=', $userId)->first();
        if ($userList) {
            $response = [
                'success' => false,
                'message' => 'Email is not available',
            ];
        } else {
            // Update the user data
            $updated = $usersModel->update($userId, $data);
    
            if ($updated) {
                $response = [
                    'success' => true,
                    'message' => 'User updated successfully!',
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to update user.',
                ];
            }
        }
    
        return $this->response->setJSON($response);
    }    
}
