<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\QuotationsModel;
use App\Models\RequestQuotationModel;
use App\Models\UserQuotationsModel;
use App\Models\ShipmentsModel;

class QuotationMasterlistController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Quotation Masterlist | Lab Ready',
            'currentpage' => 'quotationmasterlist'
        ];
        return view('admin/quotationmasterlist', $data);
    }

    public function getData()
    {
        return datatables('quotations')
            ->select('quotations.*, request_quotations.*, users.*, request_quotations.user_id as uid, quotations.status as stat')
            ->join('request_quotations', 'request_quotations.request_quotation_id = quotations.request_quotation_id', 'LEFT JOIN')
            ->join('users', 'request_quotations.user_id = users.user_id', 'LEFT JOIN')
            ->make();
    }

    public function delete($id)
    {
        $QuotationsModel = new QuotationsModel();
        $userQuotationsModel = new UserQuotationsModel();
    
        // Find the quotation by ID
        $quotation = $QuotationsModel->find($id);
    
        if ($quotation) {
            // Get the filename of the PDF associated with the quotation
            $pdfFilename = $quotation['invoicefile'];
    
            // Delete the record from the database
            $userQuotationsModel->where('quotation_id', $id)->delete();
            $deleted = $QuotationsModel->delete($id);
    
            if ($deleted) {
                // Delete the PDF file from the server
                $pdfPath = FCPATH . $pdfFilename;
                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
    
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the quotation from the database']);
            }
        }
    
        return $this->response->setJSON(['status' => 'error', 'message' => 'Quotation not found']);
    }

    public function updateStatus($id)
    {
        $quotationsModel = new QuotationsModel();
        $update = $quotationsModel->update(
            $id,
            [
                'status' => 'Paid'
            ]
        );
        if($update) {
            return $this->response->setJSON(['status' => 'success']);
        }
        else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update the quotation from the database']);
        }
    }

    public function updateShipment($id)
    {
        $shipmentsModel = new ShipmentsModel();
        $quotationsModel = new QuotationsModel();
        $requestQuotationsModel = new RequestQuotationModel();

        $data = $this->request->getPost();
        $validation = \Config\Services::validation();

        $validation->setRules([
            'shipment_address' => 'required|string|max_length[255]',
            'shipment_note' => 'required|string',
            'shipment_link' => 'required|valid_url',
            'shipment_date' => 'required|valid_date'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON(['status' => 'error', 'message' => $validation->getErrors()]);
        }

        $shipmentData = [
            'quotation_id' => $id,
            'shipment_address' => $data['shipment_address'],
            'shipment_note' => $data['shipment_note'],
            'shipment_link' => $data['shipment_link'],
            'shipment_date' => $data['shipment_date']
        ];

        $existingShipment = $shipmentsModel->where('quotation_id', $id)->first();
        $quotationDetails = $quotationsModel->find($id);

        if ($existingShipment) {
            $update = $shipmentsModel->update($existingShipment['shipment_id'], $shipmentData);
            $requestQuotationsModel
            ->where('request_quotation_id', $quotationDetails['request_quotation_id'])
            ->set('status', 'Shipped')->update();
        } else {
            $update = $shipmentsModel->insert($shipmentData);
            $requestQuotationsModel
            ->where('request_quotation_id', $quotationDetails['request_quotation_id'])
            ->set('status', 'Shipped')->update();
        }

        if ($update) {
            $email = \Config\Services::email();
            $email->setTo($data['email']);
            $email->setSubject('You\'re order has been shipped!');
            $email->setMessage('<a hre="'.$data['shipment_link'].'">Check it here </a> You\'re order has been shipped');
            if ($email->send()) {
                return $this->response->setJSON(['status' => 'success']);
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to send message!',
                ];
            }
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update the shipment details']);
        }
    }

    public function getShipment($id)
    {
        $shipmentsModel = new ShipmentsModel();
        $shipment = $shipmentsModel->where('quotation_id', $id)->first();
    
        if ($shipment) {
            return $this->response->setJSON(['status' => 'success', 'data' => $shipment]);
        } else {
            return $this->response->setJSON(['status' => 'success', 'data' => null]);
        }
    }
}
