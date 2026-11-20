<?php

namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;

class City extends Controller
{
	protected $session;
	protected $validation;

	public function __construct()
	{
		$this->validation = Services::validation();
		$this->common_model = model('App\Models\Common_model', false);
		$this->city_model = model('App\Models\City_model', false);
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
				'menutitle' => 'City',
				'pagetitle' => 'City',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage City</li></ul>'
			];
			$condition = ['status' => 'Active'];

			$contest_images = $this->city_model->getCityList("city", $condition);
			$pager=service('pager');
			$page=(int)(($this->request->getVar('page')!==null)?$this->request->getVar('page'):1)-1;
			$perPage =  PAGELIMIT;
			$total = count($contest_images);
			$pager->makeLinks($page+1, $perPage, $total);
			$offset = $page * $perPage;



			
			$cityArray = $this->city_model->getCityList('city', $condition,$perPage, $offset);
			$data['cityList'] = $cityArray;
			echo view('admin/city/city_list', $data);
		} else {
			return view('admin/index');
		}
	}


	public function add()
	{
		if ($this->session->has('userid')) {
			$data = [
				'menutitle' => 'City',
				'pagetitle' => 'Add City',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"> </i> </li> <li> <a href="' . base_url() . 'city/" >Manage City</a><i class="fa fa-angle-right"> </i> </li>Add City</a></li></ul>'
			];
			$condition = ['status' => 'Active'];
			$cityDetails = $this->common_model->getAllData('city', $condition);

			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}

			if (trim($this->request->getPost('submit')) == '') {
				echo view('admin/city/add_city', $data);
			} elseif ($this->request->getPost('submit') == 'Add City') {
				$validationRules = [
					'city' => 'required|trim',
				];

				if (!$this->validate($validationRules)) {
					return view('admin/city/add_city', $data);
				} else {
					$newCity = $this->request->getPost('city');
					$insert_data = [
						'city_name' => $newCity,
						'status' => 'Active',
						'created_on' => date('Y-m-d'),
						'created_by' => session()->get('userid'),
						'updated_on' => date('Y-m-d'),
						'updated_by' => session()->get('userid')
					];


					$id = $this->common_model->addData('city', $insert_data);
					if ($id > 0) {
						$arr_msg = array('suc_msg' => 'city added successfully!!!', 'msg-type' => 'success');
					} else {
						$arr_msg = array('suc_msg' => 'Failed to add city', 'msg-type' => 'danger');
					}
					$this->session->set($arr_msg);
					return redirect()->to('city/');
				}
			}
		}
	}


	public function edit($city_id)
	{
		if ($city_id > 0) {
			$data = [
				'menutitle' => 'City',
				'pagetitle' => 'Edit City',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'city">Manage City</a><i class="fa fa-angle-right"></i></li>Edit City</a></li></ul>'
			];
			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}
			$condition = ['city_id' => $city_id];
			$cityDetails = $this->common_model->getAllData('city', $condition);
			$data['cityDetails'] = $cityDetails;

			if (trim($this->request->getPost('submit')) == '') {
				return view('admin/city/edit_city', $data);
			} elseif (trim($this->request->getPost('submit')) == 'Edit City') {
				$validationRules = [
					'city' => 'required|trim',
				];

				if (!$this->validate($validationRules)) {
					return view('admin/city/add_city', $data);
				} else {
					$update_data = [
						'city_name' => $this->request->getPost('city'),
						'updated_on' => date('Y-m-d'),
						'updated_by' => session()->get('userid')
					];

					$condition = ['city_id' => $city_id];

					$id = $this->common_model->updateData('city', $update_data, $condition);
					if ($id > 0) {
						$arr_msg = array('suc_msg' => 'City update successfully!!!', 'msg-type' => 'success');
					} else {
						$arr_msg = array('suc_msg' => 'Failed to update City', 'msg-type' => 'danger');
					}
					$this->session->set($arr_msg);
					return redirect()->to('city/');
				}
			} else {
				return view('admin/index');
			}
		}
	}

	public function close()
	{
		$city_id = $this->request->getPost('city_id');
		$condition = ['city_id' => $city_id];
		if ($city_id > 0) {
			$update_data = [
				'status' => 'Inactive',
				'updated_on' => date('Y-m-d'),
				'updated_by' => session()->get('userid')
			];
			$id = $this->common_model->updateData('city', $update_data, $condition);
			echo $id;
		} else {
			echo 0;
		}
	}
}
