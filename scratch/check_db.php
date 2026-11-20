<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$db = \Config\Database::connect();
$query = $db->query("DESCRIBE food");
print_r($query->getResult());
