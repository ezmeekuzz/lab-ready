<?php

namespace App\Controllers\User;

use App\Controllers\User\SessionController;
use CodeIgniter\RESTful\ResourceController;
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
        return datatables('request_quotations')
        ->where('status !=', 'Ongoing')
        ->make();
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
                if (isset($assemblyFile['assembly_print_file_location'])) {
                    // Get the filename of the PDF associated with the quotation
                    $assembly = $assemblyFile['assembly_print_file_location'];

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

    public function deleteItem($id)
    {
        $QuotationItemsModel = new QuotationItemsModel();
        $RequestQuotationModel = new RequestQuotationModel();
        $AssemblyPrintFilesModel = new AssemblyPrintFilesModel();
    
        // Find the quotation item by ID
        $quotationItem = $QuotationItemsModel->find($id);
    
        if ($quotationItem) {
            $requestQuotationId = $this->request->getPost('requestQuotationId');
            
            // Check if the request quotation has only one item
            $totalItems = $QuotationItemsModel->where('request_quotation_id', $requestQuotationId)->countAllResults();
    
            if (isset($quotationItem['file_location'])) {
                $requestFile = $quotationItem['file_location'];
                $filePath = FCPATH . $requestFile;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            if (isset($quotationItem['stl_location'])) {
                $requestFileSTL = $quotationItem['stl_location'];
                $filePathSTL = FCPATH . $requestFileSTL;
                if (file_exists($filePathSTL)) {
                    unlink($filePathSTL);
                }
            }
            if (isset($quotationItem['print_location'])) {
                $requestFilePRINT = $quotationItem['print_location'];
                $filePathPRINT = FCPATH . $requestFilePRINT;
                if (file_exists($filePathPRINT)) {
                    unlink($filePathPRINT);
                }
            }
    
            $deleted = $QuotationItemsModel->delete($id);
            
            if ($deleted) {
                // If there was only one item, delete the request quotation as well
                if ($totalItems == 1) {
                    $assemblyFiles = $AssemblyPrintFilesModel->where('request_quotation_id', $requestQuotationId)->findAll();
            
                    if($assemblyFiles) {
                        foreach ($assemblyFiles as $assemblyFile) {
                            if (isset($assemblyFile['assembly_print_file_location'])) {
                                // Get the filename of the PDF associated with the quotation
                                $assembly = $assemblyFile['assembly_print_file_location'];
            
                                // Delete the PDF file from the server
                                $filePathAssembly = FCPATH . $assembly;
                                if (file_exists($filePathAssembly)) {
                                    unlink($filePathAssembly);
                                }
                            }
                        }
                        $AssemblyPrintFilesModel->where('request_quotation_id', $id)->delete();
                    }
                    $RequestQuotationModel->delete($requestQuotationId);
                }
                return $this->response->setJSON([
                    'status' => 'success',
                    'Item Count' => $totalItems,
                ]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the quotation item from the database']);
            }
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Quotation item not found']);
    }    

    public function getQuotationList($id)
    {
        $request = service('request');
    
        // Check if the request is AJAX
        if ($request->isAJAX()) {
            // Assuming you have a model called QuotationListModel
            $quotationItemsModel = new QuotationItemsModel();
    
            // Fetch the quotation list data
            $data = $quotationItemsModel
            ->join('request_quotations', 'quotation_items.request_quotation_id=request_quotations.request_quotation_id', 'left')
            ->where('quotation_items.request_quotation_id', $id)
            ->findAll(); // Adjust this according to your actual query or method in the model
    
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
    public function uploadFiles()
    {
        $requestQuotationId = $this->request->getPost('request_quotation_id');
        if (!$requestQuotationId) {
            return $this->response->setJSON(['error' => 'No request_quotation_id provided'])->setStatusCode(400);
        }
    
        $files = $this->request->getFiles();
        $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'quotation-files';
        $quotationItemsModel = new QuotationItemsModel();
        
        $response = [
            'status' => 'success',
            'message' => 'Files uploaded successfully.',
            'files' => [],
            'conversion_errors' => [] // To store conversion errors
        ];
    
        foreach ($files['files'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $originalName = $file->getName();
                $extension = strtoupper(pathinfo($file->getName(), PATHINFO_EXTENSION));
    
                if (in_array($extension, ['STEP', 'IGS', 'STL'])) {
                    $newName = bin2hex(random_bytes(8)) . '.' . $extension;
                    $file->move($uploadPath, $newName);
    
                    try {
                        $stlFilePath = $this->convertToSTL($uploadPath . DIRECTORY_SEPARATOR . $newName);
                    } catch (\Exception $e) {
                        log_message('error', 'Error converting ' . $extension . ' file: ' . $file->getName() . '. Error: ' . $e->getMessage());
                        $response['conversion_errors'][] = 'Error converting ' . $extension . ' file: ' . $file->getName();
                        $stlFilePath = null;
                    }
    
                    $fileData = [
                        'request_quotation_id' => $requestQuotationId,
                        'partnumber' => $originalName,
                        'quantity' => 1,
                        'filename' => $originalName,
                        'filetype' => $extension,
                        'file_location' => 'uploads/quotation-files/' . $newName,
                        'stl_location' => $stlFilePath ? 'uploads/quotation-files/' . basename($stlFilePath) : null,
                    ];
                    $quotationItemsModel->insert($fileData);
                    $response['files'][] = $fileData;
                } else {
                    $file->move($uploadPath, $file->getName());
    
                    $fileData = [
                        'request_quotation_id' => $requestQuotationId,
                        'partnumber' => $originalName,
                        'quantity' => 1,
                        'filename' => $originalName,
                        'filetype' => $extension,
                        'file_location' => 'uploads/quotation-files/' . $file->getName(),
                        'stl_location' => null,
                    ];
                    $quotationItemsModel->insert($fileData);
                    $response['files'][] = $fileData;
                }
            } else {
                log_message('error', 'File upload error: ' . $file->getErrorString());
            }
        }
    
        return $this->response->setJSON($response);
    }    

    private function convertToSTL($filePath)
    {
        $outputPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'quotation-files';
        $outputFile = $outputPath . DIRECTORY_SEPARATOR . bin2hex(random_bytes(8)) . '.stl';
        $freecadCmd = 'C:\\Program Files\\FreeCAD 0.21\\bin\\FreeCADCmd.exe';

        if (!file_exists($freecadCmd)) {
            throw new \RuntimeException("FreeCADCmd.exe not found at $freecadCmd");
        }

        $escapedFilePath = str_replace('\\', '\\\\', $filePath);
        $escapedOutputFile = str_replace('\\', '\\\\', $outputFile);

        $command = "\"$freecadCmd\" -c \"import Part; doc = FreeCAD.newDocument(); obj = doc.addObject('Part::Feature'); obj.Shape = Part.read('$escapedFilePath'); doc.recompute(); Part.export([obj], '$escapedOutputFile');\"";

        $logger = \Config\Services::logger();
        $logger->info('Current PATH: ' . getenv('PATH'));
        $logger->info('Executing FreeCAD command: ' . $command);

        $output = shell_exec($command . ' 2>&1');

        $logger->info('FreeCAD command output: ' . $output);

        if (!file_exists($outputFile)) {
            $logger->error('FreeCAD conversion failed: ' . $output);
            throw new \RuntimeException("Failed to convert the file. Command output: " . $output);
        }

        return $outputFile;
    }
    public function submitQuotations()
    {
        $quotationItemsModel = new QuotationItemsModel();
        $requestQuotationModel = new RequestQuotationModel();
        $assemblyPrintFilesModel = new AssemblyPrintFilesModel();
    
        $requestQuotationId = $this->request->getPost('request_quotation_id');
        $quotationItemIds = $this->request->getPost('quotation_item_id');
        $partNumbers = $this->request->getPost('partnumber');
        $quoteTypes = $this->request->getPost('quotetype');
        $materials = $this->request->getPost('material');
        $quantities = $this->request->getPost('quantity');
        $printFiles = $this->request->getFiles('printFile');
        
        $requestQuotation = $requestQuotationModel->find($requestQuotationId);

        $responses = [];
        foreach ($quotationItemIds as $index => $quotationItemId) {
            $partNumber = $partNumbers[$index];
            $quoteType = $quoteTypes[$index];
            $material = $materials[$index];
            $quantity = $quantities[$index];
            $printFile = isset($printFiles[$index]) ? $printFiles[$index] : null;
    
            // Check if the quotation item exists
            $quotationItem = $quotationItemsModel->find($quotationItemId);
            if (!$quotationItem) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Quotation item not found.']);
            }
    
            // Handle file upload if printFile is not empty
            if ($printFile && $printFile->isValid() && !$printFile->hasMoved()) {
                // Generate a new filename to avoid conflicts
                $newFileName = $printFile->getRandomName();
                // Move the file to the designated folder
                $printFile->move(FCPATH . 'uploads/print-file', $newFileName);
                
                // Update the data with the new file name
                $data = [
                    'request_quotation_id' => $requestQuotationId,
                    'partnumber' => $partNumber,
                    'quotetype' => $quoteType,
                    'material' => $material,
                    'quantity' => $quantity,
                    'print_file' => 'uploads/print-files/'. $newFileName, // Assuming you have a field to store the filename
                ];
            } else {
                // Update the data without changing the file
                $data = [
                    'request_quotation_id' => $requestQuotationId,
                    'partnumber' => $partNumber,
                    'quotetype' => $quoteType,
                    'material' => $material,
                    'quantity' => $quantity,
                ];
            }
            // Update the quotation item
            $quotationItemsModel->update($quotationItemId, $data);
            $responses[] = [
                'quotation_item_id' => $quotationItemId,
                'partnumber' => $partNumber,
                'quotetype' => $quoteType,
                'material' => $material,
                'quantity' => $quantity,
                'printFile' => $printFile ? $printFile->getClientName() : null,
            ];
        }
    
        $data = ['reference' => $requestQuotation['reference']];
        // Send thank you email to the user
        $userEmail = session()->get('user_email');
        $thankYouMessage = view('emails/thank-you', $data);

        $email = \Config\Services::email();
        $email->setTo($userEmail);
        $email->setSubject('Thank you for your quotation request!');
        $email->setMessage($thankYouMessage);
        $email->setMailType('html');  // Ensure the email is sent as HTML
        if ($email->send()) {
            log_message('info', 'Thank you email sent to user: ' . $userEmail);
        } else {
            log_message('error', 'Failed to send thank you email to user: ' . $userEmail);
        }

        // Send email to additional recipient
        $email->setTo('rustomcodilan@gmail.com');
        $email->setSubject('You received a new quotation!');
        $email->setMessage($thankYouMessage);
        $email->setMailType('html');  // Ensure the email is sent as HTML
        if ($email->send()) {
            
        } else {
            
        }
        // Respond with the processed data or a success message
        return $this->response->setJSON(['status' => 'success', 'data' => $responses]);
    } 
}
