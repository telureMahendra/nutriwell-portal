<?php
namespace App\Models;
use CodeIgniter\Model;

class Sleep_model extends Model
{
    protected $table = 'sleep_tracker';
    protected $primaryKey = 'sleep_id';
    protected $allowedFields = [
        'client_id',
        'date',
        'hours',
        'minutes',
        'start_time',
        'end_time',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by'
    ];

    public function getSleepByDate($client_id, $date)
    {
        return $this->where('client_id', $client_id)
            ->where('date', $date)
            ->first();
    }

    public function getSleepRange($client_id, $start, $end)
    {
        return $this->where('client_id', $client_id)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->orderBy('date', 'ASC')
            ->findAll();
    }
}
