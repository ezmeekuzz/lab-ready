<?php

namespace App\Controllers\User;

use App\Controllers\User\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\AuthorizeNet;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use App\Models\UserQuotationsModel;
use App\Models\QuotationsModel;
use App\Models\RequestQuotationModel;
use App\Models\UsersModel;

class QuotationsController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Quotations | Lab Ready',
            'currentpage' => 'quotations'
        ];
        return view('user/quotations', $data);
    }
    public function getData()
    {
        $userQuotationsModel = new UserQuotationsModel();
        $search = $this->request->getVar('search');
    
        // Get year and month from request, or use the current year and month if not provided
        $year = $this->request->getVar('year') ?: date('Y');
        $month = $this->request->getVar('month') ?: date('m');
    
        // Query to get the quotations joined with related tables
        $userQuotationList = $userQuotationsModel
            ->join('quotations', 'quotations.quotation_id=user_quotations.quotation_id', 'left')
            ->join('request_quotations', 'request_quotations.request_quotation_id=quotations.request_quotation_id', 'left')
            ->where('user_quotations.user_id', session()->get('user_user_id'));
    
        // Apply search filter if provided
        if ($search) {
            $userQuotationList = $userQuotationList->like('request_quotations.reference', $search);
        }
    
        // Apply year and month filter by default to the current year and month
        $userQuotationList = $userQuotationList->where('YEAR(quotations.quotationdate)', $year)
                                               ->where('MONTH(quotations.quotationdate)', $month);
    
        // Fetch the filtered quotations
        $userQuotationList = $userQuotationList->findAll();
    
        // Return the filtered results as JSON
        return $this->response->setJSON($userQuotationList);
    }      
    
    public function quotationDetails()
    {
        $userQuotationId = $this->request->getVar('userQuotationId');
        
        $userQuotationsModel = new UserQuotationsModel();
        $userQuotationsModel->where('user_quotation_id', $userQuotationId)
        ->set('readstatus', 'Read')
        ->update();
        $quotationDetails = $userQuotationsModel
        ->join('quotations', 'quotations.quotation_id=user_quotations.quotation_id', 'left')
        ->join('shipments', 'quotations.quotation_id=shipments.quotation_id', 'left')
        ->find($userQuotationId);
        
        return $this->response->setJSON($quotationDetails);
    }    
    public function pay()
    {
        $quotationId = $this->request->getPost('quotationId');
        $address = $this->request->getPost('address');
        $city = $this->request->getPost('city');
        $state = $this->request->getPost('state');
        $zipcode = $this->request->getPost('zipcode');
        $quotationsModel = new QuotationsModel();
        $usersModel = new UsersModel();
        $requestQuotationModel = new RequestQuotationModel();
        $data = [
            'quotationnId' => $quotationId
        ];
        $updated = $quotationsModel->where('quotation_id', $quotationId)
        ->set('status', 'Paid')
        ->set('address', $address)
        ->set('city', $city)
        ->set('state', $state)
        ->set('zipcode', $zipcode)
        ->update();

        $quotationDetails = $quotationsModel->find($quotationId);

        $requestQuotationModel->where('request_quotation_id', $quotationDetails['request_quotation_id'])
        ->set('status', 'Paid')
        ->update();
    
        if ($updated) {
            $userDetails = $usersModel->find(session()->get('user_user_id'));
            $requestQuotationDetails = $requestQuotationModel->find($quotationDetails['request_quotation_id']);
            $data = [
                'userDetails' => $userDetails,
                'requestQuotationDetails' => $requestQuotationDetails,
                'quotationDetails' => $quotationDetails,
            ];
            $message = view('emails/payment-success', $data);
            // Email sending code
            $pdfFilePath = FCPATH . $quotationDetails['invoicefile'];
            $this->adminEmailReceived($data);
            $email = \Config\Services::email();
            $email->setTo($userDetails['email']);
            $email->setSubject('We\'ve got you\'re payment!');
            $email->setMessage($message);
            $email->attach($pdfFilePath, 'attachment', $quotationDetails['filename']);
            if ($email->send()) {
                $response = [
                    'success' => true,
                    'message' => 'Successfully Paid!',
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to send message!',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Payment Failed!',
            ];
        }
    
        return $this->response->setJSON($response);
    }
    private function adminEmailReceived($data)
    {
        $message = "";
        $reference = $data['requestQuotationDetails']['reference'] ?? null; // Check if 'reference' exists
        $productName = $data['quotationDetails']['productname'];

        // Use the reference if it exists and is not empty, otherwise use the product name
        $message .= "An order has been paid with this Quotation Number: " . (!empty($reference) ? $reference : $productName);
        
        $email = \Config\Services::email();
        $email->setTo('charlie@lab-ready.net');
        $email->setSubject('Quotation Payment');
        $email->setMessage($message);
        $email->send();
    }
    public function deleteQuotation($id)
    {
        $userQuotationsModel = new UserQuotationsModel();
    
        // Find the users by ID
        $quotations = $userQuotationsModel->find($id);
    
        if ($quotations) {
    
            // Delete the record from the database
            $deleted = $userQuotationsModel->delete($id);
    
            if ($deleted) {
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the users from the database']);
            }
        }
    
        return $this->response->setJSON(['status' => 'error', 'message' => 'users not found']);
    }
    public function chargeCreditCard()
    {
        helper('form');
    
        $address = $this->request->getPost('address');
        $city = $this->request->getPost('city');
        $state = $this->request->getPost('state');
        $zipcode = $this->request->getPost('zipcode');
        $phoneNumber = $this->request->getPost('phoneNumber');
        $quotationId = $this->request->getPost('quotationId');
    
        $config = new \Config\AuthorizeNet();
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($config->apiLoginId);
        $merchantAuthentication->setTransactionKey($config->transactionKey);
    
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($this->request->getPost('cardNumber'));
        $creditCard->setExpirationDate($this->request->getPost('expirationDate'));
        $creditCard->setCardCode($this->request->getPost('cvv'));
    
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);
    
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($this->request->getPost('amount'));
        $transactionRequestType->setPayment($paymentOne);
    
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransactionRequest($transactionRequestType);
    
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse($config->sandbox ? \net\authorize\api\constants\ANetEnvironment::SANDBOX : \net\authorize\api\constants\ANetEnvironment::PRODUCTION);
    
        $data = [
            'quotationnId' => $quotationId
        ];
        if ($response != null) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();
    
                if ($tresponse != null && $tresponse->getMessages() != null) {
                    $quotationsModel = new QuotationsModel();
                    $requestQuotationsModel = new RequestQuotationModel();
                    $usersModel = new UsersModel();
                    $requestQuotationDetails = $requestQuotationsModel->where('request_quotation_id', $quotationId)->find();
                    $userDetails = $usersModel->find(session()->get('user_user_id'));
                    $updated = $quotationsModel->where('quotation_id', $quotationId)
                        ->set('address', $address)
                        ->set('city', $city)
                        ->set('state', $state)
                        ->set('zipcode', $zipcode)
                        ->set('phonenumber', $phoneNumber)
                        ->set('status', 'Paid')
                        ->update();
                    $quotationDetails = $quotationsModel->find($quotationId);

                    $data = [
                        'userDetails' => $userDetails,
                        'quotationDetails' => $quotationDetails,
                        'requestQuotationDetails' => $requestQuotationDetails
                    ];
                        
                    $requestQuotationsModel->where('request_quotation_id', $quotationDetails['request_quotation_id'])
                    ->set('status', 'Paid')
                    ->update();

                    $message = view('emails/payment-success', $data);
                    // Email sending code
                    $pdfFilePath = FCPATH . $quotationDetails['invoicefile'];
                    $this->adminEmailReceived($data);
                    $email = \Config\Services::email();
                    $email->setTo($userDetails['email']);
                    $email->setSubject('We\'ve got you\'re payment!');
                    $email->setMessage($message);
                    $email->attach($pdfFilePath, 'attachment', $quotationDetails['filename']);
                    if ($email->send()) {
                        $response = [
                            'success' => true,
                            'message' => 'Successfully Paid!',
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Failed to send message!',
                        ];
                    }
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Transaction Successful: ' . $tresponse->getMessages()[0]->getDescription()
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Transaction Failed: ' . $tresponse->getErrors()[0]->getErrorText()
                    ]);
                }
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction Failed: ' . $response->getMessages()->getMessage()[0]->getText()
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No response returned'
            ]);
        }
    } 
    public function chargeEcheck()
    {
        helper('form');
    
        $address = $this->request->getPost('address');
        $city = $this->request->getPost('city');
        $state = $this->request->getPost('state');
        $zipcode = $this->request->getPost('zipcode');
        $phoneNumber = $this->request->getPost('phoneNumber');
        $quotationId = $this->request->getPost('quotationId');
    
        // Get API credentials
        $config = new \Config\AuthorizeNet();
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($config->apiLoginId);
        $merchantAuthentication->setTransactionKey($config->transactionKey);
    
        // Log credentials for debugging (ensure sensitive data isn't logged in production)
        log_message('info', 'API Login ID: ' . $config->apiLoginId);
    
        // Create Bank Account
        $bankAccount = new AnetAPI\BankAccountType();
        $bankAccount->setRoutingNumber($this->request->getPost('routingNumber'));
        $bankAccount->setAccountNumber($this->request->getPost('accountNumber'));
        $bankAccount->setNameOnAccount($this->request->getPost('accountHolder')); // Fixed to match field from form
        $bankAccount->setAccountType($this->request->getPost('accountType')); // Checking or Savings from form input
    
        $payment = new AnetAPI\PaymentType();
        $payment->setBankAccount($bankAccount);
    
        // Transaction Request
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($this->request->getPost('amount'));
        $transactionRequestType->setPayment($payment);
    
        // Create Transaction Request
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setTransactionRequest($transactionRequestType);
    
        // Send request to Authorize.net
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(
            $config->sandbox ? \net\authorize\api\constants\ANetEnvironment::SANDBOX : \net\authorize\api\constants\ANetEnvironment::PRODUCTION
        );
    
        // Log the response for debugging
        log_message('info', 'Authorize.net Response: ' . print_r($response, true));
    
        // Handle response and update database
        if ($response != null && $response->getMessages()->getResultCode() == "Ok") {
            $tresponse = $response->getTransactionResponse();
            if ($tresponse != null && $tresponse->getMessages() != null) {
                $quotationsModel = new QuotationsModel();
                $requestQuotationsModel = new RequestQuotationModel();
                $usersModel = new UsersModel();
    
                $updated = $quotationsModel->where('quotation_id', $quotationId)
                    ->set('address', $address)
                    ->set('city', $city)
                    ->set('state', $state)
                    ->set('zipcode', $zipcode)
                    ->set('phonenumber', $phoneNumber)
                    ->set('status', 'Paid')
                    ->update();
    
                $quotationDetails = $quotationsModel->find($quotationId);
                $requestQuotationDetails = $requestQuotationsModel->where('request_quotation_id', $quotationDetails['request_quotation_id'])->find();
                $userDetails = $usersModel->find(session()->get('user_user_id'));
    
                // Send email notification
                $data = [
                    'userDetails' => $userDetails,
                    'quotationDetails' => $quotationDetails,
                    'requestQuotationDetails' => $requestQuotationDetails
                ];
    
                $message = view('emails/payment-success', $data);
                $pdfFilePath = FCPATH . $quotationDetails['invoicefile'];

                $this->adminEmailReceived($data);
    
                // Send Email
                $email = \Config\Services::email();
                $email->setTo($userDetails['email']);
                $email->setSubject('We\'ve got your payment!');
                $email->setMessage($message);
                $email->attach($pdfFilePath, 'attachment', $quotationDetails['filename']);
    
                if ($email->send()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Transaction Successful: ' . $tresponse->getMessages()[0]->getDescription()
                    ]);
                } else {
                    log_message('error', 'Email sending failed.');
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Transaction successful, but email sending failed.'
                    ]);
                }
            } else {
                log_message('error', 'Transaction failed: ' . $tresponse->getErrors()[0]->getErrorText());
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction Failed: ' . $tresponse->getErrors()[0]->getErrorText()
                ]);
            }
        } else {
            log_message('error', 'Authorize.net returned null response.');
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No response returned from Authorize.net.'
            ]);
        }
    }    
}
