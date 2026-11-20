<?php
try {
    $pdo = new PDO('mysql:host=healthie4u.in;dbname=healthie4u_db', 'root', 'Info@2024#');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully\n";

    $userId = 208;

    $stmt = $pdo->prepare("INSERT INTO user_rewards (user_id, challenge_id, reward_type, points_value, is_used, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Reward 1
    $stmt->execute([$userId, 4, 'points', 250, 0, date('Y-m-d H:i:s')]);
    
    // Reward 2
    $stmt->execute([$userId, 5, 'appointment_unlock', 0, 0, date('Y-m-d H:i:s')]);
    
    // Reward 3
    $stmt->execute([$userId, 6, 'points', 500, 0, date('Y-m-d H:i:s')]);

    // Update total points in client_profile
    $stmt = $pdo->prepare("UPDATE client_profile SET total_reward_points = COALESCE(total_reward_points, 0) + ? WHERE client_id = ?");
    $stmt->execute([750, $userId]);

    echo "Inserted 3 rewards for user $userId successfully.\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
