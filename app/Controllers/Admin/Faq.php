<?php
namespace App\Controllers\Admin;
use Config\Services;
use CodeIgniter\Controller;

class Faq extends Controller {
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
                'menutitle' => 'Faq',
                'pagetitle' => 'Faq',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"> </i> </li> <li>Manage Faq</li></ul>'
            ];
            $condition = ['status' => 'Active'];
            $faqArray = $this->common_model->getAllData('faq',$condition);
            $data['faqList'] = $faqArray;

            echo view('admin/faq/faq_list', $data);
        } else {
            return view('admin/index');
        }
    }

    public function add()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Faq',
                'pagetitle' => 'Add Faq',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'.base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="'.base_url().'faq">Manage Faq</a><i class="fa fa-angle-right"></i></li><li>Add Faq</li></ul>'
            ];
            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }
            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/faq/add_faq', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Add Faq') {
                
                // Updated validation rules with custom error messages
                $validationRules = [
                    'question' => [
                        'label' => 'Question',
                        'rules' => 'required|max_length[250]',
                        'errors' => [
                            'required' => 'Question is required',
                            'max_length' => 'Question cannot exceed 250 characters'
                        ]
                    ],
                    'answer' => [
                        'label' => 'Answer',
                        'rules' => 'required',
                        'errors' => [
                            'required' => 'Answer is required'
                        ]
                    ]
                ];
                
                if (!$this->validate($validationRules)) {
                    $data['validation'] = $this->validator;
                    return view('admin/faq/add_faq', $data);
                } else {
                    $insert_data = [
                        'question' => trim($this->request->getPost('question')),
                        'answer' => trim($this->request->getPost('answer')),
                        'status' => 'Active',
                        'created_on' => date('Y-m-d'),
                        'created_by' => session()->get('userid'),
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];

                    $id = $this->common_model->addData('faq',$insert_data);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg'=>'Faq added successfully!!!','msg-type'=>'success');
                    } else {
                        $arr_msg = array('suc_msg'=>'Failed to add Faq','msg-type'=>'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('faq');
                }
            }
        } else {
            return view('admin/index');
        }
    }

    public function close(){
        $faq_id = $this->request->getPost('faq_id');
        if ($faq_id > 0) {
            $update_data = [
                'status' => 'Inactive',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            $condition = ['faq_id' => $faq_id];
            $id = $this->common_model->updateData('faq', $update_data, $condition);
            echo $id;
        }else {
            echo 0;
        }
    }

    public function edit($faq_id) {
        if ($faq_id > 0) {
            $data = [
                'menutitle' => 'Faq',
                'pagetitle' => 'Edit Faq',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="'. base_url().'faq">Manage Faq</a><i class="fa fa-angle-right"></i></li> <li>Edit Faq</li></ul>'
            ];
            $condition = ['faq_id' => $faq_id];
            $faqDetails = $this->common_model->getAllData('faq', $condition);
            $data['faqDetails'] = $faqDetails;
            
            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/faq/edit_faq', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Edit Faq') {
                
                // Updated validation rules with custom error messages
                $validationRules = [
                    'question' => [
                        'label' => 'Question',
                        'rules' => 'required|max_length[250]',
                        'errors' => [
                            'required' => 'Question is required',
                            'max_length' => 'Question cannot exceed 250 characters'
                        ]
                    ],
                    'answer' => [
                        'label' => 'Answer',
                        'rules' => 'required',
                        'errors' => [
                            'required' => 'Answer is required'
                        ]
                    ]
                ];

                if (!$this->validate($validationRules)) {
                    $data['validation'] = $this->validator;
                    return view('admin/faq/edit_faq', $data);
                } else {
                    $update_data = [
                        'question' => trim($this->request->getPost('question')),
                        'answer' => trim($this->request->getPost('answer')),
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];
                    $condition = ['faq_id' => $faq_id];

                    $id = $this->common_model->updateData('faq',$update_data,$condition);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg'=>'FAQ update successfully!!!','msg-type'=>'success');
                    }else{
                        $arr_msg = array('suc_msg'=>'Failed to update FAQ','msg-type'=>'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('faq/');
                }
            }
        } else {
            return view('admin/index');
        }
    }

    
}