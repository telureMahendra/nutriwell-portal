<?php
namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;

class Testimonial extends Controller
{
    protected $session;
    protected $validation;
    protected $common_model;
    protected $testimonial_model;
    protected $upload_path;

    public function __construct()
    {
        $this->validation = Services::validation();
        $this->common_model = model('App\Models\Common_model', false);
        $this->testimonial_model = model('App\Models\Testimonial_model', false);
        $this->session = session();
        
        if (!is_admin_logged_in()) {
            redirect('');
        }
        
        $controller = class_basename(service('router')->controllerName());
        $methodName = class_basename(service('router')->methodName());
        hasAccess($controller, $methodName);
        
        // Set upload path
        $this->upload_path = ROOTPATH . 'public/uploads/testimonials/';
    }

    public function index()
    {
        if (!$this->session->has('userid')) {
            return redirect()->to('');
        }

        $data = [
            'menutitle' => 'Testimonials',
            'pagetitle' => 'Manage Testimonials',
            'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li><li>Manage Testimonials</li></ul>'
        ];

        // Get filter parameters
        $type = $this->request->getGet('type');
        $approval_status = $this->request->getGet('approval_status');
        $perPage = $this->request->getGet('per_page') ?: 10;

        // Get testimonials with pagination
        $data['testimonialList'] = $this->testimonial_model->getPaginatedTestimonials($type, $approval_status, $perPage);
        $data['pager'] = $this->testimonial_model->pager;
        $data['perPage'] = $perPage;
        
        // Get pending count for badge
        $data['pending_count'] = $this->testimonial_model->getPendingCount();

        return view('admin/testimonial/testimonial_list', $data);
    }

    public function add()
    {
        if (!$this->session->has('userid')) {
            return redirect()->to('');
        }

        $data = [
            'menutitle' => 'Testimonials',
            'pagetitle' => 'Add Testimonial',
            'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li><li><a href="' . base_url() . 'testimonial">Manage Testimonials</a><i class="fa fa-angle-right"></i></li><li>Add Testimonial</li></ul>'
        ];

        if ($this->session->get('err_msg')) {
            $data['err_msg'] = $this->session->get('err_msg');
            $this->session->remove('err_msg');
        }

        if ($this->request->getMethod() === 'get') {
            return view('admin/testimonial/add_testimonial', $data);
        }

        // Handle form submission
        if ($this->request->getPost('submit') === 'Add Testimonial') {
            $validationRules = [
                'client_name' => 'required|trim',
                'description' => 'required|trim',
                'testimonial_type' => 'required|in_list[text,image]',
                'status' => 'required|in_list[Active,Inactive]',
                'approval_status' => 'required|in_list[Pending,Approved,Rejected]'
            ];

            if ($this->request->getPost('testimonial_type') === 'image') {
                $validationRules['before_image'] = 'uploaded[before_image]|max_size[before_image,2048]|is_image[before_image]';
                $validationRules['after_image'] = 'uploaded[after_image]|max_size[after_image,2048]|is_image[after_image]';
            }

            if (!$this->validate($validationRules)) {
                return view('admin/testimonial/add_testimonial', $data);
            }

            $insert_data = [
                'client_name' => trim($this->request->getPost('client_name')),
                'designation' => trim($this->request->getPost('designation')),
                'description' => trim($this->request->getPost('description')),
                'testimonial_type' => $this->request->getPost('testimonial_type'),
                'status' => $this->request->getPost('status'),
                'approval_status' => $this->request->getPost('approval_status'),
                'rating' => (int)$this->request->getPost('rating', FILTER_SANITIZE_NUMBER_INT),
                'date' => date('Y-m-d'),
                'created_on' => date('Y-m-d'),
                'created_by' => session()->get('userid'),
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];

            // Handle image uploads for image testimonials
            if ($this->request->getPost('testimonial_type') === 'image') {
                $before_image = $this->request->getFile('before_image');
                $after_image = $this->request->getFile('after_image');

                if ($before_image->isValid() && !$before_image->hasMoved()) {
                    $newName = $before_image->getRandomName();
                    $before_image->move($this->upload_path, $newName);
                    $insert_data['before_image'] = 'uploads/testimonials/' . $newName;
                }

                if ($after_image->isValid() && !$after_image->hasMoved()) {
                    $newName = $after_image->getRandomName();
                    $after_image->move($this->upload_path, $newName);
                    $insert_data['after_image'] = 'uploads/testimonials/' . $newName;
                }
            }

            $id = $this->common_model->addData('testimonial', $insert_data);
            if ($id > 0) {
                $arr_msg = ['suc_msg' => 'Testimonial added successfully!', 'msg-type' => 'success'];
            } else {
                $arr_msg = ['suc_msg' => 'Failed to add testimonial', 'msg-type' => 'danger'];
            }

            $this->session->set($arr_msg);
            return redirect()->to('testimonial/');
        }
    }

    public function edit($testimonial_id)
    {
        if (!$this->session->has('userid')) {
            return redirect()->to('');
        }

        $data = [
            'menutitle' => 'Testimonials',
            'pagetitle' => 'Edit Testimonial',
            'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li><li><a href="' . base_url() . 'testimonial">Manage Testimonials</a><i class="fa fa-angle-right"></i></li><li>Edit Testimonial</li></ul>'
        ];

        $condition = ['testimonial_id' => $testimonial_id];
        $testimonialDetails = $this->common_model->getAllData('testimonial', $condition);
        
        if (empty($testimonialDetails)) {
            return redirect()->to('testimonial/');
        }

        $data['testimonialDetails'] = $testimonialDetails[0];

        if ($this->request->getMethod() === 'get') {
            return view('admin/testimonial/edit_testimonial', $data);
        }

        if ($this->request->getPost('submit') === 'Edit Testimonial') {
            $validationRules = [
                'client_name' => 'required|trim',
                'description' => 'required|trim',
                'testimonial_type' => 'required|in_list[text,image]',
                'status' => 'required|in_list[Active,Inactive]',
                'approval_status' => 'required|in_list[Pending,Approved,Rejected]'
            ];

            // Only validate new images if uploaded
            if ($this->request->getPost('testimonial_type') === 'image') {
                if ($this->request->getFile('before_image')->getName()) {
                    $validationRules['before_image'] = 'uploaded[before_image]|max_size[before_image,2048]|is_image[before_image]';
                }
                if ($this->request->getFile('after_image')->getName()) {
                    $validationRules['after_image'] = 'uploaded[after_image]|max_size[after_image,2048]|is_image[after_image]';
                }
            }

            if (!$this->validate($validationRules)) {
                return view('admin/testimonial/edit_testimonial', $data);
            }

            $update_data = [
                'client_name' => trim($this->request->getPost('client_name')),
                'designation' => trim($this->request->getPost('designation')),
                'description' => trim($this->request->getPost('description')),
                'testimonial_type' => $this->request->getPost('testimonial_type'),
                'status' => $this->request->getPost('status'),
                'approval_status' => $this->request->getPost('approval_status'),
                'rating' => (int)$this->request->getPost('rating', FILTER_SANITIZE_NUMBER_INT),
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];

            // Handle image uploads
            if ($this->request->getPost('testimonial_type') === 'image') {
                $before_image = $this->request->getFile('before_image');
                $after_image = $this->request->getFile('after_image');

                if ($before_image->isValid() && !$before_image->hasMoved()) {
                    $newName = $before_image->getRandomName();
                    $before_image->move($this->upload_path, $newName);
                    $update_data['before_image'] = 'uploads/testimonials/' . $newName;
                }

                if ($after_image->isValid() && !$after_image->hasMoved()) {
                    $newName = $after_image->getRandomName();
                    $after_image->move($this->upload_path, $newName);
                    $update_data['after_image'] = 'uploads/testimonials/' . $newName;
                }
            } else {
                // Remove image paths if changing from image to text
                $update_data['before_image'] = null;
                $update_data['after_image'] = null;
            }

            $condition = ['testimonial_id' => $testimonial_id];
            $id = $this->common_model->updateData('testimonial', $update_data, $condition);

            if ($id > 0) {
                $arr_msg = ['suc_msg' => 'Testimonial updated successfully!', 'msg-type' => 'success'];
            } else {
                $arr_msg = ['suc_msg' => 'Failed to update testimonial', 'msg-type' => 'danger'];
            }

            $this->session->set($arr_msg);
            return redirect()->to('testimonial/');
        }
    }

    public function close()
    {
        $testimonial_id = $this->request->getPost('testimonial_id');
        if ($testimonial_id > 0) {
            $update_data = [
                'status' => 'Deleted',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            $condition = ['testimonial_id' => $testimonial_id];
            $id = $this->common_model->updateData('testimonial', $update_data, $condition);
            echo $id;
        } else {
            echo 0;
        }
    }

    public function delete()
    {
        if (!$this->session->has('userid')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Session expired']);
        }

        $testimonial_id = $this->request->getPost('testimonial_id');
        if ($testimonial_id > 0) {
            $update_data = [
                'status' => 'Deleted',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            $condition = ['testimonial_id' => $testimonial_id];
            
            $id = $this->common_model->updateData('testimonial', $update_data, $condition);
            
            if ($id) {
                return $this->response->setJSON(['success' => true, 'message' => 'Testimonial deleted successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete testimonial']);
            }
        }
        
        return $this->response->setJSON(['success' => false, 'message' => 'Invalid ID']);
    }

    // New method to approve/reject testimonials
    public function update_approval()
{
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    if (!$this->session->has('userid')) {
        echo json_encode(['success' => false, 'message' => 'Session expired']);
        return;
    }

    $testimonial_id = $this->request->getPost('testimonial_id');
    $approval_status = $this->request->getPost('approval_status');
    
    // Validate inputs
    if (empty($testimonial_id) || empty($approval_status)) {
        echo json_encode(['success' => false, 'message' => 'Missing parameters']);
        return;
    }
    
    if (!in_array($approval_status, ['Approved', 'Rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid approval status']);
        return;
    }

    // Update the approval status
    $update_data = [
        'approval_status' => $approval_status,
        'updated_on' => date('Y-m-d H:i:s'),
        'updated_by' => $this->session->get('userid')
    ];
    
    $condition = ['testimonial_id' => $testimonial_id];
    $result = $this->common_model->updateData('testimonial', $update_data, $condition);
    
    // Return appropriate response
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Approval status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update approval status']);
    }
}

    // View testimonial details (for modal)
    public function view($testimonial_id)
    {
        if (!$this->session->has('userid')) {
            return redirect()->to('');
        }

        $condition = ['testimonial_id' => $testimonial_id];
        $testimonial = $this->common_model->getAllData('testimonial', $condition);
        
        if (empty($testimonial)) {
            return $this->response->setJSON(['error' => 'Testimonial not found']);
        }

        return $this->response->setJSON($testimonial[0]);
    }
}