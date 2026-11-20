<?php
// test_routes.php
namespace Config;

use CodeIgniter\Config\Services;
use CodeIgniter\Router\RouteCollection;

// Create a router instance
$routes = Services::routes();

// Load the routes file
require APPPATH . 'Config/Routes.php';

echo "<h2>Registered Routes:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Method</th><th>Route</th><th>Handler</th></tr>";

foreach ($routes->getRoutes() as $method => $methodRoutes) {
    foreach ($methodRoutes as $route => $handler) {
        echo "<tr>";
        echo "<td>" . strtoupper($method) . "</td>";
        echo "<td>" . htmlspecialchars($route) . "</td>";
        echo "<td>" . htmlspecialchars($handler) . "</td>";
        echo "</tr>";
    }
}

echo "</table>";