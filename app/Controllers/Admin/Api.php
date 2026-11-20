<?php

namespace App\Controllers\Admin;
use App\Models\Users_model; // Corrected model class name
use CodeIgniter\Api\ResponseTrait;


class Api extends Controller
{
	protected $users_model;
    use ResponseTrait;

	public function __construct(){
		$this->users_model = new Users_model();
	}

    public function getUser($id = null)
    {
        if($id == null){
            $userRecords = $this->users_model->getAllUsers();
            $result = [
                "status" => '201',
                "data" => $userRecords,
            ];
        } else {
            $userRecords = $this->users_model->getUserDetails($id);
            if (!empty($userRecords)) {
                $result = [
                    "status" => '201',
                    "data" => $userRecords,
                ];
            } else {
                $result = [
                    "status" => '404',
                    "data" => 'No Record Found',
                ];

            }
        }
        return $this->respond($result);
    }
    public function delete($id)
    {
        $userRecords = $this->users_model->getUserDetails($id);
        if (!empty($userRecords))
        {
            $updateData = array();
            $updateData['user_status'] = 'In-Active';
            $deleteUser = $this->users_model->updateUsers($updateData,$id);
            if ($deleteUser == 1) {
                $result = [
                    "status" => '201',
                    "data" => 'Delete Successfully',
                ];
            }else{
                $result = [
                    "status" => '404',
                    "data" => 'Some Error Found',
                ];
            }
        } else {
            $result = [
                "status" => '404',
                "data" => 'No Record Found',
            ];
        }
        return $this->respond($result);
    }

    public function addUser(){
        if ($this->request->getMethod() == 'post') {

            $data = [
                "username" => $this->request->getVar("username"),
                "pass" => md5($this->request->getVar("username")),
                "first_name" => $this->request->getVar("first_name"),
                "last_name" => $this->request->getVar("last_name"),
                "email" => $this->request->getVar("email"),
                "phone_number" => $this->request->getVar("phone_number"),
                "user_role" => $this->request->getVar("user_role"),
                "user_status" => 'Active',
                "state_id" => $this->request->getVar("state_id"),
                "address" => $this->request->getVar("address")
            ];
            // echo "<pre>";print_r($data);die();
            if (!empty($data)) {
                $userId = $this->users_model->addUsers($data);
                if ($userId > 0) {
                    $result = [
                        "status" => '201',
                        "data" => 'User Add Successfully',
                    ];
                }else{
                    $result = [
                        "status" => '404',
                        "data" => 'Some Error Found',
                    ];
                }
            }else{
                $result = [
                    "status" => '404',
                    "data" => 'Data is Empty',
                ];
            }
        }else{
            $result = [
                "status" => '404',
                "data" => 'Post Menthod Not Found',
            ];
        }
        return $this->respond($result);
    }
    public function updateUser($id){
        if ($id != '' ) {
            $userRecords = $this->users_model->getUserDetails($id);
            if (!empty($userRecords)) {
                if ($this->request->getMethod() == 'post') {
                    $data = [
                        "username" => $this->request->getVar("username"),
                        "pass" => md5($this->request->getVar("username")),
                        "first_name" => $this->request->getVar("first_name"),
                        "last_name" => $this->request->getVar("last_name"),
                        "email" => $this->request->getVar("email"),
                        "phone_number" => $this->request->getVar("phone_number"),
                        "user_role" => $this->request->getVar("user_role"),
                        "user_status" => 'Active',
                        "state_id" => $this->request->getVar("state_id"),
                        "address" => $this->request->getVar("address")
                    ];
                    // echo "$id<pre>";print_r($data);die();
                    if (!empty($data)) {
                        $userId = $this->users_model->updateUsers($data,$id);
                        if ($userId == 1) {
                            $result = [
                                "status" => '201',
                                "data" => 'User Update Successfully',
                            ];
                        }else{
                            $result = [
                                "status" => '404',
                                "data" => 'Some Error Found',
                            ];
                        }
                    }else{
                        $result = [
                            "status" => '404',
                            "data" => 'Data is Empty',
                        ];
                    }
                }else{
                    $result = [
                        "status" => '404',
                        "data" => 'Post Menthod Not Found',
                    ];
                }
            }else{
                $result = [
                    "status" => '404',
                    "data" => 'User Not Found',
                ];
            }
        }else{
            $result = [
                "status" => '404',
                "data" => 'User Id Not Found',
            ];
        }
        return $this->respond($result);
    }

    public function createUser(){
        if ($this->request->getMethod() == 'post') 
        {   
            $phone_number = $this->request->getVar("phone_number");
            $whatsapp_update = $this->request->getVar("whatsapp_update");
            
            if (!empty($phone_number)) {

                
            } else {
                $result = [
                    "status" => '404',
                    "data" => 'Phone Number Not Found',
                ];
            }
        } else {
            $result = [
                "status" => '404',
                "data" => 'Post Menthod Not Found',
            ];
        }
        return $this->respond($result);
    }

}
