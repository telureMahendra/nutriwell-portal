<?php
namespace App\Controllers\Mentor;
use Config\Services;
use CodeIgniter\Controller;

class Logout extends Controller {
	protected $session;
	protected $validation;

	public function __construct() {
		$this->validation = Services::validation();
		$this->common_model = model('App\Models\Common_model', false);
		$this->session = session();
	}

	public function index()
	{
		$this->session->destroy();
		$this->session->close();
		return redirect()->to(base_url('mentor'));
	}
}