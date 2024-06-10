<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\SubscribersModel;

class SendNewsletterController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Send Newsletter | Lab Ready',
            'currentpage' => 'sendnewsletter'
        ];
        return view('admin/sendnewsletter', $data);
    }
    public function sendMessage()
    {
        $subscribersModel = new SubscribersModel();
        $subscribers = $subscribersModel->findAll();
        
        $subject = $this->request->getPost('subject');
        $content = $this->request->getPost('content');
    
        // Email sending code
        $email = \Config\Services::email();
        $email->setSubject($subject);
        $email->setMessage($content);
    
        $successCount = 0;
        $failureCount = 0;
    
        foreach ($subscribers as $subscriber) {
            $email->setTo($subscriber['emailaddress']);
            
            if ($email->send()) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }
    
        if ($successCount > 0) {
            $response = [
                'success' => true,
                'message' => "Newsletter successfully emailed to {$successCount} recipients!",
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to send newsletter email to all recipients.',
            ];
        }
    
        return $this->response->setJSON($response);
    }
}
