<?php
namespace App\Models;

use CodeIgniter\Model;

class CouponUsageModel extends Model
{
    protected $table = 'coupon_usage';
    protected $primaryKey = 'usage_id';
    protected $allowedFields = [
        'coupon_id', 'user_id', 'booking_id', 'plan_id',
        'plan_amount', 'discount_applied', 'used_at'
    ];
    
    public function getCouponStats(int $couponId): array
    {
        $builder = $this->builder();
        $builder->select('COUNT(*) as total_usage, SUM(discount_applied) as total_discount')
               ->where('coupon_id', $couponId);
        
        return $builder->get()->getRowArray();
    }
    
    public function getUserCouponHistory(int $userId): array
    {
        return $this->where('user_id', $userId)
                   ->orderBy('used_at', 'DESC')
                   ->findAll();
    }
}