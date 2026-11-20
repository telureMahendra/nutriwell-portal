<?php

namespace App\Controllers\Apis;
use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Calorie extends ResourceController
{
    protected $Calorie_model;
    protected $Client_login_model;
    use ResponseTrait;

    public function __construct()
    {
        $this->calorie_model = model('App\Models\Calorie_model', false);
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

    public function get_calorie(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $date = $json_data->date;
        $tocken = $json_data->tocken;
        if (!empty($date) && !empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $calorieData = $this->calorie_model->getCalorie($date, $check_login[0]->client_id);
                $totalquantity = 0;
                foreach ($calorieData as $key => $value) {
                    $totalquantity = $totalquantity + $value->quantity;
                }
                $data['totalquantity'] = $totalquantity;
                $data['target'] = 4000;

                if (!empty($calorieData)) {
                    $response = ['status' => true, 'msg' => 'Calorie data get successfully', 'data' => $data];
                }else{
                    $response = ['status' => false, 'msg' => 'Calorie data not Found', 'data' => $data];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }

    public function food_type(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $date = $json_data->date;
        $tocken = $json_data->tocken;
        if (!empty(trim($date)) && !empty(trim($tocken))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $calorieData = $this->calorie_model->getCalorie($date, $check_login[0]->client_id);
                $totalCalorue = 0;
                foreach ($calorieData as $key => $value) {
                    $totalCalorue = $totalCalorue+$value->quantity;
                }
                // $calorieData->totalCalorue = $totalCalorue;
                $data['totalCalorue']=$totalCalorue;
                $data['targetCalorue']= 1400;
                $data['calorieData']=$calorieData;

                $foodType = $this->calorie_model->getFoodType();
                $data['foodType']=$foodType;
                if (!empty($foodType)) {
                    $calorieFoodList = [];
 
                    foreach ($foodType as $key => $value) {
                        $list = $this->calorie_model->getFoodCalorie($date, $check_login[0]->client_id, $value->food_type_id);
                        // Same unit fields as food_list, so a logged food row can
                        // show the same icon as it did in the food picker.
                        attachFoodUnitList($list);
                        $foodType[$key]->foodData = $list;
                    }
                    $data['foodType']=$foodType;

                    $response = ['status' => true, 'msg' => 'Food type get successfully', 'data' => $data];
                }else{
                    $response = ['status' => false, 'msg' => 'Failed to get food type', 'data' => $data];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }
    
    public function food_type031123(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $date = $json_data->date;
        $tocken = $json_data->tocken;
        if (!empty(trim($date)) && !empty(trim($tocken))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $calorieData = $this->calorie_model->getCalorie($date, $check_login[0]->client_id);
                $data['calorieData']=$calorieData;

                $foodType = $this->calorie_model->getFoodType();
                $data['foodType']=$foodType;
                if (!empty($foodType)) {
                    $calorieFoodList = [];
 
                    foreach ($foodType as $key => $value) {
                        $list = $this->calorie_model->getFoodCalorie($date, $check_login[0]->client_id, $value->food_type_id);
                        $calorieFoodList[$value->food_type_id] = $list;
                    }
                    $data['calorieFoodList']=$calorieFoodList;

                    $response = ['status' => true, 'msg' => 'Food type get successfully', 'data' => $data];
                }else{
                    $response = ['status' => false, 'msg' => 'Failed to get food type', 'data' => $data];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }

    public function food_list(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();

      

        //$food_type_id = $json_data->food_type_id;
        $search = $json_data->search;
        $tocken = $json_data->tocken;
        $data = [];
        if (!empty(trim($tocken))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
  
                $foodData = $this->calorie_model->getFoodAllList($search);
                // Adds unit / unit_label / unit_icon to every row. Falls back to
                // deriving the unit from the food name when food.unit is unset.
                attachFoodUnitList($foodData);
                $data['foodData'] = $foodData;
                //$data['food_type_id'] = $food_type_id;
                if (!empty($foodData)) {
                    
                    $response = ['status' => true, 'msg' => 'Food List get successfully', 'searchfood' => $search, 'data' => $data];
                }else{
                    $response = ['status' => false, 'msg' => 'Failed to get food list', 'data' => $data];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found'];
        }
        return $this->response->setJSON($response);
    }

    public function add_food(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $food_id = $json_data->food_id;
        $food_type_id = $json_data->food_type_id;
        $food_date = (isset($json_data->date) ? $json_data->date: date('Y-m-d H:i:s'));
        $tocken = $json_data->tocken;
        if (!empty(trim($food_id)) && !empty(trim($tocken)) && !empty(trim($food_type_id))) {

            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $foodData = $this->calorie_model->getFoodCal($food_id);
                if (!empty($foodData)) {
                    $insert_data['client_id'] = $check_login[0]->client_id;
                    $insert_data['food_id'] = $food_id;
                    $insert_data['food_type_id'] = $food_type_id;
                    $insert_data['quantity'] = $foodData[0]->calories;
                    //$insert_data['date'] = date('Y-m-d H:i:s');
                    $insert_data['date'] = $food_date;
                    $insert_data['created_on'] = date('Y-m-d H:i:s');
                    $insert_data['created_by'] = $check_login[0]->client_id;
                    $insert_data['updated_on'] = date('Y-m-d H:i:s');
                    $insert_data['updated_by'] = $check_login[0]->client_id;

                    $insert_id = $this->calorie_model->addFood($insert_data);

                    if (!empty($insert_id)) {
                        $response = ['status' => true, 'msg' => 'Food added successfully', 'data' => $insert_data];
                    }else{
                        $response = ['status' => false, 'msg' => 'Failed to add food', 'data' => $data];
                    }
                }else{
                    $response = ['status' => false, 'msg' => 'Failed to get food data', 'data' => $data];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }
    
    public function remove_food(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $calorie_id = $json_data->calorie_id;
        $tocken = $json_data->tocken;
        if (!empty(trim($calorie_id)) && !empty(trim($tocken))) {

            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                

                $insert_id = $this->calorie_model->removeFood($calorie_id);

                if ($insert_id > 0) {
                    $response = ['status' => true, 'msg' => 'Food removed successfully'];
                }else{
                    $response = ['status' => false, 'msg' => 'Failed to Removed food'];
                }
                
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found'];
        }
        return $this->response->setJSON($response);
    }

    

}
?>