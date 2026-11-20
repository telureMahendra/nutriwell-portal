<?php
namespace App\Controllers\Admin;
use Config\Services;
use CodeIgniter\Controller;

class Medical_condition extends Controller {
    protected $session;
    protected $validation;

    public function __construct() {
        $this->validation = Services::validation();
        $this->common_model = model('App\Models\Common_model', false);
        $this->session = session();
        if (!is_admin_logged_in()) {
            redirect('');
        }
		$controller = class_basename(service('router')->controllerName());
		$methodName  = class_basename(service('router')->methodName());
		hasAccess($controller, $methodName);        
    }

    public function index() {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Medical Condition',
                'pagetitle' => 'Medical Condition',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"> </i> </li> <li>Manage Medical Condition</li></ul>'
            ];
            $condition = ['status' => 'Active'];
            $medicalArray = $this->common_model->getAllData('medical_condition',$condition);
            $data['medicalList'] = $medicalArray;

            echo view('admin/medical_condition/medical_condition_list', $data);
        } else {
            return view('admin/index');
        }
    }

    public function add()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Medical Condition',
                'pagetitle' => 'Add Medical Condition',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'.base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="'.base_url().'medical_condition">Manage Medical Condition</a><i class="fa fa-angle-right"></i></li><li>Add Medical Condition</li></ul>'
            ];
            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }
            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/medical_condition/add_medical_condition', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Add Medical Condition') {
                
                $validationRules = [
                    'title' => ['label' => 'Title', 'rules' => 'required|trim'],
                ];
                if (!$this->validate($validationRules)) {
                    return view('admin/medical_condition/add_medical_condition', $data);
                } else {
                    $insert_data = [
                        'title' => trim($this->request->getPost('title')),
                        'status' => 'Active',
                        'created_on' => date('Y-m-d'),
                        'created_by' => session()->get('userid'),
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];

                    $id = $this->common_model->addData('medical_condition',$insert_data);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg'=>'Medical Condition added successfully!!!','msg-type'=>'success');
                    } else {
                        $arr_msg = array('suc_msg'=>'Failed to add Medical Condition','msg-type'=>'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('medical_condition');
                }
            }
        } else {
            return view('admin/index');
        }
    }

    public function close(){
        $medical_id = $this->request->getPost('medical_id');
        if ($medical_id > 0) {
            $update_data = [
                'status' => 'Inactive',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            $condition = ['medical_id' => $medical_id];
            $id = $this->common_model->updateData('medical_condition', $update_data, $condition);
            echo $id;
        }else {
            echo 0;
        }
    }

    public function edit($medical_id) {
        if ($medical_id > 0) {
            $data = [
                'menutitle' => 'Medical Condition',
                'pagetitle' => 'Edit Medical Condition',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="'. base_url().'medical_condition">Manage Medical Condition</a><i class="fa fa-angle-right"></i></li> <li>Edit Medical Condition </li></ul>'
            ];
            $condition = ['medical_id' => $medical_id];
            $medicalDetails = $this->common_model->getAllData('medical_condition', $condition);
            $data['medicalDetails'] = $medicalDetails;
            
            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/medical_condition/edit_medical_condition', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Edit Medical Condition') {
                
                $validationRules = [
                    'title' => ['label' => 'Medical Condition', 'rules' => 'required|trim'],
                ];

                if (!$this->validate($validationRules)) {
                    return view('admin/medical_condition/edit_medical_condition', $data);
                } else {
                    $update_data = [
                        'title' => trim($this->request->getPost('title')),
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];
                    $condition = ['medical_id' => $medical_id];

                    $id = $this->common_model->updateData('medical_condition',$update_data,$condition);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg'=>'Medical Condition update successfully!!!','msg-type'=>'success');
                    }else{
                        $arr_msg = array('suc_msg'=>'Failed to update Medical Condition','msg-type'=>'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('medical_condition');
                }
            }
        } else {
            return view('admin/index');
        }
    }
}