<?php
namespace App\Controllers;

class ViewController
{
    private $conn;

    public function __construct($db_connection = null)
    {
        $this->conn = $db_connection;
    }

    //Handle Requests
    public function handleRequest($request_uri, $method)
    {
        // Public routes
        switch ($request_uri) {
            case '/register':
                require __DIR__ . '/../Views/register.php';
                break;
                
            case '/login':
                require __DIR__ . '/../Views/login.php';
                break;
                
            case '/admin/dashboard':
                require __DIR__ . '/../Views/dashboard.php';
                break;
                
            case '/tasks':
                require __DIR__ . '/../Views/tasks.php';
                break;

            case '/':
                header('Location: /tasks');
                exit;

            default:
                header('Location: /login');
                exit;
        }
    }
}
?>