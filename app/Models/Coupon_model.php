<?php
namespace App\Models;

use CodeIgniter\Model;

class Coupon_model extends Model
{
    protected $table = 'coupon';
    protected $primaryKey = 'coupon_id';
    
    /**
     * Get all coupons
     */
    public function getAllCoupons($condition = [])
    {
        $builder = $this->db->table('coupon');
        $builder->select('*');
        if (!empty($condition)) {
            $builder->where($condition);
        }
        $builder->orderBy('coupon_id', 'DESC');
        $query = $builder->get();
        
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    
    /**
     * Get single coupon details
     */
    public function getCouponDetails($coupon_id)
    {
        $builder = $this->db->table('coupon');
        $builder->select('*');
        $builder->where('coupon_id', $coupon_id);
        $query = $builder->get();
        
        if ($query->getResult()) {
            return $query->getResult()[0];
        } else {
            return null;
        }
    }
    
    /**
     * Add new coupon
     */
    public function addCoupon($insert_data)
    {
        $builder = $this->db->table('coupon');
        $builder->insert($insert_data);
        return $this->db->insertID();
    }
    
    /**
     * Update coupon
     */
    public function updateCoupon($update_data, $condition)
    {
        $builder = $this->db->table('coupon');
        $builder->where($condition);
        $res = $builder->update($update_data);
        return $res ? 1 : 0;
    }
    
    /**
     * Get coupon usage statistics
     */
    public function getCouponUsageStats($coupon_id)
    {
        $builder = $this->db->table('coupon_usage');
        $builder->select('COUNT(*) as total_used, COUNT(DISTINCT client_id) as unique_clients');
        $builder->where('coupon_id', $coupon_id);
        $query = $builder->get();
        
        if ($query->getResult()) {
            return $query->getRow();
        } else {
            return null;
        }
    }
    
    /**
     * Get clients who used a coupon
     */
    public function getCouponUsers($coupon_id)
    {
        $builder = $this->db->table('coupon_usage AS CU');
        $builder->select('CU.*, CP.client_name, CP.client_email, CP.client_mobile_number');
        $builder->join('client_profile AS CP', 'CP.client_id = CU.client_id', 'left');
        $builder->where('CU.coupon_id', $coupon_id);
        $builder->orderBy('CU.used_at', 'DESC');
        $query = $builder->get();
        
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }
    
    /**
     * Validate coupon for a client
     */
    public function validateCoupon($coupon_code, $client_id)
    {
        $current_date = date('Y-m-d');

        $builder = $this->db->table('coupon');
        $builder->select('*');
        $builder->where('coupon_code', $coupon_code);
        $builder->where('status', 'Active');
        $builder->where('valid_from <=', $current_date);
        $builder->where('(valid_to IS NULL OR valid_to >=)', $current_date);
        $query = $builder->get();

        if (!$query->getResult()) {
            return ['valid' => false, 'message' => 'Invalid or expired coupon'];
        }

        $coupon = $query->getRow();

        // User-specific: only assigned clients can use this coupon
        if ($coupon->coupon_type === 'user_specific' && !empty($coupon->assigned_client_ids)) {
            $allowed = json_decode($coupon->assigned_client_ids, true) ?: [];
            if (!in_array((int)$client_id, array_map('intval', $allowed))) {
                return ['valid' => false, 'message' => 'This coupon is not available for your account'];
            }
        }

        // Check usage limit
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return ['valid' => false, 'message' => 'Coupon usage limit reached'];
        }

        // Check if client has used this coupon before
        if ($coupon->max_usage_per_client > 0) {
            $client_usage = $this->getClientCouponUsage($coupon->coupon_id, $client_id);
            if ($client_usage >= $coupon->max_usage_per_client) {
                return ['valid' => false, 'message' => 'You have already used this coupon'];
            }
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'message' => 'Coupon is valid'
        ];
    }

    /**
     * Get client details for assigned_client_ids (used in views/edit pre-population)
     */
    public function getAssignedClients($assigned_client_ids_json)
    {
        if (empty($assigned_client_ids_json)) return [];
        $ids = json_decode($assigned_client_ids_json, true);
        if (empty($ids)) return [];
        return $this->db->table('client_profile')
            ->select('client_id, client_name, client_email, client_mobile_number')
            ->whereIn('client_id', array_map('intval', $ids))
            ->get()->getResult();
    }
    
    /**
     * Get how many times a client has used a coupon
     */
    private function getClientCouponUsage($coupon_id, $client_id)
    {
        $builder = $this->db->table('coupon_usage');
        $builder->select('COUNT(*) as usage_count');
        $builder->where('coupon_id', $coupon_id);
        $builder->where('client_id', $client_id);
        $query = $builder->get();
        
        $result = $query->getRow();
        return $result ? $result->usage_count : 0;
    }
    
    /**
     * Apply coupon (track usage)
     */
    public function applyCoupon($coupon_id, $client_id, $booking_id = null)
    {
        // Start transaction
        $this->db->transStart();
        
        try {
            // 1. Update coupon usage count
            $builder = $this->db->table('coupon');
            $builder->set('used_count', 'used_count + 1', false);
            $builder->where('coupon_id', $coupon_id);
            $builder->update();
            
            // 2. Record usage
            $usage_data = [
                'coupon_id' => $coupon_id,
                'client_id' => $client_id,
                'booking_id' => $booking_id,
                'used_at' => date('Y-m-d H:i:s')
            ];
            
            $builder = $this->db->table('coupon_usage');
            $builder->insert($usage_data);
            
            $this->db->transComplete();
            
            return $this->db->transStatus();
            
        } catch (\Exception $e) {
            $this->db->transRollback();
            return false;
        }
    }
}