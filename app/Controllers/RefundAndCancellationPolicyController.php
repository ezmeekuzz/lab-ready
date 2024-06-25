<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class RefundAndCancellationPolicyController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Refund And Cancellation Policy - Lab Ready'
        ];
        return view('refundandcancellationpolicy', $data);
    }
}
