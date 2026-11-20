<?php

namespace App\Models;
use CodeIgniter\Model;

class Firm_model extends Model
{
    public function getcontactDetails(){
        $builder = $this->db->table('company_firms');
        $builder->select('firm_mobile,logo_image,firm_email,firm_address');
        $builder->where('status','Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        }else {
            return [];
        }
    }
}
