<?php
namespace App\Models;

use PDO;

class Tasks
{
    private $conn;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
        // Prevent the script from timing out too early during the long poll.
        set_time_limit(30); 
    }



    public function addTask($task, $id, $user_id){
        $stmt = $this->conn->prepare("INSERT INTO tasks (id, value, user_id) VALUES (:id, :value, :user_id)");
        return $stmt->execute([':id' => $id, ':value' => $task, ':user_id' => $user_id]);
    }

    public function editTask($edit_id, $edit_task, $user_id){
        $stmt = $this->conn->prepare("UPDATE tasks SET value = :value WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $edit_id, ':value' => $edit_task, ':user_id' => $user_id]);
    } 

    public function deleteTask($delete_id, $user_id){
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $delete_id, ':user_id' => $user_id]);
    }

    public function getTasks($user_id){
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll();
    }
    // --- SHORT POLLING LOGIC ---
    public function shortPolling($client_task_count, $user_id){
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $server_task_count = $stmt->fetchColumn();

        if ($server_task_count != $client_task_count) {
            $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll();
        }
        
        return null;
    }
    public function deleteAllTasks($user_id){
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $user_id]);
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM tasks");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
