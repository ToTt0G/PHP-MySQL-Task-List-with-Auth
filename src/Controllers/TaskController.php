<?php
namespace App\Controllers;

use App\Models\Tasks;
use App\Helpers\Csrf;

class TaskController {
    private $tasksModel;

    public function __construct(Tasks $tasksModel) {
        $this->tasksModel = $tasksModel;
    }

    public function handleApiRequest($request_uri, $method) {
        $user_id = $_SESSION['user_id'];

        // CSRF Protection for state-changing methods
        if ($method !== 'GET') {
            $headers = getallheaders();
            $token = $headers['X-CSRF-Token'] ?? '';
            
            if (!Csrf::verify($token)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                return;
            }
        }

        if ($method === "POST") {
            if (isset($_POST["task"])) {
                $this->createTask($user_id);
            } elseif (isset($_POST["edit_task_id"])) {
                $this->updateTask($user_id);
            } elseif (isset($_POST["delete_task_id"])) {
                $this->deleteTask($user_id);
            }
        }
    }

    private function createTask($user_id) {
        $task = htmlspecialchars($_POST["task"], ENT_QUOTES, 'UTF-8');
        $id = uniqid();
        if ($this->tasksModel->addTask($task, $id, $user_id)) {
            echo json_encode(["success" => true, "id" => $id, "task" => $task]);
        }
    }

    private function updateTask($user_id) {
        $edit_id = $_POST["edit_task_id"];
        $edit_task = htmlspecialchars($_POST["edit_task"], ENT_QUOTES, 'UTF-8');
        if ($this->tasksModel->editTask($edit_id, $edit_task, $user_id)) {
            echo json_encode(["success" => true]);
        }
    }

    private function deleteTask($user_id) {
        $delete_id = $_POST["delete_task_id"];
        if ($this->tasksModel->deleteTask($delete_id, $user_id)) {
            echo json_encode(["success" => true]);
        }
    }

    private function getTasks($user_id) {
        $tasks = $this->tasksModel->getTasks($user_id);
        echo json_encode($tasks);
    }

    private function pollTasks($user_id) {
        $client_task_count = isset($_GET['count']) ? (int)$_GET['count'] : -1;
        $tasks = $this->tasksModel->shortPolling($client_task_count, $user_id);

        if ($tasks !== null) {
            echo json_encode($tasks);
        } else {
            http_response_code(204); // No Content
        }
    }

    private function deleteAllTasks($user_id) {
        if ($this->tasksModel->deleteAllTasks($user_id) !== false) {
            echo json_encode(["success" => true]);
        }
    }
}
?>