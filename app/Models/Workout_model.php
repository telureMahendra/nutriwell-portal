<?php
namespace App\Models;
use CodeIgniter\Model;

class Workout_model extends Model
{
    protected $table = 'workout_tracker';
    protected $primaryKey = 'workout_id';
    protected $allowedFields = [
        'client_id',
        'date',
        'workout_name',
        'hours',
        'minutes',
        'start_time',
        'end_time',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by'
    ];

    public function getWorkoutByDate($client_id, $date)
    {
        return $this->where('client_id', $client_id)
            ->where('date', $date)
            ->first();
    }

    public function getWorkoutRange($client_id, $start, $end)
    {
        return $this->where('client_id', $client_id)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->orderBy('date', 'ASC')
            ->findAll();
    }
}
