<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\QuotationsModel;
use App\Models\RequestQuotationModel;
use App\Models\UserQuotationsModel;
use App\Models\QuotationItemsModel;
use App\Models\UsersModel;
use App\Models\AssemblyPrintFilesModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RequestQuotationListController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Request Quotation List | Lab Ready',
            'currentpage' => 'requestquotationlist'
        ];
        return view('admin/requestquotationlist', $data);
    }
    public function getData()
    {
        return datatables('request_quotations')
            ->select('request_quotations.*, users.*, request_quotations.user_id as uid')
            ->join('users', 'request_quotations.user_id = users.user_id', 'LEFT JOIN')
            ->where('request_quotations.status !=', 'Ongoing')
            ->make();
    }
    public function updateStatus($id)
    {
        $requestQuotationModel = new RequestQuotationModel();
        $update = $requestQuotationModel->update(
            $id,
            [
                'status' => 'Done'
            ]
        );
        if($update) {
            return $this->response->setJSON(['status' => 'success']);
        }
        else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update the quotation from the database']);
        }
    }
    public function insert()
    {
        $quotationsModel = new QuotationsModel();
        $userQuotationsModel = new UserQuotationsModel();
        $requestQuotationModel = new RequestQuotationModel();
        $productName = $this->request->getPost('productname');
        $productPrice = $this->request->getPost('productprice');
        $productPrice = $this->request->getPost('productprice');
        $invoiceFile = $this->request->getFile('invoicefile');
        $requestQuotationId = $this->request->getPost('requestQuotationId');
    
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
            'request_quotation_id' => $requestQuotationId,
            'productname' => $productName,
            'productprice' => $productPrice,
            'invoicefile' => '/uploads/PDFs/' . $newFileName,
            'quotationdate' => date('Y-m-d'),
            'status' => 'Unpaid'
        ];
    
        // Insert data into database
        $inserted = $quotationsModel->insert($data);
        $UsersModel = new UsersModel();
        $userDetails = $UsersModel->find($this->request->getPost('userId'));
        $requestQuotationDetails = $requestQuotationModel->where('request_quotation_id', $requestQuotationId)->first();
        if ($inserted) {
            $userQuotationsModel->insert([
                'user_id' => $this->request->getPost('userId'),
                'quotation_id' => $inserted,
                'dateforwarded' => date('Y-m-d'),
                'readstatus' => 'Unread'
            ]);
            $requestQuotationModel->update(
                $this->request->getPost('requestQuotationId'),
                [
                    'status' => 'Done'
                ]
            );
            $data = [
                'userDetails' => $userDetails,
                'requestQuotationDetails' => $requestQuotationDetails
            ];
            $message = view('emails/quotation-response', $data);
            // Email sending code
            $email = \Config\Services::email();
            $email->setTo($userDetails['email']);
            $email->setSubject('You\'ve got a response from your quotation!');
            $email->setMessage($message);
            if ($email->send()) {
                $response = [
                    'success' => true,
                    'message' => 'Quotation forwarded successfully!',
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
                'message' => 'Failed to forward quotation.',
            ];
        }
    
        return $this->response->setJSON($response);
    }
    public function getQuotationList($id)
    {
        $request = service('request');
    
        // Check if the request is AJAX
        if ($request->isAJAX()) {
            // Assuming you have a model called QuotationListModel
            $quotationItemsModel = new QuotationItemsModel();
    
            // Fetch the quotation list data
            $data = $quotationItemsModel->where('request_quotation_id', $id)->findAll(); // Adjust this according to your actual query or method in the model
    
            // Check if data is fetched successfully
            if ($data !== null) {
                // Prepare the response
                $response = [
                    'status' => 'success',
                    'data' => $data,
                ];
                return $this->response->setJSON($response);
            } else {
                // Return error message if data retrieval fails
                return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON(['error' => 'No data found']);
            }
        } else {
            // Return error for non-AJAX requests
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)->setJSON(['error' => 'Invalid request type']);
        }
    }
    public function downloadExcelFile($id)
    {
        // Load your models and get the data
        $quotationItemsModel = new QuotationItemsModel();
        $requestQuotationModel = new RequestQuotationModel();
        $assemblyPrintFilesModel = new AssemblyPrintFilesModel();
    
        $requestQuotation = $requestQuotationModel->find($id);
        $quotationItems = $quotationItemsModel
            ->join('request_quotations', 'request_quotations.request_quotation_id=quotation_items.request_quotation_id', 'left')
            ->join('users', 'request_quotations.user_id=users.user_id', 'left')
            ->where('quotation_items.request_quotation_id', $id)
            ->findAll();
        $assemblyFiles = $assemblyPrintFilesModel->where('request_quotation_id', $requestQuotation['request_quotation_id'])->findAll();
    
        // Initialize PHPExcel library and spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Set the header row
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Customer');
        $sheet->setCellValue('C1', 'Part Number');
        $sheet->setCellValue('D1', 'Quantity');
        $sheet->setCellValue('E1', 'Quote Type');
        $sheet->setCellValue('F1', 'Material');
        $sheet->setCellValue('G1', 'File Name');
        $sheet->setCellValue('H1', 'File Type');
        $sheet->setCellValue('I1', 'File Location');
        $sheet->setCellValue('J1', 'STL Location');
        $sheet->setCellValue('K1', 'Print Location');
        $sheet->setCellValue('L1', 'Request Quotation ID');
    
        // Populate data rows
        $row = 2;
        foreach ($quotationItems as $quotation) {
            $sheet->setCellValue('A' . $row, $quotation['quotation_item_id']);
            $sheet->setCellValue('B' . $row, $quotation['fullname']);
            $sheet->setCellValue('C' . $row, $quotation['partnumber']);
            $sheet->setCellValue('D' . $row, $quotation['quantity']);
            $sheet->setCellValue('E' . $row, $quotation['quotetype']);
            $sheet->setCellValue('F' . $row, $quotation['material']);
            $sheet->setCellValue('G' . $row, $quotation['filename']);
            $sheet->setCellValue('H' . $row, $quotation['filetype']);
            $sheet->setCellValue('I' . $row, $quotation['file_location']);
            $sheet->setCellValue('J' . $row, $quotation['stl_location']);
            $sheet->setCellValue('K' . $row, $quotation['print_location']);
            $sheet->setCellValue('L' . $row, $quotation['request_quotation_id']);
            $row++;
        }
    
        // Save the spreadsheet to a temporary file
        $tempExcelFile = tempnam(sys_get_temp_dir(), 'excel') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempExcelFile);
    
        // Create a new ZipArchive instance
        $zip = new \ZipArchive();
        $zipFileName = 'quotation_files_' . $requestQuotation['reference'] . '.zip';
    
        // Open the zip file in memory
        if ($zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            // Add the Excel file to the zip
            $zip->addFile($tempExcelFile, 'request_quotations_' . date('Ymd_His') . '.xlsx');
    
            // Add assembly print files to the zip under the 'assembly-files' folder
            foreach ($assemblyFiles as $file) {
                if (!empty($file['assembly_print_file_location']) && file_exists(FCPATH . $file['assembly_print_file_location'])) {
                    $zip->addFile(FCPATH . $file['assembly_print_file_location'], 'assembly-files/' . basename($file['assembly_print_file_location']));
                }
            }
    
            // Add quotation items files to the zip under the corresponding folders
            foreach ($quotationItems as $item) {
                if (!empty($item['file_location']) && file_exists(FCPATH . $item['file_location'])) {
                    $zip->addFile(FCPATH . $item['file_location'], 'quotation-files/' . basename($item['file_location']));
                }
                if (!empty($item['stl_location']) && file_exists(FCPATH . $item['stl_location'])) {
                    $zip->addFile(FCPATH . $item['stl_location'], 'stl-files/' . basename($item['stl_location']));
                }
                if (!empty($item['print_location']) && file_exists(FCPATH . $item['print_location'])) {
                    $zip->addFile(FCPATH . $item['print_location'], 'print-files/' . basename($item['print_location']));
                }
            }
    
            // Close the zip file
            $zip->close();
    
            // Send the zip file to the browser for download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
            header('Content-Length: ' . filesize($zipFileName));
    
            // Read the zip file from memory and send it to the browser
            readfile($zipFileName);
    
            // Delete the zip file and temporary Excel file after download
            unlink($zipFileName);
            unlink($tempExcelFile);
    
            exit;
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to create zip file.']);
        }
    }
    
}
