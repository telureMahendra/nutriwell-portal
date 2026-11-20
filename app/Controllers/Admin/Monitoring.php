<?php

namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;

class Monitoring extends Controller
{
    protected $session;

    public function __construct()
    {
        $this->session = session();
        
        if (!is_admin_logged_in()) {
            redirect('');
        }
    }

    public function index()
    {
        if ($this->session->has('userid')) {
            $data = [
                'menutitle' => 'Monitoring',
                'pagetitle' => 'Application Marketing & Source Statistics',
                'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a><i class="fa fa-angle-right"></i></li> <li>Monitoring</li></ul>'
            ];

            $db = \Config\Database::connect();
            $query = $db->table('client_profile')
                        ->select('source_of_info, COUNT(*) as count')
                        ->where('source_of_info IS NOT NULL', null, false)
                        ->where('source_of_info !=', '')
                        ->groupBy('source_of_info')
                        ->orderBy('count', 'DESC')
                        ->get();
            
            $data['stats'] = $query->getResult();

            echo view('admin/monitoring/index', $data);
        } else {
            return view('admin/admin/index');
        }
    }
}
