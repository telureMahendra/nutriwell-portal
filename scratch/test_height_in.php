<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=ci4', 'root', '');
    $stmt = $db->query("SELECT DISTINCT height_in FROM client_profile");
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_r($results);
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
