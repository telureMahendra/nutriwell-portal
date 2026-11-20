<?php

namespace App\Controllers\Apis;
use CodeIgniter\RESTful\ResourceController;

class Faq extends ResourceController
{

    public function __construct()
    {
        $this->profile_info_model = model('App\Models\Profile_info_model', false);
        $this->client_login_model = model('App\Models\Client_login_model', false);
        $this->faq_model = model('App\Models\Faq_model', false);
    }
    public function headers()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Max-Age', '1000');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->response->setHeader('Content-Type', 'application/json');
    }   
    // view all faq list 
    public function faq_list(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\",$this->_request));
        
        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $faqList = $this->faq_model->getFaqList();
                if (!empty($faqList)) {
                    $response = ['status' => true, 'msg' => 'Get All FAQ Successfully', 'data' => $faqList];
                }else{
                    $response = ['status' => false, 'msg' => 'FAQ Not Found'];
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