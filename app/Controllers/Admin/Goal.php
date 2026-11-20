<?php

namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;

class Goal extends Controller
{
    protected $session;
    protected $validation;
    protected $common_model;

    public function __construct()
    {
        $this->validation = Services::validation();
        $this->common_model = model('App\Models\Common_model', false);
        $this->session = session();
        
        if (!is_admin_logged_in()) {
            return redirect()->to(base_url());
        }
        
        $controller = class_basename(service('router')->controllerName());
        $methodName  = class_basename(service('router')->methodName());
        hasAccess($controller, $methodName);
    }

    public function index()
    {
        if (!$this->session->has('userid')) {
            return view('admin/index');
        }
        
        $data = [
            'menutitle' => 'Goal',
            'pagetitle' => 'Goal',
            'breadcrumb' => '<ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li>
                <li>Manage Goal</li>
            </ul>'
        ];
        
        $condition = ['status' => 'Active'];
        $goalArray = $this->common_model->getAllData('goal', $condition);
        $data['goallList'] = $goalArray;
        
        return view('admin/goal/goal_list', $data);
    }

    public function add()
    {
        if (!$this->session->has('userid')) {
            return view('admin/index');
        }
        
        $data = [
            'menutitle' => 'Goals',
            'pagetitle' => 'Add Goal',
            'breadcrumb' => '<ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li>
                <li><a href="' . base_url() . 'goal/">Manage Goal</a><i class="fa fa-angle-right"></i></li>
                <li>Add Goal</li>
            </ul>'
        ];
        
        if ($this->session->has('err_msg')) {
            $data['err_msg'] = $this->session->get('err_msg');
            $this->session->remove('err_msg');
        }

        if ($this->request->getMethod() === 'post') {
            // Define validation rules with proper limits
            $validationRules = [
                'goal' => [
                    'label' => 'Goal',
                    'rules' => 'required|trim|max_length[100]',
                    'errors' => [
                        'required' => 'Goal name is required',
                        'max_length' => 'Goal name cannot exceed 100 characters'
                    ]
                ],
                'description' => [
                    'label' => 'Description',
                    'rules' => 'required|max_length[1000]',
                    'errors' => [
                        'required' => 'Description is required',
                        'max_length' => 'Description cannot exceed 1000 characters'
                    ]
                ],
                'goalimage' => [
                    'label' => 'Goal Image',
                    'rules' => 'uploaded[goalimage]|max_size[goalimage,2048]|mime_in[goalimage,image/jpg,image/jpeg,image/png]|max_dims[goalimage,1200,800]',
                    'errors' => [
                        'uploaded' => 'Please select an image',
                        'max_size' => 'Image size should not exceed 2MB',
                        'mime_in' => 'Only JPG, JPEG, and PNG images are allowed',
                        'max_dims' => 'Image dimensions should not exceed 1200x800 pixels'
                    ]
                ]
            ];

            if (!$this->validate($validationRules)) {
                $data['validation'] = $this->validator;
                return view('admin/goal/add_goal', $data);
            }

            $file = $this->request->getFile('goalimage');
            
            if ($file->isValid() && !$file->hasMoved()) {
                // Generate secure random filename
                $newName = $file->getRandomName();
                $targetpath = FCPATH . 'public/uploads/goal/';
                
                // Create directory if it doesn't exist
                if (!is_dir($targetpath)) {
                    mkdir($targetpath, 0755, true);
                }
                
                // Move the file
                if ($file->move($targetpath, $newName)) {
                    $insert_data = [
                        'name' => $this->request->getPost('goal'),
                        'image' => $newName,
                        'description' => $this->request->getPost('description'),
                        'status' => 'Active',
                        'created_on' => date('Y-m-d H:i:s'),
                        'created_by' => $this->session->get('userid'),
                        'updated_on' => date('Y-m-d H:i:s'),
                        'updated_by' => $this->session->get('userid')
                    ];

                    $id = $this->common_model->addData('goal', $insert_data);
                    
                    if ($id > 0) {
                        $this->session->setFlashdata('msg', 'Goal added successfully!');
                        $this->session->setFlashdata('msg_type', 'success');
                    } else {
                        $this->session->setFlashdata('msg', 'Failed to add goal');
                        $this->session->setFlashdata('msg_type', 'danger');
                    }
                    return redirect()->to('goal/');
                }
            }
            
            $this->session->setFlashdata('msg', 'Failed to upload image');
            $this->session->setFlashdata('msg_type', 'danger');
            return redirect()->to('goal/add/');
        }
        
        return view('admin/goal/add_goal', $data);
    }

    public function close()
    {
        $goal_id = $this->request->getPost('goal_id');
        
        if ($goal_id > 0) {
            $condition = ['goal_id' => $goal_id];
            $goalDetails = $this->common_model->getAllData('goal', $condition);
            
            if (!empty($goalDetails)) {
                $update_data = [
                    'status' => 'Inactive',
                    'updated_on' => date('Y-m-d H:i:s'),
                    'updated_by' => $this->session->get('userid')
                ];
                
                $id = $this->common_model->updateData('goal', $update_data, $condition);
                
                // Delete the image file
                $old_image = $goalDetails[0]->image;
                $imagePath = FCPATH . 'public/uploads/goal/' . $old_image;
                
                if (file_exists($imagePath) && is_file($imagePath)) {
                    unlink($imagePath);
                }
                
                echo $id;
                return;
            }
        }
        
        echo 0;
    }

    public function edit($goal_id)
    {
        if (!$goal_id > 0 || !$this->session->has('userid')) {
            return view('admin/index');
        }
        
        $data = [
            'menutitle' => 'Goal',
            'pagetitle' => 'Edit Goal',
            'breadcrumb' => '<ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li>
                <li><a href="' . base_url() . 'goal">Manage Goal</a><i class="fa fa-angle-right"></i></li>
                <li>Edit Goal</li>
            </ul>'
        ];
        
        if ($this->session->has('err_msg')) {
            $data['err_msg'] = $this->session->get('err_msg');
            $this->session->remove('err_msg');
        }
        
        $condition = ['goal_id' => $goal_id];
        $goalDetails = $this->common_model->getAllData('goal', $condition);
        
        if (empty($goalDetails)) {
            return redirect()->to('goal/');
        }
        
        $data['goalDetails'] = $goalDetails;

        if ($this->request->getMethod() === 'post') {
            // Define validation rules
            $validationRules = [
                'goal' => [
                    'label' => 'Goal',
                    'rules' => 'required|trim|max_length[100]',
                    'errors' => [
                        'required' => 'Goal name is required',
                        'max_length' => 'Goal name cannot exceed 100 characters'
                    ]
                ],
                'description' => [
                    'label' => 'Description',
                    'rules' => 'required|max_length[1000]',
                    'errors' => [
                        'required' => 'Description is required',
                        'max_length' => 'Description cannot exceed 1000 characters'
                    ]
                ]
            ];
            
            // Add image validation only if new file is uploaded
            if ($this->request->getFile('goalimage')->isValid()) {
                $validationRules['goalimage'] = [
                    'label' => 'Goal Image',
                    'rules' => 'max_size[goalimage,2048]|mime_in[goalimage,image/jpg,image/jpeg,image/png]|max_dims[goalimage,1200,800]',
                    'errors' => [
                        'max_size' => 'Image size should not exceed 2MB',
                        'mime_in' => 'Only JPG, JPEG, and PNG images are allowed',
                        'max_dims' => 'Image dimensions should not exceed 1200x800 pixels'
                    ]
                ];
            }

            if (!$this->validate($validationRules)) {
                $data['validation'] = $this->validator;
                return view('admin/goal/edit_goal', $data);
            }

            $update_data = [
                'name' => $this->request->getPost('goal'),
                'description' => $this->request->getPost('description'),
                'updated_on' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->get('userid')
            ];

            $file = $this->request->getFile('goalimage');
            
            if ($file->isValid() && !$file->hasMoved()) {
                // Delete old image
                $old_image = $goalDetails[0]->image;
                $oldImagePath = FCPATH . 'public/uploads/goal/' . $old_image;
                
                if (file_exists($oldImagePath) && is_file($oldImagePath)) {
                    unlink($oldImagePath);
                }
                
                // Generate new secure filename
                $newName = $file->getRandomName();
                $targetpath = FCPATH . 'public/uploads/goal/';
                
                if ($file->move($targetpath, $newName)) {
                    $update_data["image"] = $newName;
                }
            }

            $id = $this->common_model->updateData('goal', $update_data, $condition);
            
            if ($id > 0) {
                $this->session->setFlashdata('msg', 'Goal updated successfully!');
                $this->session->setFlashdata('msg_type', 'success');
            } else {
                $this->session->setFlashdata('msg', 'Failed to update Goal');
                $this->session->setFlashdata('msg_type', 'danger');
            }
            
            return redirect()->to('goal/');
        }
        
        return view('admin/goal/edit_goal', $data);
    }
}