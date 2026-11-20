<?php

namespace App\Models;

use CodeIgniter\Model;

class MeditationModel extends Model
{
    protected $table = 'meditation_tracker';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id',
        'date',
        'hours',
        'minutes',
        'start_time',
        'end_time',
        'created_on',
        'created_at',
        'created_by',
        'updated_on',
        'updated_at',
        'updated_by'
    ];

    public function getMeditationByDate($client_id, $date)
    {
        return $this->where('client_id', $client_id)
            ->where('date', $date)
            ->first();
    }

    public function getMeditationRange($client_id, $start, $end)
    {
        return $this->where('client_id', $client_id)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->orderBy('date', 'ASC')
            ->findAll();
    }
}
