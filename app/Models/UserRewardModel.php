<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRewardModel extends Model
{
    protected $table      = 'user_rewards';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'challenge_id', 'reward_type', 'points_value', 'is_used', 'created_at'
    ];

    /** Credit reward points for a completed challenge */
    public function creditPoints($userId, $challengeId, $points, $rewardType = 'points')
    {
        $insertId = $this->insert([
            'user_id'      => $userId,
            'challenge_id' => $challengeId,
            'reward_type'  => $rewardType,
            'points_value' => $points,
            'is_used'      => 0,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        if ($insertId && $points > 0) {
            // Update cumulative points on client_profile
            $this->db->query(
                "UPDATE client_profile
                 SET total_reward_points = COALESCE(total_reward_points, 0) + ?
                 WHERE client_id = ?",
                [$points, $userId]
            );
        }

        return $insertId;
    }

    /** Get total reward points for a user */
    public function getUserTotalPoints($userId)
    {
        $row = $this->db->table('client_profile')
            ->select('COALESCE(total_reward_points, 0) AS total_points')
            ->where('client_id', $userId)
            ->get()->getRow();
        return $row ? (int)$row->total_points : 0;
    }

    /** All rewards earned by a user */
    public function getUserRewards($userId)
    {
        return $this->db->table('user_rewards AS UR')
            ->select('UR.*, C.title AS challenge_title')
            ->join('challenges AS C', 'C.id = UR.challenge_id', 'left')
            ->where('UR.user_id', $userId)
            ->orderBy('UR.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /** Unused appointment unlock rewards for a user */
    public function getUnusedAppointments($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('reward_type', 'appointment_unlock')
                    ->where('is_used', 0)
                    ->findAll();
    }

    /** Redeem a reward (mark as used, and deduct points if it's a points reward) */
    public function redeemReward($userId, $rewardId)
    {
        $reward = $this->where('id', $rewardId)->where('user_id', $userId)->first();
        
        if (!$reward) {
            return 'not_found';
        }

        if ($reward['is_used'] == 1) {
            return 'already_redeemed';
        }

        // Mark as used
        $this->update($rewardId, ['is_used' => 1, 'updated_at' => date('Y-m-d H:i:s')]);

        // If it was a points reward, deduct from the user's total points
        if ($reward['reward_type'] === 'points' && $reward['points_value'] > 0) {
            $this->db->query(
                "UPDATE client_profile
                 SET total_reward_points = GREATEST(0, COALESCE(total_reward_points, 0) - ?)
                 WHERE client_id = ?",
                [$reward['points_value'], $userId]
            );
        }

        return true;
    }
}
