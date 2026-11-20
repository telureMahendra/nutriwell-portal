<?php

namespace App\Models;

use CodeIgniter\Model;

class UserChallengeModel extends Model
{
    protected $table      = 'user_challenges';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'challenge_id', 'current_streak',
        'total_days_completed', 'last_checkin_date',
        'status', 'started_at', 'completed_at'
    ];

    /** All challenges joined by a user, with challenge metadata */
    public function getUserChallenges($userId)
    {
        return $this->db->table('user_challenges AS UC')
            ->select('UC.*, C.title, C.description, C.activity_type, C.image_url,
                      C.target_value, C.target_unit, C.target_days, C.points,
                      C.reward_type,
                      CASE WHEN C.target_days > 0
                           THEN ROUND((UC.current_streak / C.target_days)*100, 1)
                           ELSE 0 END AS progress_percent')
            ->join('challenges AS C', 'C.id = UC.challenge_id')
            ->where('UC.user_id', $userId)
            ->orderBy('UC.started_at', 'DESC')
            ->get()->getResultArray();
    }

    /** Summary stats for a user's profile */
    public function getUserChallengeSummary($userId)
    {
        $row = $this->db->table('user_challenges')
            ->select("COUNT(*) AS total_joined,
                      SUM(CASE WHEN status = 'Completed'  THEN 1 ELSE 0 END) AS total_completed,
                      SUM(CASE WHEN status = 'Joined'     THEN 1 ELSE 0 END) AS total_active,
                      SUM(CASE WHEN status = 'Failed'     THEN 1 ELSE 0 END) AS total_failed,
                      SUM(CASE WHEN status = 'Abandoned'  THEN 1 ELSE 0 END) AS total_abandoned")
            ->where('user_id', $userId)
            ->get()->getRow();

        return $row ?? (object)[
            'total_joined'    => 0, 'total_completed' => 0,
            'total_active'    => 0, 'total_failed'    => 0, 'total_abandoned' => 0
        ];
    }

    /** All enrolled users for a challenge (admin/mentor tracking view) */
    public function getActiveUsersForChallenge($challengeId)
    {
        return $this->db->table('user_challenges AS UC')
            ->select('UC.*, CP.client_name, CP.client_email,
                      CP.client_mobile_number, CP.client_profile_pic,
                      C.target_days,
                      CASE WHEN C.target_days > 0
                           THEN ROUND((UC.current_streak / C.target_days)*100, 1)
                           ELSE 0 END AS progress_percent')
            ->join('client_profile AS CP', 'CP.client_id = UC.user_id')
            ->join('challenges AS C', 'C.id = UC.challenge_id')
            ->where('UC.challenge_id', $challengeId)
            ->orderBy('UC.current_streak', 'DESC')
            ->get()->getResultArray();
    }

    /** Participant count for a challenge */
    public function getChallengeParticipantCount($challengeId)
    {
        return $this->where('challenge_id', $challengeId)->countAllResults();
    }

    /** Existing join record for one user+challenge */
    public function getUserChallenge($userId, $challengeId)
    {
        return $this->where('user_id', $userId)
                    ->where('challenge_id', $challengeId)
                    ->first();
    }

    /** Profile view: challenge list with status for a specific client */
    public function getClientChallengeHistory($userId)
    {
        return $this->db->table('user_challenges AS UC')
            ->select('UC.*, C.title, C.description, C.activity_type,
                      C.target_days, C.points, C.image_url,
                      CASE WHEN C.target_days > 0
                           THEN ROUND((UC.total_days_completed / C.target_days)*100, 1)
                           ELSE 0 END AS completion_percent')
            ->join('challenges AS C', 'C.id = UC.challenge_id')
            ->where('UC.user_id', $userId)
            ->orderBy('UC.started_at', 'DESC')
            ->get()->getResultArray();
    }
}
