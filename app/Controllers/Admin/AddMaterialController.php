<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MaterialsModel;

class AddMaterialController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Add Manufacturing Service | Lab Ready',
            'currentpage' => 'addmaterial'
        ];
        return view('admin/addmaterial', $data);
    }
    public function insert()
    {
        $materialsModel = new MaterialsModel();
    
        $quotetype = $this->request->getPost('quotetype');
        $materialname = $this->request->getPost('materialname');
    
        // Count the existing materials
        $count = $materialsModel->where('quotetype', $quotetype)->countAllResults();
    
        // Increment the count by 1 for the new material
        $arrangeOrder = $count  + 1;
    
        $data = [
            'quotetype' => $quotetype,
            'materialname' => $materialname,
            'arrange_order' => $arrangeOrder, // Use the calculated order
        ];
    
        $materialId = $materialsModel->insert($data);
    
        if ($materialId) {
            $response = [
                'success' => 'success',
                'message' => 'Material added successfully!',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to add material.',
            ];
        }
    
        return $this->response->setJSON($response);
    }    
}
