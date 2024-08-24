<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;
use App\Models\UserQuotationsModel;
use App\Models\RequestQuotationModel;
use App\Models\QuotationItemsModel;
use App\Models\QuotationsModel;

class UserMasterlistController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'User Masterlist | Lab Ready',
            'currentpage' => 'usermasterlist'
        ];
        return view('admin/usermasterlist', $data);
    }
    public function getData()
    {
        return datatables('users')->make();
    }
    public function delete($id)
    {
        $UsersModel = new UsersModel();
        $UserQuotationsModel = new UserQuotationsModel();
        $RequestQuotationModel = new RequestQuotationModel();
        $QuotationsModel = new QuotationsModel();
        $QuotationItemsModel = new QuotationItemsModel();
    
        // Find the users by ID
        $users = $UsersModel->find($id);
    
        if ($users) {
    
            // Get all quotation_ids from user_quotations that belong to the user
            $quotationIds = $UserQuotationsModel->where('user_id', $id)->findColumn('quotation_id');
    
            // Get all request_quotation_ids from the request_quotations table that belong to the user
            $requestQuotationIds = $RequestQuotationModel->where('user_id', $id)->findColumn('request_quotation_id');
    
            // Delete all user_quotations within the $id
            $UserQuotationsModel->where('user_id', $id)->delete();
    
            if ($quotationIds) {
                // Find the quotations to get the file paths
                $quotations = $QuotationsModel->whereIn('quotation_id', $quotationIds)->findAll();
    
                // Delete the files associated with the quotations
                foreach ($quotations as $quotation) {
                    if (!empty($quotation['invoicefile'])) {
                        $filePath = WRITEPATH . 'uploads/' . $quotation['invoicefile'];
                        if (file_exists($filePath) && is_file($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
    
                // Delete all quotations in the quotations table using the filtered quotation_ids
                $QuotationsModel->whereIn('quotation_id', $quotationIds)->delete();
            }
    
            if ($requestQuotationIds) {
                // Find the quotation items to get the file paths
                $quotationItems = $QuotationItemsModel->whereIn('request_quotation_id', $requestQuotationIds)->findAll();
    
                // Delete the files associated with the quotation items
                foreach ($quotationItems as $item) {
                    $filePaths = [
                        'file_location' => $item['file_location'],
                        'stl_location' => $item['stl_location'],
                        'print_location' => $item['print_location']
                    ];
                    foreach ($filePaths as $key => $path) {
                        if (!empty($path)) {
                            $fullPath = WRITEPATH . 'uploads/' . $path;
                            if (file_exists($fullPath) && is_file($fullPath)) {
                                unlink($fullPath);
                            }
                        }
                    }
                }
    
                // Delete all quotation_items within the filtered request_quotation_ids
                $QuotationItemsModel->whereIn('request_quotation_id', $requestQuotationIds)->delete();
    
                // Delete all request_quotations within the $id
                $RequestQuotationModel->whereIn('request_quotation_id', $requestQuotationIds)->delete();
            }
    
            // Delete the user record from the database
            $deleted = $UsersModel->delete($id);
    
            if ($deleted) {
                return $this->response->setJSON(['status' => 'success']);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the user from the database']);
            }
        }
    
        return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
    }    
    public function downloadCSV()
    {
        $UsersModel = new UsersModel();

        // Fetch all user data
        $users = $UsersModel
        ->where('usertype', 'Regular User')
        ->findAll();

        // Set the header for CSV output
        $filename = 'users_masterlist_' . date('YmdHis') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        // Open PHP output stream as a file handle
        $output = fopen('php://output', 'w');

        // Write the CSV column headers
        fputcsv($output, ['ID', 'Full Name', 'Email Address', 'Phone Number', 'Company Name', 'Address', 'City', 'State']);

        // Write user data to CSV
        foreach ($users as $user) {
            fputcsv($output, [
                $user['user_id'],
                $user['fullname'],
                $user['email'],
                $user['phonenumber'],
                $user['companyname'],
                $user['address'],
                $user['city'],
                $user['state'],
            ]);
        }

        // Close the output stream
        fclose($output);

        // Stop further output from the controller
        exit();
    }
}
