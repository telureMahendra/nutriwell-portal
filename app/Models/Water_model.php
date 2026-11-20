<?php
namespace App\Models;
use CodeIgniter\Model;

class Water_model extends Model
{
    protected $table = 'water';
    protected $primaryKey = 'water_id';
    protected $allowedFields = [
        'client_id',
        'date',
        'quantity',
        'target_quantity',
        'logs',
        'unit',
        'notes',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by'
    ];


    // protected $primaryKey = 'water_id';
    // protected $allowedFields = ['client_id', 'quantity', 'date', 'created_on']

    public function getWater($date, $client_id)
    {
        $builder = $this->db->table('water');
        $builder->select('*');
        $builder->where('client_id', $client_id);
        $builder->where("DATE(date) = DATE('$date')");
        $query = $builder->get();

        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function updateWater($date, $client_id, $updateData)
    {
        $builder = $this->db->table('water');
        $builder->where('client_id', $client_id);
        $builder->where("DATE(date) = DATE('$date')");
        $builder->update($updateData);

        return $this->db->affectedRows();
    }

    public function addWater($insertData)
    {
        $builder = $this->db->table('water');
        $builder->insert($insertData);
        return $this->db->insertID();
    }

    public function getWeekWater($date, $client_id)
    {
        $builder = $this->db->table('water');
        $builder->select('*');
        $builder->where('client_id', $client_id);
        $builder->where("DATE(date) = '$date'");

        $query = $builder->get();

        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    // New method to get weekly average
    public function getWeeklyAverage($client_id, $startDate, $endDate)
    {
        $builder = $this->db->table('water');
        $builder->select('AVG(quantity) as weekly_avg');
        $builder->where('client_id', $client_id);
        $builder->where("date BETWEEN '$startDate' AND '$endDate'");

        $query = $builder->get();
        $result = $query->getRow();

        return $result ? round($result->weekly_avg, 2) : 0;
    }

    // public function getWaterIntake($client_id, $date)
    // {
    //     return $this->select('SUM(quantity) as total_glass, created_on')
    //         ->where('client_id', $client_id)
    //         ->where('created_on', $date)
    //         ->groupBy('created_on')
    //         ->first();
    // }


    public function getWaterIntake($client_id, $date)
    {
        return $this->select('date, SUM(quantity) as total_glass')
            ->where('client_id', $client_id)
            ->where('date', $date)
            ->groupBy('date')
            ->first();
    }


}