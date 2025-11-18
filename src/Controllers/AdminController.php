<?php
namespace App\Controllers;

use App\Models\Sessions;
use App\Models\Tasks;
use App\Models\Users;

class AdminController
{
    private $conn;
    private $sessionsModel;
    private $tasksModel;
    private $usersModel;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
        $this->sessionsModel = new Sessions($this->conn);
        $this->tasksModel = new Tasks($this->conn);
        $this->usersModel = new Users($this->conn);
    }

    public function handleApiRequest($request_uri, $method)
    {
        if ($method === "GET") {
            switch ($request_uri) {
                case '/api/admin/all-sessions':
                    $sessions = $this->sessionsModel->getAll();
                    echo json_encode($sessions);
                    break;
                case '/api/admin/all-tasks':
                    $tasks = $this->tasksModel->getAll();
                    echo json_encode($tasks);
                    break;
                case '/api/admin/all-users':
                    $users = $this->usersModel->getAll();
                    echo json_encode($users);
                    break;
                //Check if request is to /api/users/:id
                case '/api/admin/users/current':
                    $id = substr($request_uri, strpos($request_uri, '/api/admin/users/') + strlen('/api/admin/users/'));
                    $user = $this->usersModel->getUserById($id);
                    if ($user) {
                        echo json_encode($user);
                    } else {
                        echo json_encode(["success" => false, "message" => "User not found"]);
                    }
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
                    break;
            }
        }
    }
}