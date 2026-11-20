<?php

namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;

class Lifestyle extends Controller
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
				'menutitle' => 'Lifestyle',
				'pagetitle' => 'Lifestyle',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Lifestyle</li></ul>'
			];
			$condition = ['status' => 'Active'];
			$lifestyleArray = $this->common_model->getAllData('lifestyle', $condition);
			$data['lifestyleList'] = $lifestyleArray;
			// echo "<pre>";print_r($data);die();
			echo view('admin/lifestyle/lifestyle_list', $data);
		} else {
			return view('admin/index');
		}
	}


	public function add()
	{
		if ($this->session->has('userid')) {
			$data = [
				'menutitle' => 'Lifestyle',
				'pagetitle' => 'Add Lifestyle',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"> </i> </li> <li> <a href="' . base_url() . 'lifestyle/" >Manage Lifestyle</a><i class="fa fa-angle-right"> </i> </li> <li>Add Lifestyle</li></ul>'
			];
			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}

			if (trim($this->request->getPost('submit')) == '') {
				echo view('admin/lifestyle/add_lifestyle', $data);
			} elseif ($this->request->getPost('submit') == 'Add Lifestyle') {
				//echo "124yjh";die();
				//echo "<pre>";print_r($this->request->getFile());die();
				$validationRules = [
					'lifestyle' => 'required|trim',
					'discription' => 'required'
				];

				if (!$this->validate($validationRules)) {
					// echo "not  cvsal";die();

					return view('admin/lifestyle/add_lifestyle', $data);
				} //if
				else {
					// echo "124yjh";die();

					$file = $this->request->getFile('lifestyleimage');
					if ($file->isValid()) {

						$srt = explode('.', $file->getName('lifestyleimage'));
						$file_name = uniqid() . "." . $srt[1];
						$filetype = "pdf";
						$targetpath = "./public/uploads/lifestyle";
						$file->move($targetpath, $file_name);
						//echo $file_name;
						$insert_data = [
							'name' => $this->request->getPost('lifestyle'),
							'image' => $file_name,
							'description' => $this->request->getPost('discription'),
							'status' => 'Active',
							'created_on' => date('Y-m-d'),
							'created_by' => session()->get('userid'),
							'updated_on' => date('Y-m-d'),
							'updated_by' => session()->get('userid')
						];

						//echo"<pre>";print_r($insert_data);die();
						$id = $this->common_model->addData('lifestyle', $insert_data);
						//echo $id;
						if ($id > 0) {
							$arr_msg = array('suc_msg' => 'lifestyle added successfully!!!', 'msg-type' => 'success');
						} else {
							$arr_msg = array('suc_msg' => 'Failed to add lifestyle', 'msg-type' => 'danger');
						}
						$this->session->set($arr_msg);
						return redirect()->to('lifestyle/');
					} //if
					else {
						return redirect()->to('lifestyle/add');
					}
				} //else
			} //elseif

		} //if
	} //function

	public function edit($lifestyle_id)
	{
		if ($lifestyle_id > 0) {
			$data = [
				'menutitle' => 'Lifestyle',
				'pagetitle' => 'Edit Lifestyle',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'lifestyle">Manage Lifestyle</a><i class="fa fa-angle-right"></i></li> <li>Edit Lifestyle</li></ul>'
			];
			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			} //if
			$condition = ['lifestyle_id' => $lifestyle_id];
			$lifestyleDetails = $this->common_model->getAllData('lifestyle', $condition);
			//echo"pre"; print_r($testimonialDetails);
			$data['lifestyleDetails'] = $lifestyleDetails;
			//echo $goalDetails[0]->image;

			if (trim($this->request->getPost('submit')) == '') {
				return view('admin/lifestyle/edit_lifestyle', $data);
			} //if
			elseif (trim($this->request->getPost('submit')) == 'Edit Lifestyle') {
				$validationRules = [
					'lifestyle' => 'required|trim',
					'discription' => 'required'
				];

				if (!$this->validate($validationRules)) {
					return view('admin/lifestyle/add_lifestyle', $data);
				} //if
				else {

					$update_data = [
						'name' => $this->request->getPost('lifestyle'),
						'description' => $this->request->getPost('discription'),
						'updated_on' => date('Y-m-d'),
						'updated_by' => session()->get('userid')
					];


					$file = $this->request->getFile('lifestyleimage');
					if ($file->isValid() && !$file->hasMoved()) {

						$old_image = $lifestyleDetails[0]->image;
						//echo $old_image;
						if (file_exists("./public/uploads/lifestyle/" . $old_image)) {
							unlink("./public/uploads/lifestyle/" . $old_image);
						}
						//echo $file;
						$srt = explode('.', $file->getName('lifestyleimage'));
						$file_name = uniqid() . "." . $srt[1];
						$filetype = "pdf";
						$targetpath = "./public/uploads/lifestyle";
						$file->move($targetpath, $file_name);
						$update_data["image"] = $file_name;
						//echo $file_name;
					} //if


					$condition = ['lifestyle_id' => $lifestyle_id];

					$id = $this->common_model->updateData('lifestyle', $update_data, $condition);
					if ($id > 0) {
						$arr_msg = array('suc_msg' => 'Lifestyle update successfully!!!', 'msg-type' => 'success');
					} //if
					else {
						$arr_msg = array('suc_msg' => 'Failed to update Lifestyle', 'msg-type' => 'danger');
					}
					$this->session->set($arr_msg);
					return redirect()->to('lifestyle/');
				} //else
			} //elseif
			else {
				return view('admin/index');
			} //else
		} //if
	} //fun

	public function close()
	{
		$lifestyle_id  = $this->request->getPost('lifestyle_id');
		$condition = ['lifestyle_id' => $lifestyle_id];
		$lifestyleDetails = $this->common_model->getAllData('lifestyle', $condition);
		if ($lifestyle_id > 0) {
			$update_data = [
				'status' => 'Inactive',
				'updated_on' => date('Y-m-d'),
				'updated_by' => session()->get('userid')
			];

			$id = $this->common_model->updateData('lifestyle', $update_data, $condition);
			$old_image = $lifestyleDetails[0]->image;
			//echo $old_image;
			if (file_exists("./public/uploads/lifestyle/" . $old_image)) {
				unlink("./public/uploads/lifestyle/" . $old_image);
			}
			echo $id;
		} else {
			echo 0;
		}
	}
}
