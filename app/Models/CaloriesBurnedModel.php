<?php 
namespace App\Models;

use CodeIgniter\Model;

class CaloriesBurnedModel extends Model
{
    protected $returnType = 'array';
    protected $table = 'calories_burned';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id', 'date', 'calories_burned', 'activity_type', 
        'duration_minutes', 'notes', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get client's calories burned data
     */
    public function getClientCaloriesData($clientId, $limit = 30)
    {
        $builder = $this->db->table('calories_burned cb');
        $builder->select("cb.*");
        $builder->where('cb.client_id', $clientId);
        $builder->orderBy('cb.date', 'DESC');
        $builder->limit($limit);
        
        return $builder->get()->getResult();
    }
    
    /**
     * Get calories burned statistics for dashboard
     */
    public function getCaloriesStats($clientId)
    {
        $builder = $this->db->table('calories_burned cb');
        $builder->select("
            (SELECT SUM(calories_burned) FROM calories_burned 
             WHERE client_id = $clientId 
             AND date = CURDATE()) as today_calories,
            (SELECT ROUND(AVG(daily.total), 0) FROM (
                SELECT date, SUM(calories_burned) as total 
                FROM calories_burned 
                WHERE client_id = $clientId 
                AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY date
            ) as daily) as weekly_avg,
            (SELECT target_calories FROM calories_target 
             WHERE client_id = $clientId 
             AND target_type = 'burned'
             AND target_date <= CURDATE() 
             ORDER BY target_date DESC LIMIT 1) as daily_target,
            COUNT(*) as total_records,
            MAX(date) as last_record_date");
        $builder->where('cb.client_id', $clientId);
        
        $result = $builder->get()->getRow();
        
        // Calculate completion rate for today
        $todayCalories = $result->today_calories ?? 0;
        $dailyTarget = $result->daily_target ?? 500;
        $completionRate = $dailyTarget > 0 ? round(($todayCalories / $dailyTarget) * 100, 1) : 0;
        
        // Calculate monthly total
        $monthlyTotal = $this->getMonthlyTotal($clientId);
        
        return [
            'today_calories' => (int)$todayCalories,
            'weekly_avg' => (int)($result->weekly_avg ?? 0),
            'daily_target' => (int)$dailyTarget,
            'completion_rate' => $completionRate,
            'monthly_total' => $monthlyTotal,
            'total_records' => (int)($result->total_records ?? 0),
            'last_record_date' => $result->last_record_date ?? null
        ];
    }
    
    /**
     * Get calories burned chart data
     */
    public function getCaloriesChartData($clientId, $days = 7)
    {
        $builder = $this->db->table('calories_burned cb');
        $builder->select("
            cb.date,
            DATE_FORMAT(cb.date, '%a') as day_name,
            SUM(cb.calories_burned) as calories_burned,
            (SELECT target_calories FROM calories_target 
             WHERE client_id = cb.client_id 
             AND target_type = 'burned'
             AND target_date <= cb.date 
             ORDER BY target_date DESC LIMIT 1) as target");
        $builder->where('cb.client_id', $clientId);
        $builder->where("cb.date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)");
        $builder->groupBy('cb.date');
        $builder->orderBy('cb.date', 'ASC');
        
        $results = $builder->get()->getResult();
        
        // Fill missing dates with zero calories
        $chartData = $this->fillMissingDates($results, $clientId, $days);
        
        return $chartData;
    }
    
    /**
     * Fill missing dates with zero calories
     */
    private function fillMissingDates($results, $clientId, $days)
    {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        $endDate = date('Y-m-d');
        
        // Create associative array for existing data
        $existingData = [];
        foreach ($results as $row) {
            $existingData[$row->date] = $row;
        }
        
        // Prepare chart data arrays
        $chartData = [
            'series' => [[]],
            'categories' => [],
            'detailed_data' => []
        ];
        
        // Get target model
        $targetModel = new CaloriesTargetModel();
        
        $current = strtotime($startDate);
        $end = strtotime($endDate);
        
        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $dayName = date('D', $current);
            
            if (isset($existingData[$date])) {
                $row = $existingData[$date];
                $calories = (int)$row->calories_burned;
                $target = (int)$row->target;
            } else {
                $calories = 0;
                $target = $targetModel->getCurrentTarget($clientId, $date, 'burned');
            }
            
            // Calculate completion rate
            $completion = $target > 0 ? round(($calories / $target) * 100, 1) : 0;
            
            $chartData['series'][0][] = $calories;
            $chartData['categories'][] = $dayName;
            $chartData['detailed_data'][] = [
                'date' => $date,
                'formatted_date' => date('M d, Y', $current),
                'day_name' => $dayName,
                'calories_burned' => $calories,
                'target' => $target,
                'completion' => $completion
            ];
            
            $current = strtotime('+1 day', $current);
        }
        
        return $chartData;
    }
    
    /**
     * Add new calories burned record
     */
    public function addCaloriesRecord($data)
    {
        return $this->insert($data);
    }
    
    /**
     * Get monthly total calories burned
     */
    public function getMonthlyTotal($clientId)
    {
        $builder = $this->db->table('calories_burned');
        $builder->select("SUM(calories_burned) as monthly_total");
        $builder->where('client_id', $clientId);
        $builder->where("DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')");
        
        $result = $builder->get()->getRow();
        
        return (int)($result->monthly_total ?? 0);
    }
    
    /**
     * Get total calories burned in period
     */
    public function getPeriodTotal($clientId, $startDate, $endDate)
    {
        $builder = $this->db->table('calories_burned');
        $builder->select("SUM(calories_burned) as total_calories");
        $builder->where('client_id', $clientId);
        $builder->where('date >=', $startDate);
        $builder->where('date <=', $endDate);
        
        $result = $builder->get()->getRow();
        
        return (int)($result->total_calories ?? 0);
    }
    
    /**
     * Get activities for specific date
     */
    public function getDateActivities($clientId, $date)
    {
        return $this->where('client_id', $clientId)
                    ->where('date', $date)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }
    /**
     * Get today's total calories burned for a client
     * 
     * @param int $clientId Client ID
     * @return int Total calories burned today
     */
    public function getTodayBurned($clientId)
    {
        $today = date('Y-m-d');
        
        $builder = $this->db->table('calories_burned');
        $builder->select("SUM(calories_burned) as today_total");
        $builder->where('client_id', $clientId);
        $builder->where('date', $today);
        
        $result = $builder->get()->getRow();
        
        return (int)($result->today_total ?? 0);
    }


/**
 * Get activities for specific date with total calculation
 */
public function getActivitiesByDate($clientId, $date)
{
    $builder = $this->db->table('calories_burned');
    $builder->select('*');
    $builder->where('client_id', $clientId);
    $builder->where('date', $date);
    $builder->orderBy('created_at', 'ASC');
    
    return $builder->get()->getResultArray();  // ✅ JUST THIS!
}

/**
 * Get burned history with target comparison
 */
public function getBurnedHistory($clientId, $limit = 30, $orderBy = 'DESC')
{
    $builder = $this->db->table('calories_burned cb');
    $builder->select("
        cb.*,
        ct.target_calories,
        ROUND((cb.calories_burned / ct.target_calories) * 100, 1) as target_percentage
    ");
    $builder->join('calories_target ct', 
        "ct.client_id = cb.client_id AND ct.target_type = 'burned' AND ct.target_date = cb.date", 
        'left');
    $builder->where('cb.client_id', $clientId);
    $builder->orderBy('cb.date', $orderBy);
    $builder->orderBy('cb.created_at', $orderBy);
    
    if ($limit > 0) {
        $builder->limit($limit);
    }
    
    return $builder->get()->getResult();
}


}