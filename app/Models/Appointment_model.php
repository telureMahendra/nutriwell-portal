<?php

namespace App\Models;

use CodeIgniter\Model;

class Appointment_model extends Model
{
    public function getActiveBooking($client_id)
    {
        $builder = $this->db->table('booking');
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
    public function getLastAppointment($client_id, $date)
    {
        $startDt = date('Y-m-d', strtotime($date . ' - 6 days'));
        $endDt = date('Y-m-d', strtotime($date . ' + 6 days'));

        $builder = $this->db->table('appointment');
        $builder->select('*');
        $builder->where('client_id', $client_id);
        $builder->where('date >=', $startDt);
        $builder->where('date <=', $endDt);
        $builder->whereIn('status', ['Upcoming','Completed']);
        $builder->orderBy('appointment_id', 'DESC');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function getBookingDocter($doctor_id)
    {
        $builder = $this->db->table('doctor');
        $builder->select('*');
        $builder->where('doctor_id', $doctor_id);
        $builder->where('status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function addAppointment($insert_data)
    {
        $builder = $this->db->table('appointment');
        $builder->insert($insert_data);
        return $this->db->insertID();
    }
    public function getAllAppointment($status, $client_id, $fromdate, $todate, $search)
    {
        $builder = $this->db->table('appointment AS A');
        $builder->select('A.*, D.doctor_name, D.image,D.designation,D.month,D.years');
        $builder->join('doctor AS D', 'D.doctor_id = A.doctor_id');

        $builder->where('A.client_id', $client_id);
        $builder->where('A.status', $status);

        if ($fromdate != '' && $todate != '') {
            $builder->where('DATE(A.date) >=', $fromdate);
            $builder->where('DATE(A.date) <=', $todate);
        }
        if ($search != '') {
            $builder->like('D.doctor_name', $search);
        }

        $builder->orderBy('A.appointment_id', 'DESC');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function getAppointment($appointment_id)
    {
        $builder = $this->db->table('appointment AS A');
        $builder->select('A.*, D.doctor_name, D.image');
        $builder->Join('doctor AS D', 'D.doctor_id = A.doctor_id');
        $builder->where('A.appointment_id', $appointment_id);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    public function updateAppointment($insert_data, $appointment_id)
    {
        $builder = $this->db->table('appointment');
        $builder->where('appointment_id', $appointment_id);
        $builder->limit(1);
        $builder->update($insert_data);
        if ($this->db->affectedRows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getDoctorAppointment($client_id, $condition, $like, $perPage = null, $offset = null)
{
    $builder = $this->db->table('appointment AS A');
    $builder->select('A.*,CP.client_name,CP.client_mobile_number,P.name,D.doctor_name,D.designation,D.years,D.month');
    $builder->join('booking AS B', 'B.booking_id = A.booking_id', 'left');
    $builder->join('client_profile AS CP', 'CP.client_id = A.client_id', 'left');
    $builder->join('plan AS P', 'P.plan_id = B.plan_id', 'left');
    $builder->join('doctor AS D', 'D.doctor_id = A.doctor_id', 'left');
    
    // FIX: ADD CLIENT_ID CONDITION
    //$builder->where('A.client_id', $client_id);
    
    // Add other conditions
    if (!empty($condition)) {
        $builder->where($condition);
    }
    
    if (!empty($like)) {
        $builder->groupStart();
        $builder->orLike('CP.client_name', $like['CP.client_name']);
        $builder->orLike('CP.client_mobile_number', $like['CP.client_mobile_number']);
        $builder->orLike('D.doctor_name', $like['D.doctor_name']);
        $builder->orLike('P.name', $like['P.name']);
        $builder->orLike('A.slot', $like['A.slot']);
        $builder->groupEnd();
    }
    
    $builder->groupBy('A.appointment_id');
    $builder->orderBy('A.appointment_id', 'DESC');
    
    if ($perPage !== null) {
        $builder->limit($perPage, $offset);
    }
    
    $query = $builder->get();
    
    // Debug: Uncomment to see the query
    // echo $builder->getCompiledSelect(); die();
    
    if ($query->getResult()) {
        return $query->getResult();
    } else {
        return [];
    }
}
public function getAllAppointmentsForAdmin($condition = [], $like = [], $perPage = null, $offset = null)
{
    $builder = $this->db->table('appointment AS A');
    $builder->select('A.*, CP.client_name, CP.client_mobile_number, P.name, D.doctor_name, D.designation, D.years, D.month');
    $builder->join('booking AS B', 'B.booking_id = A.booking_id', 'left');
    $builder->join('client_profile AS CP', 'CP.client_id = A.client_id', 'left');
    $builder->join('plan AS P', 'P.plan_id = B.plan_id', 'left');
    $builder->join('doctor AS D', 'D.doctor_id = A.doctor_id', 'left');
    
    // Add conditions only if they exist
    if (!empty($condition)) {
        foreach ($condition as $key => $value) {
            // Handle special cases for date comparisons
            if (strpos($key, '>=') !== false || strpos($key, '<=') !== false || strpos($key, '!=') !== false) {
                // For operators like >=, <=, !=
                $builder->where($key, $value);
            } else {
                // For regular conditions
                $builder->where($key, $value);
            }
        }
    } else {
        // If no conditions, add a default condition to avoid empty WHERE clause
        $builder->where('A.appointment_id >', 0);
    }
    
    // Add search conditions
    if (!empty($like)) {
        $builder->groupStart();
        foreach ($like as $key => $value) {
            if (!empty($value)) {
                $builder->orLike($key, $value);
            }
        }
        $builder->groupEnd();
    }
    
    $builder->groupBy('A.appointment_id');
    $builder->orderBy('A.appointment_id', 'DESC');
    
    if ($perPage !== null) {
        $builder->limit($perPage, $offset);
    }
    
    // Debug: Uncomment to see the generated SQL
    // echo $builder->getCompiledSelect(); die();
    
    $query = $builder->get();
    
    if ($query->getResult()) {
        return $query->getResult();
    } else {
        return [];
    }
}

    public function getAppointmentSlot($doctor_id, $date)
    {
        $builder = $this->db->table('appointment AS A');
        $builder->select('A.*');
        $builder->where('A.doctor_id', $doctor_id);
        $builder->where('A.date', $date);
        $builder->where('A.status !=', 'Cancelled');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getSlot()
    {
        $builder = $this->db->table('slots');
        $builder->select('time');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getAppointmentDetails($appointment_id)
    {
        $builder = $this->db->table('appointment AS A');
        $builder->select('A.*, CP.client_name, CP.age, CP.height, CP.height_in, CP.weight, CP.diet, CP.client_mobile_number, CP.client_gender, L.name AS lifestyle, G.name AS goal, P.name AS planname, P.category');
        $builder->Join('client_profile AS CP', 'CP.client_id = A.client_id', 'left');
        $builder->Join('lifestyle AS L', 'L.lifestyle_id = CP.lifestyle_id', 'left');
        $builder->Join('goal AS G', 'G.goal_id = CP.goal_id', 'left');
        $builder->Join('booking AS B', 'B.booking_id = A.booking_id', 'left');
        $builder->Join('plan AS P', 'P.plan_id = B.plan_id', 'left');
        $builder->where('A.appointment_id', $appointment_id);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }


    public function getAppointmentComment($appointment_id)
    {
        $builder = $this->db->table('appointmentcomment AS A');
        $builder->select('A.*,D.username as doctoruser,D.doctor_name,U.username,U.first_name,U.last_name');
        $builder->Join('doctor AS D', 'D.doctor_id = A.doctor_id', 'left');
        $builder->Join('users AS U', 'U.uid = A.user_id', 'left');
        $builder->where('A.appointment_id', $appointment_id);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
}
