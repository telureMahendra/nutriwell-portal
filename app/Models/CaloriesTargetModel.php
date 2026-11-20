<?php
namespace App\Models;

use CodeIgniter\Model;

class CaloriesTargetModel extends Model
{
    protected $table = 'calories_target';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id', 'target_calories', 'target_type', 
        'target_date', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get current target for client
     * FIXED: Added getClientTarget method for backward compatibility
     */
    public function getClientTarget($clientId, $date = null, $type = 'intake')
    {
        return $this->getCurrentTarget($clientId, $date, $type);
    }
    
    /**
     * Get current target for client
     * FIXED: Better default handling and proper type checking
     */
    public function getCurrentTarget($clientId, $date = null, $type = 'burned')
    {
        $date = $date ?? date('Y-m-d');
        
        try {
            $builder = $this->db->table($this->table);
            $builder->select('target_calories')
                   ->where('client_id', $clientId)
                   ->where('target_type', $type)
                   ->where('target_date <=', $date)
                   ->orderBy('target_date', 'DESC')
                   ->limit(1);
            
            $result = $builder->get()->getRow();
            
            if ($result) {
                return (int)$result->target_calories;
            }
            
            // Return default based on type
            return $type === 'burned' ? 500 : 2000;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting target: ' . $e->getMessage());
            return $type === 'burned' ? 500 : 2000;
        }
    }
    
    /**
     * Set target for client
     */
    public function setTarget($clientId, $targetCalories, $type = 'burned', $date = null)
    {
        $date = $date ?? date('Y-m-d');
        
        try {
            // Check if target exists
            $existing = $this->where('client_id', $clientId)
                            ->where('target_type', $type)
                            ->where('target_date', $date)
                            ->first();
            
            if ($existing) {
                // Update existing
                $existingId = is_array($existing) ? $existing['id'] : $existing->id;
                return $this->update($existingId, [
                    'target_calories' => $targetCalories,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // Insert new
                return $this->insert([
                    'client_id' => $clientId,
                    'target_calories' => $targetCalories,
                    'target_type' => $type,
                    'target_date' => $date,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error setting target: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get target for specific date
     */
    public function getTargetForDate($clientId, $date, $type = 'burned')
    {
        try {
            $builder = $this->db->table($this->table);
            $builder->select('target_calories')
                   ->where('client_id', $clientId)
                   ->where('target_type', $type)
                   ->where('target_date', $date)
                   ->limit(1);
            
            $result = $builder->get()->getRow();
            
            if ($result) {
                return (int)$result->target_calories;
            }
            
            // If no target for this specific date, get the most recent one before this date
            return $this->getCurrentTarget($clientId, $date, $type);
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting target for date: ' . $e->getMessage());
            return $type === 'burned' ? 500 : 2000;
        }
    }
}