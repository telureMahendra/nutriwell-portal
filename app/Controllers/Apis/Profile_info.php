<?php

namespace App\Controllers\Apis;

use App\Controllers\BaseController;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;


class Profile_info extends ResourceController
{
    protected $Profile_info_model;
    protected $Client_login_model;
    use ResponseTrait;


    public function __construct()
    {
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

    public function profile()
    {

        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();

        $client_name = $json_data->client_name;
        $client_email = $json_data->client_email;
        $client_gender = $json_data->client_gender;
        $source_of_info = isset($json_data->source_of_info) ? $json_data->source_of_info : null;
        $tocken = $json_data->tocken;
        if (!empty(trim($client_name)) && !empty(trim($client_email)) && !empty(trim($client_gender)) && !empty(trim($tocken))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $clt_id = $this->profile_info_model->checkProfile($check_login[0]->client_id);
                $client_mobile_number = $this->profile_info_model->clientPhone($check_login[0]->client_id);
                if(!empty($client_mobile_number)){
                if ($clt_id == 0) {


                    $insert_data = [
                        'client_id' => $check_login[0]->client_id,
                        'client_number' => $check_login[0]->client_id . rand(0, 9999) . '/' . $check_login[0]->client_id,
                        'client_profile_pic' => '',
                        'client_name' => $client_name,
                        'client_gender' => $client_gender,
                        'source_of_info' => $source_of_info,
                        'client_email' => $client_email,
                        'client_mobile_number' => $client_mobile_number[0]->client_phone,
                        'created_by' => $check_login[0]->client_id,
                        'updated_by' => $check_login[0]->client_id,
                        'created_on' => date('Y-m-d'),
                        'updated_on' => date('Y-m-d')
                    ];
                    $profile_id = $this->profile_info_model->addProfile($insert_data);
                    if (!empty($profile_id)) {
                        $returnData = [
                            'client_name' => $client_name,
                            'client_email' => $client_email,
                            'client_gender' => $client_gender,
                            'source_of_info' => $source_of_info
                        ];

                        $response = ['status' => true, 'msg' => 'Profile Details added Successfully', 'data' => $returnData];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed to add Profile Details'];
                    }
                } else {
                    $insert_data = [
                        'client_id' => $check_login[0]->client_id,
                        'client_profile_pic' => '',
                        'client_name' => $client_name,
                        'client_email' => $client_email,
                        'client_gender' => $client_gender,
                        'source_of_info' => $source_of_info,
                        'client_mobile_number' => $client_mobile_number[0]->client_phone,
                        'created_on' => date('Y-m-d'),
                        'updated_on' => date('Y-m-d')
                    ];

                    $profile_id = $this->profile_info_model->updateProfile($insert_data, $check_login[0]->client_id);

                    $response = ['status' => true, 'msg' => 'Client data update successfully'];
                }

            }else{
                $response = ['status' => false, 'msg' => 'Your Account is inactive please contact admin'];
            }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid data'];
        }
        return $this->response->setJSON($response);
    }

    public function addage()
    {

        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();

        $age = $json_data->age;
        $tocken = $json_data->tocken;
        if (!empty($age) && !empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            $returnData = [];
            if (!empty($check_login)) {

                $clt_id = $this->profile_info_model->checkProfile($check_login[0]->client_id);
                if ($clt_id == 1) {

                    $insert_data = [
                        'age' => $age
                    ];
                    $profile_id = $this->profile_info_model->updateProfile($insert_data, $check_login[0]->client_id);
                    if ($profile_id == 1) {
                        $returnData = [
                            'age' => $age
                        ];
                        $response = ['status' => true, 'msg' => 'Age added Successfully', 'data' => $returnData];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed to add Age in Profile Details', 'data' => $returnData];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client profile not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid data'];
        }
        return $this->response->setJSON($response);
    }

    public function addheight()
    {

        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();

        $height = $json_data->height;
        $height_in = $json_data->height_in;
        $tocken = $json_data->tocken;
        if (!empty($height_in) && !empty($height) && !empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);

            if (!empty($check_login)) {
                $returnData = [];

                $clt_id = $this->profile_info_model->checkProfile($check_login[0]->client_id);
                if ($clt_id == 1) {

                    $insert_data = [
                        'height' => $height,
                        'height_in' => $height_in
                    ];
                    $profile_id = $this->profile_info_model->updateProfile($insert_data, $check_login[0]->client_id);
                    if ($profile_id == 1) {
                        $returnData = [
                            'height' => $height,
                            'height_in' => $height_in
                        ];
                        $response = ['status' => true, 'msg' => 'Height added Successfully', 'data' => $returnData];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed to add Height in Profile Details', 'data' => $returnData];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client profile not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid data'];
        }
        return $this->response->setJSON($response);
    }

    public function addweight()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $weight = $json_data->weight;
        $tocken = $json_data->tocken;

        if (!empty($tocken) && !empty($weight)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $returnData = [];
                $clt_id = $this->profile_info_model->checkProfile($check_login[0]->client_id);
                if ($clt_id == 1) {
                    $insert_data = [
                        'weight' => $weight
                    ];
                    $profile_id = $this->profile_info_model->updateProfile($insert_data, $check_login[0]->client_id);
                    if ($profile_id == 1) {
                        $returnData = [
                            'weight' => $weight
                        ];
                        $response = ['status' => true, 'msg' => 'Weight added Successfully', 'data' => $returnData];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed to add Weight in Profile Details', 'data' => $returnData];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client Profile Not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function adddiet()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $diet = $json_data->diet;
        $tocken = $json_data->tocken;

        if (!empty($tocken) && !empty($diet)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $returnData = [];
                $clt_id = $this->profile_info_model->checkProfile($check_login[0]->client_id);
                if ($clt_id == 1) {
                    $insert_data = [
                        'diet' => $diet
                    ];
                    $profile_id = $this->profile_info_model->updateProfile($insert_data, $check_login[0]->client_id);
                    if ($profile_id == 1) {
                        $returnData = [
                            'diet' => $diet
                        ];
                        $response = ['status' => true, 'msg' => 'Diet added Successfully', 'data' => $returnData];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed to add Diet in Profile Details', 'data' => $returnData];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client Profile Not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function view_medical_condition()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;

        if (!empty(trim($tocken))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $medical_condition = $this->profile_info_model->getMedicalConditionsWithSelection($check_login[0]->client_id);

                if (!empty($medical_condition)) {
                    $response = ['status' => true, 'msg' => 'Get Data Successfully', 'data' => $medical_condition];
                } else {
                    $response = ['status' => false, 'msg' => 'Medical Conditions Not found', 'data' => $medical_condition];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function medical_condition()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $medical_condition = $json_data->medical_condition;
        $other_medical_condition = (isset($json_data->other_medical_condition) ? $json_data->other_medical_condition : '');
        $tocken = $json_data->tocken;
        // $response = ['status' => false, 'msg' => 'Enter valid Data', 'data' => $medical_condition];
        if (!empty($tocken) && isset($medical_condition) && is_array($medical_condition)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $clt_id = $this->profile_info_model->checkProfile($check_login[0]->client_id);
                if ($clt_id == 1) {
                    $insert_data = [
                        'medical_condition' => $medical_condition,
                        'other_medical_condition' => $other_medical_condition
                    ];
                    $this->profile_info_model->delete_medical($check_login[0]->client_id);
                    $profile_id = $this->profile_info_model->addMedicalCondition($insert_data, $check_login[0]->client_id);
                    if ($profile_id !== false) {

                        $response = ['status' => true, 'msg' => 'Medical Condition added Successfully', 'data' => $medical_condition];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed to add Medical Condition in Profile Details'];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client Profile Not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function view_lifestyle()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;

        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $life_style = $this->profile_info_model->getLifeStyle();
                foreach ($life_style as $key => $row) {
                    $life_style[$key]->image_url = base_url() . "public/uploads/lifestyle";
                }
                if (!empty($life_style)) {
                    $response = ['status' => true, 'msg' => 'Get Data Successfully', 'data' => $life_style];
                } else {
                    $response = ['status' => false, 'msg' => 'Life Style Not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function life_style()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $lifestyle_id = $json_data->lifestyle_id;
        $tocken = $json_data->tocken;
        // $response = ['status' => false, 'msg' => 'Enter valid Data', 'data' => $life_style];
        if (!empty($tocken) && !empty($lifestyle_id)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $returnData = [];
                $clt_id = $this->profile_info_model->checkProfile($check_login[0]->client_id);
                if ($clt_id == 1) {
                    $insert_data = [
                        'lifestyle_id' => $lifestyle_id
                    ];
                    $profile_id = $this->profile_info_model->updateProfile($insert_data, $check_login[0]->client_id);
                    if (!empty($profile_id)) {
                        $returnData = [
                            'lifestyle_id' => $lifestyle_id
                        ];
                        $response = ['status' => true, 'msg' => 'Life Style added Successfully', 'data' => $returnData];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed to add Life Style in Profile Details', 'data' => $returnData];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client Profile Not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function view_goals()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;

        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $goals = $this->profile_info_model->getGoals();
                foreach ($goals as $key => $row) {
                    $goals[$key]->image_url = base_url() . "public/uploads/goal";
                }
                if (!empty($goals)) {
                    $response = ['status' => true, 'msg' => 'Get Goals Successfully', 'data' => $goals];
                } else {
                    $response = ['status' => false, 'msg' => 'Goals Not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function add_goal()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $goal_id = $json_data->goal_id;
        $tocken = $json_data->tocken;
        if (!empty($tocken) && !empty($goal_id)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $clt_id = $this->profile_info_model->checkProfile($check_login[0]->client_id);
                if ($clt_id == 1) {
                    $returnData = [];
                    $insert_data = [
                        'goal_id' => $goal_id
                    ];
                    $profile_id = $this->profile_info_model->updateProfile($insert_data, $check_login[0]->client_id);
                    if (!empty($profile_id)) {
                        $returnData = [
                            'goal_id' => $goal_id
                        ];
                        $response = ['status' => true, 'msg' => 'Goal added Successfully', 'data' => $returnData];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed to add Goal in Profile Details', 'data' => $returnData];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client Profile Not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function view_profile()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $profile_data = $this->profile_info_model->profileData($check_login[0]->client_id);
                $profile_data[0]->profilr_pic_url = base_url() . "public/uploads/client";

                $data['profile_data'] = $profile_data;

                if (!empty($profile_data)) {
                    $medical_condition = $this->profile_info_model->getMedicalConditionsWithSelection($check_login[0]->client_id);

                    // foreach ($medical_condition as $key => $value) {
                    //     $all_medical_condition[$value->medical_id] = $value->title;
                    //}
                    $data['medical_condition'] = $medical_condition;

                    $profile_data['medical_condition'] = $medical_condition;
                    $response = ['status' => true, 'msg' => 'User Data', 'data' => $data];
                } else {
                    $response = ['status' => false, 'msg' => 'User Data Not Found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid data'];
        }
        return $this->response->setJSON($response);
    }

    public function update_profile()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $client_name = $json_data->client_name;
        $client_email = $json_data->client_email;
        $client_gender = $json_data->client_gender;
        $age = $json_data->age;
        $height = $json_data->height;
        $height_in = $json_data->height_in;
        $weight = $json_data->weight;
        $diet = $json_data->diet;
        $lifestyle_id = $json_data->lifestyle_id;
        $goal_id = $json_data->goal_id;
        $other_medical_condition = $json_data->other_medical_condition;
        $medical_condition = $json_data->medical_condition;

        if (!empty($tocken) && !empty($client_name) && !empty($client_email) && !empty($client_gender) && !empty($age) && !empty($height) && !empty($height_in) && !empty($weight) && !empty($diet) && !empty($lifestyle_id) && !empty($goal_id)) {

            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $update_data = [
                    'client_name' => $client_name,
                    'client_email' => $client_email,
                    'client_gender' => $client_gender,
                    'age' => $age,
                    'height' => $height,
                    'height_in' => $height_in,
                    'weight' => $weight,
                    'diet' => $diet,
                    'lifestyle_id' => $lifestyle_id,
                    'other_medical_condition' => $other_medical_condition,
                    'created_on' => date('Y-m-d'),
                    'created_by' => $check_login[0]->client_id,
                    'updated_on' => date('Y-m-d'),
                    'updated_by' => $check_login[0]->client_id
                ];
                $insert_data = [
                    'other_medical_condition' => $other_medical_condition,
                    'medical_condition' => $medical_condition
                ];

                if (!empty($update_data)) {
                    $deleteMedical = $this->profile_info_model->delete_medical($check_login[0]->client_id);
                    $update_medical = $this->profile_info_model->addMedicalCondition($insert_data, $check_login[0]->client_id);
                    $profile_data = $this->profile_info_model->updateProfile($update_data, $check_login[0]->client_id);
                    if ($profile_data == 1) {
                        $update_data['medical_condition'] = $medical_condition;
                        $response = ['status' => true, 'msg' => 'Profile Update Successfully', 'data' => $update_data];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed to Update Profile'];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'User Data Not Found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid data'];
        }
        return $this->response->setJSON($response);
    }

    // update profie image    
    public function update_profile_image()
    {
        $this->headers();
        $client_profile_pic = $this->request->getFile('client_profile_pic');
        $tocken = $this->request->getPost('tocken');
        if (!empty($tocken) && !empty($client_profile_pic)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $newName = $client_profile_pic->getRandomName();
                $client_profile_pic->move(ROOTPATH . 'public/uploads/profile_image', $newName);
                $insert_data = ['client_profile_pic' => 'public/uploads/profile_image/' . $newName];
                $update_id = $this->profile_info_model->updateProfile($insert_data, $check_login[0]->client_id);
                if ($update_id == 1) {
                    $response = ['status' => true, 'msg' => 'Image update successfully', 'data' => $update_id];
                } else {
                    $response = ['status' => false, 'msg' => 'Failed to update image', 'data' => $update_id];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Failed to upload image'];
        }


        return $this->response->setJSON($response);
    }

    //dev 4
    public function notification()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        //echo '<pre>';print_r($tocken);die();
        $data = [];
        if (!empty(trim($tocken))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $notificationData = $this->profile_info_model->getNotification($check_login[0]->client_id);
                $data['notification_data'] = $notificationData;
                if (!empty($notificationData)) {
                    $response = ['status' => true, 'msg' => 'Notification data get successfully', 'data' => $data];
                } else {
                    $response = ['status' => false, 'msg' => 'Notification data not Found', 'data' => $data];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }

    public function coupens_list()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        //echo '<pre>';print_r($tocken);die();
        $data = [];
        if (!empty(trim($tocken))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $coupensData = $this->profile_info_model->getCoupens($check_login[0]->client_id);
                $data['coupen_data'] = $coupensData;
                if (!empty($coupensData)) {
                    $response = ['status' => true, 'msg' => 'Coupon data get successfully', 'data' => $data];
                } else {
                    $response = ['status' => false, 'msg' => 'Coupon data not Found', 'data' => $data];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }

    public function apply_coupon()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $booking_id = $json_data->booking_id;
        $coupon_id = $json_data->coupon_id;
        $coupon_code = $json_data->coupon_code;
        //echo '<pre>';print_r($tocken);die();
        $data = [];
        if (!empty(trim($tocken)) && !empty(trim($booking_id)) && (!empty(trim($coupon_id)) || !empty(trim($coupon_code)))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                if (!empty(trim($coupon_id))) {
                    $condition = ['coupon_id' => $coupon_id];
                } elseif (!empty(trim($coupon_code))) {
                    $condition = ['coupon_code' => $coupon_code];
                }

                $coupenDetils = $this->profile_info_model->getCouponDetails($condition);
                $bookingDetils = $this->profile_info_model->getBookingDetails($booking_id);
                if (!empty($coupenDetils)) {


                    $data_update['coupon_id'] = $coupenDetils[0]->coupon_id;
                    $discount = ($bookingDetils[0]->subtotal * $coupenDetils[0]->discount) / 100;
                    $data_update['discount'] = $discount;
                    //$data_update['total_amount'] = $bookingDetils[0]->subtotal - $discount;

                    $update_id = $this->profile_info_model->applyCoupon($booking_id, $data_update);
                    //$data['coupen_data'] = $update_id;
                    if ($update_id > 0) {
                        $response = ['status' => true, 'msg' => 'Coupon applied successfully'];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed  to apply coupon'];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Coupan details Not found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data'];
        }
        return $this->response->setJSON($response);
    }


    public function appointment_doctor_details()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $booking_id = $json_data->booking_id;
        //echo '<pre>';print_r($tocken);die();
        $data = [];
        if (!empty(trim($tocken)) && !empty(trim($booking_id))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $bookingDetils = $this->profile_info_model->getBookingDetails($booking_id);
                $doctorDetails = $this->profile_info_model->getDoctorDetails($bookingDetils[0]->doctor_id);
                $doctorAppointments = $this->profile_info_model->getDoctorAppointments($bookingDetils[0]->doctor_id);
                $doctorDetails[0]->image_url = base_url() . "public/uploads/doctor";
                $data['doctorDetails'] = $doctorDetails;
                // $data['doctorDetails']['url'] = "demo test";
                $data['doctorAppointments'] = $doctorAppointments;
                if (!empty($doctorDetails)) {
                    $response = ['status' => true, 'msg' => 'Appointment details loaded successfully', 'data' => $data];
                } else {
                    $response = ['status' => false, 'msg' => 'Failed  to get appointment details', 'data' => $data];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }

    public function book_appointment()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $date = $json_data->date;
        $slot = $json_data->slot;
        $doctor_id = $json_data->doctor_id;
        $booking_id = $json_data->booking_id;
        //echo '<pre>';print_r($tocken);die();
        $data = [];
        if (!empty(trim($tocken)) && !empty(trim($date)) && !empty(trim($slot)) && !empty(trim($doctor_id)) && !empty(trim($booking_id))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                $data_insert = array();
                $data_insert['date'] = $date;
                $data_insert['slot'] = $slot;
                $data_insert['client_id'] = $check_login[0]->client_id;
                $data_insert['doctor_id'] = $doctor_id;
                $data_insert['booking_id'] = $booking_id;
                $data_insert['status'] = 'Upcoming';
                $data_insert['created_on'] = date('Y-m-d');
                $data_insert['created_by'] = $check_login[0]->client_id;
                $data_insert['updated_on'] = date('Y-m-d');
                $data_insert['updated_by'] = $check_login[0]->client_id;

                $insert_id = $this->profile_info_model->insertAppointment($data_insert);

                $appointmentDetails = $this->profile_info_model->getAppointmentDetails($insert_id);

                $data['appointmentDetails'] = $appointmentDetails;

                $doctorDetails = $this->profile_info_model->getDoctorDetails($doctor_id);
                $doctorDetails[0]->image_url = base_url() . "public/uploads/doctor";
                $data['doctorDetails'] = $doctorDetails;

                if ($insert_id > 0) {
                    $response = ['status' => true, 'msg' => 'Appointment was booked successfully', 'data' => $data];
                } else {
                    $response = ['status' => false, 'msg' => 'Failed  to book appointment', 'data' => $data];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }

    public function appointment_details()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        // $booking_id = $json_data->booking_id; 
        //echo '<pre>';print_r($tocken);die();
        $data = [];
        if (!empty(trim($tocken))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                $appointmentDetils = $this->profile_info_model->getAppointmentLists($check_login[0]->client_id);
                $data['appointmentDetils'] = $appointmentDetils;
                if (!empty($appointmentDetils)) {
                    $response = ['status' => true, 'msg' => 'Appointment details loaded successfully', 'data' => $data];
                } else {
                    $response = ['status' => false, 'msg' => 'Failed  to load appointment details', 'data' => $data];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found', 'data' => $data];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data', 'data' => $data];
        }
        return $this->response->setJSON($response);
    }


    public function update_appointment()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $appointment_id = $json_data->appointment_id;
        $status = $json_data->status;
        //echo '<pre>';print_r($tocken);die();
        $data = [];
        if (!empty(trim($tocken)) && !empty(trim($appointment_id)) && !empty(trim($status))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                $data_update = array();
                $data_update['status'] = $status;
                $update_id = $this->profile_info_model->updateAppointment($data_update, $appointment_id);
                if ($update_id > 0) {
                    $response = ['status' => true, 'msg' => 'Appointment updated successfully'];
                } else {
                    $response = ['status' => false, 'msg' => 'Failed  to update appointment'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data'];
        }
        return $this->response->setJSON($response);
    }

    public function cancel_appointment()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $appointment_id = $json_data->appointment_id;
        $reason = $json_data->reason;
        //echo '<pre>';print_r($tocken);die();
        $data = [];
        if (!empty(trim($tocken)) && !empty(trim($appointment_id))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                $data_update = array();
                $data_update['status'] = 'Cancelled';
                $data_update['cancel_reason'] = $reason;
                $update_id = $this->profile_info_model->updateAppointment($data_update, $appointment_id);
                if ($update_id > 0) {
                    $response = ['status' => true, 'msg' => 'Appointment cancelled successfully'];
                } else {
                    $response = ['status' => false, 'msg' => 'Failed  to cancel appointment'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Invalid data'];
        }
        return $this->response->setJSON($response);
    }
    // end dev 4


    public function notifications()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));
        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $bookingData = $this->client_login_model->getnotifications($check_login[0]->client_id);
                if (!empty($bookingData)) {
                    $response = ['status' => true, 'msg' => 'Notifications found', 'data' => $bookingData];
                } else {
                    $response = ['status' => false, 'msg' => 'No data found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter Valid data'];
        }
        return $this->response->setJSON($response);
    }


    public function steps_calories()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                if (count($json_data->data) > 0) {
                    $client_id = $check_login[0]->client_id;
                    foreach ($json_data->data as $json) {
                        $check_count = $this->profile_info_model->check_steps_calories($json->count_date, $json->count_type,$client_id);
                        if (empty($check_count)) {
                            $insertData = array("client_id" => $client_id, "count_date" => $json->count_date, "data_count" => $json->data_count, "count_type" => $json->count_type);
                            $this->profile_info_model->add_steps_calories($insertData);
                        } else {
                            $updateData = array("data_count" => $json->data_count);
                            
                            $this->profile_info_model->update_steps_calories($updateData, $json->count_date,$json->count_type, $client_id);
                        }
                    }
                }
                $response = ['status' => true, 'msg' => 'Record added / updated Successfully'];
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }
    public function last_steps_calories()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                $client_id = $check_login[0]->client_id;
                $steps_data = $this->profile_info_model->last_steps_calories($client_id,0);
                $calories_data = $this->profile_info_model->last_steps_calories($client_id,1);

                $response = ['status' => true, 'msg' => 'Data Found', 'steps_data' => $steps_data, "calories_data" => $calories_data];
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function add_body_measurements()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        
        $chest = isset($json_data->chest) ? $json_data->chest : '';
        $waist = isset($json_data->waist) ? $json_data->waist : '';
        $biceps = isset($json_data->biceps) ? $json_data->biceps : '';
        $wrist = isset($json_data->wrist) ? $json_data->wrist : '';
        $thighs = isset($json_data->thighs) ? $json_data->thighs : '';
        $calves = isset($json_data->calves) ? $json_data->calves : '';

        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $client_id = $check_login[0]->client_id;
                
                $check_measurements = $this->profile_info_model->get_body_measurements($client_id);
                
                if (empty($check_measurements)) {
                    $insertData = array(
                        "client_id" => $client_id,
                        "chest" => $chest,
                        "waist" => $waist,
                        "biceps" => $biceps,
                        "wrist" => $wrist,
                        "thighs" => $thighs,
                        "calves" => $calves,
                        "created_on" => date('Y-m-d'),
                        "created_by" => $client_id,
                        "updated_on" => date('Y-m-d'),
                        "updated_by" => $client_id
                    );
                    $this->profile_info_model->add_body_measurements($insertData);
                    $response = ['status' => true, 'msg' => 'Body measurements added successfully'];
                } else {
                    $updateData = array(
                        "chest" => $chest,
                        "waist" => $waist,
                        "biceps" => $biceps,
                        "wrist" => $wrist,
                        "thighs" => $thighs,
                        "calves" => $calves,
                        "updated_on" => date('Y-m-d'),
                        "updated_by" => $client_id
                    );
                    $this->profile_info_model->update_body_measurements($updateData, $client_id);
                    $response = ['status' => true, 'msg' => 'Body measurements updated successfully'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function get_body_measurements()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = isset($json_data->tocken) ? $json_data->tocken : '';

        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $client_id = $check_login[0]->client_id;
                $measurements_data = $this->profile_info_model->get_body_measurements($client_id);
                
                if (!empty($measurements_data)) {
                    $response = ['status' => true, 'msg' => 'Data Found', 'data' => $measurements_data];
                } else {
                    $response = ['status' => false, 'msg' => 'No body measurements found', 'data' => []];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter valid Data'];
        }
        return $this->response->setJSON($response);
    }
}
