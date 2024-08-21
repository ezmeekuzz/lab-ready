<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MaterialsModel;

class EditMaterialController extends SessionController
{
    public function index($id)
    {
        $MaterialsModel = new MaterialsModel();
        $materialDetails = $MaterialsModel->find($id);
        $data = [
            'title' => 'Edit Material | PageDuo',
            'currentpage' => 'materialmasterlist',
            'materialDetails' => $materialDetails
        ];
        return view('admin/editmaterial', $data);
    }
    public function update()
    {
        $MaterialsModel = new MaterialsModel();
        $materialId = $this->request->getPost('material_id');
        $quotetype = $this->request->getPost('quotetype');
        $materialname = $this->request->getPost('materialname');
        $data = [
            'quotetype' => $quotetype,
            'materialname' => $materialname
        ];
        $updated = $MaterialsModel->update($materialId, $data);

        if ($updated) {
            $response = [
                'success' => true,
                'message' => 'Material updated successfully!',
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to update material.',
            ];
        }
    
        return $this->response->setJSON($response);
    }    
}
