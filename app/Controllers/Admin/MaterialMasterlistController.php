<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\SessionController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\MaterialsModel;

class MaterialMasterlistController extends SessionController
{
    public function index()
    {
        $data = [
            'title' => 'Material Masterlist | Lab Ready',
            'currentpage' => 'materialmasterlist'
        ];
        return view('admin/materialmasterlist', $data);
    }
    public function getData()
    {
        $quotetype = $this->request->getPost('quotetype');
        $table =  datatables('materials');
        if ($quotetype) {
            $table->where('quotetype', $quotetype);
        }
        return $table->make();
    }
    public function delete($id)
    {
        $MaterialsModel = new MaterialsModel();

        $deleted = $MaterialsModel->delete($id);

        if ($deleted) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to delete the material from the database']);
        }
    
        return $this->response->setJSON(['status' => 'error', 'message' => 'Material not found']);
    }    
    public function getListByQuoteType()
    {
        if ($this->request->isAJAX()) {
            // Retrieve the quotetype from the AJAX request
            $quotetype = $this->request->getPost('quotetype');

            // Load the MaterialModel (ensure this model exists and is properly set up)
            $materialModel = new MaterialsModel();

            // Fetch the list of materials based on the quotetype
            $materials = $materialModel->where('quotetype', $quotetype)
                                       ->orderBy('arrange_order', 'ASC')
                                       ->findAll();

            // Initialize the HTML string
            $html = '';

            // Check if data is found
            if (!empty($materials)) {
                // Construct the HTML list
                $html .= '<ul class="list-group" id="sortableList">';
                foreach ($materials as $material) {
                    $html .= '<li class="list-group-item" data-id="' . $material['material_id'] . '">';
                    $html .= $material['materialname'] . ' (Order: ' . $material['arrange_order'] . ')';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            } else {
                // If no materials are found
                $html .= '<p>No materials found for the selected quote type.</p>';
            }

            // Return the HTML as a JSON response
            return $this->response->setJSON([
                'status' => 'success',
                'html' => $html
            ]);
        } else {
            // Handle non-AJAX request
            return redirect()->back();
        }
    }
    public function updateOrder()
    {
        $materialModel = new MaterialsModel();

        $order = $this->request->getPost('order'); // Array of material IDs in the new order

        if (!empty($order)) {
            foreach ($order as $index => $id) {
                $materialModel->update($id, ['arrange_order' => $index + 1]);
            }

            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid order data']);
    }
}
