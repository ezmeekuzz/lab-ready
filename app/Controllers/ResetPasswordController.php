<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;

class ResetPasswordController extends BaseController
{
    public function index($token)
    {
        $userModel = new UsersModel();
        $user = $userModel->where('reset_token', $token)
                          ->where('token_expires >=', date('Y-m-d H:i:s'))
                          ->first();

        if (!$user) {
            return redirect()->to('/forgot-password')->with('error', 'Invalid or expired token.');
        }
        $data = [
            'title' => 'Reset Password - Lab Ready',
            'token' => $token
        ];
        return view('reset-password', $data);
    }

    public function reset()
    {
        $usersModel = new UsersModel();

        // Validate inputs
        $validation = \Config\Services::validation();
        $validation->setRules([
            'password' => 'required|min_length[6]',
            'cpassword' => 'required|matches[password]',
            'g-recaptcha-response' => 'required',
            'token' => 'required'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validation->getErrors())
            ]);
        }

        // Verify reCAPTCHA
        $recaptcha = $this->request->getPost('g-recaptcha-response');
        if (!$this->verifyRecaptcha($recaptcha)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'reCAPTCHA verification failed.'
            ]);
        }

        // Get the reset token from the form and validate it
        $token = $this->request->getPost('token');
        $user = $usersModel->where('reset_token', $token)
                           ->where('token_expires >=', date('Y-m-d H:i:s'))
                           ->first();

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid or expired token.'
            ]);
        }

        // Hash the new password and update the user record
        $newPassword = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);
        $usersModel->update($user['user_id'], [
            'password' => $this->request->getPost('password'),
            'encryptedpass' => $newPassword,
            'reset_token' => null,           // Clear the reset token
            'token_expires' => null          // Clear token expiration
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Password successfully reset. Please login with your new password.'
        ]);
    }

    private function verifyRecaptcha($recaptchaResponse)
    {
        $secretKey = '6LeJO_ApAAAAACujEczYWsq6UlIPI3aLB-go15dz';
        $url = "https://www.google.com/recaptcha/api/siteverify";
        
        $response = file_get_contents($url . "?secret=" . $secretKey . "&response=" . $recaptchaResponse);
        $result = json_decode($response, true);

        return isset($result['success']) && $result['success'] == true;
    }
}
