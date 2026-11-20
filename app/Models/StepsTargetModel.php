<?php 
namespace App\Models;

use CodeIgniter\Model;

class StepsTargetModel extends Model
{
    protected $table = 'steps_target';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id', 'target_steps', 'target_date', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get current target for client
     */
    public function getCurrentTarget($clientId, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        // Try to get target for specific date
        $builder = $this->db->table('steps_target');
        $builder->select('target_steps');
        $builder->where('client_id', $clientId);
        $builder->where('target_date', $date);
        $builder->orderBy('target_date', 'DESC');
        $builder->limit(1);
        
        $result = $builder->get()->getRow();
        
        if ($result) {
            return (int)$result->target_steps;
        }
        
        // If no target for specific date, get most recent target
        $builder = $this->db->table('steps_target');
        $builder->select('target_steps');
        $builder->where('client_id', $clientId);
        $builder->where('target_date <=', $date);
        $builder->orderBy('target_date', 'DESC');
        $builder->limit(1);
        
        $result = $builder->get()->getRow();
        
        if ($result) {
            return (int)$result->target_steps;
        }
        
        // Default target
        return 10000;
    }
    
    /**
     * Get target history
     */
    public function getTargetHistory($clientId, $limit = 10)
    {
        $builder = $this->db->table('steps_target');
        $builder->select('*');
        $builder->where('client_id', $clientId);
        $builder->orderBy('target_date', 'DESC');
        $builder->limit($limit);
        
        return $builder->get()->getResult();
    }
    
    /**
     * Set or update target
     */
    public function setTarget($clientId, $targetSteps, $targetDate = null)
    {
        if (!$targetDate) {
            $targetDate = date('Y-m-d');
        }
        
        // Check if target exists for this date
        $existing = $this->where([
            'client_id' => $clientId,
            'target_date' => $targetDate
        ])->first();
        
        $data = [
            'client_id' => $clientId,
            'target_steps' => $targetSteps,
            'target_date' => $targetDate,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            // Update existing target
            $existingId = is_array($existing) ? $existing['id'] : $existing->id;
            return $this->update($existingId, $data);
        } else {
            // Insert new target
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->insert($data);
        }
    }
}