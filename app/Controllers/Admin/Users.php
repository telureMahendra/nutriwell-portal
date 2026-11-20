<?php

namespace App\Controllers\Admin;

use Config\Services;
use App\Models\Users_model;
use CodeIgniter\Controller;

class Users extends Controller
{
    protected $session;
    protected $users_model;
    protected $validation;

    public function __construct()
    {
        $this->validation = Services::validation();
        $this->users_model = new Users_model();
        $this->session = session();
        
        if (!is_admin_logged_in()) {
            redirect('');
        }
        
        $controller = class_basename(service('router')->controllerName());
        $methodName = class_basename(service('router')->methodName());
        hasAccess($controller, $methodName);
    }

    public function index()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Users',
                'pagetitle' => 'All Users',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li>Manage Users</li></ul>'
            ];

            // Get all users with pagination
            $perPage = PAGELIMIT ?? 10;
            $page = (int) ($this->request->getVar('page') ?? 1);
            $offset = ($page - 1) * $perPage;
            
            $total = $this->users_model->countAllUsers();
            $usersArray = $this->users_model->getAllUsers($perPage, $offset);
            
            $pager = service('pager');
            $pager->makeLinks($page, $perPage, $total);
            
            $data['usersList'] = $usersArray;
            $data['pager'] = $pager;
            $data['currentPage'] = $page;
            $data['perPage'] = $perPage;
            $data['total'] = $total;

            echo view('admin/users/users_list', $data);
        } else {
            return view('admin/admin/index');
        }
    }

    public function salesAgents()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Users',
                'pagetitle' => 'Sales Agents',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li><li><a href="' . base_url('admin/dashboard') . '">Dashboard</a><i class="fa fa-angle-right"></i></li><li>Sales Agents</li></ul>'
            ];

            // Get sales agents with pagination
            $perPage = PAGELIMIT ?? 10;
            $page = (int) ($this->request->getVar('page') ?? 1);
            $offset = ($page - 1) * $perPage;
            
            $total = $this->users_model->countSalesAgents();
            $salesAgents = $this->users_model->getSalesAgents($perPage, $offset);
            
            // Get active count
            $activeCount = $this->users_model->countActiveSalesAgents();
            $inactiveCount = $total - $activeCount;
            
            $pager = service('pager');
            $pager->makeLinks($page, $perPage, $total);
            
            // Pass all data to view
            $data['salesAgents'] = $salesAgents;
            $data['pager'] = $pager;
            $data['currentPage'] = $page;
            $data['perPage'] = $perPage;
            $data['total'] = $total;
            $data['activeCount'] = $activeCount;
            $data['inactiveCount'] = $inactiveCount;

            echo view('admin/users/sales_agents_list', $data);
        } else {
            return view('admin/admin/index');
        }
    }

    public function add()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Users',
                'pagetitle' => 'Add User',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li> <a href="' . base_url() . '/users" >Manage Users</a><i class="fa fa-angle-right"> </i> </li> <li>Add User </li></ul>'
            ];

            $stateArray = $this->users_model->getAllState();
            $allState = array('' => 'select state');
            foreach ($stateArray as $state) {
                $allState[$state->state_id] = $state->state_name;
            }
            $data['AllState'] = $allState;

            $submitValue = $this->request->getPost('submit');

            if (trim($submitValue) == '') {
                echo view('admin/users/users_add', $data);
            } elseif (trim($submitValue) == 'Add User') {

                $validationRules = [
                    'username' => ['label' => 'User Name', 'rules' => 'required|min_length[5]|max_length[50]|is_unique[users.username]'],
                    'pass' => ['label' => 'Password', 'rules' => 'required|min_length[4]'],
                    'first_name' => ['label' => 'First Name', 'rules' => 'required'],
                    'last_name' => ['label' => 'Last Name', 'rules' => 'required'],
                    'email' => ['label' => 'Email', 'rules' => 'required|valid_email'],
                    'phone_number' => ['label' => 'Phone Number', 'rules' => 'required'],
                    'user_role' => ['label' => 'User Role', 'rules' => 'required'],
                    'state_id' => ['label' => 'State Name', 'rules' => 'required'],
                    'address' => ['label' => 'Address', 'rules' => 'required'],
                ];

                $postData = $this->request->getPost();
                $this->validation->setRules($validationRules);

                if (!$this->validation->withRequest($this->request)->run()) {
                    $data['validation'] = $this->validation->getErrors();
                    return view('admin/users/users_add', $data);
                } else {
                    $insert_data = array();
                    $insert_data['username'] = trim($postData['username']);
                    $insert_data['pass'] = md5(trim($postData['pass']));
                    $insert_data['first_name'] = trim($postData['first_name']);
                    $insert_data['last_name'] = trim($postData['last_name']);
                    $insert_data['email'] = trim($postData['email']);
                    $insert_data['phone_number'] = trim($postData['phone_number']);
                    $insert_data['address'] = trim($postData['address']);
                    $insert_data['user_role'] = trim($postData['user_role']);
                    $insert_data['user_status'] = 'Active';
                    $insert_data['state_id'] = trim($postData['state_id']);

                    $id = $this->users_model->addUsers($insert_data);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'User added successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to add user', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('users');
                }
            }
        } else {
            return view('admin/index');
        }
    }

    public function View($uid)
    {
        if ($uid > 0) {
            $data = [
                'menutitle' => 'Users',
                'pagetitle' => 'View User',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'users">Manage Users</a><i class="fa fa-angle-right"></i></li> <li>View User </li></ul>',
            ];

            $stateArray = $this->users_model->getAllState();
            $allState = array('' => 'select state');
            foreach ($stateArray as $state) {
                $allState[$state->state_id] = $state->state_name;
            }
            $data['AllState'] = $allState;

            $userDetails = $this->users_model->getUserDetails($uid);
            
            // Check if user exists
            if (empty($userDetails)) {
                $arr_msg = array('suc_msg' => 'User not found!', 'msg-type' => 'danger');
                $this->session->set($arr_msg);
                return redirect()->to('users');
            }
            
            $data['userDetails'] = $userDetails;

            echo view('admin/users/view_user', $data);
        } else {
            return redirect()->to('users');
        }
    }

    public function edit($uid)
    {
        if ($uid > 0) {
            $data = [
                'menutitle' => 'Users',
                'pagetitle' => 'Edit User',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'users">Manage Users</a><i class="fa fa-angle-right"></i></li> <li>Edit User</li></ul>'
            ];

            $stateArray = $this->users_model->getAllState();
            $allState = array('' => 'select state');
            foreach ($stateArray as $state) {
                $allState[$state->state_id] = $state->state_name;
            }
            $data['AllState'] = $allState;

            // Get user details
            $userDetails = $this->users_model->getUserDetails($uid);
            
            // Check if user exists
            if (empty($userDetails)) {
                $arr_msg = array('suc_msg' => 'User not found!', 'msg-type' => 'danger');
                $this->session->set($arr_msg);
                return redirect()->to('users');
            }
            
            $data['userDetails'] = $userDetails;

            $submitValue = $this->request->getPost('submit');

            if (trim($submitValue) == '') {
                echo view('admin/users/users_edit', $data);
            } elseif (trim($submitValue) == 'Edit User') {
                $validationRules = [
                    'first_name' => ['label' => 'First Name', 'rules' => 'required'],
                    'last_name' => ['label' => 'Last Name', 'rules' => 'required'],
                    'email' => ['label' => 'Email', 'rules' => 'required|valid_email'],
                    'phone_number' => ['label' => 'Phone Number', 'rules' => 'required'],
                    'user_role' => ['label' => 'User Role', 'rules' => 'required'],
                    'state_id' => ['label' => 'State Name', 'rules' => 'required'],
                    'address' => ['label' => 'Address', 'rules' => 'required'],
                ];

                $postData = $this->request->getPost();
                $this->validation->setRules($validationRules);

                if (!$this->validation->withRequest($this->request)->run()) {
                    $data['validation'] = $this->validation->getErrors();
                    return view('admin/users/users_edit', $data);
                } else {
                    $insert_data = array();
                    $insert_data['username'] = trim($postData['username']);
                    $insert_data['first_name'] = trim($postData['first_name']);
                    $insert_data['last_name'] = trim($postData['last_name']);
                    $insert_data['email'] = trim($postData['email']);
                    $insert_data['phone_number'] = trim($postData['phone_number']);
                    $insert_data['user_role'] = trim($postData['user_role']);
                    $insert_data['address'] = trim($postData['address']);
                    $insert_data['user_status'] = 'Active';
                    $insert_data['state_id'] = trim($postData['state_id']);

                    if(trim($postData['pass']) != "") {
                        $insert_data['pass'] = md5(trim($postData['pass']));
                    }

                    $id = $this->users_model->updateUsers($insert_data, $uid);
                    if ($id > 0) {
                        $arr_msg = array('suc_msg' => 'User Updated successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to Updated user', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('users/');
                }
            }
        } else {
            return redirect()->to('users');
        }
    }

    public function close()
    {
        $uid = $this->request->getPost('uid');
        if ($uid > 0) {
            $userDetails = $this->users_model->getUserDetails($uid);
            if ($userDetails && strtolower($userDetails->user_role) == 'admin') {
                echo 0;
                return;
            }
            $insert_data = array();
            $insert_data['user_status'] = 'In-Active';
            $id = $this->users_model->updateUsers($insert_data, $uid);
            echo 1;
        } else {
            echo 0;
        }
    }

    public function activate()
    {
        $uid = $this->request->getPost('uid');
        if ($uid > 0) {
            $insert_data = array();
            $insert_data['user_status'] = 'Active';
            $id = $this->users_model->updateUsers($insert_data, $uid);
            echo 1;
        } else {
            echo 0;
        }
    }

    public function role($userID)
    {
        if ($this->session->has('userid')) {
            if ($userID > 0) {
                $data = [
                    'menutitle' => 'Users',
                    'pagetitle' => 'User Role',
                    'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li><a href="' . base_url() . 'users">Manage Users</a><i class="fa fa-angle-right"></i></li> <li>Edit User Role</li></ul>',
                    "userID" => $userID
                ];
                $userDetails = $this->users_model->getUserDetails($userID);
                $data['userDetails'] = $userDetails;
                $submitValue = $this->request->getPost('submit');
                if (trim($submitValue) == '') {
                    echo view('admin/users/users_role', $data);
                } elseif (trim($submitValue) == 'Submit') {
                    $postData = $this->request->getPost();
                    $status = $this->users_model->PostUserRole($postData, $userID);
                    if ($status) {
                        $arr_msg = array('suc_msg' => 'User role updated successfully!!!', 'msg-type' => 'success');
                    } else {
                        $arr_msg = array('suc_msg' => 'Failed to update user role!!!', 'msg-type' => 'danger');
                    }
                    $this->session->set($arr_msg);
                    return redirect()->to('users/role/' . $userID);
                }
            } else {
                return redirect()->to('users');
            }
        } else {
            return view('admin/index');
        }
    }
}