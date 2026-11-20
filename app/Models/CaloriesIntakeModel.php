<?php 
namespace App\Models;

use CodeIgniter\Model;

class CaloriesIntakeModel extends Model
{
    protected $table = 'calories_intake';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id', 'date', 'calories', 'food_item', 'meal_type', 
        'quantity', 'unit', 'notes', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get today's total calories intake for a client
     */
    public function getTodayIntake($clientId)
    {
        $today = date('Y-m-d');
        
        $builder = $this->db->table($this->table);
        $builder->selectSum('calories', 'total_calories');
        $builder->where('client_id', $clientId);
        $builder->where('date', $today);
        
        $query = $builder->get();
        $result = $query->getRow();
        
        return $result ? (float)$result->total_calories : 0;
    }
    
    /**
     * Get intake history
     */
    public function getIntakeHistory($clientId, $days = 30)
    {
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime("-$days days"));
        
        $builder = $this->db->table($this->table);
        $builder->select("
            date,
            SUM(calories) as total_calories,
            COUNT(*) as food_items_count,
            GROUP_CONCAT(food_item SEPARATOR ', ') as food_items
        ");
        $builder->where('client_id', $clientId);
        $builder->where("date BETWEEN '$start_date' AND '$end_date'");
        $builder->groupBy('date');
        $builder->orderBy('date', 'ASC');
        
        return $builder->get()->getResultArray();
    }
}