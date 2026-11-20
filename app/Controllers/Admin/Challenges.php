<?php

namespace App\Controllers\Admin;

use App\Models\ChallengeModel;
use App\Models\UserChallengeModel;
use CodeIgniter\Controller;
use Config\Services;

class Challenges extends Controller
{
    protected $session;
    protected $challengeModel;
    protected $userChallengeModel;

    public function __construct()
    {
        $this->session            = session();
        $this->challengeModel     = new ChallengeModel();
        $this->userChallengeModel = new UserChallengeModel();

        if (!is_admin_logged_in()) {
            return redirect()->to('/');
        }
    }

    // ----------------------------------------------------------------
    // List all challenges
    // ----------------------------------------------------------------
    public function index()
    {
        $data = [
            'menutitle'  => 'Challenges',
            'pagetitle'  => 'Manage Challenges',
            'breadcrumb' => '<ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a>
                <i class="fa fa-angle-right"></i></li>
                <li>Challenges</li></ul>',
            'challenges' => $this->challengeModel->getChallengesWithCount(),
        ];

        return view('admin/challenges/list', $data);
    }

    // ----------------------------------------------------------------
    // Show create form
    // ----------------------------------------------------------------
    public function create()
    {
        $data = [
            'menutitle'  => 'Challenges',
            'pagetitle'  => 'Create Challenge',
            'breadcrumb' => '<ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a>
                <i class="fa fa-angle-right"></i></li>
                <li><a href="' . base_url('challenges') . '">Challenges</a>
                <i class="fa fa-angle-right"></i></li>
                <li>Create Challenge</li></ul>',
        ];

        return view('admin/challenges/create', $data);
    }

    // ----------------------------------------------------------------
    // Store a new challenge
    // ----------------------------------------------------------------
    public function store()
    {
        $rules = [
            'title'         => 'required|min_length[3]|max_length[255]',
            'activity_type' => 'required|in_list[water,steps,meditation,sleep,workout,yoga,custom]',
            'target_value'  => 'required|numeric|greater_than[0]',
            'target_days'   => 'required|numeric|greater_than[0]',
            'points'        => 'required|numeric|greater_than_equal_to[0]',
            'reward_type'   => 'required|in_list[points,appointment_unlock,both]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', Services::validation()->getErrors());
        }

        $imageUrl = null;
        $file     = $this->request->getFile('image_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $name     = $file->getRandomName();
            $file->move(FCPATH . 'uploads/challenges', $name);
            $imageUrl = 'uploads/challenges/' . $name;
        }

        $saveData = [
            'title'         => $this->request->getPost('title'),
            'description'   => $this->request->getPost('description'),
            'image_url'     => $imageUrl,
            'activity_type' => $this->request->getPost('activity_type'),
            'target_value'  => $this->request->getPost('target_value'),
            'target_unit'   => $this->request->getPost('target_unit'),
            'target_days'   => $this->request->getPost('target_days'),
            'points'        => $this->request->getPost('points'),
            'reward_type'   => $this->request->getPost('reward_type'),
            'start_date'    => $this->request->getPost('start_date') ?: null,
            'end_date'      => $this->request->getPost('end_date')   ?: null,
            'status'        => 'Active',
            'created_by'    => $this->session->get('userid'),
            'creator_type'  => 'Admin',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        if ($this->challengeModel->insert($saveData)) {
            return redirect()->to('challenges')
                ->with('success', 'Challenge created successfully!');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Failed to create challenge. Please try again.');
    }

    // ----------------------------------------------------------------
    // Show edit form
    // ----------------------------------------------------------------
    public function edit($id)
    {
        $challenge = $this->challengeModel->find($id);
        if (!$challenge) {
            return redirect()->to('challenges')->with('error', 'Challenge not found.');
        }

        $data = [
            'menutitle'  => 'Challenges',
            'pagetitle'  => 'Edit Challenge',
            'breadcrumb' => '<ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a>
                <i class="fa fa-angle-right"></i></li>
                <li><a href="' . base_url('challenges') . '">Challenges</a>
                <i class="fa fa-angle-right"></i></li>
                <li>Edit Challenge</li></ul>',
            'challenge'  => $challenge,
        ];

        return view('admin/challenges/edit', $data);
    }

    // ----------------------------------------------------------------
    // Update a challenge
    // ----------------------------------------------------------------
    public function update($id)
    {
        $challenge = $this->challengeModel->find($id);
        if (!$challenge) {
            return redirect()->to('challenges')->with('error', 'Challenge not found.');
        }

        $rules = [
            'title'         => 'required|min_length[3]|max_length[255]',
            'activity_type' => 'required|in_list[water,steps,meditation,sleep,workout,yoga,custom]',
            'target_value'  => 'required|numeric|greater_than[0]',
            'target_days'   => 'required|numeric|greater_than[0]',
            'points'        => 'required|numeric|greater_than_equal_to[0]',
            'reward_type'   => 'required|in_list[points,appointment_unlock,both]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', Services::validation()->getErrors());
        }

        $imageUrl = $challenge['image_url'];
        $file     = $this->request->getFile('image_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Remove old image
            if ($imageUrl && file_exists(FCPATH . $imageUrl)) {
                @unlink(FCPATH . $imageUrl);
            }
            $name     = $file->getRandomName();
            $file->move(FCPATH . 'uploads/challenges', $name);
            $imageUrl = 'uploads/challenges/' . $name;
        }

        $updateData = [
            'title'         => $this->request->getPost('title'),
            'description'   => $this->request->getPost('description'),
            'image_url'     => $imageUrl,
            'activity_type' => $this->request->getPost('activity_type'),
            'target_value'  => $this->request->getPost('target_value'),
            'target_unit'   => $this->request->getPost('target_unit'),
            'target_days'   => $this->request->getPost('target_days'),
            'points'        => $this->request->getPost('points'),
            'reward_type'   => $this->request->getPost('reward_type'),
            'start_date'    => $this->request->getPost('start_date') ?: null,
            'end_date'      => $this->request->getPost('end_date')   ?: null,
            'status'        => $this->request->getPost('status') ?: 'Active',
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        if ($this->challengeModel->update($id, $updateData)) {
            return redirect()->to('challenges')
                ->with('success', 'Challenge updated successfully!');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Failed to update challenge.');
    }

    // ----------------------------------------------------------------
    // View a single challenge with participant list
    // ----------------------------------------------------------------
    public function view($id)
    {
        $challenge = $this->challengeModel->find($id);
        if (!$challenge) {
            return redirect()->to('challenges')->with('error', 'Challenge not found.');
        }

        $data = [
            'menutitle'    => 'Challenges',
            'pagetitle'    => 'Challenge Details',
            'breadcrumb'   => '<ul class="page-breadcrumb">
                <li><i class="fa fa-home"></i><a href="' . base_url() . '">Home</a>
                <i class="fa fa-angle-right"></i></li>
                <li><a href="' . base_url('challenges') . '">Challenges</a>
                <i class="fa fa-angle-right"></i></li>
                <li>Challenge Details</li></ul>',
            'challenge'    => $challenge,
            'participants' => $this->userChallengeModel->getActiveUsersForChallenge($id),
        ];

        return view('admin/challenges/view', $data);
    }

    // ----------------------------------------------------------------
    // Soft delete
    // ----------------------------------------------------------------
    public function delete($id)
    {
        if ($this->challengeModel->update($id, ['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')])) {
            return redirect()->to('challenges')->with('success', 'Challenge deleted successfully!');
        }
        return redirect()->to('challenges')->with('error', 'Failed to delete challenge.');
    }

    // ----------------------------------------------------------------
    // Toggle Active / Inactive status (AJAX)
    // ----------------------------------------------------------------
    public function toggle_status()
    {
        $id     = $this->request->getPost('id');
        $status = $this->request->getPost('status');

        if (!in_array($status, ['Active', 'Inactive'])) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Invalid status']);
        }

        $this->challengeModel->update($id, ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['status' => true, 'msg' => 'Status updated']);
    }
}
