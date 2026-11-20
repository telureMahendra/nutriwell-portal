<?php

namespace App\Models;

use CodeIgniter\Model;

class FoodTypeModel extends Model
{
    protected $table = 'food_type';
    protected $primaryKey = 'food_type_id';
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'food_type_name',
        'food_type_description',
        'is_active',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by'
    ];
    
    protected $useTimestamps = false;
    
    /**
     * Get all active food types
     */
    public function getActiveFoodTypes()
    {
        return $this->where('is_active', 1)
                    ->orderBy('food_type_name', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get food type by ID
     */
    public function getFoodTypeById($id)
    {
        return $this->find($id);
    }
    
    /**
     * Get food types for dropdown
     */
    public function getFoodTypesForDropdown()
    {
        $foodTypes = $this->where('is_active', 1)
                          ->orderBy('food_type_name', 'ASC')
                          ->findAll();
        
        $dropdown = [];
        foreach ($foodTypes as $type) {
            $dropdown[$type['food_type_id']] = $type['food_type_name'];
        }
        
        return $dropdown;
    }
    
    /**
     * Check if food type exists by name
     */
    public function existsByName($name)
    {
        return $this->where('food_type_name', $name)
                    ->first() !== null;
    }
    
    /**
     * Count foods by type
     */
    public function countFoodsByType($food_type_id)
    {
        $builder = $this->db->table('food');
        $builder->where('food_type_id', $food_type_id);
        return $builder->countAllResults();
    }
}