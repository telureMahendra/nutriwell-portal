<?php

namespace App\Controllers\Apis;
use App\Controllers\BaseController;

use App\Models\Client_login_model; // Assuming the model's correct class name is UsersModel
use App\Models\Users_model; // Assuming the model's correct class name is UsersModel
use CodeIgniter\RESTful\ResourceController;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;



class Clientlogin extends ResourceController
{
    protected $Client_login_model;
    protected $users_model;
    protected $Profile_info_model;

	// protected $JWT;
    protected $format = 'json';
    public function __construct()
    {
        // Load the Client_login_model model using the model() method
        // $this->load->library('JWT');

        $this->client_login_model = model('App\Models\Client_login_model', false);
        $this->profile_info_model = model('App\Models\Profile_info_model', false);

        $this->users_model = model('App\Models\Users_model', false);
        // $this->JWT = library('App\Libraries\JWT', false);
    }
    public function headers()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Max-Age', '1000');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->response->setHeader('Content-Type', 'application/json');
    }
    // generate otp and send otp
    public function sendOtp()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request=implode("",explode("\\",$this->_request));
        $json_data = $this->request->getJSON();

        if (!isset($json_data->mobile_number)) {
            return $this->failValidationError('Mobile number is required.');
        }
        $mobile_number = $json_data->mobile_number;
        $client_data = $this->client_login_model->checkIfRegistered($mobile_number);

        if ($client_data == 0) {
            $data_client_mobile['client_phone'] = $mobile_number;
            $data_client_mobile['fcm_token'] = (isset($json_data->fcm_token)? $json_data->fcm_token : '');
            $client_insert_id = $this->client_login_model->insertClientMobile($data_client_mobile);
            $response = [
                'status' => true,
                'msg' => 'Client added Successfully'
            ];
        } else {

            $fcm_token = (isset($json_data->fcm_token)? $json_data->fcm_token : '');
            $client_insert_id = $this->client_login_model->updateFcmToken($mobile_number,$fcm_token);

            $response = [ 'status' => false, 'msg' => 'Login Failed' ];
        }
        
        $mobile_no = $this->client_login_model->checkIfLogin($mobile_number);
        if ($mobile_no == 1) {
            $otp = ($mobile_number === '9988776655') ? 1234 : rand(1000, 9999);
            $end_time = date('Y-m-d H:i:s', strtotime('+30 seconds'));
            $data_insert = [
                    'mobile_number' => $mobile_number,
                    'otp' => $otp,
                    'created_on' => date('Y-m-d'),
                    'created_time' => date('Y-m-d H:i:s'),
                    'end_time' => $end_time
            ];
            $otp_id = $this->client_login_model->sendOtp($data_insert);

            if ($otp_id > 0) {
                $data = [
                        'otp_id' => $otp_id,
                        'otp' => $otp,
                        'mobile_number' => $mobile_number
                ];
                $response = [ 'status' => true, 'msg' => 'Otp Send Successfully', 'data' => $data ];
            } else {
                $response = ['status' => false, 'msg' => 'Error OTP Send'];
            }
        } else {
            $response = [ 'status' => false, 'msg' => 'Already Loggedin'];
        }
        return $this->response->setJSON($response);
    }

    //verify otp 
    public function verify_otp()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = json_decode($this->_request, true);
        $otp_id = $json_data['otp_id'];
        $otp = $json_data['otp'];
        $mobile_number = $json_data['mobile_number'];

        $verify = $this->client_login_model->verifyotp($otp_id, $otp, $mobile_number);

        if (sizeof($verify) > 0) {
            $user_data = $this->client_login_model->getUserId($mobile_number);
            if (sizeof($user_data) > 0) {

                $JWTSecretKey = 'H4U_SECRET_KEY_' . bin2hex(random_bytes(16));
                $tocken = JWT::encode($user_data, $JWTSecretKey, 'HS256');

                $data_insert = array();
                $data_insert['client_id'] = $user_data[0]->client_id;
                $data_insert['tocken_generated']=$tocken;
                $data_insert['tocken_status']='Active';
                $tocken_id=$this->client_login_model->addTocken($data_insert);
                $profileCompleted = 'No';
                
                if($tocken_id > 0){
                    
                    $isprofile = $this->profile_info_model->checkProfile($user_data[0]->client_id);
                    
                    if ($isprofile == 1) {
                        $profileCompleted = 'Yes';
                        
                    }
                    $data= [
                            'otp_id' => $verify[0]->otp_id,
                            'otp' => $verify[0]->otp,
                            'tocken'=>$tocken,
                            'profileCompleted'=>$profileCompleted,
                        ];
                        $response = ['status' => True, 'msg' => 'OTP Verification Successfully', 'data' => $data];
                    
                } else {
                    $response = ['status' => false, 'msg' => 'Tocken Not Found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Envalid Data'];
        }
        return $this->response->setJSON($response);
    }
    
    public function verify_otp_bkp26()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = json_decode($this->_request, true);
        $otp_id = $json_data['otp_id'];
        $otp = $json_data['otp'];
        $mobile_number = $json_data['mobile_number'];

        $verify = $this->client_login_model->verifyotp($otp_id, $otp, $mobile_number);

        if (sizeof($verify) > 0) {
            $user_data = $this->client_login_model->getUserId($mobile_number);
            if (sizeof($user_data) > 0) {

                $JWTSecretKey = date('Y-m-d H:i:s');
                $tocken = JWT::encode($user_data, $JWTSecretKey, 'HS256');

                $data_insert = array();
                $data_insert['client_id'] = $user_data[0]->client_id;
                $data_insert['tocken_generated']=$tocken;
                $data_insert['tocken_status']='Active';
                $tocken_id=$this->client_login_model->addTocken($data_insert);
                if($tocken_id > 0){
                    $data= [
                            'otp_id' => $verify[0]->otp_id,
                            'otp' => $verify[0]->otp,
                            'tocken'=>$tocken
                        ];
                    $response = ['status' => True, 'msg' => 'OTP Verification Successfully', 'data' => $data];
                } else {
                    $response = ['status' => false, 'msg' => 'Tocken Not Found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Envalid Data'];
        }
        return $this->response->setJSON($response);
    }

    // resend otp and regenerate it
    public function resend(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data=json_decode($this->_request, true);
        //$otp_id = $json_data['otp_id'];
        //$old_otp = $json_data['otp']; 
        $mobile_number = $json_data['mobile_number'];

        //$check_otp=$this->client_login_model->checkOtp($otp_id,$old_otp);
        //if ($check_otp > 0) {
        $mobile_no = $this->client_login_model->checkIfLogin($mobile_number);
        if ($mobile_no == 1) {

            $otp = ($mobile_number === '9988776655') ? 1234 : rand(1000, 9999);
            //$resent_otp= rand(1000,9999);
            //$otp_id=$otp_id;
            $to_add = 3;
            $end_time= date('Y-m-d H:i:s',strtotime('+30 seconds'));             
            $data_update=array();               
            $data_update['otp']=$otp;    
            $data_update['created_time']=date('Y-m-d H:i:s');
            $data_update['end_time']=$end_time;

            
            $end_time = date('Y-m-d H:i:s', strtotime('+30 seconds'));
            $data_insert = [
                    'mobile_number' => $mobile_number,
                    'otp' => $otp,
                    'created_on' => date('Y-m-d'),
                    'created_time' => date('Y-m-d H:i:s'),
                    'end_time' => $end_time
            ];
            $otp_id = $this->client_login_model->sendOtp($data_insert);

            if ($otp_id > 0) {
                $data = [
                        'otp_id' => $otp_id,
                        'otp' => $otp,
                        'mobile_number' => $mobile_number
                ];
                $response = [ 'status' => true, 'msg' => 'Otp Send Successfully', 'data' => $data ];
            } else {
                $response = ['status' => false, 'msg' => 'Error OTP Send'];
            }            


            // //$resend_otp_id=$this->client_login_model->updateSentOtp($data_update,$otp_id,$old_otp);
            // $verify = $this->client_login_model->verifyotp($otp_id, $old_otp, $mobile_number);
            // if (sizeof($verify) > 0) {
            // //if($resend_otp_id>0){
            //     //$data=['resent_otp'=>$resent_otp];

            //     $data = [
            //         //'otp_id' => $resend_otp_id,
            //         //'otp' => $resent_otp,
            //         'otp_id' => $verify[0]->otp_id,
            //         'otp' => $verify[0]->otp,                    
            //         'mobile_number' => $mobile_number
            //     ];

            //     $response = ['status' => true, 'msg' => 'OTP Resend Successfully', 'data' => $data];
            // }else{
            //     $response = ['status' => false, 'msg' => 'Faild To Resend OTP'];
            // }




        }else{
            $response = ['status' => false, 'msg' => 'Data Not Found'];
        }
        return $this->response->setJSON($response); 
    }
    
    //logout client
    public function logout(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request=implode("",explode("\\",$this->_request));

        $json_data = json_decode($this->_request, true);
        $tocken = $json_data['tocken'];

        $tocken_data=$this->client_login_model->checkTocken($tocken);
        if($tocken_data > 0){

            $data_update['tocken_status']='In-Active';
            $tocken_generated=$tocken_data[0]->tocken_generated;
            $client_id=$tocken_data[0]->client_id;
            $delete_id=$this->client_login_model->deleteTocken($data_update,$tocken_generated,$client_id);
            if($delete_id > 0){
                $response = ['status' => true, 'msg' => 'Log Out Successful'];
            }else{
                $response = ['status' => false, 'msg' => 'Log Out Failed'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Client Not Login'];
        }
        return $this->response->setJSON($response); 
    }

    
    public function delete_account(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request=implode("",explode("\\",$this->_request));
      
        $json_data = json_decode($this->_request, true);

        if (!isset($json_data['tocken'])) {
            return $this->response->setJSON([
                'status' => false,
                'msg' => 'Token is missing'
            ]);
        }

        $tocken = $json_data['tocken'];

        $tocken_data=$this->client_login_model->checkTocken($tocken);
        if($tocken_data > 0){

            $client_id=$tocken_data[0]->client_id;
            $delete=$this->client_login_model->deleteClientData($client_id);

            if($delete){
                $response = ['status' => true, 'msg' => 'Account and associated data deleted successfully'];
            }else{
                $response = ['status' => false, 'msg' => 'Account deletion failed'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Account is Inactive'];
        }

        return $this->response->setJSON($response); 
    }
}