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
    
        $userQuotationList = $userQuotationsModel
            ->join('quotations', 'quotations.quotation_id=user_quotations.quotation_id', 'left')
            ->join('request_quotations', 'request_quotations.request_quotation_id=quotations.request_quotation_id', 'left')
            ->where('user_quotations.user_id', session()->get('user_user_id'));
    
        if ($search) {
            $userQuotationList = $userQuotationList->like('request_quotations.reference', $search);
        }
    
        $userQuotationList = $userQuotationList->findAll();
    
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
    
        if ($updated) {
            $userDetails = $usersModel->find(session()->get('user_user_id'));
            $requestQuotationDetails = $requestQuotationModel->find($quotationDetails['request_quotation_id']);
            $data = [
                'userDetails' => $userDetails,
                'requestQuotationDetails' => $requestQuotationDetails,
            ];
            $message = view('emails/payment-success', $data);
            // Email sending code
            $email = \Config\Services::email();
            $email->setTo($userDetails['email']);
            $email->setCC('rustomcodilan@gmail.com');
            $email->setSubject('We\'ve got you\'re payment!');
            $email->setMessage($message);
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
                    $requestQuotationsModel = new RequestQuotationsModel();
                    $requestQuotationDetails = $requestQuotationsModel->where('request_quotation_id', $id)->find();
                    $updated = $quotationsModel->where('quotation_id', $quotationId)
                        ->set('address', $address)
                        ->set('city', $city)
                        ->set('state', $state)
                        ->set('zipcode', $zipcode)
                        ->set('phonenumber', $phoneNumber)
                        ->set('status', 'Paid')
                        ->update();
                    $data = [
                        'requestQuotationDetails' => $requestQuotationDetails
                    ];
                    $message = view('emails/payment-success', $data);
                    // Email sending code
                    $email = \Config\Services::email();
                    $email->setTo('rustomcodilan@gmail.com');
                    $email->setSubject('We\'ve got you\'re payment!');
                    $email->setMessage($message);
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
}
