<?php

namespace App\Controllers\Admin;
use CodeIgniter\Controller;

use App\Models\Login_mod;

class Login_con extends Controller
{
    protected $Login_mod;

    public function __construct()
    {
        $this->session = session();
        
        $this->login_mod = new Login_mod(); // Initialize the model instance
        // Load other necessary resources like helpers or libraries here
    }
    public function index()
    {
        $session = session();
        
        if (!$session->userid) {
            return view('index');
        } else {
            return view('index_con');
        }
    }

    public function check_login()
    {
        $session = session();
        
        // Load the necessary services and helpers
        $validation = \Config\Services::validation();

        // Validate form input
        $validation->setRules([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return view('admin\index');
        } else {
        // echo "<pre>";print_r($this->request->getVar());die();

            $username = $this->request->getVar('username');
            $password = $this->request->getVar('password');
            $return = $this->login_mod->check_login($username, $password);
        
        // echo "<pre>";print_r($return);die();

            if ($return || $return === true) {
                // Handle successful login
                $ip = $this->request->getIPAddress();
                $user_agent = $this->request->getUserAgent();
                $uid = session()->get('userid');
                $loginTime = date('Y-m-d H:i:s');
                $user_logs = [
                    'uid' => $uid,
                    'ip_address' => $ip,
                    'login_time' => $loginTime,
                    'user_agent' => $user_agent->getAgentString(),
                ];
                $this->login_mod->add_user_log($user_logs);
                return redirect()->to(base_url('index_con'));
            } else {
                $data['error_text'] = 'Username or Password is incorrect!';
                return view('admin/index', $data);
            }
        }
    }

}
