<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class MaterialsAndSurfaceFinishesController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Materials And Surface Finishes - Lab Ready'
        ];
        return view('materialsandsurfacefinishes', $data);
    }
}
