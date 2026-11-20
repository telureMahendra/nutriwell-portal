<?php

namespace App\Models;

use CodeIgniter\Model;

class Client_login_model extends Model
{
    protected $table = 'client_login'; // Replace with your actual table name

    protected $allowedFields = ['client_id', 'client_phone', 'client_status', 'is_login', 'profile_complete'];

    public function checkIfRegistered($mobile_number)
    {
        $builder = $this->db->table('client_login');
        $builder->select('*');
        $builder->where('client_phone', $mobile_number);

        if ($builder->countAllResults() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function insertClientMobile($data)
    {
        $builder = $this->db->table('client_login');
        $builder->insert($data);
        return $this->db->insertID();
    }

    public function checkIfLogin($mobile_number)
    {
        $builder = $this->db->table('client_login');
        $builder->select('*');
        $builder->where('client_phone', $mobile_number);
        $builder->where('client_status', 'Active');
        $builder->where('is_login', 'Yes');
        $query = $builder->get();
        if ($query->getNumRows() > 0) {
            return 0;
        } else {
            return 1;
        }
    }

    // public function sendOtp($data_insert)
    // {
    //     $builder = $this->db->table('client_otp_verification');

    //     if($data_insert["mobile_number"] == "9057489487"){
    //         $data_insert["otp"] =  9770;
    //     }


    //     $builder->insert($data_insert);
    //     if ($this->db->insertID() > 0) {
    //         $message = 'Your Healthie4U APP login OTP is ' . $data_insert["otp"] . '. ' . PHP_EOL . 'Please do not share this OTP with anyone. ' . PHP_EOL . PHP_EOL . 'Regards, KAIZEN GLOBAL SERVICES.';
    //         sendSms($data_insert["mobile_number"], $message);

    //         return $this->db->insertID();
    //     } else {
    //         return 0;
    //     }
    // }

    // public function sendOtp($data_insert)
    // {
    //     $builder = $this->db->table('client_otp_verification');

    //     if($data_insert["mobile_number"] == "9057489487"){
    //         $data_insert["otp"] =  9770;
    //     }


    //     $builder->insert($data_insert);

    //     if ($this->db->insertID() > 0) {

    //         // Call 2Factor API
    //         $response = sendOtpVia2Factor(
    //             $data_insert["mobile_number"],
    //             $data_insert["otp"]
    //         );

    //         return $this->db->insertID();
    //     } else {
    //         return 0;
    //     }
    // }

    public function sendOtp($data_insert)
    {
        $builder = $this->db->table('client_otp_verification');

        // Generate OTP
        if ($data_insert["mobile_number"] == "9988776655") {
            $data_insert["otp"] = 1234; // test number
        } else {
            $data_insert["otp"] = rand(1000, 9999);
        }

        $builder->insert($data_insert);

        if ($this->db->insertID() > 0) {

            // Call 2Factor API
            $response = sendOtpVia2Factor(
                $data_insert["mobile_number"],
                $data_insert["otp"]
            );

            return $this->db->insertID();
        } else {
            return 0;
        }
    }

    public function verifyotp_bkp($otp_id, $otp, $mobile_number)
    {
        $date = date('Y-m-d H:i:s');

        $builder = $this->db->table('client_otp_verification');
        $builder->select('otp', 'otp_id', 'mobile_number');
        $builder->where('otp_id', $otp_id);
        $builder->where('mobile_number', $mobile_number);
        $builder->where('otp', $otp);
        $builder->where('end_time >', $date);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return array();
        }
    }
    public function verifyotp($otp_id, $otp, $mobile_number)
    {
        $date = date('Y-m-d H:i:s');

        $builder = $this->db->table('client_otp_verification');
        $builder->select('otp, otp_id, mobile_number');
        $builder->where('otp_id', $otp_id);
        $builder->where('mobile_number', $mobile_number);
        $builder->where('otp', $otp);
        //$builder->where('end_time >', $date);
        $builder->orderBy('otp_id', 'DESC');

        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return array();
        }
    }

    public function getUserId($mobile_number)
    {
        $builder = $this->db->table('client_login');
        $builder->select('client_id');
        $builder->where('client_phone', $mobile_number);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return array();
        }
    }
    public function addTocken($data_insert)
    {
        $builder = $this->db->table('client_tockens');
        $builder->insert($data_insert);
        return $this->db->insertID();
    }
    public function checkotp($otp_id, $otp)
    {
        $date = date('Y-m-d H:i:s');
        $builder = $this->db->table('client_otp_verification');
        $builder->select('otp_id, otp, mobile_number');
        $builder->where('otp_id', $otp_id);
        $builder->where('otp', $otp);
        $query = $builder->get();
        if ($query->getResult()) {
            return 1;
        } else {
            return 0;
        }
    }

    public function updateSentOtp($data_update, $otp_id, $old_otp)
    {
        $date = date('Y-m-d H:i:s');
        $builder = $this->db->table('client_otp_verification');

        $builder->where('otp_id', $otp_id);
        $builder->where('otp', $old_otp);
        $builder->update($data_update);
        $builder->limit(1);
        $result = $builder->get();
        // Check the affected rows count
        $affectedRows = $result->connID->affected_rows;
        if ($affectedRows > 0) {
            return 1; // Update successful
        } else {
            return 0; // Update failed
        }
    }
    public function checkTocken($tocken)
    {
        $builder = $this->db->table('client_tockens');
        $builder->select('*');
        $builder->where('tocken_generated', $tocken);
        $builder->where('tocken_status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function deleteTocken($data_update, $tocken_generated, $client_id)
    {
        $builder = $this->db->table('client_tockens');
        $builder->where('tocken_generated', $tocken_generated);
        $builder->where('client_id', $client_id);
        $builder->update($data_update);
        $result = $builder->get();

        $affectedRows = $result->connID->affected_rows;
        if ($affectedRows > 0) {
            return 1; // Update successful
        } else {
            return 0; // Update failed
        }
    }


    public function updateFcmToken($mobile_number, $fcm_token)
    {
        $date = date('Y-m-d H:i:s');
        $builder = $this->db->table('client_login');

        $builder->where('client_phone', $mobile_number);
        $builder->update(array("fcm_token" => $fcm_token));
        $builder->limit(1);
        $result = $builder->get();
        // Check the affected rows count
        $affectedRows = $result->connID->affected_rows;
        if ($affectedRows > 0) {
            return 1; // Update successful
        } else {
            return 0; // Update failed
        }
    }
    public function getnotifications($client_id)
    {
        $sql = "
            SELECT
                notification_title  AS title,
                description         AS message,
                NULL                AS image_url,
                created_on          AS created_at
            FROM notification
            WHERE client_id = ?

            UNION ALL

            SELECT
                n.title,
                n.message,
                n.image_url,
                n.created_at
            FROM notifications n
            INNER JOIN notification_audience na ON na.notification_id = n.id
            WHERE n.status = 'sent'
              AND (
                na.audience_type = 0
                OR (na.audience_type = 1 AND EXISTS (
                      SELECT 1 FROM booking WHERE client_id = ? AND status = 'Active'))
                OR (na.audience_type = 2 AND NOT EXISTS (
                      SELECT 1 FROM booking WHERE client_id = ? AND status = 'Active'))
                OR (na.audience_type = 3 AND JSON_CONTAINS(na.client_ids, JSON_QUOTE(?)))
              )

            ORDER BY created_at DESC
        ";

        $query = $this->db->query($sql, [$client_id, $client_id, $client_id, $client_id]);
        return $query->getResult() ?: [];
    }


    public function deleteClientData($client_id)
    {

        $this->db->transBegin();
        try {

            $this->db->table('client_tockens')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('client_profile')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('client_login')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('client_medical_condition')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('weight')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('water')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('steps_calories')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('calorie')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('booking')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('appointment')
                ->where('client_id', $client_id)
                ->delete();

            $this->db->table('testimonial')
                ->where('client_id', $client_id)
                ->delete();


            if ($this->db->transStatus() === false) {

                $this->db->transRollback();
                return false;
            } else {
                $this->db->transCommit();
                return true;
            }

        } catch (\Exception $e) {

            $this->db->transRollback();
            return false;
        }
    }


}
