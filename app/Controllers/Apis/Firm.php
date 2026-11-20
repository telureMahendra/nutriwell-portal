<?php

namespace App\Controllers\Apis;
use CodeIgniter\RESTful\ResourceController;

class Firm extends ResourceController
{

    public function __construct()
    {
        $this->profile_info_model = model('App\Models\Profile_info_model', false);
        $this->client_login_model = model('App\Models\Client_login_model', false);
        $this->firm_model = model('App\Models\Firm_model', false);
    }
    public function headers()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Max-Age', '1000');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->response->setHeader('Content-Type', 'application/json');
    }   

    // view contact details on contact us page
    public function contact_details(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\",$this->_request));
        
        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $contactDetails = $this->firm_model->getcontactDetails();
                if (!empty($contactDetails)) {
                    $response = ['status' => true, 'msg' => 'Get Contact Details Successfully', 'data' => $contactDetails];
                }else{
                    $response = ['status' => false, 'msg' => 'Contact Details Not Found'];
                }
            }else{
                $response = ['status' => false, 'msg' => 'User Not Login'];
            } 
        } else {
            $response = ['status' => false, 'msg' => 'Envalid Data'];
        }
        return $this->response->setJSON($response);
    }
}