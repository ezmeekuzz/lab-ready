<?php

namespace App\Controllers\User;

use App\Controllers\User\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class UserInfoController extends SessionController
{
    public function index()
    {
        $usersModel = new UsersModel();
        $userDetails = $usersModel->find(session()->get('user_user_id'));
        $data = [
            'title' => 'User Info | Lab Ready',
            'currentpage' => 'userinfo',
            'userDetails' => $userDetails
        ];
        return view('user/userinfo', $data);
    }
    public function update()
    {
        $usersModel = new UsersModel();
        $userId = session()->get('user_user_id');
        $fullname = $this->request->getPost('fullname');
        $email = $this->request->getPost('email');
        $companyname = $this->request->getPost('companyname');
        $phonenumber = $this->request->getPost('phonenumber');
        $address = $this->request->getPost('address');
        $state = $this->request->getPost('state');
        $city = $this->request->getPost('city');
        $zipcode = $this->request->getPost('zipcode');
        $password = $this->request->getPost('password');
        $data = [
            'fullname' => $fullname,
            'email' => $email,
            'companyname' => $companyname,
            'phonenumber' => $phonenumber,
            'address' => $address,
            'state' => $state,
            'city' => $city,
            'zipcode' => $zipcode,
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
