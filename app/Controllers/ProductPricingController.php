<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ProductPricingController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Product Pricing - Lab Ready'
        ];
        return view('productpricing', $data);
    }
}
