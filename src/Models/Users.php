<?php
namespace App\Models;

use PDO;

class Users
{
    private $conn;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
    }

    private function generateUUID()
    {
        $data = random_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function register($email, $password, $name)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $id = $this->generateUUID();

        if (empty($email) || empty($password) || empty($name)) {
            return ["success" => false, "message" => "Email, password, and name are required"];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Invalid email format"];
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            return ["success" => false, "message" => "Email already registered"];
        }

        //Only Ryder is Admin
        $role = $email === 'rainryder4@gmail.com' ? 'admin' : 'user';

        $stmt = $this->conn->prepare("INSERT INTO users (id, email, password, name, role) VALUES (:id, :email, :password, :name, :role)");
        if ($stmt->execute([':id' => $id, ':email' => $email, ':password' => $hashedPassword, ':name' => $name, ':role' => $role])) {
            return ["success" => true, "id" => $id];
        } else {
            return ["success" => false, "message" => "Registration failed"];
        }
    }

    public function login($email, $password)
    {
        if (empty($email) || empty($password)) {
            return ["success" => false, "message" => "Email and password are required"];
        }

        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return ["success" => true, "user" => $user];
        } else {
            return ["success" => false, "message" => "Invalid email or password"];
        }
    }

    public function getUserById($id)
    {
        $stmt = $this->conn->prepare("SELECT id, email, name, role, created_at FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT id, email, name, role, created_at FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersWithStats()
    {
        // Use COALESCE to return empty array if no sessions/tasks
        $sql = "
            SELECT u.id, u.email, u.name, u.role, u.created_at,
                   COALESCE(s.sessions, '[]') as sessions,
                   COALESCE(t.tasks, '[]') as tasks
            FROM users u
            LEFT JOIN (
                SELECT user_id,
                       JSON_ARRAYAGG(JSON_OBJECT('id', id, 'session_token', session_token, 'created_at', created_at, 'expires_at', expires_at)) as sessions
                FROM sessions
                WHERE expires_at > NOW()
                GROUP BY user_id
            ) s ON u.id = s.user_id
            LEFT JOIN (
                SELECT user_id,
                       JSON_ARRAYAGG(JSON_OBJECT('id', id, 'value', value)) as tasks
                FROM tasks
                GROUP BY user_id
            ) t ON u.id = t.user_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON strings back to PHP arrays so json_encode in controller works correctly
        foreach ($results as &$row) {
            $row['sessions'] = json_decode($row['sessions'], true);
            $row['tasks'] = json_decode($row['tasks'], true);
        }

        return $results;
    }
}
?>