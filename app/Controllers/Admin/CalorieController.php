<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CalorieModel;
use App\Models\CaloriesBurnedModel;
use App\Models\CaloriesTargetModel;
use App\Models\FoodModel;
use App\Models\FoodTypeModel;

class CalorieController extends BaseController
{
    protected $calorieModel;
    protected $caloriesBurnedModel;
    protected $caloriesTargetModel;
    protected $foodModel;
    protected $foodTypeModel;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->calorieModel = new CalorieModel();
        $this->caloriesBurnedModel = new CaloriesBurnedModel();
        $this->caloriesTargetModel = new CaloriesTargetModel();
        $this->foodModel = new FoodModel();
        $this->foodTypeModel = new FoodTypeModel();
        $this->session = session();
        $this->db = \Config\Database::connect();

        if (!$this->session->get('is_admin_logged_in')) {
            return redirect()->to(base_url('admin/login'));
        }
    }

    /**
     * Get calorie data for a specific date
     * FIXED: Proper array handling and consistent data format
     */
    public function getCalorieData()
    {
        try {
            $client_id = $this->request->getPost('client_id');
            $date = $this->request->getPost('date') ?: date('Y-m-d');

            log_message('debug', "CalorieController::getCalorieData - Client: {$client_id}, Date: {$date}");

            if (empty($client_id)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Client ID is required'
                ]);
            }

            // Get today's totals
            $today_intake = $this->calorieModel->getTodayCalories($client_id, $date);
            $today_burned = $this->caloriesBurnedModel->getTodayBurned($client_id);

            log_message('debug', "Today's intake: {$today_intake}, burned: {$today_burned}");

            // Get targets
            $intake_target = $this->caloriesTargetModel->getClientTarget($client_id, $date, 'intake');
            $burned_target = $this->caloriesTargetModel->getClientTarget($client_id, $date, 'burned');

            // Get food logs for the date
            $foods = $this->calorieModel->getMealsByDate($client_id, $date);
            
            // FIXED: Format food data consistently as arrays (not objects)
            $formatted_foods = [];
            if (!empty($foods)) {
                foreach ($foods as $food) {
                    // Ensure we're working with array, not object
                    $foodArray = is_object($food) ? (array)$food : $food;
                    
                    $calories_per_unit = $foodArray['calories_per_unit'] ?? $foodArray['calories'] ?? 0;
                    $quantity = $foodArray['quantity'] ?? 1;
                    $food_name = $foodArray['food_name'] ?? 'Custom Food';
                    $food_id = $foodArray['food_id'] ?? 0;
                    $record_id = $foodArray['calorie_id'] ?? $foodArray['id'] ?? null;
                    $food_date = $foodArray['date'] ?? $date;
                    
                    $total_calories = (float)$calories_per_unit * (float)$quantity;
                    
                    $formatted_foods[] = [
                        'id' => $record_id,
                        'time' => date('H:i', strtotime($food_date)),
                        'name' => $food_name,
                        'calories' => $total_calories,
                        'calories_per_unit' => (float)$calories_per_unit,
                        'quantity' => (float)$quantity,
                        'food_id' => $food_id
                    ];
                }
            }

            log_message('debug', "Formatted foods count: " . count($formatted_foods));

            // FIXED: Get activity logs for the date with proper array handling
            $activities_result = $this->caloriesBurnedModel->getActivitiesByDate($client_id, $date);
            
            // Handle both array and object returns from model
            $activities_data = [];
            if (is_array($activities_result)) {
                // Check if it's the new format with 'activities' key
                if (isset($activities_result['activities'])) {
                    $activities_data = $activities_result['activities'];
                } else {
                    // It's a direct array of activities
                    $activities_data = $activities_result;
                }
            }
            
            $formatted_activities = [];
            if (!empty($activities_data)) {
                foreach ($activities_data as $activity) {
                    // Ensure we're working with array, not object
                    $activityArray = is_object($activity) ? (array)$activity : $activity;
                    
                    $activity_date = $activityArray['date'] ?? $activityArray['created_at'] ?? $date;
                    $calories_burned = $activityArray['calories_burned'] ?? 0;
                    $duration_minutes = $activityArray['duration_minutes'] ?? null;
                    
                    // FIXED: Handle both activity_name and activity_type
                    $activity_name = $activityArray['activity_name'] ?? 
                                   $activityArray['activity_type'] ?? 
                                   'Activity';
                    
                    $record_id = $activityArray['id'] ?? 
                               $activityArray['calorie_burned_id'] ?? 
                               null;
                    
                    $formatted_activities[] = [
                        'id' => $record_id,
                        'time' => date('H:i', strtotime($activity_date)),
                        'name' => $activity_name,
                        'calories' => (float)$calories_burned,
                        'duration' => !empty($duration_minutes) 
                            ? $duration_minutes . ' min' 
                            : null
                    ];
                }
            }

            log_message('debug', "Formatted activities count: " . count($formatted_activities));

            // Get chart history - days param from request (default 7)
            $chart_days = (int)($this->request->getPost('days') ?? 7);
            if ($chart_days < 1) $chart_days = 7;

            $weekly_intake = $this->calorieModel->getCalorieHistory($client_id, $chart_days);
            $weekly_burned = $this->caloriesBurnedModel->getBurnedHistory($client_id, $chart_days, 'ASC');

            log_message('debug', "Chart intake records: " . count($weekly_intake ?? []));
            log_message('debug', "Chart burned records: " . count($weekly_burned ?? []));

            // Prepare chart data
            $weekly_intake_data = array_fill(0, $chart_days, 0);
            $weekly_burned_data = array_fill(0, $chart_days, 0);
            $weekly_categories = [];
            $weekly_dates = [];

            for ($i = $chart_days - 1; $i >= 0; $i--) {
                $current_date = date('Y-m-d', strtotime("-$i days"));
                $index = $chart_days - 1 - $i;

                $weekly_categories[] = date('D', strtotime($current_date));
                $weekly_dates[] = date('M d', strtotime($current_date));

                // Match intake data - FIXED: Handle both arrays and objects
                if (!empty($weekly_intake)) {
                    foreach ($weekly_intake as $intake) {
                        $intakeArray = is_object($intake) ? (array)$intake : $intake;
                        
                        $intake_date = $intakeArray['day_date'] ?? null;
                        $total_calories = $intakeArray['total_calories'] ?? 0;
                        
                        if ($intake_date == $current_date) {
                            $weekly_intake_data[$index] = (float)$total_calories;
                            break;
                        }
                    }
                }

                // Match burned data - FIXED: Handle both arrays and objects
                if (!empty($weekly_burned)) {
                    foreach ($weekly_burned as $burned) {
                        $burnedArray = is_object($burned) ? (array)$burned : $burned;
                        
                        $burned_date = $burnedArray['day_date'] ?? 
                                     $burnedArray['date'] ?? 
                                     null;
                        
                        $total_burned = $burnedArray['total_burned'] ?? 
                                      $burnedArray['calories_burned'] ?? 
                                      0;
                        
                        if ($burned_date == $current_date) {
                            $weekly_burned_data[$index] = (float)$total_burned;
                            break;
                        }
                    }
                }
            }

            // Calculate averages
            $weekly_avg_intake = count(array_filter($weekly_intake_data)) > 0 
                ? array_sum($weekly_intake_data) / count(array_filter($weekly_intake_data, function($v) { return $v > 0; })) 
                : 0;
                
            $weekly_avg_burned = count(array_filter($weekly_burned_data)) > 0
                ? array_sum($weekly_burned_data) / count(array_filter($weekly_burned_data, function($v) { return $v > 0; }))
                : 0;

            $response = [
                'status' => 'success',
                'data' => [
                    'today_intake' => (float)$today_intake,
                    'today_burned' => (float)$today_burned,
                    'intake_target' => (float)$intake_target,
                    'burned_target' => (float)$burned_target,
                    'foods' => $formatted_foods,
                    'activities' => $formatted_activities,
                    'weekly_intake' => $weekly_intake_data,
                    'weekly_burned' => $weekly_burned_data,
                    'weekly_categories' => $weekly_categories,
                    'weekly_dates' => $weekly_dates,
                    'weekly_avg_intake' => round($weekly_avg_intake),
                    'weekly_avg_burned' => round($weekly_avg_burned)
                ]
            ];

            log_message('debug', "Response prepared successfully");
            log_message('debug', "Response: " . json_encode($response));

            return $this->response->setJSON($response);

        } catch (\Exception $e) {
            log_message('error', 'Error in getCalorieData: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to load calorie data: ' . $e->getMessage(),
                'trace' => ENVIRONMENT === 'development' ? $e->getTraceAsString() : null
            ]);
        }
    }

    /**
     * Save calorie data (food and activity entries)
     */
    public function saveCalorieData()
    {
        $db = \Config\Database::connect();
        
        try {
            $client_id = $this->request->getPost('client_id');
            $date = $this->request->getPost('date');
            $foods = json_decode($this->request->getPost('foods') ?? '[]', true);
            $activities = json_decode($this->request->getPost('activities') ?? '[]', true);

            log_message('debug', "Saving calorie data for client {$client_id} on {$date}");
            log_message('debug', "Foods to save: " . count($foods));
            log_message('debug', "Activities to save: " . count($activities));

            if (empty($client_id) || empty($date)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Client ID and date are required'
                ]);
            }

            $db->transStart();

            // Save new food entries
            if (is_array($foods)) {
                foreach ($foods as $food) {
                    if (isset($food['is_new']) && $food['is_new']) {
                        // Find or create food
                        $food_id = $this->findOrCreateFood(
                            $food['name'], 
                            $food['calories_per_unit'] ?? $food['calories'] ?? 0
                        );
                        
                        $foodData = [
                            'client_id' => $client_id,
                            'food_id' => $food_id,
                            'food_type_id' => $food['food_type_id'] ?? 1,
                            'quantity' => $food['quantity'] ?? 1,
                            'calories' => $food['calories_per_unit'] ?? $food['calories'] ?? 0,
                            'date' => $date . ' ' . ($food['time'] ?? '12:00') . ':00',
                            'created_on' => date('Y-m-d H:i:s'),
                            'created_by' => $this->session->get('user_id') ?? 1,
                            'updated_on' => date('Y-m-d H:i:s'),
                            'updated_by' => $this->session->get('user_id') ?? 1
                        ];
                        
                        $this->calorieModel->insert($foodData);
                        log_message('debug', "Inserted food: {$food['name']}");
                    }
                }
            }

            // Save new activity entries
            if (is_array($activities)) {
                foreach ($activities as $activity) {
                    if (isset($activity['is_new']) && $activity['is_new']) {
                        // Handle duration properly
                        $duration = $activity['duration'] ?? '0 min';
                        if (strpos($duration, 'min') !== false) {
                            $duration_minutes = (int)str_replace(' min', '', $duration);
                        } else {
                            $duration_minutes = (int)$duration;
                        }
                        
                        // FIXED: Use activity_name or activity_type based on your DB schema
                        // Check your database to see which column name you have
                        $activityData = [
                            'client_id' => $client_id,
                            'activity_type' => $activity['name'] ?? 'Activity', // Change to 'activity_name' if needed
                            'calories_burned' => $activity['calories'] ?? 0,
                            'duration_minutes' => $duration_minutes,
                            'date' => $date,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        
                        $this->caloriesBurnedModel->insert($activityData);
                        log_message('debug', "Inserted activity: {$activity['name']}");
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Database transaction failed');
            }

            log_message('debug', "Calorie data saved successfully");

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Data saved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in saveCalorieData: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to save data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete food item
     */
    public function deleteFoodItem()
    {
        try {
            $calorie_id = $this->request->getPost('calorie_id');

            if (empty($calorie_id)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Food ID is required'
                ]);
            }

            $this->calorieModel->delete($calorie_id);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Food item deleted successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error deleting food: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete food item: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete activity item
     */
    public function deleteActivityItem()
    {
        try {
            $activity_id = $this->request->getPost('activity_id');

            if (empty($activity_id)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Activity ID is required'
                ]);
            }

            $this->caloriesBurnedModel->delete($activity_id);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Activity deleted successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error deleting activity: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to delete activity: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update calorie targets
     */
    public function updateTargets()
    {
        try {
            $client_id = $this->request->getPost('client_id');
            $intake_target = $this->request->getPost('intake_target');
            $burned_target = $this->request->getPost('burned_target');
            $date = $this->request->getPost('date') ?: date('Y-m-d');

            if (empty($client_id)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Client ID is required'
                ]);
            }

            // Set intake target
            $this->caloriesTargetModel->setTarget($client_id, $intake_target, 'intake', $date);
            
            // Set burned target
            $this->caloriesTargetModel->setTarget($client_id, $burned_target, 'burned', $date);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Targets updated successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in updateTargets: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to update targets: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get food list for autocomplete
     */
    public function getFoodList()
    {
        try {
            $search = $this->request->getGet('search');
            
            if (empty($search)) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => []
                ]);
            }
            
            $foods = $this->foodModel->searchFood($search, 10);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $foods
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in getFoodList: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to fetch food list: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * HELPER: Find or create food
     */
    private function findOrCreateFood($food_name, $calories = 0)
    {
        try {
            // Check if food exists
            $existingFood = $this->foodModel->where('food_name', $food_name)->first();
            
            if ($existingFood) {
                if (is_object($existingFood)) {
                    return $existingFood->food_id ?? $existingFood->id;
                } else {
                    return $existingFood['food_id'] ?? $existingFood['id'];
                }
            }
            
            // Create new food
            $foodData = [
                'food_name' => $food_name,
                'calories' => $calories,
                'food_type_id' => 1,
                'is_active' => 1,
                'created_on' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->get('user_id') ?? 1,
                'updated_on' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->get('user_id') ?? 1
            ];
            
            return $this->foodModel->insert($foodData);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in findOrCreateFood: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get food types for dropdown
     */
    public function getFoodTypes()
    {
        try {
            $foodTypes = $this->foodTypeModel->getActiveFoodTypes();
            
            return $this->response->setJSON([
                'status' => 'success',
                'data' => $foodTypes
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getFoodTypes: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to fetch food types'
            ]);
        }
    }

    /**
     * Quick add calorie entry (simple API)
     */
    public function quickAddCalorie()
    {
        try {
            $client_id = $this->request->getPost('client_id');
            $food_name = $this->request->getPost('food_name');
            $calories = $this->request->getPost('calories');
            $quantity = $this->request->getPost('quantity') ?? 1;
            $date = $this->request->getPost('date') ?? date('Y-m-d H:i:s');

            if (empty($client_id) || empty($food_name) || empty($calories)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Required fields missing'
                ]);
            }

            $food_id = $this->findOrCreateFood($food_name, $calories);
            
            $data = [
                'client_id' => $client_id,
                'food_id' => $food_id,
                'food_type_id' => 1,
                'calories' => $calories,
                'quantity' => $quantity,
                'date' => $date,
                'created_on' => date('Y-m-d H:i:s'),
                'created_by' => $this->session->get('user_id') ?? 1,
                'updated_on' => date('Y-m-d H:i:s'),
                'updated_by' => $this->session->get('user_id') ?? 1
            ];

            $this->calorieModel->insert($data);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Calorie entry added successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in quickAddCalorie: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Failed to add calorie entry'
            ]);
        }
    }
}