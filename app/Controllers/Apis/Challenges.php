<?php

namespace App\Controllers\Apis;

use App\Models\ChallengeModel;
use App\Models\UserChallengeModel;
use App\Models\ChallengeDailyLogModel;
use App\Models\UserRewardModel;
use App\Models\Client_login_model;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class Challenges extends ResourceController
{
    use ResponseTrait;

    protected $format             = 'json';
    protected $challengeModel;
    protected $userChallengeModel;
    protected $dailyLogModel;
    protected $rewardModel;
    protected $clientLoginModel;

    public function __construct()
    {
        $this->challengeModel     = new ChallengeModel();
        $this->userChallengeModel = new UserChallengeModel();
        $this->dailyLogModel      = new ChallengeDailyLogModel();
        $this->rewardModel        = new UserRewardModel();
        $this->clientLoginModel   = new Client_login_model();
    }

    // ----------------------------------------------------------------
    // CORS headers (consistent with other API controllers)
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
    // GET: List all active challenges with the user's join status
    // POST body: { "tocken": "..." }
    // ----------------------------------------------------------------
    public function list()
    {
        $this->headers();
        $json     = $this->request->getJSON(true) ?? [];
        $token    = $json['tocken'] ?? $this->request->getVar('tocken');
        $clientId = $this->authenticate($token);

        if (!$clientId) {
            return $this->response->setJSON([
                'status' => false, 'msg' => 'User Not Login'
            ]);
        }

        $challenges = $this->challengeModel->getChallengesForUser($clientId);

        return $this->response->setJSON([
            'status' => true,
            'msg'    => 'Challenges fetched successfully',
            'data'   => $challenges,
        ]);
    }

    // ----------------------------------------------------------------
    // POST: Join a challenge
    // Body: { "tocken": "...", "challenge_id": 1 }
    // ----------------------------------------------------------------
    public function join()
    {
        $this->headers();
        $json         = $this->request->getJSON(true) ?? [];
        $token        = $json['tocken'] ?? '';
        $challengeId  = $json['challenge_id'] ?? null;
        $clientId     = $this->authenticate($token);

        if (!$clientId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Login']);
        }
        if (!$challengeId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'challenge_id is required']);
        }

        // Check challenge exists and is active
        $challenge = $this->challengeModel->find($challengeId);
        if (!$challenge || $challenge['status'] !== 'Active') {
            return $this->response->setJSON(['status' => false, 'msg' => 'Challenge not found or inactive']);
        }

        // Already joined?
        $existing = $this->userChallengeModel->getUserChallenge($clientId, $challengeId);
        if ($existing) {
            return $this->response->setJSON(['status' => false, 'msg' => 'You have already joined this challenge']);
        }

        $insertId = $this->userChallengeModel->insert([
            'user_id'              => $clientId,
            'challenge_id'         => $challengeId,
            'current_streak'       => 0,
            'total_days_completed' => 0,
            'last_checkin_date'    => null,
            'status'               => 'Joined',
            'started_at'           => date('Y-m-d H:i:s'),
        ]);

        if ($insertId) {
            return $this->response->setJSON(['status' => true, 'msg' => 'Challenge joined successfully!']);
        }

        return $this->response->setJSON(['status' => false, 'msg' => 'Failed to join challenge']);
    }

    // ----------------------------------------------------------------
    // POST: Get challenges joined by the current user
    // Body: { "tocken": "..." }
    // ----------------------------------------------------------------
    public function my_challenges()
    {
        $this->headers();
        $json     = $this->request->getJSON(true) ?? [];
        $token    = $json['tocken'] ?? $this->request->getVar('tocken');
        $clientId = $this->authenticate($token);

        if (!$clientId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Login']);
        }

        $challenges = $this->userChallengeModel->getUserChallenges($clientId);

        return $this->response->setJSON([
            'status' => true,
            'msg'    => 'Your challenges fetched successfully',
            'data'   => $challenges,
        ]);
    }

    // ----------------------------------------------------------------
    // POST: Manual daily check-in — user taps "Mark as Done"
    // Body: { "tocken": "...", "challenge_id": 1, "note": "optional" }
    //
    // Logic:
    //  1. Validate token + challenge_id
    //  2. Confirm user has joined this challenge
    //  3. Reject if already checked in today
    //  4. Compute streak:
    //     - last_checkin_date == yesterday  → streak + 1
    //     - anything else (missed day)      → reset streak to 1
    //  5. Insert challenge_daily_logs row
    //  6. Update user_challenges (streak, total_days_completed, last_checkin_date)
    //  7. If current_streak >= target_days → mark Completed, credit reward points
    //  8. Return updated state
    // ----------------------------------------------------------------
    public function daily_checkin()
    {
        $this->headers();
        $json        = $this->request->getJSON(true) ?? [];
        $token       = $json['tocken']      ?? '';
        $challengeId = $json['challenge_id'] ?? null;
        $note        = $json['note']         ?? null;
        $clientId    = $this->authenticate($token);

        if (!$clientId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Login']);
        }
        if (!$challengeId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'challenge_id is required']);
        }

        // Load challenge
        $challenge = $this->challengeModel->find($challengeId);
        if (!$challenge || $challenge['status'] !== 'Active') {
            return $this->response->setJSON(['status' => false, 'msg' => 'Challenge not found or inactive']);
        }

        // Load user's join record
        $userChallenge = $this->userChallengeModel->getUserChallenge($clientId, $challengeId);
        if (!$userChallenge) {
            return $this->response->setJSON(['status' => false, 'msg' => 'You have not joined this challenge yet']);
        }
        if ($userChallenge['status'] === 'Completed') {
            return $this->response->setJSON(['status' => false, 'msg' => 'You have already completed this challenge!']);
        }

        $today = date('Y-m-d');

        // Already checked in today?
        $todayLog = $this->dailyLogModel->getTodayLog($clientId, $challengeId, $today);
        if ($todayLog) {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'You have already checked in today for this challenge',
            ]);
        }

        // ----- Streak calculation -----
        $lastDate      = $userChallenge['last_checkin_date'];
        $yesterday     = date('Y-m-d', strtotime('-1 day'));
        $currentStreak = (int)$userChallenge['current_streak'];

        if ($lastDate === $yesterday) {
            // Consecutive day — extend streak
            $newStreak = $currentStreak + 1;
        } else {
            // Missed at least one day — reset streak to 1 (today is day 1 of new streak)
            $newStreak = 1;
        }

        $totalDaysCompleted = (int)$userChallenge['total_days_completed'] + 1;

        // Insert daily log
        $this->dailyLogModel->insert([
            'user_id'      => $clientId,
            'challenge_id' => $challengeId,
            'log_date'     => $today,
            'is_completed' => 1,
            'note'         => $note,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        // Check completion
        $targetDays  = (int)$challenge['target_days'];
        $isCompleted = $newStreak >= $targetDays;

        $updateData = [
            'current_streak'       => $newStreak,
            'total_days_completed' => $totalDaysCompleted,
            'last_checkin_date'    => $today,
        ];

        if ($isCompleted) {
            $updateData['status']       = 'Completed';
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        }

        $this->userChallengeModel->update($userChallenge['id'], $updateData);

        // Credit reward points if completed
        $rewardMessage = null;
        if ($isCompleted && (int)$challenge['points'] > 0) {
            $this->rewardModel->creditPoints(
                $clientId,
                $challengeId,
                (int)$challenge['points'],
                $challenge['reward_type']
            );
            $rewardMessage = 'Congratulations! You earned ' . $challenge['points'] . ' reward points!';
        }

        $progressPercent = $targetDays > 0 ? round(($newStreak / $targetDays) * 100, 1) : 0;

        return $this->response->setJSON([
            'status'           => true,
            'msg'              => $isCompleted
                                    ? '🏆 Challenge completed! ' . ($rewardMessage ?? '')
                                    : 'Check-in recorded! Day ' . $newStreak . ' of ' . $targetDays,
            'is_completed'     => $isCompleted,
            'current_streak'   => $newStreak,
            'target_days'      => $targetDays,
            'progress_percent' => $progressPercent,
            'reward_message'   => $rewardMessage,
        ]);
    }

    // ----------------------------------------------------------------
    // POST: Get single challenge detail with user's progress
    // Body: { "tocken": "...", "challenge_id": 1 }
    // ----------------------------------------------------------------
    public function detail()
    {
        $this->headers();
        $json        = $this->request->getJSON(true) ?? [];
        $token       = $json['tocken']      ?? $this->request->getVar('tocken');
        $challengeId = $json['challenge_id'] ?? $this->request->getVar('challenge_id');
        $clientId    = $this->authenticate($token);

        if (!$clientId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Login']);
        }
        if (!$challengeId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'challenge_id is required']);
        }

        $challenge = $this->challengeModel->find($challengeId);
        if (!$challenge) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Challenge not found']);
        }

        $userChallenge    = $this->userChallengeModel->getUserChallenge($clientId, $challengeId);
        $dailyLogs        = $userChallenge
                            ? $this->dailyLogModel->getLogsForChallenge($clientId, $challengeId)
                            : [];
        $today            = date('Y-m-d');
        $checkedInToday   = $userChallenge
                            ? (bool)$this->dailyLogModel->getTodayLog($clientId, $challengeId, $today)
                            : false;

        $progressPercent = 0;
        if ($userChallenge && (int)$challenge['target_days'] > 0) {
            $progressPercent = round(
                ((int)$userChallenge['current_streak'] / (int)$challenge['target_days']) * 100, 1
            );
        }

        return $this->response->setJSON([
            'status' => true,
            'msg'    => 'Challenge detail fetched successfully',
            'data'   => [
                'challenge'        => $challenge,
                'user_challenge'   => $userChallenge,
                'daily_logs'       => $dailyLogs,
                'checked_in_today' => $checkedInToday,
                'progress_percent' => $progressPercent,
                'is_joined'        => !empty($userChallenge),
            ],
        ]);
    }

    // ----------------------------------------------------------------
    // POST: User stats for profile screen
    // Body: { "tocken": "..." }
    // ----------------------------------------------------------------
    public function user_stats()
    {
        $this->headers();
        $json     = $this->request->getJSON(true) ?? [];
        $token    = $json['tocken'] ?? $this->request->getVar('tocken');
        $clientId = $this->authenticate($token);

        if (!$clientId) {
            return $this->response->setJSON(['status' => false, 'msg' => 'User Not Login']);
        }

        $summary      = $this->userChallengeModel->getUserChallengeSummary($clientId);
        $totalPoints  = $this->rewardModel->getUserTotalPoints($clientId);
        $challenges   = $this->userChallengeModel->getClientChallengeHistory($clientId);

        return $this->response->setJSON([
            'status' => true,
            'msg'    => 'User challenge stats fetched successfully',
            'data'   => [
                'total_joined'    => (int)($summary->total_joined    ?? 0),
                'total_completed' => (int)($summary->total_completed ?? 0),
                'total_active'    => (int)($summary->total_active    ?? 0),
                'total_failed'    => (int)($summary->total_failed    ?? 0),
                'total_reward_points' => $totalPoints,
                'challenges'      => $challenges,
            ],
        ]);
    }
}
