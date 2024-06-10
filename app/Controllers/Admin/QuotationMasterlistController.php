<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\QuotationsModel;
use App\Models\UserQuotationsModel;

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
        return datatables('quotations')->make();
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
}
