<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class ForgotPasswordController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Forgot Password - Lab Ready'
        ];
        return view('forgot-password', $data);
    }
    public function sendEmail()
    {
        $email = $this->request->getPost('email');
        $captchaResponse = $this->request->getPost('g-recaptcha-response');
        
        // Verify reCAPTCHA response
        $secretKey = '6LeJO_ApAAAAACujEczYWsq6UlIPI3aLB-go15dz';
        $captchaVerified = $this->verifyRecaptcha($captchaResponse, $secretKey);

        if (!$captchaVerified) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid reCAPTCHA. Please try again.']);
        }

        $userModel = new UsersModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'Email not found.']);
        }

        // Generate a token and expiration time for password reset
        $token = bin2hex(random_bytes(32));
        $userModel->update($user['user_id'], ['reset_token' => $token, 'token_expires' => date('Y-m-d H:i:s', strtotime('+1 hour'))]);

        // Send email with reset link
        $resetLink = base_url('reset-password/' . $token);
        $this->sendResetEmail($email, $resetLink);

        return $this->response->setJSON(['success' => true, 'message' => 'A password reset link has been sent to your email.']);
    }

    private function verifyRecaptcha($captchaResponse, $secretKey)
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = file_get_contents($url . '?secret=' . $secretKey . '&response=' . $captchaResponse);
        $result = json_decode($response, true);
        return isset($result['success']) && $result['success'] == true;
    }

    private function sendResetEmail($email, $resetLink)
    {
        $emailService = \Config\Services::email();

        $emailService->setTo($email);
        $emailService->setSubject('Password Reset Request');
        
        // Load the email template
        $message = view('emails/reset_password', ['resetLink' => $resetLink]);
        $emailService->setMessage($message);
        
        return $emailService->send();
    }
}
