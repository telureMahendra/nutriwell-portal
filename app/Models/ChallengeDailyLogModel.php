<?php

namespace App\Models;

use CodeIgniter\Model;

class ChallengeDailyLogModel extends Model
{
    protected $table      = 'challenge_daily_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'challenge_id', 'log_date', 'is_completed', 'note', 'created_at'
    ];

    /** Check if a user already checked in today for a challenge */
    public function getTodayLog($userId, $challengeId, $date = null)
    {
        $date = $date ?? date('Y-m-d');
        return $this->where('user_id', $userId)
                    ->where('challenge_id', $challengeId)
                    ->where('log_date', $date)
                    ->first();
    }

    /** All daily logs for a user's challenge (for timeline view) */
    public function getLogsForChallenge($userId, $challengeId)
    {
        return $this->where('user_id', $userId)
                    ->where('challenge_id', $challengeId)
                    ->orderBy('log_date', 'DESC')
                    ->findAll();
    }

    /** Count completed days for a user's challenge */
    public function getCompletedCount($userId, $challengeId)
    {
        return $this->where('user_id', $userId)
                    ->where('challenge_id', $challengeId)
                    ->where('is_completed', 1)
                    ->countAllResults();
    }
}
