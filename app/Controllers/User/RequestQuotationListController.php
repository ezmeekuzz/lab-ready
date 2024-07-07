<?php

namespace App\Controllers\User;

use App\Controllers\User\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\RequestQuotationModel;
use App\Models\QuotationItemsModel;
use App\Models\AssemblyPrintFilesModel;

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
        $QuotationItemsModel = new QuotationItemsModel();
        $AssemblyPrintFilesModel = new AssemblyPrintFilesModel();
    
        // Find the quotation by ID
        $requestQuotation = $RequestQuotationModel->find($id);
        $quotationItems = $QuotationItemsModel->where('request_quotation_id', $id)->findAll();
        $assemblyFiles = $AssemblyPrintFilesModel->where('request_quotation_id', $id)->findAll();

        if($assemblyFiles) {
            foreach ($assemblyFiles as $assemblyFile) {
                if (isset($assemblyFile['assembly_file_location'])) {
                    // Get the filename of the PDF associated with the quotation
                    $assembly = $assemblyFile['assembly_file_location'];

                    // Delete the PDF file from the server
                    $filePathAssembly = FCPATH . $assembly;
                    if (file_exists($filePathAssembly)) {
                        unlink($filePathAssembly);
                    }
                }
            }
            $AssemblyPrintFilesModel->where('request_quotation_id', $id)->delete();
        }
    
        if ($requestQuotation) {
            if (!empty($quotationItems)) {
                foreach ($quotationItems as $quotationItem) {
                    if (isset($quotationItem['file_location'])) {
                        // Get the filename of the PDF associated with the quotation
                        $requestFile = $quotationItem['file_location'];
    
                        // Delete the PDF file from the server
                        $filePath = FCPATH . $requestFile;
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                    if (isset($quotationItem['stl_location'])) {
                        // Get the filename of the PDF associated with the quotation
                        $requestFileSTL = $quotationItem['stl_location'];
    
                        // Delete the PDF file from the server
                        $filePathSTL = FCPATH . $requestFileSTL;
                        if (file_exists($filePathSTL)) {
                            unlink($filePathSTL);
                        }
                    }
                    if (isset($quotationItem['print_location'])) {
                        // Get the filename of the PDF associated with the quotation
                        $requestFilePRINT = $quotationItem['print_location'];
    
                        // Delete the PDF file from the server
                        $filePathPRINT = FCPATH . $requestFilePRINT;
                        if (file_exists($filePathPRINT)) {
                            unlink($filePathPRINT);
                        }
                    }
                    if (isset($quotationItem['assembly_file_location'])) {
                        // Get the filename of the PDF associated with the quotation
                        $requestFileASSEMBLY = $quotationItem['assembly_file_location'];
    
                        // Delete the PDF file from the server
                        $filePathASSEMBLY = FCPATH . $requestFileASSEMBLY;
                        if (file_exists($filePathASSEMBLY)) {
                            unlink($filePathASSEMBLY);
                        }
                    }
                }
    
                // Delete the records from the database
                $QuotationItemsModel->where('request_quotation_id', $id)->delete();
            }
    
            $deleted = $RequestQuotationModel->delete($id);
    
            if ($deleted) {
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the request data quotation from the database']);
            }
        }
    
        return $this->response->setJSON(['status' => 'error', 'message' => 'Request quotation not found']);
    }
    
}

