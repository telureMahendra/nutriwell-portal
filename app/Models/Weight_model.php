<?php
namespace App\Models;

use CodeIgniter\Model;

class Weight_model extends Model
{
    protected $table      = 'weight';
    protected $primaryKey = 'weight_id';
    protected $allowedFields = [
        'client_id',
        'weight',
        'target_weight',
        'created_on',
        'created_by',
        'updated_on',
        'updated_by',
        'notes',
        'measurement_time',
        'created_at',
        'updated_at'
    ];

    // Get weight for a specific date
    public function getWeight($date, $client_id)
    {
        $builder = $this->db->table('weight');
        $builder->select('*');
        $builder->where('client_id', $client_id);
        $builder->where("DATE(created_on) = '$date'");
        $builder->orderBy('weight_id', 'DESC');
        $builder->limit(1);
        $query = $builder->get();
        return $query->getResult() ?: [];
    }

    // Get weight record by ID
    public function getWeightById($weight_id)
    {
        return $this->where('weight_id', $weight_id)->first();
    }

    // Update weight — returns 1/0 for backward compat with Apis\Weight
    public function updateWeight($updateData, $weight_id)
    {
        $builder = $this->db->table('weight');
        $builder->where('weight_id', $weight_id);
        $builder->update($updateData);
        return $this->db->affectedRows() > 0 ? 1 : 0;
    }

    // Add weight record
    public function addWeight($insertData)
    {
        $builder = $this->db->table('weight');
        $builder->insert($insertData);
        return $this->db->insertID();
    }

    // Soft/hard delete a weight record
    public function deleteWeight($weight_id)
    {
        return $this->delete($weight_id);
    }

    // Get weight for a specific date (used by Apis\Weight::get_week_weight)
    public function getWeekWeight($date, $client_id)
    {
        $builder = $this->db->table('weight');
        $builder->select('*');
        $builder->where('client_id', $client_id);
        $builder->where("DATE(created_on) = '$date'");
        $query = $builder->get();
        return $query->getResult() ?: [];
    }

    // Get weight history for graph (last N days)
    public function getWeightHistory($client_id, $days = 30)
    {
        $endDate   = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-$days days"));

        $builder = $this->db->table('weight');
        $builder->select('weight_id, weight, target_weight, DATE(created_on) as date, created_on, notes, measurement_time');
        $builder->where('client_id', $client_id);
        $builder->where("DATE(created_on) >= '$startDate'");
        $builder->where("DATE(created_on) <= '$endDate'");
        $builder->orderBy('created_on', 'ASC');

        return $builder->get()->getResult() ?: [];
    }

    // Get the most recent weight entry
    public function getLatestWeight($client_id)
    {
        $builder = $this->db->table('weight');
        $builder->select('weight, target_weight, created_on, notes');
        $builder->where('client_id', $client_id);
        $builder->orderBy('created_on', 'DESC');
        $builder->limit(1);
        return $builder->get()->getRow();
    }

    // Get the first ever weight entry
    public function getFirstWeight($client_id)
    {
        $builder = $this->db->table('weight');
        $builder->select('weight, created_on');
        $builder->where('client_id', $client_id);
        $builder->orderBy('created_on', 'ASC');
        $builder->limit(1);
        return $builder->get()->getRow();
    }

    // Get target weight from client_profile
    public function getTargetWeight($client_id)
    {
        $builder = $this->db->table('client_profile');
        $builder->select('target_weight');
        $builder->where('client_id', $client_id);
        $builder->limit(1);
        $result = $builder->get()->getRow();
        return $result ? $result->target_weight : null;
    }

    // Get recent weight logs
    public function getRecentLogs($client_id, $limit = 10)
    {
        $builder = $this->db->table('weight');
        $builder->select('weight_id, weight, target_weight, DATE(created_on) as date, TIME(created_on) as time, notes, measurement_time');
        $builder->where('client_id', $client_id);
        $builder->orderBy('created_on', 'DESC');
        $builder->limit($limit);
        return $builder->get()->getResult() ?: [];
    }

    // Get week-over-week weight change
    public function getWeeklyChange($client_id)
    {
        $oneWeekAgo  = date('Y-m-d', strtotime('-7 days'));
        $latest      = $this->getLatestWeight($client_id);
        $todayWeight = $latest ? $latest->weight : null;

        $builder = $this->db->table('weight');
        $builder->select('weight');
        $builder->where('client_id', $client_id);
        $builder->where("DATE(created_on) <= '$oneWeekAgo'");
        $builder->orderBy('created_on', 'DESC');
        $builder->limit(1);
        $row            = $builder->get()->getRow();
        $weekAgoWeight  = $row ? $row->weight : null;

        if ($todayWeight && $weekAgoWeight) {
            $change = round($todayWeight - $weekAgoWeight, 1);
            return [
                'value'     => $change,
                'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'same'),
                'icon'      => $change > 0 ? 'fa-arrow-up' : ($change < 0 ? 'fa-arrow-down' : 'fa-minus'),
            ];
        }
        return ['value' => 0, 'direction' => 'same', 'icon' => 'fa-minus'];
    }

    // Get month-over-month weight change
    public function getMonthlyChange($client_id)
    {
        $oneMonthAgo = date('Y-m-d', strtotime('-30 days'));
        $latest      = $this->getLatestWeight($client_id);
        $todayWeight = $latest ? $latest->weight : null;

        $builder = $this->db->table('weight');
        $builder->select('weight');
        $builder->where('client_id', $client_id);
        $builder->where("DATE(created_on) <= '$oneMonthAgo'");
        $builder->orderBy('created_on', 'DESC');
        $builder->limit(1);
        $row            = $builder->get()->getRow();
        $monthAgoWeight = $row ? $row->weight : null;

        if ($todayWeight && $monthAgoWeight) {
            $change = round($todayWeight - $monthAgoWeight, 1);
            return [
                'value'     => $change,
                'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'same'),
                'icon'      => $change > 0 ? 'fa-arrow-up' : ($change < 0 ? 'fa-arrow-down' : 'fa-minus'),
            ];
        }
        return ['value' => 0, 'direction' => 'same', 'icon' => 'fa-minus'];
    }

    // Get aggregated weight statistics for a client
    public function getWeightStats($client_id)
    {
        $stats = [
            'current'           => 0,
            'start'             => 0,
            'target'            => 0,
            'change'            => 0,
            'change_percentage' => 0,
            'progress'          => 0,
            'last_updated'      => null,
            'notes'             => '',
        ];

        $latest = $this->getLatestWeight($client_id);
        $first  = $this->getFirstWeight($client_id);
        $target = $this->getTargetWeight($client_id);

        if ($latest) {
            $stats['current']      = floatval($latest->weight);
            $stats['last_updated'] = $latest->created_on;
            $stats['notes']        = $latest->notes ?? '';
        }
        if ($first) {
            $stats['start'] = floatval($first->weight);
        }
        if ($target) {
            $stats['target'] = floatval($target);
        }

        if ($stats['current'] > 0 && $stats['start'] > 0) {
            $stats['change'] = round($stats['current'] - $stats['start'], 1);
            $stats['change_percentage'] = round(($stats['change'] / $stats['start']) * 100, 1);
        }

        if ($stats['target'] > 0 && $stats['start'] > 0 && $stats['current'] > 0) {
            $needed   = abs($stats['target'] - $stats['start']);
            $progress = abs($stats['current'] - $stats['start']);
            if ($needed > 0) {
                $stats['progress'] = min(100, round(($progress / $needed) * 100, 1));
            }
        }

        return $stats;
    }
}
