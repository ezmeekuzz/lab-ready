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
        $sheet->getStyle('A1:I1')->applyFromArray($lighterFontStyleArray);
        $sheet->getStyle('A2:I2')->applyFromArray($lighterFontStyleArray);
        $sheet->getStyle('A3:I3')->applyFromArray($lighterFontStyleArray);
        $sheet->getStyle('A4:I4')->applyFromArray($lighterFontStyleArray);
        $sheet->getStyle('A5:I5')->applyFromArray($lighterFontStyleArray);
        
        // Apply styles to the header row
        $sheet->getStyle('A6:I6')->applyFromArray($boldHeaderStyleArray);
        
        // Apply center alignment to specific cells
        $sheet->getStyle('A1')->applyFromArray($centerAlignment);
        $sheet->getStyle('B1')->applyFromArray($centerAlignment);
        $sheet->getStyle('A6:I6')->applyFromArray($centerAlignment);
        
        // Apply left alignment to the rest of the cells
        $sheet->getStyle('A2:I5')->applyFromArray($leftAlignment);
        
        // Set the title row
        $sheet->setCellValue('A1', 'Lab Ready');
        $sheet->mergeCells('A1:I1');
        
        // Set the content row
        $sheet->setCellValue('A2', 'Orthopedic Prototypes - On Time and Under Budget');
        $sheet->mergeCells('A2:I2');
        $sheet->getRowDimension(2)->setRowHeight(30); // Adjust row height for padding
        $sheet->getRowDimension(3)->setRowHeight(30); // Adjust row height for padding
        $sheet->getRowDimension(4)->setRowHeight(30); // Adjust row height for padding
        $sheet->getRowDimension(6)->setRowHeight(30); // Adjust row height for padding
        
        // Set quote details
        $sheet->setCellValue('A3', 'Quote:');
        $sheet->setCellValue('B3', $requestQuotation['reference']); // Assuming reference is the quote number
        $sheet->setCellValue('H3', $requestQuotation['fullname']); // Assuming customer name field
        
        $sheet->setCellValue('A4', 'Date:');
        $sheet->setCellValue('B4', date('Y-m-d')); // Actual date
        $sheet->setCellValue('H4', $requestQuotation['email']); // Assuming customer email field
        
        $sheet->setCellValue('H5', $requestQuotation['phonenumber']); // Assuming customer phone field
        
        // Set the header row
        $sheet->setCellValue('A6', 'Item No');
        $sheet->setCellValue('B6', 'Part Number');
        $sheet->setCellValue('C6', 'Material');
        $sheet->setCellValue('D6', 'Special Surface Treatment');
        $sheet->setCellValue('E6', 'Method');
        $sheet->setCellValue('F6', 'Print Uploaded');
        $sheet->setCellValue('G6', 'Qty');
        $sheet->setCellValue('H6', 'Price');
        $sheet->setCellValue('I6', 'Note');
        
        $sheet->getStyle('A6:I6')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
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
        $sheet->getStyle('A1:I1')->applyFromArray($titleStyleArray);
        $sheet->getStyle('A2:I2')->applyFromArray($contentStyleArray);
        
        // Apply background style
        $sheet->getStyle('A1:I6')->applyFromArray($backgroundStyleArray);
        
        // Set column widths (approximation in units)
        $totalWidthPixels = 1500;
        $numColumns = 9; // Columns A to I
        $approxPixelsPerColumn = $totalWidthPixels / $numColumns;
        
        // Example conversion: 1 unit ≈ 8 pixels
        $approxUnitsPerColumn = $approxPixelsPerColumn / 8;
        
        // Set widths for columns A to I
        for ($column = 'A'; $column <= 'I'; $column++) {
            $sheet->getColumnDimension($column)->setWidth($approxUnitsPerColumn);
        }
        
        $row = 7;
        foreach ($quotationItems as $item) {
            $sheet->setCellValue('A' . $row, $item['request_quotation_id']); // Assuming item number is stored in 'item_no'
            $sheet->setCellValue('B' . $row, $item['partnumber']); // Assuming part number is stored in 'part_number'
            $sheet->setCellValue('C' . $row, $item['material']); // Assuming material is stored in 'material'
            $sheet->setCellValue('D' . $row, '(anodizing, etc)'); // Assuming special surface treatment is stored in 'special_surface_treatment'
            $sheet->setCellValue('E' . $row, $item['quotetype']); // Assuming method is stored in 'method'
            $sheet->setCellValue('F' . $row, ($item['print_location']) ? 'Yes' : 'No'); // Assuming print uploaded is stored in 'print_uploaded'
            $sheet->setCellValue('G' . $row, $item['quantity']); // Assuming quantity is stored in 'quantity'
            $sheet->setCellValue('H' . $row, '0.00'); // Assuming price is stored in 'price'
            $sheet->setCellValue('I' . $row, 'Add Note...'); // Assuming note is stored in 'note'
        
            // Format the price column as currency
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('$#,##0.00'); // Format as USD currency
        
            // Center alignment and adjust padding
            $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray($centerAlignment);
            $sheet->getStyle('A' . $row . ':I' . $row)->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(30); // Adjust row height for padding
            $sheet->getStyle('A'.$row.':I'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $row++;
        }
        $subtotalRow = $row; // This will be the next row after the loop
        $sheet->mergeCells('A' . $subtotalRow . ':G' . $subtotalRow);
        $sheet->setCellValue('A' . $subtotalRow, 'Subtotal (components):');
        $sheet->getStyle('A'.$subtotalRow.':I'.$subtotalRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        // Apply styles for the subtotal row
        $sheet->getStyle('A' . $subtotalRow . ':G' . $subtotalRow)->applyFromArray($centerAlignment);
        $sheet->getStyle('A' . $subtotalRow . ':G' . $subtotalRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // Align right
        $sheet->getRowDimension($subtotalRow)->setRowHeight(30); // Adjust row height for padding
        $sheet->getStyle('A' . $subtotalRow . ':G' . $subtotalRow)->applyFromArray($backgroundStyleArray);
        $sheet->getStyle('A' . $subtotalRow . ':G' . $subtotalRow)->applyFromArray($contentStyleArray);
        $sheet->getStyle('A' . $subtotalRow . ':G' . $subtotalRow)->applyFromArray($boldSubtotalStyleArray);
        
        // Set the content for columns H and I in the subtotal row
        $sheet->setCellValue('H' . $subtotalRow, '=SUM(H7:H' . ($row - 1) . ')'); // Calculate subtotal (sum of H column cells above)
        $sheet->setCellValue('I' . $subtotalRow, '');
        
        // Apply styles for columns H and I
        $sheet->getStyle('H' . $subtotalRow)->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle('H' . $subtotalRow . ':I' . $subtotalRow)->applyFromArray($centerAlignment);

        $assemblyRow = $row+1;

        $sheet->setCellValue('A' . $assemblyRow, '1');
        $sheet->mergeCells('B' . $assemblyRow . ':F' . $assemblyRow);
        $sheet->setCellValue('G' . $assemblyRow, '(Leave Blank)');
        $sheet->setCellValue('B' . $assemblyRow, 'Fit, finish and assembly - shop hours');

        $sheet->getStyle('A' . $assemblyRow . ':G' . $assemblyRow)->applyFromArray($centerAlignment);
        $sheet->getRowDimension($assemblyRow)->setRowHeight(30); // Adjust row height for padding
        $sheet->setCellValue('H' . $assemblyRow, '0.00'); // Calculate subtotal (sum of H column cells above)
        $sheet->setCellValue('I' . $assemblyRow, 'Add Note...');
        $sheet->getStyle('A'.$assemblyRow.':I'.$assemblyRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Apply styles for columns H and I
        $sheet->getStyle('H' . $assemblyRow)->getNumberFormat()->setFormatCode('$#,##0.00'); // Format as USD currency
        $sheet->getStyle('H' . $assemblyRow . ':I' . $assemblyRow)->applyFromArray($centerAlignment);

        $shippingRow = $assemblyRow+1;

        $sheet->setCellValue('A' . $shippingRow, 'Shipping');
        $sheet->mergeCells('A' . $shippingRow . ':G' . $shippingRow);

        $sheet->getStyle('A' . $shippingRow . ':G' . $shippingRow)->applyFromArray($centerAlignment);
        $sheet->getStyle('A' . $shippingRow . ':G' . $shippingRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // Align right
        $sheet->getRowDimension($shippingRow)->setRowHeight(30); // Adjust row height for padding
        $sheet->getStyle('A' . $shippingRow . ':G' . $shippingRow)->applyFromArray($backgroundStyleArray);
        $sheet->getStyle('A' . $shippingRow . ':G' . $shippingRow)->applyFromArray($contentStyleArray);
        $sheet->getStyle('A' . $shippingRow . ':G' . $shippingRow)->applyFromArray($boldSubtotalStyleArray);
        
        $sheet->setCellValue('H' . $shippingRow, '0.00'); // Calculate subtotal (sum of H column cells above)
        $sheet->setCellValue('I' . $shippingRow, 'Add Note...');

        // Apply styles for columns H and I
        $sheet->getStyle('H' . $shippingRow)->getNumberFormat()->setFormatCode('$#,##0.00'); // Format as USD currency
        $sheet->getStyle('H' . $shippingRow . ':I' . $shippingRow)->applyFromArray($centerAlignment);
        $sheet->getStyle('A'.$shippingRow.':I'.$shippingRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $totalRow = $shippingRow+1;

        $sheet->setCellValue('A' . $totalRow, 'Total :');
        $sheet->mergeCells('A' . $totalRow . ':G' . $totalRow);

        $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray($centerAlignment);
        $sheet->getRowDimension($totalRow)->setRowHeight(30); // Adjust row height for padding
        $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray($backgroundStyleArray);
        $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray($contentStyleArray);
        $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray($boldSubtotalStyleArray);
        
        $sheet->setCellValue('H' . $totalRow, '=SUM(H'. $subtotalRow .':H' . $shippingRow . ')'); // Calculate subtotal (sum of H column cells above)
        $sheet->setCellValue('I' . $totalRow, 'Add Note...');

        // Apply styles for columns H and I
        $sheet->getStyle('H' . $totalRow)->getNumberFormat()->setFormatCode('$#,##0.00'); // Format as USD currency
        $sheet->getStyle('H' . $totalRow . ':I' . $totalRow)->applyFromArray($centerAlignment);
        $sheet->getStyle('A'.$totalRow.':I'.$totalRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $signatureRow = $totalRow+1;

        $sheet->setCellValue('A' . $signatureRow, "Charlie Barfield\ncharlie@lab-ready.net\n662-910-9173");

        // Enable text wrapping for the cell
        $sheet->getStyle('A' . $signatureRow)->getAlignment()->setWrapText(true);
        
        // Optionally, adjust the row height to accommodate the wrapped text
        $sheet->getRowDimension($signatureRow)->setRowHeight(-1);
        $sheet->mergeCells('A' . $signatureRow . ':I' . $signatureRow);
        $sheet->getRowDimension($signatureRow)->setRowHeight(50); // Adjust row height for padding
        $sheet->getStyle('A' . $signatureRow . ':I' . $signatureRow)->applyFromArray($backgroundStyleArray);
        $sheet->getStyle('A' . $signatureRow . ':I' . $signatureRow)->applyFromArray($contentStyleArray);
        $sheet->getStyle('A' . $signatureRow . ':I' . $signatureRow)->applyFromArray($leftAlignment);
        $sheet->getStyle('A'.$signatureRow.':I'.$signatureRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

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
            $newSheet->setCellValue('B' . $row, $items['print_location_original_name']);
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
