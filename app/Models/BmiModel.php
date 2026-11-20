<?php namespace App\Models;

use CodeIgniter\Model;

class BmiModel extends Model
{
    protected $table = 'bmi';
    protected $primaryKey = 'bmi_id';
    protected $allowedFields = [
        'client_id', 'date', 'age', 'height', 'height_unit', 
        'weight', 'created_on', 'created_by', 'updated_on', 'updated_by'
    ];
    
    protected $useTimestamps = false;
    
    /**
     * Get BMI data for a client with calculated BMI value
     */
    public function getClientBmiData($clientId, $limit = 30)
    {
        $builder = $this->db->table('bmi b');
        $builder->select("b.*, 
            ROUND(b.weight / POWER(b.height/100, 2), 2) as bmi_value,
            CASE 
                WHEN (b.weight / POWER(b.height/100, 2)) < 18.5 THEN 'Underweight'
                WHEN (b.weight / POWER(b.height/100, 2)) < 25 THEN 'Normal'
                WHEN (b.weight / POWER(b.height/100, 2)) < 30 THEN 'Overweight'
                ELSE 'Obesity'
            END as bmi_category");
        $builder->where('b.client_id', $clientId);
        $builder->orderBy('b.date', 'DESC');
        $builder->limit($limit);
        
        return $builder->get()->getResult();
    }
    
    /**
     * Get BMI statistics for dashboard
     */
    public function getBmiStats($clientId)
    {
        $builder = $this->db->table('bmi b');
        $builder->select("
            ROUND(b.weight / POWER(b.height/100, 2), 2) as current_bmi,
            (SELECT ROUND(weight / POWER(height/100, 2), 2) 
             FROM bmi 
             WHERE client_id = $clientId 
             ORDER BY date ASC LIMIT 1) as start_bmi,
            (SELECT ROUND(AVG(weight / POWER(height/100, 2)), 2) 
             FROM bmi 
             WHERE client_id = $clientId) as avg_bmi,
            COUNT(*) as total_records");
        $builder->where('b.client_id', $clientId);
        $builder->orderBy('b.date', 'DESC');
        $builder->limit(1);
        
        $result = $builder->get()->getRow();
        
        // Calculate target BMI (assuming normal range: 22.0)
        $targetBMI = 22.0;
        
        // Calculate progress percentage
        $progress = 0;
        if ($result && $result->current_bmi && $result->start_bmi) {
            if ($result->start_bmi > $targetBMI) {
                // For weight loss
                $totalChangeNeeded = $result->start_bmi - $targetBMI;
                $changeAchieved = $result->start_bmi - $result->current_bmi;
                $progress = ($changeAchieved / $totalChangeNeeded) * 100;
            } else {
                // For weight gain
                $totalChangeNeeded = $targetBMI - $result->start_bmi;
                $changeAchieved = $result->current_bmi - $result->start_bmi;
                $progress = ($changeAchieved / $totalChangeNeeded) * 100;
            }
            $progress = min(max(round($progress), 0), 100);
        }
        
        return [
            'current' => $result->current_bmi ?? 0,
            'start' => $result->start_bmi ?? 0,
            'target' => $targetBMI,
            'avg' => $result->avg_bmi ?? 0,
            'total_records' => $result->total_records ?? 0,
            'progress' => $progress,
            'category' => $this->getBmiCategory($result->current_bmi ?? 0)
        ];
    }
    
    /**
     * Get BMI category based on value
     */
    private function getBmiCategory($bmi)
    {
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal';
        if ($bmi < 30) return 'Overweight';
        return 'Obesity';
    }
    
    /**
     * Get BMI chart data
     */
    public function getBmiChartData($clientId, $months = 6)
    {
        $builder = $this->db->table('bmi b');
        $builder->select("
            DATE_FORMAT(b.date, '%b %Y') as month,
            ROUND(b.weight / POWER(b.height/100, 2), 2) as bmi_value,
            b.date,
            b.weight,
            b.height");
        $builder->where('b.client_id', $clientId);
        $builder->where("b.date >= DATE_SUB(CURDATE(), INTERVAL $months MONTH)");
        $builder->orderBy('b.date', 'ASC');
        
        $results = $builder->get()->getResult();
        
        // Prepare data for chart
        $chartData = [
            'series' => [[]],
            'categories' => [],
            'weights' => [],
            'heights' => []
        ];
        
        foreach ($results as $row) {
            $chartData['series'][0][] = (float)$row->bmi_value;
            $chartData['categories'][] = $row->month;
            $chartData['weights'][] = (float)$row->weight;
            $chartData['heights'][] = (float)$row->height;
        }
        
        return $chartData;
    }
    
    /**
     * Add new BMI record
     */
    public function addBmiRecord($data)
    {
        // Check if record exists for this date
        $existing = $this->where([
            'client_id' => $data['client_id'],
            'date' => $data['date']
        ])->first();
        
        if ($existing) {
            // Update existing record
            $data['updated_on'] = date('Y-m-d H:i:s');
            return $this->update($existing->bmi_id, $data);
        } else {
            // Insert new record
            $data['created_on'] = date('Y-m-d H:i:s');
            $data['updated_on'] = date('Y-m-d H:i:s');
            return $this->insert($data);
        }
    }
    
    /**
     * Calculate BMI manually
     */
    public function calculateBmi($weight, $height, $unit = 'CM')
    {
        // Convert height to meters if needed
        if ($unit === 'CM') {
            $heightM = $height / 100;
        } elseif ($unit === 'INCH') {
            $heightM = $height * 0.0254;
        } else { // FIT (assuming feet)
            $heightM = $height * 0.3048;
        }
        
        if ($heightM > 0) {
            $bmi = $weight / ($heightM * $heightM);
            return round($bmi, 2);
        }
        
        return 0;
    }
}