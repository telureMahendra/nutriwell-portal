<?php
namespace App\Models;
use CodeIgniter\Model;

class Calorie_model extends Model
{

    protected $table = 'calorie'; // ✅
    protected $primaryKey = 'calorie_id';
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

    public function getCalorie($date, $client_id)
    {
        $builder = $this->db->table('calorie');
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

    public function getFoodType()
    {
        $builder = $this->db->table('food_type');
        $builder->select('*');
        $builder->where('status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getFoodCalorie($date, $client_id, $food_type_id)
    {
        $builder = $this->db->table('calorie AS C');
        $builder->select('C.*, F.food_name,F.calories,F.unit');
        $builder->join('food AS F', 'F.food_id = C.food_id');
        $builder->where('C.client_id', $client_id);
        $builder->where('C.food_type_id', $food_type_id);
        $builder->where("DATE(C.date) = '$date'");
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getFoodList($food_type_id, $search)
    {
        $builder = $this->db->table('food AS F');
        $builder->select('F.*');

        // $builder->where('F.food_type_id', $food_type_id);

        if ($search != '') {
            $builder->like('F.food_name', $search);
        }
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getFoodAllList($search)
    {

        $builder = $this->db->table('food AS F');
        $builder->select('F.*');

        // $builder->where('F.food_type_id', $food_type_id);

        if ($search != '') {
            $builder->like('F.food_name', $search, 'both');
        }
        $builder->where('status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getFoodCal($food_id)
    {
        $builder = $this->db->table('food AS F');
        $builder->select('F.*');
        $builder->where('F.food_id', $food_id);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function addFood($insert_data)
    {
        $builder = $this->db->table('calorie');
        $builder->insert($insert_data);
        return $this->db->insertID();
    }
    public function removeFood($calorie_id)
    {
        $builder = $this->db->table('calorie');
        $builder->where('calorie_id', $calorie_id);
        $builder->delete();
        if ($this->db->affectedRows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    // public function removeFoodbkp($calorie_id)
    // {
    //     $builder = $this->db->table('calorie');
    //     $builder->where('calorie_id', $calorie_id);
    //     if ($db->affectedRows() > 0) {
    //         return 1;
    //     } else {
    //         return 0;
    //     }
    // }

    public function getCalorieSummary($client_id, $date)
    {
        return $this->select('SUM(COALESCE(calorie.calories, food.calories * calorie.quantity)) as total_calories, calorie.date')
            ->join('food', 'food.food_id = calorie.food_id', 'left')
            ->where('calorie.client_id', $client_id)
            ->where('calorie.date', $date)
            ->groupBy('calorie.date')
            ->first();
    }

    public function getFoodByDate($client_id, $date)
    {
        return $this->select('
            calorie.date,
            calorie.food_id,
            calorie.food_type_id,
            calorie.quantity,
            COALESCE(calorie.calories, food.calories * calorie.quantity) as calories,
            food.calories as single_quantity_calories,
            food.food_name
        ')
            ->join('food', 'food.food_id = calorie.food_id', 'left')
            ->where('calorie.client_id', $client_id)
            ->where('calorie.date', $date)
            ->findAll();
    }

    public function getFoodByDateRange($client_id, $start_date, $end_date)
    {
        return $this->select('
            calorie.date,
            calorie.food_id,
            calorie.food_type_id,
            calorie.quantity,
            COALESCE(calorie.calories, food.calories * calorie.quantity) as calories,
            food.calories as single_quantity_calories,
            food.food_name
        ')
            ->join('food', 'food.food_id = calorie.food_id', 'left')
            ->where('calorie.client_id', $client_id)
            ->where('calorie.date >=', $start_date)
            ->where('calorie.date <=', $end_date)
            ->orderBy('calorie.date', 'ASC')
            ->findAll();
    }



}