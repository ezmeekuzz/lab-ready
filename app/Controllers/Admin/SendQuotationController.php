<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\QuotationsModel;
use App\Models\UsersModel;
use App\Models\UserQuotationsModel;
use App\Models\RequestQuotationModel;

class SendQuotationController extends SessionController
{
    public function index()
    {
        $usersModel = new UsersModel();
        $userList = $usersModel->where('usertype', 'Regular User')->findAll();
        $data = [
            'title' => 'Send Quotation | Lab Ready',
            'currentpage' => 'sendquotation',
            'userList' => $userList
        ];
        return view('admin/sendquotation', $data);
    }
    public function insert()
    {
        $quotationsModel = new QuotationsModel();
        $requestQuotationModel = new RequestQuotationModel();
        $userQuotationsModel = new UserQuotationsModel();
        $productName = $this->request->getPost('productname');
        $productPrice = $this->request->getPost('productprice');
        $invoiceFile = $this->request->getFile('invoicefile');
        
        $dataRequestQuotation = [
            'reference' =>  $productName,//$this->generateReference($this->request->getPost('userId')),
            'user_id' => $this->request->getPost('userId'),
            'status' => 'Done',
            'datesubmitted' => date('Y-m-d')
        ];

        $requestSubmitted = $requestQuotationModel->insert($dataRequestQuotation);

        $errors = [];
    
        // Check each field individually
        if (empty($productName)) {
            $errors[] = 'Product Name';
        }
        if (empty($productPrice)) {
            $errors[] = 'Product Price';
        }
        if (!$invoiceFile->isValid()) {
            $errors[] = 'Invoice File';
        }
    
        // If there are any errors, return them
        if (!empty($errors)) {
            $errorMessage = 'Please fill in the following fields: ' . implode(', ', $errors);
            $response = [
                'success' => false,
                'message' => $errorMessage,
            ];
            return $this->response->setJSON($response);
        }
    
        // Upload invoice file
        $newFileName = $invoiceFile->getRandomName();
        $invoiceFile->move(FCPATH . 'uploads/PDFs', $newFileName);
    
        // Prepare data for insertion
        $data = [
            'request_quotation_id' => $requestSubmitted,
            'productname' => $productName,
            'productprice' => $productPrice,
            'invoicefile' => '/uploads/PDFs/' . $newFileName,
            'filename' => $invoiceFile->getClientName(),
            'quotationdate' => date('Y-m-d'),
            'status' => 'Unpaid'
        ];
    
        // Insert data into database
        $inserted = $quotationsModel->insert($data);
    
        if ($inserted) {
            $usersModel = new UsersModel();

            $userDetails = $usersModel->find($this->request->getPost('userId'));

            $data = [
                'fullname' => $userDetails['fullname'],
                'reference' => $productName,
            ];
            $thankYouMessage = view('emails/thank-you', $data);
    
            $email = \Config\Services::email();
            $email->setTo($userDetails['email']);
            $email->setSubject('Thank you for your quotation request!');
            $email->setMessage($thankYouMessage);
            $email->setMailType('html');

            if ($email->send()) {

                $userQuotationsModel->insert([
                    'user_id' => $this->request->getPost('userId'),
                    'quotation_id' => $inserted,
                    'dateforwarded' => date('Y-m-d'),
                    'readstatus' => 'Unread'
                ]);
                $response = [
                    'success' => true,
                    'message' => 'Quotation forwarded successfully!',
                ];

            } else {
                log_message('error', 'Failed to send thank you email to user: ' . $userEmail);
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to forward quotation.',
            ];
        }
    
        return $this->response->setJSON($response);
    }
    
    private function generateReference($userId)
    {
        $requestQuotationModel = new RequestQuotationModel();
    
        // Get today's date in YYYYMMDD format
        $todayDate = date('Ymd');
    
        // Count existing requests for the user on the current date
        $count = $requestQuotationModel->like('reference', $todayDate, 'after')->where('user_id', $userId)->countAllResults() + 1;
    
        // Generate the reference in YYYYMMDD-NNN format
        $reference = $todayDate . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        return $reference;
    }
}
