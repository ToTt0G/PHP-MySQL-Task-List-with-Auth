<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/db_config.php';

use App\Router;

$conn = App\Config\get_db_connection();

$router = new Router($conn);
$router->handleRequest();

?>