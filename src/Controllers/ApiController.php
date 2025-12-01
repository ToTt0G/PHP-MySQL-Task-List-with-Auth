<?php
namespace App\Controllers;

use App\Controllers\AuthController;
use App\Controllers\UsersController;
use App\Controllers\TaskController;
use App\Controllers\AdminController;

use App\Models\Tasks;
use App\Models\Users;
use App\Models\Sessions;

class ApiController
{
    private $conn;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
    }

    public function handleRequest($request_uri, $method)
    {
        header('Content-Type: application/json');
        //Check if request is to /api/auth
        $endpoint = '';
        if (preg_match('#^/api/([^/]+)#', $request_uri, $matches)) {
            $endpoint = $matches[1];
        }

        switch ($endpoint) {
            case 'auth':
            case 'users':
                $usersModel = new Users($this->conn);
                $sessionsModel = new Sessions($this->conn);
                $authController = new AuthController($usersModel, $sessionsModel);
                $authController->handleApiRequest($request_uri, $method);
                break;
            
            // Add this block to handle /api/tasks
            case 'tasks':
                $tasksModel = new Tasks($this->conn);
                $taskController = new TaskController($tasksModel);
                $taskController->handleApiRequest($request_uri, $method);
                break;

            // Add this block to handle /api/admin
            case 'admin':
                $adminController = new AdminController($this->conn);
                $adminController->handleApiRequest($request_uri, $method);
                break;
        }
    }
}
?>