<?php

namespace App\Models;

use CodeIgniter\Model;

class Clients_model extends Model
{
    protected $table = 'client_profile';
    protected $primaryKey = 'client_id';
    protected $allowedFields = ['client_name', 'client_email', 'client_mobile_number', 'status', 'created_on'];
    protected $useTimestamps = false;
    
     public function getAllClients()
    {
        $builder = $this->db->table('client_profile');
        $builder->select('*');
        $builder->where('status', 'Active');
        $query = $builder->get();

        if ($query->getResult()) {
            return $query->getResult();
        } else {
            return [];
        }
    }

    public function getClients($tablename, $condition, $like, $perPage = null, $offset = null, $paymentStatus = '', $statusFilter = '')
    {
        $builder = $this->db->table($tablename . ' AS C');
        $builder->select('C.*, CONCAT(U.first_name," ",U.last_name) as sales_agent, 
                         B.booking_id, B.bookStatus, B.plan_expiry_date');
        $builder->join('users AS U', 'C.uid = U.uid', 'left');
        $builder->join('(SELECT b1.booking_id, b1.plan_expiry_date, b1.client_id, b1.status AS bookStatus
            FROM booking b1
            WHERE b1.status = "Active"
            AND b1.plan_expiry_date >= CURDATE()
            GROUP BY b1.client_id) AS B', 'C.client_id = B.client_id', 'left');
        
        // Apply conditions from controller
        if (!empty($condition)) {
            $builder->where($condition);
        }
        
        // FIX: Only apply status filter when specifically requested
        // Don't add default status filter for "All Clients"
        
        // Apply payment status filtering
        if ($paymentStatus === 'paid') {
            // Paid clients: Must have active booking
            $builder->where('C.status', 'Active');
            $builder->where('B.bookStatus', 'Active');
            $builder->where('B.plan_expiry_date >=', date('Y-m-d'));
        } elseif ($paymentStatus === 'unpaid') {
            // Unpaid clients: Active clients without active booking
            $builder->where('C.status', 'Active');
            $builder->where("(B.bookStatus IS NULL OR B.bookStatus != 'Active' OR B.plan_expiry_date < '" . date('Y-m-d') . "')");
        }
        // REMOVED: No default filter - show ALL clients when no filter is specified
        
        // Apply search
        if (!empty($like)) {
            $builder->groupStart();
            $builder->orLike('C.client_name', $like['client_name'] ?? '');
            $builder->orLike('C.client_email', $like['client_name'] ?? '');
            $builder->orLike('C.client_mobile_number', $like['client_mobile_number'] ?? '');
            $builder->groupEnd();
        }
        
        $builder->orderBy('C.created_on', 'DESC');
        
        if ($perPage !== null && $offset !== null) {
            $builder->limit($perPage, $offset);
        }
        
        $query = $builder->get();
        return $query->getResult() ?: [];
    }

/**
 * Get total clients count (without pagination)
 */
/**
 * Get total clients count (without pagination - optimized for counting only)
 */
  public function getTotalClientsCount($condition, $like, $paymentStatus = '', $statusFilter = '')
    {
        $builder = $this->db->table('client_profile AS C');
        
        // We only need count, not all fields
        $builder->select('COUNT(C.client_id) as total_count');
        
        // Apply joins only if needed for payment status filtering
        if ($paymentStatus === 'paid' || $paymentStatus === 'unpaid') {
            $builder->join('(SELECT b1.client_id, b1.status AS bookStatus, b1.plan_expiry_date
                FROM booking b1
                WHERE b1.status = "Active"
                AND b1.plan_expiry_date >= CURDATE()
                GROUP BY b1.client_id) AS B', 'C.client_id = B.client_id', 'left');
        }
        
        // Apply conditions
        if (!empty($condition)) {
            foreach ($condition as $key => $value) {
                $builder->where($key, $value);
            }
        }
        
        // FIX: Don't apply default status filter for "All Clients"
        // Only filter by status if explicitly in conditions
        
        // Apply payment status filtering
        if ($paymentStatus === 'paid') {
            $builder->where('C.status', 'Active');
            $builder->where('B.bookStatus', 'Active');
            $builder->where('B.plan_expiry_date >=', date('Y-m-d'));
        } elseif ($paymentStatus === 'unpaid') {
            $builder->where('C.status', 'Active');
            $builder->where("(B.bookStatus IS NULL OR B.bookStatus != 'Active' OR B.plan_expiry_date < '" . date('Y-m-d') . "')");
        }
        // No default status filter - count ALL clients
        
        // Apply search
        if (!empty($like)) {
            $builder->groupStart();
            if (isset($like['client_name'])) {
                $builder->orLike('C.client_name', $like['client_name']);
                $builder->orLike('C.client_email', $like['client_name']);
            }
            if (isset($like['client_mobile_number'])) {
                $builder->orLike('C.client_mobile_number', $like['client_mobile_number']);
            }
            $builder->groupEnd();
        }
        
        $query = $builder->get();
        $result = $query->getRow();
        
        return $result ? (int)$result->total_count : 0;
    }

/**
 * Check payment status for a client
 */
public function checkPaymentStatus($client_id)
{
    $builder = $this->db->table('booking');
    $builder->select('COUNT(*) as active_bookings')
            ->where('client_id', $client_id)
            ->where('status', 'Active')
            ->where('plan_expiry_date >=', date('Y-m-d'));
    
    $query = $builder->get();
    $result = $query->getRow();
    
    return ($result && $result->active_bookings > 0) ? 'paid' : 'unpaid';
}

/**
 * Get counts for dashboard tabs
 */
public function getClientCounts()
{
    $counts = [
        'all' => 0,
        'active' => 0,
        'inactive' => 0,
        'paid' => 0,
        'unpaid' => 0
    ];

    try {
        // Total active clients
        $builder = $this->db->table('client_profile');
        $builder->select('COUNT(*) as total')->where('status', 'Active');
        $query = $builder->get();
        $counts['active'] = $query->getRow()->total ?? 0;

        // Total inactive clients
        $builder = $this->db->table('client_profile');
        $builder->select('COUNT(*) as total')->where('status', 'Inactive');
        $query = $builder->get();
        $counts['inactive'] = $query->getRow()->total ?? 0;

        // Total all clients
        $counts['all'] = $counts['active'] + $counts['inactive'];

        // Paid clients (active clients with active bookings)
        $builder = $this->db->table('client_profile cp');
        $builder->select('COUNT(DISTINCT cp.client_id) as total')
                ->join('booking b', 'cp.client_id = b.client_id', 'inner')
                ->where('cp.status', 'Active')
                ->where('b.status', 'Active')
                ->where('b.plan_expiry_date >=', date('Y-m-d'));
        $query = $builder->get();
        $counts['paid'] = $query->getRow()->total ?? 0;

        // Unpaid clients (active - paid)
        $counts['unpaid'] = $counts['active'] - $counts['paid'];

    } catch (\Exception $e) {
        log_message('error', 'Error in getClientCounts: ' . $e->getMessage());
    }

    return $counts;
}

    public function getClientComment($client_id)
    {
        try {
            if (!$this->db->tableExists('clientcomment')) {
                log_message('info', 'clientcomment table does not exist');
                return [];
            }
            
            $builder = $this->db->table('clientcomment AS A');
            $builder->select('A.*, D.username as doctoruser, D.doctor_name');
            $builder->join('doctor AS D', 'D.doctor_id = A.doctor_id', 'left');
            $builder->where('A.client_id', $client_id);
            $query = $builder->get();
            
            return $query->getResult() ?: [];
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getClientComment: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get Active Plan Details for a client
     * Joins: client_profile → booking → plan
     */
    public function getActivePlanDetails($client_id)
    {
        try {
            // First, get the latest active booking for this client
            $builder = $this->db->table('booking b');
            $builder->select('b.*');
            $builder->where('b.client_id', $client_id);
            $builder->where('b.status', 'Active');
            $builder->where('b.plan_expiry_date >=', date('Y-m-d'));
            $builder->orderBy('b.booking_id', 'DESC');
            $builder->limit(1);
            
            $bookingQuery = $builder->get();
            $booking = $bookingQuery->getRow();
            
            if (empty($booking)) {
                return [];
            }
            
            // Now get the plan details for this booking
            $planBuilder = $this->db->table('plan p');
            $planBuilder->select('p.*');
            $planBuilder->where('p.plan_id', $booking->plan_id);
            $planBuilder->where('p.status', 'Active');
            
            $planQuery = $planBuilder->get();
            $plan = $planQuery->getRow();
            
            if (empty($plan)) {
                return [];
            }
            
            // Get client details
            $clientBuilder = $this->db->table('client_profile cp');
            $clientBuilder->select('cp.*');
            $clientBuilder->where('cp.client_id', $client_id);
            
            $clientQuery = $clientBuilder->get();
            $client = $clientQuery->getRow();
            
            // Combine all data into a single object
            $result = new \stdClass();
            
            // Client info
            $result->client_id = $client->client_id;
            $result->client_name = $client->client_name;
            $result->client_email = $client->client_email;
            $result->age = $client->age;
            $result->client_gender = $client->client_gender;
            $result->height = $client->height;
            $result->weight = $client->weight;
            $result->target_weight = $client->target_weight;
            
            // Booking info
            $result->booking_id = $booking->booking_id;
            $result->booking_date = $booking->date;
            $result->plan_expiry_date = $booking->plan_expiry_date;
            $result->plan_duration = $booking->plan_duration;
            $result->total_amount = $booking->total_amount;
            $result->discount = $booking->discount;
            $result->subtotal = $booking->subtotal;
            $result->payment = $booking->payment;
            $result->booking_status = $booking->status;
            
            // Plan info
            $result->plan_id = $plan->plan_id;
            $result->plan_name = $plan->name;
            $result->plan_caption = $plan->caption;
            $result->plan_description = $plan->description;
            $result->plan_original_price = $plan->price;
            $result->plan_validity = $plan->validaty;
            $result->plan_category = $plan->category;
            $result->plan_status = $plan->status;
            
            // Calculate days remaining
            if (!empty($result->plan_expiry_date)) {
                $expiryDate = new \DateTime($result->plan_expiry_date);
                $currentDate = new \DateTime();
                $interval = $currentDate->diff($expiryDate);
                $result->days_remaining = $interval->days;
                
                // Adjust for past dates
                if ($interval->invert) {
                    $result->days_remaining = -$result->days_remaining;
                }
                
                // Determine status indicator
                if ($result->days_remaining < 0) {
                    $result->plan_status_indicator = 'EXPIRED';
                } elseif ($result->days_remaining == 0) {
                    $result->plan_status_indicator = 'EXPIRES TODAY';
                } elseif ($result->days_remaining <= 7) {
                    $result->plan_status_indicator = 'EXPIRING SOON';
                } else {
                    $result->plan_status_indicator = 'ACTIVE';
                }
            } else {
                $result->days_remaining = null;
                $result->plan_status_indicator = 'NO EXPIRY DATE';
            }
            
            return [$result];
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getActivePlanDetails: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get All Plans History for a client
     * Joins: client_profile → booking → plan
     */
    public function getAllPlansHistory($client_id)
    {
        try {
            $builder = $this->db->table('booking b');
            $builder->select('b.*, p.*');
            $builder->join('plan p', 'b.plan_id = p.plan_id', 'left');
            $builder->where('b.client_id', $client_id);
            $builder->whereIn('b.status', ['Active', 'Complete', 'Processing']);
            $builder->orderBy('b.date', 'DESC');
            
            $query = $builder->get();
            $results = $query->getResult();
            
            $formattedResults = [];
            $today = new \DateTime();
            
            foreach ($results as $row) {
                $formattedRow = new \stdClass();
                
                // Plan info from plan table
                $formattedRow->plan_id = $row->plan_id;
                $formattedRow->plan_name = $row->name;
                $formattedRow->category = $row->category;
                $formattedRow->original_price = $row->price;
                
                // Booking info
                $formattedRow->booking_id = $row->booking_id;
                $formattedRow->amount_paid = $row->total_amount;
                $formattedRow->booking_status = $row->status;
                $formattedRow->payment = $row->payment;
                
                // Format dates
                $formattedRow->purchased_date = !empty($row->date) ? date('d M Y', strtotime($row->date)) : 'N/A';
                $formattedRow->expiry_date = !empty($row->plan_expiry_date) ? date('d M Y', strtotime($row->plan_expiry_date)) : 'N/A';
                
                // Determine current status and timeline
                if (!empty($row->plan_expiry_date)) {
                    $expiryDate = new \DateTime($row->plan_expiry_date);
                    
                    if ($today > $expiryDate) {
                        $formattedRow->current_status = 'Expired';
                        $interval = $today->diff($expiryDate);
                        $formattedRow->timeline = 'Expired ' . $interval->days . ' days ago';
                    } elseif ($row->status == 'Active' && $today <= $expiryDate) {
                        $formattedRow->current_status = 'Currently Active';
                        $interval = $today->diff($expiryDate);
                        $formattedRow->timeline = $interval->days . ' days remaining';
                    } else {
                        $formattedRow->current_status = $row->status;
                        $interval = $today->diff($expiryDate);
                        $formattedRow->timeline = $interval->days . ' days remaining';
                    }
                } else {
                    $formattedRow->current_status = $row->status;
                    $formattedRow->timeline = 'No expiry date';
                }
                
                $formattedResults[] = $formattedRow;
            }
            
            return $formattedResults;
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getAllPlansHistory: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Alternative method using single query with joins
     * Get Active Plan Details (simpler version with joins)
     */
    public function getActivePlanDetailsWithJoins($client_id)
    {
        try {
            $builder = $this->db->table('client_profile cp');
            $builder->select('cp.client_id, cp.client_name, cp.client_email, cp.age, cp.client_gender, 
                            cp.height, cp.weight, cp.target_weight,
                            b.booking_id, b.date as booking_date, b.plan_expiry_date, 
                            b.plan_duration, b.total_amount, b.discount, b.subtotal, 
                            b.payment, b.status as booking_status,
                            p.plan_id, p.name as plan_name, p.caption as plan_caption, 
                            p.description as plan_description, p.price as plan_original_price,
                            p.validaty as plan_validity, p.category as plan_category, p.status as plan_status');
            
            $builder->join('booking b', 'cp.client_id = b.client_id', 'left');
            $builder->join('plan p', 'b.plan_id = p.plan_id', 'left');
            $builder->where('cp.client_id', $client_id);
            $builder->where('b.status', 'Active');
            $builder->where('b.plan_expiry_date >=', date('Y-m-d'));
            $builder->where('p.status', 'Active');
            $builder->orderBy('b.booking_id', 'DESC');
            $builder->limit(1);
            
            $query = $builder->get();
            $result = $query->getResult();
            
            // Add calculated fields
            if (!empty($result)) {
                $row = $result[0];
                
                if (!empty($row->plan_expiry_date)) {
                    $expiryDate = new \DateTime($row->plan_expiry_date);
                    $currentDate = new \DateTime();
                    $interval = $currentDate->diff($expiryDate);
                    $row->days_remaining = $interval->days;
                    
                    if ($interval->invert) {
                        $row->days_remaining = -$row->days_remaining;
                    }
                    
                    if ($row->days_remaining < 0) {
                        $row->plan_status_indicator = 'EXPIRED';
                    } elseif ($row->days_remaining == 0) {
                        $row->plan_status_indicator = 'EXPIRES TODAY';
                    } elseif ($row->days_remaining <= 7) {
                        $row->plan_status_indicator = 'EXPIRING SOON';
                    } else {
                        $row->plan_status_indicator = 'ACTIVE';
                    }
                } else {
                    $row->days_remaining = null;
                    $row->plan_status_indicator = 'NO EXPIRY DATE';
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getActivePlanDetailsWithJoins: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if client has an active plan
     */
    public function checkActivePlanStatus($client_id)
    {
        try {
            $builder = $this->db->table('booking b');
            $builder->select('b.booking_id, b.plan_expiry_date, b.status as booking_status, 
                            p.name as active_plan_name, p.plan_id');
            $builder->join('plan p', 'b.plan_id = p.plan_id', 'left');
            $builder->where('b.client_id', $client_id);
            $builder->where('b.status', 'Active');
            $builder->where('b.plan_expiry_date >=', date('Y-m-d'));
            $builder->where('p.status', 'Active');
            $builder->orderBy('b.booking_id', 'DESC');
            $builder->limit(1);
            
            $query = $builder->get();
            $result = $query->getRow();
            
            if (!empty($result)) {
                $today = new \DateTime();
                $expiryDate = new \DateTime($result->plan_expiry_date);
                $interval = $today->diff($expiryDate);
                
                $result->has_active_plan = 'YES';
                $result->validity = $interval->days . ' days remaining';
            } else {
                $result = new \stdClass();
                $result->has_active_plan = 'NO';
                $result->validity = 'NO ACTIVE PLAN';
                $result->booking_id = null;
                $result->active_plan_name = null;
            }
            
            // Add client info
            $clientBuilder = $this->db->table('client_profile');
            $clientBuilder->select('client_id, client_name, client_mobile_number');
            $clientBuilder->where('client_id', $client_id);
            $clientQuery = $clientBuilder->get();
            $client = $clientQuery->getRow();
            
            if ($client) {
                $result->client_id = $client->client_id;
                $result->client_name = $client->client_name;
                $result->client_mobile_number = $client->client_mobile_number;
            }
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'Error in checkActivePlanStatus: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get Client Dashboard Summary
     */
    public function getClientDashboardSummary($client_id)
    {
        try {
            // Get client basic info
            $clientBuilder = $this->db->table('client_profile cp');
            $clientBuilder->select('cp.*');
            $clientBuilder->where('cp.client_id', $client_id);
            $clientQuery = $clientBuilder->get();
            $client = $clientQuery->getRow();
            
            if (empty($client)) {
                return null;
            }
            
            // Get active plan info
            $planBuilder = $this->db->table('booking b');
            $planBuilder->select('b.plan_expiry_date, p.name as active_plan, p.category as plan_category');
            $planBuilder->join('plan p', 'b.plan_id = p.plan_id', 'left');
            $planBuilder->where('b.client_id', $client_id);
            $planBuilder->where('b.status', 'Active');
            $planBuilder->where('b.plan_expiry_date >=', date('Y-m-d'));
            $planBuilder->where('p.status', 'Active');
            $planBuilder->orderBy('b.booking_id', 'DESC');
            $planBuilder->limit(1);
            
            $planQuery = $planBuilder->get();
            $plan = $planQuery->getRow();
            
            // Create result object
            $result = new \stdClass();
            
            // Client info
            $result->client_id = $client->client_id;
            $result->client_name = $client->client_name;
            $result->client_profile_pic = $client->client_profile_pic;
            $result->weight = $client->weight;
            $result->target_weight = $client->target_weight;
            
            // Plan info
            if (!empty($plan)) {
                $result->active_plan = $plan->active_plan;
                $result->plan_category = $plan->plan_category;
                $result->plan_ends_on = $plan->plan_expiry_date;
                
                // Calculate plan status
                $expiryDate = new \DateTime($plan->plan_expiry_date);
                $today = new \DateTime();
                $interval = $today->diff($expiryDate);
                
                if ($today > $expiryDate) {
                    $result->plan_status = 'Plan Expired';
                } elseif ($interval->days <= 7) {
                    $result->plan_status = 'Renew in ' . $interval->days . ' days';
                } else {
                    $result->plan_status = 'Plan Active';
                }
            } else {
                $result->active_plan = 'No Active Plan';
                $result->plan_category = 'N/A';
                $result->plan_ends_on = 'N/A';
                $result->plan_status = 'No Active Plan';
            }
            
            // Weight metrics
            $result->weight_to_lose = 0;
            if (!empty($client->weight) && !empty($client->target_weight)) {
                $result->weight_to_lose = round($client->target_weight - $client->weight, 2);
            }
            
            // Get booking statistics
            $stats = $this->getBookingStatistics($client_id);
            $result->total_bookings = $stats['total_bookings'];
            $result->total_spent = $stats['total_spent'];
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getClientDashboardSummary: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Helper method to get booking statistics
     */
    private function getBookingStatistics($client_id)
    {
        $stats = ['total_bookings' => 0, 'total_spent' => 0];
        
        try {
            $builder = $this->db->table('booking');
            $builder->select('COUNT(*) as total_bookings, 
                            SUM(CASE WHEN payment = "Yes" THEN total_amount ELSE 0 END) as total_spent');
            $builder->where('client_id', $client_id);
            
            $query = $builder->get();
            $result = $query->getRow();
            
            if ($result) {
                $stats['total_bookings'] = $result->total_bookings ?? 0;
                $stats['total_spent'] = $result->total_spent ?? 0;
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error in getBookingStatistics: ' . $e->getMessage());
        }
        
        return $stats;
    }
}