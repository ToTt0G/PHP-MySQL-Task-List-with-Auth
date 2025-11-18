<?php
namespace App\Models;

use PDO;

class Sessions
{
    private $conn;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
    }

    private function generateUUID()
    {
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function createSession($user_id)
    {
        $session_token = bin2hex(random_bytes(32));
        $session_id = $this->generateUUID();

        $stmt = $this->conn->prepare("INSERT INTO sessions (id, user_id, session_token, expires_at) VALUES (:id, :user_id, :session_token, DATE_ADD(NOW(), INTERVAL 30 DAY))");
        if ($stmt->execute([':id' => $session_id, ':user_id' => $user_id, ':session_token' => $session_token])) {
            return $session_token;
        } else {
            return false;
        }
    }

    public function getSession($session_token)
    {
        $stmt = $this->conn->prepare("SELECT * FROM sessions WHERE session_token = :session_token AND expires_at > NOW()");
        $stmt->execute([':session_token' => $session_token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteSession($session_token)
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE session_token = :session_token");
        return $stmt->execute([':session_token' => $session_token]);
    }

    public function refreshSession($session_token)
    {
        $stmt = $this->conn->prepare("UPDATE sessions SET expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE session_token = :session_token");
        return $stmt->execute([':session_token' => $session_token]);
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT id, user_id, session_token, created_at, expires_at FROM sessions");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>