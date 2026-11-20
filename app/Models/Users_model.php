<?php

namespace App\Models;

use CodeIgniter\Model;

class Users_model extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'uid';
    protected $allowedFields = ['username', 'pass', 'first_name', 'last_name', 'email', 'phone_number', 'address', 'user_role', 'user_status', 'state_id'];
    
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }
    
    // Get all users with pagination
    public function getAllUsers($perPage = null, $offset = null)
    {
        $builder = $this->db->table('users');
        $builder->select('*');
        $builder->where('user_status !=', 'In-Active');
        $builder->orderBy('first_name', 'ASC');
        
        if ($perPage !== null && $offset !== null) {
            $builder->limit($perPage, $offset);
        }
        
        $query = $builder->get();
        return $query->getResult();
    }
    
    // Count all users
    public function countAllUsers()
    {
        $builder = $this->db->table('users');
        $builder->where('user_status !=', 'In-Active');
        return $builder->countAllResults();
    }
    
    // Get sales agents
    public function getSalesAgents($perPage, $offset)
    {
        $builder = $this->db->table('users');
        $builder->select('uid, CONCAT(first_name, " ", last_name) as full_name, phone_number, email, user_role, user_status');
        $builder->whereIn('user_role', ['tele_caller', 'SalesAgent', 'sales_manager', 'sales_executive']);
        $builder->orderBy('first_name', 'ASC');
        $builder->limit($perPage, $offset);
        $query = $builder->get();
        return $query->getResult();
    }
    
    // Count sales agents
    public function countSalesAgents()
    {
        $builder = $this->db->table('users');
        $builder->whereIn('user_role', ['tele_caller', 'SalesAgent', 'sales_manager', 'sales_executive']);
        return $builder->countAllResults();
    }
    
    // Count active sales agents
    public function countActiveSalesAgents()
    {
        $builder = $this->db->table('users');
        $builder->whereIn('user_role', ['tele_caller', 'SalesAgent', 'sales_manager', 'sales_executive']);
        $builder->where('user_status', 'Active');
        return $builder->countAllResults();
    }
    
    // Get user details by ID
    public function getUserDetails($uid)
    {
        $builder = $this->db->table('users AS U');
        $builder->select('U.*, GSC.state_name');
        $builder->where('U.uid', $uid);
        $builder->join('gst_state_codes AS GSC', 'GSC.state_id = U.state_id', 'left');
        $query = $builder->get();
        return $query->getRow(); // Returns single object, not array
    }
    
    // Get all states
    public function getAllState()
    {
        $builder = $this->db->table('gst_state_codes');
        $query = $builder->get();
        return $query->getResult();
    }
    
    // Add new user
    public function addUsers($data)
    {
        $builder = $this->db->table('users');
        $builder->insert($data);
        return $this->db->insertID();
    }
    
    // Update user
    public function updateUsers($data, $uid)
    {
        $builder = $this->db->table('users');
        $builder->where('uid', $uid);
        return $builder->update($data);
    }
    // Update user role
    public function PostUserRole($data, $userID)
    {
        // 1. Delete all existing roles for this user
        $builder = $this->db->table('userrole');
        $builder->where('userID', $userID);
        $builder->delete();

        // 2. Insert newly selected roles
        if (isset($data['menuID']) && is_array($data['menuID'])) {
            $insertData = [];
            foreach ($data['menuID'] as $menuID) {
                $insertData[] = [
                    'userID' => $userID,
                    'menuID' => $menuID
                ];
            }
            if (!empty($insertData)) {
                $builder->insertBatch($insertData);
            }
        }
        return true;
    }
}