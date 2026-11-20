<?php
namespace App\Models;
use CodeIgniter\Model;

class Steps_model extends Model
{
    protected $table = 'steps_tracker';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id',
        'date',
        'steps',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];

    public function getStepsByDate($client_id, $date)
    {
        return $this->where('client_id', $client_id)
            ->where('date', $date)
            ->first();
    }

    public function getStepsRange($client_id, $start, $end)
    {
        return $this->select('date, SUM(steps) as total_steps')
            ->where('client_id', $client_id)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->findAll();
    }
}
