<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ContactUsController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Contact Us - Lab Ready'
        ];
        return view('contactus', $data);
    }
    public function sendMessage()
    {
        /*$recaptchaResponse = $this->request->getVar('g-recaptcha-response');
        $secretKey = '6LehBM0pAAAAALnH2DU-gMZXguCX34eGf7Tf07Da'; // Replace with your secret key
        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $recaptchaResponse);
        $responseData = json_decode($verifyResponse);
    
        if (!$responseData->success) {
            // reCAPTCHA validation failed
            return json_encode(['success' => false, 'message' => 'reCAPTCHA verification failed.']);
        }*/
        $data = [
            'fullname' => $this->request->getPost('fullName'),
            'email' => $this->request->getPost('email'),
            'phonenumber' => $this->request->getPost('phoneNumber'),
            'companyName' => $this->request->getPost('companyName'),
            'message' => $this->request->getPost('message')
        ];
    
        $content = "";

        $content .= "Email : " . $data['email'] . "<br/>";
        $content .= "Phone Number : " . $data['phonenumber'] . "<br/>";
        $content .= "Message : " . $data['message'] . "<br/>";
        // Email sending code
        $email = \Config\Services::email();
        $email->setTo('charlie@lab-ready.net');
        $email->setSubject('You\'ve got a new message!');
        $email->setMessage($content);

        if ($email->send()) {
            $response = [
                'success' => 'success',
                'message' => 'We will get back at you soon!',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to send message!',
            ];
        }

        return $this->response->setJSON($response);
    }
}
