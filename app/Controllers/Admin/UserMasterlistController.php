<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class UserMasterlistController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'User Masterlist | Lab Ready',
            'currentpage' => 'usermasterlist'
        ];
        return view('admin/usermasterlist', $data);
    }
    public function getData()
    {
        return datatables('users')->make();
    }
    public function delete($id)
    {
        $UsersModel = new UsersModel();
    
        // Find the users by ID
        $users = $UsersModel->find($id);
    
        if ($users) {
    
            // Delete the record from the database
            $deleted = $UsersModel->delete($id);
    
            if ($deleted) {
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the users from the database']);
            }
        }
    
        return $this->response->setJSON(['status' => 'error', 'message' => 'users not found']);
    } 
}
