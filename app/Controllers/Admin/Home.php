<?php

namespace App\Controllers\Admin;

class Home extends Controller
{
    public function index()
    {
        echo view('admin/layouts/header');
        echo view('admin/welcome_message');
        echo view('admin/layouts/footer');

    }
}
