<?php

namespace App\Controllers\Apis;

use CodeIgniter\RESTful\ResourceController;

class Appointment extends ResourceController
{
    public function __construct()
    {
        $this->profile_info_model = model('App\Models\Profile_info_model', false);
        $this->client_login_model = model('App\Models\Client_login_model', false);
        $this->appointment_model = model('App\Models\Appointment_model', false);
    }

    public function headers()
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Max-Age', '1000');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->response->setHeader('Content-Type', 'application/json');
    }

    //view data to book appointment
    public function view_appointment()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));
        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        if (!empty($tocken)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $bookingData = $this->appointment_model->getActiveBooking($check_login[0]->client_id);
                if (!empty($bookingData)) {
                    $docterData = $this->appointment_model->getBookingDocter($bookingData[0]->doctor_id);
                    $docterData[0]->image_url = base_url() . "public/uploads/doctor";

                    if (!empty($docterData)) {
                        $response = ['status' => true, 'msg' => 'Found Doctor Details Successfully', 'data' => $docterData];
                    } else {
                        $response = ['status' => true, 'msg' => 'Doctor Details not found', 'data' => $docterData];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client Not have any Acive Plan'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Enter Valid data'];
        }
        return $this->response->setJSON($response);
    }

    //add new appointment
    public function book_appointment()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $date = $json_data->date;
        $slot = $json_data->slot;
        if (!empty(trim($tocken)) && !empty(trim($date)) && !empty(trim($slot))) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $bookingData = $this->appointment_model->getActiveBooking($check_login[0]->client_id);
                if (!empty($bookingData)) {
                    if ($bookingData[0]->doctor_id != 0) {


                        $insert_data = [
                            'date' => $date,
                            'slot' => $slot,
                            'client_id' => $check_login[0]->client_id,
                            'booking_id' => $bookingData[0]->booking_id,
                            'doctor_id' => $bookingData[0]->doctor_id,
                            'status' => "Upcoming",
                            'updated_by' => $check_login[0]->client_id,
                            'updated_on' => date('Y-m-d'),
                            'created_by' => $check_login[0]->client_id,
                            'created_on' => date('Y-m-d'),
                        ];
                        $appointment_id = $this->appointment_model->addAppointment($insert_data);
                        if ($appointment_id > 0) {
                            $response = ['status' => true, 'msg' => 'Appointment added successfully', 'data' => $appointment_id];
                        } else {
                            $response = ['status' => false, 'msg' => 'Failed to add appointment'];
                        }
                    } else {
                        $response = ['status' => false, 'msg' => 'Doctor is not assigned'];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client not have any active plan'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Send Valid Data'];
        }
        return $this->response->setJSON($response);
    }

    //list of all appointment by status
    public function book_appointment_list()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $status = $json_data->status;
        $fromdate = $json_data->fromdate;
        $todate = $json_data->todate;
        $search = $json_data->search;

        if (!empty($tocken) && !empty($status)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $bookingData = $this->appointment_model->getActiveBooking($check_login[0]->client_id);
                if (!empty($bookingData)) {
                    $appointmentList = $this->appointment_model->getAllAppointment($status, $check_login[0]->client_id, $fromdate, $todate, $search);
                    if (!empty($appointmentList)) {

                        foreach ($appointmentList as $key => $value) {
                            $appointmentList[$key]->image_url = base_url() . "public/uploads/doctor/";
                        }
                        $response = ['status' => true, 'msg' => 'Get all appointment successfully', 'data' => $appointmentList];
                    } else {
                        $response = ['status' => false, 'msg' => 'Appointment not found', 'data' => $appointmentList];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client not have any active plan'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Send Valid Data'];
        }
        return $this->response->setJSON($response);
    }

    // view all details of appointment 
    public function view_book_appointment()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $appointment_id = $json_data->appointment_id;
        if (!empty($tocken) && !empty($appointment_id)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $doctorData = $this->appointment_model->getDoctor($check_login[0]->client_id);

                $appointmentData = $this->appointment_model->getAppointment($appointment_id);
                if (!empty($appointmentData)) {
                    $response = ['status' => true, 'msg' => 'Get appointment Details successfully', 'data' => $appointmentData];
                } else {
                    $response = ['status' => false, 'msg' => 'Appointment not found', 'data' => $appointmentData];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Send Valid Data'];
        }
        return $this->response->setJSON($doctorData);
    }



    // Close appointment 
    public function close_appointment()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $appointment_id = $json_data->appointment_id;
        $reason = $json_data->reason;

        if (!empty($tocken) && !empty($appointment_id)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $insert_data = [
                    'status' => 'Cancelled',
                    'cancel_reason' => $reason,
                    'updated_by' => $check_login[0]->client_id,
                    'updated_on' => date('Y-m-d'),
                ];

                $appointment_id = $this->appointment_model->updateAppointment($insert_data, $appointment_id);
                if ($appointment_id == 1) {
                    $response = ['status' => true, 'msg' => 'Appointment Cancelled successfully', 'data' => $appointment_id];
                } else {
                    $response = ['status' => false, 'msg' => 'Failed to cancelled appointment'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Send Valid Data'];
        }
        return $this->response->setJSON($response);
    }


    //confirm appointment
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
                $update_id = $this->appointment_model->updateAppointment($data_update, $appointment_id);
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

    // appointment list for time slot by doctor
    public function slot_appointment()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $date = $json_data->date;
       
        if (!empty($tocken) && !empty($date)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                //echo $check_login[0]->client_id;
                //die();

                $bookingData = $this->appointment_model->getActiveBooking($check_login[0]->client_id);

                if (!empty($bookingData)) {
                    if ($bookingData[0]->doctor_id != 0) {

                        $checklastAppointment = $this->appointment_model->getLastAppointment($check_login[0]->client_id, $date);
                       
                       // if ($checklastAppointment) {
                        if (!empty($checklastAppointment)) { 
                            $nextDt = date('d M Y', strtotime($checklastAppointment[0]->date . ' + 7 days'));
                                $response = ['status' => false, 'msg' => "You can schedule your next appointment after 7 days on $nextDt", 'data' => [ ['date'=>$checklastAppointment[0]->date] ]];
                        
                        }else{
                            $appointmentSlot = $this->appointment_model->getAppointmentSlot($bookingData[0]->doctor_id, $date);
                            $allSlot = $this->appointment_model->getSlot();
                            $sizeSlot = sizeof($allSlot);
                            if ($appointmentSlot) {
                                // $i = 0;                        
                                for ($i = 0; $i < $sizeSlot; $i++) {
                                    foreach ($appointmentSlot as $value) {
                                        if (trim($value->slot) == trim($allSlot[$i]->time)) {
                                            $slotArray[$i]['isBooked'] = "Booked";
                                            $slotArray[$i]['slot'] = trim($allSlot[$i]->time);
                                        } else {
                                            $slotArray[$i]['isBooked'] = "Available";
                                            $slotArray[$i]['slot'] = trim($allSlot[$i]->time);
                                        }
                                    }
                                }
                            } else {
                                for ($i = 0; $i < $sizeSlot; $i++) {
                                    $slotArray[$i]['isBooked'] = "Available";
                                    $slotArray[$i]['slot'] = trim($allSlot[$i]->time);
                                }
                            }
                            if (!empty($slotArray)) {
                                $response = ['status' => true, 'msg' => 'Get slots successfully', 'data' => $slotArray];
                            } else {
                                $response = ['status' => false, 'msg' => 'Slots not found', 'data' => $slotArray];
                            }
                        }
                    } else {
                        $response = ['status' => false, 'msg' => 'Mentor not assigned. Contact admin.'];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client not have any active plan'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Send Valid Data'];
        }
        return $this->response->setJSON($response);
    }

    public function slot_appointment_bkp_061123()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $date = $json_data->date;

        if (!empty($tocken) && !empty($date)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {
                $bookingData = $this->appointment_model->getActiveBooking($check_login[0]->client_id);
                if (!empty($bookingData)) {

                    $appointmentSlot = $this->appointment_model->getAppointmentSlot($bookingData[0]->doctor_id, $date);
                    if (!empty($appointmentSlot)) {
                        $response = ['status' => true, 'msg' => 'Get slots appointment successfully', 'data' => $appointmentSlot];
                    } else {
                        $response = ['status' => false, 'msg' => 'Slots not found', 'data' => $appointmentSlot];
                    }
                } else {
                    $response = ['status' => false, 'msg' => 'Client not have any active plan'];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Send Valid Data'];
        }
        return $this->response->setJSON($response);
    }
    // view all details of appointment 
    public function appointment_details()
    {
        $this->headers();
        $this->_request = file_get_contents("php://input");
        $this->_request = implode("", explode("\\", $this->_request));

        $json_data = $this->request->getJSON();
        $tocken = $json_data->tocken;
        $appointment_id = $json_data->appointment_id;
        if (!empty($tocken) && !empty($appointment_id)) {
            $check_login = $this->client_login_model->checkTocken($tocken);
            if (!empty($check_login)) {

                $appointmentData = $this->appointment_model->getAppointment($appointment_id);
                if (!empty($appointmentData)) {
                    $appointmentData[0]->img_url = base_url() . "public/uploads/doctor/";
                    $response = ['status' => true, 'msg' => 'Get appointment Details successfully', 'data' => $appointmentData];
                } else {
                    $response = ['status' => false, 'msg' => 'Appointment not found', 'data' => $appointmentData];
                }
            } else {
                $response = ['status' => false, 'msg' => 'User Not Login'];
            }
        } else {
            $response = ['status' => false, 'msg' => 'Send Valid Data'];
        }
        return $this->response->setJSON($response);
    }
}
