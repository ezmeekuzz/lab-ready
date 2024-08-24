<?php

namespace App\Controllers\User;

use App\Controllers\User\SessionController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\RequestQuotationModel;
use App\Models\QuotationItemsModel;
use App\Models\AssemblyPrintFilesModel;
use App\Models\UserQuotationsModel;
use App\Models\QuotationsModel;
use App\Models\ShipmentsModel;
use ZipArchive;

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
        ->where('user_id', session()->get('user_user_id'))
        ->make();
    }

    public function delete($id)
    {
        $RequestQuotationModel = new RequestQuotationModel();
        $QuotationItemsModel = new QuotationItemsModel();
        $AssemblyPrintFilesModel = new AssemblyPrintFilesModel();
        $userQuotationsModel = new UserQuotationsModel();
        $quotationsModel = new QuotationsModel();
    
        // Find and delete related quotations
        $quotations = $quotationsModel->where('request_quotation_id', $id)->findAll();
        if ($quotations) {
            foreach ($quotations as $quotation) {
                if (isset($quotation['invoicefile'])) {
                    $invoice = $quotation['invoicefile'];
                    $filePathInvoice = FCPATH . $invoice;
                    if (file_exists($filePathInvoice)) {
                        unlink($filePathInvoice);
                    }
                }
                $userQuotationsModel->where('quotation_id', $quotation['quotation_id'])->delete();
            }
            $quotationsModel->where('request_quotation_id', $id)->delete();
        }
    
        // Find and delete related assembly files
        $assemblyFiles = $AssemblyPrintFilesModel->where('request_quotation_id', $id)->findAll();
        if ($assemblyFiles) {
            foreach ($assemblyFiles as $assemblyFile) {
                if (isset($assemblyFile['assembly_print_file_location'])) {
                    $assembly = $assemblyFile['assembly_print_file_location'];
                    $filePathAssembly = FCPATH . $assembly;
                    if (file_exists($filePathAssembly)) {
                        unlink($filePathAssembly);
                    }
                }
            }
            $AssemblyPrintFilesModel->where('request_quotation_id', $id)->delete();
        }
    
        // Find and delete related quotation items
        $quotationItems = $QuotationItemsModel->where('request_quotation_id', $id)->findAll();
        if ($quotationItems) {
            foreach ($quotationItems as $quotationItem) {
                // Remove all related files similarly as above
                $this->deleteFilesForQuotationItem($quotationItem);
            }
            $QuotationItemsModel->where('request_quotation_id', $id)->delete();
        }
    
        // Finally, delete the request quotation itself
        $deleted = $RequestQuotationModel->delete($id);
        if ($deleted) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the request data quotation from the database']);
        }
    
        return $this->response->setJSON(['status' => 'error', 'message' => 'Request quotation not found']);
    }
    
    // Helper method to delete files associated with a quotation item
    private function deleteFilesForQuotationItem($quotationItem)
    {
        if (isset($quotationItem['file_location'])) {
            $filePath = FCPATH . $quotationItem['file_location'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        if (isset($quotationItem['stl_location'])) {
            $filePathSTL = FCPATH . $quotationItem['stl_location'];
            if (file_exists($filePathSTL)) {
                unlink($filePathSTL);
            }
        }
        if (isset($quotationItem['print_location'])) {
            $filePathPRINT = FCPATH . $quotationItem['print_location'];
            if (file_exists($filePathPRINT)) {
                unlink($filePathPRINT);
            }
        }
        if (isset($quotationItem['assembly_file_location'])) {
            $filePathASSEMBLY = FCPATH . $quotationItem['assembly_file_location'];
            if (file_exists($filePathASSEMBLY)) {
                unlink($filePathASSEMBLY);
            }
        }
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
            $assemblyPrintFilesModel = new AssemblyPrintFilesModel();
    
            // Fetch the quotation list data
            $quotationItems  = $quotationItemsModel
            ->join('materials', 'materials.material_id=quotation_items.material_id', 'left')
            ->join('request_quotations', 'quotation_items.request_quotation_id=request_quotations.request_quotation_id', 'left')
            ->where('quotation_items.request_quotation_id', $id)
            ->findAll(); // Adjust this according to your actual query or method in the model

            $assemblyPrintFiles = $assemblyPrintFilesModel
            ->where('request_quotation_id', $id)
            ->findAll();
            // Check if data is fetched successfully
            if ($quotationItems !== null) {
                // Prepare the response
                $response = [
                    'status' => 'success',
                    'data' => $quotationItems,
                    'assemblyPrintFiles' => $assemblyPrintFiles,
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
    
        $insertedIds = [];
    
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
                    $insertedIds[] = $quotationItemsModel->insertID();
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

        $command = "\"$freecadCmd\" -c \"import FreeCAD as App; import Part, MeshPart; doc = App.newDocument(); obj = doc.addObject('Part::Feature'); obj.Shape = Part.read('$escapedFilePath'); doc.recompute(); mesh_obj = MeshPart.meshFromShape(Shape=obj.Shape, LinearDeflection=0.1, AngularDeflection=0.5); mesh_obj.write('$escapedOutputFile');\"";

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
        $assemblyFiles = $this->request->getFiles('assemblyFile');
    
        // Check if data is received
        if (empty($printFiles) && empty($assemblyFiles)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No files uploaded.']);
        }
    
        $requestQuotation = $requestQuotationModel->find($requestQuotationId);
        $assemblyFileLists = $assemblyPrintFilesModel->findAll($requestQuotationId);
    
        $assemblyFilePaths = [];
        $uploadPath2 = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'assembly-files' . DIRECTORY_SEPARATOR;
    
        // Only delete existing assembly files if new ones are being uploaded
        if (is_array($assemblyFiles) && count($assemblyFiles) > 0) {
            foreach ($assemblyFiles as $fileArray) {
                foreach ($fileArray as $assemblyFile) {
                    if ($assemblyFile->isValid() && !$assemblyFile->hasMoved()) {
                        // Upload new assembly files
                        $newFileName2 = $assemblyFile->getRandomName();
                        $originalName = $assemblyFile->getClientName();
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
    
            // Save new assembly file paths to the database
            foreach ($assemblyFilePaths as $fileData) {
                $assemblyPrintFilesModel->insert([
                    'request_quotation_id' => $requestQuotationId,
                    'assembly_print_file_location' => $fileData['path'],
                    'filename' => $fileData['original_name'],
                ]);
            }
        }
    
        $responses = [];
        foreach ($quotationItemIds as $index => $quotationItemId) {
            $partNumber = $partNumbers[$index];
            $quoteType = $quoteTypes[$index];
            $material = $materials[$index];
            $quantity = $quantities[$index];
            $printFile = isset($printFiles['printFile'][$index]) ? $printFiles['printFile'][$index] : null;
    
            // Check if the quotation item exists
            $quotationItem = $quotationItemsModel->find($quotationItemId);
            if (!$quotationItem) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Quotation item not found.']);
            }
    
            // Handle file upload if printFile is not empty
            if ($printFile && $printFile->isValid() && !$printFile->hasMoved()) {
                if (!empty($quotationItem['print_location']) && file_exists(FCPATH . $quotationItem['print_location'])) {
                    unlink(FCPATH . $quotationItem['print_location']);
                }
                // Generate a new filename to avoid conflicts
                $newFileName = $printFile->getRandomName();
                $originalFileName = $printFile->getClientName();
                // Move the file to the designated folder
                $printFile->move(FCPATH . 'uploads/print-files', $newFileName);
    
                // Update the data with the new file name
                $data = [
                    'request_quotation_id' => $requestQuotationId,
                    'partnumber' => $partNumber,
                    'quotetype' => $quoteType,
                    'material_id' => $material,
                    'quantity' => $quantity,
                    'print_location' => 'uploads/print-files/' . $newFileName, // Assuming you have a field to store the filename
                    'print_location_original_name' => $originalFileName, // Assuming you have a field to store the filename
                ];
            } else {
                // Update the data without changing the file
                $data = [
                    'request_quotation_id' => $requestQuotationId,
                    'partnumber' => $partNumber,
                    'quotetype' => $quoteType,
                    'material_id' => $material,
                    'quantity' => $quantity,
                ];
            }
            // Update the quotation item
            $quotationItemsModel->update($quotationItemId, $data);
            $requestQuotationModel->update($requestQuotationId, ['status' => 'Pending']);
            $responses[] = [
                'quotation_item_id' => $quotationItemId,
                'partnumber' => $partNumber,
                'quotetype' => $quoteType,
                'material_id' => $material,
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
    
    public function duplicateQuotation($id)
    {
        $quotationItemsModel = new QuotationItemsModel();
        $requestQuotationModel = new RequestQuotationModel();
        $assemblyPrintFilesModel = new AssemblyPrintFilesModel();
    
        $requestQuotation = $requestQuotationModel->find($id);
        $quotationItems = $quotationItemsModel->where('request_quotation_id', $requestQuotation['request_quotation_id'])->findAll();
        $assemblyFiles = $assemblyPrintFilesModel->where('request_quotation_id', $requestQuotation['request_quotation_id'])->findAll();
        $user_id = session()->get('user_user_id');
    
        $data = [
            'reference' => $this->generateReference(),
            'user_id' => $user_id,
            'status' => 'Duplicate',
            'datesubmitted' => date('Y-m-d')
        ];
    
        $inserted = $requestQuotationModel->insert($data);
    
        if($inserted) {
            $newFileData = [];
            if($assemblyFiles) {
                foreach($assemblyFiles as $files) {
                    if (!empty($files['assembly_print_file_location']) && file_exists(FCPATH . $files['assembly_print_file_location'])) {
                        $newFileName = $this->copyFile(FCPATH . $files['assembly_print_file_location'], 'uploads/assembly-files');
                        $newFileData['assembly_print_file_location'] = 'uploads/assembly-files/' . $newFileName;
    
                        // Insert only if the new file path is set
                        $data = [
                            'request_quotation_id' => $inserted,
                            'assembly_print_file_location' => $newFileData['assembly_print_file_location'],
                            'filename' => $files['filename']
                        ];
                        $assemblyPrintFilesModel->insert($data);
                    }
                }
            }
            if($quotationItems) {
                foreach($quotationItems as $item) {
                    if (!empty($item['file_location']) && file_exists(FCPATH . $item['file_location'])) {
                        $newFileName = $this->copyFile(FCPATH . $item['file_location'], 'uploads/quotation-files');
                        $newFileData['file_location'] = 'uploads/quotation-files/' . $newFileName;
                    }
    
                    if (!empty($item['stl_location']) && file_exists(FCPATH . $item['stl_location'])) {
                        $newFileName = $this->copyFile(FCPATH . $item['stl_location'], 'uploads/quotation-files');
                        $newFileData['stl_location'] = 'uploads/quotation-files/' . $newFileName;
                    }
    
                    if (!empty($item['print_location']) && file_exists(FCPATH . $item['print_location'])) {
                        $newFileName = $this->copyFile(FCPATH . $item['print_location'], 'uploads/print-files');
                        $newFileData['print_location'] = 'uploads/print-files/' . $newFileName;
                    }
    
                    $data = [
                        'request_quotation_id' => $inserted,
                        'partnumber' => $item['partnumber'],
                        'quantity' => $item['quantity'],
                        'quotetype' => $item['quotetype'],
                        'material_id' => $item['material_id'],
                        'filename' => $item['filename'],
                        'file_location' => $newFileData['file_location'] ?? null,
                        'stl_location' => $newFileData['stl_location'] ?? null,
                        'print_location' => $newFileData['print_location'] ?? null,
                        'print_location_original_name' => $item['print_location_original_name'] ?? null,
                    ];
    
                    // Insert only if at least one file location is set
                    if ($data['file_location'] || $data['stl_location'] || $data['print_location']) {
                        $quotationItemsModel->insert($data);
                    }
                }
            }
            $response = [
                'success' => true,
                'message' => 'Quotation successfully duplicated!'
            ];
        }
        else {
            $response = [
                'success' => false,
                'message' => 'Quotation was not successfully duplicated!'
            ];
        }
        return $this->response->setJSON($response);
    }    
    private function copyFile($sourcePath, $destinationDir)
    {
        $newFileName = uniqid() . '_' . basename($sourcePath);
        if (!is_dir(FCPATH . $destinationDir)) {
            mkdir(FCPATH . $destinationDir, 0755, true);
        }
        copy($sourcePath, FCPATH . $destinationDir . '/' . $newFileName);
        return $newFileName;
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
    public function downloadAllFiles($id)
    {
        $quotationItemsModel = new QuotationItemsModel();
        $requestQuotationModel = new RequestQuotationModel();
        $assemblyPrintFilesModel = new AssemblyPrintFilesModel();
    
        $requestQuotation = $requestQuotationModel->find($id);
        $quotationItems = $quotationItemsModel
        ->where('request_quotation_id', $requestQuotation['request_quotation_id'])->findAll();
        $assemblyFiles = $assemblyPrintFilesModel->where('request_quotation_id', $requestQuotation['request_quotation_id'])->findAll();
    
        // Create a new ZipArchive instance
        $zip = new \ZipArchive();
        $zipFileName = 'quotation_files_' . $requestQuotation['reference'] . '.zip';
    
        // Open the zip file in memory
        if ($zip->open($zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            // Add assembly print files to the zip under the 'assembly-files' folder
            foreach ($assemblyFiles as $file) {
                if (!empty($file['assembly_print_file_location']) && file_exists(FCPATH . $file['assembly_print_file_location'])) {
                    $zip->addFile(FCPATH . $file['assembly_print_file_location'], 'assembly-files/' . basename($file['filename']));
                }
            }
    
            // Add quotation items files to the zip under the corresponding folders
            foreach ($quotationItems as $item) {
                if (!empty($item['file_location']) && file_exists(FCPATH . $item['file_location'])) {
                    // Use the original filename stored in the 'filename' column from the database
                    $zip->addFile(FCPATH . $item['file_location'], 'quotation-files/' . $item['filename']);
                }
                if (!empty($item['print_location']) && file_exists(FCPATH . $item['print_location'])) {
                    $zip->addFile(FCPATH . $item['print_location'], 'print-files/' . basename($item['print_location_original_name']));
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
    
            // Delete the zip file after download
            unlink($zipFileName);
    
            exit;
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to create zip file.']);
        }
    }    
    public function downloadAssemblyFiles($id)
    {
        $assemblyPrintFilesModel = new AssemblyPrintFilesModel();
        $requestQuotationModel = new RequestQuotationModel();

        $requestQuotation = $requestQuotationModel->find($id);
        $assemblyFiles = $assemblyPrintFilesModel->where('request_quotation_id', $requestQuotation['request_quotation_id'])->findAll();

        // Create a new ZipArchive instance
        $zip = new ZipArchive();
        $zipFileName = 'assembly_files_' . $requestQuotation['reference'] . '.zip';

        // Open the zip file in memory
        if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Add assembly print files to the zip under the 'assembly-files' folder
            foreach ($assemblyFiles as $file) {
                if (!empty($file['assembly_print_file_location']) && file_exists(FCPATH . $file['assembly_print_file_location'])) {
                    $zip->addFile(FCPATH . $file['assembly_print_file_location'], 'assembly-files/' . basename($file['filename']));
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

            // Delete the zip file after download
            unlink($zipFileName);

            exit;
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to create zip file.']);
        }
    }
    public function deleteAssemblyFile()
    {
        $assemblyPrintFilesModel = new AssemblyPrintFilesModel();
        $fileId = $this->request->getPost('id');
    
        // Fetch the file details from the database using the file ID
        $file = $assemblyPrintFilesModel->find($fileId);
    
        if ($file) {
            // Get the file path from the fetched file details
            $filePath = FCPATH . $file['assembly_print_file_location']; // Adjust the path as needed
    
            // Check if the file exists on the server
            if (file_exists($filePath)) {
                // Attempt to delete the file from the server
                if (unlink($filePath)) {
                    // File deleted successfully, now delete the record from the database
                    $deleted = $assemblyPrintFilesModel->delete($fileId);
    
                    if ($deleted) {
                        return $this->response->setJSON(['success' => true]);
                    } else {
                        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete file from database']);
                    }
                } else {
                    // File deletion failed
                    return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete file from server']);
                }
            } else {
                // File does not exist
                return $this->response->setJSON(['success' => false, 'message' => 'File does not exist on server']);
            }
        } else {
            // File not found in the database
            return $this->response->setJSON(['success' => false, 'message' => 'File not found in database']);
        }
    }    
    public function shipmentLink($id)
    {
        $QuotationsModel = new QuotationsModel();
        $shipmentsModel = new ShipmentsModel();

        $quotations = $QuotationsModel->where('request_quotation_id', $id)->first();
        $shipments = $shipmentsModel->where('quotation_id', $quotations['quotation_id'])->first();
        if ($shipments) {
            $shipmentLink = $shipments['shipment_link'];
            return redirect()->to("{$shipmentLink}");
        }
    
        // If no shipment found, you could redirect to an error page or back to the previous page
        return redirect()->to('/requestquotationlist')->with('error', 'Shipment not found.');
    }
}
