<?php 
namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;
use App\Models\BmiModel;

class BmiController extends Controller
{
    protected $session;
    protected $validation;
    protected $bmi_model;
    protected $common_model;

    public function __construct() {
        $this->validation = Services::validation();
        $this->bmi_model = new BmiModel();
        $this->common_model = model('App\Models\Common_model', false);
        $this->session = session();
        
        // Check if admin is logged in (similar to reference)
        if (!is_admin_logged_in()) {
            redirect('');
        }
    }

    /**
     * Save BMI record (AJAX endpoint)
     */
    public function saveBmi()
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
            'weight' => ['label' => 'Weight', 'rules' => 'required|decimal'],
            'height' => ['label' => 'Height', 'rules' => 'required|decimal'],
            'height_unit' => ['label' => 'Height Unit', 'rules' => 'required|in_list[CM,FIT,INCH]'],
            'age' => ['label' => 'Age', 'rules' => 'permit_empty|numeric'],
            'calories' => ['label' => 'Calories', 'rules' => 'permit_empty|decimal']
        ];
        
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }
        
        $data = [
            'client_id' => $this->request->getPost('client_id'),
            'date' => $this->request->getPost('date'),
            'weight' => $this->request->getPost('weight'),
            'height' => $this->request->getPost('height'),
            'height_unit' => $this->request->getPost('height_unit'),
            'age' => $this->request->getPost('age') ?? 0,
            'calories' => $this->request->getPost('calories') ?? 0,
            'created_on' => date('Y-m-d H:i:s'),
            'created_by' => session()->get('userid'),  // Use 'userid' from reference
            'updated_on' => date('Y-m-d H:i:s'),
            'updated_by' => session()->get('userid')
        ];
        
        // Check if we're updating existing record
        $bmi_id = $this->request->getPost('bmi_id');
        
        try {
            if (!empty($bmi_id) && $bmi_id > 0) {
                // Update existing record
                $condition = ['bmi_id' => $bmi_id];
                $result = $this->common_model->updateData('bmi_record', $data, $condition);
                $message = 'BMI record updated successfully';
            } else {
                // Insert new record
                $result = $this->common_model->addData('bmi_record', $data);
                $message = 'BMI record saved successfully';
            }
            
            if ($result) {
                // Calculate BMI value
                $bmiValue = $this->calculateBmiValue(
                    $data['weight'],
                    $data['height'],
                    $data['height_unit']
                );
                
                // Update with calculated BMI if needed
                if (!empty($bmi_id) && $bmi_id > 0) {
                    $updateBmi = ['bmi_value' => $bmiValue];
                    $this->common_model->updateData('bmi_record', $updateBmi, ['bmi_id' => $bmi_id]);
                } else {
                    $updateBmi = ['bmi_value' => $bmiValue];
                    $this->common_model->updateData('bmi_record', $updateBmi, ['bmi_record_id' => $result]);
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message,
                    'bmi_value' => $bmiValue,
                    'bmi_category' => $this->getBmiCategory($bmiValue),
                    'data' => $data
                ]);
            }
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save BMI record'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'BMI Save Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Server error occurred. Please try again.'
            ]);
        }
    }
    
    /**
 * Get BMI history (AJAX endpoint)
 */
public function getBmiHistory($clientId)
{
    if (!$this->request->isAJAX()) {
        return $this->response->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => 'Invalid request'
        ]);
    }
    
    try {
        // Debug: Log the client ID
        log_message('debug', 'Getting BMI history for client ID: ' . $clientId);
        
        $condition = ['client_id' => $clientId];
        $history = $this->common_model->getAllData('bmi_record', $condition, 'date DESC', 10);
        
        // Debug: Log what we got from database
        log_message('debug', 'Database query result count: ' . count($history));
        
        // Format the data
        $formattedHistory = [];
        foreach ($history as $record) {
            $formattedHistory[] = [
                'bmi_id' => $record->bmi_record_id ?? $record->bmi_id,
                'date' => $record->date,
                'weight' => floatval($record->weight),
                'height' => floatval($record->height),
                'height_unit' => $record->height_unit,
                'age' => intval($record->age),
                'calories' => floatval($record->calories),
                'bmi_value' => floatval($record->bmi_value)
            ];
            
            // Debug: Log each record
            log_message('debug', 'Record: ' . json_encode(end($formattedHistory)));
        }
        
        // Debug: Log final response
        log_message('debug', 'Sending response with ' . count($formattedHistory) . ' records');
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $formattedHistory
        ]);
        
    } catch (\Exception $e) {
        log_message('error', 'BMI History Error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error loading BMI history'
        ]);
    }
}
    
    /**
     * Get single BMI record (AJAX endpoint)
     */
    public function getBmiRecord($bmiId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            $condition = ['bmi_record_id' => $bmiId];
            $record = $this->common_model->getAllData('bmi_record', $condition);
            
            if (empty($record)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Record not found'
                ]);
            }
            
            $record = $record[0];
            $formattedRecord = [
                'bmi_id' => $record->bmi_record_id,
                'date' => $record->date,
                'weight' => floatval($record->weight),
                'height' => floatval($record->height),
                'height_unit' => $record->height_unit,
                'age' => intval($record->age),
                'calories' => floatval($record->calories),
                'bmi_value' => floatval($record->bmi_value)
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $formattedRecord
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Get BMI Record Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading record'
            ]);
        }
    }
    
    /**
     * Delete BMI record (AJAX endpoint)
     */
    public function deleteBmi($bmiId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            $condition = ['bmi_record_id' => $bmiId];
            $result = $this->common_model->deleteData('bmi_record', $condition);
            
            return $this->response->setJSON([
                'success' => (bool)$result,
                'message' => $result ? 'BMI record deleted successfully' : 'Failed to delete record'
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Delete BMI Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error deleting record'
            ]);
        }
    }
    
    /**
     * Get BMI chart data (AJAX endpoint)
     */
    public function getBmiChartData($clientId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            $db = \Config\Database::connect();
            $days = (int)($this->request->getGet('days') ?? 180);
            if ($days < 1) $days = 180;
            $startDate = date('Y-m-d', strtotime("-{$days} days"));

            $builder = $db->table('bmi_record');
            if ($days <= 90) {
                $builder->select('DATE_FORMAT(date, "%b %d") as month, ROUND(AVG(bmi_value), 2) as avg_bmi')
                       ->where('client_id', $clientId)
                       ->where('date >=', $startDate)
                       ->groupBy('DATE(date)')
                       ->orderBy('date', 'ASC');
            } else {
                $builder->select('DATE_FORMAT(date, "%b %Y") as month, ROUND(AVG(bmi_value), 2) as avg_bmi')
                       ->where('client_id', $clientId)
                       ->where('date >=', $startDate)
                       ->groupBy('MONTH(date), YEAR(date)')
                       ->orderBy('date', 'ASC');
            }
            
            $result = $builder->get()->getResult();
            
            $categories = [];
            $series = [];
            
            foreach ($result as $row) {
                $categories[] = $row->month;
                $series[] = floatval($row->avg_bmi);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'series' => [$series],
                    'categories' => $categories
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'BMI Chart Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading chart data'
            ]);
        }
    }
    
    /**
     * Get BMI statistics (AJAX endpoint)
     */
    public function getBmiStats($clientId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }
        
        try {
            $db = \Config\Database::connect();
            
            // Get current BMI (latest record)
            $builder = $db->table('bmi_record');
            $builder->select('bmi_value')
                   ->where('client_id', $clientId)
                   ->orderBy('date', 'DESC')
                   ->limit(1);
            $current = $builder->get()->getRow();
            $currentBmi = $current ? floatval($current->bmi_value) : 0;
            
            // Get start BMI (oldest record)
            $builder = $db->table('bmi_record');
            $builder->select('bmi_value')
                   ->where('client_id', $clientId)
                   ->orderBy('date', 'ASC')
                   ->limit(1);
            $start = $builder->get()->getRow();
            $startBmi = $start ? floatval($start->bmi_value) : 0;
            
            // Get average BMI
            $builder = $db->table('bmi_record');
            $builder->select('AVG(bmi_value) as avg_bmi')
                   ->where('client_id', $clientId);
            $avg = $builder->get()->getRow();
            $avgBmi = $avg ? floatval($avg->avg_bmi) : 0;
            
            // Target BMI (fixed or from client profile)
            $targetBmi = 22.0;
            
            // Calculate progress
            $progress = 0;
            if ($startBmi > 0 && $targetBmi > 0) {
                $totalChangeNeeded = abs($startBmi - $targetBmi);
                $currentChange = abs($currentBmi - $startBmi);
                if ($totalChangeNeeded > 0) {
                    $progress = min(100, round(($currentChange / $totalChangeNeeded) * 100));
                }
            }
            
            // Total records
            $builder = $db->table('bmi_record');
            $builder->where('client_id', $clientId);
            $totalRecords = $builder->countAllResults();
            
            $stats = [
                'current' => $currentBmi,
                'start' => $startBmi,
                'target' => $targetBmi,
                'avg' => $avgBmi,
                'progress' => $progress,
                'total_records' => $totalRecords,
                'category' => $this->getBmiCategory($currentBmi)
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'BMI Stats Error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading statistics'
            ]);
        }
    }
    
    /**
     * Calculate BMI value
     */
    private function calculateBmiValue($weight, $height, $unit)
    {
        // Convert height to meters
        switch ($unit) {
            case 'CM':
                $heightInMeters = $height / 100;
                break;
            case 'INCH':
                $heightInMeters = $height * 0.0254;
                break;
            case 'FIT':
                $heightInMeters = $height * 0.3048;
                break;
            default:
                $heightInMeters = $height / 100;
        }
        
        if ($heightInMeters <= 0) {
            return 0;
        }
        
        $bmi = $weight / ($heightInMeters * $heightInMeters);
        return round($bmi, 2);
    }
    
    /**
     * Get BMI category
     */
    private function getBmiCategory($bmi)
    {
        if ($bmi <= 0) return 'No Data';
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal';
        if ($bmi < 30) return 'Overweight';
        return 'Obesity';
    }
    
    /**
     * Manual BMI Calculation (non-AJAX)
     */
    public function calculateManual()
    {
        if (!$this->request->isAJAX()) {
            // Handle non-AJAX request
            $weight = $this->request->getPost('weight');
            $height = $this->request->getPost('height');
            $unit = $this->request->getPost('unit');
            
            if ($weight && $height && $unit) {
                $bmi = $this->calculateBmiValue($weight, $height, $unit);
                $category = $this->getBmiCategory($bmi);
                
                return $this->response->setJSON([
                    'success' => true,
                    'bmi' => $bmi,
                    'category' => $category
                ]);
            }
        }
        
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Invalid parameters'
        ]);
    }
}