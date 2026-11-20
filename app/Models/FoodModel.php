<?php

namespace App\Models;

use CodeIgniter\Model;

class FoodModel extends Model
{
    protected $table = 'food';
    protected $primaryKey = 'food_id';
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'food_name',
        'calories',
        'protein',
        'carbs',
        'fat',
        'serving_size',
        'serving_unit',
        'food_type_id',
        'is_active',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by'
    ];
    
    protected $useTimestamps = false;
    
    /**
     * Get food list with pagination (Your existing method)
     */
    public function getFoodList($tableName, $condition, $perPage = null, $offset = null, $sort = 'food_name', $order = 'asc')
    {
        $builder = $this->db->table($tableName);
        $builder->select('*');
        $builder->where($condition);
        
        // Apply sorting
        if (in_array($sort, ['food_name', 'calories', 'created_on'])) {
            $builder->orderBy($sort, $order);
        } else {
            $builder->orderBy('food_name', 'asc');
        }
        
        // Only apply limit and offset if provided
        if ($perPage !== null && $offset !== null) {
            $builder->limit($perPage, $offset);
        }
        
        $query = $builder->get();
        
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    
    /**
     * Check if food exists by name (Your existing method)
     */
    public function checkFood($food_name)
    {
        $builder = $this->db->table("food");
        $builder->select('*');
        $builder->where('food_name', trim($food_name));
        $query = $builder->get();
        if ($query->getRow()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Get food with type name for detailed view
     */
    public function getFoodWithType($food_id = null)
    {
        $builder = $this->db->table($this->table . ' AS F');
        $builder->select('F.*, FT.food_type_name');
        $builder->join('food_type AS FT', 'FT.food_type_id = F.food_type_id', 'left');
        
        if ($food_id) {
            $builder->where('F.food_id', $food_id);
            return $builder->get()->getRowArray();
        }
        
        $builder->where('F.is_active', 1);
        $builder->orderBy('F.food_name', 'ASC');
        return $builder->get()->getResultArray();
    }
    
    /**
     * Search foods by name (for autocomplete)
     */
    public function searchFoods($keyword, $limit = 20)
    {
        $builder = $this->db->table($this->table . ' AS F');
        $builder->select('F.*, FT.food_type_name');
        $builder->join('food_type AS FT', 'FT.food_type_id = F.food_type_id', 'left');
        $builder->like('F.food_name', $keyword);
        $builder->where('F.is_active', 1);
        $builder->orderBy('F.food_name', 'ASC');
        $builder->limit($limit);
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get foods by type ID
     */
    public function getFoodsByType($food_type_id, $limit = 100)
    {
        return $this->where('food_type_id', $food_type_id)
                    ->where('is_active', 1)
                    ->orderBy('food_name', 'ASC')
                    ->findAll($limit);
    }
    
    /**
     * Get all active foods for dropdown
     */
    public function getFoodsForDropdown()
    {
        $foods = $this->where('is_active', 1)
                      ->orderBy('food_name', 'ASC')
                      ->findAll();
        
        $dropdown = [];
        foreach ($foods as $food) {
            $dropdown[$food['food_id']] = $food['food_name'] . ' (' . $food['calories'] . ' cal)';
        }
        
        return $dropdown;
    }
    
    /**
     * Find or create food (for calorie tracking)
     */
    public function findOrCreateFood($food_name, $calories = 0, $food_type_id = 1, $user_id = 0)
    {
        // Try to find existing food
        $food = $this->where('food_name', $food_name)->first();
        
        if ($food) {
            return $food['food_id'];
        }
        
        // Create new food
        $data = [
            'food_name' => $food_name,
            'calories' => $calories,
            'food_type_id' => $food_type_id,
            'is_active' => 1,
            'created_on' => date('Y-m-d H:i:s'),
            'created_by' => $user_id,
            'updated_on' => date('Y-m-d H:i:s'),
            'updated_by' => $user_id
        ];
        
        $this->insert($data);
        return $this->db->insertID();
    }
    
    /**
     * Get popular foods (most used in calorie tracking)
     */
    public function getPopularFoods($limit = 10)
    {
        $builder = $this->db->table('calorie AS C');
        $builder->select('F.food_id, F.food_name, F.calories, COUNT(C.food_id) as usage_count');
        $builder->join('food AS F', 'F.food_id = C.food_id', 'inner');
        $builder->where('F.is_active', 1);
        $builder->groupBy('C.food_id');
        $builder->orderBy('usage_count', 'DESC');
        $builder->limit($limit);
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get food suggestions for a client based on their history
     */
    public function getFoodSuggestionsForClient($client_id, $limit = 15)
    {
        // Get foods this client has eaten before
        $builder = $this->db->table('calorie AS C');
        $builder->select('F.*, COUNT(C.calorie_id) as eat_count, MAX(C.date) as last_eaten');
        $builder->join('food AS F', 'F.food_id = C.food_id', 'inner');
        $builder->where('C.client_id', $client_id);
        $builder->where('F.is_active', 1);
        $builder->groupBy('C.food_id');
        $builder->orderBy('eat_count', 'DESC');
        $builder->orderBy('last_eaten', 'DESC');
        $builder->limit($limit);
        
        $clientFoods = $builder->get()->getResultArray();
        
        // If not enough client-specific foods, add popular foods
        if (count($clientFoods) < $limit) {
            $remaining = $limit - count($clientFoods);
            $popularFoods = $this->getPopularFoods($remaining);
            
            // Merge arrays
            $clientFoods = array_merge($clientFoods, $popularFoods);
        }
        
        return $clientFoods;
    }
    
    /**
     * Update food calories
     */
    public function updateFoodCalories($food_id, $calories)
    {
        return $this->update($food_id, [
            'calories' => $calories,
            'updated_on' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get total count of foods (for pagination)
     */
    public function getTotalFoods($condition = [])
    {
        $builder = $this->db->table($this->table);
        
        if (!empty($condition)) {
            $builder->where($condition);
        }
        
        return $builder->countAllResults();
    }
    
    /**
     * Get foods with nutritional info
     */
    public function getFoodsWithNutrition($limit = 50)
    {
        $builder = $this->db->table($this->table . ' AS F');
        $builder->select('F.*, FT.food_type_name');
        $builder->join('food_type AS FT', 'FT.food_type_id = F.food_type_id', 'left');
        $builder->where('F.is_active', 1);
        $builder->orderBy('F.food_name', 'ASC');
        $builder->limit($limit);
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Bulk insert foods (for import functionality)
     */
    public function bulkInsertFoods($foods)
    {
        return $this->db->table($this->table)->insertBatch($foods);
    }
}