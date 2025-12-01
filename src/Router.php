<?php

namespace App;

use App\Controllers\ApiController;
use App\Controllers\ViewController;
use App\Middleware\AuthMiddleware;

class Router
{
    private $conn;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
        AuthMiddleware::init($db_connection); // Initialize AuthMiddleware
    }

    private $protectedRoutes = [
        '/api/users',
        '/api/dashboard',
        '/dashboard',
        '/api/tasks',
        '/tasks'
    ];

    public function handleRequest()
    {
        $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Define which routes do not require authentication
        $publicRoutes = ['/login', '/register', '/api/auth/register', '/api/auth/login'];

        // If the route is not public, it's protected, so run the middleware.
        if (!in_array($request_uri, $publicRoutes)) {
            AuthMiddleware::handle();
        }

        // Special handling for authenticated users trying to access login/register
        if (isset($_SESSION['user_id']) && in_array($request_uri, ['/login', '/register'])) {
            header('Location: /tasks');
            exit();
        }

        if (strpos($request_uri, '/api') === 0) {
            $apiController = new ApiController($this->conn);
            $apiController->handleRequest($request_uri, $method);
        } else {
            $viewController = new ViewController($this->conn);
            $viewController->handleRequest($request_uri, $method);
        }
    }
}
?>