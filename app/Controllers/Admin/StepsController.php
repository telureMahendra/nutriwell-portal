<?php
namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;
use App\Models\StepsModel;
use App\Models\StepsCaloriesModel;
use App\Models\StepsTargetModel;

class StepsController extends Controller
{
    protected $session;
    protected $validation;
    protected $steps_model;
    protected $steps_calories_model;
    protected $steps_target_model;
    protected $common_model;

    public function __construct() {
    $this->validation = \Config\Services::validation();
    $this->steps_model = new StepsModel();
    $this->steps_calories_model = new StepsCaloriesModel();
    $this->steps_target_model = new StepsTargetModel();
    
    // Get the common model properly
    $this->common_model = new \App\Models\Common_model();
    
    $this->session = \Config\Services::session();
    
    // Check if admin is logged in
    if (!is_admin_logged_in()) {
        return redirect()->to('');
    }
}

    /**
     * Save Steps record (AJAX endpoint)
     * Route: POST /admin/steps/saveSteps
     */
    public function saveSteps()
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
            'steps' => ['label' => 'Steps', 'rules' => 'required|numeric|greater_than_equal_to[0]'],
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
        $steps = $this->request->getPost('steps');
        $source = $this->request->getPost('source') ?? 'form';
        
        // Calculate calories
        $calories = $this->calculateCalories($steps);
        
        $data = [
            'client_id' => $client_id,
            'date' => $date,
            'steps' => $steps,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Check if we're updating existing record
        $steps_id = $this->request->getPost('steps_id');
        
        try {
            if (!empty($steps_id) && $steps_id > 0) {
                // Update existing record
                $condition = ['id' => $steps_id];
                $result = $this->common_model->updateData('steps_tracker', $data, $condition);
                $message = 'Steps record updated successfully';
            } else {
                // Check if record exists for this date
                $existing = $this->common_model->getAllData('steps_tracker', [
                    'client_id' => $client_id,
                    'date' => $date
                ]);
                
                if (!empty($existing)) {
                    // Update existing
                    $condition = ['client_id' => $client_id, 'date' => $date];
                    $result = $this->common_model->updateData('steps_tracker', $data, $condition);
                    $message = 'Steps record updated successfully';
                } else {
                    // Insert new record
                    $result = $this->common_model->addData('steps_tracker', $data);
                    $message = 'Steps record saved successfully';
                }
            }
            
            if ($result) {
                // Save calories data
                $this->saveCaloriesData($client_id, $date, $calories);
                
                // If saved from graph, return updated chart data
                if ($source === 'graph') {
                    $chartData = $this->getStepsChartData($client_id);
                    $chartResult = json_decode($chartData->getBody(), true);
                    
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => $message,
                        'calories' => $calories,
                        'date' => date('M d, Y', strtotime($date)),
                        'chart_data' => $chartResult['data'] ?? null
                    ]);
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message,
                    'calories' => $calories,
                    'date' => date('M d, Y', strtotime($date))
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save steps record'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Steps Save Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server error occurred. Please try again.'
            ]);
        }
    }
    
    /**
     * Get Steps history (AJAX endpoint)
     * Route: GET /admin/steps/getStepsHistory/:clientId
     */
    public function getStepsHistory($clientId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            // Debug: Log the client ID
            log_message('debug', 'Getting Steps history for client ID: ' . $clientId);
            
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
            $builder = $db->table('steps_tracker');
            
            $builder->select('*')
                   ->where('client_id', $clientId)
                   ->where('date >=', $start_date)
                   ->where('date <=', $end_date)
                   ->orderBy('date', 'DESC');
            
            $history = $builder->get()->getResult();
            
            // Debug: Log what we got from database
            log_message('debug', 'Database query result count: ' . count($history));
            
            // Get current target for calculations
            $target = $this->steps_target_model->getCurrentTarget($clientId);
            
            // Format the data
            $formattedHistory = [];
            foreach ($history as $record) {
                $calories = $this->calculateCalories($record->steps);
                $completion = $target > 0 ? ($record->steps / $target) * 100 : 0;
                
                $formattedHistory[] = [
                    'id' => $record->id,
                    'date' => $record->date,
                    'formatted_date' => date('M d, Y', strtotime($record->date)),
                    'day_name' => date('D', strtotime($record->date)),
                    'steps' => intval($record->steps),
                    'calories' => $calories,
                    'target' => $target,
                    'completion' => round($completion, 1),
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at
                ];
                
                // Debug: Log each record
                log_message('debug', 'Record: ' . json_encode(end($formattedHistory)));
            }
            
            // Debug: Log final response
            log_message('debug', 'Sending response with ' . count($formattedHistory) . ' records');
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedHistory,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_records' => count($formattedHistory)
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Steps History Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading steps history'
            ]);
        }
    }
    
    /**
     * Get Steps data for specific date (AJAX endpoint)
     * Route: GET /admin/steps/getStepsData/:clientId
     */
    public function getStepsData($clientId)
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
            
            $condition = [
                'client_id' => $clientId,
                'date' => $date
            ];
            
            $record = $this->common_model->getAllData('steps_tracker', $condition);
            
            if (empty($record)) {
                return $this->response->setJSON([
                    'success' => true,
                    'data' => [
                        'date' => $date,
                        'steps' => 0,
                        'calories' => 0
                    ]
                ]);
            }
            
            $record = $record[0];
            $calories = $this->calculateCalories($record->steps);
            
            $formattedRecord = [
                'id' => $record->id,
                'date' => $record->date,
                'formatted_date' => date('M d, Y', strtotime($record->date)),
                'steps' => intval($record->steps),
                'calories' => $calories,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedRecord
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Get Steps Data Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading steps data'
            ]);
        }
    }
    
    /**
     * Delete Steps record (AJAX endpoint)
     * Route: DELETE /admin/steps/deleteSteps/:id
     */
    public function deleteSteps($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            // First get the record to get client_id and date
            $condition = ['id' => $id];
            $record = $this->common_model->getAllData('steps_tracker', $condition);
            
            if (empty($record)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Record not found'
                ]);
            }
            
            $record = $record[0];
            $client_id = $record->client_id;
            $date = $record->date;
            
            // Delete steps record
            $result = $this->common_model->deleteData('steps_tracker', $condition);
            
            if ($result) {
                // Also delete associated calories data
                $caloriesCondition = [
                    'client_id' => $client_id,
                    'DATE(count_date)' => $date,
                    'count_type' => 1
                ];
                $this->common_model->deleteData('steps_calories', $caloriesCondition);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Steps record deleted successfully'
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete record'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Delete Steps Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error deleting record'
            ]);
        }
    }
    
    /**
     * Get Steps chart data (AJAX endpoint)
     * Route: GET /admin/steps/getStepsChartData/:clientId
     */
    public function getStepsChartData($clientId)
{
    try {
        $db = \Config\Database::connect();
        
        // Get date range from request or default to last 7 days
        $start_date = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-6 days'));
        $end_date = $this->request->getGet('end_date') ?? date('Y-m-d');
        
        // Get steps data
        $builder = $db->table('steps_tracker');
        $builder->select('date, steps')
               ->where('client_id', $clientId)
               ->where('date >=', $start_date)
               ->where('date <=', $end_date)
               ->orderBy('date', 'ASC');
        
        $result = $builder->get()->getResult();
        
        // Fill missing dates with zero steps
        $start = strtotime($start_date);
        $end = strtotime($end_date);
        
        // Create associative array with dates as keys
        $dataByDate = [];
        foreach ($result as $row) {
            $dataByDate[$row->date] = intval($row->steps ?? 0);
        }
        
        // Generate all dates in range
        $categories = [];
        $series = [];
        $detailed_data = [];
        
        $current = $start;
        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $dayName = date('D', $current);
            
            $steps = isset($dataByDate[$date]) ? intval($dataByDate[$date]) : 0;
            $calories = $this->calculateCalories($steps);
            
            $categories[] = $dayName;
            $series[] = $steps;
            $detailed_data[] = [
                'date' => $date,
                'formatted_date' => date('M d, Y', $current),
                'day_name' => $dayName,
                'steps' => $steps,
                'calories' => $calories,
                'target' => 10000 // Default target
            ];
            
            $current = strtotime('+1 day', $current);
        }
        
        // Get target for completion calculation
        $target = $this->steps_target_model->getCurrentTarget($clientId) ?? 10000;
        
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
        log_message('error', 'Steps Chart Error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error loading chart data',
            'error' => $e->getMessage()
        ]);
    }
}
    
    /**
     * Get Steps statistics (AJAX endpoint)
     * Route: GET /admin/steps/getStepsStats/:clientId
     */
    public function getStepsStats($clientId)
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
            
            // Get today's steps
            $builder = $db->table('steps_tracker');
            $builder->select('steps')
                   ->where('client_id', $clientId)
                   ->where('date', $today)
                   ->limit(1);
            $todayResult = $builder->get()->getRow();
            $todaySteps = $todayResult ? intval($todayResult->steps) : 0;
            
            // Get weekly average (last 7 days)
            $builder = $db->table('steps_tracker');
            $builder->select('AVG(steps) as avg_steps')
                   ->where('client_id', $clientId)
                   ->where('date >=', date('Y-m-d', strtotime('-6 days')));
            $avgResult = $builder->get()->getRow();
            $weeklyAvg = $avgResult ? round(floatval($avgResult->avg_steps)) : 0;
            
            // Get current target
            $target = $this->steps_target_model->getCurrentTarget($clientId);
            
            // Calculate today's completion
            $completion = $target > 0 ? round(($todaySteps / $target) * 100, 1) : 0;
            
            // Get total steps this month
            $monthStart = date('Y-m-01');
            $monthEnd = date('Y-m-t');
            
            $builder = $db->table('steps_tracker');
            $builder->select('SUM(steps) as total_steps')
                   ->where('client_id', $clientId)
                   ->where('date >=', $monthStart)
                   ->where('date <=', $monthEnd);
            $monthResult = $builder->get()->getRow();
            $monthlySteps = $monthResult ? intval($monthResult->total_steps) : 0;
            
            // Calculate monthly calories
            $monthlyCalories = $this->calculateCalories($monthlySteps);
            
            // Get total records count
            $builder = $db->table('steps_tracker');
            $builder->where('client_id', $clientId);
            $totalRecords = $builder->countAllResults();
            
            // Get streak (consecutive days with steps > 0)
            $streak = $this->calculateStepsStreak($clientId);
            
            $stats = [
                'today_steps' => $todaySteps,
                'weekly_average' => $weeklyAvg,
                'daily_target' => $target,
                'completion_rate' => $completion,
                'monthly_total' => $monthlySteps,
                'monthly_calories' => $monthlyCalories,
                'total_records' => $totalRecords,
                'current_streak' => $streak,
                'calories_per_step' => 0.04,
                'last_updated' => $today
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Steps Stats Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading statistics'
            ]);
        }
    }
    
    /**
     * Save Steps target (AJAX endpoint)
     * Route: POST /admin/steps/saveTarget
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
            'target_steps' => ['label' => 'Target Steps', 'rules' => 'required|numeric|greater_than_equal_to[0]'],
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
        $target_steps = $this->request->getPost('target_steps');
        $target_date = $this->request->getPost('target_date') ?? date('Y-m-d');
        
        $data = [
            'client_id' => $client_id,
            'target_steps' => $target_steps,
            'target_date' => $target_date,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            // Check if target exists for this date
            $existing = $this->common_model->getAllData('steps_target', [
                'client_id' => $client_id,
                'target_date' => $target_date
            ]);
            
            if (!empty($existing)) {
                // Update existing target
                $condition = [
                    'client_id' => $client_id,
                    'target_date' => $target_date
                ];
                $result = $this->common_model->updateData('steps_target', $data, $condition);
                $message = 'Target updated successfully';
            } else {
                // Insert new target
                $result = $this->common_model->addData('steps_target', $data);
                $message = 'Target saved successfully';
            }
            
            return $this->response->setJSON([
                'success' => (bool)$result,
                'message' => $result ? $message : 'Failed to save target',
                'target' => $target_steps,
                'date' => $target_date
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
     * Route: GET /admin/steps/getDateData/:clientId
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
            
            // Get steps for the date
            $condition = [
                'client_id' => $clientId,
                'date' => $date
            ];
            
            $record = $this->common_model->getAllData('steps_tracker', $condition);
            $steps = $record ? intval($record[0]->steps) : 0;
            
            // Calculate calories
            $calories = $this->calculateCalories($steps);
            
            // Get target for this date
            $target = $this->steps_target_model->getTargetForDate($clientId, $date);
            
            // Calculate completion
            $completion = $target > 0 ? round(($steps / $target) * 100, 1) : 0;
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'formatted_date' => date('M d, Y', strtotime($date)),
                    'steps' => $steps,
                    'calories' => $calories,
                    'target' => $target,
                    'completion' => $completion
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
     * Route: POST /admin/steps/quickUpdate
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
            'steps' => ['label' => 'Steps', 'rules' => 'required|numeric|greater_than_equal_to[0]']
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
        $steps = $this->request->getPost('steps');
        
        // Calculate calories
        $calories = $this->calculateCalories($steps);
        
        $data = [
            'client_id' => $client_id,
            'date' => $date,
            'steps' => $steps,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            // Check if record exists
            $existing = $this->common_model->getAllData('steps_tracker', [
                'client_id' => $client_id,
                'date' => $date
            ]);
            
            if (!empty($existing)) {
                // Update existing record
                $condition = [
                    'client_id' => $client_id,
                    'date' => $date
                ];
                $result = $this->common_model->updateData('steps_tracker', $data, $condition);
                $action = 'updated';
            } else {
                // Insert new record
                $data['created_at'] = date('Y-m-d H:i:s');
                $result = $this->common_model->addData('steps_tracker', $data);
                $action = 'added';
            }
            
            if ($result) {
                // Save calories data
                $this->saveCaloriesData($client_id, $date, $calories);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Steps $action successfully!",
                    'date' => $date,
                    'steps' => $steps,
                    'calories' => $calories,
                    'action' => $action
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update steps'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Quick Update Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating steps'
            ]);
        }
    }
    
    /**
     * Helper: Calculate calories from steps
     */
    private function calculateCalories($steps)
    {
        return round($steps * 0.04);
    }
    
    /**
     * Helper: Save calories data to steps_calories table
     */
    private function saveCaloriesData($client_id, $date, $calories)
    {
        $user_id = session()->get('userid') ?? 1;
        
        $calorieData = [
            'count_date' => $date . ' 12:00:00',
            'data_count' => $calories,
            'count_type' => 1, // 1: calories
            'client_id' => $client_id,
            'created_on' => date('Y-m-d'),
            'created_by' => $user_id,
            'updated_on' => date('Y-m-d'),
            'updated_by' => $user_id
        ];
        
        // Check if calories record exists
        $existing = $this->common_model->getAllData('steps_calories', [
            'client_id' => $client_id,
            'DATE(count_date)' => $date,
            'count_type' => 1
        ]);
        
        if (!empty($existing)) {
            // Update existing
            $condition = [
                'client_id' => $client_id,
                'DATE(count_date)' => $date,
                'count_type' => 1
            ];
            $this->common_model->updateData('steps_calories', $calorieData, $condition);
        } else {
            // Insert new
            $this->common_model->addData('steps_calories', $calorieData);
        }
    }
    
    /**
     * Helper: Calculate steps streak
     */
    private function calculateStepsStreak($client_id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('steps_tracker');
        
        $builder->select('date, steps')
               ->where('client_id', $client_id)
               ->where('steps >', 0)
               ->orderBy('date', 'DESC');
        
        $result = $builder->get()->getResult();
        
        if (empty($result)) {
            return 0;
        }
        
        $streak = 0;
        $currentDate = date('Y-m-d');
        
        foreach ($result as $row) {
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
     * Manual Steps Calculation (non-AJAX)
     */
    public function calculateManual()
    {
        $steps = $this->request->getPost('steps');
        
        if ($steps) {
            $calories = $this->calculateCalories($steps);
            
            return $this->response->setJSON([
                'success' => true,
                'calories' => $calories
            ]);
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid parameters'
        ]);
    }
    
}