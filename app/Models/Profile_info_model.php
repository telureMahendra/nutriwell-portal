<?php

namespace App\Models;

use CodeIgniter\Model;

class Profile_info_model extends Model
{
    // protected $table = 'client_login'; // Replace with your actual table name

    // protected $allowedFields = ['client_id', 'client_phone', 'client_status', 'is_login', 'profile_complete'];
    public function addProfile($insert_data)
    {
        $builder = $this->db->table('client_profile');
        $builder->insert($insert_data);
        return $this->db->insertID();
    }
    public function checkProfile($client_id)
    {
        $builder = $this->db->table('client_profile');
        $builder->select('*');
        $builder->where('client_id', $client_id);
        $builder->where('status', 'Active');
        //$builder->where('goal_id >',"0");

        $query = $builder->get();
        if ($query->getResult()) {
            return 1;
        } else {
            return 0;
        }
    }
    public function updateProfile($insert_data, $client_id)
    {
        $builder = $this->db->table('client_profile');
        $builder->where('client_id', $client_id);
        $builder->update($insert_data);
        return 1;
    }
    public function getMedicalCondition()
    {
        $builder = $this->db->table('medical_condition');
        $builder->select('medical_id,title');
        $builder->where('status', 'Active');
        $query = $builder->get();

        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getMedicalConditionsWithSelection($client_id)
    {
        $builder = $this->db->table('medical_condition AS MC');
        $builder->select('MIN(MC.medical_id) as medical_id, MC.title');
        $builder->select('IF(MAX(CMC.medical_id) IS NOT NULL, "Yes", "No") AS is_selected', false);
        $builder->join('client_medical_condition AS CMC', 'CMC.medical_id = MC.medical_id AND CMC.client_id = ' . (int) $client_id . ' AND CMC.status = "Active"', 'left');
        $builder->where('MC.status', 'Active');
        $builder->groupBy('MC.title');
        $query = $builder->get();
        return $query->getResult() ?: [];
    }
    public function addMedicalCondition($insert_data, $client_id)
    {

        if (array_key_exists("other_medical_condition", $insert_data)) {

            $builder = $this->db->table('client_profile');
            $builder->set(array("other_medical_condition" => $insert_data["other_medical_condition"]));
            $builder->where('client_id', $client_id);
            $builder->update();
            $result = $builder->get();
            //$affectedRows = $result->connID->affected_rows;
        }

        $builder = $this->db->table('client_medical_condition');
        $numRows = sizeof($insert_data);
        $data_insert = array();
        foreach ($insert_data['medical_condition'] as $key => $value) {
            // If the frontend sends is_selected = "No", skip inserting this condition
            if (isset($value->is_selected) && strcasecmp(trim($value->is_selected), 'No') === 0) {
                continue;
            }


            if ($value->medical_id == '') {
                // Check if title already exists to prevent double entry
                $existing = $this->db->table('medical_condition')
                                     ->where('title', trim($value->title))
                                     ->get()
                                     ->getRow();
                if ($existing) {
                    $data_insert['medical_id'] = $existing->medical_id;
                } else {
                    $insert_data_mc = [
                        'title' => trim($value->title),
                        'status' => 'Active',
                        'created_on' => date('Y-m-d'),
                        'created_by' => $client_id,
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => $client_id,
                    ];
                    $insert_medical_id = $this->addMedical($insert_data_mc);
                    $data_insert['medical_id'] = $insert_medical_id;
                }
            } else {
                $data_insert['medical_id'] = $value->medical_id; // by Akshay 281023
            }

            $data_insert['client_id'] = $client_id;
            $data_insert['status'] = 'Active';
            $data_insert['created_on'] = date('Y-m-d');
            $data_insert['created_by'] = $client_id;
            $data_insert['updated_on'] = date('Y-m-d');
            $data_insert['updated_by'] = $client_id;
            $builder->insert($data_insert);
        }
        return true;
    }

    public function addMedical($insert_data)
    {
        $builder = $this->db->table('medical_condition');
        $builder->insert($insert_data);
        return $this->db->insertID();
    }

    public function getLifeStyle()
    {
        $builder = $this->db->table('lifestyle');
        $builder->select('*');
        $builder->where('status', 'Active');
        $query = $builder->get();

        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function getGoals()
    {
        $builder = $this->db->table('goal');
        $builder->select('*');
        $builder->where('status', 'Active');
        $query = $builder->get();

        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function profileData($client_id)
    {
        $builder = $this->db->table('client_profile');
        $builder->select('*');
        $builder->where('client_id', $client_id);
        $builder->where('status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function clientMedicalCondition($client_id)
    {
        $builder = $this->db->table('client_medical_condition AS CMC');
        $builder->select('CMC.medical_id,MC.title');
        $builder->join('medical_condition AS MC', 'MC.medical_id = CMC.medical_id');
        $builder->where('CMC.client_id', $client_id);
        $builder->where('CMC.status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function clientMedicalCondition_bkp($client_id)
    {
        $builder = $this->db->table('client_medical_condition');
        $builder->select('medical_id');
        $builder->where('client_id', $client_id);
        $builder->where('status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function delete_medical($client_id)
    {
        $this->db->query("SET SQL_SAFE_UPDATES = 0;");
        
        $builder = $this->db->table('client_medical_condition');
        $builder->where('client_id', $client_id);
        $builder->update(['status' => 'Inactive']);
        
        $builder2 = $this->db->table('client_medical_condition');
        $builder2->where('client_id', $client_id);
        $builder2->delete();
        
        return true;
    }

    public function clientPhone($client_id)
    {
        $builder = $this->db->table('client_login');
        $builder->select('client_phone');
        $builder->where('client_id', $client_id);
        $builder->where('client_status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getWeight($client_id)
    {
        $builder = $this->db->table('client_profile AS CP');
        $builder->select('CP.weight,CP.target_weight');
        $builder->where('CP.client_id', $client_id);
        $builder->where('CP.status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    //Dev 4

    public function getNotification($client_id)
    {
        $builder = $this->db->table('notification');
        $builder->select('notification_id,client_id,description');
        $builder->where('client_id', $client_id);
        $query_notification = $builder->get();
        if ($query_notification->getResult()) {
            return $query_notification->getResult();
        } else {
            return [];
        }
    }

    public function getCoupens($client_id = null)
    {
        $current_date = date('Y-m-d');
        $builder = $this->db->table('coupon');
        $builder->select('coupon_id, coupon_code, discount, discount_type, date, status, valid_from, valid_to, description, coupon_type');
        $builder->where('status', 'Active');
        $builder->where('valid_from <=', $current_date);
        $builder->groupStart()
            ->where('valid_to IS NULL', null, false)
            ->orWhere('valid_to >=', $current_date)
            ->groupEnd();

        // Return general coupons + user-specific ones assigned to this client
        if ($client_id) {
            $cid = (int) $client_id;
            $builder->groupStart()
                ->where('coupon_type', 'general')
                ->orWhere("(coupon_type = 'user_specific' AND JSON_CONTAINS(assigned_client_ids, '\"$cid\"'))", null, false)
                ->groupEnd();
        } else {
            $builder->where('coupon_type', 'general');
        }

        return $builder->get()->getResult() ?: [];
    }

    public function getCouponDetails($condition)
    {
        $builder = $this->db->table('coupon');
        $builder->select('coupon_id,coupon_code,discount,date,status');
        $builder->where($condition);
        $query_coupen = $builder->get();
        if ($query_coupen->getResult()) {
            return $query_coupen->getResult();
        } else {
            return [];
        }
    }

    public function getBookingDetails($booking_id)
    {
        $builder = $this->db->table('booking');
        $builder->select('booking_id,total_amount,subtotal,doctor_id');
        $builder->where('booking_id', $booking_id);
        $query_booking = $builder->get();
        if ($query_booking->getResult()) {
            return $query_booking->getResult();
        } else {
            return [];
        }
    }

    public function applyCoupon($booking_id, $data_update)
    {
        $builder = $this->db->table('booking');
        $builder->set($data_update);
        $builder->where('booking_id', $booking_id);
        $builder->update();
        $result = $builder->get();
        $affectedRows = $result->connID->affected_rows;

        if ($affectedRows > 0) {
            return 1;
        } else {
            return 0;
        }
    }


    public function getDoctorDetails($doctor_id)
    {
        $builder = $this->db->table('doctor');
        $builder->select('doctor_id,doctor_name,description,image');
        $builder->where('doctor_id', $doctor_id);
        $query_doctor = $builder->get();
        if ($query_doctor->getResult()) {
            return $query_doctor->getResult();
        } else {
            return [];
        }
    }

    public function getDoctorAppointments($doctor_id)
    {
        $builder = $this->db->table('appointment');
        $builder->select('appointment_id,date,slot,client_id,booking_id');
        $builder->where('doctor_id', $doctor_id);
        $builder->where('status', 'Active');
        $query_doctor = $builder->get();
        if ($query_doctor->getResult()) {
            return $query_doctor->getResult();
        } else {
            return [];
        }
    }

    public function insertAppointment($data_insert)
    {
        $builder = $this->db->table('appointment');
        $builder->insert($data_insert);
        return $this->db->insertID();
    }

    public function getAppointmentDetails($appointment_id)
    {
        $builder = $this->db->table('appointment');
        $builder->select('appointment_id,date,slot,client_id,booking_id,doctor_id');
        $builder->where('appointment_id', $appointment_id);
        $query_doctor = $builder->get();
        if ($query_doctor->getResult()) {
            return $query_doctor->getResult();
        } else {
            return [];
        }
    }

    public function getAppointmentLists($client_id)
    {
        $builder = $this->db->table('appointment');
        $builder->select('appointment_id,date,slot,client_id,booking_id,doctor_id,status');
        $builder->where('client_id', $client_id);
        $builder->where('date >=', date('Y-m-d'));
        $query_doctor = $builder->get();
        if ($query_doctor->getResult()) {
            return $query_doctor->getResult();
        } else {
            return [];
        }
    }

    public function updateAppointment($data_update, $appointment_id)
    {
        $builder = $this->db->table('appointment');
        $builder->set($data_update);
        $builder->where('appointment_id', $appointment_id);
        $builder->update();
        $result = $builder->get();
        $affectedRows = $result->connID->affected_rows;

        if ($affectedRows > 0) {
            return 1;
        } else {
            return 0;
        }
    }


    public function check_steps_calories($count_date, $count_type, $client_id)
    {
        $builder = $this->db->table('steps_calories');
        $builder->select('*');
        $builder->where('DATE(count_date)', date('Y-m-d', strtotime($count_date)));
        $builder->where('count_type', $count_type);
        $builder->where('client_id', $client_id);
        $query = $builder->get();
        //echo $this->db->getLastQuery();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function add_steps_calories($insert_data)
    {
        $builder = $this->db->table('steps_calories');
        $builder->insert($insert_data);
        return $this->db->insertID();
    }
    public function update_steps_calories($update_data, $count_date, $count_type, $client_id)
    {
        $builder = $this->db->table('steps_calories');
        $builder->where('DATE(count_date)', date('Y-m-d', strtotime($count_date)));
        $builder->where('count_type', $count_type);
        $builder->where('client_id', $client_id);

        $builder->update($update_data);
        return 1;
    }
    public function last_steps_calories($client_id, $count_type)
    {
        $builder = $this->db->table('steps_calories');
        $builder->select('*');
        $builder->where('count_type', $count_type);
        $builder->where('client_id', $client_id);
        $builder->orderBy('count_date', 'DESC');
        $builder->limit(1);
        $query = $builder->get();
        //echo $this->db->getLastQuery();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function get_body_measurements($client_id)
    {
        $builder = $this->db->table('body_measurements');
        $builder->select('*');
        $builder->where('client_id', $client_id);
        $builder->where('status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function add_body_measurements($insert_data)
    {
        $builder = $this->db->table('body_measurements');
        $builder->insert($insert_data);
        return $this->db->insertID();
    }

    public function update_body_measurements($update_data, $client_id)
    {
        $builder = $this->db->table('body_measurements');
        $builder->where('client_id', $client_id);
        $builder->update($update_data);
        return 1;
    }
}
