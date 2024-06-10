<?php

namespace App\Controllers\User;

use App\Controllers\User\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\RequestQuotationModel;

class RequestQuotationListController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Request Quotation List | Lab Ready',
            'currentpage' => 'requestquotationlist'
        ];
        return view('user/requestquotationlist', $data);
    }

    public function getData()
    {
        return datatables('request_quotations')->make();
    }

    public function delete($id)
    {
        $RequestQuotationModel = new RequestQuotationModel();

        // Find the quotation by ID
        $requestQuotation = $RequestQuotationModel->find($id);

        if ($requestQuotation) {
            // Get the filename of the PDF associated with the quotation
            $requestFile = $requestQuotation['file_location'];

            // Delete the record from the database
            $deleted = $RequestQuotationModel->delete($id);

            if ($deleted) {
                // Delete the PDF file from the server
                $filePath = FCPATH . $requestFile;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the request data quotation from the database']);
            }
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Request quotation not found']);
    }
}

