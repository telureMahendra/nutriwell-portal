<?php

namespace App\Controllers\Apis;

use App\Models\UserRewardModel;
use App\Models\Client_login_model;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Rewards extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';
    protected $rewardModel;
    protected $clientLoginModel;

    public function __construct()
    {
        $this->rewardModel      = new UserRewardModel();
        $this->clientLoginModel = new Client_login_model();
    }

    // ----------------------------------------------------------------
    // CORS headers
    // ----------------------------------------------------------------
    private function headers()
    {
        $this->response->setHeader('Access-Control-Allow-Origin',  '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $this->response->setHeader('Content-Type', 'application/json');
    }

    // ----------------------------------------------------------------
    // Token authentication helper
    // ----------------------------------------------------------------
    private function authenticate($token)
    {
        if (empty($token)) return null;
        $check = $this->clientLoginModel->checkTocken($token);
        return !empty($check) ? (int)$check[0]->client_id : null;
    }

    // ----------------------------------------------------------------
    // POST: Get user rewards summary
    // Body: { "tocken": "..." }
    // ----------------------------------------------------------------
    public function summary()
    {
        $this->headers();
        $json     = $this->request->getJSON(true) ?? [];
        $token    = $json['tocken'] ?? $this->request->getVar('tocken');
        $clientId = $this->authenticate($token);

        if (!$clientId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Login']);
        }

        $totalPoints         = $this->rewardModel->getUserTotalPoints($clientId);
        $rewardsHistory      = $this->rewardModel->getUserRewards($clientId);
        $unusedAppointments  = $this->rewardModel->getUnusedAppointments($clientId);

        // 4 reward levels
        $levels = [500, 1000, 1500, 2000];
        $targetPoints = 0;
        $currentLevel = 0;
        
        foreach ($levels as $index => $levelPoints) {
            if ($totalPoints < $levelPoints) {
                $targetPoints = $levelPoints;
                $currentLevel = $index;
                break;
            }
        }
        
        if ($targetPoints === 0) {
            // Max level reached
            $pointsNeeded = 0;
            $currentLevel = count($levels);
            $targetPoints = 2000;
        } else {
            $pointsNeeded = $targetPoints - $totalPoints;
        }

        return $this->response->setJSON([
            'status' => true,
            'msg'    => 'Rewards summary fetched successfully',
            'data'   => [
                'total_reward_points'  => $totalPoints,
                'required_points_to_unlock_next_reward' => $pointsNeeded,
                'current_level'        => $currentLevel,
                'next_level_target'    => $targetPoints,
                'rewards_history'      => $rewardsHistory,
                'unused_appointments'  => $unusedAppointments,
                'unused_appointments_count' => count($unusedAppointments),
            ],
        ]);
    }
    // ----------------------------------------------------------------
    // POST: Get user rewards history
    // Body: { "tocken": "..." }
    // ----------------------------------------------------------------
    public function history()
    {
        $this->headers();
        $json     = $this->request->getJSON(true) ?? [];
        $token    = $json['tocken'] ?? $this->request->getVar('tocken');
        $clientId = $this->authenticate($token);

        if (!$clientId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Login']);
        }

        $rewardsHistory = $this->rewardModel->getUserRewards($clientId);

        return $this->response->setJSON([
            'status' => true,
            'msg'    => 'Rewards history fetched successfully',
            'data'   => $rewardsHistory,
        ]);
    }

    // ----------------------------------------------------------------
    // POST: Redeem a specific reward
    // Body: { "tocken": "...", "reward_id": 123 }
    // ----------------------------------------------------------------
    public function redeem()
    {
        $this->headers();
        $json     = $this->request->getJSON(true) ?? [];
        $token    = $json['tocken'] ?? $this->request->getVar('tocken');
        $rewardId = $json['reward_id'] ?? $this->request->getVar('reward_id');
        
        $clientId = $this->authenticate($token);

        if (!$clientId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Login']);
        }
        
        if (!$rewardId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Reward ID is required']);
        }

        $status = $this->rewardModel->redeemReward($clientId, $rewardId);

        if ($status === 'success') {
            return $this->response->setJSON([
                'status' => true,
                'msg'    => 'Reward redeemed successfully',
            ]);
        } elseif ($status === 'already_redeemed') {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Reward is already redeemed',
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Reward not found or invalid',
            ]);
        }
    }
}
