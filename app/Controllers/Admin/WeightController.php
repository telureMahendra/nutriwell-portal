<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Weight_model;
use App\Models\Common_model;
use Config\Services;

class WeightController extends BaseController
{
    protected $session;
    protected $validation;
    protected $weightModel;
    protected $common_model;
    
    public function __construct()
    {
        $this->validation = Services::validation();
        $this->weightModel = model('App\Models\Weight_model', false);
        $this->common_model = model('App\Models\Common_model', false);
        $this->session = session();
        
        if (!is_admin_logged_in()) {
            redirect('');
        }
        
        $controller = class_basename(service('router')->controllerName());
        $methodName  = class_basename(service('router')->methodName());
        //hasAccess($controller, $methodName);
    }

    /**
     * Get weight data for modal (API endpoint)
     */
    public function getWeightData($client_id)
    {
        if ($this->session->has('userid')) {
            try {
                $weight_data = $this->weightModel->getWeightStats($client_id);
                $weekly_change = $this->weightModel->getWeeklyChange($client_id);
                $monthly_change = $this->weightModel->getMonthlyChange($client_id);
                $recent_logs = $this->weightModel->getRecentLogs($client_id, 5);
                
                $response = [
                    'success' => true,
                    'data' => [
                        'current' => $weight_data['current'] ?? 0,
                        'start' => $weight_data['start'] ?? 0,
                        'target' => $weight_data['target'] ?? 0,
                        'change' => $weight_data['change'] ?? 0,
                        'change_percentage' => $weight_data['change_percentage'] ?? 0,
                        'progress' => $weight_data['progress'] ?? 0,
                        'weekly_change' => $weekly_change,
                        'monthly_change' => $monthly_change,
                        'recent_logs' => $recent_logs
                    ]
                ];
                
                return $this->response->setJSON($response);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error fetching weight data: ' . $e->getMessage()
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
        }
    }

    /**
     * Get graph data (API endpoint)
     */
    public function getWeightGraphData($client_id, $days = 30)
    {
        if ($this->session->has('userid')) {
            try {
                $history = $this->weightModel->getWeightHistory($client_id, $days);
                
                $seriesData = [];
                $targetSeries = [];
                $categories = [];
                $tooltipDates = [];
                $dataPoints = [];
                
                foreach ($history as $record) {
                    $seriesData[] = floatval($record->weight);
                    $targetSeries[] = floatval($record->target_weight);
                    $categories[] = date('M d', strtotime($record->created_on));
                    $tooltipDates[] = date('Y-m-d H:i', strtotime($record->created_on));
                    
                    $dataPoints[] = [
                        'weight_id' => $record->weight_id,
                        'weight' => $record->weight,
                        'target_weight' => $record->target_weight,
                        'date' => date('Y-m-d', strtotime($record->created_on)),
                        'time' => date('H:i', strtotime($record->created_on)),
                        'full_date' => date('Y-m-d H:i:s', strtotime($record->created_on)),
                        'notes' => $record->notes
                    ];
                }
                
                $response = [
                    'success' => true,
                    'series' => $seriesData,
                    'targetSeries' => $targetSeries,
                    'categories' => $categories,
                    'tooltipDates' => $tooltipDates,
                    'dataPoints' => $dataPoints
                ];
                
                return $this->response->setJSON($response);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error fetching graph data: ' . $e->getMessage()
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
        }
    }

    /**
     * Add new weight entry
     */
    public function addWeightEntry()
    {
        if ($this->session->has('userid')) {
            if ($this->request->getMethod() === 'post') {
                try {
                    $client_id = $this->request->getPost('client_id');
                    $weight = $this->request->getPost('weight');
                    $target_weight = $this->request->getPost('target_weight');
                    $date = $this->request->getPost('date');
                    $notes = $this->request->getPost('notes');
                    
                    // Get current target weight from profile if not provided
                    if (empty($target_weight)) {
                        $target_weight = $this->weightModel->getTargetWeight($client_id) ?? 0;
                    }
                    
                    // Update client profile target weight if provided
                    if ($target_weight > 0) {
                        $db = db_connect();
                        $builder = $db->table('client_profile');
                        $builder->where('client_id', $client_id);
                        $builder->update(['target_weight' => $target_weight]);
                    }
                    
                    $insertData = [
                        'client_id' => $client_id,
                        'weight' => $weight,
                        'target_weight' => $target_weight,
                        'notes' => $notes,
                        'created_on' => $date ? date('Y-m-d H:i:s', strtotime($date)) : date('Y-m-d H:i:s'),
                        'created_by' => $this->session->get('uid') ?? 1,
                        'updated_on' => date('Y-m-d H:i:s'),
                        'updated_by' => $this->session->get('uid') ?? 1
                    ];
                    
                    $weight_id = $this->weightModel->addWeight($insertData);
                    
                    if ($weight_id > 0) {
                        $response = [
                            'success' => true,
                            'message' => 'Weight logged successfully!',
                            'weight_id' => $weight_id
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Failed to save weight entry'
                        ];
                    }
                    
                    return $this->response->setJSON($response);
                } catch (\Exception $e) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ]);
                }
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
        }
    }

    /**
     * Update weight entry
     */
    public function updateWeightEntry()
    {
        if ($this->session->has('userid')) {
            if ($this->request->getMethod() === 'post') {
                try {
                    $weight_id = $this->request->getPost('weight_id');
                    $client_id = $this->request->getPost('client_id');
                    $weight = $this->request->getPost('weight');
                    $target_weight = $this->request->getPost('target_weight');
                    $date = $this->request->getPost('date');
                    $notes = $this->request->getPost('notes');
                    
                    // Update client profile target weight if changed
                    if ($target_weight) {
                        $db = db_connect();
                        $builder = $db->table('client_profile');
                        $builder->where('client_id', $client_id);
                        $builder->update(['target_weight' => $target_weight]);
                    }
                    
                    $updateData = [
                        'weight' => $weight,
                        'target_weight' => $target_weight,
                        'notes' => $notes,
                        'updated_on' => date('Y-m-d H:i:s'),
                        'updated_by' => $this->session->get('uid') ?? 1
                    ];
                    
                    // If date is provided, update created_on
                    if ($date) {
                        $updateData['created_on'] = date('Y-m-d H:i:s', strtotime($date));
                    }
                    
                    $success = $this->weightModel->updateWeight($updateData, $weight_id);
                    
                    $response = [
                        'success' => $success,
                        'message' => $success ? 'Weight updated successfully!' : 'Failed to update weight'
                    ];
                    
                    return $this->response->setJSON($response);
                } catch (\Exception $e) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ]);
                }
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
        }
    }

    /**
     * Get weight entry by ID
     */
    public function getWeightEntry($weight_id)
    {
        if ($this->session->has('userid')) {
            try {
                $weight = $this->weightModel->getWeightById($weight_id);
                
                if ($weight) {
                    // Format date for datetime-local input
                    $weight->formatted_date = date('Y-m-d\TH:i', strtotime($weight->created_on));
                    
                    $response = [
                        'success' => true,
                        'data' => $weight
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Weight entry not found'
                    ];
                }
                
                return $this->response->setJSON($response);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
            }
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
        }
    }

    /**
     * Delete weight entry
     */
    public function deleteWeightEntry()
    {
        if ($this->session->has('userid')) {
            if ($this->request->getMethod() === 'post') {
                try {
                    $weight_id = $this->request->getPost('weight_id');
                    
                    $success = $this->weightModel->deleteWeight($weight_id);
                    
                    $response = [
                        'success' => $success,
                        'message' => $success ? 'Weight entry deleted successfully!' : 'Failed to delete weight entry'
                    ];
                    
                    return $this->response->setJSON($response);
                } catch (\Exception $e) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage()
                    ]);
                }
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
        }
    }
}