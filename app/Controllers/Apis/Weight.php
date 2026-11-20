<?php

namespace App\Controllers\Apis;
use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Weight extends ResourceController
{
    protected $Weight_model;
    protected $Client_login_model;
    protected $Profile_info_model;
    use ResponseTrait;

    public function __construct()
    {
        $this->weight_model = model('App\Models\Weight_model', false);
        $this->profile_info_model = model('App\Models\Profile_info_model', false);
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
    
    //get weight of client
    public function get_weight(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $date = $json_data->date;
        $tocken = $json_data->tocken;
        $data = [];
        if (!empty($date) && !empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $tData = $this->profile_info_model->getWeight($check_login[0]->client_id);
                $target_weight = $tData[0]->target_weight;
                $weightData = $this->weight_model->getWeight($date, $check_login[0]->client_id);                          
                if (!empty($weightData)) {              
                    //$weightData[0]->target = "75";
                    //$weightData[0]->target_weight = $target_weight;
                    $response = ['status' => true, 'msg' => 'Weight data get successfully', 'data' => $weightData,"target_weight"=>$target_weight];
                }else{
                    if (!empty($weightData)) {
                        //$weightData[0]->target = "75";
                        //$weightData[0]->target_weight = $target_weight;
                        $response = ['status' => true, 'msg' => 'Weight data get successfully', 'data' => $weightData,"target_weight"=>$target_weight];
                    }else{
                        //$weightData[]['target'] = "75";
                        //$weightData[0]->target_weight = $target_weight;
                        $response = ['status' => false, 'msg' => 'Client Weight data not Found', 'data' => $weightData,"target_weight"=>$target_weight];
                    }
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data'];
        }
        return $this->response->setJSON($response);
    }
    
    // add new client weight daily or update it
    public function update_weight(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $weight = $json_data->weight;
        $target_weight = (isset($json_data->target_weight) ? $json_data->target_weight:0);
        $tocken = $json_data->tocken;
        $date = $json_data->date;

        if (!empty(trim($tocken)) && !empty(trim($weight))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $weightData = $this->weight_model->getWeight($date, $check_login[0]->client_id);
                if (empty($weightData)) {
                    $insertData['client_id'] = $check_login[0]->client_id;
                    $insertData['weight'] = $weight;
                    $insertData['target_weight'] = $target_weight;
                    $insertData['created_on'] = $date;
                    $insertData['created_by'] = $check_login[0]->client_id;
                    $insertData['updated_on'] = date('Y-m-d H:i:s');
                    $insertData['updated_by'] = $check_login[0]->client_id;
                    $weight_id = $this->weight_model->addWeight($insertData);

                    $update_data['weight'] = $weight;
                    $update_data['target_weight'] = $json_data->target_weight;                    
                    $update_id = $this->profile_info_model->updateProfile($update_data, $check_login[0]->client_id);

                    if ($weight_id > 0 && $update_id > 0) {
                        $response = ['status' => true, 'msg' => 'Weight data add successfully!'];
                    }else{
                        $response = ['status' => false, 'msg' => 'Failed to add weight data'];
                    }
                }else{
                    $updatesData['weight'] = $weight;
                    $updatesData['target_weight'] = $target_weight;
                    $updatesData['updated_on'] = date('Y-m-d H:i:s');
                    $updatesData['updated_by'] = $check_login[0]->client_id;
                    $weight_id = $this->weight_model->updateWeight($updatesData, $weightData[0]->weight_id);

                    $update_data['weight'] = $weight;
                    $update_data['target_weight'] = $json_data->target_weight; 
                    $update_id = $this->profile_info_model->updateProfile($update_data, $check_login[0]->client_id);
                        
                    if ($weight_id > 0) {
                        $response = ['status' => true, 'msg' => 'Weight data Update successfully!'];
                    }else{
                        $response = ['status' => false, 'msg' => 'Failed to update weight data'];
                    }
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data'];
        }
        return $this->response->setJSON($response);
    }

    // get current week weight data 
    public function get_week_weight_bkp(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $date = (isset($json_data->date)?$json_data->date:date('Y-m-d'));
        $weightData = [];
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                //$startOfWeek = date('Y-m-d H:i:s', strtotime('last sunday', strtotime($date)));
                $currentDate = date('Y-m-d', strtotime(date('Y-m-d').'+1 days'));
                $startOfWeek = date('Y-m-d', strtotime($currentDate . ' - 7 days'));
                $startDate = date('Y-m-d', strtotime($currentDate . ' - 7 days')); 
                
                for ($i = 0; $i < 7; $i++) {
                    $weightData[$startOfWeek] = $this->weight_model->getWeekWeight($startOfWeek, $check_login[0]->client_id);
                    $startOfWeek = date('Y-m-d H:i:s', strtotime("+$i days", strtotime($date)));
                }
                
                // $weightData = $this->weight_model->getWeekWeight($date, $check_login[0]->client_id);
                
                if (!empty($weightData)) {
                    $data['weightData']=$weightData;
                    $data['target']= "75";
                    
                    $response = ['status' => true, 'msg' => 'Current week weight data get successfully', 'data' => $data];
                }else{
                    $response = ['status' => false, 'msg' => 'weight data not Found', 'data' => $weightData];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found'];
        }
        return $this->response->setJSON($response);
    }
    public function get_week_weight(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $date = date('Y-m-d');
        $date = (isset($json_data->date)?$json_data->date:date('Y-m-d'));        
        $weightData = [];
        $data['weightData'] = $weightData;
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                //$startOfWeek = date('Y-m-d h:i:s', strtotime('last sunday', strtotime($date)));
                //$startDate = date('Y-m-d H:i:s', strtotime('last sunday', strtotime($date)));

                // $currentDate = date('Y-m-d h:i:s', strtotime('+1 days'));
                // $startOfWeek = date('Y-m-d h:i:s', strtotime($currentDate . ' - 7 days'));
                // $startDate = date('Y-m-d h:i:s', strtotime($currentDate . ' - 7 days'));

                $currentDate = date('Y-m-d', strtotime($date.'+1 days'));
                $startOfWeek = date('Y-m-d', strtotime($currentDate . ' - 7 days'));
                $startDate = date('Y-m-d', strtotime($currentDate . ' - 7 days'));    

                for ($i = 1; $i <= 7; $i++) {                   
                    $dataArray = $this->weight_model->getWeekWeight($startOfWeek, $check_login[0]->client_id);
                    if (!empty($dataArray)) {
                         $weightData[] = $dataArray[0];
                    }else{
                         $weightData[] = array('weight_id' => "0",'client_id' => $check_login[0]->client_id,'weight' => "0",'target_weight' => "0",'created_on' => date('Y-m-d H:i:s',strtotime($startOfWeek)),'created_by' => $check_login[0]->client_id,'updated_on' => date('Y-m-d H:i:s',strtotime($startOfWeek)),'updated_by' => $check_login[0]->client_id);
                    }

                    $startOfWeek = date('Y-m-d H:i:s', strtotime("+$i days", strtotime($startDate)));
                }
                $data['weightData'] = $weightData;
                //$data['target'] = "75";



                if (!empty($weightData)) {
                    $response = ['status' => true, 'msg' => 'Current week weight data get successfully', 'data' => $data];
                }else{
                    $response = ['status' => false, 'msg' => 'weight data not Found', 'data' => $data];
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