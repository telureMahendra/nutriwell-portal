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
        $calorieData = [];
        if (!empty($date) && !empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $calorieData = $this->calorie_model->getCalorie($date, $check_login[0]->client_id);
                if (!empty($calorieData)) {
                    $response = ['status' => true, 'msg' => 'Calorie data get successfully', 'data' => $calorieData];
                }else{
                    $response = ['status' => false, 'msg' => 'Calorie data not Found', 'data' => $calorieData];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $calorieData];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data Found', 'data' => $calorieData];
        }
        return $this->response->setJSON($response);
    }

}