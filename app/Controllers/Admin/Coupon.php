<?php
namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;

class Coupon extends Controller
{
    protected $session;
    protected $validation;
    protected $common_model;
    protected $coupon_model;
    protected $db;
    
    public function __construct()
    {
        $this->validation = Services::validation();

        $this->common_model = model('Common_model');
        $this->coupon_model = model('Coupon_model');
        $this->db           = \Config\Database::connect();
        
        $this->session = session();
        
        // Check if admin is logged in
        if (!is_admin_logged_in()) {
            redirect('');
        }
        
        // Check access permissions
        $controller = class_basename(service('router')->controllerName());
        $methodName  = class_basename(service('router')->methodName());
        hasAccess($controller, $methodName);
    }
    
    /**
     * Display all coupons
     */
    public function index()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Coupon',
                'pagetitle' => 'Coupon',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Coupon</li></ul>'
            ];
            
            // Debug: Check if model is loaded
            if (!isset($this->coupon_model) || !method_exists($this->coupon_model, 'getAllCoupons')) {
                die("Coupon_model not loaded properly. Check if Coupon_model.php exists in app/Models/");
            }
            
            // Get all coupons
            $couponArray = $this->coupon_model->getAllCoupons();
            $data['couponList'] = $couponArray;
            
            echo view('admin/coupon/coupon_list', $data);
        } else {
            return view('admin/index');
        }
    }
    
    /**
     * Add new coupon
     */
    public function add()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Coupon',
                'pagetitle' => 'Add Coupon',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"> </i> </li> <li> <a href="'.base_url().'coupon/">Manage Coupon</a><i class="fa fa-angle-right"> </i> </li> <li>Add Coupon</li></ul>'
            ];
            
            // Check for error messages
            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }
            
            // Form submission handler
            if (trim($this->request->getPost('submit')) == '') {
                // Display empty form
                echo view('admin/coupon/add_coupon', $data);
            } elseif ($this->request->getPost('submit') == 'Add Coupon') {
                
                // Set validation rules
                $validationRules = [
                    'coupon_code' => 'required|trim|is_unique[coupon.coupon_code]|max_length[50]',
                    'discount' => 'required|numeric|greater_than_equal_to[0]',
                    'valid_from' => 'required|valid_date',
                    'valid_to' => 'permit_empty|valid_date',
                    'usage_limit' => 'permit_empty|is_natural',
                    'max_usage_per_client' => 'required|is_natural|greater_than_equal_to[1]'
                ];
                
                // Validate form
                if (!$this->validate($validationRules)) {
                    return view('admin/coupon/add_coupon', $data);
                } else {
                    // Resolve user-specific assignment
                    $coupon_type        = $this->request->getPost('coupon_type') === 'user_specific' ? 'user_specific' : 'general';
                    $raw_ids            = $this->request->getPost('assigned_client_ids');
                    $assigned_client_ids = null;
                    if ($coupon_type === 'user_specific' && !empty($raw_ids)) {
                        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $raw_ids)))));
                        $assigned_client_ids = !empty($ids) ? json_encode($ids) : null;
                    }

                    // Prepare insert data
                    $insert_data = [
                        'coupon_code'          => strtoupper(trim($this->request->getPost('coupon_code'))),
                        'coupon_type'          => $coupon_type,
                        'assigned_client_ids'  => $assigned_client_ids,
                        'discount'             => $this->request->getPost('discount'),
                        'discount_type'        => $this->request->getPost('discount_type') === 'percentage' ? 'percentage' : 'fixed',
                        'valid_from'           => $this->request->getPost('valid_from'),
                        'valid_to'             => !empty($this->request->getPost('valid_to')) ? $this->request->getPost('valid_to') : null,
                        'usage_limit'          => !empty($this->request->getPost('usage_limit')) ? $this->request->getPost('usage_limit') : null,
                        'max_usage_per_client' => $this->request->getPost('max_usage_per_client'),
                        'description'          => $this->request->getPost('description') ?? null,
                        'status'               => 'Active',
                        'date'                 => date('Y-m-d'),
                        'created_on'           => date('Y-m-d'),
                        'created_by'           => session()->get('userid'),
                        'updated_on'           => date('Y-m-d'),
                        'updated_by'           => session()->get('userid')
                    ];
                    
                    // Insert coupon
                    $id = $this->coupon_model->addCoupon($insert_data);
                    
                    // Set response message
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'Coupon added successfully!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to add coupon', 'msg-type' => 'danger');
                    }
                    
                    $this->session->set($arr_msg);
                    return redirect()->to('coupon/');
                }
            }
        }
    }
    
    /**
     * Edit existing coupon
     */
    public function edit($coupon_id)
    {
        if ($coupon_id > 0) {
            $data = [
                'menutitle' => 'Coupon',
                'pagetitle' => 'Edit Coupon',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="'. base_url().'coupon">Manage Coupon</a><i class="fa fa-angle-right"></i></li> <li>Edit Coupon</li></ul>'
            ];
            
            // Check for error messages
            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }
            
            // Get coupon details
            $couponDetails = $this->coupon_model->getCouponDetails($coupon_id);
            
            if (empty($couponDetails)) {
                $arr_msg = array('suc_msg' => 'Coupon not found!', 'msg-type' => 'danger');
                $this->session->set($arr_msg);
                return redirect()->to('coupon/');
            }
            
            $data['couponDetails'] = $couponDetails;
            $data['assignedClients'] = $this->coupon_model->getAssignedClients($couponDetails->assigned_client_ids ?? '');

            // Form submission handler
            if (trim($this->request->getPost('submit')) == '') {
                // Display form with data
                return view('admin/coupon/edit_coupon', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Edit Coupon') {
                
                // Set validation rules (allow current coupon code)
                $validationRules = [
                    'coupon_code' => 'required|trim|max_length[50]|is_unique[coupon.coupon_code,coupon_id,'.$coupon_id.']',
                    'discount' => 'required|numeric|greater_than_equal_to[0]',
                    'valid_from' => 'required|valid_date',
                    'valid_to' => 'permit_empty|valid_date',
                    'usage_limit' => 'permit_empty|is_natural',
                    'max_usage_per_client' => 'required|is_natural|greater_than_equal_to[1]'
                ];
                
                // Validate form
                if (!$this->validate($validationRules)) {
                    return view('admin/coupon/edit_coupon', $data);
                } else {
                    // Resolve user-specific assignment
                    $coupon_type        = $this->request->getPost('coupon_type') === 'user_specific' ? 'user_specific' : 'general';
                    $raw_ids            = $this->request->getPost('assigned_client_ids');
                    $assigned_client_ids = null;
                    if ($coupon_type === 'user_specific' && !empty($raw_ids)) {
                        $ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $raw_ids)))));
                        $assigned_client_ids = !empty($ids) ? json_encode($ids) : null;
                    }

                    // Prepare update data
                    $update_data = [
                        'coupon_code'          => strtoupper(trim($this->request->getPost('coupon_code'))),
                        'coupon_type'          => $coupon_type,
                        'assigned_client_ids'  => $assigned_client_ids,
                        'discount'             => $this->request->getPost('discount'),
                        'discount_type'        => $this->request->getPost('discount_type') === 'percentage' ? 'percentage' : 'fixed',
                        'valid_from'           => $this->request->getPost('valid_from'),
                        'valid_to'             => !empty($this->request->getPost('valid_to')) ? $this->request->getPost('valid_to') : null,
                        'usage_limit'          => !empty($this->request->getPost('usage_limit')) ? $this->request->getPost('usage_limit') : null,
                        'max_usage_per_client' => $this->request->getPost('max_usage_per_client'),
                        'description'          => $this->request->getPost('description') ?? null,
                        'updated_on'           => date('Y-m-d'),
                        'updated_by'           => session()->get('userid')
                    ];
                    
                    // Update coupon
                    $condition = ['coupon_id' => $coupon_id];
                    $result = $this->coupon_model->updateCoupon($update_data, $condition);
                    
                    // Set response message
                    if ($result) {
                        $arr_msg = array('suc_msg' => 'Coupon updated successfully!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to update coupon', 'msg-type' => 'danger');
                    }
                    
                    $this->session->set($arr_msg);
                    return redirect()->to('coupon/');
                }
            }
        } else {
            return redirect()->to('coupon/');
        }
    }
    
    /**
     * Deactivate coupon
     */
    public function close()
{
    $coupon_id = $this->request->getPost('coupon_id');
    $action = $this->request->getPost('action'); // 'activate' or 'deactivate'
    
    if ($coupon_id > 0) {
        $status = ($action == 'activate') ? 'Active' : 'Inactive';
        
        $update_data = [
            'status' => $status,
            'updated_on' => date('Y-m-d'),
            'updated_by' => session()->get('userid')
        ];
        
        $condition = ['coupon_id' => $coupon_id];
        $result = $this->common_model->updateData('coupon', $update_data, $condition);
        
        echo $result ? 1 : 0;
    } else {
        echo 0;
    }
}

    /**
     * Activate coupon
     */
    public function activate()
    {
        $coupon_id = $this->request->getPost('coupon_id');
        
        if ($coupon_id > 0) {
            $update_data = [
                'status' => 'Active',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            
            $condition = ['coupon_id' => $coupon_id];
            
            // Using common_model for consistency
            $result = $this->common_model->updateData('coupon', $update_data, $condition);
            
            echo json_encode([
                'success' => $result ? true : false,
                'message' => $result ? 'Coupon activated successfully' : 'Failed to activate coupon'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid coupon ID'
            ]);
        }
    }

    /**
     * AJAX: search clients for user-specific coupon assignment
     */
    public function clientSearch()
    {
        $q = trim($this->request->getGet('q') ?? '');
        if (empty($q)) return $this->response->setJSON(['clients' => []]);

        $clients = $this->db->table('client_profile')
            ->select('client_id, client_name, client_mobile_number, client_email')
            ->groupStart()
                ->like('client_name', $q)
                ->orLike('client_mobile_number', $q)
                ->orLike('client_email', $q)
            ->groupEnd()
            ->where('status', 'Active')
            ->limit(20)
            ->get()->getResult();

        return $this->response->setJSON(['clients' => $clients]);
    }

    /**
     * View coupon usage
     */
    public function usage($coupon_id)
    {
        if ($coupon_id > 0 && $this->session->has('userid')) {
            $data = [
                'menutitle' => 'Coupon',
                'pagetitle' => 'Coupon Usage',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li><li><a href="'. base_url().'coupon">Manage Coupon</a><i class="fa fa-angle-right"></i></li><li>Coupon Usage</li></ul>'
            ];
            
            // Get coupon details
            $coupon = $this->coupon_model->getCouponDetails($coupon_id);
            
            if (!$coupon) {
                $arr_msg = array('suc_msg' => 'Coupon not found!', 'msg-type' => 'danger');
                $this->session->set($arr_msg);
                return redirect()->to('coupon/');
            }
            
            $data['coupon'] = $coupon;

            // Get usage statistics
            $usage_stats = $this->coupon_model->getCouponUsageStats($coupon_id);
            $data['usage_stats'] = $usage_stats;

            // Get clients who used this coupon
            $usage_list = $this->coupon_model->getCouponUsers($coupon_id);
            $data['usage_list'] = $usage_list;

            // For user-specific coupons, fetch assigned client details
            $data['assignedClients'] = $this->coupon_model->getAssignedClients($coupon->assigned_client_ids ?? '');
            
            return view('admin/coupon/usage', $data);
        } else {
            return redirect()->to('coupon/');
        }
    }
}