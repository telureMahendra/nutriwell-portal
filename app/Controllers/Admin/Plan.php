<?php
namespace App\Controllers\Admin;
use Config\Services;
use CodeIgniter\Controller;

class Plan extends Controller
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
				'menutitle' => 'Plan',
				'pagetitle' => 'Plan',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Plan</li></ul>'
			];
			$condition = ['status' => 'Active'];
			$planArray = $this->common_model->getAllData('plan', $condition);
			$data['planList'] = $planArray;
			// echo "<pre>";print_r($data);die();
			echo view('admin/plan/plan_list', $data);
		} else {
			return view('admin/index');
		}
	}

	public function add()
	{
		if ($this->session->has('userid')) {
			$data = [
				'menutitle' => 'Plan',
				'pagetitle' => 'Add Plan',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"> </i> </li> <li> <a href="' . base_url() . 'plan/" >Manage Plan</a><i class="fa fa-angle-right"> </i> </li> <li>Add Plan</li></ul>'
			];
			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}
			$month = ['' => 'Select Validaty', '1 months' => '1 months', '2 months' => '2 months', '3 months' => '3 months', '4 months' => '4 months', '5 months' => '5 months', '6 months' => '6 months', '7 months' => '7 months', '8 months' => '8 months', '9 months' => '9 months', '10 months' => '10 months', '11 months' => '11 months', '12 months' => '12 months'];
			$data['validaty'] = $month;
			$category = ['' => 'Select Category', 'Premium' => 'Premium', 'Premium Lite' => 'Premium Lite', 'Premium Plus' => 'Premium Plus'];
			$data['category'] = $category;
			if (trim($this->request->getPost('submit')) == '') {
				echo view('admin/plan/add_plan', $data);
			} elseif ($this->request->getPost('submit') == 'Add Plan') {
				$validationRules = [
					'planname' => 'required|trim',
					'caption' => 'required|trim',
					'discription' => 'required|trim',
					'price' => 'required|trim',
					'validaty' => 'required|trim',
					'category' => 'required|trim',
				];

				if (!$this->validate($validationRules)) {
					return view('admin/plan/add_plan', $data);
				} else {

					$background_img = $this->request->getFile('background_img');
					$header_img = $this->request->getFile('header_img');
					$image = $this->request->getFiles('feature_img');
					$feature_imgs = $image['feature_img'];

					if ($background_img->isValid() && $header_img->isValid()) {
						$background_srt = explode('.', $background_img->getName('background_img'));
						$background_name = uniqid() . "." . $background_srt[1];
						$filetype1 = "pdf";
						$background_targetpath = "./public/uploads/plan/background";
						$background_img->move($background_targetpath, $background_name);

						$header_srt = explode('.', $header_img->getName('$header_img'));
						$header_name = uniqid() . "." . $header_srt[1];
						$filetype2 = "pdf";
						$header_targetpath = "./public/uploads/plan/header";
						$header_img->move($header_targetpath, $header_name);

						foreach ($feature_imgs as $feature_img) {

							if ($feature_img->isValid()) {
								$feature_srt = explode('.', $feature_img->getName('feature_img'));
								$feature_name = uniqid() . "." . $feature_srt[1];
								$filetype3 = "pdf";
								$feature_targetpath = "./public/uploads/plan/feature";
								$feature_img->move($feature_targetpath, $feature_name);
							} else {
								return redirect()->to('plan/add');
							}
						}

						$insert_data = [
							'name' => $this->request->getPost('planname'),
							'caption' => $this->request->getPost('caption'),
							'description' => $this->request->getPost('discription'),
							'price' => $this->request->getPost('price'),
							'validaty' => $this->request->getPost('validaty'),
							'category' => $this->request->getPost('category'),
							'background_img' => $background_name,
							'header_img' => $header_name,
							'status' => 'Active',
							'created_on' => date('Y-m-d'),
							'created_by' => session()->get('userid'),
							'updated_on' => date('Y-m-d'),
							'updated_by' => session()->get('userid')
						];

						$insert_feature = [

							'feature_name' => $this->request->getPost('feature_name'),
							'feature_caption' => $this->request->getPost('feature_caption'),
							'feature_image' => $feature_imgs,
							'status' => 'Active',
							'created_on' => date('Y-m-d'),
							'created_by' => session()->get('userid'),
							'updated_on' => date('Y-m-d'),
							'updated_by' => session()->get('userid')
						];

						//echo"<pre>";print_r($insert_data);die();
						// echo"<pre>";print_r($insert_feature);die();
						$id = $this->common_model->addPlan($insert_data, $insert_feature);
						//echo $id;
						if ($id > 0) {
							$arr_msg = array('suc_msg' => 'plan added successfully!!!', 'msg-type' => 'success');
						} else {
							$arr_msg = array('suc_msg' => 'Failed to add plan', 'msg-type' => 'danger');
						}
						$this->session->set($arr_msg);
						return redirect()->to('plan/');
					} else {
						return redirect()->to('plan/add');
					}
				}
			}

		}
	}



	public function add_new_row()
	{

		$rownums = trim($this->request->getPost('rownums')) + 2;

		$rowHTml = '<tr class="gradeX short odd" id="tr_' . $rownums . '">
		<td>' . form_input("feature_name[]", "", 'class="form-control" 
			data_row="' . $rownums . '" id="feature_name_' . $rownums . '" placeholder="Feature Name" required="required"') . '</td>

		<td width="15%">

		<input type="file" accept="image/jpg,image/png,image/jpeg" name="feature_img[]" id="feature_img_' . $rownums . '" class="form-control" onchange="valimage3()" required> </td>

		<td width="15%">' . form_textarea('feature_caption[]', "", 'class="form-control" id="feature_caption_' . $rownums . '" placeholder="Caption" required="required" rows="3"') . '</td>

		<td width="5%">
		<a class="btn default btn-xs red Delete" href="javascript:void(0)" alt="Delete" title="Delete" data_row="' . $rownums . '"><i class="fa fa-trash"></i></a>
		</td>
	</tr>
	<script type="text/javascript">
	function valimage3() {
		var fileInput3 = document.getElementById("feature_' . $rownums . '");
		var filePath3 = fileInput3.value;
		var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

		if (!allowedExtensions.exec(filePath3)) {
			alert("Invalid file type. Please upload an image with extension .jpg, .jpeg, or .png.");
			fileInput3.value = "";
			return false;
		}
	}

	</script>';



		$rowHTml .= '<script>

	const deleteButton = document.querySelector(".Delete[data_row=\'' . $rownums . '\']");
	if(deleteButton) {
		deleteButton.addEventListener("click", function () {
			var data_row = this.getAttribute("data_row");
			var tr = document.querySelector("#tr_" + data_row);
			tr.remove();
			});
		}

		</script>';


		echo $rowHTml;
	}

	public function close()
	{
		$plan_id = $this->request->getPost('plan_id');
		$condition = ['plan_id' => $plan_id];
		$planDetails = $this->common_model->getAllData('plan', $condition);
		if ($plan_id > 0) {
			$update_data = [
				'status' => 'Inactive',
				'updated_on' => date('Y-m-d'),
				'updated_by' => session()->get('userid')
			];

			$id = $this->common_model->updateData('plan', $update_data, $condition);
			$background_image = $planDetails[0]->background_img;
			$header_image = $planDetails[0]->header_img;
			//echo $old_image;
			if (file_exists("./public/uploads/plan/background/" . $background_image)) {
				unlink("./public/uploads/plan/background/" . $background_image);
			}

			if (file_exists("./public/uploads/plan/header/" . $header_image)) {
				unlink("./public/uploads/plan/header/" . $header_image);
			}
			echo $id;
		} else {
			echo 0;
		}
	}


	public function edit($plan_id)
	{
		if ($plan_id > 0) {
			$data = [
				'menutitle' => 'Plan',
				'pagetitle' => 'Edit Plan',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'plan">Manage Plan</a><i class="fa fa-angle-right"></i></li> <li>Edit Plan</li></ul>'
			];
			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}
			$condition = ['plan_id' => $plan_id];
			$planDetails = $this->common_model->getAllData('plan', $condition);
			$featureDetails = $this->common_model->getAllData('plan_features', $condition);
			$data['planDetails'] = $planDetails;
			$data['featureDetails'] = $featureDetails;

			// echo"<pre>"; print_r($featureDetails);die();
			//echo $goalDetails[0]->image;
			$month = ['' => 'Select Validaty', '1 months' => '1 months', '2 months' => '2 months', '3 months' => '3 months', '4 months' => '4 months', '5 months' => '5 months', '6 months' => '6 months', '7 months' => '7 months', '8 months' => '8 months', '9 months' => '9 months', '10 months' => '10 months', '11 months' => '11 months', '12 months' => '12 months'];
			$data['validaty'] = $month;
			$category = ['' => 'Select Category', 'Premium' => 'Premium', 'Premium Lite' => 'Premium Lite', 'Premium Plus' => 'Premium Plus'];
			$data['category'] = $category;

			if (trim($this->request->getPost('submit')) == '') {
				return view('admin/plan/edit_plan', $data);
			} elseif (trim($this->request->getPost('submit')) == 'Edit Plan') {
				// echo "<pre>";print_r($this->request->getFiles());die();

				// echo '<pre>';print_r($this->request->getFiles());

				//echo '<pre>';print_r($this->request->getPost());
				//print_r($_FILES);die();
				$validationRules = [
					'planname' => 'required|trim',
					'caption' => 'required|trim',
					'discription' => 'required|trim',
					'price' => 'required|trim',
					'validaty' => 'required|trim',
					'category' => 'required|trim',

				];

				if (!$this->validate($validationRules)) {
					return view('admin/plan/edit_plan', $data);
				} else {


					//$feature_name=$this->request->getPost('feature_name');
					//echo"<pre>";print_r($feature_name);die();
					$background_img_info = $this->request->getFiles()['background_img'];
					$header_img_info = $this->request->getFiles()['header_img'];
					$feature_img_info = $this->request->getFiles()['feature_img'];
					$header_img = $header_img_info->getName();
					$background_img = $background_img_info->getName();
					//echo"<pre>";print_r($feature_img_info);die();
					$update_Plan_data = [];
					$update_feature = [];
					if (!empty($header_img)) {
						$old_header_image = $planDetails[0]->header_img;

						if (file_exists("./public/uploads/plan/header/" . $old_header_image)) {
							unlink("./public/uploads/plan/header/" . $old_header_image);
						}

						$header_srt = explode('.', $header_img);
						$header_name = uniqid() . "." . $header_srt[1];
						$header_targetpath = "./public/uploads/plan/header";

						$header_img_info->move($header_targetpath, $header_name);

						$update_Plan_data['header_img'] = $header_name;
					}

					if (!empty($background_img)) {
						$old_background_image = $planDetails[0]->background_img;

						if (file_exists("./public/uploads/plan/background/" . $old_background_image)) {
							unlink("./public/uploads/plan/background/" . $old_background_image);
						}

						$background_srt = explode('.', $background_img);
						$background_name = uniqid() . "." . $background_srt[1];
						$background_targetpath = "./public/uploads/plan/background";

						$background_img_info->move($background_targetpath, $background_name);

						$update_Plan_data['background_img'] = $background_name;
					}



					if (!empty($feature_img_info)) {
						foreach ($feature_img_info as $key => $feature_imgs) {
							$feature_img = $feature_imgs->getName();
							$old_image = $this->request->getPost("old_img-" . $key);
							//echo $feature_img." ->" .$key."<br>";
							//echo '<pre>';print_r($feature_img);
							//echo '<pre>Old';print_r($old_image);die();
							if (!empty($feature_img)) {
								if (!empty($old_image)) {
									// echo ("./public/uploads/plan/feature/" . $old_image); die();
									if (file_exists("./public/uploads/plan/feature/" . $old_image)) {

										$id = unlink("./public/uploads/plan/feature/" . $old_image);

									}

								}
								$feature_srt = explode('.', $feature_img);
								$feature_name = uniqid() . "." . $feature_srt[1];
								$filetype3 = "pdf";
								$feature_targetpath = "./public/uploads/plan/feature";
								$feature_imgs->move($feature_targetpath, $feature_name);
								$update_feature['feature_image'][$key] = $feature_name;


							} else {
								$update_feature['feature_image'][$key] = $old_image;
							}
						}
						//echo "<pre>feature"; print_r($update_feature);die();
					}


					//echo "<pre>"; print_r($update_feature);die();
					$update_Plan_data['name'] = $this->request->getPost('planname');
					$update_Plan_data['caption'] = $this->request->getPost('caption');
					$update_Plan_data['description'] = $this->request->getPost('discription');
					$update_Plan_data['price'] = $this->request->getPost('price');
					$update_Plan_data['validaty'] = $this->request->getPost('validaty');
					$update_Plan_data['category'] = $this->request->getPost('category');
					$update_Plan_data['updated_on'] = date('Y-m-d');
					$update_Plan_data['updated_by'] = session()->get('userid');


					$update_feature['feature_name'] = $this->request->getPost('feature_name');
					$update_feature['pf_id'] = $this->request->getPost('pf_id');
					$update_feature['feature_caption'] = $this->request->getPost('feature_caption');
					$update_feature['status'] = 'Active';
					$update_feature['created_on'] = date('Y-m-d');
					$update_feature['created_by'] = session()->get('userid');
					$update_feature['updated_on'] = date('Y-m-d');
					$update_feature['updated_by'] = session()->get('userid');
					//echo"<pre>";print_r($update_feature);die();
					$plan_id = $plan_id;
					//echo"<pre>";print_r($update_Plan_data);
					//echo"<pre>";print_r($update_feature);die();
					$id = $this->common_model->updatePlanData($update_Plan_data, $update_feature, $plan_id);
					if ($id > 0) {
						$arr_msg = array('suc_msg' => 'plan update successfully!!!', 'msg-type' => 'success');
					} else {
						$arr_msg = array('suc_msg' => 'Failed to update plan', 'msg-type' => 'danger');
					}
					$this->session->set($arr_msg);
					return redirect()->to('plan/');
				}
			}

		}
	}

	public function view($plan_id)
	{
		if ($plan_id > 0) {
			$data = [
				'menutitle' => 'Plan',
				'pagetitle' => 'View Plan',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'plan">Manage Plan</a><i class="fa fa-angle-right"></i></li> <li>View Plan</li></ul>'
			];
			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}
			$month = ['' => 'Select Validaty', '1 months' => '1 months', '2 months' => '2 months', '3 months' => '3 months', '4 months' => '4 months', '5 months' => '5 months', '6 months' => '6 months', '7 months' => '7 months', '8 months' => '8 months', '9 months' => '9 months', '10 months' => '10 months', '11 months' => '11 months', '12 months' => '12 months'];
			$data['validaty'] = $month;
			$category = ['' => 'Select Category', 'Premium' => 'Premium', 'Premium Lite' => 'Premium Lite', 'Premium Plus' => 'Premium Plus'];
			$data['category'] = $category;
			$condition = ['plan_id' => $plan_id];
			$planDetails = $this->common_model->getAllData('plan', $condition);
			$featureDetails = $this->common_model->getAllData('plan_features', $condition);
			$data['planDetails'] = $planDetails;
			$data['featureDetails'] = $featureDetails;
			//echo"<pre>";print_r($planDetails);die();
			return view('admin/plan/view_plan', $data);

		}
	}//fun

}