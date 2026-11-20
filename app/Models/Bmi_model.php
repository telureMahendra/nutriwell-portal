<?php
namespace App\Models;
use CodeIgniter\Model;

class Bmi_model extends Model
{
    protected $table = 'bmi_record';
    protected $primaryKey = 'bmi_record_id';
    protected $allowedFields = [
        'client_id',
        'date',
        'age',
        'height',
        'height_unit',
        'weight',
        'bmi_value',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by'
    ];

    public function getBmiByDate($client_id, $date)
    {
        return $this->where('client_id', $client_id)
            ->where('date', $date)
            ->first();
    }

    public function getBmiRange($client_id, $start, $end)
    {
        return $this->where('client_id', $client_id)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->orderBy('date', 'ASC')
            ->findAll();
    }
}
