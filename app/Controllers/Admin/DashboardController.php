<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersModel;
use App\Models\QuotationsModel;

class DashboardController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Dashboard | Lab Ready',
            'currentpage' => 'dashboard'
        ];
        return view('admin/dashboard', $data);
    }
    public function getData()
    {
        // Get the current year
        $currentYear = date('Y');
    
        // Initialize an array to store the sum of product prices for each month
        $quotationDetails = [];
    
        // Fetch all quotations with status 'Paid' for the current year
        $quotationsModel = new QuotationsModel();
        $quotations = $quotationsModel->where('YEAR(quotationdate)', $currentYear)
                                      ->where('status', 'Paid')
                                      ->findAll();
    
        // Calculate the sum of product prices for each month of the current year
        foreach ($quotations as $quotation) {
            // Extract month from quotationdate
            $month = date('m', strtotime($quotation['quotationdate']));
    
            // If the month is not yet initialized, initialize it
            if (!isset($quotationDetails[$month])) {
                $quotationDetails[$month] = 0;
            }
    
            // Add productprice to the sum for the corresponding month
            $quotationDetails[$month] += $quotation['productprice'];
        }
    
        // Return the JSON response
        return $this->response->setJSON($quotationDetails);
    }
}
