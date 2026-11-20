<?php

namespace App\Models;
use CodeIgniter\Model;

class Banners_model extends Model
{
    protected $table = 'banners';
    protected $primaryKey = 'banner_id';
    protected $allowedFields = ['title', 'banner_image', 'status', 'created_at', 'updated_at'];

    public function getAllBanners()
    {
        return $this->where('status', 'Active')->findAll();
    }
}
