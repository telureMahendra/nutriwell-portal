<?php
try {
    $db = \Config\Database::connect();
    $builder = $db->table('calories_burned');
    $result = $builder->limit(1)->get()->getRow();
    echo 'Table calories_burned exists.' . PHP_EOL;
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
