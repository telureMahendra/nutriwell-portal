<?php
namespace App\Controllers\Admin;
use Config\Services;
use CodeIgniter\Controller;

class Doctor extends Controller {
	protected $session;
	protected $validation;

	public function __construct() {
		$this->validation = Services::validation();

		$this->common_model = model('App\Models\Common_model', false);
		$this->doctor_model = model('App\Models\Doctor_model', false);
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

			if ($this->request->getGet('submit') == 'Filter') {
	            $designation = $this->request->getGet('designation');
	        }else{
	        	$designation = '';
	        }

			$data = [
				'designation' => $designation,
				'menutitle' => 'Mentor',
				'pagetitle' => 'Mentor',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Mentor</li></ul>'
			];

			$condition = ['status' => 'Active'];
			$designationList = $this->common_model->getAllData('designation',$condition);
            $allDesignation = ['' => 'Select Designation'];
            foreach ($designationList as $row) {
                $allDesignation[$row->designation] = $row->designation;
            }
            $data['allDesignation'] = $allDesignation;

			$condition = ['status' => 'Active'];
			if ($designation !='') {
				$condition= ['designation' => $designation,'status' => 'Active'];
			}
			
			$contest_images = $this->doctor_model->getDoctorList("doctor", $condition);
			$pager=service('pager');
			$page=(int)(($this->request->getVar('page')!==null)?$this->request->getVar('page'):1)-1;
			$perPage =  PAGELIMIT;
			$total = count($contest_images);
			$pager->makeLinks($page+1, $perPage, $total);
			$offset = $page * $perPage;


			$doctorArray = $this->doctor_model->getDoctorList('doctor',$condition,$perPage, $offset);
			$data['doctorList'] = $doctorArray;
			echo view('admin/doctor/doctor_list', $data);
		} else {
			return view('admin/index');
		}
	}

	public function add(){
		if ($this->session->has('userid')) {
			$data = [
				'menutitle' => 'Mentor',
				'pagetitle' => 'Add Mentor',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"> </i> </li> <li> <a href="'.base_url().'doctor/" >Manage Mentor</a><i class="fa fa-angle-right"> </i> </li> <li>Add Mentor</a></li></ul>'
			];
			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}
			
			$condition = ['status'=>'Active'];
			$designationArray = $this->common_model->getAllData('designation',$condition);
            $allDesignation = array(''=>'Select Designation');
            foreach ($designationArray as $row) {
                $allDesignation[$row->designation] = $row->designation;
            }
            $data['allDesignation'] = $allDesignation;

            $condition = [];
			$stateArray = $this->common_model->getAllData('gst_state_codes',$condition);
            $stateList = array(''=>'Select State');
            foreach ($stateArray as $row) {
                $stateList[$row->state_id] = $row->state_name;
            }
            $data['stateList'] = $stateList;

			$condition = ['status'=>'Active'];
			$cityArray = $this->common_model->getAllData('city',$condition);
            $cityList = array(''=>'Select City');
            foreach ($cityArray as $row) {
                $cityList[$row->city_id] = $row->city_name;
            }
            $data['cityList'] = $cityList;

            $condition = ['status'=>'Active'];
			$languageArray = $this->common_model->getAllData('language',$condition);
            foreach ($languageArray as $row) {
                $languageList[$row->language_id] = $row->language;
            }
            $data['languageList'] = $languageList;

			if (trim($this->request->getPost('submit')) == '') {
				echo view('admin/doctor/add_doctor',$data);
			} elseif (trim($this->request->getPost('submit')) == 'Add Mentor') {
				$validationRules = [
					'username' => 'required|trim|is_unique[doctor.username]',
					'pass' => 'required|trim',
					'doctor_name' => 'required|trim',
					'discription' => 'required|trim',
					'gender' => 'required|trim',
					'phone_number' => 'required|trim',
					'email' => 'required|trim|valid_email',
					'designation' => 'required|trim',
					'qualification' => 'required|trim',
					'years' => 'required|trim',
					'month' => 'required|trim',
					'state_id' => 'required|trim',
					'city_id' => 'required|trim'
				];
				
				if (!$this->validate($validationRules)) {
					return view('admin/doctor/add_doctor', $data);
				} else {

						
						
						$insert_data = [
						 	'username' => trim($this->request->getPost('username')),
						 	'pass' => md5(trim($this->request->getPost('pass'))),							
							'doctor_name' => trim($this->request->getPost('doctor_name')),
							'description' => trim($this->request->getPost('discription')),
							//'image' => $file_name,
							'gender' => trim($this->request->getPost('gender')),
							'phone_number' => trim($this->request->getPost('phone_number')),
							'email' => trim($this->request->getPost('email')),
							'designation' => trim($this->request->getPost('designation')),
							'qualification' => trim($this->request->getPost('qualification')),							
							'years' => trim($this->request->getPost('years')),							
							'month' => trim($this->request->getPost('month')),
							'state_id' => trim($this->request->getPost('state_id')),
							'city_id' => trim($this->request->getPost('city_id')),
							'status' => 'Active',
							'created_on' => date('Y-m-d'),
							'created_by' => session()->get('userid'),
							'updated_on' => date('Y-m-d'),
							'updated_by' => session()->get('userid')
						];

						$language_id = $this->request->getPost('language_id');

						// $user_data = [
						// 	'username' => trim($this->request->getPost('username')),
						// 	'pass' => md5(trim($this->request->getPost('pass'))),
						// 	'first_name' => trim($this->request->getPost('doctor_name')),
						// 	'email' => trim($this->request->getPost('email')),
						// 	'phone_number' => trim($this->request->getPost('phone_number')),
						// 	'user_role' => trim($this->request->getPost('designation')),
						// 	'user_status' => 'Active',
						// 	'state_id' => trim($this->request->getPost('state_id'))
						// ];
	                    

						$file = $this->request->getFile('doctorimage');
						if ($file->isValid()) {
							$srt = explode('.', $file->getName('doctorimage'));
							$file_name = uniqid() . "." . $srt[1];
							$filetype = "pdf";
							$targetpath = "./public/uploads/doctor";
							$file->move($targetpath, $file_name);

							$insert_data["image"] = $file_name;
						}
						
						
						$id = $this->doctor_model->addDoctor($insert_data, $language_id);
						
						if ($id > 0) {
							$arr_msg = array('suc_msg'=>'doctor added successfully!!!','msg-type'=>'success');
						}else{
							$arr_msg = array('suc_msg'=>'Failed to add doctor','msg-type'=>'danger');
						}
						$this->session->set($arr_msg);
						return redirect()->to('doctor/');

				}
			}
		}
	}

	public function edit($doctor_id) 
	{
		if ($doctor_id > 0) {
			$data = [
				'menutitle' => 'Mentor',
				'pagetitle' => 'Edit Mentor',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="'. base_url().'doctor">Manage Mentor</a><i class="fa fa-angle-right"></i></li> <li>Edit Mentor</a></li></ul>'
			];
			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}


			$condition = ['status'=>'Active'];
			$designationArray = $this->common_model->getAllData('designation',$condition);
            $allDesignation = array(''=>'Select Designation');
            foreach ($designationArray as $row) {
                $allDesignation[$row->designation] = $row->designation;
            }
            $data['allDesignation'] = $allDesignation;

            $condition = [];
			$stateArray = $this->common_model->getAllData('gst_state_codes',$condition);
            $stateList = array(''=>'Select State');
            foreach ($stateArray as $row) {
                $stateList[$row->state_id] = $row->state_name;
            }
            $data['stateList'] = $stateList;

			$condition = ['status'=>'Active'];
			$cityArray = $this->common_model->getAllData('city',$condition);
            $cityList = array(''=>'Select City');
            foreach ($cityArray as $row) {
                $cityList[$row->city_id] = $row->city_name;
            }
            $data['cityList'] = $cityList;

            $condition = ['status'=>'Active'];
			$languageArray = $this->common_model->getAllData('language',$condition);
            foreach ($languageArray as $row) {
                $languageList[$row->language_id] = $row->language;
            }
            $data['languageList'] = $languageList;
            
            $condition = ['doctor_id'=> $doctor_id];
			$doctorLanguage =  $this->common_model->getAllData('doctor_language', $condition);
			
			$data['doctorLanguage'] = $doctorLanguage;
			
			$doctorDetails = $this->doctor_model->getDoctor($doctor_id);

			$data['doctorDetails'] = $doctorDetails;
			// echo "<pre>";print_r($doctorLanguage);die();
			if (trim($this->request->getPost('submit')) == '') {
				return view('admin/doctor/edit_doctor', $data);

			} elseif (trim($this->request->getPost('submit')) == 'Edit Mentor') {
				$validationRules = [
					//'username' => 'required|trim',
					//'pass' => 'required|trim',
					'doctor_name' => 'required|trim',
					'discription' => 'required|trim',
					'gender' => 'required|trim',
					'phone_number' => 'required|trim',
					'email' => 'required|trim|valid_email',
					'designation' => 'required|trim',
					'qualification' => 'required|trim',
					'years' => 'required|trim',
					'month' => 'required|trim',
					'state_id' => 'required|trim',
					'city_id' => 'required|trim'
				];
				
				if (!$this->validate($validationRules)) {
					return view('admin/doctor/edit_doctor', $data);
				} else {	


						$update_data = [
							// 'doctor_name' => $this->request->getPost('doctor_name'),
							// 'image'=>$file_name,
							// 'description' => $this->request->getPost('discription'),
							// 'updated_on' => date('Y-m-d'),
							// 'updated_by' => session()->get('userid')
						
						   'doctor_name' => trim($this->request->getPost('doctor_name')),
						   'description' => trim($this->request->getPost('discription')),
						   'gender' => trim($this->request->getPost('gender')),
						   'phone_number' => trim($this->request->getPost('phone_number')),
						   'email' => trim($this->request->getPost('email')),
						   'designation' => trim($this->request->getPost('designation')),
						   'qualification' => trim($this->request->getPost('qualification')),
						   'years' => trim($this->request->getPost('years')),
						   'month' => trim($this->request->getPost('month')),
						   'state_id' => trim($this->request->getPost('state_id')),
						   'city_id' => trim($this->request->getPost('city_id')),
							'updated_on' => date('Y-m-d'),
							'updated_by' => session()->get('userid')
						];  						

						if(trim($this->request->getPost('pass'))!=""){
							$update_data['pass'] = md5(trim($this->request->getPost('pass')));
						}						

						$file = $this->request->getFile('doctorimage');
						if ($file->isValid() && !$file->hasMoved()) {
	
							$old_image=$doctorDetails[0]->image;
							if(@file_exists("./public/uploads/doctor/".$old_image)){
								@unlink("./public/uploads/doctor/".$old_image);
							}
							
							$srt = explode('.', $file->getName('doctorimage'));
							$file_name = uniqid() . "." . $srt[1];
							$filetype = "pdf";
							$targetpath = "./public/uploads/doctor";
							$file->move($targetpath, $file_name);
							$update_data['image'] = $file_name;
						}


						$postData = $this->request->getPost();

						$lid = $this->doctor_model->adddoctor_language($this->request->getPost('language_id'), $doctor_id);

						$condition = ['doctor_id' => $doctor_id];
						$id = $this->common_model->updateData('doctor',$update_data,$condition);
						if ($id > 0) {
							$arr_msg = array('suc_msg'=>'Doctor update successfully!!!','msg-type'=>'success');
						} else {
							$arr_msg = array('suc_msg'=>'Failed to update Doctor','msg-type'=>'danger');
						}
						$this->session->set($arr_msg);
						return redirect()->to('doctor/');
					
				}
			} else {
				return view('admin/index');
			}
		}
	}

	public function close() {
		$doctor_id  = $this->request->getPost('doctor_id');
		$condition = ['doctor_id' => $doctor_id];
		$doctorDetails = $this->common_model->getAllData('doctor', $condition);
		if ($doctor_id > 0) {
			$update_data = [
				'status' => 'Inactive',
				'updated_on' => date('Y-m-d'),
				'updated_by' => session()->get('userid')
			];
			
			$id = $this->common_model->updateData('doctor', $update_data, $condition);

			// if ($id > 0) {
			// 	$condition = ['doctor_id' => $doctor_id];
			// 	$user_data = ['user_status' => 'In-Active'];
			// 	$id = $this->common_model->updateData('users', $user_data, $condition);
			// }

			$old_image = $doctorDetails[0]->image;
			if(@file_exists("./public/uploads/doctor/".$old_image))
			{
				@unlink("./public/uploads/doctor/".$old_image);
			}
			echo $id;
		}else {
			echo 0;
		}
	}
}