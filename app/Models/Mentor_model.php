<?php

namespace App\Models;

use CodeIgniter\Model;

class Mentor_model extends Model
{
    protected $table = 'doctor'; // Replace 'doctor' with your actual table name

    public function check_login($username, $password)
    {
        $builder = $this->db->table('doctor'); // Replace 'doctor' with your actual table name

        $builder->select('doctor_id, username,doctor_name, phone_number, designation');
        $builder->where('username', $username);
        $builder->where('pass', md5($password));
        $builder->where('status', 'active');
        $builder->limit(1);

        $query = $builder->get();
            // echo  $this->db->getLastQuery();;die;
        // echo "<pre>";print_r($query);die();
        if ($query->getNumRows() > 0) {
            $row = $query->getRow();

            $arr_session = [
                "doctor_id"   => $row->doctor_id,
                "userid"      => $row->doctor_id,   // is_doctor_logged_in() checks userid > 0
                "username"    => $row->username,
                "fullname"    => $row->doctor_name,
                "phone_number"=> $row->phone_number,
                "designation" => $row->designation,
                "usertype"    => "mentor",
                "userrole"    => $row->designation, // must match 'Doctor'|'Dietitian'|'Psychologist'|'Fitness Trainer'
            ];

            $session = session();
            $session->set($arr_session);
            //var_dump($arr_session);
            return true;
        } else {
            // echo "Login failed.";
            return false;
        }
    }
   
    public function add_doctor_log($data)
    {
        $builder = $this->db->table('doctor_logs'); // Replace 'user_logs' with your actual table name

        $builder->insert($data);

        return $this->db->insertID(); // Use insertID() to retrieve the insert ID
    }


    public function change_password($userid, $old_pwd, $new_pwd)
    {
        $data = [
            'password' => md5($new_pwd),
        ];

        $builder = $this->db->table($this->table);
        $builder->where('id', $userid);
        $builder->where('password', md5($old_pwd));

        $query = $builder->get();

        if ($query->getNumRows() > 0) {
            $builder->where('id', $userid);
            $builder->update($data);

            if (isset($_COOKIE['user_pwd'])) {
                setcookie('user_pwd', $new_pwd, time() + (30 * 60 * 60 * 24));
            }

            return true;
        } else {
            return false;
        }
    }
}
