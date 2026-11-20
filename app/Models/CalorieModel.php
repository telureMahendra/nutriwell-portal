<?php

namespace App\Models;

use CodeIgniter\Model;

class CalorieModel extends Model
{
    protected $table = 'calorie';
    protected $primaryKey = 'calorie_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'client_id',
        'food_id',
        'food_type_id',
        'quantity',
        'calories',
        'date',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by'
    ];

    /* ============================
       Get Today's Total Calories
    ============================ */
    public function getTodayCalories($client_id, $date = null)
{
    $date = $date ?: date('Y-m-d');
    
    $builder = $this->db->table($this->table);
    $builder->select('SUM(calories * quantity) AS total_calories');
    $builder->where('client_id', $client_id);
    $builder->where('DATE(date)', $date);
    
    $result = $builder->get()->getRow();
    return $result ? (float) $result->total_calories : 0;
}

    /* ============================
       Get Today's Meals
    ============================ */
    public function getTodayMeals($client_id)
    {
        $today = date('Y-m-d');

        $builder = $this->db->table($this->table . ' AS C');
        $builder->select('
            C.*,
            F.food_name,
            F.calories AS food_calories,
            FT.food_type_name AS food_type
        ');
        $builder->join('food AS F', 'F.food_id = C.food_id', 'left');
        $builder->join('food_type AS FT', 'FT.food_type_id = C.food_type_id', 'left');
        $builder->where('C.client_id', $client_id);
        $builder->where('C.date >=', $today . ' 00:00:00');
        $builder->where('C.date <=', $today . ' 23:59:59');
        $builder->orderBy('C.date', 'ASC');

        $results = $builder->get()->getResultArray();

        return $this->formatMeals($results);
    }

    /* ============================
       Calorie History (Chart)
    ============================ */
    public function getCalorieHistory($client_id, $days = 7)
    {
        $end_date   = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-$days days"));

        $builder = $this->db->table($this->table);
        $builder->select('
            DATE(date) AS day_date,
            SUM(calories * quantity) AS total_calories,
            COUNT(*) AS meal_count
        ');
        $builder->where('client_id', $client_id);
        $builder->where('date >=', $start_date . ' 00:00:00');
        $builder->where('date <=', $end_date . ' 23:59:59');
        $builder->groupBy('DATE(date)');
        $builder->orderBy('DATE(date)', 'ASC');

        return $builder->get()->getResultArray();
    }

    /* ============================
       Meals By Date
    ============================ */
    public function getMealsByDate($client_id, $date)
{
    $builder = $this->db->table($this->table . ' AS C');
    $builder->select('
        C.*,
        F.food_name,
        F.calories AS food_calories,
        FT.food_type_name AS food_type
    ');
    $builder->join('food AS F', 'F.food_id = C.food_id', 'left');
    $builder->join('food_type AS FT', 'FT.food_type_id = C.food_type_id', 'left');
    $builder->where('C.client_id', $client_id);
    $builder->where('DATE(C.date)', $date);
    $builder->orderBy('C.date', 'ASC');
    
    $results = $builder->get()->getResultArray();
    return $this->formatMeals($results);
}

    /* ============================
       Daily Average Intake
    ============================ */
    public function getDailyAverageIntake($client_id, $days = 30)
{
    $end_date   = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime("-$days days"));

    $sql = "
        SELECT COUNT(*) AS day_count, SUM(total) AS sum_total FROM (
            SELECT DATE(date) AS day_date, SUM(calories * quantity) AS total
            FROM {$this->table}
            WHERE client_id = ?
            AND date BETWEEN ? AND ?
            GROUP BY DATE(date)
        ) daily
    ";

    $query = $this->db->query(
        $sql,
        [$client_id, $start_date . ' 00:00:00', $end_date . ' 23:59:59']
    );

    $row = $query->getRow();

    if (!$row || (int)$row->day_count === 0) {
        return 0; // ✅ PREVENT division by zero
    }

    return round($row->sum_total / $row->day_count);
}


    /* ============================
       Add Calorie Intake
    ============================ */
    public function addCalorieIntake(array $data)
    {
        $insertData = [
            'client_id'     => $data['client_id'],
            'food_id'       => $data['food_id'] ?? 0,
            'food_type_id'  => $data['food_type_id'] ?? 1,
            'calories'      => $data['calories'] ?? 0,
            'quantity'      => $data['quantity'] ?? 1,
            'date'          => $data['date'] ?? date('Y-m-d H:i:s'),
            'created_on'    => date('Y-m-d H:i:s'),
            'created_by'    => $data['created_by'] ?? 0,
            'updated_on'    => date('Y-m-d H:i:s'),
            'updated_by'    => $data['updated_by'] ?? 0
        ];

        return $this->db->table($this->table)->insert($insertData);
    }

    /* ============================
       Format Meals (Reusable)
    ============================ */
    private function formatMeals(array $results): array
    {
        $formatted = [];

        foreach ($results as $row) {
            $totalCalories = (float) $row['calories'] * (float) $row['quantity'];

            $formatted[] = [
                'calorie_id'          => $row['calorie_id'],
                'client_id'           => $row['client_id'],
                'food_id'             => $row['food_id'],
                'food_type_id'        => $row['food_type_id'],
                'food_name'           => $row['food_name'] ?? 'Unknown Food',
                'food_type'           => $row['food_type'] ?? 'Meal',
                'quantity'            => (float) $row['quantity'],
                'calories_per_unit'   => (float) $row['calories'],
                'total_calories'      => $totalCalories,
                'date'                => $row['date'],
                'created_on'          => $row['created_on'],
                'meal_time'           => date('h:i A', strtotime($row['date']))
            ];
        }

        return $formatted;
    }
}
