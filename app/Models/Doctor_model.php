<?php
namespace App\Models;
use CodeIgniter\Model;

class Doctor_model extends Model
{
    public function getDoctorList($tableName, $condition, $perPage = null, $offset = null)
    {

        //print_r($condition);

        $builder = $this->db->table($tableName);
        $builder->select('*');
        $builder->where($condition);
        //$builder->orderBy('B.booking_id', 'DESC');
        $builder->limit($perPage, $offset);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function addDoctor($insert_data, $language_id){
        
        //$builder = $this->db->table('users');
        //$builder->insert($user_data);
        //$users_id = $this->db->insertID();

        //$insert_data['users_id'] = $users_id;
        $builder = $this->db->table('doctor');
        $builder->insert($insert_data);
        $doctor_id = $this->db->insertID();

        //$updateData['doctor_id'] = $doctor_id;
        //$builder = $this->db->table('users');
        //$builder->where('uid', $users_id);
        //$builder->update($updateData);

        $totalLang = sizeof($language_id);
        for ($i=0; $i < $totalLang; $i++) { 
            $languageArray  = [
                'doctor_id' => $doctor_id,
                'language_id' => $language_id[$i],
            ];
            $builder = $this->db->table('doctor_language');
            $builder->insert($languageArray);
            $doctor_lang_id = $this->db->insertID();
        }

        if ($doctor_id > 0) {
            return 1;
        }else {
            return 0;            
        }
    }

    public function getDoctor($doctor_id){
        $builder = $this->db->table('doctor');
        $builder->select('*');
        //$builder->join('users AS U','U.doctor_id = D.doctor_id');
        $builder->where('doctor_id',$doctor_id);
        
        $query = $builder->get();

        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function adddoctor_language($languageArray, $doctor_id)
{
    // Delete existing languages for this doctor
    $dSql = "DELETE FROM doctor_language WHERE doctor_id = " . $doctor_id . "";
    $dQuery = $this->db->query($dSql);

    $roleArray = array();
    
    // Check if $languageArray is not null and not empty
    if (!empty($languageArray) && is_array($languageArray)) {
        foreach ($languageArray as $key => $val) {
            $roleArray[] = array("language_id" => $val, "doctor_id" => $doctor_id);
        }
    }

    // Check if roleArray has elements before inserting
    if (!empty($roleArray)) {
        $builder = $this->db->table('doctor_language');
        $builder->insertBatch($roleArray);
        if ($this->db->affectedRows() > 0) {
            return true;
        }
    }
    
    // Return true even if no languages were added (doctor might not have languages)
    return true;
}
}
