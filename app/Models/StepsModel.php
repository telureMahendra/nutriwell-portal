<?php 
namespace App\Models;

use CodeIgniter\Model;

class StepsModel extends Model
{
    protected $table = 'steps_tracker';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id', 'date', 'steps', 'created_at', 'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get Steps data for a client with calculated calories
     */
    public function getClientStepsData($clientId, $limit = 30)
    {
        $builder = $this->db->table('steps_tracker st');
        $builder->select("st.*, 
            ROUND(st.steps * 0.04, 0) as calories_burned,
            CASE 
                WHEN st.steps >= 10000 THEN 'Excellent'
                WHEN st.steps >= 7500 THEN 'Good'
                WHEN st.steps >= 5000 THEN 'Average'
                ELSE 'Below Average'
            END as steps_category,
            (SELECT target_steps FROM steps_target 
             WHERE client_id = st.client_id 
             AND target_date <= st.date 
             ORDER BY target_date DESC LIMIT 1) as daily_target");
        $builder->where('st.client_id', $clientId);
        $builder->orderBy('st.date', 'DESC');
        $builder->limit($limit);
        
        return $builder->get()->getResult();
    }
    
    /**
     * Get Steps statistics for dashboard
     */
    public function getStepsStats($clientId)
    {
        $builder = $this->db->table('steps_tracker st');
        $builder->select("
            (SELECT steps FROM steps_tracker 
             WHERE client_id = $clientId 
             AND date = CURDATE() 
             LIMIT 1) as today_steps,
            (SELECT ROUND(AVG(steps), 0) FROM steps_tracker 
             WHERE client_id = $clientId 
             AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) as weekly_avg,
            (SELECT target_steps FROM steps_target 
             WHERE client_id = $clientId 
             AND target_date <= CURDATE() 
             ORDER BY target_date DESC LIMIT 1) as daily_target,
            COUNT(*) as total_records,
            MAX(date) as last_record_date");
        $builder->where('st.client_id', $clientId);
        
        $result = $builder->get()->getRow();
        
        // Calculate calories burned today
        $todaySteps = $result->today_steps ?? 0;
        $todayCalories = round($todaySteps * 0.04, 0);
        
        // Calculate completion rate for today
        $dailyTarget = $result->daily_target ?? 10000;
        $completionRate = $dailyTarget > 0 ? round(($todaySteps / $dailyTarget) * 100, 1) : 0;
        
        // Calculate monthly total
        $monthlyTotal = $this->getMonthlyTotal($clientId);
        $monthlyCalories = round($monthlyTotal * 0.04, 0);
        
        // Calculate current streak
        $currentStreak = $this->getCurrentStreak($clientId);
        
        return [
            'today_steps' => (int)$todaySteps,
            'weekly_avg' => (int)($result->weekly_avg ?? 0),
            'daily_target' => (int)$dailyTarget,
            'completion_rate' => $completionRate,
            'today_calories' => $todayCalories,
            'monthly_total' => $monthlyTotal,
            'monthly_calories' => $monthlyCalories,
            'current_streak' => $currentStreak,
            'total_records' => (int)($result->total_records ?? 0),
            'last_record_date' => $result->last_record_date ?? null,
            'category' => $this->getStepsCategory($todaySteps)
        ];
    }
    
    /**
     * Get Steps category based on steps count
     */
    private function getStepsCategory($steps)
    {
        if ($steps >= 10000) return 'Excellent';
        if ($steps >= 7500) return 'Good';
        if ($steps >= 5000) return 'Average';
        if ($steps > 0) return 'Below Average';
        return 'No Data';
    }
    
    /**
     * Get Steps chart data
     */
    public function getStepsChartData($clientId, $days = 7)
    {
        $builder = $this->db->table('steps_tracker st');
        $builder->select("
            st.date,
            DATE_FORMAT(st.date, '%a') as day_name,
            st.steps,
            ROUND(st.steps * 0.04, 0) as calories,
            (SELECT target_steps FROM steps_target 
             WHERE client_id = st.client_id 
             AND target_date <= st.date 
             ORDER BY target_date DESC LIMIT 1) as target");
        $builder->where('st.client_id', $clientId);
        $builder->where("st.date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)");
        $builder->orderBy('st.date', 'ASC');
        
        $results = $builder->get()->getResult();
        
        // Fill missing dates with zero steps
        $chartData = $this->fillMissingDates($results, $clientId, $days);
        
        return $chartData;
    }
    
    /**
     * Fill missing dates with zero steps
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
            'calories_series' => [[]],
            'categories' => [],
            'detailed_data' => []
        ];
        
        // Get target for each date
        $targetModel = new StepsTargetModel();
        
        $current = strtotime($startDate);
        $end = strtotime($endDate);
        
        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $dayName = date('D', $current);
            
            if (isset($existingData[$date])) {
                $row = $existingData[$date];
                $steps = (int)$row->steps;
                $calories = (int)$row->calories;
                $target = (int)$row->target;
            } else {
                $steps = 0;
                $calories = 0;
                $target = $targetModel->getCurrentTarget($clientId, $date);
            }
            
            // Calculate completion rate
            $completion = $target > 0 ? round(($steps / $target) * 100, 1) : 0;
            
            $chartData['series'][0][] = $steps;
            $chartData['calories_series'][0][] = $calories;
            $chartData['categories'][] = $dayName;
            $chartData['detailed_data'][] = [
                'date' => $date,
                'formatted_date' => date('M d, Y', $current),
                'day_name' => $dayName,
                'steps' => $steps,
                'calories' => $calories,
                'target' => $target,
                'completion' => $completion
            ];
            
            $current = strtotime('+1 day', $current);
        }
        
        return $chartData;
    }
    
    /**
     * Add new Steps record
     */
    public function addStepsRecord($data)
    {
        // Check if record exists for this date
        $existing = $this->where([
            'client_id' => $data['client_id'],
            'date' => $data['date']
        ])->first();
        
        if ($existing) {
            // Update existing record
            return $this->update($existing->id, $data);
        } else {
            // Insert new record
            return $this->insert($data);
        }
    }
    
    /**
     * Calculate calories from steps
     */
    public function calculateCalories($steps)
    {
        return round($steps * 0.04, 0);
    }
    
    /**
     * Get monthly total steps
     */
    public function getMonthlyTotal($clientId)
    {
        $builder = $this->db->table('steps_tracker');
        $builder->select("SUM(steps) as monthly_total");
        $builder->where('client_id', $clientId);
        $builder->where("DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')");
        
        $result = $builder->get()->getRow();
        
        return (int)($result->monthly_total ?? 0);
    }
    
    /**
     * Get current streak (consecutive days with steps > 0)
     */
    public function getCurrentStreak($clientId)
    {
        $builder = $this->db->table('steps_tracker');
        $builder->select('date, steps');
        $builder->where('client_id', $clientId);
        $builder->where('steps >', 0);
        $builder->orderBy('date', 'DESC');
        
        $results = $builder->get()->getResult();
        
        if (empty($results)) {
            return 0;
        }
        
        $streak = 0;
        $currentDate = date('Y-m-d');
        
        foreach ($results as $row) {
            $expectedDate = date('Y-m-d', strtotime($currentDate . ' -' . $streak . ' days'));
            
            if ($row->date == $expectedDate) {
                $streak++;
            } else {
                break;
            }
        }
        
        return $streak;
    }
    
    /**
     * Get weekly progress (last 7 days)
     */
    public function getWeeklyProgress($clientId)
    {
        $builder = $this->db->table('steps_tracker st');
        $builder->select("
            st.date,
            st.steps,
            ROUND(st.steps * 0.04, 0) as calories,
            (SELECT target_steps FROM steps_target 
             WHERE client_id = st.client_id 
             AND target_date <= st.date 
             ORDER BY target_date DESC LIMIT 1) as target");
        $builder->where('st.client_id', $clientId);
        $builder->where("st.date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $builder->orderBy('st.date', 'ASC');
        
        $results = $builder->get()->getResult();
        
        $weeklyData = [];
        $totalSteps = 0;
        $totalCalories = 0;
        $daysCompleted = 0;
        
        foreach ($results as $row) {
            $completion = $row->target > 0 ? round(($row->steps / $row->target) * 100, 1) : 0;
            
            $weeklyData[] = [
                'date' => $row->date,
                'day_name' => date('D', strtotime($row->date)),
                'steps' => (int)$row->steps,
                'calories' => (int)$row->calories,
                'target' => (int)$row->target,
                'completion' => $completion
            ];
            
            $totalSteps += $row->steps;
            $totalCalories += $row->calories;
            if ($completion >= 100) {
                $daysCompleted++;
            }
        }
        
        return [
            'weekly_data' => $weeklyData,
            'total_steps' => $totalSteps,
            'total_calories' => $totalCalories,
            'days_completed' => $daysCompleted,
            'weekly_avg' => count($weeklyData) > 0 ? round($totalSteps / count($weeklyData), 0) : 0
        ];
    }
    
    /**
     * Get best day record
     */
    public function getBestDay($clientId)
    {
        $builder = $this->db->table('steps_tracker');
        $builder->select('date, steps');
        $builder->where('client_id', $clientId);
        $builder->orderBy('steps', 'DESC');
        $builder->limit(1);
        
        $result = $builder->get()->getRow();
        
        if ($result) {
            return [
                'date' => $result->date,
                'formatted_date' => date('M d, Y', strtotime($result->date)),
                'steps' => (int)$result->steps,
                'calories' => round($result->steps * 0.04, 0)
            ];
        }
        
        return null;
    }
    
    /**
     * Get steps summary by period
     */
    public function getPeriodSummary($clientId, $period = 'month')
    {
        $builder = $this->db->table('steps_tracker');
        
        if ($period === 'month') {
            $builder->select("
                DATE_FORMAT(date, '%Y-%m') as period,
                SUM(steps) as total_steps,
                ROUND(SUM(steps) * 0.04, 0) as total_calories,
                COUNT(*) as days_recorded,
                ROUND(AVG(steps), 0) as avg_daily_steps");
            $builder->groupBy("DATE_FORMAT(date, '%Y-%m')");
            $builder->orderBy('period', 'DESC');
            $builder->limit(6);
        } else { // weekly
            $builder->select("
                YEARWEEK(date, 1) as period,
                SUM(steps) as total_steps,
                ROUND(SUM(steps) * 0.04, 0) as total_calories,
                COUNT(*) as days_recorded,
                ROUND(AVG(steps), 0) as avg_daily_steps");
            $builder->groupBy('YEARWEEK(date, 1)');
            $builder->orderBy('period', 'DESC');
            $builder->limit(8);
        }
        
        $builder->where('client_id', $clientId);
        
        return $builder->get()->getResult();
    }

    // Add this method to StepsModel class
/**
 * Get target for specific date
 */
public function getTargetForDate($clientId, $date)
{
    $targetModel = new StepsTargetModel();
    return $targetModel->getCurrentTarget($clientId, $date);
}

/**
 * Get chart data with improved logic
 */
public function getChartData($clientId, $days = 7)
{
    $builder = $this->db->table('steps_tracker st');
    $builder->select("
        st.date,
        DATE_FORMAT(st.date, '%a') as day_name,
        st.steps,
        ROUND(st.steps * 0.04, 0) as calories");
    $builder->where('st.client_id', $clientId);
    $builder->where("st.date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)");
    $builder->orderBy('st.date', 'ASC');
    
    $results = $builder->get()->getResult();
    
    // Fill missing dates
    return $this->fillChartData($results, $clientId, $days);
}

/**
 * Fill chart data with missing dates
 */
private function fillChartData($results, $clientId, $days)
{
    $startDate = date('Y-m-d', strtotime("-$days days"));
    $endDate = date('Y-m-d');
    
    // Create associative array for existing data
    $existingData = [];
    foreach ($results as $row) {
        $existingData[$row->date] = $row;
    }
    
    // Get target model
    $targetModel = new StepsTargetModel();
    
    // Prepare arrays
    $series = [];
    $categories = [];
    $detailed_data = [];
    
    $current = strtotime($startDate);
    $end = strtotime($endDate);
    
    while ($current <= $end) {
        $date = date('Y-m-d', $current);
        $dayName = date('D', $current);
        
        if (isset($existingData[$date])) {
            $row = $existingData[$date];
            $steps = (int)$row->steps;
            $calories = (int)$row->calories;
        } else {
            $steps = 0;
            $calories = 0;
        }
        
        $target = $targetModel->getCurrentTarget($clientId, $date);
        
        $series[] = $steps;
        $categories[] = $dayName;
        $detailed_data[] = [
            'date' => $date,
            'formatted_date' => date('M d, Y', $current),
            'day_name' => $dayName,
            'steps' => $steps,
            'calories' => $calories,
            'target' => $target
        ];
        
        $current = strtotime('+1 day', $current);
    }
    
    return [
        'series' => [$series],
        'categories' => $categories,
        'detailed_data' => $detailed_data
    ];
}
}