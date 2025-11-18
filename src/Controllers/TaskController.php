<?php
namespace App\Controllers;

use App\Models\Tasks;

class TaskController {
    private $conn;
    private $tasksModel;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->tasksModel = new Tasks($this->conn);
    }

    public function handleApiRequest($request_uri, $method) {
        $user_id = $_SESSION['user_id'];
        if ($method === "POST") {
            if (isset($_POST["task"])) {
                $task = htmlspecialchars($_POST["task"], ENT_QUOTES, 'UTF-8');
                $id = uniqid();
                if ($this->tasksModel->addTask($task, $id, $user_id)) {
                    echo json_encode(["success" => true, "id" => $id]);
                }
            } elseif (isset($_POST["edit_task_id"])) {
                $edit_id = $_POST["edit_task_id"];
                $edit_task = htmlspecialchars($_POST["edit_task"], ENT_QUOTES, 'UTF-8');
                if ($this->tasksModel->editTask($edit_id, $edit_task, $user_id)) {
                    echo json_encode(["success" => true]);
                }
            } elseif (isset($_POST["delete_task_id"])) {
                $delete_id = $_POST["delete_task_id"];
                if ($this->tasksModel->deleteTask($delete_id, $user_id)) {
                    echo json_encode(["success" => true]);
                }
            }
        } elseif ($method === "GET") {
            if (isset($_GET['poll'])) {
                $client_task_count = isset($_GET['count']) ? (int)$_GET['count'] : -1;
                $tasks = $this->tasksModel->shortPolling($client_task_count, $user_id);

                if ($tasks !== null) {
                    echo json_encode($tasks);
                } else {
                    http_response_code(204); // No Content
                }
            } else {
                $tasks = $this->tasksModel->getTasks($user_id);
                echo json_encode($tasks);
            }
        } elseif ($method === "DELETE") {
            if ($this->tasksModel->deleteAllTasks($user_id) !== false) {
                echo json_encode(["success" => true]);
            }
        }
    }
}
?>