<?php
namespace App\Controllers\Admin;
use CodeIgniter\Controller;
use App\Models\Water_model;

class Water extends Controller
{
    protected $waterModel;

    public function __construct()
    {
        $this->waterModel = new Water_model();
    }

    public function getWaterData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Method not allowed']);
        }

        $client_id = $this->request->getPost('client_id');
        $date = $this->request->getPost('date');

        // Get water data from model
        $waterData = $this->waterModel->getWater($date, $client_id);

        if (!empty($waterData)) {
            $data = $waterData[0];
            
            // Calculate completion percentage
            $completion = 0;
            if ($data->quantity > 0 && $data->target_quantity > 0) {
                $completion = min(100, round(($data->quantity / $data->target_quantity) * 100));
            }

            // Parse logs if they exist
            $logs = [];
            if (!empty($data->logs)) {
                $logs = json_decode($data->logs, true);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'today_intake' => floatval($data->quantity),
                    'daily_target' => floatval($data->target_quantity),
                    'completion' => $completion,
                    'logs' => $logs,
                    'unit' => $data->unit ?? 'ml',
                    'notes' => $data->notes ?? ''
                ]
            ]);
        } else {
            // Return empty structure
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'today_intake' => 0,
                    'daily_target' => 3.0,
                    'completion' => 0,
                    'logs' => [],
                    'unit' => 'ml',
                    'notes' => ''
                ]
            ]);
        }
    }

    public function saveWaterData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Method not allowed']);
        }

        $data = $this->request->getJSON(true);
        
        try {
            $client_id = $data['client_id'];
            $date = $data['date'];
            
            // Calculate total quantity from logs
            $totalQuantity = 0;
            $logs = $data['logs'] ?? [];
            foreach ($logs as $log) {
                $totalQuantity += $log['amount'];
            }

            // Convert to liters if needed (assuming logs are in ml)
            $quantityInLiters = $totalQuantity / 1000;

            // Prepare update data
            $updateData = [
                'quantity' => $quantityInLiters,
                'target_quantity' => $data['daily_target'],
                'logs' => json_encode($logs),
                'updated_on' => date('Y-m-d H:i:s'),
                'updated_by' => session()->get('userid')
            ];

            // Check if record exists
            $existingData = $this->waterModel->getWater($date, $client_id);
            
            if (!empty($existingData)) {
                // Update existing record
                $result = $this->waterModel->updateWater($date, $client_id, $updateData);
            } else {
                // Insert new record
                $insertData = $updateData;
                $insertData['client_id'] = $client_id;
                $insertData['date'] = $date;
                $insertData['created_on'] = date('Y-m-d H:i:s');
                $insertData['created_by'] = session()->get('userid');
                
                $result = $this->waterModel->addWater($insertData);
            }

            if ($result) {
                return $this->response->setJSON(['success' => true, 'message' => 'Water data saved successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to save water data']);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function getWaterRangeData()
    {
        $client_id = $this->request->getPost('client_id') ?? $this->request->getGet('client_id');
        $days = (int)($this->request->getPost('days') ?? $this->request->getGet('days') ?? 7);
        if ($days < 1) $days = 7;

        try {
            $db = \Config\Database::connect();
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

            $result = $db->table('water')
                ->select('DATE(date) as day, SUM(quantity) as total_qty')
                ->where('client_id', $client_id)
                ->where('date >=', $startDate)
                ->where('date <=', $endDate . ' 23:59:59')
                ->groupBy('DATE(date)')
                ->orderBy('date', 'ASC')
                ->get()->getResult();

            $byDate = [];
            foreach ($result as $row) {
                $byDate[$row->day] = floatval($row->total_qty);
            }

            $series = [];
            $categories = [];
            $current = strtotime($startDate);
            $end = strtotime($endDate);
            while ($current <= $end) {
                $d = date('Y-m-d', $current);
                $series[] = round($byDate[$d] ?? 0, 2);
                $categories[] = $days <= 90 ? date('M d', $current) : date('M Y', $current);
                $current = strtotime('+1 day', $current);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => ['series' => $series, 'categories' => $categories]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}