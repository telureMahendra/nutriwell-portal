<?php
namespace App\Models;
use CodeIgniter\Model;

class Calories_burned_model extends Model
{
    protected $table = 'calories_burned';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id',
        'date',
        'from_source',
        'calories_burned',
        'created_at',
        'updated_at',

    ];

    public function getBurnedByDate($client_id, $date)
    {
        return $this->where('client_id', $client_id)
            ->where('date', $date)
            ->findAll();
    }

    public function getBurnedRange($client_id, $start, $end)
    {
        return $this->select('date, SUM(calories_burned) as total_burned')
            ->where('client_id', $client_id)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->findAll();
    }
}
