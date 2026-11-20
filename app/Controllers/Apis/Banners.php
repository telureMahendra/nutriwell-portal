<?php

namespace App\Controllers\Apis;
use CodeIgniter\RESTful\ResourceController;

class Banners extends ResourceController
{
    public function __construct()
    {
        $this->banners_model = model('App\Models\Banners_model', false);
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

    //view list of banners
    public function homepage_banner(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\",$this->_request));

        $json_data = $this->request->getJSON();

        $tocken = $json_data->tocken; 
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $bannerList = $this->banners_model->getAllBanners();
                foreach($bannerList as $key => $row) {
                    $bannerList[$key]['banner_image_url'] = base_url() . "public/uploads/banner/" . $row['banner_image'];
                }
                if (!empty($bannerList)) {
                    $response = ['status' => true, 'msg' => 'Banners List', 'data' => $bannerList];
                }else{
                    $response = ['status' => false, 'msg' => 'Banners Not Found'];
                }
            }else{
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        }else{
            $response = ['status' => false, 'msg' => 'User Not Login'];
        }
        return $this->response->setJSON($response);
    } 


}
?>