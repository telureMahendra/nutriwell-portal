<?php

namespace App\Controllers\Apis;

use CodeIgniter\RESTful\ResourceController;

class Testimonial extends ResourceController
{
     protected $Testimonial_model; 
     
    public function __construct()
    {
        $this->profile_info_model = model('App\Models\Profile_info_model', false);
        $this->client_login_model = model('App\Models\Client_login_model', false);
        $this->testimonial_model = model('App\Models\Testimonial_model', false);
    }
    public function headers()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Max-Age', '1000');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->response->setHeader('Content-Type', 'application/json');
    }

    //view all testimonial list 
    public function testimonial_list(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\",$this->_request));
        $json_data = $this->request->getJSON();

        $tocken = $json_data->tocken;
        
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $testimonialList = $this->testimonial_model->getTestimonial();
                foreach($testimonialList as $key => $row){
                    $testimonialList[$key]->profilr_pic_url = base_url()."public/uploads/client";
                }
                if (!empty($testimonialList)) {
                    $response = ['status' => true, 'msg' => 'get Testimonial Successfully', 'data' => $testimonialList];
                }else{
                    $response = ['status' => false, 'msg' => 'Testimonial not Found'];
                }
            }else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'User Not Login'];
        }
        return $this->response->setJSON($response);
    }

    public function image_testimonials()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));
        $json_data = $this->request->getJSON();

        $tocken = $json_data->tocken;

        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $list = $this->testimonial_model->getRandomTestimonials('image', 5);
                foreach ($list as $row) {
                    $row->before_image_url = !empty($row->before_image) ? base_url() . 'public/' . $row->before_image : '';
                    $row->after_image_url  = !empty($row->after_image)  ? base_url() . 'public/' . $row->after_image  : '';
                    unset($row->before_image, $row->after_image, $row->image_path);
                }
                if (!empty($list)) {
                    $response = ['status' => true, 'msg' => 'Image testimonials fetched successfully', 'data' => $list];
                } else {
                    $response = ['status' => false, 'msg' => 'No image testimonials found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'User Not Login'];
        }
        return $this->response->setJSON($response);
    }

    public function text_testimonials()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));
        $json_data = $this->request->getJSON();

        $tocken = $json_data->tocken;

        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $list = $this->testimonial_model->getRandomTestimonials('text', 5);
                foreach ($list as $row) {
                    unset($row->before_image, $row->after_image, $row->image_path);
                }
                if (!empty($list)) {
                    $response = ['status' => true, 'msg' => 'Text testimonials fetched successfully', 'data' => $list];
                } else {
                    $response = ['status' => false, 'msg' => 'No text testimonials found'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Found'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'User Not Login'];
        }
        return $this->response->setJSON($response);
    }

    public function submit_testimonial()
    {
        $this->headers();

        $tocken           = $this->request->getPost('tocken');
        $client_name      = trim($this->request->getPost('client_name') ?? '');
        $designation      = trim($this->request->getPost('designation') ?? '');
        $description      = trim($this->request->getPost('description') ?? '');
        $rating           = (int)($this->request->getPost('rating') ?? 5);
        $testimonial_type = $this->request->getPost('testimonial_type') ?? 'text';

        if (empty($tocken)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Login']);
        }

        if (empty($client_name) || empty($description)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'client_name and description are required']);
        }

        $check_login = $this->client_login_model->checkTocken($tocken);
        if (empty($check_login)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Found']);
        }

        $client_id = $check_login[0]->client_id;

        $insertData = [
            'client_id'        => $client_id,
            'client_name'      => $client_name,
            'designation'      => $designation,
            'description'      => $description,
            'rating'           => $rating,
            'testimonial_type' => $testimonial_type,
            'status'           => 'Active',
            'approval_status'  => 'Pending',
            'date'             => date('Y-m-d'),
            'created_on'       => date('Y-m-d H:i:s'),
            'created_by'       => $client_id,
            'updated_on'       => date('Y-m-d H:i:s'),
            'updated_by'       => $client_id,
        ];

        if ($testimonial_type === 'image') {
            $upload_path  = ROOTPATH . 'public/uploads/testimonials/';
            $before_image = $this->request->getFile('before_image');
            $after_image  = $this->request->getFile('after_image');

            if ($before_image && $before_image->isValid() && !$before_image->hasMoved()) {
                $newName = $before_image->getRandomName();
                $before_image->move($upload_path, $newName);
                $insertData['before_image'] = 'uploads/testimonials/' . $newName;
            }

            if ($after_image && $after_image->isValid() && !$after_image->hasMoved()) {
                $newName = $after_image->getRandomName();
                $after_image->move($upload_path, $newName);
                $insertData['after_image'] = 'uploads/testimonials/' . $newName;
            }
        }

        $testimonial_id = $this->testimonial_model->addTestimonial($insertData);

        if ($testimonial_id > 0) {
            $response = ['status' => true, 'msg' => 'Testimonial submitted successfully, pending approval'];
        } else {
            $response = ['status' => false, 'msg' => 'Failed to submit testimonial'];
        }

        return $this->response->setJSON($response);
    }

    // screen not found add new testimonial
    public function addTestimonial(){
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));
        $json_data = $this->request->getJSON();

        $tocken = $json_data->tocken; 
        $description = $json_data->description; 

        if (!empty($tocken) && !empty($description)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $clt_id = $this->profile_info_model->checkProfile($check_login[0]->client_id);
                if (!empty($clt_id)) {
                    $insertData = [
                        'client_id' => $check_login[0]->client_id,
                        'description' => $description,
                        'status' => 'Active',
                        'date' => date('Y-m-d'),
                        'created_on' => date('Y-m-d'),
                        'created_by' => $check_login[0]->client_id,
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => $check_login[0]->client_id
                    ];
                    $testimonial_id = $this->testimonial_model->addTestimonial($insertData);

                    if ($testimonial_id > 0) {
                        $response = ['status' => true, 'msg' => 'Testimonial added Successfully', 'data' => $insertData];
                    } else {
                        $response = ['status' => false, 'msg' => 'Failed add Testimonial'];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'User Not Found'];
                }
            } else {
                $response = ['status' => false, 'msg' => "User Not Login"];
            }
        } else {
            $response = ['status' => false, 'msg' => "Enter Valid Data"];
        }
        return $this->response->setJSON($response);
    }
}