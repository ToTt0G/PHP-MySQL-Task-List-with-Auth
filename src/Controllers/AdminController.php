<?php
namespace App\Controllers;

use App\Models\Sessions;
use App\Models\Tasks;
use App\Models\Users;
use App\Helpers\Csrf;

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

    private function checkAdmin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Fetch user role to ensure they are admin
        $user = $this->usersModel->getUserById($_SESSION['user_id']);
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden: Admins only']);
            exit;
        }
    }

    public function handleApiRequest($request_uri, $method)
    {
        $this->checkAdmin();

        if ($method === "GET") {
            // Handle specific routes
            if ($request_uri === '/api/admin/all-sessions') {
                $sessions = $this->sessionsModel->getAll();
                echo json_encode($sessions);
                return;
            }

            if ($request_uri === '/api/admin/all-tasks') {
                $tasks = $this->tasksModel->getAll();
                echo json_encode($tasks);
                return;
            }

            if ($request_uri === '/api/admin/all-users') {
                $users = $this->usersModel->getAll();
                echo json_encode($users);
                return;
            }

            // Handle dynamic route /api/admin/users/{id}
            if (preg_match('#^/api/admin/users/([a-zA-Z0-9-]+)$#', $request_uri, $matches)) {
                $id = $matches[1];
                $user = $this->usersModel->getUserById($id);
                if ($user) {
                    echo json_encode($user);
                } else {
                    echo json_encode(["success" => false, "message" => "User not found"]);
                }
                return;
            }

            // Default 404
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        }

        if ($method === "DELETE") {
            // CSRF Check
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!Csrf::verify($token)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                return;
            }

            // Handle delete session: /api/admin/sessions/{id}
            if (preg_match('#^/api/admin/sessions/([a-zA-Z0-9-]+)$#', $request_uri, $matches)) {
                $id = $matches[1];
                if ($this->sessionsModel->deleteSessionById($id)) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "message" => "Failed to delete session"]);
                }
                return;
            }
            
             // Default 404 for DELETE
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        }
    }
}