<?php

namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Dompdf\Dompdf;

class Booking extends Controller
{
	protected $session;
	protected $validation;

	public function __construct()
	{
		$this->validation = Services::validation();
		$this->common_model = model('App\Models\Common_model', false);
		$this->booking_model = model('App\Models\Booking_model', false);
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

			if ($this->request->getGet('submit') == 'Filter') {
				// echo "<pre>";print_r($this->request->getPost());die();
				$startDate = $this->request->getGet('fromdate');
				$endDate = $this->request->getGet('todate');
				$plan_id = $this->request->getGet('plan_id');
				$city_id = $this->request->getGet('city_id');
				$language = $this->request->getGet('language');
				$search = $this->request->getGet('search');
			} else {
				//die();

				//$startDate = date("Y-m-d", strtotime($startDate . " -30 days"));;
				$startDate = date("Y-m-d", strtotime(date('Y-m-d') . " -30 days"));;
				$endDate = date("Y-m-d");
				$plan_id = '';
				$city_id = '';
				$language = '';
				$search = '';
			}

			$data = [
				'fromdate' => $startDate,
				'todate' => $endDate,
				'plan_id' => $plan_id,
				'city_id' => $city_id,
				'language' => $language,
				'search' => $search,
				'menutitle' 	=> 'Booking',
				'pagetitle' 	=> 'Booking',
				'breadcrumb' 	=> '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url('') . '">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Booking</li></ul>'
			];

			$condition = ['status' => 'Active'];
			$doctorList = $this->common_model->getAllData('doctor', $condition);
			$allDoctor = ['' => 'Select Mentor'];
			foreach ($doctorList as $row) {
				$allDoctor[$row->doctor_id] = $row->doctor_name;
			}
			$data['allDoctor'] = $allDoctor;

			$condition = ['status' => 'Active'];
			$planList = $this->common_model->getAllData('plan', $condition);
			$allPlan = ['' => 'Select Plan'];
			foreach ($planList as $row) {
				$allPlan[$row->plan_id] = $row->name;
			}
			$data['allPlan'] = $allPlan;

			$condition = ['status' => 'Active'];
			$cityList = $this->common_model->getAllData('city', $condition);
			$allCity = ['' => 'Select City'];
			foreach ($cityList as $row) {
				$allCity[$row->city_id] = $row->city_name;
			}
			$data['allCity'] = $allCity;

			$condition = ['status' => 'Active'];
			$languageList = $this->common_model->getAllData('language', $condition);
			$allLanguage = ['' => 'Select Language'];
			foreach ($languageList as $row) {
				$allLanguage[$row->language] = $row->language;
			}
			$data['allLanguage'] = $allLanguage;

			$condition = [
				'B.status !=' => 'Inactive',
				'B.date >=' => $startDate,
				'B.date <=' => $endDate
			];
			if ($plan_id > 0) {
				$condition['B.plan_id'] = $plan_id;
			}
			if ($city_id > 0) {
				$condition['B.city_id'] = $city_id;
			}
			if ($language != '') {
				$condition['B.language'] = $language;
			}
			$like = [
				'CP.client_name' => $search,
				'CP.client_mobile_number' => $search
			];
			$tableName = 'booking AS B';


			$contest_images = $this->booking_model->getBookingList($tableName, $condition, $like);
			if($this->request->getGet('download')!=""){
				$this->download($contest_images);
				die();
			}

			// Totals for summary panel
			$totalSubtotal = 0;
			$totalDiscount = 0;
			$totalAmount   = 0;
			foreach ($contest_images as $row) {
				$totalSubtotal += $row->subtotal     ?? 0;
				$totalDiscount += $row->discount     ?? 0;
				$totalAmount   += $row->total_amount ?? 0;
			}
			$data['totalSubtotal'] = $totalSubtotal;
			$data['totalDiscount'] = $totalDiscount;
			$data['totalAmount']   = $totalAmount;

			$pager=service('pager');
			$page=(int)(($this->request->getVar('page')!==null)?$this->request->getVar('page'):1)-1;
			$perPage =  PAGELIMIT;
			$total = count($contest_images);
			$pager->makeLinks($page+1, $perPage, $total);
			$offset = $page * $perPage;


			$bookingArray = $this->booking_model->getBookingList($tableName, $condition, $like,$perPage, $offset);
			$data['bookingList'] = $bookingArray;
			echo view('admin/booking/booking_list', $data);
		} else {
			return view('admin/index');
		}
	}

	public function plan_info()
	{
		if (!$this->session->has('userid')) {
			return $this->response->setJSON(['outcome' => 0, 'message' => 'Session expired']);
		}
		$plan_id = trim($this->request->getPost('plan_id'));
		$result = [];
		if ($plan_id > 0) {
			$condition = ['status' => 'Active', 'plan_id' => $plan_id];
			$planData = $this->common_model->getAllData('plan', $condition);
			if (!empty($planData)) {
				$result['plan_duration'] = trim($planData[0]->validaty);
				$result['price'] = trim($planData[0]->price);
				$result['outcome'] = 1;
			} else {
				$result['outcome'] = 0;
				$result['message'] = "Plan not found or inactive";
			}
		} else {
			$result['outcome'] = 0;
			$result['message'] = "Invalid Plan ID";
		}
		return $this->response->setJSON($result);
	}

	public function apply_coupon()
	{
		$coupon_id = trim($this->request->getPost('coupon_id'));
		$result = [];
		if ($coupon_id != '') {
			$condition = ['status' => 'Active', 'coupon_id' => $coupon_id];
			$couponData = $this->common_model->getAllData('coupon', $condition);
			if (!empty($couponData)) {
				$result['discount'] = trim($couponData[0]->discount);
				$result['outcome'] = 1;
			} else {
				$result['outcome'] = 0;
				$result['message'] = "Coupon not found";
			}
		} else {
			$result['outcome'] = 0;
			$result['message'] = "No coupon selected";
		}
		return $this->response->setJSON($result);
	}

	public function add()
	{
		if ($this->session->has('userid')) {
			$data = [
				'menutitle' 	=> 'Booking',
				'pagetitle'		=> 'Add Booking',
				'breadcrumb' 	=> '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"> </i> </li> <li> <a href="' . base_url() . 'booking/" >Manage Booking</a><i class="fa fa-angle-right"> </i> </li> <li>Add Booking</li></ul>'
			];

			$condition = ['status' => 'Active'];
			$clientlist = $this->common_model->getAllData('client_profile', $condition);
			$AllClients = ['' => 'Select client'];
			foreach ($clientlist as $client) {
				$AllClients[$client->client_id] = $client->client_name;
			}
			$data['AllClients'] = $AllClients;

			$planlist = $this->common_model->getAllData('plan', $condition);
			$Allplans = ['' => 'Select Plan'];
			foreach ($planlist as $plan) {
				$Allplans[$plan->plan_id] = $plan->name;
			}
			$data['Allplans'] = $Allplans;

			$citylist = $this->common_model->getAllData('city', $condition);
			$Allcitys = ['' => 'Select city'];
			foreach ($citylist as $city) {
				$Allcitys[$city->city_id] = $city->city_name;
			}
			$data['Allcitys'] = $Allcitys;

			$languagelist = $this->common_model->getAllData('language', $condition);
			$Alllanguages = ['' => 'Select Language'];
			foreach ($languagelist as $language) {
				$Alllanguages[$language->language_id] = $language->language;
			}
			$data['Alllanguages'] = $Alllanguages;

			$couponlist = $this->common_model->getAllData('coupon', $condition);
			$allCoupon = ['' => 'Select Coupon'];
			foreach ($couponlist as $coupon) {
				$allCoupon[$coupon->coupon_id] = $coupon->coupon_code;
			}
			$data['allCoupon'] = $allCoupon;

			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}
			if (trim($this->request->getPost('submit')) == '') {
				echo view('admin/booking/add_booking', $data);
			} elseif ($this->request->getPost('submit') == 'Add Booking') {

				$validationRules = [
					'client_id' 	=> 'required|trim',
					'language_id' 	=> 'required|trim',
					'city_id' 		=> 'required|trim',
					'plan_id'		=> 'required|trim',
					'plan_duration'	=> 'required|trim',
					'subtotal'		=> 'required|trim',
					'discount' 		=> 'required|trim',
					'total_amount' 	=> 'required|trim',
				];

				if (!$this->validate($validationRules)) {
					return view('admin/booking/add_booking', $data);
				} else {

					$insert_data = [
						'client_id' 	=> trim($this->request->getPost('client_id')),
						'language_id' 	=> trim($this->request->getPost('language_id')),
						'city_id' 		=> trim($this->request->getPost('city_id')),
						'plan_id' 		=> trim($this->request->getPost('plan_id')),
						'plan_duration'	=> trim($this->request->getPost('plan_duration')),
						'subtotal' 		=> trim($this->request->getPost('subtotal')),
						'coupon_id' 	=> trim($this->request->getPost('coupon_id')),
						'discount' 		=> trim($this->request->getPost('discount')),
						'total_amount' 	=> trim($this->request->getPost('total_amount')),
						'status' 		=> 'Processing',
						'date' 			=> date('Y-m-d'),
						'created_on' 	=> date('Y-m-d'),
						'created_by' 	=> session()->get('userid'),
						'updated_on' 	=> date('Y-m-d'),
						'updated_by' 	=> session()->get('userid')
					];

					$id = $this->common_model->addData('booking', $insert_data);
					if ($id > 0) {
						$arr_msg = array('suc_msg' => 'Booking added successfully!!!', 'msg-type' => 'success');
					} else {
						$arr_msg = array('suc_msg' => 'Failed to add booking', 'msg-type' => 'danger');
					}
					$this->session->set($arr_msg);
					return redirect()->to('booking/');
				}
			}
		}
	}


	public function close()
	{
		$booking_id  = $this->request->getPost('booking_id');
		if ($booking_id > 0) {
			$condition = ['booking_id' => $booking_id];
			$update_data = [
				'status' => 'Inactive',
				'updated_on' => date('Y-m-d'),
				'updated_by' => session()->get('userid')
			];

			$id = $this->common_model->updateData('booking', $update_data, $condition);
			echo $id;
		} else {
			echo 0;
		}
	}

	public function view($booking_id)
	{
		if ($booking_id > 0) {
			$data = [
				'menutitle'  	=> 'Booking',
				'pagetitle' 	=> 'View Booking',
				'breadcrumb' 	=> '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url('booking') . '">Manage Booking</a><i class="fa fa-angle-right"></i></li> <li>View Booking</li></ul>'
			];
			$condition = ['booking_id' => $booking_id];
			$tableName = 'booking AS B';
			$bookingArray = $this->booking_model->getBookingList($tableName, $condition, '');
			$data['bookingArray'] = $bookingArray;

			return view('admin/booking/view_booking', $data);
		} else {
			return view('admin/booking/');
		}
	}

	public function edit($booking_id)
	{
		if ($booking_id > 0) {
			$data = [
				'menutitle' => 'Booking',
				'pagetitle' => 'Edit Booking',
				'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'booking">Manage Booking</a><i class="fa fa-angle-right"></i></li>Edit Booking</a></li></ul>'
			];

			$condition = ['booking_id' => $booking_id];
			$tableName = 'booking AS B';
			$bookingArray = $this->booking_model->getBookingList($tableName, $condition, '');
			$data['bookingArray'] = $bookingArray;

			$condition = ['status' => 'Active'];
			$clientlist = $this->common_model->getAllData('client_profile', $condition);
			$AllClients = ['' => 'Select client'];
			foreach ($clientlist as $client) {
				$AllClients[$client->client_id] = $client->client_name;
			}
			$data['AllClients'] = $AllClients;

			$planlist = $this->common_model->getAllData('plan', $condition);
			$Allplans = ['' => 'Select Plan'];
			foreach ($planlist as $plan) {
				$Allplans[$plan->plan_id] = $plan->name;
			}
			$data['Allplans'] = $Allplans;

			$citylist = $this->common_model->getAllData('city', $condition);
			$Allcitys = ['' => 'Select city'];
			foreach ($citylist as $city) {
				$Allcitys[$city->city_id] = $city->city_name;
			}
			$data['Allcitys'] = $Allcitys;

			$languagelist = $this->common_model->getAllData('language', $condition);
			$Alllanguages = ['' => 'Select Language'];
			foreach ($languagelist as $language) {
				$Alllanguages[$language->language_id] = $language->language;
			}
			$data['Alllanguages'] = $Alllanguages;

			$couponlist = $this->common_model->getAllData('coupon', $condition);
			$allCoupon = ['' => 'Select Coupon'];
			foreach ($couponlist as $coupon) {
				$allCoupon[$coupon->coupon_id] = $coupon->coupon_code;
			}
			$data['allCoupon'] = $allCoupon;

			if ($this->session->get('err_msg')) {
				$data['err_msg'] = $this->session->get('err_msg');
				$this->session->remove('err_msg');
			}
			if (trim($this->request->getPost('submit')) == '') {
				return view('admin/booking/edit_booking', $data);
			} elseif (trim($this->request->getPost('submit')) == 'Edit Booking') {

				$validationRules = [
					'client_id' 	=> 'required|trim',
					'language_id' 	=> 'required|trim',
					'city_id' 		=> 'required|trim',
					'plan_id'		=> 'required|trim',
					'plan_duration'	=> 'required|trim',
					'subtotal'		=> 'required|trim',
					'discount' 		=> 'required|trim',
					'total_amount' 	=> 'required|trim',
					'status' 		=> 'required|trim',
				];

				if (!$this->validate($validationRules)) {
					return view('admin/booking/edit_booking', $data);
				} else {
					$update_data = [
						'client_id' 	=> trim($this->request->getPost('client_id')),
						'language_id' 	=> trim($this->request->getPost('language_id')),
						'city_id' 		=> trim($this->request->getPost('city_id')),
						'plan_id' 		=> trim($this->request->getPost('plan_id')),
						'plan_duration'	=> trim($this->request->getPost('plan_duration')),
						'subtotal' 		=> trim($this->request->getPost('subtotal')),
						'coupon_id' 	=> trim($this->request->getPost('coupon_id')),
						'discount' 		=> trim($this->request->getPost('discount')),
						'total_amount' 	=> trim($this->request->getPost('total_amount')),
						'status' 		=> trim($this->request->getPost('status')),
						'updated_on' 	=> date('Y-m-d'),
						'updated_by' 	=> session()->get('userid')
					];

					$condition = ['booking_id' => $booking_id];
					$id = $this->common_model->updateData('booking', $update_data, $condition);
					if ($id > 0) {
						$arr_msg = array('suc_msg' => 'Booking update successfully!!!', 'msg-type' => 'success');
					} else {
						$arr_msg = array('suc_msg' => 'Failed to update booking', 'msg-type' => 'danger');
					}
					$this->session->set($arr_msg);
					return redirect()->to('booking/');
				}
			} else {
				return view('admin/index');
			}
		}
	}

	public function assign_doctor()
	{

		// echo "<pre>";print_r($this->request->getPost());die();
		$booking_id = trim($this->request->getPost('booking_id'));
		$doctor_id = trim($this->request->getPost('doctor_id'));
		if ($booking_id > 0 && $doctor_id > 0) {

			$condition = ['booking_id' => $booking_id];
			$update_data['doctor_id'] = $doctor_id;




			$id = $this->common_model->updateData('booking', $update_data, $condition);
			if ($id > 0) {				
				$id = $this->common_model->updateData('appointment', $update_data, $condition);
				echo 1;
			// 	$nTitle = "Mentor Assigned";
			// 	$nMessage = "We're pleased to inform you that a new mentor has been assigned to you. Feel free to reach out for any assistance or guidance.";

				
			// 	$db = \Config\Database::connect();
			// 	$query   = $db->query("
			// 	SELECT 
			// 		booking.booking_id,booking.booking_id,booking.plan_expiry_date,
			// 		plan.name,
			// 		client_login.*			
			// 	FROM 
			// 		booking 
			// 	INNER JOIN 
			// 		plan ON booking.plan_id=plan.plan_id 
			// 	INNER JOIN 
			// 		client_login ON booking.client_id=client_login.client_id 			
			// 	WHERE 
			// 		client_login.fcm_token!='' AND 
			// 		booking.booking_id='" . $booking_id . "' AND 
			// 		client_login.client_status='Active'
			// 	");
			// 	$results = $query->getRowArray();
			// 	if ($results) {

			// 		$notificationArray[] = array("client_id"=>$results["client_id"],"notification_title"=>$nTitle,"description"=>$nMessage,"type_id"=>4);
			// 		$fcmArray[] = $results["fcm_token"];
	
			// 		//print '<pre>';
			// 		//print_r($fcmArray);
			// 		//sendNotification($nTitle, $nMessage, $fcmArray);
		
			// 		if (count($notificationArray) > 0) {

			// 			die("ddd");
			// 			$builder = $db->table("notification");
			// 			$builder->insertBatch($notificationArray); 
			// 		}
					
			// 	}

			// 	die('ddd');
			// 	echo 1;
				
			 } else {
			 	echo 0;
			 }
		} else {
			echo 0;
		}
	}

	public function download($result){	
		require(APPPATH.'ThirdParty/phpspreadsheet/vendor/autoload.php');   
		$fileName = 'BookingData-'.time().'.xlsx'; 
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		
		$sheet->setCellValue('A1', 'Client');
		$sheet->setCellValue('B1', 'Plan');
		$sheet->setCellValue('C1', 'Start Date');
		$sheet->setCellValue('D1', 'End Date');
		$sheet->setCellValue('E1', 'Duration');
		$sheet->setCellValue('F1', 'Status');
		$sheet->setCellValue('G1', 'Amount');
		$sheet->setCellValue('H1', 'Mentor');
		
		$rowCount = 2;
		foreach ($result as $row){
			$sheet->setCellValue('A' . $rowCount, $row->client_name);
			$sheet->setCellValue('B' . $rowCount, $row->name . '-'.$row->category);
			$sheet->setCellValue('C' . $rowCount, $row->date);
			$sheet->setCellValue('D' . $rowCount, (($row->plan_expiry_date!="0000-00-00") ? $row->plan_expiry_date:''));
			$sheet->setCellValue('E' . $rowCount, $row->plan_duration);
			$sheet->setCellValue('F' . $rowCount, $row->status);
			$sheet->setCellValue('G' . $rowCount, $row->total_amount);
			$sheet->setCellValue('H' . $rowCount, $row->doctor_name);
			$rowCount++;
		}
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		
		/* To Save File */
		//$savePath = FCPATH."public\\".$fileName;    
		//$writer->save($savePath); 
		//die();
		
		/* Direact Doenload Without Save */
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 
		header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
		header('Cache-Control: max-age=0'); 
		$writer->save('php://output');
		exit;
	}

	public function viewpdf($booking_id)
	{
		if ($this->session->has('userid')) {
			$html = '';
			$condition = ['booking_id' => $booking_id];
			$tableName = 'booking AS B';
			$bookingArray = $this->booking_model->getBookingList($tableName, $condition, '');

			//print_r($bookingArray);
			//die();
			
			$data['booking'] = $bookingArray;
			$data['is_pdf_gen'] = true;
			
			$dompdf_path = APPPATH . 'ThirdParty/dompdf/vendor/autoload.php';
			
			if (file_exists($dompdf_path)) {
				require($dompdf_path);   
				$dompdf = new \Dompdf\Dompdf();
				$options = $dompdf->getOptions(); 
				$options->set(array('isRemoteEnabled' => true));
				$dompdf->setOptions($options);
			
				$html = view('admin/booking/invoice', $data);
				$dompdf->loadHtml($html);
				$dompdf->setPaper('A4', 'portrait');
				$dompdf->render();
		
				$fileName = "Invoice-".sprintf("%08d", $booking_id).".pdf";
				$dompdf->stream($fileName, array("Attachment" => false));
				exit(0);
			} else {
				// Fallback to HTML view if Dompdf is missing
				$data['print_mode'] = true;
				return view('admin/booking/invoice', $data);
			}
		} else {
			return view('admin/index');
		}
	}
}
