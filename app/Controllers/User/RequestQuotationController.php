<?php

namespace App\Controllers\User;

use App\Controllers\User\SessionController;
use CodeIgniter\Files\File;
use App\Models\RequestQuotationModel;
use App\Models\QuotationItemsModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\API\ResponseTrait;
use Psr\Log\LogLevel;

class RequestQuotationController extends SessionController
{
    use ResponseTrait;

    public function index()
    {
        $data = [
            'title' => 'Request Quotation | Lab Ready',
            'currentpage' => 'requestquotation'
        ];
        return view('user/requestquotation', $data);
    }

    public function uploadFiles()
    {
        $files = $this->request->getFiles();
        $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'quotation-files';
        $quotationItemsModel = new QuotationItemsModel();
        $requestQuotationModel = new RequestQuotationModel();
    
        // Check ongoing data (unchanged)
        $ongoingData = $requestQuotationModel->where('user_id', session()->get('user_user_id'))
            ->where('status', 'Ongoing')
            ->first();
    
        if ($ongoingData) {
            $requestQuotationId = $ongoingData['request_quotation_id'];
        } else {
            // Insert new data
            $requestQuotationModel->insert([
                'user_id' => session()->get('user_user_id'),
                'status' => 'Ongoing'
            ]);
            // Get the inserted ID
            $requestQuotationId = $requestQuotationModel->insertID();
        }
    
        $response = [
            'success' => 'Files uploaded successfully.',
            'files' => [],
            'conversion_errors' => [] // To store conversion errors
        ];
    
        foreach ($files['files'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $originalName = $file->getName();
                $extension = strtoupper(pathinfo($file->getName(), PATHINFO_EXTENSION));
                
                // Check for STEP, IGS, or X_T extension
                if (in_array($extension, ['STEP', 'IGS', 'X_T'])) {
                    $newName = bin2hex(random_bytes(8)) . '.' . $extension;  // Generate random name with the appropriate extension
                    $file->move($uploadPath, $newName);
    
                    // Call FreeCAD-based conversion method
                    try {
                        $stlFilePath = $this->convertToSTL($uploadPath . DIRECTORY_SEPARATOR . $newName);
                    } catch (\Exception $e) {
                        log_message('error', 'Error converting ' . $extension . ' file: ' . $file->getName() . '. Error: ' . $e->getMessage());
                        $response['conversion_errors'][] = 'Error converting ' . $extension . ' file: ' . $file->getName();
                        $stlFilePath = null;
                    }
    
                    $fileData = [
                        'request_quotation_id' => $requestQuotationId,
                        'filename' => $originalName,
                        'filetype' => $extension,
                        'file_location' => 'uploads/quotation-files/' . $newName, // Store original file location
                        'stl_location' => $stlFilePath ? 'uploads/quotation-files/' . basename($stlFilePath) : null, // Store converted STL file location if available
                    ];
                    $quotationItemsModel->insert($fileData);
                    $response['files'][] = $fileData;
                } else {
                    // For non-STEP, non-IGS files, just upload without conversion
                    $file->move($uploadPath, $file->getName());
    
                    $fileData = [
                        'request_quotation_id' => $requestQuotationId,
                        'filename' => $originalName,
                        'filetype' => $extension,
                        'file_location' => 'uploads/quotation-files/' . $file->getName(), // Store original file location
                        'stl_location' => null, // No STL location for non-STEP, non-IGS files
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
        $freecadCmd = 'C:\\Program Files\\FreeCAD 0.21\\bin\\FreeCADCmd.exe'; // Use full path for now
    
        // Ensure the FreeCADCmd.exe is available
        if (!file_exists($freecadCmd)) {
            throw new \RuntimeException("FreeCADCmd.exe not found at $freecadCmd");
        }
    
        // Use double backslashes to avoid Unicode decoding errors
        $escapedFilePath = str_replace('\\', '\\\\', $filePath);
        $escapedOutputFile = str_replace('\\', '\\\\', $outputFile);
    
        $command = "\"$freecadCmd\" -c \"import Part; doc = FreeCAD.newDocument(); obj = doc.addObject('Part::Feature'); obj.Shape = Part.read('$escapedFilePath'); doc.recompute(); Part.export([obj], '$escapedOutputFile');\"";
    
        // Log the command
        $logger = \Config\Services::logger();
        $logger->info('Current PATH: ' . getenv('PATH'));
        $logger->info('Executing FreeCAD command: ' . $command);
    
        $output = shell_exec($command . ' 2>&1');
    
        // Log the command output
        $logger->info('FreeCAD command output: ' . $output);
    
        if (!file_exists($outputFile)) {
            $logger->error('FreeCAD conversion failed: ' . $output);
            throw new \RuntimeException("Failed to convert the file. Command output: " . $output);
        }
    
        return $outputFile;
    }

    public function quotationLists()
    {
        $quotationItemModel = new QuotationItemsModel();

        $quotationList = $quotationItemModel
            ->join('request_quotations', 'request_quotations.request_quotation_id=quotation_items.request_quotation_id', 'left')
            ->where('request_quotations.user_id', session()->get('user_user_id'))
            ->where('request_quotations.status', 'Ongoing')
            ->findAll();

        return $this->response->setJSON($quotationList);
    }

    public function submitQuotations()
    {
        $request = service('request');
    
        // Check if the request is AJAX
        if ($request->isAJAX()) {
            $forms = $this->request->getPost('forms');
            $files = $this->request->getFiles();
    
            log_message('info', 'Received forms: ' . print_r($forms, true));
            log_message('info', 'Received files: ' . print_r($files, true));
    
            if (is_array($forms)) {
                $quotationItemsModel = new QuotationItemsModel();
                $requestQuotationModel = new RequestQuotationModel();
    
                // Ensure the target directory exists
                $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'print-files' . DIRECTORY_SEPARATOR;
                if (!is_dir($uploadPath)) {
                    if (!mkdir($uploadPath, 0777, true) && !is_dir($uploadPath)) {
                        log_message('error', 'Failed to create directory for uploads');
                        return $this->fail('Failed to create directory for uploads', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
    
                foreach ($forms as $index => $form) {
                    $partNumber = $form['partNumber'] ?? null;
                    $quotetype = $form['quotetype'] ?? null;
                    $material = $form['material'] ?? null;
                    $quantity = $form['quantity'] ?? null;
                    $quotationItemId = $form['quotation_item_id'] ?? null;
                    $printFile = $files["forms"][$index]['printFile'] ?? null;
    
                    log_message('info', 'Processing form index: ' . $index);
                    log_message('info', 'Part Number: ' . $partNumber);
                    log_message('info', 'Quotation Type: ' . $quotetype);
                    log_message('info', 'Material: ' . $material);
                    log_message('info', 'Quantity: ' . $quantity);
                    log_message('info', 'Quotation Item ID: ' . $quotationItemId);
                    log_message('info', 'Print File: ' . print_r($printFile, true));
    
                    // Handle file upload if a file is provided
                    $printFilePath = null;
                    if ($printFile && $printFile->isValid() && !$printFile->hasMoved()) {
                        $newFileName = $printFile->getRandomName();
                        log_message('info', 'Moving file to: ' . $uploadPath . $newFileName);
                        if (!$printFile->move($uploadPath, $newFileName)) {
                            log_message('error', 'Failed to upload print file: ' . $printFile->getErrorString());
                            return $this->fail('Failed to upload print file: ' . $printFile->getErrorString(), ResponseInterface::HTTP_BAD_REQUEST);
                        }
                        $printFilePath = 'uploads/print-files/' . $newFileName;
                    }
    
                    // Save or update the quotation item
                    $quotationItemsModel->update($quotationItemId, [
                        'partnumber' => $partNumber,
                        'quantity' => $quantity,
                        'quotetype' => $quotetype,
                        'material' => $material,
                        'print_location' => $printFilePath,
                    ]);
                }
    
                // Update request quotation status
                $requestQuotationModel
                    ->where('user_id', session()->get('user_user_id'))
                    ->where('status', 'Ongoing')
                    ->set([
                        'status' => 'Pending',
                        'datesubmitted' => date('Y-m-d')
                    ])
                    ->update();
    
                return $this->respond(['success' => 'Quotations submitted successfully'], ResponseInterface::HTTP_OK);
            } else {
                log_message('error', 'Invalid data format');
                return $this->fail('Invalid data format', ResponseInterface::HTTP_BAD_REQUEST);
            }
        }
    
        log_message('error', 'Invalid request type');
        return $this->failForbidden('Invalid request type');
    }    

    public function delete($id)
    {
        $QuotationItemsModel = new QuotationItemsModel();
    
        // Find the quotation by ID
        $quotation = $QuotationItemsModel->find($id);
    
        if (!$quotation) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Quotation not found']);
        }
    
        // Get the filename of the PDF associated with the quotation
        $fileLocation = $quotation['file_location'];
        $stlLocation = $quotation['stl_location'];
    
        // Delete the record from the database
        $deleted = $QuotationItemsModel->delete($id);
    
        if (!$deleted) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the quotation from the database']);
        }
    
        // Delete the PDF file from the server
        $filePath = FCPATH . $fileLocation;
        $stlPath = FCPATH . $stlLocation;
    
        if (!file_exists($filePath) || !file_exists($stlPath)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File not found for deletion']);
        }
    
        try {
            if($filePath !== NULL) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            if($stlLocation !== NULL) {
                if (file_exists($stlPath)) {
                    unlink($stlPath);
                }
            }
    
            return $this->response->setJSON(['status' => 'success']);
        } catch (\Exception $e) {
            // Log the error for troubleshooting
            log_message('error', 'Error deleting files: ' . $e->getMessage());
    
            return $this->response->setJSON(['status' => 'error', 'message' => 'An error occurred while deleting files']);
        }
    }
}