<?php
namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;
use App\Models\CaloriesBurnedModel;
use App\Models\CaloriesTargetModel;
use App\Models\CaloriesIntakeModel;

class CaloriesBurnedController extends Controller
{
    protected $session;
    protected $validation;
    protected $calories_burned_model;
    protected $calories_target_model;
    protected $calories_intake_model;
    protected $common_model;

    public function __construct() {
        $this->validation = \Config\Services::validation();
        $this->calories_burned_model = new CaloriesBurnedModel();
        $this->calories_target_model = new CaloriesTargetModel();
        $this->calories_intake_model = new CaloriesIntakeModel();
        
        // Get the common model properly
        $this->common_model = new \App\Models\Common_model();
        
        $this->session = \Config\Services::session();
        
        // Check if admin is logged in
        if (!is_admin_logged_in()) {
            return redirect()->to('');
        }
    }

    /**
     * Save Calories burned record (AJAX endpoint)
     * Route: POST /admin/calories/saveCalories
     */
    public function saveCalories()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        $validationRules = [
            'client_id' => ['label' => 'Client ID', 'rules' => 'required|numeric'],
            'date' => ['label' => 'Date', 'rules' => 'required|valid_date'],
            'calories_burned' => ['label' => 'Calories Burned', 'rules' => 'required|numeric|greater_than_equal_to[0]'],
            'activity_type' => ['label' => 'Activity Type', 'rules' => 'permit_empty|string'],
            'duration_minutes' => ['label' => 'Duration', 'rules' => 'permit_empty|numeric|greater_than[0]'],
            'notes' => ['label' => 'Notes', 'rules' => 'permit_empty|string'],
            'source' => ['label' => 'Source', 'rules' => 'permit_empty|in_list[form,graph]']
        ];
        
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }
        
        $client_id = $this->request->getPost('client_id');
        $date = $this->request->getPost('date');
        $calories_burned = $this->request->getPost('calories_burned');
        $activity_type = $this->request->getPost('activity_type') ?? 'General Exercise';
        $duration_minutes = $this->request->getPost('duration_minutes') ?? 30;
        $notes = $this->request->getPost('notes');
        $source = $this->request->getPost('source') ?? 'form';
        
        $data = [
            'client_id' => $client_id,
            'date' => $date,
            'calories_burned' => $calories_burned,
            'activity_type' => $activity_type,
            'duration_minutes' => $duration_minutes,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Check if we're updating existing record
        $calories_id = $this->request->getPost('calories_id');
        
        try {
            if (!empty($calories_id) && $calories_id > 0) {
                // Update existing record
                $condition = ['id' => $calories_id];
                $result = $this->common_model->updateData('calories_burned', $data, $condition);
                $message = 'Calories burned record updated successfully';
            } else {
                // Insert new record
                $result = $this->common_model->addData('calories_burned', $data);
                $message = 'Calories burned record saved successfully';
            }
            
            if ($result) {
                // If saved from graph, return updated chart data
                if ($source === 'graph') {
                    $chartData = $this->getCaloriesChartData($client_id);
                    $chartResult = json_decode($chartData->getBody(), true);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => $message,
                        'calories_burned' => $calories_burned,
                        'date' => date('M d, Y', strtotime($date)),
                        'chart_data' => $chartResult['data'] ?? null
                    ]);
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message,
                    'calories_burned' => $calories_burned,
                    'date' => date('M d, Y', strtotime($date))
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save calories burned record'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Calories Save Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server error occurred. Please try again.'
            ]);
        }
    }
    
    /**
     * Get Calories burned history (AJAX endpoint)
     * Route: GET /admin/calories/getCaloriesHistory/:clientId
     */
    public function getCaloriesHistory($clientId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            // Debug: Log the client ID
            log_message('debug', 'Getting Calories history for client ID: ' . $clientId);
            
            // Get date range from request
            $start_date = $this->request->getGet('start_date');
            $end_date = $this->request->getGet('end_date');
            
            if (!$start_date) {
                $start_date = date('Y-m-d', strtotime('-30 days'));
            }
            if (!$end_date) {
                $end_date = date('Y-m-d');
            }
            
            $db = \Config\Database::connect();
            $builder = $db->table('calories_burned');
            
            $builder->select('*')
                   ->where('client_id', $clientId)
                   ->where('date >=', $start_date)
                   ->where('date <=', $end_date)
                   ->orderBy('date', 'DESC');
            
            $history = $builder->get()->getResult();
            
            // Debug: Log what we got from database
            log_message('debug', 'Database query result count: ' . count($history));
            
            // Get current target for calculations
            $target = $this->calories_target_model->getCurrentTarget($clientId);
            
            // Format the data
            $formattedHistory = [];
            foreach ($history as $record) {
                $completion = $target > 0 ? ($record->calories_burned / $target) * 100 : 0;
                
                $formattedHistory[] = [
                    'id' => $record->id,
                    'date' => $record->date,
                    'formatted_date' => date('M d, Y', strtotime($record->date)),
                    'day_name' => date('D', strtotime($record->date)),
                    'calories_burned' => intval($record->calories_burned),
                    'activity_type' => $record->activity_type,
                    'duration_minutes' => $record->duration_minutes,
                    'notes' => $record->notes,
                    'target' => $target,
                    'completion' => round($completion, 1),
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at
                ];
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedHistory,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_records' => count($formattedHistory)
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Calories History Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading calories history'
            ]);
        }
    }
    
    /**
     * Get Calories burned data for specific date (AJAX endpoint)
     * Route: GET /admin/calories/getCaloriesData/:clientId
     */
    public function getCaloriesData($clientId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            $date = $this->request->getGet('date');
            
            if (!$date) {
                $date = date('Y-m-d');
            }
            
            // Get total calories burned for the date
            $db = \Config\Database::connect();
            $builder = $db->table('calories_burned');
            $builder->select('SUM(calories_burned) as total_calories')
                   ->where('client_id', $clientId)
                   ->where('date', $date);
            $result = $builder->get()->getRow();
            $total_calories = $result ? intval($result->total_calories) : 0;
            
            // Get individual activities
            $builder = $db->table('calories_burned');
            $builder->select('*')
                   ->where('client_id', $clientId)
                   ->where('date', $date)
                   ->orderBy('created_at', 'ASC');
            $activities = $builder->get()->getResult();
            
            // Get target
            $target = $this->calories_target_model->getCurrentTarget($clientId);
            $completion = $target > 0 ? ($total_calories / $target) * 100 : 0;
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'total_calories' => $total_calories,
                    'target' => $target,
                    'completion' => round($completion, 1),
                    'activities' => $activities
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Get Calories Data Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading calories data'
            ]);
        }
    }
    
    /**
     * Delete Calories burned record (AJAX endpoint)
     * Route: DELETE /admin/calories/deleteCalories/:id
     */
    public function deleteCalories($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            $condition = ['id' => $id];
            $result = $this->common_model->deleteData('calories_burned', $condition);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Calories burned record deleted successfully'
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete record'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Delete Calories Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error deleting record'
            ]);
        }
    }
    
    /**
     * Get Calories burned chart data (AJAX endpoint)
     * Route: GET /admin/calories/getCaloriesChartData/:clientId
     */
    public function getCaloriesChartData($clientId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            $db = \Config\Database::connect();
            
            // Get date range from request or default to last 7 days
            $start_date = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-6 days'));
            $end_date = $this->request->getGet('end_date') ?? date('Y-m-d');
            
            // Get calories burned data grouped by date
            $builder = $db->table('calories_burned');
            $builder->select('date, SUM(calories_burned) as total_calories')
                   ->where('client_id', $clientId)
                   ->where('date >=', $start_date)
                   ->where('date <=', $end_date)
                   ->groupBy('date')
                   ->orderBy('date', 'ASC');
            
            $result = $builder->get()->getResult();
            
            // Fill missing dates with zero calories
            $start = strtotime($start_date);
            $end = strtotime($end_date);
            
            // Create associative array with dates as keys
            $dataByDate = [];
            foreach ($result as $row) {
                $dataByDate[$row->date] = intval($row->total_calories);
            }
            
            // Generate all dates in range
            $categories = [];
            $series = [];
            $detailed_data = [];
            
            $current = $start;
            while ($current <= $end) {
                $date = date('Y-m-d', $current);
                $dayName = date('D', $current);
                
                $calories = isset($dataByDate[$date]) ? $dataByDate[$date] : 0;
                
                $categories[] = $dayName;
                $series[] = $calories;
                $detailed_data[] = [
                    'date' => $date,
                    'formatted_date' => date('M d, Y', $current),
                    'day_name' => $dayName,
                    'calories_burned' => $calories
                ];
                
                $current = strtotime('+1 day', $current);
            }
            
            // Get target for completion calculation
            $target = $this->calories_target_model->getCurrentTarget($clientId) ?? 500;
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'series' => [$series],
                    'categories' => $categories,
                    'detailed_data' => $detailed_data,
                    'target' => $target,
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Calories Chart Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading chart data',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get Calories burned statistics (AJAX endpoint)
     * Route: GET /admin/calories/getCaloriesStats/:clientId
     */
    public function getCaloriesStats($clientId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            $db = \Config\Database::connect();
            
            // Today's date
            $today = date('Y-m-d');
            
            // Get today's total calories burned
            $builder = $db->table('calories_burned');
            $builder->select('SUM(calories_burned) as total_calories')
                   ->where('client_id', $clientId)
                   ->where('date', $today);
            $todayResult = $builder->get()->getRow();
            $todayCalories = $todayResult ? intval($todayResult->total_calories) : 0;
            
            // Get weekly average (last 7 days)
            $builder = $db->table('calories_burned');
            $builder->select('AVG(daily.total) as avg_calories');
            $builder->from("(
                SELECT date, SUM(calories_burned) as total
                FROM calories_burned
                WHERE client_id = $clientId
                AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY date
            ) as daily", false);
            $avgResult = $builder->get()->getRow();
            $weeklyAvg = $avgResult ? round(floatval($avgResult->avg_calories)) : 0;
            
            // Get current target
            $target = $this->calories_target_model->getCurrentTarget($clientId);
            
            // Calculate today's completion
            $completion = $target > 0 ? round(($todayCalories / $target) * 100, 1) : 0;
            
            // Get total calories burned this month
            $monthStart = date('Y-m-01');
            $monthEnd = date('Y-m-t');
            
            $builder = $db->table('calories_burned');
            $builder->select('SUM(calories_burned) as total_calories')
                   ->where('client_id', $clientId)
                   ->where('date >=', $monthStart)
                   ->where('date <=', $monthEnd);
            $monthResult = $builder->get()->getRow();
            $monthlyCalories = $monthResult ? intval($monthResult->total_calories) : 0;
            
            // Get total records count
            $builder = $db->table('calories_burned');
            $builder->where('client_id', $clientId);
            $totalRecords = $builder->countAllResults();
            
            // Get today's activities count
            $builder = $db->table('calories_burned');
            $builder->where('client_id', $clientId);
            $builder->where('date', $today);
            $todayActivities = $builder->countAllResults();
            
            $stats = [
                'today_calories' => $todayCalories,
                'weekly_average' => $weeklyAvg,
                'daily_target' => $target,
                'completion_rate' => $completion,
                'monthly_total' => $monthlyCalories,
                'total_records' => $totalRecords,
                'today_activities' => $todayActivities,
                'last_updated' => $today
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Calories Stats Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading statistics'
            ]);
        }
    }
    
    /**
     * Save Calories target (AJAX endpoint)
     * Route: POST /admin/calories/saveTarget
     */
    public function saveTarget()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        $validationRules = [
            'client_id' => ['label' => 'Client ID', 'rules' => 'required|numeric'],
            'target_calories' => ['label' => 'Target Calories', 'rules' => 'required|numeric|greater_than_equal_to[0]'],
            'target_type' => ['label' => 'Target Type', 'rules' => 'permit_empty|in_list[burned,intake]'],
            'target_date' => ['label' => 'Target Date', 'rules' => 'permit_empty|valid_date']
        ];
        
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }
        
        $client_id = $this->request->getPost('client_id');
        $target_calories = $this->request->getPost('target_calories');
        $target_type = $this->request->getPost('target_type') ?? 'burned';
        $target_date = $this->request->getPost('target_date') ?? date('Y-m-d');
        
        $data = [
            'client_id' => $client_id,
            'target_calories' => $target_calories,
            'target_type' => $target_type,
            'target_date' => $target_date,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            // Check if target exists for this date and type
            $existing = $this->common_model->getAllData('calories_target', [
                'client_id' => $client_id,
                'target_date' => $target_date,
                'target_type' => $target_type
            ]);
            
            if (!empty($existing)) {
                // Update existing target
                $condition = [
                    'client_id' => $client_id,
                    'target_date' => $target_date,
                    'target_type' => $target_type
                ];
                $result = $this->common_model->updateData('calories_target', $data, $condition);
                $message = 'Target updated successfully';
            } else {
                // Insert new target
                $result = $this->common_model->addData('calories_target', $data);
                $message = 'Target saved successfully';
            }
            
            return $this->response->setJSON([
                'success' => (bool)$result,
                'message' => $result ? $message : 'Failed to save target',
                'target' => $target_calories,
                'date' => $target_date,
                'type' => $target_type
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Save Target Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error saving target'
            ]);
        }
    }
    
    /**
     * Get Date specific data (AJAX endpoint)
     * Route: GET /admin/calories/getDateData/:clientId
     */
    public function getDateData($clientId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            $date = $this->request->getGet('date');
            
            if (!$date) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Date parameter is required'
                ]);
            }
            
            // Get total calories burned for the date
            $builder = $this->db->table('calories_burned');
            $builder->select('SUM(calories_burned) as total_calories')
                   ->where('client_id', $clientId)
                   ->where('date', $date);
            $result = $builder->get()->getRow();
            $total_calories = $result ? intval($result->total_calories) : 0;
            
            // Get individual activities
            $builder = $this->db->table('calories_burned');
            $builder->select('*')
                   ->where('client_id', $clientId)
                   ->where('date', $date)
                   ->orderBy('created_at', 'ASC');
            $activities = $builder->get()->getResultArray();
            
            // Get target for this date
            $target = $this->calories_target_model->getTargetForDate($clientId, $date);
            
            // Calculate completion
            $completion = $target > 0 ? round(($total_calories / $target) * 100, 1) : 0;
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'formatted_date' => date('M d, Y', strtotime($date)),
                    'total_calories' => $total_calories,
                    'target' => $target,
                    'completion' => $completion,
                    'activities' => $activities,
                    'activity_count' => count($activities)
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Get Date Data Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading date data'
            ]);
        }
    }
    
    /**
     * Quick update for graph editing (AJAX endpoint)
     * Route: POST /admin/calories/quickUpdate
     */
    public function quickUpdate()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        $validationRules = [
            'client_id' => ['label' => 'Client ID', 'rules' => 'required|numeric'],
            'date' => ['label' => 'Date', 'rules' => 'required|valid_date'],
            'calories_burned' => ['label' => 'Calories Burned', 'rules' => 'required|numeric|greater_than_equal_to[0]']
        ];
        
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }
        
        $client_id = $this->request->getPost('client_id');
        $date = $this->request->getPost('date');
        $calories_burned = $this->request->getPost('calories_burned');
        
        $data = [
            'client_id' => $client_id,
            'date' => $date,
            'calories_burned' => $calories_burned,
            'activity_type' => 'Quick Entry',
            'duration_minutes' => 30,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            // Check if record exists for today
            $existing = $this->common_model->getAllData('calories_burned', [
                'client_id' => $client_id,
                'date' => $date
            ]);
            
            if (!empty($existing)) {
                // Update existing record (first one found)
                $condition = [
                    'client_id' => $client_id,
                    'date' => $date,
                    'id' => $existing[0]->id
                ];
                $result = $this->common_model->updateData('calories_burned', $data, $condition);
                $action = 'updated';
            } else {
                // Insert new record
                $data['created_at'] = date('Y-m-d H:i:s');
                $result = $this->common_model->addData('calories_burned', $data);
                $action = 'added';
            }
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Calories burned $action successfully!",
                    'date' => $date,
                    'calories_burned' => $calories_burned,
                    'action' => $action
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update calories burned'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Quick Update Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating calories burned'
            ]);
        }
    }
    
    
    /**
     * Helper: Get calories for specific date and type
     */
    private function getCaloriesForDate($clientId, $date, $type = 'burned')
    {
        if ($type === 'burned') {
            $table = 'calories_burned';
            $field = 'calories_burned';
        } else {
            $table = 'calories_intake';
            $field = 'calories';
        }
        
        $builder = $this->db->table($table);
        $builder->select("SUM($field) as total_calories")
               ->where('client_id', $clientId)
               ->where('date', $date);
        
        $result = $builder->get()->getRow();
        return $result ? intval($result->total_calories) : 0;
    }
    
    // In CaloriesBurnedController.php

/**
 * Delete calories by date (AJAX endpoint)
 * Route: POST /admin/calories/deleteCaloriesByDate
 */
public function deleteCaloriesByDate()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }
    
    $validationRules = [
        'client_id' => ['label' => 'Client ID', 'rules' => 'required|numeric'],
        'date' => ['label' => 'Date', 'rules' => 'required|valid_date']
    ];
    
    if (!$this->validate($validationRules)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $this->validator->getErrors()
        ]);
    }
    
    $client_id = $this->request->getPost('client_id');
    $date = $this->request->getPost('date');
    
    try {
        $db = \Config\Database::connect();
        $builder = $db->table('calories_burned');
        
        $builder->where('client_id', $client_id);
        $builder->where('date', $date);
        $result = $builder->delete();
        
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Calories burned records deleted successfully for ' . $date
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No records found to delete'
        ]);
        
    } catch (\Exception $e) {
        log_message('error', 'Delete Calories By Date Error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error deleting records'
        ]);
    }
}

/**
 * Get activity types for dropdown
 * Route: GET /admin/calories/getActivityTypes
 */
public function getActivityTypes()
{
    if (!$this->request->isAJAX()) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }
    
    $activity_types = [
        'Running', 'Walking', 'Cycling', 'Swimming', 'Gym Workout',
        'Yoga', 'Pilates', 'Dancing', 'Hiking', 'Tennis',
        'Basketball', 'Football', 'Weight Training', 'Cardio',
        'Stair Climbing', 'Rowing', 'Elliptical', 'Skiing',
        'Snowboarding', 'Skating', 'General Exercise'
    ];
    
    return $this->response->setJSON([
        'success' => true,
        'data' => $activity_types
    ]);
}

/**
 * Get calories comparison data
 * Route: GET /admin/calories/getCaloriesComparison/:clientId
 */
public function getCaloriesComparison($clientId)
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }
    
    try {
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        
        $db = \Config\Database::connect();
        
        // Get calories burned for the date
        $builder = $db->table('calories_burned');
        $builder->select('SUM(calories_burned) as total_burned')
               ->where('client_id', $clientId)
               ->where('date', $date);
        $burnedResult = $builder->get()->getRow();
        $burned = $burnedResult ? intval($burnedResult->total_burned) : 0;
        
        // Get calories intake for the date
        $builder = $db->table('calories_intake');
        $builder->select('SUM(calories) as total_intake')
               ->where('client_id', $clientId)
               ->where('date', $date);
        $intakeResult = $builder->get()->getRow();
        $intake = $intakeResult ? intval($intakeResult->total_intake) : 0;
        
        // Get targets
        $targetModel = new CaloriesTargetModel();
        $burned_target = $targetModel->getCurrentTarget($clientId, $date, 'burned');
        $intake_target = $targetModel->getCurrentTarget($clientId, $date, 'intake');
        
        // Calculate net calories
        $net_calories = $intake - $burned;
        
        // Calculate completion percentages
        $burned_completion = $burned_target > 0 ? round(($burned / $burned_target) * 100, 1) : 0;
        $intake_completion = $intake_target > 0 ? round(($intake / $intake_target) * 100, 1) : 0;
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'date' => $date,
                'burned' => $burned,
                'intake' => $intake,
                'net' => $net_calories,
                'burned_target' => $burned_target,
                'intake_target' => $intake_target,
                'burned_completion' => $burned_completion,
                'intake_completion' => $intake_completion
            ]
        ]);
        
    } catch (\Exception $e) {
        log_message('error', 'Calories Comparison Error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error loading calories comparison'
        ]);
    }
}

}