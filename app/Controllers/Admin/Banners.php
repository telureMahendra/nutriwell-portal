<?php

namespace App\Controllers\Admin;

use Config\Services;
use App\Models\Banners_model;
use CodeIgniter\Controller;

class Banners extends Controller
{
    protected $session;
    protected $banners_model;
    protected $validation;

    public function __construct()
    {
        $this->validation = Services::validation();
        $this->banners_model = new Banners_model();
        $this->session = session();
        
        if (!is_admin_logged_in()) {
            redirect('');
        }
        
        $controller = class_basename(service('router')->controllerName());
        $methodName = class_basename(service('router')->methodName());
        hasAccess($controller, $methodName);
    }

    public function index()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Banners',
                'pagetitle' => 'All Banners',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Banners</li></ul>'
            ];

            $data['bannersList'] = $this->banners_model->findAll();

            echo view('admin/banners/index', $data);
        } else {
            return view('admin/admin/index');
        }
    }

    public function add()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Banners',
                'pagetitle' => 'Add Banner',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li> <a href="' . base_url() . 'banners" >Manage Banners</a><i class="fa fa-angle-right"> </i> </li> <li>Add Banner </li></ul>'
            ];

            $submitValue = $this->request->getPost('submit');

            if (trim($submitValue) == '') {
                echo view('admin/banners/add', $data);
            } elseif (trim($submitValue) == 'Add Banner') {

                $validationRules = [
                    'title' => ['label' => 'Title', 'rules' => 'required'],
                    'banner_image' => ['label' => 'Banner Image', 'rules' => 'uploaded[banner_image]|is_image[banner_image]|mime_in[banner_image,image/jpg,image/jpeg,image/png]'],
                ];

                $postData = $this->request->getPost();
                $this->validation->setRules($validationRules);

                if (!$this->validation->withRequest($this->request)->run()) {
                    $data['validation'] = $this->validation->getErrors();
                    return view('admin/banners/add', $data);
                } else {
                    $insert_data = array();
                    $insert_data['title'] = trim($postData['title']);
                    $insert_data['status'] = trim($postData['status']) ? trim($postData['status']) : 'Active';

                    $file = $this->request->getFile('banner_image');
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(ROOTPATH . 'public/uploads/banner', $newName);
                        $insert_data['banner_image'] = $newName;
                    }

                    $id = $this->banners_model->insert($insert_data);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'Banner added successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to add banner', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('banners');
                }
            }
        } else {
            return view('admin/index');
        }
    }

    public function edit($id)
    {
        if ($id > 0) {
            $data = [
                'menutitle' => 'Banners',
                'pagetitle' => 'Edit Banner',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'banners">Manage Banners</a><i class="fa fa-angle-right"></i></li> <li>Edit Banner</li></ul>'
            ];

            $bannerDetails = $this->banners_model->find($id);
            if (empty($bannerDetails)) {
                $arr_msg = array('suc_msg' => 'Banner not found!', 'msg-type' => 'danger');
                $this->session->set($arr_msg);
                return redirect()->to('banners');
            }
            $data['bannerDetails'] = $bannerDetails;

            $submitValue = $this->request->getPost('submit');

            if (trim($submitValue) == '') {
                echo view('admin/banners/edit', $data);
            } elseif (trim($submitValue) == 'Edit Banner') {
                $validationRules = [
                    'title' => ['label' => 'Title', 'rules' => 'required'],
                ];

                $postData = $this->request->getPost();
                $this->validation->setRules($validationRules);

                if (!$this->validation->withRequest($this->request)->run()) {
                    $data['validation'] = $this->validation->getErrors();
                    return view('admin/banners/edit', $data);
                } else {
                    $update_data = array();
                    $update_data['title'] = trim($postData['title']);
                    $update_data['status'] = trim($postData['status']) ? trim($postData['status']) : 'Active';

                    $file = $this->request->getFile('banner_image');
                    if ($file && $file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(ROOTPATH . 'public/uploads/banner', $newName);
                        $update_data['banner_image'] = $newName;
                    }

                    $this->banners_model->update($id, $update_data);
                    $arr_msg = array('suc_msg' => 'Banner Updated successfully!!!', 'msg-type' => 'success');
                    $this->session->set($arr_msg);
                    return redirect()->to('banners');
                }
            }
        } else {
            return redirect()->to('banners');
        }
    }

    public function close()
    {
        $id = $this->request->getPost('id');
        if ($id > 0) {
            $this->banners_model->delete($id);
            echo 1;
        } else {
            echo 0;
        }
    }
}
