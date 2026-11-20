<?php
namespace App\Controllers\Admin;

use Config\Services;
use CodeIgniter\Controller;


class Index_con extends Controller
{
    public function index()
    {

        // Get the session service
        $session = session();
        
        
       // print_r($session->get());
        

        // Check if the 'userid' session variable is set
        if ($session->has('userid')) {
            $data = [
                'menutitle' => 'Dashboard',
                'pagetitle' => 'Dashboard'
            ];
        
            echo view('admin/admin_index', $data);
        } else {
            return view('admin/index');
        }
    }
}
