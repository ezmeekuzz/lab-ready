<?php

namespace App\Controllers\User;

use App\Controllers\User\SessionController;
use CodeIgniter\Files\File;
use App\Models\RequestQuotationModel;
use App\Models\QuotationItemsModel;
use App\Models\AssemblyPrintFilesModel;
use App\Models\MaterialsModel;
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
                'reference' => $this->generateReference(),
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
                if (in_array($extension, ['STEP', 'IGS', 'STL'])) {
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
                    $insertedIds[] = $quotationItemsModel->insertID();
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
                    $insertedIds[] = $quotationItemsModel->insertID();
                }
            } else {
                log_message('error', 'File upload error: ' . $file->getErrorString());
            }
        }
    
        // Fetch all inserted records using their IDs
        if (!empty($insertedIds)) {
            $insertedData = $quotationItemsModel->whereIn('quotation_item_id', $insertedIds)->findAll();
            $response['files'] = $insertedData;
        }
        return $this->response->setJSON($response);
    }
    
    private function generateReference()
    {
        $user_id = session()->get('user_user_id');
        $requestQuotationModel = new RequestQuotationModel();
    
        // Get today's date in YYYYMMDD format
        $todayDate = date('Ymd');
    
        // Count existing requests for the user on the current date
        $count = $requestQuotationModel->like('reference', $todayDate, 'after')->where('user_id', $user_id)->countAllResults() + 1;
    
        // Generate the reference in YYYYMMDD-NNN format
        $reference = $todayDate . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        return $reference;
    }

    private function convertToSTL($filePath)
    {
        $outputPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'quotation-files';
        $outputFile = $outputPath . DIRECTORY_SEPARATOR . bin2hex(random_bytes(8)) . '.stl';
        $freecadCmd = 'C:\\Program Files\\FreeCAD 0.21\\bin\\FreeCADCmd.exe'; 
    
        // Ensure the FreeCADCmd.exe is available
        if (!file_exists($freecadCmd)) {
            throw new \RuntimeException("FreeCADCmd.exe not found at $freecadCmd");
        }
    
        // Escaping file paths for Windows command line
        $escapedFilePath = str_replace('\\', '\\\\', $filePath);
        $escapedOutputFile = str_replace('\\', '\\\\', $outputFile);
    
        $command = "\"$freecadCmd\" -c \"import FreeCAD as App; import Part, MeshPart; doc = App.newDocument(); obj = doc.addObject('Part::Feature'); obj.Shape = Part.read('$escapedFilePath'); doc.recompute(); mesh_obj = MeshPart.meshFromShape(Shape=obj.Shape, LinearDeflection=0.1, AngularDeflection=0.5); mesh_obj.write('$escapedOutputFile');\"";
    
        // Log the command
        $logger = \Config\Services::logger();
        $logger->info('Executing FreeCAD command: ' . $command);
    
        // Execute the command
        $output = shell_exec($command . ' 2>&1');
    
        // Log the output of the command
        $logger->info('FreeCAD command output: ' . $output);
    
        // Check if the output file was created
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
    public function getMaterials()
    {
        // Load the necessary model
        $materialModel = new MaterialsModel();

        // Get the quoteType from the request
        $quoteType = $this->request->getVar('quoteType');
        
        $materials = $materialModel->where('quotetype', $quoteType)->findAll();

        // Return the materials as a JSON response
        return $this->response->setJSON($materials);
    }
    public function submitQuotations()
    {
        $quotationItemsModel = new QuotationItemsModel();
        $requestQuotationModel = new RequestQuotationModel();
        $assemblyPrintFilesModel = new AssemblyPrintFilesModel(); // Assuming you have a model for the assembly_print_files table
        $requestQuotation = $requestQuotationModel
            ->where('user_id', session()->get('user_user_id'))
            ->where('status', 'Ongoing')
            ->first(); // Changed find() to first() to get a single record
    
        $requestQuotationId = $requestQuotation['request_quotation_id']; // Assuming the primary key is 'id'
        $request = service('request');
    
        // Check if the request is AJAX
        if ($request->isAJAX()) {
            $forms = $request->getPost('forms');
            $files = $request->getFiles();
    
            log_message('info', 'Received forms: ' . print_r($forms, true));
            log_message('info', 'Received files: ' . print_r($files, true));
    
            if (is_array($forms)) {
                // Ensure the target directories exist
                $uploadPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'print-files' . DIRECTORY_SEPARATOR;
                $uploadPath2 = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'assembly-files' . DIRECTORY_SEPARATOR;
    
                if (!is_dir($uploadPath)) {
                    if (!mkdir($uploadPath, 0777, true) && !is_dir($uploadPath)) {
                        log_message('error', 'Failed to create directory for print uploads');
                        return $this->fail('Failed to create directory for print uploads', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
    
                if (!is_dir($uploadPath2)) {
                    if (!mkdir($uploadPath2, 0777, true) && !is_dir($uploadPath2)) {
                        log_message('error', 'Failed to create directory for assembly uploads');
                        return $this->fail('Failed to create directory for assembly uploads', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
                    }
                }
    
                // Handle multiple assembly file uploads
                $assemblyFiles = $files['assemblyFile'] ?? [];
                log_message('info', 'Assembly Files: ' . print_r($assemblyFiles, true));
                $assemblyFilePaths = [];
    
                if (is_array($assemblyFiles)) {
                    foreach ($assemblyFiles as $assemblyFile) {
                        if ($assemblyFile->isValid() && !$assemblyFile->hasMoved()) {
                            $newFileName2 = $assemblyFile->getRandomName();
                            $originalName = $assemblyFile->getClientName();
                            log_message('info', 'Moving assembly file to: ' . $uploadPath2 . $newFileName2);
                            if (!$assemblyFile->move($uploadPath2, $newFileName2)) {
                                log_message('error', 'Failed to upload assembly file: ' . $assemblyFile->getErrorString());
                                return $this->fail('Failed to upload assembly file: ' . $assemblyFile->getErrorString(), ResponseInterface::HTTP_BAD_REQUEST);
                            }
                            $assemblyFilePaths[] = [
                                'path' => 'uploads/assembly-files/' . $newFileName2,
                                'original_name' => $originalName,
                            ];
                        }
                    }
                }
    
                // Save each assembly file path into the assembly_print_files table
                foreach ($assemblyFilePaths as $fileData) {
                    $assemblyPrintFilesModel->insert([
                        'request_quotation_id' => $requestQuotationId,
                        'assembly_print_file_location' => $fileData['path'],
                        'filename' => $fileData['original_name'],
                    ]);
                }
    
                foreach ($forms as $index => $form) {
                    $partNumber = $form['partNumber'] ?? null;
                    $quotetype = $form['quotetype'] ?? null;
                    $material = $form['material'] ?? null;
                    $quantity = $form['quantity'] ?? null;
                    $quotationItemId = $form['quotation_item_id'] ?? null;
                    $printFile = $files['forms'][$index]['printFile'] ?? null;
    
                    log_message('info', 'Processing form index: ' . $index);
                    log_message('info', 'Part Number: ' . $partNumber);
                    log_message('info', 'Quotation Type: ' . $quotetype);
                    log_message('info', 'Material: ' . $material);
                    log_message('info', 'Quantity: ' . $quantity);
                    log_message('info', 'Quotation Item ID: ' . $quotationItemId);
                    log_message('info', 'Print File: ' . print_r($printFile, true));
    
                    // Handle print file upload if a file is provided
                    $printFilePath = null;
                    $originalFileName  = null;
                    if ($printFile && $printFile->isValid() && !$printFile->hasMoved()) {
                        $newFileName = $printFile->getRandomName();
                        $originalFileName = $printFile->getClientName();
                        log_message('info', 'Moving print file to: ' . $uploadPath . $newFileName);
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
                        'material_id' => $material,
                        'print_location' => $printFilePath,
                        'print_location_original_name' => $originalFileName,
                    ]);
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
                    // Update request quotation status
                    $requestQuotationModel
                        ->where('user_id', session()->get('user_user_id'))
                        ->where('status', 'Ongoing')
                        ->set([
                            'status' => 'Pending',
                            'datesubmitted' => date('Y-m-d')
                        ])
                        ->update();
        
                    $response = [
                        'success' => 'Quotations submitted successfully'
                    ];
                    log_message('info', 'Thank you email sent to user: ' . $userEmail);
                } else {
                    log_message('error', 'Failed to send thank you email to user: ' . $userEmail);
                }

                // Send email to additional recipient
                $email->setTo('rustomcodilan@gmail.com');
                $email->setSubject('You received a new quotation!');
                $email->setMessage($thankYouMessage);
                $email->setMailType('html');  // Ensure the email is sent as HTML
                $email->send();
    
                return $this->respond($response, ResponseInterface::HTTP_OK);
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
