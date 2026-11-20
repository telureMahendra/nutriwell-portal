<?php

namespace App\Models;

use CodeIgniter\Model;

class HealthTrackerModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // === WATER ===
    public function upsertWater($clientId, $date, $data)
    {
        $builder = $this->db->table('water');
        $exists = $builder->where(['client_id' => $clientId, 'date' => $date])->get()->getRowArray();

        $exists ? $builder->where(['client_id' => $clientId, 'date' => $date])->update($data)
            : $builder->insert($data);
    }

    // === CALORIES ===
    public function upsertCalorie($clientId, $date, $data)
    {
        $builder = $this->db->table('calorie');
        $exists = $builder->where([
            'client_id' => $clientId,
            'date' => $date,
            'food_id' => $data['food_id'],
            'food_type_id' => $data['food_type_id']
        ])->get()->getRowArray();

        $exists ? $builder->where([
            'client_id' => $clientId,
            'date' => $date,
            'food_id' => $data['food_id'],
            'food_type_id' => $data['food_type_id']
        ])->update($data)
            : $builder->insert($data);
    }

    // === STEPS ===
    public function upsertSteps($clientId, $date, $data)
    {
        $builder = $this->db->table('steps');
        $exists = $builder->where(['client_id' => $clientId, 'date' => $date])->get()->getRowArray();

        $exists ? $builder->where(['client_id' => $clientId, 'date' => $date])->update($data)
            : $builder->insert($data);
    }

    // === SLEEP ===
    public function upsertSleep($clientId, $date, $data)
    {
        $builder = $this->db->table('sleep');
        $exists = $builder->where(['client_id' => $clientId, 'date' => $date])->get()->getRowArray();

        $exists ? $builder->where(['client_id' => $clientId, 'date' => $date])->update($data)
            : $builder->insert($data);
    }

    // === WORKOUT ===
    public function upsertWorkout($clientId, $date, $data)
    {
        $builder = $this->db->table('workout');
        $exists = $builder->where(['client_id' => $clientId, 'date' => $date])->get()->getRowArray();

        $exists ? $builder->where(['client_id' => $clientId, 'date' => $date])->update($data)
            : $builder->insert($data);
    }

    // === BMI ===
    public function upsertBMI($clientId, $date, $data)
    {
        $builder = $this->db->table('bmi');
        $exists = $builder->where(['client_id' => $clientId, 'date' => $date])->get()->getRowArray();

        $exists ? $builder->where(['client_id' => $clientId, 'date' => $date])->update($data)
            : $builder->insert($data);
    }
}
