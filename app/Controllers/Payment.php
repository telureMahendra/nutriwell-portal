<?php

namespace App\Controllers;

use Config\Services;
use CodeIgniter\Controller;

class Payment extends Controller
{

	public function __construct()
	{
		$this->common_model = model('App\Models\Common_model', false);
	}
	public function index()
	{
		return $this->response->setJSON(array('status' => false, 'msg' => 'Invalid Action'));
		die();
	}
	public function Success()
	{
		$response = $this->request->getPost();
		$encryptedText  = $response["encResp"];
		$decryptText = decrypt($encryptedText, _working_key);

		$exArray  = explode("&", $decryptText);


		$order_id =  str_replace("order_id=", "", $exArray[0]);
		$tracking_id =  str_replace("tracking_id=", "", $exArray[1]);
		$order_status =  str_replace("order_status=", "", $exArray[3]);
		$orderAmt = str_replace("amount=", "", $exArray[10]);

		$data["orderNo"] = $order_id;
		$data["orderStatus"] = $order_status;
		$data["tracking_id"] = $tracking_id;
		$data["orderAmt"] = $orderAmt;

		$condition = ['order_id=' => $order_id];
		$update_data = array("tracking_id" => $tracking_id, "response" => serialize($response));
		$this->common_model->updateData("booking", $update_data, $condition);

		return view('success', $data);
	}
	public function Cancel()
	{
		$response = $this->request->getPost();
		return view('cancel');
	}
	public function CheckOrder($id)
	{
		$response = $this->request->getPost();

		//$response = $this->request->getPost();
		// 	    $condition = ['order_id=' => $order_id];
		// 	    $update_data = array("response"=>serialize($response));
		// 		$this->common_model->updateData("booking", $update_data, $condition);

		//return view('cancel');

		print '<pre>';

		$merchant_json_data =
			array(
				'order_no' => $id,
			);



		print_r($merchant_json_data);




		$merchant_data = json_encode($merchant_json_data);
		echo 'JSON Request';
		print_r($merchant_data);

		echo '<br><br><br><br>';


		$encrypted_data = encrypt($merchant_data, _working_key);
		$final_data = 'enc_request=' . $encrypted_data . '&access_code=AVNC96KH16AK95CNKA&request_type=JSON&response_type=JSON&command=orderStatusTracker&version=1.2';

		echo 'Encrypted Request';
		print_r($final_data);

		echo '<br><br><br><br>';


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://apitest.ccavenue.com/apis/servlet/DoWebTrans");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/json'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $final_data);
		// Get server response ...
		$result = curl_exec($ch);
		curl_close($ch);

		print_r($result);

		echo '<br><br><br><br>';

		$information = explode('&', $result);
		print_r($information);


		echo '<br><br><br><br>';
		$a = str_replace("enc_response=", "", $information[1]);


		echo '<br><br><br><br>enc_response:- ';
		echo $a;


		echo '<br><br><br><br>Decrypted Response is:-';
		$s = decrypt(trim($a), _working_key);


		print '<pre>';
		print_r($s);

		// echo '<br><br><br><br>';
		// $a = str_replace("enc_response=","",$d)
		// print_r($a);


		// $status = decrypt(trim($info_value[1]), _working_key);
		die();



		$status = '';
		$information = explode('&', $result);

		$dataSize = sizeof($information);
		for ($i = 0; $i < $dataSize; $i++) {
			$info_value = explode('=', $information[$i]);
			if ($info_value[0] == 'enc_response') {
				$status = decrypt(trim($info_value[1]), _working_key);
			}
		}

		echo 'Status revert is: ' . $status . '<pre>';
		$obj = json_decode($status);
		print_r($obj);
	}
}
