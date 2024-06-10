<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SubscribersModel;

class SubscribersController extends BaseController
{
    public function insert()
    {
        $subscribersModel = new SubscribersModel();
        $emailaddress = $this->request->getPost('emailaddress');

        $checkEmail = $subscribersModel->where('emailaddress', $emailaddress)->find();

        if($checkEmail) {
            $response = [
                'success' => false,
                'message' => 'Email already subscribed!'
            ];
            return $this->response->setJSON($response);
        }

        $data = [
            'emailaddress' => $emailaddress
        ];

        $insert = $subscribersModel->insert($data);

        if($insert) {
            $response = [
                'success' => true,
                'message' => 'Successfully Subscribe',
            ];
            return $this->response->setJSON($response);
        }
        else {
            $response = [
                'success' => false,
                'message' => 'Failed to subscribe.',
            ];
            return $this->response->setJSON($response);
        }
    }
}
