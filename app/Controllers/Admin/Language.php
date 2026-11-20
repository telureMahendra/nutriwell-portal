<?php

namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;

class Language extends Controller
{
    protected $session;
    protected $validation;

    public function __construct()
    {
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

    public function index()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Language',
                'pagetitle' => 'Language',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Language</li></ul>'
            ];
            $condition = ['status' => 'Active'];
            $languageArray = $this->common_model->getAllData('language', $condition);
            $data['languageList'] = $languageArray;
            echo view('admin/language/language_list', $data);
        } else {
            return view('admin/index');
        }
    }

    public function add()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Language',
                'pagetitle' => 'Add Language',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'language">Manage Language</a><i class="fa fa-angle-right"></i></li><li>Add Language</li></ul>'
            ];
            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }

            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/language/add_language', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Add Language') {

                $validationRules = [
                    'language' => ['label' => 'Language Name', 'rules' => 'required|trim'],
                ];
                if (!$this->validate($validationRules)) {
                    return view('admin/language/add_language', $data);
                } else {

                    $file = $this->request->getFile('language_image');
                    //echo "<pre>";print_r($file);die();
                    $file_name = "";
                    if ($file->isValid()) {

                        $srt = explode('.', $file->getName('language_image'));
                        $file_name = uniqid() . "." . $srt[1];


                        $filetype = "pdf";
                        $targetpath = "./public/uploads/language";
                        $file->move($targetpath, $file_name);
                    }


                    $insert_data = [
                        'language' => trim($this->request->getPost('language')),
                        'language_image' => $file_name,
                        'created_on' => date('Y-m-d'),
                        'created_by' => session()->get('userid'),
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];

                    $id = $this->common_model->addData('language', $insert_data);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'Language added successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to add Language', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('language');
                    // } else {
                    //     return redirect()->to('language/add/');
                    // }
                }
            }
        } else {
            return view('admin/index');
        }
    }

    public function close()
    {
        $language_id = $this->request->getPost('language_id');
        if ($language_id > 0) {
            $update_data = [
                'status' => 'Inactive',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            $condition = ['language_id' => $language_id];
            $id = $this->common_model->updateData('language', $update_data, $condition);
            echo $id;
        } else {
            echo 0;
        }
    }

    public function edit($language_id)
    {
        if ($language_id > 0) {
            $data = [
                'menutitle' => 'Language',
                'pagetitle' => 'Edit Language',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'language">Manage Language</a><i class="fa fa-angle-right"></i></li> <li>Edit Language</li></ul>'
            ];
            $condition = ['language_id' => $language_id];
            $languageDetails = $this->common_model->getAllData('language', $condition);
            $data['languageDetails'] = $languageDetails;

            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/language/edit_language', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Edit Language') {



                $validationRules = [
                    'language' => ['label' => 'Language Name', 'rules' => 'required|trim'],
                ];

                if (!$this->validate($validationRules)) {


                    return view('admin/language/edit_language', $data);
                } else {
                    $file = $this->request->getFile('language_image');
                    $file_name = $languageDetails[0]->language_image;
                    if ($file->isValid() && !$file->hasMoved()) {



                        $old_image = $languageDetails[0]->language_image;

                        //die('lll');
                        //echo $old_image;
                        if (@file_exists("./public/uploads/language/" . $old_image)) {
                            @unlink("./public/uploads/language/" . $old_image);
                        }



                        //echo $file;
                        $srt = explode('.', $file->getName('language_image'));
                        $file_name = uniqid() . "." . $srt[1];
                        $filetype = "pdf";
                        $targetpath = "./public/uploads/language";
                        $file->move($targetpath, $file_name);


                        //die($file_name);
                    }


                    $update_data = [
                        'language' => trim($this->request->getPost('language')),
                        'language_image' => $file_name,
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];
                    $condition = ['language_id' => $language_id];

                    $id = $this->common_model->updateData('language', $update_data, $condition);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'Language update successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to update Language', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('language/');
                    // } else {
                    //     return redirect()->to('language/edit/' . $language_id);
                    // }
                }
            }
        } else {
            return view('admin/index');
        }
    }
}
