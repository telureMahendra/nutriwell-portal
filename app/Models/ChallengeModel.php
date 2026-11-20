<?php

namespace App\Models;

use CodeIgniter\Model;

class ChallengeModel extends Model
{
    protected $table      = 'challenges';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title', 'description', 'image_url', 'activity_type',
        'target_value', 'target_unit', 'target_days', 'points',
        'reward_type', 'start_date', 'end_date', 'status',
        'created_by', 'creator_type', 'created_at', 'updated_at'
    ];

    /** All active challenges */
    public function getActiveChallenges()
    {
        return $this->where('status', 'Active')->orderBy('id', 'DESC')->findAll();
    }

    /** Active challenges enriched with participant count */
    public function getChallengesWithCount()
    {
        return $this->db->table('challenges AS C')
            ->select('C.*, COALESCE(UC.participant_count, 0) AS participant_count,
                      COALESCE(UC.completed_count, 0) AS completed_count')
            ->join(
                "(SELECT challenge_id,
                         COUNT(*) AS participant_count,
                         SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_count
                  FROM user_challenges GROUP BY challenge_id) AS UC",
                'UC.challenge_id = C.id', 'left'
            )
            ->where('C.status !=', 'Deleted')
            ->orderBy('C.id', 'DESC')
            ->get()->getResultArray();
    }

    /** Active challenges with the current user's join status for the Flutter API */
    public function getChallengesForUser($userId)
    {
        $db = \Config\Database::connect();

        return $db->table('challenges AS C')
            ->select("C.id, C.title, C.description, C.image_url, C.activity_type,
                      C.target_value, C.target_unit, C.target_days, C.points,
                      C.reward_type, C.start_date, C.end_date, C.status,
                      C.created_by, C.creator_type, C.created_at, C.updated_at,
                      COALESCE(UC.status, 'Not Joined') AS user_status,
                      COALESCE(UC.current_streak, 0)    AS current_streak,
                      COALESCE(UC.total_days_completed, 0) AS total_days_completed,
                      COALESCE(UC.started_at, NULL) AS started_at,
                      COALESCE(UC.completed_at, NULL) AS completed_at,
                      (CASE WHEN UC.id IS NOT NULL THEN 'Joined' ELSE 'Not Joined' END) AS joined_status,
                      (SELECT COUNT(*) FROM challenge_daily_logs WHERE challenge_id = C.id AND user_id = $userId AND log_date = CURDATE() AND is_completed = 1) AS checked_in_today")
            ->join("user_challenges AS UC",
                   "UC.challenge_id = C.id AND UC.user_id = $userId", 'left')
            ->where('C.status', 'Active')
            ->orderBy('C.id', 'DESC')
            ->get()->getResultArray();
    }

    /** Single challenge detail */
    public function getChallengeDetails($id)
    {
        return $this->find($id);
    }
}
