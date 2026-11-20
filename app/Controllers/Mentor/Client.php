<?php

namespace App\Controllers\Mentor;

use Config\Services;
use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;

class Client extends Controller
{
    protected $session;
    protected $validation;
    protected $db;

    public function __construct()
    {
        $this->validation = Services::validation();
        $this->common_model = model('App\Models\Common_model', false);
        $this->appointment_model = model('App\Models\Appointment_model', false);
        $this->clients_model = model('App\Models\Clients_model', false);
        $this->session = session();
        $this->db = \Config\Database::connect();

        if (!is_doctor_logged_in()) {
            redirect('');
        }
    }

    public function index()
    {
        if ($this->session->has('doctor_id')) {
            
            // Handle filter parameters
            if ($this->request->getGet('submit') == 'Filter') {
                $search = $this->request->getGet('search');
                $status = $this->request->getGet('status');
            } else {
                $search = '';
                $status = 'all';
            }

            // Prepare data for view
            $data = [
                'search' => $search,
                'status' => $status,
                'menutitle' => 'Clients',
                'pagetitle' => 'Manage Clients',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url('mentor') . '">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Clients</li></ul>'
            ];

            // Build condition - get clients assigned to this doctor
            $doctor_id = session()->get('doctor_id');
            
            // Get unique client IDs from appointments for this doctor
            $appointment_clients = $this->getDoctorClients($doctor_id);
            
            if (!empty($appointment_clients)) {
                // Use WHERE IN clause properly
                $condition = "C.client_id IN (" . implode(',', $appointment_clients) . ")";

            } else {
                $condition = "C.client_id = 0"; // No clients assigned
            }

            // Add status filter
            if ($status != 'all') {
                // You can add status-based filtering here if needed
                // For example: active vs inactive clients
            }

            // Build like condition for search
            $like = [
                'client_name' => $search,
                'client_mobile_number' => $search,
                'client_email' => $search
            ];

            // Pagination setup
            $pager = service('pager');
            $page = (int)(($this->request->getVar('page') !== null) ? $this->request->getVar('page') : 1) - 1;
            $perPage = defined('PAGELIMIT') ? PAGELIMIT : 10;
            
            // Get total count with the custom WHERE condition
            $total = $this->getClientCount($condition, $like);

            $pager->makeLinks($page + 1, $perPage, $total);
            $offset = $page * $perPage;

            // Get paginated client list
            $clientList = $this->getClientsWithCondition($condition, $like, $perPage, $offset);
            
            // Enhance client data with appointment statistics
            foreach ($clientList as &$client) {
                $client->appointment_stats = $this->getClientAppointmentStats($client->client_id, $doctor_id);
                $client->last_appointment = $this->getLastAppointmentDate($client->client_id, $doctor_id);
            }

            $data['clientList'] = $clientList;
            $data['pager'] = $pager;

            echo view('mentor/appointment/client_list', $data);
        } else {
            return view('mentor/index');
        }
    }

    /**
     * Get clients with custom WHERE condition
     */
    private function getClientsWithCondition($whereCondition, $like, $perPage = null, $offset = null)
    {
        $builder = $this->db->table('client_profile AS C');
        $builder->select('C.*, CONCAT(U.first_name," ",U.last_name) as sales_agent, B.booking_id, B.bookStatus, B.plan_expiry_date');
        $builder->join('users AS U', 'C.uid = U.uid', 'left');
        $builder->join('(SELECT b1.booking_id, b1.plan_expiry_date, b1.client_id, b1.status AS bookStatus
            FROM booking b1
            JOIN (SELECT MAX(booking_id) AS latest_booking_id
                  FROM booking WHERE status = "Active"
                  GROUP BY client_id) AS b2
            ON b1.booking_id = b2.latest_booking_id) AS B', 'C.client_id = B.client_id', 'left');
        
        $builder->where($whereCondition, null, false); // false means don't escape the WHERE clause
        //$builder->where('C.status', 'Active');
        
        if (!empty($like) && !empty($like['client_name'])) {
            $builder->groupStart();
            $builder->orLike('C.client_name', $like['client_name']);
            $builder->orLike('C.client_email', $like['client_name']);
            $builder->orLike('C.client_mobile_number', $like['client_mobile_number']);
            $builder->groupEnd();
        }
        
        $builder->orderBy('DATE(C.created_on)', 'DESC');
        $builder->orderBy('C.client_id', 'DESC');

        if ($perPage !== null) {
            $builder->limit($perPage, $offset);
        }
        
        $query = $builder->get();
        return $query->getResult();
    }

    /**
     * Get client count with custom WHERE condition
     */
    private function getClientCount($whereCondition, $like)
    {

        $builder = $this->db->table('client_profile AS C');
        $builder->where($whereCondition, null, false);
        ///$builder->where('C.status', 'Active');
        
        if (!empty($like) && !empty($like['client_name'])) {
            $builder->groupStart();
            $builder->orLike('C.client_name', $like['client_name']);
            $builder->orLike('C.client_email', $like['client_name']);
            $builder->orLike('C.client_mobile_number', $like['client_mobile_number']);
            $builder->groupEnd();
        }
        // For debugging only - remove after fixing
    // echo "<h3>SQL Query Debug</h3>";
    // echo "Generated SQL: <code>" . $builder->getCompiledSelect() . "</code><br><br>";
        return $builder->countAllResults();
    }

    /**
     * Get all unique client IDs assigned to this doctor.
     * Checks both appointment.doctor_id (scheduled sessions) and
     * booking.doctor_id (assignment made before any session is created).
     */
    private function getDoctorClients($doctor_id)
    {
        $client_ids = [];

        // 1. Clients from appointment table (sessions already scheduled)
        $apptResults = $this->db->table('appointment')
            ->select('client_id')
            ->where('doctor_id', $doctor_id)
            ->where('status !=', 'Cancelled')
            ->get()->getResultArray();

        foreach ($apptResults as $row) {
            if (!empty($row['client_id'])) {
                $client_ids[(int)$row['client_id']] = (int)$row['client_id'];
            }
        }

        // 2. Clients from booking table (mentor assigned but no appointment created yet)
        $bookingResults = $this->db->table('booking')
            ->select('client_id')
            ->where('doctor_id', $doctor_id)
            ->where('status', 'Active')
            ->get()->getResultArray();

        foreach ($bookingResults as $row) {
            if (!empty($row['client_id'])) {
                $client_ids[(int)$row['client_id']] = (int)$row['client_id'];
            }
        }

        return array_values($client_ids);
    }

    /**
     * Get appointment statistics for a specific client and doctor
     */
    private function getClientAppointmentStats($client_id, $doctor_id)
    {
        $builder = $this->db->table('appointment');
        $builder->select('
            COUNT(*) as total_appointments,
            SUM(CASE WHEN status = "Completed" THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = "Upcoming" AND date >= CURDATE() THEN 1 ELSE 0 END) as upcoming,
            SUM(CASE WHEN status = "Upcoming" AND date < CURDATE() THEN 1 ELSE 0 END) as missed,
            SUM(CASE WHEN status = "Cancelled" THEN 1 ELSE 0 END) as cancelled
        ');
        $builder->where('client_id', $client_id);
        $builder->where('doctor_id', $doctor_id);
        
        $query = $builder->get();
        return $query->getRow();
    }

    /**
     * Get last appointment date for a client with this doctor
     */
    private function getLastAppointmentDate($client_id, $doctor_id)
    {
        $builder = $this->db->table('appointment');
        $builder->select('date, status');
        $builder->where('client_id', $client_id);
        $builder->where('doctor_id', $doctor_id);
        $builder->where('status !=', 'Cancelled');
        $builder->orderBy('date', 'DESC');
        $builder->limit(1);
        
        $query = $builder->get();
        $result = $query->getRow();
        
        return $result ? $result : null;
    }

    /**
     * View client details
     */
    public function view($client_id)
    {
        if ($this->session->has('doctor_id')) {
            // Verify this client belongs to this doctor
            $doctor_id = session()->get('doctor_id');
            $is_valid_client = $this->verifyClientAssignment($client_id, $doctor_id);
            
            if (!$is_valid_client) {
                $this->session->setFlashdata('error', 'You are not authorized to view this client.');
                return redirect()->to('mentor/clients');
            }

            $data = [
                'menutitle' => 'Clients',
                'pagetitle' => 'Client Details',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url('mentor') . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url('mentor/clients') . '">Manage Clients</a><i class="fa fa-angle-right"></i></li><li> Client Details</li></ul>'
            ];

            // Get client basic info
            $builder = $this->db->table('client_profile AS CP');
            $builder->select('CP.*, L.name AS lifestyle_name, G.name AS goal_name');
            $builder->join('lifestyle AS L', 'L.lifestyle_id = CP.lifestyle_id', 'left');
            $builder->join('goal AS G', 'G.goal_id = CP.goal_id', 'left');
            $builder->where('CP.client_id', $client_id);
            
            $query = $builder->get();
            $data['clientDetails'] = $query->getRow();

            // Get appointment history with this doctor
            $appointment_builder = $this->db->table('appointment AS A');
            $appointment_builder->select('A.*, P.name AS plan_name');
            $appointment_builder->join('booking AS B', 'B.booking_id = A.booking_id', 'left');
            $appointment_builder->join('plan AS P', 'P.plan_id = B.plan_id', 'left');
            $appointment_builder->where('A.client_id', $client_id);
            $appointment_builder->where('A.doctor_id', $doctor_id);
            $appointment_builder->orderBy('A.date', 'DESC');
            
            $data['appointmentHistory'] = $appointment_builder->get()->getResult();

            // Get client comments
            $data['clientComments'] = $this->clients_model->getClientComment($client_id);

            // Current health targets
            $profileRow = $this->db->table('client_profile')
                ->select('target_water, target_sleep, target_weight')
                ->where('client_id', $client_id)
                ->get()->getRow();

            $data['target_water']   = $profileRow ? (int)$profileRow->target_water               : 8;
            $data['target_sleep']   = $profileRow ? (int)$profileRow->target_sleep               : 480;
            $data['target_weight']  = $profileRow ? (float)($profileRow->target_weight ?? 0)     : 0;
            $data['target_steps']   = model('App\Models\StepsTargetModel')->getCurrentTarget($client_id);
            $data['target_calorie'] = model('App\Models\CaloriesTargetModel')->getCurrentTarget($client_id, null, 'intake');

            echo view('mentor/appointment/client_view', $data);
        } else {
            return view('mentor/index');
        }
    }

    /**
     * Verify if a client is assigned to this doctor
     */
    public function set_targets($client_id)
    {
        if (!$this->session->has('doctor_id')) {
            return redirect()->to('mentor');
        }

        $this->db->table('client_profile')->where('client_id', $client_id)->update([
            'target_water'  => (int)$this->request->getPost('target_water'),
            'target_sleep'  => (int)$this->request->getPost('target_sleep'),
            'target_weight' => (float)$this->request->getPost('target_weight'),
        ]);

        model('App\Models\StepsTargetModel')->setTarget($client_id, (int)$this->request->getPost('target_steps'));
        model('App\Models\CaloriesTargetModel')->setTarget($client_id, (int)$this->request->getPost('target_calorie'), 'intake');

        $this->session->setFlashdata('suc_msg', 'Targets updated successfully!');
        return redirect()->to('mentor/clients/view/' . $client_id);
    }

    private function verifyClientAssignment($client_id, $doctor_id)
    {
        $builder = $this->db->table('appointment');
        $builder->select('COUNT(*) as count');
        $builder->where('client_id', $client_id);
        $builder->where('doctor_id', $doctor_id);
        $builder->where('status !=', 'Cancelled');
        
        $query = $builder->get();
        $result = $query->getRow();
        
        return ($result && $result->count > 0);
    }
}