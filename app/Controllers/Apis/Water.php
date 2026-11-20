<?php

namespace App\Controllers\Apis;
use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Water extends ResourceController
{
    protected $Water_model;
    protected $Client_login_model;
    use ResponseTrait;

    public function __construct()
    {
        $this->water_model = model('App\Models\Water_model', false);
        $this->client_login_model = model('App\Models\Client_login_model', false);
    }
    public function headers()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Max-Age', '1000');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->response->setHeader('Content-Type', 'application/json');
    }
    // get water data on dashboard
    public function get_water(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $date = $json_data->date;
        $tocken = $json_data->tocken;
        $waterData = [];
        if (!empty($date) && !empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $waterData = $this->water_model->getWater($date, $check_login[0]->client_id);
	            
                if (!empty($waterData)) {
                    $waterData[0]->target = "16";
                    $response = ['status' => true, 'msg' => 'Water data get successfully', 'data' => $waterData];
                }else{
                    $waterData[]['target'] = "16";

                    $response = ['status' => false, 'msg' => 'Water data not Found', 'data' => $waterData];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found'];
        }
        return $this->response->setJSON($response);
    }

    // add and update water on dashboard
    public function update_water(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $date = $json_data->date;
        $tocken = $json_data->tocken;
        $type = $json_data->type;
        $updatewater = [];
        if (!empty($date) && !empty($tocken)  && !empty($type)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                $waterData = $this->water_model->getWater($date, $check_login[0]->client_id);
                if (empty($waterData) == true && $type == 'plus') {
                	$insertData['client_id'] = $check_login[0]->client_id;
            		$insertData['quantity'] = 1;
            		$insertData['date'] = $date;
            		$insertData['created_on'] = $date;
            		$insertData['created_by'] = $check_login[0]->client_id;
            		$insertData['updated_on'] = $date;
            		$insertData['updated_by'] = $check_login[0]->client_id;

	                $insert_id = $this->water_model->addWater($insertData);

                    $data['updatewater'] = $insert_id;
	                if (!empty($insert_id)) {
	                    $response = ['status' => true, 'msg' => 'Water quantity added successfully', 'data' => $data];
	                }else{
	                    $response = ['status' => false, 'msg' => 'Failed to add water quantity', 'data' => $data];
	                }
                }elseif (empty($waterData) == false) {

                	if ($waterData[0]->quantity >= 0 && $type == 'plus' ) {
	            		$updateData['quantity'] = ($waterData[0]->quantity + 1);
	            		$updateData['updated_on'] = $date;
	            		$updateData['updated_by'] = $check_login[0]->client_id;
	            	}elseif ($waterData[0]->quantity > 0 && $type == 'minus') {
	            		$updateData['quantity'] = ($waterData[0]->quantity - 1);
	            		$updateData['updated_on'] = $date;
	            		$updateData['updated_by'] = $check_login[0]->client_id;
	            	}
	            	$updatewater = $this->water_model->updateWater($date, $check_login[0]->client_id, $updateData);
	            	$data['updatewater'] = $updatewater;
	                if (!empty($updatewater)) {
	                    $response = ['status' => true, 'msg' => 'Water quantity update successfully', 'data' => $data];
	                }else{
	                    $response = ['status' => false, 'msg' => 'Failed to update water quantity', 'data' => $data];
	                }
                }else{
                	$response = ['status' => false, 'msg' => 'Failed to add water', 'data' => $data];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }

    public function get_week_water(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $date = (isset($json_data->date)?$json_data->date:date('Y-m-d'));
        $waterData = [];
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                
                //$startOfWeek = date('Y-m-d H:i:s', strtotime('last sunday', strtotime($date)));
                //$startDate = date('Y-m-d H:i:s', strtotime('last sunday', strtotime($date)));
                //$currentDate = date('Y-m-d', strtotime('+1 days'));

                $currentDate = date('Y-m-d', strtotime($date.'+1 days'));
                $startOfWeek = date('Y-m-d', strtotime($currentDate . ' - 7 days'));
                $startDate = date('Y-m-d', strtotime($currentDate . ' - 7 days'));    
                                            
                for ($i = 1; $i <= 7; $i++) {
                    $dataArray = $this->water_model->getWeekWater($startOfWeek, $check_login[0]->client_id);
                    if (!empty($dataArray)) {
                         $waterData[] = $dataArray[0];
                    }else{
                        $waterData[] = array('water_id'=>"0",'client_id'=>$check_login[0]->client_id,'quantity'=>"0",'date'=>date('Y-m-d H:i:s', strtotime($startOfWeek)),'created_on'=>date('Y-m-d H:i:s', strtotime($startOfWeek)),'created_by'=>$check_login[0]->client_id,'updated_on'=>date('Y-m-d H:i:s', strtotime($startOfWeek)),'updated_by'=>$check_login[0]->client_id);

                    }
                    $startOfWeek = date('Y-m-d H:i:s', strtotime("+$i days", strtotime($startDate)));
                }
                $data['waterData'] = $waterData;
                $data['target'] = "16";

                if (!empty($waterData)) {
                    
                    $response = ['status' => true, 'msg' => 'Current week water data get successfully', 'data' => $data];
                }else{
                    $response = ['status' => false, 'msg' => 'Water data not Found', 'data' => $data];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }


}