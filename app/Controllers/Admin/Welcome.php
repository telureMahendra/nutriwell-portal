<?php

namespace App\Controllers\Admin;
use \CodeIgniter\View\Table;

class Welcome extends Controller
{
    public function index()
    {
        echo 'Welcome index method';
    }
    public function test($name)
    {
    	$table = new Table();

    	$subjects = [
    			['sub1', 'sub2','sub3'],
    			['sub11', 'sub21','sub31'],
    			['sub111', 'sub211','sub311'],
    	];
    	// $data['subjects'] = $subjects;

    	$template = [
		    'table_open' => '<table border="1 cellpadding="20" cellspacing="5" class="table">',
		];

		$table->setTemplate($template);
    	$data['subjects'] = $table->generate($subjects);
       echo view("Welcome/test.php",$data);
    }
    public function blog($name)
    {


    	$parser = \Config\Services::parser();

		$data = [
		    'page_title' => 'Your Page Title',
		    'city' => 'Your City',
		    'status' =>'true'
		];

		$parser->setData($data);
		$output = $parser->render("welcome/test.php");

		return $output;



    }
    public function address($city ,$state)
    {
        echo 'Welcome to address method '.$city;
    }

    public function _remap($method, $para1 = null, $para2 = null){
    	if (method_exists($this, $method)) {
    		return $this->$method($para1,$para2);
    	}else{
    		return $this->index();
    	}
    }
}
