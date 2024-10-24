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
        // Get year and month from the POST request
        $year = $this->request->getPost('year');
        $month = $this->request->getPost('month');
    
        // Start the query
        $query = datatables('request_quotations')
            ->select('request_quotations.*, users.*, request_quotations.user_id as uid')
            ->join('users', 'request_quotations.user_id = users.user_id', 'LEFT JOIN')
            ->where('request_quotations.status !=', 'Ongoing')
            ->where('request_quotations.status !=', 'Duplicate')
            ->where('request_quotations.status !=', 'Done');
    
        // Apply year filter if provided
        if ($year) {
            $query = $query->where('YEAR(request_quotations.datesubmitted)', $year); // Assuming 'datesubmitted' is the date field
        }
    
        // Apply month filter if provided
        if ($month) {
            $query = $query->where('MONTH(request_quotations.datesubmitted)', $month);
        }
    
        // Return the filtered data
        return $query->make();
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
        $nickName = $this->request->getPost('nickname');
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
            'nickname' => $nickName,
            'productprice' => $productPrice,
            'invoicefile' => '/uploads/PDFs/' . $newFileName,
            'filename' => $invoiceFile->getClientName(),
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
    public function downloadFiles($id)
    {
        // Load your models and get the data
        $quotationItemsModel = new QuotationItemsModel();
        $requestQuotationModel = new RequestQuotationModel();
        $assemblyPrintFilesModel = new AssemblyPrintFilesModel();
        
        $requestQuotation = $requestQuotationModel
            ->join('users', 'users.user_id=request_quotations.user_id', 'left')
            ->find($id);
        $quotationItems = $quotationItemsModel
            ->join('request_quotations', 'request_quotations.request_quotation_id=quotation_items.request_quotation_id', 'left')
            ->join('users', 'request_quotations.user_id=users.user_id', 'left')
            ->join('materials', 'materials.material_id=quotation_items.material_id', 'left')
            ->where('quotation_items.request_quotation_id', $id)
            ->findAll();
        $assemblyFiles = $assemblyPrintFilesModel->where('request_quotation_id', $requestQuotation['request_quotation_id'])->findAll();
        
        // Initialize PHPExcel library and spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Quotation Item');
        $secondSheet = $this->createSecondSheet($spreadsheet, $id);
        // Define the font style array with lighter text color
        $lighterFontStyleArray = [
            'font' => [
                'name' => 'Crete Round',
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'], // Light gray text color
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Vertical alignment to simulate padding
            ],
        ];
        
        // Define bold style for header row
        $boldHeaderStyleArray = [
            'font' => [
                'bold' => true,
                'name' => 'Crete Round',
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'], // Light gray text color
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Vertical alignment to simulate padding
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '595959'], // Background color
            ],
        ];
        
        // Define alignment styles
        $centerAlignment = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Vertical alignment to simulate padding
            ],
        ];
        $leftAlignment = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Vertical alignment to simulate padding
            ],
        ];
        
        // Apply lighter text color to specific ranges
        $sheet->getStyle('A1:J1')->applyFromArray($lighterFontStyleArray);
        $sheet->getStyle('A2:J2')->applyFromArray($lighterFontStyleArray);
        $sheet->getStyle('A3:J3')->applyFromArray($lighterFontStyleArray);
        $sheet->getStyle('A4:J4')->applyFromArray($lighterFontStyleArray);
        $sheet->getStyle('A5:J5')->applyFromArray($lighterFontStyleArray);
        
        // Apply styles to the header row
        $sheet->getStyle('A6:J6')->applyFromArray($boldHeaderStyleArray);
        
        // Apply center alignment to specific cells
        $sheet->getStyle('A1')->applyFromArray($centerAlignment);
        $sheet->getStyle('B1')->applyFromArray($centerAlignment);
        $sheet->getStyle('A6:J6')->applyFromArray($centerAlignment);
        
        // Apply left alignment to the rest of the cells
        $sheet->getStyle('A2:J5')->applyFromArray($leftAlignment);
        
        // Set the title row
        $sheet->setCellValue('A1', 'Lab Ready');
        $sheet->mergeCells('A1:J1');
        
        // Set the content row
        $sheet->setCellValue('A2', 'Orthopedic Prototypes - On Time and Under Budget');
        $sheet->mergeCells('A2:J2');
        $sheet->getRowDimension(2)->setRowHeight(30); // Adjust row height for padding
        $sheet->getRowDimension(3)->setRowHeight(30); // Adjust row height for padding
        $sheet->getRowDimension(4)->setRowHeight(30); // Adjust row height for padding
        $sheet->getRowDimension(6)->setRowHeight(30); // Adjust row height for padding
        
        // Set quote details
        $sheet->setCellValue('A3', 'Quote:');
        $sheet->setCellValue('B3', $requestQuotation['reference']); // Assuming reference is the quote number
        $sheet->setCellValue('I3', $requestQuotation['fullname']); // Assuming customer name field
        
        $sheet->setCellValue('A4', 'Date:');
        $sheet->setCellValue('B4', date('Y-m-d')); // Actual date
        $sheet->setCellValue('I4', $requestQuotation['email']); // Assuming customer email field
        
        $sheet->setCellValue('I5', $requestQuotation['phonenumber']); // Assuming customer phone field
        
        // Set the header row
        $sheet->setCellValue('A6', 'Item No');
        $sheet->setCellValue('B6', 'Part Number');
        $sheet->setCellValue('C6', 'Material');
        $sheet->setCellValue('D6', 'Material Cert?');
        $sheet->setCellValue('E6', 'Special Surface Treatment');
        $sheet->setCellValue('F6', 'Method');
        $sheet->setCellValue('G6', 'Print Uploaded');
        $sheet->setCellValue('H6', 'Qty');
        $sheet->setCellValue('I6', 'Price');
        $sheet->setCellValue('J6', 'Note');        
        
        $sheet->getStyle('A6:J6')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Apply additional styles if needed
        $titleStyleArray = [
            'font' => [
                'bold' => true,
                'size' => 36,
                'name' => 'Crete Round',
                'color' => ['rgb' => 'FFFFFF'], // Light gray text color
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Vertical alignment to simulate padding
            ],
        ];
        $boldSubtotalStyleArray = [
            'font' => [
                'bold' => true,
                'name' => 'Crete Round',
                'size' => 12,
                'color' => ['rgb' => 'FFFFFF'], // Light gray text color
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Vertical alignment to simulate padding
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '595959'], // Background color
            ],
        ];
        $contentStyleArray = [
            'font' => [
                'size' => 12, // Set font size to 12
                'name' => 'Crete Round',
                'color' => ['rgb' => 'FFFFFF'], // Light gray text color
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Vertical alignment to simulate padding
            ],
        ];
        $backgroundStyleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '595959'], // Black background color
            ],
        ];
        
        // Apply title style
        $sheet->getStyle('A1:J1')->applyFromArray($titleStyleArray);
        $sheet->getStyle('A2:J2')->applyFromArray($contentStyleArray);
        
        // Apply background style
        $sheet->getStyle('A1:J6')->applyFromArray($backgroundStyleArray);
        
        // Set column widths (approximation in units)
        $totalWidthPixels = 1500;
        $numColumns = 10; // Columns A to I
        $approxPixelsPerColumn = $totalWidthPixels / $numColumns;
        
        // Example conversion: 1 unit ≈ 8 pixels
        $approxUnitsPerColumn = $approxPixelsPerColumn / 8;
        
        // Set widths for columns A to I
        for ($column = 'A'; $column <= 'J'; $column++) {
            $sheet->getColumnDimension($column)->setWidth($approxUnitsPerColumn);
        }
        
        $row = 7;
        foreach ($quotationItems as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1); // Assuming item number is stored in 'item_no'
            $sheet->setCellValue('B' . $row, $item['partnumber']); // Assuming part number is stored in 'part_number'
            $sheet->setCellValue('C' . $row, $item['materialname']); // Assuming material is stored in 'material'
            $sheet->setCellValue('D' . $row, ($item['is_material_item_required'] == 'true') ? 'Y' : 'N'); // Assuming material certification is stored in 'is_material_item_required'
            $sheet->setCellValue('E' . $row, '(anodizing, etc)'); // Assuming special surface treatment is stored in 'special_surface_treatment'
            $sheet->setCellValue('F' . $row, $item['quotetype']); // Assuming method is stored in 'method'
            $sheet->setCellValue('G' . $row, ($item['print_location']) ? 'Yes' : 'No'); // Assuming print uploaded is stored in 'print_uploaded'
            $sheet->setCellValue('H' . $row, $item['quantity']); // Assuming quantity is stored in 'quantity'
            $sheet->setCellValue('I' . $row, '0.00'); // Assuming price is stored in 'price'
            $sheet->setCellValue('J' . $row, ($item['other_information']) ? $item['other_information'] : 'Use this for the special notes'); // Assuming note is stored in 'note'
        
            // Format the price column as currency
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('$#,##0.00'); // Format as USD currency
        
            // Center alignment and adjust padding
            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($centerAlignment);
            $sheet->getStyle('A' . $row . ':J' . $row)->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(30); // Adjust row height for padding
            $sheet->getStyle('A'.$row.':J'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $row++;
        }
        $subtotalRow = $row; // This will be the next row after the loop
        $sheet->mergeCells('A' . $subtotalRow . ':H' . $subtotalRow);
        $sheet->setCellValue('A' . $subtotalRow, 'Subtotal (components):');
        $sheet->getStyle('A'.$subtotalRow.':J'.$subtotalRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // Apply styles for the subtotal row
        $sheet->getStyle('A' . $subtotalRow . ':H' . $subtotalRow)->applyFromArray($centerAlignment);
        $sheet->getStyle('A' . $subtotalRow . ':H' . $subtotalRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // Align right
        $sheet->getRowDimension($subtotalRow)->setRowHeight(30); // Adjust row height for padding
        $sheet->getStyle('A' . $subtotalRow . ':H' . $subtotalRow)->applyFromArray($backgroundStyleArray);
        $sheet->getStyle('A' . $subtotalRow . ':H' . $subtotalRow)->applyFromArray($contentStyleArray);
        $sheet->getStyle('A' . $subtotalRow . ':H' . $subtotalRow)->applyFromArray($boldSubtotalStyleArray);
        
        // Set the content for columns H and I in the subtotal row
        $sheet->setCellValue('I' . $subtotalRow, '=SUM(I7:I' . ($row - 1) . ')'); // Calculate subtotal (sum of H column cells above)
        $sheet->setCellValue('J' . $subtotalRow, 'Use this for the special notes');
        
        // Apply styles for columns H and I
        $sheet->getStyle('I' . $subtotalRow)->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle('I' . $subtotalRow . ':J' . $subtotalRow)->applyFromArray($centerAlignment);

        $assemblyRow = $row+1;

        $sheet->setCellValue('A' . $assemblyRow, '1');
        $sheet->mergeCells('B' . $assemblyRow . ':G' . $assemblyRow);
        $sheet->setCellValue('I' . $assemblyRow, '(Leave Blank)');
        $sheet->setCellValue('B' . $assemblyRow, 'Fit, finish and assembly - shop hours');

        $sheet->getStyle('A' . $assemblyRow . ':H' . $assemblyRow)->applyFromArray($centerAlignment);
        $sheet->getRowDimension($assemblyRow)->setRowHeight(30); // Adjust row height for padding
        $sheet->setCellValue('I' . $assemblyRow, '0.00'); // Calculate subtotal (sum of H column cells above)
        $sheet->setCellValue('J' . $assemblyRow, 'Use this for the special notes');
        $sheet->getStyle('A'.$assemblyRow.':J'.$assemblyRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Apply styles for columns H and I
        $sheet->getStyle('I' . $assemblyRow)->getNumberFormat()->setFormatCode('$#,##0.00'); // Format as USD currency
        $sheet->getStyle('I' . $assemblyRow . ':J' . $assemblyRow)->applyFromArray($centerAlignment);

        $shippingRow = $assemblyRow+1;

        $sheet->setCellValue('A' . $shippingRow, 'Shipping');
        $sheet->mergeCells('A' . $shippingRow . ':H' . $shippingRow);

        $sheet->getStyle('A' . $shippingRow . ':H' . $shippingRow)->applyFromArray($centerAlignment);
        $sheet->getStyle('A' . $shippingRow . ':H' . $shippingRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // Align right
        $sheet->getRowDimension($shippingRow)->setRowHeight(30); // Adjust row height for padding
        $sheet->getStyle('A' . $shippingRow . ':H' . $shippingRow)->applyFromArray($backgroundStyleArray);
        $sheet->getStyle('A' . $shippingRow . ':H' . $shippingRow)->applyFromArray($contentStyleArray);
        $sheet->getStyle('A' . $shippingRow . ':H' . $shippingRow)->applyFromArray($boldSubtotalStyleArray);
        
        $sheet->setCellValue('I' . $shippingRow, '0.00'); // Calculate subtotal (sum of H column cells above)
        $sheet->setCellValue('J' . $shippingRow, 'Use this for the special notes');

        // Apply styles for columns H and I
        $sheet->getStyle('I' . $shippingRow)->getNumberFormat()->setFormatCode('$#,##0.00'); // Format as USD currency
        $sheet->getStyle('I' . $shippingRow . ':J' . $shippingRow)->applyFromArray($centerAlignment);
        $sheet->getStyle('A'.$shippingRow.':J'.$shippingRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $totalRow = $shippingRow+1;

        $sheet->setCellValue('A' . $totalRow, 'Total :');
        $sheet->mergeCells('A' . $totalRow . ':H' . $totalRow);

        $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray($centerAlignment);
        $sheet->getRowDimension($totalRow)->setRowHeight(30); // Adjust row height for padding
        $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray($backgroundStyleArray);
        $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray($contentStyleArray);
        $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray($boldSubtotalStyleArray);
        
        $sheet->setCellValue('I' . $totalRow, '=SUM(I'. $subtotalRow .':I' . $shippingRow . ')'); // Calculate subtotal (sum of H column cells above)
        $sheet->setCellValue('J' . $totalRow, 'Use this for the special notes');

        // Apply styles for columns H and I
        $sheet->getStyle('I' . $totalRow)->getNumberFormat()->setFormatCode('$#,##0.00'); // Format as USD currency
        $sheet->getStyle('I' . $totalRow . ':J' . $totalRow)->applyFromArray($centerAlignment);
        $sheet->getStyle('A'.$totalRow.':J'.$totalRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $signatureRow = $totalRow+1;

        $sheet->setCellValue('A' . $signatureRow, "Charlie Barfield\ncharlie@lab-ready.net\n662-910-9173");

        // Enable text wrapping for the cell
        $sheet->getStyle('A' . $signatureRow)->getAlignment()->setWrapText(true);
        
        // Optionally, adjust the row height to accommodate the wrapped text
        $sheet->getRowDimension($signatureRow)->setRowHeight(-1);
        $sheet->mergeCells('A' . $signatureRow . ':J' . $signatureRow);
        $sheet->getRowDimension($signatureRow)->setRowHeight(50); // Adjust row height for padding
        $sheet->getStyle('A' . $signatureRow . ':J' . $signatureRow)->applyFromArray($backgroundStyleArray);
        $sheet->getStyle('A' . $signatureRow . ':J' . $signatureRow)->applyFromArray($contentStyleArray);
        $sheet->getStyle('A' . $signatureRow . ':J' . $signatureRow)->applyFromArray($leftAlignment);
        $sheet->getStyle('A'.$signatureRow.':J'.$signatureRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

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
            $zip->addFile($tempExcelFile, $requestQuotation['reference'] . '.xlsx');
        
            // Add assembly print files to the zip under the 'assembly-files' folder
            foreach ($assemblyFiles as $file) {
                if (!empty($file['assembly_print_file_location']) && file_exists(FCPATH . $file['assembly_print_file_location'])) {
                    $zip->addFile(FCPATH . $file['assembly_print_file_location'], 'assembly-files/' . basename($file['filename']));
                }
            }
            $csvData = [];
            $csvData[] = ['Address', 'City', 'State', 'Phone Number'];  // Add CSV headers
            
            // Assuming $quotationItems contains the shipping details as well
            foreach ($quotationItems as $item) {
                // Add each item's shipping data to the CSV
                $csvData[] = [
                    $item['address'] ?? 'N/A',  // Use your actual keys for shipping details
                    $item['city'] ?? 'N/A',
                    $item['state'] ?? 'N/A',
                    $item['phonenumber'] ?? 'N/A',
                ];
            }
            
            // Create a temporary file for the CSV
            $csvFilePath = tempnam(sys_get_temp_dir(), 'shipping_details');
            $csvFile = fopen($csvFilePath, 'w');
            
            // Write the CSV data to the file
            foreach ($csvData as $csvRow) {
                fputcsv($csvFile, $csvRow);
            }
            
            fclose($csvFile);
            
            // Add the CSV file to the zip archive in the 'quotation-files' folder
            $zip->addFile($csvFilePath, 'quotation-files/shipping_details.csv');
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
        
            // Delete the zip file and temporary Excel file after download
            unlink($zipFileName);
            unlink($tempExcelFile);
        
            exit;
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to create zip file.']);
        }
    }   
    private function createSecondSheet($spreadsheet, $id)
    {
        $quotationItemsModel = new QuotationItemsModel();
        $requestQuotationModel = new RequestQuotationModel();
        
        $requestQuotation = $requestQuotationModel
            ->join('users', 'users.user_id=request_quotations.user_id', 'left')
            ->find($id);
        $quotationItems = $quotationItemsModel
            ->join('request_quotations', 'request_quotations.request_quotation_id=quotation_items.request_quotation_id', 'left')
            ->join('users', 'request_quotations.user_id=users.user_id', 'left')
            ->where('quotation_items.request_quotation_id', $id)
            ->findAll();
        $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Print Files');
        $spreadsheet->addSheet($newSheet);
    
        // Set title header for the new sheet
        $newSheet->setCellValue('A1', 'Print File (Y/N)');
        $newSheet->setCellValue('B1', 'Part No.');
        $newSheet->setCellValue('C1', 'Vendor Quote');
        $newSheet->setCellValue('D1', 'Shipping');
        $newSheet->setCellValue('E1', '% Markup');
        $newSheet->setCellValue('F1', 'Shop Time');
        $newSheet->setCellValue('G1', 'Total');
        
        // Apply some styling if needed (e.g., bold, font size)
        $newSheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(12);
    
        // Center the title text horizontally and vertically
        $newSheet->getStyle('A1:G1')->getAlignment()->setHorizontal('center')->setVertical('center');

        $newSheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFADADAD');
        $newSheet->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Set padding (indentation) for each cell
        $newSheet->getRowDimension(1)->setRowHeight(25);  
        // Set the width of each column to 1300 (if it's a pixel-based width)
        $columnWidth = 1500 / 7; // Distribute the 1300 width across the 7 columns
        $approxPerCol = $columnWidth / 8;
        $newSheet->getColumnDimension('A')->setWidth($approxPerCol);
        $newSheet->getColumnDimension('B')->setWidth($approxPerCol);
        $newSheet->getColumnDimension('C')->setWidth($approxPerCol);
        $newSheet->getColumnDimension('D')->setWidth($approxPerCol);
        $newSheet->getColumnDimension('E')->setWidth($approxPerCol);
        $newSheet->getColumnDimension('F')->setWidth($approxPerCol);
        $newSheet->getColumnDimension('G')->setWidth($approxPerCol);
        $row = 2;
        foreach($quotationItems as $items) {
            $newSheet->setCellValue('A' . $row, ($items['print_location']) ? 'Y' : 'N');
            $newSheet->setCellValue('B' . $row, $items['partnumber']);
            $newSheet->setCellValue('C' . $row, '');
            $newSheet->setCellValue('D' . $row, '');
            $newSheet->setCellValue('E' . $row, '');
            $newSheet->setCellValue('F' . $row, '');
            $newSheet->setCellValue('G' . $row, "=C$row*(1+E$row)+D$row+(F$row*120)");
            $newSheet->getRowDimension($row)->setRowHeight(30);  
            $newSheet->getStyle('A'.$row.':G'.$row.'')->getAlignment()->setHorizontal('center')->setVertical('center');
            $newSheet->getStyle('A'.$row.':G'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $newSheet->getStyle('C'.$row.':G'.$row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD);
            $row++;
        }
        $blankRow = $row;
        $newSheet->mergeCells('A'.$blankRow.':G'.$blankRow.'');
        $newSheet->getRowDimension($blankRow)->setRowHeight(30);  
        $newSheet->getStyle('A'.$blankRow.':G'.$blankRow.'')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFADADAD');
        $newSheet->getStyle('A'.$blankRow.':G'.$blankRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $lastRow = $blankRow + 1;
        $newSheet->setCellValue('A'.$lastRow.'', '');
        $newSheet->mergeCells('B'.$lastRow.':E'.$lastRow.'');
        $newSheet->setCellValue('B'.$lastRow.'', 'Fit, finish and assembly');
        $newSheet->setCellValue('F'.$lastRow.'', '');
        $newSheet->setCellValue('G' . $lastRow, "=F$lastRow*120");
        $newSheet->getStyle('A'.$lastRow.':G'.$lastRow.'')->getAlignment()->setHorizontal('center')->setVertical('center');
        $newSheet->getRowDimension($lastRow)->setRowHeight(30); 
        $newSheet->getStyle('A'.$lastRow.':G'.$lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        return $newSheet;
    }
}
