<?php 
namespace App\Models;

use CodeIgniter\Model;

class StepsCaloriesModel extends Model
{
    protected $table = 'steps_calories';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'count_date', 'data_count', 'count_type', 
        'client_id', 'created_on', 'created_by', 'updated_on', 'updated_by'
    ];
    
    protected $useTimestamps = false;
    
    /**
     * Get calories data for client
     */
    public function getClientCalories($clientId, $limit = 30)
    {
        $builder = $this->db->table('steps_calories sc');
        $builder->select('sc.*');
        $builder->where('sc.client_id', $clientId);
        $builder->where('sc.count_type', 1); // 1 = calories
        $builder->orderBy('sc.count_date', 'DESC');
        $builder->limit($limit);
        
        return $builder->get()->getResult();
    }
    
    /**
     * Get total calories burned in period
     */
    public function getTotalCalories($clientId, $startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-01'); // Start of month
        }
        if (!$endDate) {
            $endDate = date('Y-m-d');
        }
        
        $builder = $this->db->table('steps_calories');
        $builder->select('SUM(data_count) as total_calories');
        $builder->where('client_id', $clientId);
        $builder->where('count_type', 1);
        $builder->where("DATE(count_date) >= ", $startDate);
        $builder->where("DATE(count_date) <= ", $endDate);
        
        $result = $builder->get()->getRow();
        
        return (int)($result->total_calories ?? 0);
    }
    
    /**
     * Save calories record
     */
    public function saveCalories($clientId, $date, $calories, $userId)
    {
        $data = [
            'count_date' => $date . ' 12:00:00',
            'data_count' => $calories,
            'count_type' => 1, // 1 = calories
            'client_id' => $clientId,
            'created_on' => date('Y-m-d'),
            'created_by' => $userId,
            'updated_on' => date('Y-m-d'),
            'updated_by' => $userId
        ];
        
        // Check if record exists for this date
        $existing = $this->where([
            'client_id' => $clientId,
            'DATE(count_date)' => $date,
            'count_type' => 1
        ])->first();
        
        if ($existing) {
            // Update existing record
            return $this->update($existing->id, $data);
        } else {
            // Insert new record
            return $this->insert($data);
        }
    }
}