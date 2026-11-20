<?php
// C:\xampp\htdocs\h4u-aws-backup\create_challenges_tables_v2.php

$host = 'localhost';
$db   = 'ci4';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$queries = [
    "CREATE TABLE IF NOT EXISTS `challenges` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `image_url` VARCHAR(255),
        `activity_type` ENUM('water', 'steps', 'meditation', 'sleep', 'workout') NOT NULL,
        `target_value` INT NOT NULL DEFAULT 1,
        `target_days` INT NOT NULL DEFAULT 1,
        `points` INT NOT NULL DEFAULT 0,
        `reward_type` ENUM('points', 'appointment_unlock') DEFAULT 'points',
        `status` ENUM('Active', 'Inactive', 'Deleted') DEFAULT 'Active',
        `created_by` INT,
        `creator_type` ENUM('Admin', 'Mentor') DEFAULT 'Admin',
        `created_at` DATETIME,
        `updated_at` DATETIME
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `meditation_tracker` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `client_id` INT NOT NULL,
        `date` DATE NOT NULL,
        `hours` INT DEFAULT 0,
        `minutes` INT DEFAULT 0,
        `start_time` TIME,
        `end_time` TIME,
        `created_on` DATETIME,
        `created_at` DATETIME,
        `created_by` INT,
        `updated_at` DATETIME,
        `updated_on` DATETIME,
        `updated_by` INT,
        INDEX (`client_id`),
        INDEX (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `user_challenges` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `challenge_id` INT NOT NULL,
        `current_streak` INT DEFAULT 0,
        `last_met_date` DATE,
        `status` ENUM('Joined', 'Completed', 'Failed') DEFAULT 'Joined',
        `started_at` DATETIME,
        `completed_at` DATETIME,
        UNIQUE KEY `user_challenge` (`user_id`, `challenge_id`),
        INDEX (`user_id`),
        INDEX (`challenge_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS `user_rewards` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `reward_type` ENUM('appointment', 'badge', 'bonus_points') DEFAULT 'appointment',
        `is_used` TINYINT(1) DEFAULT 0,
        `reference_id` INT COMMENT 'ID of the challenge or milestone that granted this',
        `created_at` DATETIME,
        INDEX (`user_id`),
        INDEX (`reward_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($queries as $query) {
    try {
        $pdo->exec($query);
        echo "Success: Table created or already exists.\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
echo "Done.\n";
