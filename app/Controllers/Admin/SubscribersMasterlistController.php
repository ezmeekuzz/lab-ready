<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SubscribersModel;

class SubscribersMasterlistController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Subscribers Masterlist | Lab Ready',
            'currentpage' => 'subscribersmasterlist'
        ];
        return view('admin/subscribersmasterlist', $data);
    }
    public function getData()
    {
        return datatables('subscribers')->make();
    }
    public function delete($id)
    {
        $subscribersModel = new SubscribersModel();
    
        $deleted = $subscribersModel->delete($id);
    
        if ($deleted) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the subscriber from the database']);
        }
    }
}
