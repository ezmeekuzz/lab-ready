<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TermsAndConditionsController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Terms and Conditions - Lab Ready'
        ];
        return view('termsandconditions', $data);
    }
}
