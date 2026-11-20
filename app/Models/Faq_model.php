<?php

namespace App\Models;
use CodeIgniter\Model;

class Faq_model extends Model
{
    public function getFaqList(){
        $builder = $this->db->table('faq');
        $builder->select('*');
        $builder->where('status','Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        }else {
            return [];
        }
    }
}
