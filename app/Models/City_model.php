<?php

namespace App\Models;

use CodeIgniter\Model;

class City_model extends Model
{
    public function getCityList($tableName, $condition, $perPage = null, $offset = null)
    {
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
}