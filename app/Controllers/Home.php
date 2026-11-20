<?php

namespace App\Controllers;

use Config\Services;
use CodeIgniter\Controller;

class Home extends Controller
{

	public function __construct()
	{
	}
	public function index()
	{
		$data = [
			'menutitle' => 'Access Denied',
			'pagetitle' => 'Access Denied',
			'breadcrumb' => '<ul class="page-breadcrumb"><li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a></ul>'
		];
		return view('home', $data);
	}

	public function deleteAccount()
	{
		return view('delete_account');
	}

	public function invite()
	{
		return view('invite');
	}
}
