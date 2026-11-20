<?php


namespace App\Controllers\Mentor;

use Config\Services;
use CodeIgniter\Controller;


class Index_con extends Controller
{
    protected $session;

    public function __construct() {
        $this->validation = Services::validation();
        $this->session = session();   
    }
    public function index() {
        if ($this->session->has('doctor_id')) {
            $data = [
                'menutitle' => 'Login',
                'pagetitle' => 'Login'
            ];        

            
            echo view('mentor/mentor_index', $data);
        } else {
            return view('mentor/index');
        }
    }
    public function check_login()
    {

        $session = session();
        
        // Load the necessary services and helpers
        $validation = \Config\Services::validation();
        $this->mentor_model = model('App\Models\Mentor_model', false);


        // Validate form input
        $validation->setRules([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return view('index');
        } else {
        // echo "<pre>";print_r($this->request->getVar());die();

            $username = $this->request->getVar('username');
            $password = $this->request->getVar('password');
            $return = $this->mentor_model->check_login($username, $password);
        
        // echo "<pre>";print_r($return);die();

            if ($return || $return === true) {
                // Handle successful login
                $ip = $this->request->getIPAddress();
                $user_agent = $this->request->getUserAgent();
                $doctor_id = session()->get('doctor_id');
                $loginTime = date('Y-m-d H:i:s');
                $user_logs = [
                    'doctor_id' => $doctor_id,
                    'ip_address' => $ip,
                    'login_time' => $loginTime,
                    'user_agent' => $user_agent->getAgentString(),
                ];
                $this->mentor_model->add_doctor_log($user_logs);
                return redirect()->to(base_url('mentor/appointment'));
            } else {
                $data['error_text'] = 'Username or Password is incorrect!';
                return view('mentor/index', $data);
            }
        }
    }     
}
