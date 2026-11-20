<?php

namespace App\Models;

use CodeIgniter\Model;

class Plans_model extends Model
{


    public function addBookingWithCoupon(array $insert_data, ?string $couponCode = null): array
    {
        $discount = 0;
        $couponId = null;

        if ($couponCode) {
            $couponModel = model(CouponModel::class);
            $userId = $insert_data['client_id'];
            $planId = $insert_data['plan_id'];
            $planAmount = $insert_data['subtotal']; // Assuming subtotal is plan amount

            $coupon = $couponModel->validateForPlan($couponCode, $userId, $planId, $planAmount);

            if ($coupon) {
                $discount = $coupon->calculated_discount;
                $couponId = $coupon->coupon_id;

                // Apply discount to booking
                $insert_data['coupon_id'] = $couponId;
                $insert_data['discount'] = $discount;
                $insert_data['total_amount'] = $insert_data['subtotal'] - $discount;

                // Track coupon usage (after successful booking)
                // We'll track after booking is created
            }
        }

        // Create booking
        $bookingId = $this->addBooking($insert_data);

        // Track coupon usage if applied
        if ($bookingId && $couponId) {
            $couponModel->applyToBooking(
                $couponId,
                $insert_data['client_id'],
                $bookingId,
                $insert_data['plan_id'],
                $insert_data['subtotal'],
                $discount
            );
        }

        return [
            'booking_id' => $bookingId,
            'discount' => $discount,
            'coupon_id' => $couponId
        ];
    }

    /**
     * Validate coupon during booking
     */
    public function validateCouponForBooking(string $code, int $clientId, int $planId, float $amount): array
    {
        $couponModel = model(CouponModel::class);
        $coupon = $couponModel->validateForPlan($code, $clientId, $planId, $amount);

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Invalid or expired coupon code'
            ];
        }

        return [
            'valid' => true,
            'coupon' => [
                'id' => $coupon->coupon_id,
                'code' => $coupon->coupon_code,
                'type' => $coupon->coupon_type,
                'discount_amount' => $coupon->calculated_discount,
                'description' => $coupon->description
            ],
            'discount' => $coupon->calculated_discount,
            'final_amount' => $amount - $coupon->calculated_discount
        ];
    }





    function getAllPlans($category, $plan_id = '')
    {
        $builder = $this->db->table('plan');
        $builder->select('*');
        if (!empty($plan_id)) {
            $builder->where('plan_id', $plan_id);
        }
        if (!empty($category)) {
            $builder->where('category', $category);
        }
        $builder->where('status', 'Active');
        $builder->orderBy('plan_id', 'DESC');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    function getIsPlanActive($client_id)
    {
        $builder = $this->db->table('booking AS B');
        $builder->select('B.*');
        $builder->where('B.status', 'Active');
        $builder->where('B.client_id', $client_id);
        $builder->groupBy('plan_id');
        $builder->orderBy('plan_id', 'DESC');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    function getAllplan($category, $client_id)
    {
        $builder = $this->db->table('plan AS P');
        $builder->select('P.*, B.booking_id');
        $builder->join('booking AS B', 'B.plan_id = P.plan_id AND B.client_id=' . $client_id . ' AND B.status = "Active"', 'left');
        $builder->where('P.category', $category);
        $builder->where('P.status', 'Active');
        $builder->groupBy('plan_id');
        $builder->orderBy('plan_id', 'ASC');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getPlanFeatures($plan_id)
    {
        $builder = $this->db->table('plan_features');
        $builder->select('*');
        $builder->where('plan_id', $plan_id);
        $builder->where('status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function addBooking($insert_data)
    {
        $builder = $this->db->table('booking');
        $builder->insert($insert_data);
        return $this->db->insertID();
    }

    public function updateBooking($update_data, $booking_id)
    {
        if ($booking_id > 0 && !empty($update_data)) {
            $builder = $this->db->table('booking');
            $builder->where('booking_id', $booking_id);
            $builder->update($update_data);
            return 1;
        } else {
            return 0;
        }
    }

    public function getAllList($name)
    {
        $builder = $this->db->table($name);
        $builder->select('*');
        $builder->where('status', 'Active');
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getBookingList($client_id)
    {
        $builder = $this->db->table('booking AS B');
        $builder->select('P.*,B.booking_id, B.status AS subcr_status, B.date, B.plan_expiry_date');

        $builder->join('plan AS P', 'P.plan_id = B.plan_id');
        if ($client_id > 0) {
            $builder->where('B.client_id', $client_id);
        }
        $builder->where('B.status', 'Active');
        $builder->orderBy('B.booking_id', 'DESC');
        $builder->limit(1);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }



    //Dev 4
    public function getBookingDetails($booking_id)
    {
        $builder = $this->db->table('booking');
        $builder->select('booking_id,doctor_id,client_id,coupon_id,total_amount,discount,subtotal,tax_rate,taxable_amount,convenience_fee');
        $builder->where('booking_id', $booking_id);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getBookingDetailsBeforePayment($booking_id)
    {
        $builder = $this->db->table('booking');
        $builder->select('booking_id,doctor_id,client_id,coupon_id,discount,subtotal,payment,plan_duration');
        $builder->where('booking_id', $booking_id);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getBookingDetailsAfterPayment($booking_id)
    {
        $builder = $this->db->table('booking');
        $builder->select('booking_id,doctor_id,client_id,coupon_id,total_amount,discount,subtotal,tax_rate,taxable_amount,convenience_fee,payment');
        $builder->where('booking_id', $booking_id);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function checkBookingPayment($booking_id)
    {
        $builder = $this->db->table('booking');
        $builder->select('payment');
        $builder->where('booking_id', $booking_id);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getPlanID($booking_id)
    {
        $builder = $this->db->table('booking');
        $builder->select('plan_id');
        $builder->where('booking_id', $booking_id);
        $query = $builder->get();
        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function update_enc_val($booking_id, $update_data)
    {
        if ($booking_id > 0 && !empty($update_data)) {
            $builder = $this->db->table('booking');
            $builder->where('booking_id', $booking_id);
            $builder->update($update_data);
            return 1;
        } else {
            return 0;
        }
    }

    public function hasUsedFreeTrial($client_id)
    {
        return $this->db->table('booking')
            ->where('client_id', $client_id)
            ->where('payment', 'Free')
            ->countAllResults() > 0;
    }

}
