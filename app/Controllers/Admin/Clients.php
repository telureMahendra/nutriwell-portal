<?php
namespace App\Controllers\Admin;
use Config\Services;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Clients extends Controller {
    protected $session;
    protected $validation;
    protected $clients_model;
    protected $common_model;

    public function __construct() {
        $this->validation = Services::validation();
        $this->clients_model = model('App\Models\Clients_model', false);
        $this->common_model = model('App\Models\Common_model', false);
        $this->session = session();
    }

    public function plan_info()
    {
        if (!$this->session->has('userid')) {
            return $this->response->setJSON(['outcome' => 0, 'message' => 'Session expired']);
        }
        $plan_id = trim($this->request->getPost('plan_id'));
        $result = [];
        if ($plan_id > 0) {
            $condition = ['status' => 'Active', 'plan_id' => $plan_id];
            $planData = $this->common_model->getAllData('plan', $condition);
            if (!empty($planData)) {
                $result['plan_duration'] = trim($planData[0]->validaty);
                $result['price'] = trim($planData[0]->price);
                $result['outcome'] = 1;
            } else {
                $result['outcome'] = 0;
                $result['message'] = "Plan not found";
            }
        } else {
            $result['outcome'] = 0;
            $result['message'] = "Invalid Plan ID";
        }
        return $this->response->setJSON($result);
    }

    public function apply_coupon()
    {
        if (!$this->session->has('userid')) {
            return $this->response->setJSON(['outcome' => 0, 'message' => 'Session expired']);
        }
        $coupon_id = trim($this->request->getPost('coupon_id'));
        $result = [];
        if ($coupon_id != '') {
            $condition = ['status' => 'Active', 'coupon_id' => $coupon_id];
            $couponData = $this->common_model->getAllData('coupon', $condition);
            if (!empty($couponData)) {
                $result['discount'] = trim($couponData[0]->discount);
                $result['outcome'] = 1;
            } else {
                $result['outcome'] = 0;
                $result['message'] = "Coupon not found";
            }
        } else {
            $result['outcome'] = 0;
            $result['message'] = "No coupon selected";
        }
        return $this->response->setJSON($result);
    }

    public function index() {
        if ($this->session->has('userid')) {
            // Get ALL filter parameters from URL
            $status = $this->request->getGet('status') ?? '';
            $paymentStatus = $this->request->getGet('payment_status') ?? '';
            
            // Check if filter button was clicked
            if ($this->request->getGet('submit') == 'Filter') {
                $fromdate = $this->request->getGet('fromdate');
                $endDate = $this->request->getGet('todate');                
                $search = trim($this->request->getGet('search'));
            } else {
                $fromdate = '';
                $endDate = '';                
                $search = '';
            }

            // Set page title based on filter
            $pageTitle = 'Clients';
            if ($status == 'Active') {
                $pageTitle = 'Active Clients';
            } elseif ($status == 'Inactive') {
                $pageTitle = 'Inactive Clients';
            }
            if ($paymentStatus == 'paid') {
                $pageTitle = 'Paid Clients';
            } elseif ($paymentStatus == 'unpaid') {
                $pageTitle = 'Unpaid Clients';
            }

            $data = [
                'status_filter' => $status,
                'payment_status_filter' => $paymentStatus,
                'fromdate' => $fromdate,
                'todate' => $endDate,                
                'search' => $search,
                'menutitle' => 'Clients',
                'pagetitle' => $pageTitle,
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage ' . $pageTitle . '</li></ul>'
            ];

            // Build conditions based on URL parameters
            $condition = [];
            
            // FIX: Only add status condition when specifically filtering by status
            if (!empty($status)) {
                $condition['C.status'] = $status;
            }
            // Don't add status condition for "All Clients" or payment status filters
            
            // Date range filter
            if($fromdate != "") {
                $condition['DATE(C.created_on) >='] = $fromdate;
                $condition['DATE(C.created_on) <='] = $endDate;
            }
            
            $like = [];
            if (!empty($search)) {
                $like = [
                    'client_name' => $search,
                    'client_mobile_number' => $search
                ];
            }

            // ============================================
            // FIX: Get TOTAL count WITHOUT pagination
            // ============================================
            $totalClientsCount = $this->clients_model->getTotalClientsCount($condition, $like, $paymentStatus, $status);
            
            // Setup pagination
            $pager = service('pager');
            $page = (int)(($this->request->getVar('page')!==null)?$this->request->getVar('page'):1)-1;
            $allowedPerPage = [10, 20, 50, 100];
            $perPage = (int)($this->request->getGet('per_page') ?? 50);
            if (!in_array($perPage, $allowedPerPage)) {
                $perPage = 50;
            }
            $data['perPage'] = $perPage;
            
            // Create pagination
            $pager->makeLinks($page+1, $perPage, $totalClientsCount);
            
            $offset = $page * $perPage;

            // Get paginated clients with all filters
            $clientsArray = $this->clients_model->getClients('client_profile', $condition, $like, $perPage, $offset, $paymentStatus, $status);
            $data['clientsList'] = $clientsArray;
            
            // Pass the ACTUAL TOTAL count to view
            $data['totalClientsCount'] = $totalClientsCount;
            $data['currentPageCount'] = count($clientsArray);
            $data['currentPage'] = $page + 1;
            $data['totalPages'] = ceil($totalClientsCount / $perPage);

            // Compute footer stats: clients with/without active plan (across ALL matching clients)
            $db = \Config\Database::connect();
            $apBuilder = $db->table('client_profile AS C')
                ->select('COUNT(DISTINCT C.client_id) AS cnt')
                ->join('booking AS B',
                    'C.client_id = B.client_id AND B.status = "Active" AND B.plan_expiry_date >= CURDATE()',
                    'inner');
            if (!empty($condition)) {
                foreach ($condition as $k => $v) {
                    $apBuilder->where($k, $v);
                }
            }
            if (!empty($like)) {
                $apBuilder->groupStart();
                if (isset($like['client_name'])) {
                    $apBuilder->orLike('C.client_name', $like['client_name']);
                    $apBuilder->orLike('C.client_email', $like['client_name']);
                }
                if (isset($like['client_mobile_number'])) {
                    $apBuilder->orLike('C.client_mobile_number', $like['client_mobile_number']);
                }
                $apBuilder->groupEnd();
            }
            $data['footerActivePlans']   = (int)($apBuilder->get()->getRow()->cnt ?? 0);
            $data['footerNoActivePlans'] = $totalClientsCount - $data['footerActivePlans'];

            if($this->request->getGet('download') != "") {              
                // For download, get ALL records without pagination
                $allClients = $this->clients_model->getClients("client_profile", $condition, $like, null, null, $paymentStatus, $status);
                $this->download($allClients);
                die();
            }  

            return view('admin/clients/clients_list', $data);
        } else {
            return view('admin/index');
        }
    }


/**
 * Get total clients count without pagination
 */
private function getTotalClientsCount($condition, $like, $paymentStatus = '')
{
    // We need to create a separate query just for counting
    // OR modify the existing getClients method to accept a count parameter
    
    // Option 1: Create a new method in model
    return $this->clients_model->getClientsCount($condition, $like, $paymentStatus);
    
    // Option 2: Call getClients with null limits and count the result
    // $allClients = $this->clients_model->getClients("client_profile", $condition, $like, null, null, $paymentStatus);
    // return count($allClients);
}

    private function get_water_data() {
        return [
            'today' => '2.5L',
            'average' => '2.8L',
            'target' => '3.0L',
            'completion' => 83,
            'series' => [
                [2.0, 2.5, 3.0, 2.8, 2.2, 3.2, 2.5, 2.8, 2.7, 3.1, 2.9, 2.6, 2.4, 2.8]
            ],
            'categories' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14'],
            'logs' => [
                ['time' => '08:00 AM', 'amount' => 500, 'type' => 'Glass'],
                ['time' => '10:30 AM', 'amount' => 500, 'type' => 'Bottle'],
                ['time' => '12:30 PM', 'amount' => 750, 'type' => 'Bottle'],
                ['time' => '03:00 PM', 'amount' => 500, 'type' => 'Glass'],
                ['time' => '05:30 PM', 'amount' => 250, 'type' => 'Glass']
            ],
            'weekly_average' => '2.8L',
            'monthly_goal' => '85L',
            'current_month' => '72L',
            'achievements' => [
                '7_day_streak' => true,
                'weekly_goal_met' => 4,
                'monthly_goal_progress' => 85
            ]
        ];
    }
    
    private function get_calorie_data() {
        return [
            'today' => 1850,
            'average' => 1950,
            'target' => 2200,
            'completion' => 84,
            'series' => [
                [1850, 1950, 2100, 1900, 2200, 1800, 2000, 2050, 1950, 2150, 1900, 2250, 1850, 1950]
            ],
            'categories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'meals' => [
                [
                    'name' => 'Oatmeal with Fruits',
                    'time' => '08:00 AM',
                    'type' => 'Breakfast',
                    'calories' => 450,
                    'carbs' => 65,
                    'protein' => 15,
                    'fat' => 8
                ],
                [
                    'name' => 'Grilled Chicken Salad',
                    'time' => '01:00 PM',
                    'type' => 'Lunch',
                    'calories' => 650,
                    'carbs' => 25,
                    'protein' => 45,
                    'fat' => 12
                ],
                [
                    'name' => 'Protein Shake',
                    'time' => '04:30 PM',
                    'type' => 'Snack',
                    'calories' => 250,
                    'carbs' => 15,
                    'protein' => 30,
                    'fat' => 2
                ],
                [
                    'name' => 'Salmon with Vegetables',
                    'time' => '07:00 PM',
                    'type' => 'Dinner',
                    'calories' => 500,
                    'carbs' => 20,
                    'protein' => 35,
                    'fat' => 18
                ]
            ],
            'macros' => [
                'carbs' => ['value' => 45, 'target' => 50],
                'protein' => ['value' => 30, 'target' => 30],
                'fat' => ['value' => 25, 'target' => 20]
            ],
            'remaining' => 350
        ];
    }
    
    private function get_weight_data() {
        return [
            'current' => 73.2,
            'start' => 75.0,
            'target' => 70.0,
            'change' => -1.8,
            'series' => [
                [75.0, 74.8, 74.5, 74.2, 74.0, 73.8, 73.6, 73.5, 73.4, 73.3, 73.2, 73.1, 73.0, 73.2]
            ],
            'categories' => ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8', 'W9', 'W10', 'W11', 'W12', 'W13', 'W14'],
            'logs' => [
                ['date' => '2024-01-01', 'time' => 'Morning', 'weight' => 75.0, 'note' => 'Start of program'],
                ['date' => '2024-01-08', 'time' => 'Morning', 'weight' => 74.5, 'note' => ''],
                ['date' => '2024-01-15', 'time' => 'Morning', 'weight' => 74.2, 'note' => ''],
                ['date' => '2024-01-22', 'time' => 'Morning', 'weight' => 73.8, 'note' => ''],
                ['date' => '2024-01-29', 'time' => 'Morning', 'weight' => 73.6, 'note' => ''],
                ['date' => 'Today', 'time' => 'Morning', 'weight' => 73.2, 'note' => 'After workout']
            ],
            'weekly_change' => -0.4,
            'monthly_change' => -1.8,
            'trend' => 'losing',
            'pace' => -0.4,
            'goal_date' => '2024-03-15',
            'weeks_to_goal' => 8
        ];
    }
    
    private function get_bmi_data() {
        $currentBMI = 23.6;
        
        return [
            'current' => $currentBMI,
            'start' => 24.5,
            'target' => 22.0,
            'category' => $this->get_bmi_category($currentBMI),
            'progress' => 65,
            'series' => [
                [24.5, 24.3, 24.1, 23.9, 23.8, 23.7, 23.6, 23.5, 23.5, 23.6, 23.5, 23.4, 23.4, 23.6]
            ],
            'categories' => ['Jan W1', 'Jan W2', 'Jan W3', 'Jan W4', 'Feb W1', 'Feb W2', 'Feb W3', 'Feb W4', 'Mar W1', 'Mar W2', 'Mar W3', 'Mar W4', 'Apr W1', 'Apr W2'],
            'history' => [
                ['date' => '2024-01-01', 'bmi' => 24.5, 'weight' => 75.0, 'height' => 175],
                ['date' => '2024-02-01', 'bmi' => 23.9, 'weight' => 73.2, 'height' => 175],
                ['date' => 'Today', 'bmi' => 23.6, 'weight' => 72.3, 'height' => 175]
            ],
            'risk_factors' => [
                'blood_pressure' => 'Normal',
                'cholesterol' => 'Borderline',
                'blood_sugar' => 'Normal'
            ],
            'recommendations' => [
                'Continue current exercise routine',
                'Maintain protein intake',
                'Consider strength training 2x/week'
            ]
        ];
    }
    
    private function get_steps_data() {
        return [
            'today' => 8234,
            'average' => 9567,
            'target' => 10000,
            'completion' => 82,
            'series' => [
                [8234, 10432, 7567, 11234, 9876, 8567, 12098, 9345, 11023, 7890, 10567, 9234, 11345, 9678]
            ],
            'categories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'breakdown' => [
                ['period' => 'Morning (6-12)', 'steps' => 3245, 'calories' => 120],
                ['period' => 'Afternoon (12-6)', 'steps' => 2567, 'calories' => 95],
                ['period' => 'Evening (6-12)', 'steps' => 2422, 'calories' => 90]
            ],
            'achievements' => [
                [
                    'name' => '5K Steps Daily',
                    'description' => '7-day streak completed!',
                    'icon' => 'medal',
                    'color' => '#f39c12',
                    'completed' => true
                ],
                [
                    'name' => 'Weekend Warrior',
                    'description' => '15K steps on Saturday',
                    'icon' => 'fire',
                    'color' => '#e74c3c',
                    'completed' => true
                ],
                [
                    'name' => 'Marathon Walker',
                    'description' => '50K steps in a week',
                    'icon' => 'walking',
                    'color' => '#3498db',
                    'completed' => false,
                    'progress' => 85
                ]
            ],
            'weekly_total' => 67456,
            'monthly_average' => 8923,
            'best_day' => ['date' => '2024-01-20', 'steps' => 12098],
            'current_streak' => 7
        ];
    }
    
    private function get_calories_burned_data() {
        return [
            'today' => 354,
            'average' => 420,
            'target' => 500,
            'completion' => 71,
            'series' => [
                [450, 520, 380, 610, 490, 570, 530, 480, 550, 420, 590, 510, 470, 540]
            ],
            'categories' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'exercises' => [
                [
                    'name' => 'Morning Jog',
                    'duration' => 30,
                    'calories' => 254,
                    'type' => 'Cardio',
                    'intensity' => 'Moderate',
                    'time' => '07:00 AM'
                ],
                [
                    'name' => 'Yoga',
                    'duration' => 20,
                    'calories' => 100,
                    'type' => 'Flexibility',
                    'intensity' => 'Light',
                    'time' => '06:00 PM'
                ]
            ],
            'total_exercise_time' => 50,
            'activities_completed' => 2,
            'weekly_total' => 3450,
            'monthly_goal' => 15000,
            'monthly_progress' => 8560,
            'favorite_activity' => 'Running',
            'calories_burned_today' => [
                'exercise' => 354,
                'basal' => 1850,
                'total' => 2204
            ]
        ];
    }
    
    private function get_bmi_category($bmi) {
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Normal';
        if ($bmi < 30) return 'Overweight';
        return 'Obesity';
    }
    
    public function add() {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Client',
                'pagetitle' => 'Add Clients',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"> </i> </li> <li> <a href="'.base_url().'clients/" >Manage Client</a><i class="fa fa-angle-right"> </i> </li> <li>Add Client</li></ul>'
            ];
            
            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }
            
            $condition = ['status' => 'Active'];
            $lifestyleDetails = $this->common_model->getAllData('lifestyle',$condition);
            $allLifestyle = ['' => 'Select Lifestyle'];
            foreach ($lifestyleDetails as $lifestyle) {
                $allLifestyle[$lifestyle->lifestyle_id] = $lifestyle->name;
            }
            $data['lifestyleList'] = $allLifestyle;

            $goalDetails = $this->common_model->getAllData('goal',$condition);
            $allGoal = ['' => 'Select Goal'];
            foreach ($goalDetails as $goal) {
                $allGoal[$goal->goal_id] = $goal->name;
            }
            $data['goalList'] = $allGoal;

            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/clients/add_clients',$data);
            } elseif ($this->request->getPost('submit') == 'Add Clients') {
                $validationRules = [
                    'client_name' => ['label' => 'Client Name','rules' =>'required'],
                    'email' => ['label' => 'Email', 'rules' => 'required|valid_email'],
                    'phone_number' => ['label' => 'Phone Number', 'rules' => 'required|is_unique[client_profile.client_mobile_number]'],
                    'client_gender' => ['label' => '', 'rules' => 'required'],
                    'lifestyle' => ['label' => 'Lifestyle', 'rules' => 'required'],
                    'goal' => ['label' => 'Goal', 'rules' => 'required'],
                    'age' => ['label' => 'Age', 'rules' => 'required'],
                    'weight' => ['label' => 'Weight', 'rules' => 'required'],
                    'height' => ['label' => 'Height', 'rules' => 'required'],
                    'diet' => ['label' => 'Diet', 'rules' => 'required'],
                    'heightIn' => ['label' => 'Height In', 'rules' => 'required'],
                ];
                
                if (!$this->validate($validationRules)) {
                    return view('admin/clients/add_clients', $data);
                } else {
                    $client_img_info = $this->request->getFiles()['clientimage'];
                    $clientImage = $client_img_info->getName();
                    
                    if (!empty($clientImage)) {
                        $client_srt = explode('.', $clientImage);
                        $client_name = uniqid() . "." . $client_srt[1];
                        $client_targetpath = "./public/uploads/client";
                        $client_img_info->move($client_targetpath,$client_name);
                    }
                    
                    $client_login_insert_data = [
                        'client_phone' => $this->request->getPost('phone_number'),
                        'client_status' => 'Active',
                        'is_login' => 'No',
                        'profile_complete' => 'No',
                    ];
                    
                    $id = $this->common_model->addData('client_login',$client_login_insert_data);
                    
                    if ($id > 0) {
                        $client_number = mt_rand(100000, 999999);
                        $insert_data = [
                            'client_name' => $this->request->getPost('client_name'),
                            'client_email' => $this->request->getPost('email'),
                            'client_profile_pic' => $client_name ?? '',
                            'client_id' => $id,
                            'client_number' => $client_number.''.$id,
                            'age' => $this->request->getPost('age'),
                            'weight' => $this->request->getPost('weight'),
                            'diet' => $this->request->getPost('diet'),
                            'height' => $this->request->getPost('height'),
                            'height_in' => $this->request->getPost('heightIn'),
                            'lifestyle_id' => $this->request->getPost('lifestyle'),
                            'goal_id' => $this->request->getPost('goal'),
                            'client_mobile_number' => $this->request->getPost('phone_number'),
                            'client_gender' => $this->request->getPost('client_gender'),
                            'status' => 'Active',
                            'created_on' => date('Y-m-d'),
                            'created_by' => session()->get('userid'),
                            'updated_on' => date('Y-m-d'),
                            'updated_by' => session()->get('userid')
                        ];  

                        $id1 = $this->common_model->addData('client_profile',$insert_data);
                        
                        if($id1 > 0) {
                            $update_data = [
                                'profile_complete' => 'Yes'
                            ];
                            $condition1 = ['client_id' => $id];
                            $this->common_model->updateData('client_login', $update_data, $condition1);

                            $arr_msg = array('suc_msg' => 'Client added successfully!!!', 'msg-type' => 'success');
                        } else {
                            $arr_msg = array('suc_msg' => 'Failed to add client', 'msg-type' => 'danger');
                        }
                        
                        $this->session->set($arr_msg);
                        return redirect()->to('clients/');
                    }
                }
            }
        }
    }

    public function edit($client_id) {
        if ($client_id > 0) {
            $data = [
                'menutitle' => 'Client',
                'pagetitle' => 'Edit Client',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="'. base_url().'clients">Manage Client</a><i class="fa fa-angle-right"></i></li> <li>Edit Client</a></li></ul>'
            ];
            
            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }
            
            $condition = ['status' => 'Active'];
            $lifestyleDetails = $this->common_model->getAllData('lifestyle',$condition);
            $allLifestyle = ['' => 'Select Lifestyle'];
            foreach ($lifestyleDetails as $lifestyle) {
                $allLifestyle[$lifestyle->lifestyle_id] = $lifestyle->name;
            }
            $data['lifestyleList'] = $allLifestyle;

            $goalDetails = $this->common_model->getAllData('goal',$condition);
            $allGoal = ['' => 'Select Goal'];
            foreach ($goalDetails as $goal) {
                $allGoal[$goal->goal_id] = $goal->name;
            }
            $data['goalList'] = $allGoal;

            $conditionU = ['user_role' => 'SalesAgent', 'user_status' => 'Active'];
            $userDetails = $this->common_model->getAllData('users',$conditionU);
            $userslist = ['' => 'Select Sales Agent'];
            foreach ($userDetails as $user) {
                $userslist[$user->uid] = trim($user->first_name.' '.$user->last_name);
            }
            $data['userslist'] = $userslist;

            $conditionMentor = ['status' => 'Active'];
            $mentorDetails = $this->common_model->getAllData('doctor', $conditionMentor);
            $mentorList = ['' => 'Select Mentor'];
            foreach ($mentorDetails as $mentor) {
                $mentorList[$mentor->doctor_name] = trim($mentor->doctor_name);
            }
            $data['mentorList'] = $mentorList;

            $condition1 = ['client_id' => $client_id];
            $clientDetails = $this->common_model->getAllData('client_profile',$condition1);
            $data['clientDetails'] = $clientDetails;

            if (trim($this->request->getPost('submit')) == '') {
                return view('admin/clients/clients_edit', $data);
            } elseif (trim($this->request->getPost('submit')) == 'Edit Client') {
                $validationRules = [
                    'client_name' => ['label' => 'Client Name','rules' =>'required'],
                    'email' => ['label' => 'Email', 'rules' => 'required'],
                    'phone_number' => ['label' => 'Phone Number', 'rules' => 'required'],
                    'client_gender' => ['label' => '', 'rules' => 'required'],
                    'lifestyle' => ['label' => 'Lifestyle', 'rules' => 'required'],
                    'goal' => ['label' => 'Goal', 'rules' => 'required'],
                    'age' => ['label' => 'Age', 'rules' => 'required'],
                    'weight' => ['label' => 'Weight', 'rules' => 'required'],
                    'height' => ['label' => 'Height', 'rules' => 'required'],
                    'diet' => ['label' => 'Diet', 'rules' => 'required'],
                    'heightIn' => ['label' => 'Height In', 'rules' => 'required'],
                    'uid' => ['label' => 'Sales Agent', 'rules' => 'permit_empty'],
                    'mentor' => ['label' => 'Mentor', 'rules' => 'permit_empty'],
                ];
                
                if (!$this->validate($validationRules)) {
                    return view('admin/clients/clients_edit', $data);
                } else {
                    $update_data = [
                        'client_name' => $this->request->getPost('client_name'),
                        'client_email' => $this->request->getPost('email'),
                        'age' => $this->request->getPost('age'),
                        'weight' => $this->request->getPost('weight'),
                        'diet' => $this->request->getPost('diet'),
                        'height' => $this->request->getPost('height'),
                        'height_in' => $this->request->getPost('heightIn'),
                        'lifestyle_id' => $this->request->getPost('lifestyle'),
                        'goal_id' => $this->request->getPost('goal'),
                        'client_mobile_number' => $this->request->getPost('phone_number'),
                        'client_gender' => $this->request->getPost('client_gender'),
                        'uid' => $this->request->getPost('uid'),
                        'mentor' => $this->request->getPost('mentor'),
                        'updated_on' => date('Y-m-d'),
                        'updated_by' => session()->get('userid')
                    ];  
                    
                    $id = $this->common_model->updateData('client_profile', $update_data, $condition1);
                    
                    if($id > 0) {
                        $arr_msg = array('suc_msg' => 'Client updated successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to update client', 'msg-type' => 'danger');
                    }

                    $this->session->set($arr_msg);
                    return redirect()->to('clients/');
                }
            }
        }
    }

    public function close() {
        $client_id = $this->request->getPost('client_id');
        $condition = ['client_id' => $client_id];
        $clientDetails = $this->common_model->getAllData('client_profile',$condition);
        
        if ($client_id > 0) {
            $update_data = [
                'status' => 'Inactive',
                'updated_on' => date('Y-m-d'),
                'updated_by' => session()->get('userid')
            ];
            
            $id = $this->common_model->updateData('client_profile', $update_data, $condition);
            
            if($id > 0) {
                $update_client_login_data = [
                    'client_status' => 'In-Active',
                    'is_login' => 'No',
                    'profile_complete' => 'No'
                ];
                
                $id1 = $this->common_model->updateData('client_login', $update_client_login_data, $condition);
                
                $old_image = $clientDetails[0]->client_profile_pic; 
                if(@file_exists("./public/uploads/client/".$old_image)) {
                    @unlink("./public/uploads/client/".$old_image);
                }
                echo $id;
            }
        } else {
            echo 0;
        }
    }

    public function view($client_id) {
        if ($client_id > 0) {
            $data = [
                'menutitle' => 'Client',
                'pagetitle' => 'View Client',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="'. base_url().'">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="'. base_url().'clients">Manage Client</a><i class="fa fa-angle-right"></i></li> <li>View Client</li></ul>'
            ];
            
            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }
            
            // Get Client Basic Details
            $condition1 = ['client_id' => $client_id];
            $clientDetails = $this->common_model->getAllData('client_profile', $condition1);
            $data['clientDetails'] = $clientDetails;
            
            // Get Active Plan Details using model method
            $activePlanDetails = $this->clients_model->getActivePlanDetails($client_id);
            $data['activePlanDetails'] = $activePlanDetails;
            
            // Get All Plans History using model method
            $allPlansHistory = $this->clients_model->getAllPlansHistory($client_id);
            $data['allPlansHistory'] = $allPlansHistory;
            
            // Calculate Water Intake
            $waterModel = new \App\Models\Water_model();
            $today = date('Y-m-d');
            $waterData = $waterModel->getWater($today, $client_id);
            
            $waterIntake = 0;
            $waterTarget = 3.0;
            
            if (!empty($waterData)) {
                $waterIntake = floatval($waterData[0]->quantity) ?? 0;
                $waterTarget = floatval($waterData[0]->target_quantity) ?? 3.0;
            }
            
            $glassesConsumed = round($waterIntake / 0.25);
            $targetGlasses = round($waterTarget / 0.25);
            
            $data['water_glasses_consumed'] = $glassesConsumed;
            $data['water_target_glasses'] = $targetGlasses;
            $data['water_intake_liters'] = $waterIntake;
            $data['water_target_liters'] = $waterTarget;

            // Current health targets
            $db = \Config\Database::connect();
            $profileRow = $db->table('client_profile')
                ->select('target_water, target_sleep, target_weight')
                ->where('client_id', $client_id)
                ->get()->getRow();

            $data['target_water']   = $profileRow ? (int)$profileRow->target_water               : 8;
            $data['target_sleep']   = $profileRow ? (int)$profileRow->target_sleep               : 480;
            $data['target_weight']  = $profileRow ? (float)($profileRow->target_weight ?? 0)     : 0;
            $data['target_steps']   = model('App\Models\StepsTargetModel')->getCurrentTarget($client_id);
            $data['target_calorie'] = model('App\Models\CaloriesTargetModel')->getCurrentTarget($client_id, null, 'intake');

            return view('admin/clients/view_clients', $data);
        }
    }

    public function set_targets($client_id)
    {
        if (!$this->session->has('userid')) {
            return redirect()->to('');
        }

        $db = \Config\Database::connect();
        $db->table('client_profile')->where('client_id', $client_id)->update([
            'target_water'  => (int)$this->request->getPost('target_water'),
            'target_sleep'  => (int)$this->request->getPost('target_sleep'),
            'target_weight' => (float)$this->request->getPost('target_weight'),
        ]);

        model('App\Models\StepsTargetModel')->setTarget($client_id, (int)$this->request->getPost('target_steps'));
        model('App\Models\CaloriesTargetModel')->setTarget($client_id, (int)$this->request->getPost('target_calorie'), 'intake');

        $this->session->setFlashdata('suc_msg', 'Targets updated successfully!');
        return redirect()->to('clients/view/' . $client_id);
    }

    public function download($result) {
        require(APPPATH.'ThirdParty/phpspreadsheet/vendor/autoload.php');   
        $fileName = 'ClientData-'.time().'.xlsx'; 
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Client Number');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Age');
        $sheet->setCellValue('E1', 'Phone');
        $sheet->setCellValue('F1', 'Gender');
        $sheet->setCellValue('G1', 'Is Free');
        $sheet->setCellValue('H1', 'Sales Agent');
        $sheet->setCellValue('I1', 'Created On');
        
        $rowCount = 2;
        foreach ($result as $row) {
            if($row->bookStatus == 'Active') {
                $status = 'Paid';
            } elseif (time() > strtotime($row->plan_expiry_date) && $row->plan_expiry_date != "") {
                $status = 'Expired';
            } else {
                $status = 'Unpaid';
            }
            
            $sheet->setCellValue('A' . $rowCount, $row->client_number);
            $sheet->setCellValue('B' . $rowCount, $row->client_name);
            $sheet->setCellValue('C' . $rowCount, $row->client_email);
            $sheet->setCellValue('D' . $rowCount, $row->age);
            $sheet->setCellValue('E' . $rowCount, $row->client_mobile_number);
            $sheet->setCellValue('F' . $rowCount, $row->client_gender);
            $sheet->setCellValue('G' . $rowCount, $status);
            $sheet->setCellValue('H' . $rowCount, $row->sales_agent);
            $sheet->setCellValue('I' . $rowCount, date('d/m/Y', strtotime($row->created_on)));
            $rowCount++;
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); 
        header('Content-Disposition: attachment;filename="'.$fileName.'"'); 
        header('Cache-Control: max-age=0'); 
        $writer->save('php://output');
        exit;
    }

    public function listcomment($id) {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Comment',
                'pagetitle' => 'Comment',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"> </i> </li> <a href="' . base_url('clients') . '">Clients</a> <i class="fa fa-angle-right"> </i> </li>  <li>Manage Comment</li></ul>',
                "client_id" => $id
            ];
            
            $condition = ['client_id' => $id];
            $clientcomment_Array = $this->clients_model->getClientComment($id);
            $data['clientcomment'] = $clientcomment_Array;

            return view('admin/clients/comment', $data);
        } else {
            return view('admin/index');
        }
    }
    
    public function postcomment() {
        $comment_id = trim($this->request->getPost('comment_id'));
        $client_id = trim($this->request->getPost('client_id'));
        $comment = trim($this->request->getPost('comment'));
        
        if ($comment_id > 0) {
            $condition = ['id' => $comment_id];
            $update_data['comment'] = $comment;
            $id = $this->common_model->updateData('clientcomment', $update_data, $condition);
            if ($id > 0) {
                echo 1;
            } else {
                echo 0;
            }
        } else {
            $insert_data['client_id'] = $client_id;
            $insert_data['date'] = date('Y-m-d H:i:s');
            $insert_data['comment'] = $comment;
            $id = $this->common_model->addData('clientcomment', $insert_data);
            if ($id > 0) {
                echo 1;
            } else {
                echo 0;
            }
        }
    }
    
    public function closecomment() {
        $comment_id = $this->request->getPost('comment_id');
        $condition = ['id' => $comment_id];
        
        if ($comment_id > 0) {
            $id = $this->common_model->deleteClientcomment($condition);
            echo $id;
        } else {
            echo 0;
        }
    }
    
    /**
     * Check if client has active plan (AJAX method)
     */
    public function checkActivePlan($client_id) {
        if ($client_id > 0) {
            $result = $this->clients_model->checkActivePlanStatus($client_id);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($result);
            }
            
            return $result;
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid client ID']);
        }
        
        return null;
    }

    /**
     * Manually add subscription plan for client
     */
    public function add_subscription($client_id = 0)
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Clients',
                'pagetitle' => 'Add Subscription',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"> </i> </li> <li> <a href="' . base_url() . 'clients/" >Manage Client</a><i class="fa fa-angle-right"> </i> </li> <li>Add Subscription</li></ul>',
                'selected_client_id' => $client_id
            ];

            if ($this->session->get('err_msg')) {
                $data['err_msg'] = $this->session->get('err_msg');
                $this->session->remove('err_msg');
            }

            $condition = ['status' => 'Active'];
            // Get Clients
            $clientlist = $this->common_model->getAllData('client_profile', $condition);
            $AllClients = ['' => 'Select client'];
            foreach ($clientlist as $client) {
                $AllClients[$client->client_id] = $client->client_name . ' (' . $client->client_mobile_number . ')';
            }
            $data['AllClients'] = $AllClients;

            // Get Plans
            $planlist = $this->common_model->getAllData('plan', $condition);
            $Allplans = ['' => 'Select Plan'];
            foreach ($planlist as $plan) {
                $Allplans[$plan->plan_id] = $plan->name . ' - ' . $plan->category . ' (₹' . $plan->price . ')';
            }
            $data['Allplans'] = $Allplans;

            // Get Cities
            $citylist = $this->common_model->getAllData('city', $condition);
            $Allcitys = ['' => 'Select city'];
            foreach ($citylist as $city) {
                $Allcitys[$city->city_id] = $city->city_name;
            }
            $data['Allcitys'] = $Allcitys;

            // Get Languages
            $languagelist = $this->common_model->getAllData('language', $condition);
            $Alllanguages = ['' => 'Select Language'];
            foreach ($languagelist as $language) {
                $Alllanguages[$language->language_id] = $language->language;
            }
            $data['Alllanguages'] = $Alllanguages;

            // Get Coupons
            $couponlist = $this->common_model->getAllData('coupon', $condition);
            $allCoupon = ['' => 'Select Coupon'];
            foreach ($couponlist as $coupon) {
                $allCoupon[$coupon->coupon_id] = $coupon->coupon_code;
            }
            $data['allCoupon'] = $allCoupon;

            if ($this->request->getPost('submit') == 'Add Subscription') {
                $validationRules = [
                    'client_id'     => 'required',
                    'plan_id'       => 'required',
                    'subtotal'      => 'required',
                    'total_amount'  => 'required',
                    'payment_status' => 'required',
                    'start_date'    => 'required',
                ];

                if ($this->request->getPost('payment_status') == 'Yes') {
                    $validationRules['payment_method'] = 'required';
                }

                if (!$this->validate($validationRules)) {
                    return view('admin/clients/add_subscription', $data);
                } else {
                    $plan_id = $this->request->getPost('plan_id');
                    $plan_duration = $this->request->getPost('plan_duration');
                    $start_date = $this->request->getPost('start_date');
                    
                    // Calculate expiry date
                    $month = explode(' ', $plan_duration);
                    $plan_expiry_date = date('Y-m-d', strtotime($start_date . ' + ' . ($month[0] ?? 0) . ' months'));

                    $insert_data = [
                        'client_id'      => $this->request->getPost('client_id'),
                        'plan_id'        => $plan_id,
                        'language_id'    => $this->request->getPost('language_id') ?: 0,
                        'city_id'        => $this->request->getPost('city_id') ?: 0,
                        'plan_duration'  => $plan_duration,
                        'subtotal'       => $this->request->getPost('subtotal'),
                        'coupon_id'      => $this->request->getPost('coupon_id') ?: 0,
                        'discount'       => $this->request->getPost('discount') ?: 0,
                        'total_amount'   => $this->request->getPost('total_amount'),
                        'payment'        => $this->request->getPost('payment_status'), // 'Yes' for Paid, 'No' for Unpaid
                        'payment_method' => $this->request->getPost('payment_method') ?: '',
                        'status'         => 'Active',
                        'date'           => $start_date,
                        'plan_expiry_date' => $plan_expiry_date,
                        'created_on'     => date('Y-m-d'),
                        'created_by'     => session()->get('userid'),
                        'updated_on'     => date('Y-m-d'),
                        'updated_by'     => session()->get('userid'),
                        // Default values for non-nullable fields
                        'language'       => 'English',
                        'language_id'    => 0,
                        'tax_rate'       => 0,
                        'taxable_amount' => 0,
                        'convenience_fee' => 0,
                        'order_id'       => 'MANUAL_' . time(),
                        'enc_val'        => '',
                        'tracking_id'    => '',
                        'response'       => ''
                    ];

                    $id = $this->common_model->addData('booking', $insert_data);
                    
                    if ($id > 0) {
                        $this->session->setFlashdata(['suc_msg' => 'Subscription added successfully!!!', 'msg-type' => 'success']);
                    } else {
                        $this->session->setFlashdata(['suc_msg' => 'Failed to add subscription', 'msg-type' => 'danger']);
                    }
                    return redirect()->to('clients/');
                }
            }

            return view('admin/clients/add_subscription', $data);
        } else {
            return view('admin/index');
        }
    }
    
    /**
     * Get Client Dashboard Summary (AJAX method)
     */
    public function getClientDashboardSummary($client_id) {
        if ($client_id > 0) {
            $summary = $this->clients_model->getClientDashboardSummary($client_id);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($summary);
            }
            
            return $summary;
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid client ID']);
        }
        
        return null;
    }
    
    /**
     * Get Expiring Plans (for notifications)
     */
    public function getExpiringPlans($days = 7) {
        $expiringPlans = $this->clients_model->getExpiringPlans($days);
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($expiringPlans);
        }
        
        return $expiringPlans;
    }

    // In your Clients controller, add this method
public function viewClient($id)
{
    // Your existing client view code...
    
    // Add calorie burned data
    $calorieBurnedModel = new \App\Models\CalorieBurnedModel();
    $burned_data = [
        'today' => $calorieBurnedModel->getTodayBurned($id),
        'average' => $calorieBurnedModel->getDailyAverageBurned($id, 30),
        'target' => $calorieBurnedModel->getClientTarget($id)['calorie_burned_target'],
        'exercises' => []
    ];
    
    // Calculate completion
    $completion = $burned_data['target'] > 0 ? round(($burned_data['today'] / $burned_data['target']) * 100) : 0;
    if ($completion > 100) $completion = 100;
    $burned_data['completion'] = $completion;
    
    // Get chart data
    $chartData = $calorieBurnedModel->getChartData($id, 7);
    $burned_data['series'] = [$chartData['series']];
    $burned_data['categories'] = $chartData['categories'];
    
    // Pass to view
    $data['burned_data'] = $burned_data;
    
    return view('admin/clients/view_client', $data);
}


}